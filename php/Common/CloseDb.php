<?php
function CloseDb() {
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = "CloseDb()";
		Logger();
	}
	mysql_close( $GLOBALS['mysql_db'] );
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
}
?>