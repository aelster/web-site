<?php

function DirTree($dir, $index_required) {
    $path = '';
    $stack[] = $dir;
    $dirs = array();
    while ($stack) {
        $thisdir = array_pop($stack);
        if (!is_dir($thisdir))
            continue;
        $tmp = scandir($thisdir);
        if ($index_required) {
            if (preg_grep('/index.htm/', $tmp) || preg_grep('/index.php/', $tmp)) {
                $dirs[] = $thisdir;
            }
        } else {
            $dirs[] = $thisdir;
        }
        foreach ($tmp as $file) {
            if ($file == "." || $file == "..")
                continue;
            if (preg_match('/^\./', $file))
                continue;
            if (preg_match('/^_/', $file))
                continue;
            if (preg_match('/^phpMyAdmin/', $file))
                continue;
            if (preg_match('/^andyMyAdmin/', $file))
                continue;
            if (preg_match('/^phpmanual/', $file))
                continue;
            if (preg_match('/images/', $file))
                continue;
            if (preg_match('/^wp-/i', $file))
                continue;
            if (preg_match('/^fpdf/', $file))
                continue;
            if (preg_match('/nbproject/', $file))
                continue;

            $t = $thisdir . DIRECTORY_SEPARATOR . $file;
            if (preg_match('/site$/', $file))
                $dirs[] = $t;
            if (is_dir($t)) {
                $stack[] = $t;
            }
        }
    }
    return $dirs;
}

function SourceCleanPath($path) {
    global $web_base, $local_base, $once;

    $vpath = preg_split('/\\' . DIRECTORY_SEPARATOR . '/', $path);
    $tpath = preg_split('/\\' . DIRECTORY_SEPARATOR . '/', $web_base);

    $hit = 0;
    $done = 0;
    while (count($vpath) && count($tpath) && !$done) {
        if (empty($vpath[0]) && empty($tpath[0])) {
            array_shift($vpath);
            array_shift($tpath);
            continue;
        }
        if ($vpath[0] == $tpath[0]) {
            $hit = 1;
            array_shift($vpath);
            array_shift($tpath);
        } else {
            $done = 1;
        }
    }
    if ($hit) {
        return join(DIRECTORY_SEPARATOR, $vpath);
    }

    $vpath = preg_split('/\\' . DIRECTORY_SEPARATOR . '/', $path);
    $tpath = preg_split('/\\' . DIRECTORY_SEPARATOR . '/', $local_base);

    $hit = 0;
    $done = 0;
    while (count($vpath) && count($tpath) && !$done) {
        if (empty($vpath[0]) && empty($tpath[0])) {
            array_shift($vpath);
            array_shift($tpath);
            continue;
        }
        if ($vpath[0] == $tpath[0]) {
            $hit = 1;
            array_shift($vpath);
            array_shift($tpath);
        } else {
            $done = 1;
        }
    }
    return join(DIRECTORY_SEPARATOR, $vpath);
}

