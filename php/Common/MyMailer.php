<?php

function MyMailerNew() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    require 'local_mailer.php';

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
    
    return $mail;
}
