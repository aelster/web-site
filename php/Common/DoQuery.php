<?php
function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];
    $gDb = $GLOBALS['gDb'];

    if (!empty($debug))
        echo "<!-- debug: [$debug] -->\n";
        $dmsg = "&nbsp;&nbsp;&nbsp;&nbsp;DoQuery: $query";

    
    $num = 0;
    $stmt = NULL;
    
    try {
        if( $query == 'start transaction' ) {
            $gDb->beginTransaction();
            
        } elseif( $query == 'commit' ) {
            $gDb->commit();
            
        } elseif( $query == 'rollback' ) {
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

    if (!empty($debug)) {
        $dmsg .= sprintf(", # rows: %d", $num );

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
