<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function MyMailerNew() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $mail = new PHPMailer(true);

    foreach( $GLOBALS['gMailServer'] as $key => $val ) {
        switch ($key) {
            case 'Label': // noop, display purposes only
                break;
            
            case 'Host':
                $mail->Host = $val;
                break;
            
            case 'SMTPAuth':
                $mail->SMTPAuth = $val;
                break;
            
            case 'Username':
                $mail->Username = $val;
                break;
            
            case 'Password':
                $mail->Password = $val;
                break;
            
            case 'isSMTP':
                if( $val ) {
                    $mail->isSMTP();
                }
                break;
                
            case 'isHTML':
                if( $val ) {
                    $mail->isHTML();
                }
                break;
                
            case 'debug':
                if( $val ) {
                    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                }
                break;
            
            case 'protocol':
                if( $val == "tls" ) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                } elseif( $val == "ssl" ) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;                    
                }
                break;
                
            default:
                $str = "Invalid Mail Server option [$key]. Aborting ...";
                error_log($str);
                Logger($str);
                echo "$str<br>";
                exit;
        }                
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);

    return $mail;
}

function MyMailerSend($mail) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $status = 0;
    try {
        if (!$mail->send()) {
            $err = 'Message could not be sent.';
            $err .= 'Mailer Error: ' . $mail->ErrorInfo;
            echo $err;
        } else {
            $status = 1;
        }
    } catch (phpmailerException $e) {
        echo $e->errorMessage();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $status;
}