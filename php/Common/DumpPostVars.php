<?php
function DumpPostVars() {
	$trace = $GLOBALS['gTrace'];
	if( $trace ) {
		$GLOBALS['gFunction'][] = __FUNCTION__;
		Logger();
	}

	echo "<div align=left target=dbg style=\"background-color: lightgrey;\">";

	if( func_num_args() > 0 ) {
		echo func_get_arg(0) . "<br>";
	}

	$dump_server = 0;

	ksort( $_POST );
	$i = 0;
	foreach( $_POST as $var => $val ) {
		if( $i++ == 0 ) { echo "---------------------------------------\n<br>"; }
		if( preg_match( "/userpass/i", $var ) || preg_match( "/password/i", $var ) ) {
			printf( "dpv:  %-20s: %s, length: %d<br>\n", $var, "******", strlen($val) );
		} else {
			if( is_array( $val ) ) {
				foreach( $val as $k => $v ) {
					printf( "dpav:  %-20s[%s]: %s<br>\n", $var, $k, $v );
				}
			}
			else
			{
				printf( "dpv:  %-20s: %s<br>\n", $var, $val );
			}
		}
	}

	$i = 0;
	if( $dump_server > 0 ) {
		if( $i++ == 0 ) { echo "---------------------------------------\n<br>"; }
		foreach( $gServer as $var => $val ) {
			if( $var != "passwd" ) {
				printf( "dsv:  %-20s: %s<br>\n", $var, $val );
			} else {
				printf( "dsv:  %-20s: %s<br>\n", $var, "******" );
			}
		}
	}

	$i = 0;
	if( isset( $_SESSION ) ) {
		foreach( $_SESSION as $var => $val ) {
			if( $i++ == 0 ) { echo "---------------------------------------\n<br>"; }
			if( $var != "passwd" ) {
				printf( "sess:  %-20s: %s<br>\n", $var, $val );
			} else {
				printf( "sess:  %-20s: %s<br>\n", $var, "******" );
			}
		}
	}
	echo "</div>";
	if( $trace ) array_pop( $GLOBALS['gFunction'] );
}
?>
