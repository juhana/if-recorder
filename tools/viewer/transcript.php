<?php
/**
Parchment Transcript Recording Plugin Database Viewer
	* URL: http://code.google.com/p/parchment-transcript/
	* Description: A tool for viewing transcripts recorded with the transcript recording plugin for Parchment.
	* Author: Juhana Leinonen
	* Copyright: Copyright (c) 2011 Juhana Leinonen under MIT license.
**/ 

// transcript session id
$session = $_GET[ 'session' ];

$options = array(
	'warnings'		=> true,
	'statusline'	=> 'inline',
	'output'		=> 'html',
	'stripHTML'		=> true
);

// show warnings?
$options[ 'warnings' ] = !( isset( $_GET[ 'warnings' ] ) && $_GET[ 'warnings' ] == '0' );

if( isset( $_GET[ 'statusline' ] ) ) {
	$options[ 'statusline' ] = $_GET[ 'statusline' ];
}

if( isset( $_GET[ 'output' ] ) ) {
	$options[ 'output' ] = $_GET[ 'output' ];
}

$options[ 'stripHTML' ] = !( isset( $_GET[ 'stripHTML' ] ) && $_GET[ 'stripHTML' ] == '0' );


// tell the browser we're sending plaintext if so requested,
// otherwise create the HTML page structure
if( $options[ 'output' ] == 'text' ) {
	header( "Content-type: text/plain" );
}
else {

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

<?php 
}

define( 'INCLUDE_PATH', '../include/' );

include_once( INCLUDE_PATH.'db.php' );
include_once( INCLUDE_PATH.'view.php' );


echo display_transcript( $db, $session, $options );

if( $options[ 'output' ] == 'html' ) {
?>
</div>
</div>
</body>
</html><?php 
}