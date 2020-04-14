<?php

$path = dirname(__FILE__) . 'php/Common/globals.php';
if (file_exists($path)) {
    require $path;
}

function SiteLoad($library) {
    global $gSiteLoadLibraries;

    $pathArray = explode(PATH_SEPARATOR, get_include_path());

    $found = 0;
    $i = 0;
    while ($i < count($pathArray) && !$found) {
        $path = $pathArray[$i++] . DIRECTORY_SEPARATOR . $library;
        if (file_exists($path)) {
            $found = 1;
        }
    }

    if ($found == 0) {
        printf("Can't find library %s using path %s", $library, get_include_path());
        return;
    }

    $d = dir($path);
    while (false !== ( $file = $d->read())) {
        $tmp = preg_split("/\./", $file);
        $ext = array_pop($tmp);
        if ($ext == "php") {
            $name = join('.', $tmp);
            if (preg_match("/^local_/", $name))
                continue;
            $str = $path . DIRECTORY_SEPARATOR . $file;
            require_once( $str );
            if (preg_match("/_Init$/", $name)) {
                call_user_func($name);
            }
        }
    }

    $gSiteLoadLibraries[] = $library;
}
