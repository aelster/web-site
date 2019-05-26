<?php

function DirTree($dir, $index_required) {
    include 'SourceStuffIgnore.php';
    
    $path = '';
    $stack[] = $dir;
    $dirs = array();
    while ($stack) {
        $thisdir = array_pop($stack);
        if (!is_dir($thisdir))
            continue;

        $match = 0;
        foreach( $SourceStuff_DirsRegex as $regex ) {
            if( preg_match($regex, $thisdir ) ) {
                $match = 1;
                break;
            }
        }
        if( $match ) continue;
                
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
            
            $match = 0;
            foreach( $SourceStuff_FilesRegex as $regex ) {
                if( preg_match($regex, $file ) ) {
                    $match = 1;
                    Logger( "    Match = 1" );
                    break;
                }
            }
            if( $match ) continue;

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

    echo "<div class=center>";

    $web_base = "";
    $GLOBALS['gMasterSum'] = 0;

    $func = $_POST['func'];
    echo "<input type=button value=Back onclick=\"setValue('from', '$func');addAction('Back');\">";

    $acts = array();
    $acts[] = "setValue('area','display')";
    $acts[] = "setValue('func','source')";
    $acts[] = "addAction('Main')";
    echo sprintf("<input type=button onClick=\"%s\" value=Refresh>", join(';', $acts));


    $gMasterSum = 0;
    echo "<h2>Combined checksum:  <span id=master_sum></span></h2>";

    echo "</div>";

    $hiddenDivs = [];

    echo "<table class=sourcestuff>";
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
        if (preg_match('/tmp/', $dir))
            continue;
        SourceDisplaySub($dir, $hiddenDivs);
    }

    $str = get_include_path();
    $ps = PATH_SEPARATOR;
    $dirs = preg_split("/$ps/", $str);
    foreach ($dirs as $path) {
        if ($path == ".")
            continue;
        $tmp = DirTree($path, 0);
        sort($tmp);
        foreach ($tmp as $dir) {
            if (preg_match('/overlib/', $dir))
                continue;
            SourceDisplaySub($dir, $hiddenDivs);
        }
    }

    foreach (array("css", "scripts") as $xx) {
        $path = $_SERVER['DOCUMENT_ROOT'] . "/$xx";
        $tmp = DirTree($path, 0);
        sort($tmp);
        foreach ($tmp as $dir) {
            if (preg_match('/overlib/', $dir))
                continue;
            SourceDisplaySub($dir, $hiddenDivs);
        }
    }

    echo "</table>";
    echo "<br>";
    echo "<p style='text-align: center;'>Total files: $tot_files</p>";

    echo sprintf("<script type=\"text/javascript\">setHtml('master_sum','%s');</script>", dechex($GLOBALS['gMasterSum']));

    echo "</div>"; # sourcestuff

    foreach ($hiddenDivs as $str) {
        echo "$str\n";
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function SourceDisplaySub($dir, &$hiddenDivs) {
    include 'SourceStuffIgnore.php';
    
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    global $n, $tot_files, $already_scanned, $web_base, $local_base;

# Don't process a directory twice
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

# Walk over all the files in the directory.
# Exclude specific types/name matches
# Save an array of file types
# Keep sub-totaled MD5 by file type as well as aggregate for the directory

    $body = array();
    $dh = opendir($dir);
    $hsum = 0;
    while (false !== ( $file = readdir($dh) )) {
        $ffile = $dir . DIRECTORY_SEPARATOR . $file;
        if (!is_file($ffile))
            continue;

        $match = 0;
        foreach( $SourceStuff_FilesRegex as $regex ) {
            if( preg_match($regex, $file ) ) {
                $match = 1;
                Logger( "    Match = 1" );
                break;
            }
        }
        if( $match ) continue;
        
        if (preg_match("/^local/", $file)) {
            $name = $file;
            $type = 'local';
            $tmp = [0, 0, 0, 0, 0, 0, 0, 0];
        } else {
            $xx = preg_split('/\./', $file);
            if( count($xx) > 1 ) {
                list( $name, $type ) = $xx;
            } else {
                $name = $xx[0];
                $type = 'other';
            }
            $local = md5_file($ffile);
            $tmp = str_split($local, 4);
        }
        $ftypes[$type] = 1;
        $var = "file_$type";
        ${$var}[] = $ffile;
        $var = "sum_$type";
        if (!isset($$var))
            $$var = 0;
        $$var += hexdec($tmp[7]);
    }

    # Build the pop-up

    $hdx = count($hiddenDivs) + 1;
    $text = array();
    $text[] = "<div class='sourcestuffdetail hidden' id=hidden$hdx onmouseout=\"showHideDiv(event,$hdx);\">";
    $text[] = "<h2>$cap</h2>";
    $num_files = 0;

    ksort($ftypes);
    foreach (array_keys($ftypes) as $type) {
        $var = "file_$type";
        asort($$var);
        $sum = "sum_$type";
        $text[] = sprintf("<h3>%s files (%s)</h3>", $type, dechex($$sum));
        $text[] = "<table class=sourcestuffdetail>";
        $text[] = "<tr>";
        $text[] = "<th>#</th>";
        $text[] = "<th>Source File</th>";
        $text[] = "<th>Mod Date</th>";
        $text[] = "<th>Local MD5</th>";
        $text[] = "</tr>";

        $seen = [];

        $body = array();
        foreach ($$var as $idx => $ffile) {
            $web = preg_match('/htdocs/', $ffile) || preg_match('/wwwroot/', $ffile);

            if ($web) {
                if (empty($web_base)) {
                    $base = basename($ffile);
                    $web_base = preg_replace("/$base/", "", $ffile);
                    echo "<p style='text-align: center;'>web base: [$web_base]</p>";
                }
            } else {
                if (empty($local_base)) {
                    $dir = dirname($ffile);
                    $xx = preg_split("/\\" . DIRECTORY_SEPARATOR . "/", $dir);
                    $base = array_pop($xx);
                    $local_base = preg_replace("/$base/", "", $dir);
                    echo "<p  style='text-align: center;'>local base( $base ): [$local_base]</p>";
                }
            }

            if( preg_match( '/local_/', $ffile ) ) {
                $local = 0;
                $tmp = [0, 0, 0, 0, 0, 0, 0, 0];
            } else {
                $local = md5_file($ffile);
                $tmp = str_split($local, 4);
            }
            $mtime = filemtime($ffile);
            $line = "<td>" . basename($ffile) . "</td>";
            $line .= "<td class=normc>" . date("Y-M-j H:i", $mtime) . "</td>";
            $line .= "<td class=md5>" . join(" ", $tmp) . "</td>";
            $hsum += hexdec($tmp[7]);
            $body[$ffile] = $line;
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
        $text[] = "<br>";
    }
    $text[] = "</div>";

    $hiddenDivs[] = join('', $text);

#    if ($num_files) {
    $n++;

    echo "<tr>";
    echo "<td>$n</td>";
    echo "<td>$dir</td>";
    echo "<td>" . SourceCleanPath($dir) . "</td>";
    echo "<td  style='text-align: center;'>$num_files</td>";
    $tag = dechex($hsum);
    $jsx = [];
    $jsx[] = "onmouseenter=\"showHideDiv(event,$hdx)\"";
    $jsx[] = "onmouseout=\"showHideDiv(event,$hdx)\"";
    $js = join(';', $jsx);
    echo "<td style='text-align: center;' $js><a href='#top'>$tag</a></td>";
    echo "</tr>";

    closedir($dh);

    $GLOBALS['gMasterSum'] += $hsum;
    #   }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}
