<?php

function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];
    
    $num = 0;
    $stmt = NULL;
    $db = $GLOBALS['gDb'];
    $tmp = [];
    $numArgs = func_num_args();
    $args = ( $numArgs > 1 ) ? func_get_arg(1) : "";

    $force = 0;
    if ($force || $debug ) {
        $tmp[] = "DoQuery: [$query]";

        if (!empty($args)) {
            $i = 0;
            foreach ($args as $key => $val) {
                $v2 = ( strlen($val) < 100 ) ? $val : "blob";
                $tmp[] = sprintf("arg %d: %s => %s", $i++, $key, $v2);
            }
        }
    }
    
    try {
        if (preg_match("/start transaction/i", $query )) {
            $db->beginTransaction();
        } elseif (preg_match("/commit/i", $query )) {
            $db->commit();
        } elseif (preg_match("/rollback/i", $query )) {
            $db->rollBack();
        } else {
            $stmt = $db->prepare($query);
            if($numArgs == 1) {
                $stmt->execute();
            } else {
                $stmt->execute($args);
            }
            $GLOBALS['gPDO_num_rows'] = $num = $stmt->rowCount();
            $GLOBALS['gPDO_lastInsertID'] = $db->lastInsertID();
            $tmp[] = sprintf("# rows: %d", $num);
        }

    } catch (PDOException $e) {
        echo "<pre>";
        echo "*** Error ***\n";
        print_r($tmp);
        echo $e->getMessage();
        echo "\n";
        echo $e->getTraceAsString();
        echo "</pre>";
    }

    $force = 0;
    if ($force || $debug ) {
        Logger($tmp);
    }
    return $stmt;
}