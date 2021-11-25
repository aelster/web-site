<?php

return;

$vars = ['gDebug','gFunction','gPDO_attr','gPDO_dbh','gPDO_dsn','gPDO_user','gPDO_pass','gSiteLoadLibrary','gTrace'];

foreach( $vars as $var ) {
    if( empty( $$var ) ) {
        $var = 0;
    }
}
