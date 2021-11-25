<?php

function OpenDb(
        $dsn = '', //PDO data source
        $user = '', //PDO user
        $pass = '' //PDO password
    ) {
/*
 * Change the priority.
 * 
 *  1. If arguments are used, they take priority.
 *  2. If argmenets are empty, try the global variables
 *  3. If no argments and globals not set, fail
 */
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    if( empty( $dsn ) ) {
        if( array_key_exists( 'gPDO_dsn', $GLOBALS ) ) {
            $dsn = $GLOBALS['gPDO_dsn'];
        }
    }

    if( empty( $user ) ) {
        if( array_key_exists( 'gPDO_user', $GLOBALS ) ) {
            $user = $GLOBALS['gPDO_user'];
        }
    }

    if( empty( $pass ) ) {
        if( array_key_exists( 'gPDO_pass', $GLOBALS ) ) {
            $pass = $GLOBALS['gPDO_pass'];
        }
    }

    try {
        $attr = !empty($GLOBALS['gPDO_attr']) ? $GLOBALS['gPDO_attr'] : array();
        $dbh = new PDO($dsn, $user, $pass, $attr);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . '<br/>';
        die();
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);

    return $dbh;
}