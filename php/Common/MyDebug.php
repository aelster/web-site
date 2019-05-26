<?php

function MyDebug() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $gUserId = $GLOBALS['gUserId'];
    $gFunc = $GLOBALS['gFunc'];
    $gDebug = $GLOBALS['gDebug'];
    $gDebugAll = $GLOBALS['gDebugAll'];
    $gDebugInLine = $GLOBALS['gDebugInLine'];
    $gDebugErrorLog = $GLOBALS['gDebugErrorLog'];
    $gDebugWindow = $GLOBALS['gDebugWindow'];
    $gDebugHTML = $GLOBALS['gDebugHTML'];
    
    if( $gFunc == 'display' ) {
        echo "<div class=center>";
        echo "<h2>Debug Control</h2>";
        
        echo "<input type=button value=Back onclick=\"setValue('from', 'MyDebug');addAction('Back');\">";
        
/*        $tag = MakeTag('update');
        $jsx = array();
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo "<input type=button value=Update $tag $js>";
*/
        echo "<br><br>";
        
        echo "</div>";
        
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
        if( $gDebug == 0 ) {
            $state = "All Off";
            $options[] = [ 'label' => '-- select --', 'value' => -1 ];
            $options[] = [ 'label' => 'All On', 'value' => $gDebugAll ];
            $class = 'dbg-off';
        } elseif ($gDebug == $GLOBALS['gDebugAll'] ) {
            $state = "All On";
            $options[] = [ 'label' => '-- select --', 'value' => -1 ];
            $options[] = [ 'label' => 'All Off', 'value' => 0 ];
            $class = 'dbg-on';
        } else {
            $state = "Part On";                        
            $options[] = [ 'label' => '-- select --', 'value' => -1 ];
            $options[] = [ 'label' => 'All On', 'value' => $gDebugAll ];
            $options[] = [ 'label' => 'All Off', 'value' => 0 ];
            $class = 'dbg-part';
        }
        echo "<td class='$class'>$state</td>";
        
        $jsx = array();
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "addField('$fld')";
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        echo "<td>";
        echo "<select $tag $js>";
        foreach( $options as $row ) {
            printf( "<option value=%d>%s</option>", $row['value'], $row['label']);
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
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        $enabled = ($gDebug & $gDebugInLine) ? 1 : 0;
        if( $enabled ) {
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
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        
        $enabled = ($gDebug & $gDebugErrorLog) ? 1 : 0;

        if( $enabled ) {
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
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        
        $enabled = ($gDebug & $gDebugWindow) ? 1 : 0;
        if( $enabled ) {
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
        $jsx[] = "setValue('area','debug')";
        $jsx[] = "setValue('from','MyDebug')";
        $jsx[] = "setValue('func','update')";
        $jsx[] = "addAction('Update')";
        $js = sprintf("onChange=\"%s\"", join(';', $jsx));
        $tag = MakeTag("$fld");
        
        $enabled = ($gDebug & $gDebugHTML) ? 1 : 0;
        if( $enabled ) {
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
        
    } elseif( $gFunc == 'update' ) {
        $tmp2 = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
        $tmp = array_unique($tmp2);
        $debug = $gDebug;
        foreach( $tmp as $fld ) {
            $var = "g$fld";
            if( $fld == 'DebugAll' ) {
                $debug = $_POST['DebugAll'];
            } else {
                if( $_POST[$fld] == 0 ) {
                    $debug -= $$var;
                    if( $fld == 'DebugWindow' ) {
                        echo "<script type='text/javascript'>closeDebugWindow();</script>";
                    }
                } elseif( $_POST[$fld] == 1 ) {
                    $debug += $$var;
                }
            }
        }
        $GLOBALS['gDb'] = $GLOBALS['gDbVector'][0];  

        $query = "update users set debug = :v1 where userid = :v2";        
        DoQuery( $query, [':v1' => $debug, ':v2' => $gUserId ] );
        
        $GLOBALS['gDb'] = $GLOBALS['gDbVector'][$_SESSION['dbId']];

        $GLOBALS['gDebug'] = $debug;
    }
}