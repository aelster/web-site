<?php

global $mysql_numrows;
global $mysql_result;

function DoQuery()
{
	$num_args = func_num_args();
	$query = func_get_arg( 0 );
	$db = ( $num_args == 1 ) ? $GLOBALS[ 'mysql_db' ] : func_get_arg( 1 );
	
	$debug = $GLOBALS[ 'gDebug' ];
	$support = $GLOBALS['mysql_admin'];
	
	if( $debug ) $dmsg = "&nbsp;&nbsp;&nbsp;&nbsp;DoQuery: $query";
	
	$result = mysql_query( $query, $db );
	if( mysql_errno( $db ) != 0 )
	{
		if( ! $db ) { echo "  query: $query<br>\n"; }
		echo "  result: " . mysql_error( $db ) . "<br>\n";
		echo "I'm sorry but something unexpected occurred.  Please send all details<br>";
		echo "of what you were doing and any error messages to $support<br>";
	}
	else
	{
		if( preg_match( "/^select/i", $query ) )
		{
			$numrows = mysql_num_rows( $result );
		}
		else
		{
			$numrows = mysql_affected_rows( $db );
		}
		if( $debug ) $dmsg .= sprintf( ", # rows: %d", $numrows );
	}
	
 	if( $debug ) Logger( $dmsg );
 
	$GLOBALS[ 'mysql_numrows' ] = $numrows;
	$GLOBALS[ 'mysql_result' ] = $result;
}
?>
