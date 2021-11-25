<?php

function SessionStuff($cmd) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $save_db = $GLOBALS['gDb'];
    $GLOBALS['gDb'] = $GLOBALS['gPDO'][$GLOBALS['gDbControlId']]['inst'];

    switch ($cmd) {
        case( 'start' ):
            session_start();
            if (empty($_SESSION['user_id'])) {
                if ($GLOBALS['gTrace'])
                    Logger("Starting new session");
            } else {
                if ($GLOBALS['gTrace'])
                    Logger("Using existing session");
                UserManager('load', $_SESSION['user_id']);
            }
            break; 

        case( 'display' ):
            if ($GLOBALS['gDebug'] == 0)
                return;
            foreach ($_COOKIE as $key => $val) {
                echo sprintf("COOKIE[%s]=%s<br>", $key, $val);
            }

            if (isset($_SESSION)) {
                echo sprintf("session_name: %s<br>", session_name());
                foreach ($_SESSION as $key => $val) {
                    echo sprintf("pre-SESSION[%s]=%s<br>", $key, $val);
                }
            }
            if (isset($_SESSION)) {
                foreach ($_SESSION as $key => $val) {
                    echo sprintf("post-SESSION[%s]=%s<br>", $key, $val);
                }
            }
            break;

        case( 'logout' ):
            unset($text);
            EventLog('record',[
                'type' => 'logout',
                'user_id' => $GLOBALS['gUserId'],
                'item' => 'session_id: ' . session_id()
                ]);
            foreach (array_keys($_SESSION) as $key) {
                unset($_SESSION[$key]);
            }
            $_SESSION = [];
            unset($_COOKIE[session_name()]);
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 42000, '/');
            }
            session_destroy();
            $GLOBALS['gUserVerified'] = 0;
            break;
    }
    $GLOBALS['gDb'] = $save_db;

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}