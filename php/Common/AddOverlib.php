<?php
function AddOverlib()
{
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = "AddOverlib()";
		Logger();
	}
?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
}
?>