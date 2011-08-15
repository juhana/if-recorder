<?php 
// Database connection settings. Edit these to match your settings.
$dbSettings = array(
	'host'		=> 'localhost',
	'database'	=> 'parchment',
	'username'	=> 'root',
	'password'	=> 'root',
	'prefix'	=> ''	// if your web site provider forces prefixes in table names, write the prefix here
);


// If you're not using MySQL, change this to whatever your database uses.
// See http://php.net/manual/en/pdo.construct.php for details.
$dsn = "mysql:host={$dbSettings[ 'host' ]};dbname={$dbSettings[ 'database' ]}";


// Connecting to the database
try {
	$db = new PDO( 
		$dsn, 
		$dbSettings[ 'username' ], 
		$dbSettings[ 'password' ],  
		array(
	    	PDO::ATTR_PERSISTENT => true
		)
	);
	$db->query( "SET NAMES 'utf8'" );
} catch (PDOException $e) {
	server_error( $e->getMessage() );
}

function server_error( $text ) {
	if( !headers_sent() ) {
		header( 'HTTP/1.1 500 Internal Server Error' );
	}
    die( $text ); 
}

function database_error( $errorInfo ) {
	server_error( 'Database error: '.print_r( $errorInfo, true ) );
}