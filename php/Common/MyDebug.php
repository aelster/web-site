<?php

function MyDebug() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $userId = $GLOBALS['gUserId'];
    $func = $GLOBALS['gFunc'];
    $debug = $GLOBALS['gDebug'];
    
    $gDebugInLine = $GLOBALS['gDebugInLine'];
    $gDebugErrorLog = $GLOBALS['gDebugErrorLog'];
    $gDebugWindow = $GLOBALS['gDebugWindow'];
    $gDebugHTML = $GLOBALS['gDebugHTML'];
    $gDebugAll = $GLOBALS['gDebugAll'];

    $dreamweaver = array_key_exists('gDreamweaver',$GLOBALS) ? $GLOBALS['gDreamweaver'] : 0;
    
    $save_db = $GLOBALS['gDb'];
    $GLOBALS['gDb'] = $GLOBALS['gDbAll'][$GLOBALS['gDbControlId']];
        
    if ($func == 'display') {
        if ( ! $dreamweaver) {
            echo "<div class=center>";
            echo "<h2>Debug Control</h2>";

            echo "<input type=button value=Back onclick=\"setValue('from', 'MyDebug');addAction('Back');\">";
            echo "<br><br>";

            echo "</div>";
        }
        /*        $tag = MakeTag('update');
          $jsx = array();
          $jsx[] = "setValue('area','debug')";
          $jsx[] = "setValue('from','MyDebug')";
          $jsx[] = "setValue('func','update')";
          $jsx[] = "addAction('Update')";
          $js = sprintf("onClick=\"%s\"", join(';', $jsx));
          echo "<input type=button value=Update $tag $js>";
         */
        echo "<table class=debug>";
        echo "<tr>";
        echo "  <th>Option</th>";
        echo "  <th>Current</th>";
        echo "  <th>Action</th>";
        echo "</tr>";
#==================================================================
        $fld = 'DebugAll';
        echo "<tr>";
        echo "  <td>All Debug</td>";

        $options = array();
        if ($debug == 0) {
            $state = "All Off";
            $options[] = ['label' => '-- select --', 'value' => -1];
            $options[] = ['label' => 'All On', 'value' => $gDebugAll];
            $class = 'dbg-off';
        } elseif ($debug == $gDebugAll) {
            $state = "All On";
            $options[] = ['label' => '-- select --', 'value' => -1];
            $options[] = ['label' => 'All Off', 'value' => 0];
            $class = 'dbg-on';
        } else {
            $state = "Part On";
            $options[] = ['label' => '-- select --', 'value' => -1];
            $options[] = ['label' => 'All On', 'value' => $gDebugAll];
            $options[] = ['label' => 'All Off', 'value' => 0];
            $class = 'dbg-part';
        }
        echo "<td class='$class'>$state</td>";

        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('mode','control')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        echo "<td>";
        echo "<select $tag $js>";
        foreach ($options as $row) {
            printf("<option value=%d>%s</option>", $row['value'], $row['label']);
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";
#==================================================================
        $fld = 'DebugInLine';

        echo "<tr>";
        echo "  <td>In Line</td>";

        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('mode','control')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        $enabled = ($debug & $gDebugInLine) ? 1 : 0;
        if ($enabled) {
            echo "<td class=dbg-on>On</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=0>Turn Off</option>";
        } else {
            echo "<td class=dbg-off>Off</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=1>Turn On</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

#==================================================================
        $fld = 'DebugErrorLog';

        echo "<tr>";
        echo "  <td>Error Log</td>";

        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('mode','control')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");

        $enabled = ($debug & $gDebugErrorLog) ? 1 : 0;

        if ($enabled) {
            echo "<td class=dbg-on>On</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=0>Turn Off</option>";
        } else {
            echo "<td class=dbg-off>Off</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=1>Turn On</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

#==================================================================
        $fld = 'DebugWindow';

        echo "<tr>";
        echo "  <td>Window</td>";

        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('mode','control')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");

        $enabled = ($debug & $gDebugWindow) ? 1 : 0;
        if ($enabled) {
            echo "<td class=dbg-on>On</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=0>Turn Off</option>";
        } else {
            echo "<td class=dbg-off>Off</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=1>Turn On</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

#==================================================================
        $fld = 'DebugHTML';

        echo "<tr>";
        echo "  <td>HTML</td>";

        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('mode','control')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','" . __FUNCTION__ . "')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");

        $enabled = ($debug & $gDebugHTML) ? 1 : 0;
        if ($enabled) {
            echo "<td class=dbg-on>On</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=0>Turn Off</option>";
        } else {
            echo "<td class=dbg-off>Off</td>";
            echo "<td><select $tag $js>";
            echo "<option value=-1>-- select --</option>";
            echo "<option value=1>Turn On</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
    } elseif ($func == 'update') {
        $tmp2 = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
        $tmp = array_unique($tmp2);
        foreach ($tmp as $fld) {
            $var = "g$fld";
            if ($fld == 'DebugAll') {
                $debug = $_POST['DebugAll'];
            } else {
                if ($_POST[$fld] == 0) {
                    if( ($debug & $$var ) > 0 ) {
                        $debug -= $$var;
                        if ($fld == 'DebugWindow') {
                            echo "<script type='text/javascript'>closeDebugWindow();</script>";
                        }
                    }
                } elseif ($_POST[$fld] == 1) {
                    $debug = $debug | $$var;
                }
            }
            error_log( "** var: [$var], debug: [$debug] **" );
        }

        $query = "update users set debug = :v1 where userid = :v2";
        DoQuery($query, [':v1' => $debug, ':v2' => $userId]);

        $GLOBALS['gDb'] = $save_db;

        $GLOBALS['gDebug'] = $debug;
    }
}
