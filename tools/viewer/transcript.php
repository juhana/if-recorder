<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<title>Transcript viewer</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="Robots" content="noindex,nofollow" />
<link rel="stylesheet" type="text/css" href="assets/parchment.css">
<link rel="stylesheet" type="text/css" href="assets/transcript.css">

</head>

<body>
<div id="transcript">
<div id="content">
<?php 
include_once( '../db.php' );

// session id
$session = $_GET[ 'session' ];

// show warnings?
$warnings = !( isset( $_GET[ 'warnings' ] ) && $_GET[ 'warnings' ] == '0' );

$prevInputCount = 0;
$prevOutputCount = 0;

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
		echo '<span class="command">'.$snippet[ 'input' ].'</span><br />';
		$prevInputCount = $snippet[ 'inputcount' ];
	}
	
	if( $snippet[ 'window' ] == 0 ) {
		echo '<span class="'.$snippet[ 'styles' ].'">';
		echo nl2br( str_replace( '  ', '&nbsp; ', str_replace( '  ', '&nbsp; ', $snippet[ 'output' ] ) ) );
		echo '</span>';
	}
}

?>
</div>
</div>
</body>
</html>