<?php
/* db.php opens the database connection */
include_once( '../db.php' );

/*
 * json functions were added with PHP 5.2. If you get this
 * error message, you need to install a JSON library separately.
 */
if( !function_exists( 'json_decode' ) ) {
    server_error( 'JSON functions are not supported by this PHP installation' );
}

if( empty( $_POST[ 'data' ] ) ) {
	die( 'POST data not found' );
}


/* Depending on PHP settings the POST data might be escaped with 
 * slashes, so we have to strip them before continuing
 */
if( get_magic_quotes_gpc() ) {
	$postdata = stripslashes( $_POST[ 'data' ] );
}
else {
	$postdata = $_POST[ 'data' ];
}

$data = json_decode( $postdata, true );

/*
 * json_last_error() was introduced in PHP 5.3.0.
 * If the function exists we can use that, otherwise just check if the
 * result was null 
 */
if( function_exists( 'json_last_error' ) ) {
	$jsonError = json_last_error();

	if( $jsonError != JSON_ERROR_NONE ) {
		die( "JSON parsing error $jsonError" ); 
	}
}
else {
	if( $data === NULL ) {
		die( 'JSON parsing error' );
	}
}

if( !isset( $data[ 'session' ] ) ) {
	die( 'Session identifier missing' );
}

// if there is no log information, this is the start of a transcript
if( !empty( $data[ 'start' ] ) ) {
	if( empty( $data[ 'start' ][ 'story' ] ) ) {
		die( 'Story identifier missing' );
	}
	
	// check if the session data is already in the database, save if not
	$query = $db->prepare(
		"SELECT COUNT(*) as count FROM {$dbSettings[ 'prefix' ]}stories 
			WHERE session = ?"
	);
	
	$query->execute( array( $data[ 'session' ] ) );
	$count = $query->fetchAll();
	
	if( $count[ '0' ][ 'count' ] == 0 ) { 
		$insert = $db->prepare(
			"INSERT INTO {$dbSettings[ 'prefix' ]}stories 
				SET session = ?,
				story = ?,
				interpreter = ?,
				browser = ?,
				started = ?"
		);
		
		if( empty( $data[ 'start' ][ 'interpreter' ] ) ) {
			$interpreter = '';
		}
		else {
			$interpreter = $data[ 'start' ][ 'interpreter' ];
		}
		
		if( empty( $data[ 'start' ][ 'browser' ] ) ) {
			$browser = '';
		}
		else {
			$browser = $data[ 'start' ][ 'browser' ];
		}
		
		if( !$insert->execute( 
			array(
				$data[ 'session' ],
				$data[ 'start' ][ 'story' ],
				$interpreter,
				$browser,
				date( 'Y-m-d H:i:s' )
			)
		) ) {
			server_error( 'Error saving startup data: '.print_r( $insert->errorInfo(), true ) );
		}
	}
}

/* Saving transcript pieces the database */
if( !empty( $data[ 'log' ] ) ) {
	if( !empty( $data[ 'log' ][ 'timestamp' ] ) ) {
		$timestamp = date( 'Y-m-d H:i:s', round( $data[ 'log' ][ 'timestamp' ] / 1000 ) );
	}
	else {
		$timestamp = '';
	}
	
	$insert = $db->prepare( 
		"INSERT INTO {$dbSettings[ 'prefix' ]}transcripts 
		SET session = ?,
			input = ?,
			output = ?,
			window = ?,
			styles = ?,
			inputcount = ?,
			outputcount = ?,
			timestamp = ?"
	);
	
	if( !$insert->execute(
		array(
			$data[ 'session' ],
			$data[ 'log' ][ 'input' ],
			$data[ 'log' ][ 'output' ],
			$data[ 'log' ][ 'window' ],
			$data[ 'log' ][ 'styles' ],
			$data[ 'log' ][ 'inputcount' ],
			$data[ 'log' ][ 'outputcount' ],
			$timestamp
		)
	) ) {
		server_error( 'Error saving log data: '.print_r( $insert->errorInfo(), true ) );
	}
	
	// Update story information. The "ended" counter is updated as transcript
	// pieces are saved.
	$storyupdate = $db->prepare(
		"UPDATE {$dbSettings[ 'prefix' ]}stories 
		SET ended = IF( ended < :timestamp, :timestamp, ended ),
			inputcount = IF( inputcount < :count, :count, inputcount ),
		WHERE session = :session"
	);
	
	$storyupdate->execute(
		array(
			':timestamp'	=> $timestamp,
			':count'		=> $data[ 'log' ][ 'inputcount' ],
			':session'		=> $data[ 'session' ]
		)
	);
}

die( 'OK' );