<?php
function CloseDb() {
	global $gFunction;
	global $gTrace;

	if( $trace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
	
	mysql_close( $GLOBALS['mysql_db'] );
	
	if( $gTrace ) array_pop( $gFunction );
}
?>
