<?php

function DumpPostVars() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $saved = [];
    $saved[] = "<div align=left target=dbg class=dpv>";

    if (func_num_args() > 0) {
        $saved[] = func_get_arg(0);
    }

    $dump_server = 0;
    $saved[] = sprintf("# post variables: %d", count(array_keys($_POST)));

    ksort($_POST);
    $i = 0;
    foreach ($_POST as $var => $val) {
        if ($i++ == 0) {
            $saved[] = "---------------------------------------";
        }
        if (preg_match("/userpass/i", $var) || preg_match("/password/i", $var)) {
            $saved[] = sprintf("dpv:  %-20s: %s, length: %d", $var, "******", strlen($val));
        } else {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $saved[] = sprintf("dpav:  %-20s[%s]: %s", $var, $k, $v);
                }
            } else {
                $saved[] = sprintf("dpv:  %-20s: %s", $var, $val);
            }
        }
    }

    $i = 0;
    if ($dump_server > 0) {
        if ($i++ == 0) {
            $saved[] = "---------------------------------------";
        }
        foreach ($gServer as $var => $val) {
            if ($var != "passwd") {
                $saved[] = sprintf("dsv:  %-20s: %s", $var, $val);
            } else {
                $saved[] = sprintf("dsv:  %-20s: %s", $var, "******");
            }
        }
    }

    $i = 0;
    if (isset($_SESSION)) {
        foreach ($_SESSION as $var => $val) {
            if ($i++ == 0) {
                $saved[] = "---------------------------------------";
            }
            if( is_object($val) ) {
                $saved[] = sprintf("sess:  %-20s: %s", $var, "object");
            } elseif( is_array($val) ) {
                $saved[] = sprintf("sess:  %-20s: %s", $var, "array");
            } elseif ($var != "passwd") {
                $saved[] = sprintf("sess:  %-20s: %s", $var, $val);
            } else {
                $saved[] = sprintf("sess:  %-20s: %s", $var, "******");
            }
        }
    }
    $saved[] = "</div>";
    Logger($saved );
    
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
}