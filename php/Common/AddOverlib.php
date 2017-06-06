<?php
function AddOverlib()
{
	global $gFunction;
	global $gTrace;

	if( $trace ) {
		$gFunction[] = __FUNCTION__;
		Logger();
	}
?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
	if( $gTrace ) array_pop( $gFunction );
}
?>
