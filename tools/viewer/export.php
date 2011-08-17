<?php
/**
Parchment Transcript Recording Plugin Database Exporter
	* URL: http://code.google.com/p/parchment-transcript/
	* Description: A tool for exporting transcripts recorded with the transcript recording plugin for Parchment.
	* Author: Juhana Leinonen
	* Copyright: Copyright (c) 2011 Juhana Leinonen under MIT license.
**/

// a simple function to sanitize file and directory names 
function sanitize_filename( $filename ) {
	$sanitized = trim( preg_replace( "/[^a-zA-Z0-9]/", "_", $filename ), "_" );
	if( empty( $sanitized ) ) {
		return '__';
	}
	return $sanitized;
}

if( empty( $argv ) ) {
	die( 'Please run this tool from the command line.' );
}

define( 'INCLUDE_PATH', '../include/' );

require_once( INCLUDE_PATH.'db.php' );
require_once( INCLUDE_PATH.'view.php' );


// helper for getting the command line arguments.
function get_argval( $arguments, $value, $default ) {
	$pos = array_search( "--$value", $arguments );
	if( $pos === false || !isset( $arguments[ $pos + 1 ] ) ) {
		return $default;
	}
	
	return $arguments[ $pos + 1 ];
}

if( in_array( '--help', $argv ) ) {
	$error = true;
}

// transcript session id
$session = $_GET[ 'session' ];

$options = array(
	'session'		=> get_argval( $argv, 'session', false ),
	'story'			=> get_argval( $argv, 'story', false ),
	'all'			=> in_array( '--all', $argv ),
	'path'			=> get_argval( $argv, 'path', false ),
	'output'		=> get_argval( $argv, 'output', 'txt' ),
	'statusline'	=> in_array( '--statusline', $argv ),
	'warnings'		=> in_array( '--warnings', $argv ),
	'from'			=> get_argval( $argv, 'from', false )
);

$options[ 'extension' ] = get_argval( $argv, 'extension', '.'.$options[ 'output' ] );

$mute = ( in_array( '--mute', $argv ) || $options[ 'path' ] == false );

if( in_array( '--help', $argv ) || ( $options[ 'story' ] == false && $options[ 'session' ] == false && $options[ 'all' ] == false ) ) {
	echo <<<EOT
Options:
  --session <id>: Name of a single play session to export
  --story <id>: Export all play sessions of this story
  --all: Export the entire database
  --path <pathname>: The path where to export the sessions, no trailing /. The directory will be created if it doesn't exist. Leave empty for output to stdout.  
  --output <type>: Either html or txt (default)
  --statusline: Add status line to transcripts
  --warnings: Add a warning to the transcript if it's missing parts
  --from <yyy-mm-dd>: Export only transcripts newer than the given date (inclusive)
  --extension <ext>: File extension appended to transcripts (default ".txt" for text files, ".html" for HTML files)
  --mute: Don't print status reports while exporting (mute mode automatically on if printing to stdout)
  --help: Display this text

Example: php -q export.php --story zork --path transcripts/zork --warnings

With the --all option a separate folder is created for each story 
and story and session options are ignored.

At least one of the following parameters are required: --session, --story, or --all.


EOT;
	die();
}


$htmlHeader = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<title>Transcript viewer</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="assets/parchment.css">
<link rel="stylesheet" type="text/css" href="assets/glkote.css">
<link rel="stylesheet" type="text/css" href="assets/transcript.css">

</head>

<body>
<div id="transcript">

<div id="content">

EOT;

$htmlFooter = "\n</div></div>\n</body></html>\n";

$queryConditions = array();
$tokens = array();

if( !$options[ 'all' ] ) {
	if( $options[ 'session' ] ) {
		$queryConditions[] = "session = :session";
		$tokens[ ':session' ] = $options[ 'session' ];
	} 
	if( $options[ 'story' ] ) {
		$queryConditions[] = "story = :story";
		$tokens[ ':story' ] = $options[ 'story' ];
	}
}

if( $options[ 'from' ] ) {
	$queryConditions[] = "started >= :from";
		$tokens[ ':from' ] = $options[ 'from' ];
}

if( empty( $queryConditions ) ) {
	$queryConditions[] = '1';
}

$findSessions = $db->prepare( 'SELECT session, story, started, DATE( started ) AS date FROM stories WHERE '
	.implode( ' AND ', $queryConditions )
	.' ORDER BY started ASC' );
$findSessions->execute( $tokens ) or database_error( $findSessions->errorInfo() );

$sessions = $findSessions->fetchAll();

$foundRows = count( $sessions );

if( $foundRows == 0 ) {
	server_error( 'No matching transcripts found.' );
}

if( !$mute && $foundRows > 1 ) {
	echo "$foundRows transcripts found.\n";
}

foreach( $sessions as $session ) {
	if( !$mute ) {
		echo "Exporting session $session[session] ($session[started])...\n";
	}

	$transcript = display_transcript( $db, $session[ 'session' ], $options );
	
	if( empty( $transcript ) ) {
		file_put_contents( 'php://stderr', "Skipping invalid/empty session $session[session].\n" );
		continue;
	}

	if( strtolower( $options[ 'output' ] ) == 'html' ) {
		$transcript = $htmlHeader.$transcript.$htmlFooter;
	}

	if( !$options[ 'path' ] ) {
		$filename = 'php://stdout'; 
	}
	else {
		$pathname = $options[ 'path' ];
		if( $options[ 'all' ] ) {
			$pathname .= '/'.sanitize_filename( $session[ 'story' ] );
		}
		
		if( !file_exists( $pathname ) ) {
			mkdir( $pathname, 0777, true );
		}
		
		$filename = $pathname.'/'.$session[ 'date' ].' '.sanitize_filename( $session[ 'session' ] ).$options[ 'extension' ];
	}
	
	$file = fopen( $filename, 'w' );
	
	if( $file === false ) {
		server_error( "Unable to open $filename for writing." );
	}
	
	if( !fwrite( $file, $transcript ) ) {
		server_error( "Error writing to $filename" );
	}
	
	fclose( $file );
}

if( !$mute ) {
	echo "\nDone.\n\n";
}