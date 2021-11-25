<?php

function EventLog() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $area = func_get_arg(0);

    switch ($area) {
        case 'confirm':
            EventLogConfirm();
            break;

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

function EventLogConfirm() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    echo "<div class=danger>";
    echo "This erases ALL event history!";
    echo "<br><br>";
    $jsx = [];
    $jsx[] = sprintf("setValue('mode','%s')", $_POST['mode']);
    $jsx[] = sprintf("setValue('area','%s')", $_POST['area']);
    $jsx[] = sprintf("setValue('key','%s')", $newKey);
    $jsx[] = "setValue('from','events')";
    $jsx[] = "setValue('func','init')";
    $jsx[] = "myConfirm('events','Are you really sure you want to proceed!')";
    echo sprintf("<input type=button onClick=\"%s\" value=Proceed>", implode(';', $jsx));
    echo "</div>";

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function EventLogDisplay() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $GLOBALS['gDb'] = $GLOBALS['gPDO'][$GLOBALS['gDbControlId']]['inst'];

    $userNames = [];
    $query = "select id, username from users";
    $stmt = DoQuery($query);
    while (list( $id, $name ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $userNames[$id] = $name;
    }

    $GLOBALS['gDb'] = $GLOBALS['gPDO'][$GLOBALS['gDbDataId']]['inst'];

    $dates = [];
    $query = "select * from event_log order by time desc";

    $stmt = DoQuery($query);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        list($day, $time) = explode(' ', $row["time"]);
        if (!array_key_exists($day, $dates)) {
            $dates[$day] = [];
        }
        $dates[$day][] = $row;
    }

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

    $tmp = array_keys($dates);
    foreach ($tmp as $day) {
        $col = 0;
        echo "<tr>";

        $entries = [];
        foreach ($dates[$day] as $entry) {
            $entries[$entry['time']] = $entry;
        }

        $height = count($entries);

        $col++;
        echo "<td class='col$col c' rowspan=$height>$day<br>($height)</td>";


        $tmp = array_reverse($entries);
        foreach ($tmp as $entry) {
            $col++;
            echo "<td class='col$col nw'>" . $entry['time'] . "</td>";
            $col++;
            $name = array_key_exists($entry['user_id'], $userNames) ? $userNames[$entry['user_id']] : "unk";
            echo "<td class='col$col c'>$name</td>";
            $col++;
            echo "<td class=col$col>" . $entry['item'] . "</td>";
            echo "</tr>";
            if ($height > 1) {
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
    $acts[] = "myConfirm('events','Are you sure you want to initialize the Event Log?')";
    $js = join(';', $acts);

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function EventLogInit() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    DoQuery("truncate event_log");
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function EventLogRecord($obj) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    DoQuery("insert into event_log set time=now(), type=:v2, user_id=:v3, item=:v4", [
        ':v2' => $obj['type'],
        ':v3' => $obj['user_id'],
        ':v4' => $obj['item']
    ]);

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}
