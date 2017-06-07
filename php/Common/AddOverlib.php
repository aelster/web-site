<?php
function AddOverlib()
{
	if( $GLOBALS['gTrace'] ) {
		$GLOBALS['gFunction'][] = __FUNCTION__;
		Logger();
	}
?>
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<?php
	if( $GLOBALS['gTrace'] ) array_pop( $GLOBALS['gFunction'] );
}
?>
