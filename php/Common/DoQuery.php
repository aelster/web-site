<?php

function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];

    if (!empty($debug))
        $dmsg = "&nbsp;&nbsp;&nbsp;&nbsp;DoQuery: $query";

    $stmt = $GLOBALS['gDb']->prepare($query);
    try {
        if (func_num_args() == 1) {
            $stmt->execute();
        } else {
            $args = func_get_arg(1);
            $stmt->execute($args);
        }
    } catch (PDOException $e) {
        echo $e->getMessage();
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
    $GLOBALS['gPDO_num_rows'] = $stmt->rowCount();

    if (!empty($debug)) {
        $dmsg .= sprintf(", # rows: %d", $GLOBALS['gPDO_num_rows']);

        Logger($dmsg);
        if( !empty($args) ) {
            $i = 0;
            foreach ($args as $key => $val) {
                printf("&nbsp;&nbsp;&nbsp;&nbsp;arg %d: %s => %s<br>", $i++, $key, $val);
            }
        }
    }
    return $stmt;
}
