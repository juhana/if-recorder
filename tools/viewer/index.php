<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
<title>Transcript viewer</title>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="Robots" content="noindex,nofollow" />

</head>
<body>
<?php 
include_once( '../db.php' );

$query = $db->query( "SELECT * FROM {$dbSettings[ 'prefix' ]}stories WHERE 1 ORDER BY started DESC" ) or database_error( $db->errorInfo() );

echo '<ul>';

foreach( $query as $q ) {
	echo '<li><a href="transcript.php?session=';
	echo $q[ 'session' ];
	echo '">';
	echo $q[ 'story' ].' ('.$q[ 'started' ].')';
	echo '</a></li>';
}

echo '</ul>';

?>

</body>
</html>