<?php

function OpenDb(
				$dsn = 'PDO data source',
				$user = 'PDO user',
				$pass = 'PDO password' ) {
	
	global $gPDO_dsn, $gPDO_user, $gPDO_pass, $gPDO_attr;
	
	if( $GLOBALS['gTrace'] ) {
		$GLOBALS['gFunction'][] = __FUNCTION__;
		Logger();
	}
	
	if( strcmp( $dsn, 'PDO data source') == 0 ) { // No DSN specified, default assigned
		$dsn = ! empty( $GLOBALS['gPDO_dsn'] ) ? $gPDO_dsn : $dsn; // Use the global value if present
	}

	if( strcmp( $user, 'PDO user') == 0 ) { // No USER specified, default assigned
		$user = ! empty( $GLOBALS['gPDO_user'] ) ? $gPDO_user : $user; // Use the global value if present
	}

	if( strcmp( $pass, 'PDO password') == 0 ) { // No PASSWORD specified, default assigned
		$pass = ! empty( $GLOBALS['gPDO_pass'] ) ? $gPDO_pass : $pass; // Use the global value if present
	}

	
	try {
		$attr = ! empty( $GLOBALS['gPDO_attr'] ) ? $gPDO_attr : array();
	    $dbh = new PDO( $dsn, $user, $pass, $attr );
		$dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	} catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . '<br/>';
		die();
	}
	
	if( $GLOBALS['gTrace'] ) array_pop( $GLOBALS['gFunction'] );
	
	return $dbh;
}
?>