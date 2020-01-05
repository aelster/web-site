<?php

function EventLog() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $area = func_get_arg(0);

    switch ($area) {
        case 'display':
            EventLogDisplay();
            break;

        case 'init':
            EventLogInit();
            break;

        case( 'record' ):
            $obj = func_get_arg(1);
            EventLogRecord($obj);
            break;

        default:
            echo "Uh-oh:  Contact Andy regarding EventLog( $area )<br>";
            break;
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function EventLogDisplay() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $gDreamweaver = array_key_exists('gDreamweaver',$GLOBALS) ? $GLOBALS['gDreamweaver'] : 0;

    $dates = [];
    $query = "select * from event_log order by time asc";
    
    $stmt = DoQuery( $query );

    while( $row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        list($day, $time)=explode(' ', $row["time"]);
        $dates[$day][] = $row;
    }
    
//    $GLOBALS['gDb'] = $GLOBALS['gDatabases'][$_SESSION['dbId']];
  
    echo "<div class=center>";
    echo "<h2>Event Log</h2>";
    echo "<input type=submit name=action value=Back>";
    if (!$gDreamweaver) {
        $acts = array();
        $acts[] = "setValue('from','" . __FUNCTION__ . "')";
        $acts[] = "setValue('func','init')";
        $acts[] = "myConfirm('Are you sure you want to initialize the Event Log?')";
        echo sprintf("<input type=button onClick=\"%s\" id=update value=\"Initialize\">", join(';', $acts));
    }
    echo "</div>";

    $tmp = array_reverse( array_keys( $dates ) );
    echo "<table class='scrollable sortable'>";
    
    echo "<thead>";
    echo "<tr>";
    $col = 0;
    $col++;
    echo "<th class=col$col>Date</th>";
    $col++;
    echo "<th class=col$col>Time</th>";
    $col++;
    echo "<th class=col$col>User</th>";
    $col++;
    echo "<th class=col$col>Action</th>";
    echo "</tr>";
    echo "</thead>";
    
    echo "<tbody>";
    
    foreach( $tmp as $day ) {
        $col = 0;
        echo "<tr>";
        $height = count($dates[$day] );
        
        $col++;
        echo "<td class='col$col c' rowspan=$height>$day<br>($height)</td>";
        
        $entries = [];
        foreach( $dates[$day] as $entry ) {
            $entries[$entry['time']] = $entry;
        }
        
        $tmp = array_reverse($entries);
        foreach( $tmp as $entry ) {
            $col++;
            echo "<td class=col$col>" . $entry['time'] . "</td>";
            $col++;
            $name = array_key_exists( $entry['userid'], $GLOBALS['gUsers'] ) ? $GLOBALS['gUsers'][$entry['userid']] : "unk";
            echo "<td class='col$col c'>$name</td>";
            $col++;
            echo "<td class=col$col>" . $entry['item'] . "</td>";
            echo "</tr>";
            if( $height > 1 ) {
                echo "<tr>";
                $col = 1;
            }          
        }
        
    }
    echo "</tbody>";
    echo "</table>";
    $acts = array();
    $acts[] = "setValue('from','" . __FUNCTION__ . "')";
    $acts[] = "setValue('func','init')";
    $acts[] = "myConfirm('Are you sure you want to initialize the Event Log?')";
    $js = join(';',$acts);

    if( $gDreamweaver == 23 ) {
?>
<script type="text/javascript">
    var btn = document.createElement('button');
    btn.setAttribute('class','sidebar-btn');
    btn.innerText = 'Initialize?';
    btn.onClick = "<?php echo $js ?>";
    document.getElementById("sidebar").appendChild(btn);
</script>
<?php
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);    
}

function EventLogInit() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    DoQuery( "truncate event_log");
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);    
}

function EventLogRecord( $obj ) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    
    DoQuery("insert into event_log set time=now(), type=:v2, userid=:v3, item=:v4", [
            ':v2' => $obj['type'],
            ':v3' => $obj['userid'],
            ':v4' => $obj['item']
        ]);

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);    
}