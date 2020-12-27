<?php

function BackupMySql() {
    // Make sure the required globals exist
    $required = array('gPDO', 'gSiteDir', 'gId', 'gMailBackup');
    foreach ($required as $key) {
        if (!array_key_exists($key, $GLOBALS)) {
            echo "** Global variable [$key] is undefined **";
            exit();
        } else if (empty($GLOBALS[$key])) {
            echo "** Global variable [$key] is empty **";
            exit();
        } else {
            $$key = $GLOBALS[$key];
        }
    }

    // Make sure that the site tmp directory exits
    if (!file_exists($gSiteDir . "/tmp")) {
        $cmd = "mkdir $gSiteDir/tmp";
        exec($cmd, $output, $retval);
        if ($retval) {
            error_log(print_r($output, true));
        }
    }

    $dstr = date("Ymd");

    $file = "$gSiteDir/tmp/{$gId}_backup_$dstr.sh";
    $fh = fopen($file, "w");
    fputs($fh, "#!/bin/bash\n");
    fputs($fh, "cd $gSiteDir/tmp\n");

    $tars = [];
    $files_to_delete = [];

    $files_to_delete[] = $file;

    foreach ($gPDO as $obj) {
        $sql_file = "{$obj['dbname']}_$dstr.sql";
        $bck_file = $sql_file . ".bz2";

        fputs($fh, "mysqldump -u {$obj['user']} -p'{$obj['pass']}' {$obj['dbname']} > $sql_file\n");
        fputs($fh, "tar -cjf $bck_file $sql_file\n");

        $tars[] = "-a $bck_file";
        $files_to_delete[] = $sql_file;
        $files_to_delete[] = $bck_file;
    }

    $email = $gMailBackup[0]['email'];
    fputs($fh, "echo | mailx " . implode(' ', $tars) . " -s \"IHDS " . ucfirst($gId) . " Backups\" " . $email . "\n");
    fputs($fh, "rm " . implode(" ", $files_to_delete) . "\n");
    fclose($fh);
    chmod($file, 0700);
    exec($file, $output, $retval);
    if ($retval) {
        error_log(print_r($output, true));
    }
}
