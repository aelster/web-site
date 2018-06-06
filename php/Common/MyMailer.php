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

function MyMailerSend($mail) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    try {
        if (!$mail->send()) {
            $err = 'Message could not be sent.';
            $err .= 'Mailer Error: ' . $mail->ErrorInfo;
            echo $err;
        }
    } catch (phpmailerException $e) {
        echo $e->errorMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}