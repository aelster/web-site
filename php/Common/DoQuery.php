<?php

function DoQuery($query) {
    $debug = $GLOBALS['gDebug'];

    if ($debug)
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
    /*
      if (mysql_errno($db) != 0) {
      if (!$db) {
      echo "  query: $query<br>\n";
      }
      echo "  result: " . mysql_error($db) . "<br>\n";
      echo "I'm sorry but something unexpected occurred.  Please send all details<br>";
      echo "of what you were doing and any error messages to $support<br>";
      } else {
      if (preg_match("/^select/i", $query)) {
      $numrows = mysql_num_rows($result);
      } elseif (preg_match("/^insert/i", $query)) {
      $numrows = mysql_affected_rows($db);
      $last_id = mysql_insert_id();
      } else {
      $numrows = mysql_affected_rows($db);
      }
     * 
     */
    if ($debug)
        $dmsg .= sprintf(", # rows: %d", $GLOBALS['gPDO_num_rows']);


    if ($debug)
        Logger($dmsg);
    
    return $stmt;
}
