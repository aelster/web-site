<?php

function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];
    $gDb = $GLOBALS['gDb'];

    $dmsg = "DoQuery: $query";


    $num = 0;
    $stmt = NULL;

    try {
        if ($query == 'start transaction') {
            $gDb->beginTransaction();
        } elseif ($query == 'commit') {
            $gDb->commit();
        } elseif ($query == 'rollback') {
            $gDb->rollBack();
        } else {
            $stmt = $gDb->prepare($query);
            if (func_num_args() == 1) {
                $stmt->execute();
            } else {
                $args = func_get_arg(1);
                $stmt->execute($args);
            }
            $GLOBALS['gPDO_num_rows'] = $num = $stmt->rowCount();
            $GLOBALS['gPDO_lastInsertID'] = $gDb->lastInsertID();
        }
    } catch (PDOException $e) {
        echo "<pre>";
        echo $e->getMessage();
        echo "<hr>";
        echo $e->getTraceAsString();
        echo "</pre>";
    }

    $force = 0;
    if ($force || $debug ) {
        $dmsg .= sprintf(", # rows: %d", $num);

        Logger($dmsg);
        if (!empty($args)) {
            $i = 0;
            foreach ($args as $key => $val) {
                Logger(sprintf("arg %d: %s => %s", $i++, $key, $val));
            }
        }
    }
    return $stmt;
}
