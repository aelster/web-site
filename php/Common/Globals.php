<?php

return;

$vars = ['gDebug','gFunction','gPDO_attr','gPDO_dbh','gPDO_dsn','gPDO_user','gPDO_pass','gSiteLoadLibrary','gTrace'];

foreach( $vars as $var ) {
    if( empty( $$var ) ) {
        $var = 0;
    }
}
/*
$gDebug = 0;
$gFunction = array();
$gPDO_attr = array();
$gPDO_dbh = null;
$gPDO_dsn = "";
$gPDO_user = "";
$gPDO_pass = "";
$gSiteLoadLibraries = array();
$gTrace = 0;
*/