function SourceDisplay() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    global $n, $tot_files, $already_scanned, $web_base, $local_base;

    $web_base = "";
    $GLOBALS['gMasterSum'] = 0;
    ?>
    <div class=source>
        <?php
        $func = $_POST['func'];
        echo "<div class=CommonV2>";
        echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";

        $acts = array();
        $acts[] = "setValue('area','display')";
        $acts[] = "setValue('func','source')";
        $acts[] = "addAction('Main')";
        echo sprintf("<input type=button onClick=\"%s\" value=Refresh>", join(';', $acts));

        $gMasterSum = 0;
        echo "<h2>Combined checksum:  <span id=master_sum></span></h2>";

        echo "<table>";
        echo "<tr>";
        echo "  <th>#</th>";
        echo "  <th>Path</th>";
        echo "  <th>ComparePath</th>";
        echo "  <th># Files</th>";
        echo "  <th>Aggregate MD5</th>";
        echo "<tr>";

        $n = $tot_files = 0;
        $already_scanned = array();

        $path = getcwd();
        $tmp = DirTree($path, 0);
        sort($tmp);
        foreach ($tmp as $dir) {
            if (preg_match('/Templates/', $dir))
                continue;
            SourceDisplaySub($dir);
        }

        $str = get_include_path();
        $ps = PATH_SEPARATOR;
        $dirs = preg_split("/$ps/", $str);
        foreach ($dirs as $path) {
            if ($path == ".")
                continue;
            if (preg_match('/swift/i', $path))
                continue;
            if (preg_match('/fpdf/i', $path))
                continue;
            if (preg_match('/usr\/lib/', $path))
                continue;
            if (preg_match('/usr\/local\/lib/', $path))
                continue;
            $tmp = DirTree($path, 0);
            sort($tmp);
            foreach ($tmp as $dir) {
                if (preg_match('/overlib/', $dir))
                    continue;
                SourceDisplaySub($dir);
            }
        }

        $path = '/usr/local/site';
        $tmp = DirTree($path, 0);
        sort($tmp);
        foreach ($tmp as $dir) {
            if (preg_match('/overlib/', $dir))
                continue;
            SourceDisplaySub($dir);
        }

        foreach (array("css", "scripts") as $xx) {
            $path = "/home/cbi18/public_html/$xx";
            $tmp = DirTree($path, 0);
            sort($tmp);
            foreach ($tmp as $dir) {
                if (preg_match('/overlib/', $dir))
                    continue;
                SourceDisplaySub($dir);
            }
        }

        echo "</table>";
        echo "<br>";
        echo "Total files: $tot_files";

        echo sprintf("<script type=\"text/javascript\">setHtml('master_sum','%s');</script>", dechex($GLOBALS['gMasterSum']));
        if ($GLOBALS['gTrace'])
            array_pop($GLOBALS['gFunction']);
    }

    function SourceDisplaySub($dir) {
        if ($GLOBALS['gTrace']) {
            $GLOBALS['gFunction'][] = __FUNCTION__;
            Logger();
        }
        global $n, $tot_files, $already_scanned, $web_base, $local_base;

        if (!empty($already_scanned[$dir])) {
            if ($GLOBALS['gTrace'])
                array_pop($GLOBALS['gFunction']);
            return;
        }
        $already_scanned[$dir] = 1;

        $cap = "Path: $dir";

        if (isset($ftypes)) {
            foreach ($ftypes as $type) {
                $var = "file_$type";
                if (isset($$var)) {
                    unset($$var);
                }
            }
        }
        $ftypes = array();

        $body = array();
        $dh = opendir($dir);
        $hsum = 0;
        while (false !== ( $file = readdir($dh) )) {
            $ffile = $dir . DIRECTORY_SEPARATOR . $file;
            if (!is_file($ffile))
                continue;
            if (preg_match("/^\./", $file))
                continue;
            if (preg_match("/\.dbg$/", $file))
                continue;
            if (preg_match("/\.ini$/", $file))
                continue;
            if (preg_match("/\.*z$/", $file))
                continue;
            if (preg_match("/kpf$/", $file))
                continue;
            if (preg_match("/ste$/", $file))
                continue;
            if (preg_match("/^_/", $file))
                continue;
            if (preg_match("/komodoproject$/", $file))
                continue;
            if (preg_match("/swp$/", $file))
                continue;
            if (preg_match("/svn-commit/", $file))
                continue;
            if (preg_match("/error_log/", $file))
                continue;
            if (preg_match("/favicon.png/", $file))
                continue;
            if (preg_match("/andyMyAdmin/", $file))
                continue;
            if (preg_match("/.sql$/", $file))
                continue;
            if (preg_match("/^local/", $file))
                continue;
            if (preg_match("/^.publish/", $file))
                continue;
            if (preg_match("/~$/", $file))
                continue;
            list( $name, $type ) = preg_split('/\./', $file);
            $ftypes[$type] = 1;
            $var = "file_$type";
            ${$var}[] = $ffile;
            $local = md5_file($ffile);
            $tmp = str_split($local, 4);
            $var = "sum_$type";
            if (!isset($$var))
                $$var = 0;
            $$var += hexdec($tmp[7]);
        }

        $text = array();
        $text[] = "<div class=source>";
        $num_files = 0;

        ksort($ftypes);
        foreach (array_keys($ftypes) as $type) {
            $var = "file_$type";
            asort($$var);
            $sum = "sum_$type";
            $text[] = sprintf("<h3>%s files (%s)</h3>", $type, dechex($$sum));
            $text[] = "<table>";
            $text[] = "<tr>";
            $text[] = "<th>#</th>";
            $text[] = "<th>Source File</th>";
            $text[] = "<th>Mod Date</th>";
            $text[] = "<th>Local MD5</th>";
            $text[] = "</tr>";

            $body = array();
            foreach ($$var as $idx => $ffile) {
                $web = preg_match('/htdocs/', $ffile) || preg_match('/wwwroot/', $ffile);
                if ($web) {
                    if (empty($web_base)) {
                        $base = basename($ffile);
                        $web_base = preg_replace("/$base/", "", $ffile);
                        echo "web base: [$web_base]<br>";
                    }
                } else {
                    if (empty($local_base)) {
                        $dir = dirname($ffile);
                        $xx = preg_split("/\\" . DIRECTORY_SEPARATOR . "/", $dir);
                        $base = array_pop($xx);
                        $local_base = preg_replace("/$base/", "", $dir);
                        echo "local base( $base ): [$local_base]<br>";
                    }
                }
                $local = md5_file($ffile);
                $mtime = filemtime($ffile);
                $line = "<td>" . basename($ffile) . "</td>";
                $line .= "<td class=normc>" . date("Y-M-j H:i", $mtime) . "</td>";
                $tmp = str_split($local, 4);
                $line .= "<td class=md5>" . join(" ", $tmp) . "</td>";
                $hsum += hexdec($tmp[7]);
                $body[$ffile . $mtime] = $line;
#            $body[ $ffile ] = $line;
                $num_files++;
                $tot_files++;
            }
            ksort($body);
            $i = 0;
            foreach ($body as $line) {
                $i++;
                $text[] = sprintf("<tr><td>%d</td>%s</tr>", $i, $line);
            }
            $text[] = "</table>";
        }
        $text[] = "</div>";

        if ($num_files) {
            $n++;

            echo "<tr>";
            echo "<td>$n</td>";
            echo "<td>$dir</td>";
            echo "<td>" . SourceCleanPath($dir) . "</td>";
            echo "<td class=c>$num_files</td>";
            $tag = dechex($hsum);
            $str = CVT_Str_to_Overlib(join('', $text));
            echo "<td class=c>";
            ?><a href="javascript:void(0);"
               onmouseover="return overlib('<?php echo $str ?>', WIDTH, 900, STICKY, CAPTION, '<?php echo $cap ?>')"
               onmouseout="return nd();"><?php echo $tag ?></a>
        <?php
        echo "</td>";
        echo "</tr>";

        closedir($dh);

        $GLOBALS['gMasterSum'] += $hsum;
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}
?>