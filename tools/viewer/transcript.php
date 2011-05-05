<?php
/**
Parchment Transcript Recording Plugin Database Viewer
	* URL: http://code.google.com/p/parchment-transcript/
	* Description: A tool for viewing transcripts recorded with the transcript recording plugin for Parchment.
	* Author: Juhana Leinonen
	* Copyright: Copyright (c) 2011 Juhana Leinonen under MIT license.
**/ 
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<title>Transcript viewer</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="assets/parchment.css">
<link rel="stylesheet" type="text/css" href="assets/transcript.css">

</head>

<body>
<div id="transcript">

<!--  <div style="width: 640px; line-height: 18px; left: 392.5px;" id="top-window" class="buffered-window"></div> -->
<div id="content">
<?php 
include_once( '../db.php' );

// session id
$session = $_GET[ 'session' ];

// show warnings?
$warnings = !( isset( $_GET[ 'warnings' ] ) && $_GET[ 'warnings' ] == '0' );

// status line
$statusLine = 'inline';
if( isset( $_GET[ 'statusline' ] ) ) {
	$statusLine = $_GET[ 'statusline' ];
}

$prevInputCount = 0;
$prevOutputCount = 0;

$gameText = '';
$statusLineText = '';
$outputSnippet = '';

$query = $db->prepare( 'SELECT * FROM transcripts WHERE session = ? ORDER BY outputcount ASC' );
$query->execute( array( $session ) ) or database_error( $query->errorInfo() );

$rows = $query->fetchAll();

foreach( $rows as $snippet ) {
	// The output count should be continuous. If we've skipped a count, inform the user.
	if( $prevOutputCount < $snippet[ 'outputcount' ] - 1 && $warnings ) {
		echo '<div class="error">WARNING: Possible gap in the transcript</div>';
	}
	
	$prevOutputCount = $snippet[ 'outputcount' ];

	// When input count increments, we've started a new turn.
	if( $prevInputCount != $snippet[ 'inputcount' ] ) {
		if( !empty( $statusLineText ) && $statusLine == 'inline' ) {
			echo '<div>';
			echo $statusLineText;
			echo '</div>';
		}
		echo $gameText; 
		$gameText = '';
		$statusLineText = '';
		echo '<span class="command">'.htmlentities( $snippet[ 'input' ] ).'</span><br />';
		$prevInputCount = $snippet[ 'inputcount' ];
	}
	
	$outputSnippet = '<span class="'.$snippet[ 'styles' ].'">';
	$outputSnippet .= nl2br( str_replace( '  ', '&nbsp; ', str_replace( '  ', '&nbsp; ', htmlentities( $snippet[ 'output' ] ) ) ) );
	$outputSnippet .= '</span>';

	if( $snippet[ 'window' ] == 0 ) {
		$gameText .= $outputSnippet;
	}
	
	if( $snippet[ 'window' ] == 1 ) {
		$statusLineText .= $outputSnippet; 
	}
}

// flush the final text
if( !empty( $statusLineText ) && $statusLine == 'inline' ) {
	echo '<div>';
	echo $statusLineText;
	echo '</div>';
}
echo $gameText; 
?>
</div>
</div>
</body>
</html>