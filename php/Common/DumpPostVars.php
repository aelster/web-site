<?php

function DumpPostVars() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    Logger("<div align=left target=dbg style=\"background-color: lightgrey;\">");

    if (func_num_args() > 0) {
        Logger(func_get_arg(0));
    }

    $dump_server = 0;

    ksort($_POST);
    $i = 0;
    foreach ($_POST as $var => $val) {
        if ($i++ == 0) {
            Logger("---------------------------------------");
        }
        if (preg_match("/userpass/i", $var) || preg_match("/password/i", $var)) {
            Logger(sprintf("dpv:  %-20s: %s, length: %d", $var, "******", strlen($val)));
        } else {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    Logger(sprintf("dpav:  %-20s[%s]: %s", $var, $k, $v));
                }
            } else {
                Logger(sprintf("dpv:  %-20s: %s", $var, $val));
            }
        }
    }

    $i = 0;
    if ($dump_server > 0) {
        if ($i++ == 0) {
            Logger("---------------------------------------");
        }
        foreach ($gServer as $var => $val) {
            if ($var != "passwd") {
                Logger(sprintf("dsv:  %-20s: %s", $var, $val));
            } else {
                Logger(sprintf("dsv:  %-20s: %s", $var, "******"));
            }
        }
    }

    $i = 0;
    if (isset($_SESSION)) {
        foreach ($_SESSION as $var => $val) {
            if ($i++ == 0) {
                Logger("---------------------------------------");
            }
            if( is_object($val) ) {
                Logger(sprintf("sess:  %-20s: %s", $var, "object"));
            } elseif( is_array($val) ) {
                Logger(sprintf("sess:  %-20s: %s", $var, "array"));
            } elseif ($var != "passwd") {
                Logger(sprintf("sess:  %-20s: %s", $var, $val));
            } else {
                Logger(sprintf("sess:  %-20s: %s", $var, "******"));
            }
        }
    }
    Logger("</div>");
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
}