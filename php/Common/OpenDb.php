<?php

global $mysql_host;
global $mysql_user;
global $mysql_pass;
global $mysql_dbname;
global $myqsl_db;

function OpenDb(
	$host="<mysql hostname>",
	$dbname="<mysql db name>",
	$user="<mysql username>",
	$pass="<mysql password>"
	) {
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = __FUNCTION__;
		Logger();
	}
	
	$num_args = func_num_args();
	if( $num_args > 0 ) {
		for( $i = 0; $i < $num_args; $i++ ) {
			if( $i == 0 ) $GLOBALS['mysql_host'] = func_get_arg( $i );
			if( $i == 1 ) $GLOBALS['mysql_dbname'] = func_get_arg( $i );
			if( $i == 2 ) $GLOBALS['mysql_user'] = func_get_arg( $i );
			if( $i == 3 ) $GLOBALS['mysql_pass'] = func_get_arg( $i );
		}
	}

	$str = 'mysql:host=' . $GLOBALS['mysql_host'] . ';dbname=' . $GLOBALS['mysql_dbname'];
	$user = $GLOBALS['mysql_user'];
	$pass = $GLOBALS['mysql_pass'];

try {
    $dbh = new PDO($str, $user, $pass );
    echo "Success";

} catch (PDOException $e) {
	print "Error!: " . $e->getMessage() . '<br/>';
	die();
}
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
	
	return $GLOBALS['mysql_db'];
}

?>
