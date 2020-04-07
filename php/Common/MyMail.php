<?php

global $mail_admin;
global $mail_enabled;
global $mail_from;
global $mail_live;
global $mail_servers;
global $mail_transport;

function MyMail($message) {
    $trace = $GLOBALS['gTrace'];
    if ($trace) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $debug = $GLOBALS['gDebug'];
    static $ms = array();
    static $mailer;
    static $logger;

    if (empty($GLOBALS['mail_enabled'])) {
        echo "** Mail service for this application is not enabled, please use \$mail_enabled in your local settings<br>";
        exit;
    }
    if (empty($GLOBALS['mail_admin'])) {
        echo "** Mail administrator required, please use \$mail_admin in your local settings<br>";
    }
    if (empty($ms)) {
        if (count($GLOBALS['mail_servers']) == 0) {
            echo "** no Mail Servers defined, please use \$mail_servers in your local settings<br>";
            exit;
        }
        $ms = array_shift($GLOBALS['mail_servers']);
        $ms['connected'] = 0;
    }

    if (!$ms['connected']) {
        if ($debug) {
            printf("MyMail:  Connecting to server: [%s], port: [%d], transport: [%s]<br>", $ms['server'], $ms['port'], $ms['transport']);
        }

        if ($ms['transport'] == "smtp") {
            $transport = Swift_SmtpTransport::newInstance();
            $transport->setHost($ms['server']);
            $transport->setPort($ms['port']);
            if (isset($ms['encr']))
                $transport->setEncryption($ms['encr']);
            if (isset($ms['user'])) { # a login is required
                $transport->setUsername($ms['user']);
                $transport->setPassword($ms['pass']);
            }
        } elseif ($ms['transport'] == "sendmail") {
            $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
        }

        $mailer = Swift_Mailer::newInstance($transport);
        if ($debug)
            $logger = new Swift_Plugins_Loggers_EchoLogger();
        $mailer->registerPlugin(new Swift_Plugins_AntiFloodPlugin(500, 20));
        if ($debug)
            $mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));
        $ms['connected'] = 1;
    } else {
        if ($debug) {
            printf("MyMail:  Already connected to %s<br>", $ms['server']);
        }
    }

    if ($debug) {
        echo "MyMail> before send<br>";
        echo "logger #1:";
#		echo $logger->dump();
    }

    if (!$GLOBALS['mail_live']) {
        $message->setTo(['andy.elster@gmail.com','Andy Elster']);
        $message->setCc(array());
        $message->setBcc(array());
    }

    if (!empty($GLOBALS['mail_from'])) {
        $message->setFrom($GLOBALS['mail_from']);
    }

    $result = $mailer->send($message, $failures);

    if ($debug) {
        echo "MyMail> after send, sent $result<br>";
        echo "logger #2:";
#		echo $logger->dump();	
    }

    $retval = 0;

    if (!$result) {
        if ($debug)
            echo "error<br>";
        if (!empty($failures)) {
            $text = array();
            foreach ($failures as $key => $val) {
                $text[] = sprintf("%s -> %s", $key, $val);
            }
            $body = join("<br>", $text);
            $msg = Swift_Message::newInstance();
            $msg->setTo($GLOBALS['mail_admin']);
            $msg->setSubject('Email failures');
            $msg->setBody($body, 'text/html');
            $msg->setFrom($GLOBALS['mail_admin']);
            $mailer->send($msg);
            $retval = 0;
        }
    } else {
        if ($debug)
            echo "sent ok<br>";
        $retval = 1;
    }

    if ($trace)
        array_pop($GLOBALS['gFunction']);
    return $retval;
}

?>
