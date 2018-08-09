<?php

function CloseDb() {
    global $gFunction;
    global $gTrace;

    if ($trace) {
        $gFunction[] = __FUNCTION__;
        Logger();
    }
    $GLOBALS['gDb'] = NULL;

    if ($gTrace)
        array_pop($gFunction);
}