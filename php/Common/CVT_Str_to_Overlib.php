<?php
function CVT_Str_to_Overlib( $str ) {
	$str = str_replace( "'", "\\'", $str );
	
	$patterns[0] = '/</';
	$patterns[1] = '/>/';
	$patterns[2] = '/"/';
	$patterns[3] = "/'/";

	$replacements[0] = '&lt;';
	$replacements[1] = '&gt;';
	$replacements[2] = '&quot;';
	$replacements[3] = '&#39;';

	return preg_replace( $patterns, $replacements, $str );
}
?>