<?php

function OpenDb(
        $dsn = 'PDO data source',
        $user = 'PDO user',
        $pass = 'PDO password') {


    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    if (strcmp($dsn, 'PDO data source') == 0) { // No DSN specified, default assigned
        $dsn = !empty($GLOBALS['gPDO_dsn']) ? $GLOBALS['gPDO_dsn'] : $dsn; // Use the global value if present
    }

    if (strcmp($user, 'PDO user') == 0) { // No USER specified, default assigned
        $user = !empty($GLOBALS['gPDO_user']) ? $GLOBALS['gPDO_user'] : $user; // Use the global value if present
    }

    if (strcmp($pass, 'PDO password') == 0) { // No PASSWORD specified, default assigned
        $pass = !empty($GLOBALS['gPDO_pass']) ? $GLOBALS['gPDO_pass'] : $pass; // Use the global value if present
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