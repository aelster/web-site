<?php

global $mysql_host;
global $mysql_user;
global $mysql_pass;
global $mysql_dbname;
global $myqsl_db;

function OpenDb(
	$host="<mysql hostname>",
	$user="<mysql username>",
	$pass="<mysql password>",
	$dbname="<mysql db name>"
	) {
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = "OpenDb()";
		Logger();
	}
	
	$num_args = func_num_args();
	if( $num_args > 0 ) {
		for( $i = 0; $i < $num_args; $i++ ) {
			if( $i == 0 ) $GLOBALS['mysql_host'] = func_get_arg( $i );
			if( $i == 1 ) $GLOBALS['mysql_user'] = func_get_arg( $i );
			if( $i == 2 ) $GLOBALS['mysql_pass'] = func_get_arg( $i );
			if( $i == 3 ) $GLOBALS['mysql_dbname'] = func_get_arg( $i );
		}
	}
	
	$GLOBALS['mysql_db'] = mysql_connect(
		$GLOBALS['mysql_host'],
		$GLOBALS['mysql_user'],
		$GLOBALS['mysql_pass'],
		true );
	
	if( ! $GLOBALS['mysql_db'] )
	{
		$str = sprintf( "Could not connect using host: [%s], user: [%s], pass: [xxx]<br>",
						  $GLOBALS['mysql_host'], $GLOBALS['mysql_user'] );
		die( $str . mysql_error() );
	}
  
	$stat = mysql_select_db( $GLOBALS['mysql_dbname'], $GLOBALS['mysql_db'] );
	if( ! $stat )
	{
		$str = sprintf( "Could not select datqbase: [%s]<br>", $GLOBALS['mysql_dbname'] );
		die( $str . mysql_error() );
	}
	
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
	
	return $GLOBALS['mysql_db'];
}

?>