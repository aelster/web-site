<?php

function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];
    
    $num = 0;
    $stmt = NULL;
    $db = $GLOBALS['gDb'];
    
    try {
        if (preg_match("/start transaction/i", $query )) {
            $db->beginTransaction();
        } elseif (preg_match("/commit/i", $query )) {
            $db->commit();
        } elseif (preg_match("/rollback/i", $query )) {
            $db->rollBack();
        } else {
            $stmt = $db->prepare($query);
            if (func_num_args() == 1) {
                $stmt->execute();
            } else {
                $args = func_get_arg(1);
                $stmt->execute($args);
            }
            $GLOBALS['gPDO_num_rows'] = $num = $stmt->rowCount();
            $GLOBALS['gPDO_lastInsertID'] = $db->lastInsertID();
        }

    } catch (PDOException $e) {
        echo "<pre>";
        echo "Query: [$query]\n";
        echo $e->getMessage();
        echo "\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    }

    $force = 0;
    if ($force || $debug ) {
        $dmsg = "DoQuery: [$query]" . sprintf(", # rows: %d", $num);

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