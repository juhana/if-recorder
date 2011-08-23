<?php
/* db.php opens the database connection */
include_once( '../include/db.php' );

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

$data = $_POST[ 'data' ];

if( !isset( $data[ 'session' ] ) && !isset( $data[ 0 ][ 'session' ] ) ) {
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
				version = ?,
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
		
		if( empty( $data[ 'start' ][ 'version' ] ) ) {
			$storyVersion = '';
		}
		else {
			$storyVersion = $data[ 'start' ][ 'version' ];
		}
		
		$insert->execute( 
			array(
				$data[ 'session' ],
				$data[ 'start' ][ 'story' ],
				$storyVersion,
				$interpreter,
				$browser,
				date( 'Y-m-d H:i:s' )
			)
		) or server_error( 'Error saving startup data: '.print_r( $insert->errorInfo(), true ) );
	}
}

// if there are no multiple log entries coming in at the same time,
// create an array of the single log entry
if( isset( $data['log'] ) ) {
	$data = array( $data );
}


/* Saving transcript pieces the database */
foreach( $data as $d ) {
	if( isset( $d[ 'log' ] ) && is_array( $d[ 'log' ] ) ) {
		if( !empty( $d[ 'log' ][ 'timestamp' ] ) ) {
			$timestamp = date( 'Y-m-d H:i:s', round( $d[ 'log' ][ 'timestamp' ] / 1000 ) );
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
		
		$insert->execute(
			array(
				$d[ 'session' ],
				$d[ 'log' ][ 'input' ],
				$d[ 'log' ][ 'output' ],
				$d[ 'log' ][ 'window' ],
				$d[ 'log' ][ 'styles' ],
				$d[ 'log' ][ 'inputcount' ],
				$d[ 'log' ][ 'outputcount' ],
				$timestamp
			)
		) or server_error( 'Error saving log data: '.print_r( $insert->errorInfo(), true ) );
		
		
		// Update story information. The "ended" counter is updated as transcript
		// pieces are saved.
		$storyupdate = $db->prepare(
			"UPDATE {$dbSettings[ 'prefix' ]}stories 
			SET ended = IF( ended < :timestamp, :timestamp, ended ),
				inputcount = IF( inputcount < :count, :count, inputcount )
			WHERE session = :session"
		);
		
		$storyupdate->execute(
			array(
				':timestamp'	=> $timestamp,
				':count'		=> $d[ 'log' ][ 'inputcount' ],
				':session'		=> $d[ 'session' ]
			)
		) or server_error( 'Error updating story data: '.print_r( $storyupdate->errorInfo(), true ) );
	}
}

die( 'OK' );