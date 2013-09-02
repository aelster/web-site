<?php
function Logger () {
	echo "<div align=left>";
	if( func_num_args() > 0 ) {
		echo func_get_arg(0);
	} else {
		echo join( '>', $GLOBALS['gFunction'] );
	}
	echo "</div>\n";
}
?>