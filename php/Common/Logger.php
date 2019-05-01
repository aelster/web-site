<?php

function Logger() {
    $gDebug = $GLOBALS['gDebug'];
    $gDebugInLine = $GLOBALS['gDebugInLine'];
    $gDebugErrorLog = $GLOBALS['gDebugErrorLog'];
    $gDebugWindow = $GLOBALS['gDebugWindow'];
    $gDebugHTML = $GLOBALS['gDebugHTML'];

    if ($gDebug & $gDebugInLine) {
        if (func_num_args() > 0) {
            $str = func_get_arg(0);
        } else {
            $str = join('>', $GLOBALS['gFunction']);
        }
        echo "<div align=left>$str<br></div>";
    }

    if ($gDebug & $gDebugErrorLog) {
        if (func_num_args() > 0) {
            error_log(func_get_arg(0));
        } else {
            error_log(join('>', $GLOBALS['gFunction']));
        }
    }

    if ($gDebug & $gDebugWindow) {
        if (func_num_args() > 0) {
            $str = func_get_arg(0);
        } else {
            $str = join('>', $GLOBALS['gFunction']);
        }
        echo "<script type='text/javascript'>\n";
        echo " debug('$str');";
        echo "</script>";
    }

    if ($gDebug & $gDebugHTML) {
        if (func_num_args() > 0) {
            $str = func_get_arg(0);
        } else {
            $str = join('>', $GLOBALS['gFunction']);
        }
        echo "<!--\n";
        echo "$str\n";
        echo "-->\n";
    }
}

?>
