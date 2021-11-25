<?php

function UserManager() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $save_db = $GLOBALS['gDb'];
    $GLOBALS['gDb'] = $GLOBALS['gPDO'][$GLOBALS['gDbControlId']]['inst'];
    $GLOBALS['gDebug'] = 2;

    $fn = func_get_arg(0);

    switch ($fn) {
        case 'activate':
            UserManagerActivate();
            break;

        case( 'authorized' ):
            $auth = UserManagerAuthorized(func_get_arg(1));
            break;

        case( 'control' ):
            UserManagerControl();
            break;

        case( 'forgot' );
            UserManagerForgot();
            break;

        case( 'inactive' );
            UserManagerInactive();
            break;

        case( 'load' ):
            UserManagerLoad(func_get_arg(1));
            break;

        case( 'login' ):
            UserManagerLogin();
            break;

        case( 'logout' ):
            UserManagerLogout();
            break;

        case( 'mail' ):
            UserManagerMail();
            break;

        case( 'new' ):
            UserManagerNew();
            break;

        case( 'newpassword' ):
            UserManagerPassword();
            break;

        case( 'privileges' ):
            UserManagerPrivileges();
            break;

        case( 'report' ):
            UserManagerReport();
            break;

        case( 'reset' ):
            UserManagerReset();
            break;

        case( 'update' ):
            UserManagerUpdate();
            break;

        case( 'verify' ):
            UserManagerVerify();
            break;

        case( 'welcome' ):
            UserManagerWelcome();
            break;

        default:
            echo "Uh-oh:  Contact Andy regarding UserManager( $fn )<br>";
            break;
    }

    $GLOBALS['gDb'] = $save_db;

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
    if ($fn == 'authorized')
        return $auth;
}

function UserManagerActivate() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

//collect values from the url
    $userId = trim($_GET['x']);
    $active = trim($_GET['y']);

//if id is number and the active token is not empty carry on
    if (is_numeric($userId) && !empty($active)) {

        //update users record set the active column to Yes where the memberID and active value match the ones provided in the array
        $query = "UPDATE users SET active = 'Yes' WHERE id = :v0 AND active = :v1";
        $stmt = DoQuery($query, [':v0' => $userId, ':v1' => $active]);

        //if the row was updated redirect the user
        if ($stmt->rowCount() == 1) {

            //redirect to login page
            UserManagerLoad($userId);
            $GLOBALS['gAction'] = 'start';
            $GLOBALS['gArea'] = 'active';
            return;
        } else {
            echo "Your account could not be activated.";
        }
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerAdd() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $text = array();
    $text[] = sprintf("insert into users set first = '%s'", $_POST['u__0__first']);
    $text[] = sprintf("last = '%s'", $_POST['u__0__last']);
    $text[] = sprintf("email = '%s'", $_POST['u__0__email']);
    $text[] = sprintf("username = '%s'", $_POST['u__0__username']);
    $text[] = sprintf("password = '%s'", md5(sprintf("%d", time())));
    $text[] = sprintf("active = '1'");
    $query = join(",", $text);
    DoQuery($query);
    $id = $GLOBALS['gPDO_lastInsertID'];

    if ($id) {
        EventLogRecord( [
            'type' => 'control',
            'user_id' => $GLOBALS['gUserId'],
            'item' => sprintf("item = 'added user %s %s, username %s, id %d, e-mail %s'", $_POST['u__0__first'], $_POST['u__0__last'], $_POST['u__0__username'], $id, $_POST['u__0__email']) ]);

        $query = "insert into access set id = '$id', priv_id = '" . $_POST['access'] . "'";
        DoQuery($query);
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerAuthorized($privilege) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = sprintf("%s (%s)", __FUNCTION__, $privilege);
        Logger();
    }
    $level = $GLOBALS['gAccessLevel'];
    $ok = ( $level >= $GLOBALS['gAccessNameToLevel'][$privilege] ) ? 1 : 0;
    $ok = $ok && $GLOBALS['gAccessLevelEnabled'][$level];
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
    return $ok;
}

function UserManagerControl() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $action = isset($_POST['btn_action']) ? $_POST['btn_action'] : "";

    if (empty($action)) {
        UserManagerDisplay();
    } elseif ($action == 'Add') {
        UserManagerAdd();
    } elseif ($action == 'Delete') {
        UserManagerDelete();
    } elseif ($action == 'Edit') {
        UserManagerEdit();
        $GLOBALS['gFrom'] = 'Done';
    } elseif ($action == "Enable") {
        UserManagerActivate(1);
    } elseif ($action == "Disable") {
        UserManagerActivate(0);
    }

    $_POST['btn_action'] = NULL;

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerDelete() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];

    $stmt = DoQuery("select * from users where id = '$id'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    DoQuery("delete from users where id = '$id'");
    EventLogRecord( [
        'type' => 'control',
        'user_id' => $GLOBALS['gUserId'],
        'item' => sprintf("item = 'deleted user %s %s, username %s, e-mail %s'", $user['first'], $user['last'], $user['username'], $user['email'])
        ]);

    DoQuery("delete from access where id = '$id'");

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerDisplay() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $dreamweaver = array_key_exists('gDreamweaver', $GLOBALS) ? $GLOBALS['gDreamweaver'] : 0;
    if (!$dreamweaver) {
        echo "<div class=center>";

        echo "<h2>User Control</h2>";
        echo "<input type=hidden name=from value=Users>";
        echo sprintf("<input type=hidden name=user_id value='%d'>", $GLOBALS['gUserId']);

        echo "<input type=button onClick=\"addAction('main');\" value=Back>";

            echo "&nbsp;";
    echo "<input type=button onclick=\"addAction('users')\" value=Refresh>";
    echo "&nbsp;";

        $acts = [
            "setValue('area','update')",
            "setValue('id', '" . $GLOBALS['gUserId'] . "')",
            "addAction('update')"
        ];
        echo sprintf("<input type=button onClick=\"%s\" id=update value=Update>", join(';', $acts));
    }

    $vprivs = array();
    $vlevels = array();
    $stmt = DoQuery('select name, id, level from privileges order by level desc');
    while (list( $name, $id, $level ) = $stmt->fetch(PDO::FETCH_NUM)) {
        $vprivs[$name] = $id;
        $vlevels[$name] = $level;
    }

    foreach ($vprivs as $name => $level) {
        if (!UserManagerAuthorized($name))
            continue;

        $i = 0;
        $divOpened = 0;

        $query = "select * from users, access where";
        $query .= " users.id = access.id and access.priv_id = :pid order by users.username ASC";
        $stmt = DoQuery($query, array(':pid' => $level));
        if ($stmt->rowCount() > 0) {
            $divOpened = 1;

            echo "<div class=$name>";
            echo "<h3>$name</h3>";

            echo "<table class=usermanager>";

            echo "<thead>";
            echo "<tr>";
            echo "<th>Id</th>";
            echo "<th>Username</th>";
            echo "<th>First</th>";
            echo "<th>Last</th>";
            echo "<th>E-Mail</th>";
            echo "<th>Access</th>";
            echo "<th>Last Login</th>";
            echo "<th>Disabled</th>";
            echo "<th>Debug</th>";
            echo "<th>Actions</th>";
            echo "</tr>";
            echo "</thead>";
        }
        echo "<tbody>";
        $j = 1;
        while ($usr = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $usr['id'];
            $last_usr = $usr;

            echo "<tr>\n";
            printf("  <td>$id</td>\n");

            $ajax_id = "id=\"users__username__$id\"";
            printf("  <td><input type=text $ajax_id class=ajax value=\"%s\" size=10></td>\n", $usr['username']);

            $ajax_id = "id=\"users__first__$id\"";
            printf("  <td><input type=text $ajax_id class=ajax value=\"%s\" size=10></td>\n", $usr['first']);

            $ajax_id = "id=\"users__last__$id\"";
            printf("  <td><input type=text $ajax_id class=ajax value=\"%s\" size=10></td>\n", $usr['last']);

            $ajax_id = "id=\"users__email__$id\"";
            printf("  <td><input type=text $ajax_id class=ajax value=\"%s\" size=25></td>\n", $usr['email']);

            $ajax_id = "id=\"access__priv_id__$id\"";
            printf("  <td><select $ajax_id class=ajax>", $id);
            foreach ($vprivs as $name => $priv_id) {
                if ($vlevels[$name] > $GLOBALS['gAccessLevel'])
                    continue;
                $opt = ( $usr['priv_id'] == $priv_id ) ? 'selected' : '';
                echo "<option value=$priv_id $opt>$name</option>";
            }
            echo "</select></td>\n";

            if ($usr['lastlogin'] == NULL )
                $str = "never";
            else {
                $diff = time() - strtotime($usr['lastlogin']);
                $days = $diff / 60 / 60 / 24;
                if ($days >= 1) {
                    $str = sprintf("%d days ago", $days);
                } else {
                    $str = $usr['lastlogin'];
                }
            }
            echo "  <td align=center>$str</td>\n";

            echo "<td style=\"text-align: center;\">";
            if ($usr['disabled']) {
                $checked = "checked";
                $val = 0;
            } else {
                $checked = "";
                $val = 1;
            }
            $ajax_id = "id=\"users__disabled__$id\"";
            echo "<input type=checkbox $ajax_id class=ajax value=$val $checked>";
            echo "</td>\n";
#------------------------------------------------------------------------------------------------
            echo "<td style=\"text-align: center;\">";

            $tag = "DebugInLine";
            $val = $GLOBALS["g$tag"];
            if (($usr['debug'] & $val) > 0) {
                $checked = "checked";
            } else {
                $checked = "";
            }
            $ajax_id = "id=\"users__debug__$id\"";
            echo "I:<input type=checkbox $ajax_id class=ajax value=$val $checked>";

            $tag = "DebugErrorLog";
            $val = $GLOBALS["g$tag"];
            if (($usr['debug'] & $val) > 0) {
                $checked = "checked";
            } else {
                $checked = "";
            }
            $str = sprintf("debug: %d, val:  %d, flag: %s", $usr['debug'], $val, $checked);
            Logger($str);
            $name = "u_{$id}_{$tag}";
            $jsx = [];
            $jsx[] = "setValue('area','users')";
            $jsx[] = "setValue('mode','control')";
            $jsx[] = "setValue('func','update')";
            $jsx[] = "addField('$name')";
            $jsx[] = "addAction('update')";
            $jscript = "onChange=\"" . implode(";", $jsx) . "\"";
            echo "E:<input type=checkbox name=\"$name\" value=$val $checked $jscript >";

            echo "<br>";
            
            $tag = "DebugWindow";
            $val = $GLOBALS["g$tag"];
            if (($usr['debug'] & $val ) > 0) {
                $checked = "checked";
            } else {
                $checked = "";
            }
            $str = sprintf("debug: %d, val:  %d, flag: %s", $usr['debug'], $val, $checked);
            Logger($str);
            $name = "u_{$id}_{$tag}";
            $jsx = [];
            $jsx[] = "setValue('area','users')";
            $jsx[] = "setValue('mode','control')";
            $jsx[] = "setValue('func','update')";
            $jsx[] = "addField('$name')";
            $jsx[] = "addAction('update')";
            $jscript = "onChange=\"" . implode(";", $jsx) . "\"";
            echo "W:<input type=checkbox name=\"$name\" value=$val $checked $jscript >";

            $tag = "DebugHTML";
            $val = $GLOBALS["g$tag"];
            if (($usr['debug'] & $val ) > 0) {
                $checked = "checked";
            } else {
                $checked = "";
            }
            $str = sprintf("debug: %d, val:  %d, flag: %s", $usr['debug'], $val, $checked);
            Logger($str);
            $name = "u_{$id}_{$tag}";
            $jsx = [];
            $jsx[] = "setValue('area','users')";
            $jsx[] = "setValue('mode','control')";
            $jsx[] = "setValue('func','update')";
            $jsx[] = "addField('$name')";
            $jsx[] = "addAction('update')";
            $jscript = "onChange=\"" . implode(";", $jsx) . "\"";
            echo "H:<input type=checkbox name=\"$name\" value=$val $checked $jscript >";

            echo "</td>\n";
#------------------------------------------------------------------------------------------------
            echo "  <td>";
            $acts = array();
            $acts[] = "setValue('area','users')";
            $acts[] = "setValue('func','delete')";
            $acts[] = "setValue('id', '$id')";
            $name = sprintf("%s (%s %s)", $usr['username'], $usr['first'], $usr['last']);
            $acts[] = "myConfirm('users','Are you sure you want to delete user $name')";
            echo sprintf("<input type=button onClick=\"%s\" value=Del style=\"vertical-align: middle;\">", join(';', $acts));
            echo "&nbsp;";
           
            $tag = MakeTag('email');
            $jsx = [];
            $jsx[] = "setValue('id','$id')";
            $jsx[] = "setValue('func','mail')";
            $jsx[] = "setValue('id2','reset')";
            $jsx[] = "addAction('UserManager')";
            $js = "onclick=\"" . join(';',$jsx) . "\"";
            echo "<input type=hidden $tag>";
            echo "<input type=button value=\"Reset Password\" $js style=\"vertical-align: middle; white-space: break-spaces; width: 80px;\">";
            echo "</td>\n";

            echo "</tr>\n";
            $j++;
        }
        echo "</tbody>";
        echo "</table>";
        if ($divOpened) {
            echo "</div>";
            echo "<br>";
        }
    }

    if (UserManagerAuthorized('admin')) {
        $id = 0;
        foreach ($last_usr as $key => $val) {
            $usr[$key] = "";
        }
        echo "<div class=center>";
        echo "<h3>New Users</h3>";

        echo "<table class=usermanager>";

        echo "<thead>";
        echo "<tr>";
        echo "<th>Id</th>";
        echo "<th>Username</th>";
        echo "<th>First</th>";
        echo "<th>Last</th>";
        echo "<th>E-Mail</th>";
        echo "<th>Access</th>";
        echo "<th>Last Login</th>";
        echo "<th>Disabled</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";

        echo "<tr>\n";
        printf("  <td></td>\n");
        printf("  <td><input type=text name=u__%d__username value=\"%s\" size=10></td>\n", $id, $usr['username']);
        printf("  <td><input type=text name=u__%d__first value=\"%s\" size=10></td>\n", $id, $usr['first']);
        printf("  <td><input type=text name=u__%d__last value=\"%s\" size=10></td>\n", $id, $usr['last']);
        printf("  <td><input type=text name=u__%d__email value=\"%s\" size=25></td>\n", $id, $usr['email']);

        printf("  <td><select name=u__%d__priv_id>", $id);
        $defSet = 0;
        $defMax = 0;
        foreach ($vprivs as $name => $priv_id) {
            if ($vlevels[$name] > $GLOBALS['gAccessLevel'])
                continue;
            $defSet = ( $usr['priv_id'] == $priv_id ) ? $priv_id : 0;
            if ($priv_id > $defMax)
                $defMax = $priv_id;
        }
        if ($defSet == 0)
            $defSet = $defMax; // Force a selection of at least one new value
        foreach ($vprivs as $name => $priv_id) {
            if ($vlevels[$name] > $GLOBALS['gAccessLevel'])
                continue;
            $opt = ( $priv_id == $defSet ) ? 'selected' : '';
            echo "<option value=$priv_id $opt>$name</option>";
        }
        echo "</select></td>\n";

        $str = "n/a";
        echo "  <td align=center>$str</td>\n";

        $checked = $usr['disabled'] ? "checked" : "";
        printf("  <td style='text-align: center;'><input type=checkbox name=u_%d_disabled value=1 $checked></td>\n", $id);

        echo "  <td>";
        $acts = array();
        $acts[] = "setValue('area','users')";
        $acts[] = "setValue('func','add')";
        $acts[] = "setValue('id', '$id')";
        $name = sprintf("%s (%s %s)", $usr['username'], $usr['first'], $usr['last']);
        $acts[] = "addAction('update')";
        echo sprintf("<input type=button onClick=\"%s\" value=Add>", join(';', $acts));
        echo "</td>\n";

        echo "</tr>\n";
        echo "</tbody>";
        echo "</table>";
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerEdit() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $query = "select * from users where id = '$id'";
    $stmt = DoQuery($query);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo sprintf("<input type=hidden name=user_id value='%d']>", $GLOBALS['gUserId']);
    echo "
        <input type=hidden name=from value=UserEdit>
        <input type=hidden name=id id=id>
        <input type=hidden name=btn_action id=btn_action>
        <div id=users>
            <table>
                <tr>
                    <th>First</th>
                    <th>Last</th>
                    <th>Username</th>
                    <th>E-Mail</th>
                </tr>
                <tr>
    ";
    echo sprintf("<td><input type=text name=first size=20 value='%s'></td>", $user['first']);
    echo sprintf("<td><input type=text name=last size=20 value='%s'></td>", $user['last']);
    echo sprintf("<td><input type=text name=username size=20 value='%s'></td>", $user['username']);
    echo sprintf("<td><input type=text name=email size=20 value='%s'></td>", $user['email']);
    echo "<tr>";
    echo "</table>";
    echo "</div>";
    echo "<input type=submit name=action value=Back>";
    echo "<input type=button onclick=\"setValue( 'id', '$id'); setValue( 'btn_action', 'Update' ); addAction('update');\" value=Update>";

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerReset() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $jsx = array();
    $jsx[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
    $jsx[] = "setValue('func','mail')";
    $jsx[] = "addAction('UserManager')";
    $js1 = "onclick=\"" . join(';',$jsx) . "\"";
    
    $jsx = [];
    $jsx[] = "addAction('start')";
    $js2 = "onclick=\"" . join(';',$jsx) . "\"";

    echo <<<EOT
<div class="user_manager center">
    <h2>Reset Password</h2>
    <hr>
    <input type='text' name='email' id='email' placeholder='Email' tabindex='1' size=40>
    <hr>
    <input type=button value="Send Reset Link" $js1 tabindex=2><br>
    <input type=button value="Start Over" $js2 tabindex=3>
</div>
EOT;

    if ($GLOBALS['gTrace'])
    array_pop($GLOBALS['gFunction']);
}

function UserManagerResetxy() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $func = $GLOBALS['gFunc'];
    $error = array();
//    $admin = $GLOBALS['gMailAdmin'][0];

    {
        echo "
                        <div class='container'>

                            <div class='row'>

                                <div class='col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3'>
                                    <form role='form' method='post' action='' autocomplete='off'>
                                        <h2>Reset Password</h2>
                                        <hr>
";
        //check for any errors
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<p class="bg-danger">' . $error . '</p>';
            }
        }

        if (isset($_GET['action'])) {

            //check the action
            switch ($_GET['action']) {
                case 'active':
                    echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                    break;
                case 'reset':
                    echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
                    break;
            }
        }
        echo "
                                        <div class='form-group'>
                                            <input type='email' name='email' id='email' class='form-control input-lg' placeholder='Email' value='' tabindex='1' size=40>
                                        </div>

                                        <hr>
                                        <div class='form-group'>
                                            <div class='col-xs-6 col-md-6'>
                                        ";
        $acts = array();
        $acts[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
        $acts[] = "setValue('func','mail')";
        $acts[] = "addAction('login')";
        echo sprintf("<input type=button onClick=\"%s\" id=update value='Send Reset Link'"
                . " class=\"btn btn-primary btn-block btn-lg\" tabindex=\"2\">", join(';', $acts));
        $jsx = [];
        $jsx[] = "addAction('start')";
        $js = sprintf("onclick=\"%s\"", join(';', $jsx));

        echo "
        &nbsp;<div class='form-group'><input type=button name=action value=\"Start Over\" label=start $js></div>
        </div>
        </div>
        ";
        echo "
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                        </div>

                ";
    }
    $GLOBALS['gError'] = $error;
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
}

function UserManagerGetEmail() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $func = $GLOBALS['gFunc'];
    $error = array();
    $admin = $GLOBALS['gMailAdmin'][0];

    if ($func == 'send') {
#        if (isset($_POST['submit'])) {
        //Make sure all POSTS are declared
        if (!isset($_POST['email']))
            $error[] = "Please fill out all fields";

        //email validation
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error[] = 'Please enter a valid email address';
        } else {
            $stmt = DoQuery('SELECT * FROM users WHERE email = :email', array(':email' => $_POST['email']));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($GLOBALS['gPDO_num_rows'] == 0) {
                $error[] = 'Email provided is not recognised.';
            }
        }
        //if no errors have been created carry on
        if (empty($error)) {
            //create the activation code
            $token = hash_hmac('SHA256', $GLOBALS['user']->generate_entropy(8), $row['password']); //Hash and Key the random data
            $storedToken = hash('SHA256', ($token)); //Hash the key stored in the database, the normal value is sent to the user

            $query = "UPDATE users SET resetToken = :token, resetComplete=0 WHERE email = :email";
            DoQuery($query, array(
                ':email' => $row['email'],
                ':token' => $storedToken
            ));

            $subject = "Password Reset for " . $row['username'];
            $recipients[$row['email']] = $row['first'] . " " . $row['last'];

            $body = sprintf("<img src=\"cid:sigimg\" width='%d' height='%d'/>", $GLOBALS['gMailSignatureImageSize']['width'], $GLOBALS['gMailSignatureImageSize']['height']);
            $body .= "<p>A password reset request was made for an account with this email.";
            $body .= " If this was a mistake just ignore this email and nothing will happen.</p>";
            $body .= "<p>Username: " . $row['username'] . "</p>";
            $body .= "<p>To reset your password click on the following link: <a href='" . $GLOBALS['gSourceCode'];
            $body .= "?action=password&func=reset&key=$token'>" . $GLOBALS['gSourceCode'] . "</a></p>";
            $body .= "<br>" . join('<br>', $GLOBALS['gMailSignature']);

            unset($mail);
            $mail = MyMailerNew();
            try {
                //Receipients
                $mail->setFrom($admin['email'], $admin['name']);


                if ($GLOBALS['gTestModeEnabled']) {
                    $mail->addAddress($admin['email'], $admin['name']);
                } else {
                    foreach ($recipients as $email => $name) {
                        $mail->addAddress($email, $name);
                    }
                }
                //Attachments
                $mail->AddEmbeddedImage($GLOBALS['gMailSignatureImage'], 'sigimg', $GLOBALS['gMailSignatureImage']);

                //Content
                $mail->Subject = $subject;
                $mail->Body = $body;

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
#        }
    } else {
        echo "
                        <div class='container'>

                            <div class='row'>

                                <div class='col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3'>
                                    <form role='form' method='post' action='' autocomplete='off'>
                                        <h2>Reset Password</h2>
                                        <hr>
";
        //check for any errors
        if (isset($error)) {
            foreach ($error as $error) {
                echo '<p class="bg-danger">' . $error . '</p>';
            }
        }

        if (isset($_GET['action'])) {

            //check the action
            switch ($_GET['action']) {
                case 'active':
                    echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                    break;
                case 'reset':
                    echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
                    break;
            }
        }
        echo "
                                        <div class='form-group'>
                                            <input type='email' name='email' id='email' class='form-control input-lg' placeholder='Email' value='' tabindex='1' size=40>
                                        </div>

                                        <hr>
                                        <div class='form-group'>
                                            <div class='col-xs-6 col-md-6'>
                                        ";
        $acts = array();
        $acts[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
        $acts[] = "setValue('func','send')";
        $acts[] = "addAction('password')";
        echo sprintf("<input type=button onClick=\"%s\" id=update value='Send Reset Link'"
                . " class=\"btn btn-primary btn-block btn-lg\" tabindex=\"2\">", join(';', $acts));
        $jsx = [];
        $jsx[] = "addAction('start')";
        $js = sprintf("onclick=\"%s\"", join(';', $jsx));

        echo "
        &nbsp;<div class=\"form-group\"><input type=button name=action value=\"Start Over\" label=start $js></div>
        </div>
        </div>
        ";
        echo "
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>


                        </div>

                ";
    }
    $GLOBALS['gError'] = $error;
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
}

function UserManagerInactive() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    if (empty($GLOBALS['gEnabled'])) {
        $level = $GLOBALS['gAccessLevel'];
        $stmt = DoQuery("select name from privileges where level = '$level'");
        list( $name ) = $stmt->fetch(PDO::FETCH_NUM);
        echo "$name access has been temporarily disabled.  ";
    } else if (empty($GLOBALS['gActive'])) {
        echo "Access to your account has been temporarily disabled.  ";
    }

    $admin = $GLOBALS['gSupport'];
    echo "Please try again later or click ";
    echo "<a href=\"mailto:$admin?subject=Account access disabled\">here</a>";
    echo " for support.";
    echo "<br><br>";
    echo "<input type=submit name=action value=Logout>";

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerLoad($userId) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = sprintf("%s (%d)", __FUNCTION__, $userId);
        Logger();
    }

    $stmt = DoQuery('SELECT * from users where id = :uid', [':uid' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $GLOBALS['gUserId'] = $userId;
    $GLOBALS['gNameFirst'] = $row['first'];
    $GLOBALS['gNameLast'] = $row['last'];
    $GLOBALS['gPasswdChanged'] = $row['pwdchanged'];
    $GLOBALS['gUserVerified'] = 1;
    $GLOBALS['gUserName'] = $row['username'];
    $GLOBALS['gLastLogin'] = $row['lastlogin'];
    $GLOBALS['gActive'] = $row['active'];
    $GLOBALS['gDebug'] = $GLOBALS['gTrace'] = $row['debug'];

    $query = 'select privileges.level, privileges.enabled from privileges, access';
    $query .= ' where access.priv_id = privileges.id and access.id = :uid';
    $stmt2 = DoQuery($query, array(':uid' => $userId));
    list( $level, $enabled ) = $stmt2->fetch(PDO::FETCH_NUM);
    $GLOBALS['gAccessLevel'] = $level;
    $GLOBALS['gEnabled'] = $enabled;
    $_SESSION['username'] = $row['username'];
    $_SESSION['level'] = $level;

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}
function UserManagerMail() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    
    $error = [];
    $reset = (!empty($_POST['id2']) ) && $_POST['id2'] == 'reset';

    if (! empty($_POST['email'])) {
//        $error[] = "Please fill out all fields";
        if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $error[] = 'Please enter a valid email address';
        } else {
            $stmt = DoQuery('SELECT * FROM users WHERE email = :email', [':email' => $_POST['email'] ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } elseif ( $reset && $_POST['id'] ) {
        $id = $_POST['id'];
        $stmt = DoQuery('SELECT * FROM users WHERE id = :id', [':id' => $id ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } else {
        $error[] = "uh oh, a problem";
    }

    //if no errors have been created carry on
    if (empty($error)) {
        //create the activation code
        $token = password_hash(microtime(), PASSWORD_DEFAULT);

        $query = "UPDATE users SET resetToken = :token, resetComplete=0, lastlogin = NULL WHERE email = :email";
        DoQuery($query, array(
            ':email' => $row['email'],
            ':token' => $token
        ));

        $subject = "Password Reset for " . $row['username'];
        $recipients[$row['email']] = $row['first'] . " " . $row['last'];

        $body = sprintf("<img src=\"cid:sigimg\" width='%d' height='%d'/>", $GLOBALS['gMailSignatureImageSize']['width'], $GLOBALS['gMailSignatureImageSize']['height']);
        $body .= "<p>A password reset request was made for an account with this email.";
        $body .= " If this was a mistake just ignore this email and nothing will happen.</p>";
        $body .= "<p>Username: " . $row['username'] . "</p>";
        $body .= "<p>To reset your password click on the following link: <a href='" . $GLOBALS['gSourceCode'];
        $body .= "?action=password&func=reset&key=$token&id={$row['id']}'>" . $GLOBALS['gSourceCode'] . "</a></p>";
        $body .= "<br>" . join('<br>', $GLOBALS['gMailSignature']);

        unset($mail);
        $mail = MyMailerNew();
        try {
            //Receipients
            $admin = $GLOBALS['gMailAdmin'];

            $mail->setFrom($admin[0]['email'], $admin[0]['name']);


            if( $GLOBALS['gMailLive'] ) {
                foreach ($recipients as $email => $name) {
                    $mail->addAddress($email, $name);
                }
            } else {
                $mail->addAddress($admin[0]['email'], $admin[0]['name']);
            }
            //Attachments
            $mail->AddEmbeddedImage($GLOBALS['gMailSignatureImage'], 'sigimg', $GLOBALS['gMailSignatureImage']);

            //Content
            $mail->Subject = $subject;
            $mail->Body = $body;

            if ($mail->send()) {
                if( ! $reset ) {
                    $jsx = [];
                    $jsx[] = "setValue('func','login')";
                    $jsx[] = "addAction('UserManager')";
                    $js = sprintf("onclick=\"%s\"", join(';', $jsx));

                    echo <<<EOT
<div class="user_manager center">
    <h2 class='bg-success'>Please check your inbox for a reset link</h2>
    <input type=button name=action value="Continue" label=start $js>
</div>
EOT;
                }
            } else {
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
    
    if( $GLOBALS['gFrom'] = 'UserManagerDisplay' ) {
        $GLOBALS['gFunc'] = 'users';
        $GLOBALS['gAction'] = 'start';
    }
}

function UserManagerNew() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $user = $GLOBALS['user'];
    $admin = $GLOBALS['gMailAdmin'][0];
    $area = filter_input(INPUT_POST, 'area');
    if ($area == 'verify') {
        if (!isset($_POST['username']))
            $error[] = "Please fill out all fields";
        if (!isset($_POST['email']))
            $error[] = "Please fill out all fields";
        if (!isset($_POST['password']))
            $error[] = "Please fill out all fields";

        $username = $_POST['username'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];

        //very basic validation
        if (!$user->isValidUsername($username)) {
            $error[] = 'Usernames must be at least 3 Alphanumeric characters';
        } else {
            $stmt = DoQuery('SELECT username FROM users WHERE username = :username', [':username' => "$username"]);
            if ($stmt->rowCount()) {
                $error[] = 'Username provided is already in use.';
            }
        }

        if (strlen($_POST['password']) < 3) {
            $error[] = 'Password is too short.';
        }

        if (strlen($_POST['passwordConfirm']) < 3) {
            $error[] = 'Confirm password is too short.';
        }

        if ($_POST['password'] != $_POST['passwordConfirm']) {
            $error[] = 'Passwords do not match.';
        }

        //email validation
        $email = htmlspecialchars_decode($_POST['email'], ENT_QUOTES);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = 'Please enter a valid email address';
        } else {
            $stmt = DoQuery('SELECT email FROM users WHERE email = :email', [':email' => $email]);
            if (!empty($row['email'])) {
                $error[] = 'Email provided is already in use.';
            }
        }


        //if no errors have been created carry on
        if (!isset($error)) {

            //hash the password
            $hashedpassword = $user->password_hash($_POST['password'], PASSWORD_BCRYPT);

            //create the activasion code
            $activasion = md5(uniqid(rand(), true));

            $stmt = DoQuery('select * from users');
            $num_users = $stmt->rowCount();

            //insert into database with a prepared statement
            $query = "insert into users (username,first,last,password,email,active)";
            $query .= " value (:username,:first,:last,:password,:email,:active)";
            DoQuery($query, [
                ':username' => $username,
                ':first' => $firstName,
                ':last' => $lastName,
                ':password' => $hashedpassword,
                ':email' => $email,
                ':active' => $activasion
            ]);

            $id = $GLOBALS['gDb']->lastInsertId('user_id');
            if ($num_users == 0) {
                $stmt = DoQuery('select max(level) from privileges'); # first user is privileged
            } else {
                $stmt = DoQuery('select min(level) from privileges'); # all others minimal at first
            }
            list( $level ) = $stmt->fetch(PDO::FETCH_NUM);
            $stmt = DoQuery('select id from privileges where level = :level', [':level' => $level]);
            list( $pid ) = $stmt->fetch(PDO::FETCH_NUM);
            DoQuery('insert into access set id = :uid, PrivId = :pid', [':uid' => $id, ':pid' => $pid]);

            $subject = "Registration Confirmation for " . $GLOBALS['gTitle'];
            $recipients[$email] = $firstName . " " . $lastName;

            $body = "<p>Thank you for registering at " . $GLOBALS['gTitle'] . ".</p>";
            $body .= "<p>To activate your account, please click on this link: ";
            $body .= "<a href='" . $_SERVER['HTTP_REFERER'] . "?action=activate&x=$id&y=$activasion'>";
            $body .= "activate</a></p>";
            $body .= "<p>Regards Site Admin</p>";

            unset($mail);
            $mail = MyMailerNew();
            try {
                //Receipients
                $mail->setFrom($admin['email'], $admin['name']);
                if ($GLOBALS['gTestModeEnabled']) {
                    $mail->addAddress($admin['email'], $admin['name']);
                } else {
                    foreach ($recipients as $email => $name) {
                        $mail->addAddress($email, $name);
                    }
                    $mail->AddCC($admin['email'], $admin['name']);
                }
                //Attachments
                $mail->AddEmbeddedImage($GLOBALS['gMailSignatureImage'], 'sigimg', $GLOBALS['gMailSignatureImage']);

                $mail->subject = $subject;
                $mail->body = $body;
                if (!$mail->send()) {
                    $err = 'Message could not be sent.';
                    $err .= 'Mailer Error: ' . $mail->ErrorInfo;
                    echo $err;
                    foreach ($mail as $key => $val) {
                        printf("mail[%s] = [%s]<br>", $key, $val);
                    }
                }
                //Content
                $mail->Subject = $subject;
                $mail->Body = $body;

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

            //redirect to index page
            $GLOBALS['gAction'] = 'start';
            $GLOBALS['gFunc'] = 'users';
            $action = 'joined';
        }
    }
    ?>


    <div class="container">

        <div class="row">

            <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
                <form role="form" method="post" action="" autocomplete="off">
                    <h2>Please Sign Up</h2>
                    <p>Already a member? <a href='admin.php'>Login</a></p>
                    <hr>

                    <?php
                    //check for any errors
                    if (isset($error)) {
                        foreach ($error as $error) {
                            echo '<p class="bg-danger">' . $error . '</p>';
                        }
                    }

                    //if action is joined show sucess
                    if (isset($action) && $action == 'joined') {
                        echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
                        $acts = [
                            "setValue('from','" . __FUNCTION__ . "')",
                            "addAction('Back')"
                        ];
                        echo sprintf("<input type=button onClick=\"%s\" value='Back' class='btn btn-primary btn-block btn-lg' tabindex='7'>", join(';', $acts));
                    } else {
                        ?>

                        <div class="form-group">
                            <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name/Phone" value="<?php
                            if (isset($error)) {
                                echo htmlspecialchars($_POST['username'], ENT_QUOTES);
                            }
                            ?>" tabindex="1">
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="text" name="firstName" id="firstName" class="form-control input-lg" placeholder="First Name" value="<?php
                                    if (isset($error)) {
                                        echo htmlspecialchars($_POST['firstName'], ENT_QUOTES);
                                    }
                                    ?>" tabindex="2">
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="text" name="lastName" id="lastName" class="form-control input-lg" placeholder="Last Name" value="<?php
                                    if (isset($error)) {
                                        echo htmlspecialchars($_POST['lastName'], ENT_QUOTES);
                                    }
                                    ?>" tabindex="3">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php
                            if (isset($error)) {
                                echo htmlspecialchars($_POST['email'], ENT_QUOTES);
                            }
                            ?>" tabindex="4">
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="5">
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="6">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-6 col-md-6">
                                <?php
                                $acts = [
                                    "setValue('area','verify')",
                                    "addAction('New')"
                                ];
                                echo sprintf("<input type=button onClick=\"%s\" value='Register' class='btn btn-primary btn-block btn-lg' tabindex='7'>", join(';', $acts));
                                ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <?php
    }
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
}

function UserManagerLogin() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $stmt = DoQuery('select * from users');
    if ($stmt->rowCount() == 0) {
        UserManagerNew();
        return;
    }

        $jsx = array();
        $jsx[] = "setValue('func','reset')";
        $jsx[] = "addAction('login')";
        $js1 = sprintf("onClick=\"%s\"", join(';', $jsx));
        
        $jsx = array();
        $jsx[] = "setValue('func','verify')";
        $jsx[] = "addAction('login')";
        $js2 = sprintf("onClick=\"%s\"", join(';', $jsx));
        echo <<<EOT
<div class="user_manager center">
            <h2>Please Login</h2>
            <input type=button $js1 value="Reset your password?">
            <hr>
            <table class=no_border>
                <tr>
                <td><input type="text" name="username" id="username" placeholder="User Name" tabindex=1></td>
                <td><p id=username_error class=hidden></p></td>
                </tr>
                <tr>
                <td><input type="password" name="password" id="password" placeholder="Password" tabindex=2></td>
                <td><p id=password_error class=hidden></p></td>
                </tr>
            </table>
            <hr>
            <input type=button value="Login" $js2 tabindex=3>
</div>
EOT;

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerLogout() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    SessionStuff('logout');
    echo "<div class=center>";
    echo "<div class=row>";
    echo "<h2 class='bg-success'>You have been successfully logged out</h2>";
    $jsx = [];
    $jsx[] = "addAction('start')";
    $js = sprintf("onclick=\"%s\"", join(';', $jsx));

    echo "<div class=\"form-group\">
    <input type=button name=action value=\"Start Over\" label=start $js>
        </div>
    </div>
    </div>
    ";
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerPassword() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $stmt = DoQuery("select id, username from users where resetToken = :v1", [':v1' => $GLOBALS['gResetKey'] ]);
    if( $GLOBALS['gPDO_num_rows']) {
        list( $id, $username ) = $stmt->fetch(PDO::FETCH_NUM);
        $jsx = array();
        $jsx[] = "setValue('area','password')";
        $jsx[] = "setValue('id','$id')";
        $jsx[] = "addAction('UserManager')";
        $js = "onClick=\"" . join(';', $jsx) . "\"";

        echo <<<EOT
    <div align=center>
        <div style="width:50%;">
            <br>
            You will need to select a username if it is blank<br>
            You will now need to select a new password.<br>
            The password is secure and encrypted and never transmitted or stored in clear text.<br>

            The UPDATE button will be activated once your password, entered twice, has been verified for a match.
            <br><br>
        </div>
        <input type=hidden name=from value=UserManagerPassword>
        <input type=hidden name=update_pass value=1>
        <input type=hidden name=nobypass value=1>
        <table class=norm>
            <tr>
                <th class=norm>Username</th>
                <td><input type=text name=username id=username size=30 value="$username" disabled></td>
            </tr>
            <tr>
                <th class=norm>Password
                <td class=norm><input type=password name=newpassword1 id=newpassword1 onKeyUp="verifypwd(1);" value="oneoneone" size=30>
            </tr>
            <tr>
                <th class=norm>One more time
                <td class=norm><input type=password name=newpassword2 id=newpassword2 onKeyUp="verifypwd(2);" value="twotwotwo" size=30>
            </tr>
        </table>
        <br>
        <a id=pwdval>&nbsp;</a>
        <br><br>
    <div class=user_manager>
    <input type=button id=userSettingsUpdate name=userSettingsUpdate disabled $js value=Update></th>
    </div>
EOT;
    } else {
        $jsx = [];
        $jsx[] = "addAction('start')";
        $js = sprintf("onclick=\"%s\"", join(';', $jsx));
        
        echo <<<EOT
<div class="user_manager center">
  <h2>Your password reset has already been tried</h2>
  <input type=button name=action value="Start Over" label=start $js>
</div>
EOT;
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
    exit;
}

function UserManagerPrivileges() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $dreamweaver = array_key_exists('gDreamweaver', $GLOBALS) ? $GLOBALS['gDreamweaver'] : 0;

    if (!$dreamweaver) {
        echo "<div class=center>";
        echo "<h2>Privilege Control</h2>";

        echo "<input type=button value=Back onclick=\"addAction('main');\">";
        echo "<br><br>";

        echo "</div>";
    }

    echo "<table class=privileges>";
    echo "<tr>";
    echo "<th>Name</th>";
    echo "<th>Level</th>";
    echo "<th>Enabled</th>";
    echo "<th>Actions</ht>";
    echo "</tr>";

    $stmt = DoQuery("select * from privileges order by level desc");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $jscript = "onChange=\"addField('$id');toggleBgRed('update');\"";
        echo "<tr>";
        echo "<td><input type=text size=8 name=p_${id}_name $jscript value=\"" . $row['name'] . "\"></td>";
        echo "<td><input type=text size=8 name=p_${id}_level $jscript value=\"" . $row['level'] . "\"></td>";
        $checked = empty($row['enabled']) ? "" : "checked";
        echo "<td class=c><input type=checkbox name=p_${id}_enabled $jscript value=1 $checked></td>";

        $acts = array();
        $acts[] = "setValue('area','privileges')";
        $acts[] = "setValue('func','delete')";
        $acts[] = "setValue('id','$id')";
        $msg = sprintf('Are you sure you want to delete privilege:%s, level:%d?', $row['name'], $row['level']);
        $acts[] = "myConfirm('privileges','$msg')";
        echo sprintf("<td class=c><input type=button onClick=\"%s\" value=Del></td>", join(';', $acts));

        echo "</tr>";
    }
    $id = 0;
    $jscript = "onChange=\"addField('$id');toggleBgRed('update');\"";
    echo "<tr>";
    echo "<td><input type=text size=8 name=p_${id}_name $jscript value=\"\"></td>";
    echo "<td><input type=text size=8 name=p_${id}_level $jscript value=\"\"></td>";
    echo "<td class=c><input type=checkbox name=p_${id}_enabled $jscript value=1 ></td>";

    $acts = array();
    $acts[] = "setValue('area','privileges')";
    $acts[] = "setValue('func','add')";
    $acts[] = "setValue('id','$id')";
    $acts[] = "addAction('update')";
    $js = join(';', $acts);
    echo sprintf("<td><input type=button onClick=\"%s\" value=Add></td>", $js);

    echo "</tr>";
    echo "</table>";
    echo "</div>";

    if ($dreamweaver == 23) {
        ?>
        <script type="text/javascript">
            var sidebar = document.getElementById("sidebar");
            sidebar.innerHTML += '<hr>';

            var btn = document.createElement('button');
            btn.id = 'update';
            btn.setAttribute('class', 'sidebar-btn');
            btn.setAttribute("onclick", "<?php echo $js ?>");
            btn.innerText = 'Update';
            sidebar.appendChild(btn);
        </script>
        <?php
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerReport() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    echo "<input type=hidden name=from value=Users>";
    echo "<input type=hidden name=addr_list id=addr_list>";
    echo "<div id=users>";

    echo "<table>";

    echo "<tr>";
    echo "<th>#</th>";
    echo "<th>Name</th>";
    echo "<th colspan=2>Email</th>";
    echo "<th>Contact</th>";
    echo "<th>Last Login</th>";
    echo "</tr>";
    $i = 0;

    $query = "select * from users";
    $query .= " where access > 1 and access < '" . $gAccessLevels['author'] . "'";
    $query .= " order by last ASC";

    $stmt = DoQuery($query);

    while ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userid = $user['userid'];

        $cl = ( $user['lastlogin'] == '0000-00-00 00:00:00' ) ? "class=never" : "";

        $i++;
        echo "<tr $cl>";
        echo "<td>$i</td>";
        echo sprintf("<td>%s, %s</td>", $user['last'], $user['first']);
        echo sprintf("<td><a id=email_%s href=\"mailto:%s\">%s</a></td>", $userid, $user['email'], $user['email']);
        echo sprintf("<td><input type=checkbox name=btn_email_%s id=btn_email_%s value=1 onclick=\"javascript:toggleEmail();\"></td>", $userid, $userid);

        $text = array();
        $text[] = "<div id=\"popup_members\">";
        $text[] = "<table>";
        $text[] = "<tr><th>Home Phone</th><td>" . FormatPhone($user['home']) . "</td></tr>";
        $text[] = "<tr><th>Work Phone</th><td>" . FormatPhone($user['work']) . "</td></tr>";
        $text[] = "<tr><th>Cell Phone</th><td>" . FormatPhone($user['cell']) . "</td></tr>";
        $text[] = "<tr><th>Street</th><td>" . $user['street'] . "</td></tr>";
        $text[] = "<tr><th>City</th><td>" . $user['city'] . "</td></tr>";
        $text[] = "<tr><th>ZIP</th><td>" . $user['zip'] . "</td></tr>";
        $text[] = "</table>";
        $text[] = "</div>";

        $str = CVT_Str_to_Overlib(join("", $text));
        $cap = sprintf("Contact info for %s %s", $user['first'], $user['last']);

        echo "<td><a href=\"javascript:void(0);\"" .
        "onmouseover=\"return overlib('$str', CAPTION, '$cap', WIDTH, 300)\"" .
        "onmouseout=\"return nd();\">info</a></td>";

        if ($user['lastlogin'] == '0000-00-00 00:00:00') {
            $str = "never";
        } else {
            $diff = time() - strtotime($user['lastlogin']);
            $days = $diff / 60 / 60 / 24;
            $str = sprintf("%d days ago", $days);
        }
        echo "<td align=center>$str</td>";
        echo "</tr>";
    }

    echo "<tr>";
    echo "<td colspan=2>&nbsp;</td>";
    echo "<td><input type=button name=action value=Mail onclick=\"addAction('Mail');\"></td>";
    echo "<td><input type=button id=btn_all_email name=btn_all_email value=All onclick=\"javascript:toggleEmail('all');\"></td>";
    echo "<td colspan=2>&nbsp;</td>";
    echo "</tr>";

    echo "</table>";
    echo "</div>";

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerResetxx() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

//if logged in redirect to members page
    $user = (array_key_exists("user", $GLOBALS) ) ? $GLOBALS["user"] : "";
    if ($user && $user->is_logged_in()) {
        return;
    }

    if (empty($GLOBALS['gResetKey'])) {
        $GLOBALS['gResetKey'] = $_POST['key'];
    }
    $resetToken = hash('SHA256', $GLOBALS['gResetKey']);

    $query = 'SELECT resetToken, resetComplete FROM users WHERE resetToken = :token';
    $stmt = DoQuery($query, [':token' => $resetToken]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

//if no token from db then kill the page
    if (empty($row['resetToken'])) {
        $stop = 'Invalid token provided, please use the link provided in the reset email.';
    } elseif ($row['resetComplete'] == 1) {
        $stop = 'Your password has already been changed!';
    }
    if (isset($stop))
        echo "stop: [$stop]<br>";

//if form has been submitted process it
    if (array_key_exists('password', $_POST)) {

        if (!isset($_POST['password']) || !isset($_POST['passwordConfirm']))
            $error[] = 'Both Password fields are required to be entered';

        //basic validation
        if (strlen($_POST['password']) < 3) {
            $error[] = 'Password is too short.';
        }

        if (strlen($_POST['passwordConfirm']) < 3) {
            $error[] = 'Confirm password is too short.';
        }

        if ($_POST['password'] != $_POST['passwordConfirm']) {
            $error[] = 'Passwords do not match.';
        }

        //if no errors have been created carry on
        if (!isset($error)) {

            //hash the password
            $hashedpassword = $GLOBALS['user']->password_hash($_POST['password'], PASSWORD_BCRYPT);

            try {

                $query = "UPDATE users SET password = :hashedpassword, resetComplete = 1, active = 'YES'  WHERE resetToken = :token";
                DoQuery($query, array(
                    ':hashedpassword' => $hashedpassword,
                    ':token' => $row['resetToken']
                ));

                //redirect to index page
#                header('Location: index.php?action=resetAccount');
                $GLOBALS['gAction'] = 'password';
                return;

                //else catch the exception and show the error.
            } catch (PDOException $e) {
                $error[] = $e->getMessage();
            }
        }
    }
    ?>

    <div class="container center">

        <div class="row">

            <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">


                <?php
                if (isset($stop)) {

                    echo "<p class='bg-danger'>$stop</p>";
                } else {
                    ?>

                    <form role="form" method="post" action="" autocomplete="off">
                        <h2>Change Password</h2>
                        <hr>

                        <?php
                        //check for any errors
                        if (isset($error)) {
                            foreach ($error as $error) {
                                echo '<p class="bg-danger">' . $error . '</p>';
                            }
                        }

                        //check the action
                        if (array_key_exists('action', $_POST)) {
                            $val = $_POST['action'];
                        } elseif (array_key_exists('action', $_GET)) {
                            $val = $_GET['action'];
                        }
                        switch ($val) {
                            case 'active':
                                echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                                break;
                        }
                        ?>

                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="1">
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <input type="password" name="passwordConfirm" id="passwordConfirm" class="form-control input-lg" placeholder="Confirm Password" tabindex="1">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row">
                            <div class="col-xs-6 col-md-6">
                                <?php
                                $acts = array();
                                $acts[] = "setValue('from','" . __FUNCTION__ . "')";
                                $acts[] = "setValue('func','change')";
                                $acts[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
                                $acts[] = "setValue('key', '" . $GLOBALS['gResetKey'] . "')";
                                $acts[] = "addAction('password')";
                                echo sprintf("<input type=button onClick=\"%s\" id=update value='Change Password' class=\"btn btn-primary btn-block btn-lg\" tabindex=\"3\">", join(';', $acts));
                                ?>
                            </div>
                        </div>
                    </form>

    <?php } ?>
            </div>
        </div>


    </div>

    <?php
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerSettings() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $num_args = func_num_args();
    switch ($num_args) {
        case( 1 ):
            $userid = func_get_arg(0);
            $mode = "";
            break;

        case( 2 ):
            $userid = func_get_arg(0);
            $mode = func_get_arg(1);
            break;

        default:
            echo "Bad # of arguments ($num_args) to UserManagerSettings<br>";
            exit;
    }

    $stmt = DoQuery("SELECT * from `users` WHERE `id` = '$userid'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<input type=hidden name=from value=UserSettings$mode>";
    echo "<input type=hidden name=user_id id=userid value=$userid>";
    echo "<input type=hidden name=id id=id>";
    echo "<input type=hidden name=update_pass id=update_pass value=0>";

    echo sprintf("<h2>%s %s</h2>", $user['first'], $user['last']);
    echo "<div id=settings>";
    echo "<table>";

    echo "<tr>";
    echo "<th>Last Login</th>";
    $ts = strtotime($user['lastlogin']);
    echo sprintf("<td class=transp>%s</td>", date("Y, M j, g:i A", $ts));
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Username</th>";
    echo sprintf("<td><input type=text name=username value=\"%s\"></td>", $user['username']);
    echo "<th></th>";
    echo "</tr>";

    if ($gAccess >= $GLOBALS['gAccessLevels']['author']) {
        echo "<tr>";
        echo "<th>Last</th>";
        echo sprintf("<td><input type=text name=last value=\"%s\"></td>", $user['last']);
        echo "<th></th>";
        echo "</tr>";

        echo "<tr>";
        echo "<th>First</th>";
        echo sprintf("<td><input type=text name=first value=\"%s\"></td>", $user['first']);
        echo "<th></th>";
        echo "</tr>";

        echo "<tr>";
        echo "<th>Access</th>";
        echo "<td>";
        echo "<select name=access>";
        foreach ($GLOBALS['gAccessLevels'] as $level) {
            $opt = ( $user['access'] == $level ) ? "selected" : "";
            echo sprintf("<option value=%s $opt>%s</option>", $level, $GLOBALS['gAccessLevelToName'][$level]);
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<th>Active</th>";
        echo sprintf("<td><input type=text name=active value=\"%s\"></td>", $user['active']);
        echo "<th></th>";
        echo "</tr>";
    }

    $stmt = DoQuery("select * from contacts where id = $userid");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<tr>";
    echo "<th>Home Phone</th>";
    echo sprintf("<td><input type=text name=home value=\"%s\"></td>", FormatPhone($user['home']));
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Work Phone</th>";
    echo sprintf("<td><input type=text name=work value=\"%s\"></td>", FormatPhone($user['work']));
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Cell Phone</th>";
    echo sprintf("<td><input type=text name=cell value=\"%s\"></td>", FormatPhone($user['cell']));
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Street</th>";
    echo sprintf("<td><input type=text name=street value=\"%s\"></td>", $user['street']);
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>City</th>";
    echo sprintf("<td><input type=text name=city value=\"%s\"></td>", $user['city']);
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Zip</th>";
    echo sprintf("<td><input type=text name=zip value=\"%s\"></td>", $user['zip']);
    echo "<th></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Password</th>";
    echo "<td><input type=password id=newpassword1 name=newpassword1 onKeyUp=\"verifypwd(3);\"></td>";
    echo "<th align=left></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th>Confirm</th>";
    echo "<td><input type=password id=newpassword2 name=newpassword2 onKeyUp=\"verifypwd(4);\"></td>";
    echo "<th align=left><a id=\"pwdval\">&nbsp;</a></th>";
    echo "</tr>";

    echo "<tr>";
    echo "<th></th>";
    echo "<th align=center><input type=button class=btn id=userSettingsUpdate name=action onClick=\"mungepwd(); setValue( 'userid', '$userid'); addAction('update');\" value=Update></th>";
    echo "<th></th>";
    echo "</tr>";

    echo "</table>";
    echo "</div>";
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerUpdate() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    if (!isset($GLOBALS['gUserId'])) {
        $GLOBALS['gUserId'] = $id;
        $_SESSION['userid'] = $id;
    }

    $userid = $GLOBALS['gUserId'];

    if (!empty($_POST['update_pass'])) {
        $newpwd = $_POST['newpassword1'];
        $hash = password_hash($_POST['newpassword1'], PASSWORD_BCRYPT);
        $query = "update users set pwdchanged = now(), password = '$hash', resetToken = 'n/a' where id = '$id'";
        DoQuery($query);
        $GLOBALS['gPasswdChanged'] = date("Y-m-d H:i:s");
        EventLogRecord( [
        'type' => 'pwd change',
        'user_id' => $GLOBALS['gUserId'],
        'item' => 'n/a'
        ]);
        UserManager('load', $id);

        $GLOBALS['gAction'] = 'start';
    }

    $area = $_POST['area'];
    $func = $_POST['func'];

    logger("made it here. area: [$area], func: [$func]");

    if ($area == "xxdelete") {
        $id = $_POST['id'];
        $query = "delete from users where id = '$id'";
        DoQuery($query);

        $text = array();
        EventLogRecord( [
        'type' => 'user',
        'user_id' => $GLOBALS['gUserId'],
        'item' => sprintf("item = 'delete %s(%d)'", $_POST["u_${id}_username"], $id)
        ]);

        DoQuery("delete from access where id = '$id'");

        DoQuery("show tables like 'grades'");
        if ($GLOBALS['gPDO_num_rows']) {
            DoQuery("delete from grades where userid = '$id'");
        }
    }

    if ($area == "privileges") {
        if ($func == "add") {
            $acts = array();
            $acts[] = sprintf("name = '%s'", addslashes($_POST['p__0__name']));
            $acts[] = sprintf("level = '%d'", $_POST['p__0__level']);
            $val = isset($_POST['p__0__enabled']) ? 1 : 0;
            $acts[] = "enabled = '$val'";
            $query = "insert into privileges set " . join(',', $acts);
            DoQuery($query);

            EventLogRecord( [
                'type' => 'privilege',
                'user_id' => $userid,
                'item' => sprintf("item = 'add %s'", $_POST['p__0__name'])
                ]);
        }

        if ($func == "delete") {
            $id = $_POST['id'];
            $stmt = DoQuery("select * from privileges where id = '$id'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $query = "delete from privileges where id = '$id'";
            DoQuery($query);

            EventLogRecord( [
                'type' => 'privilege',
                'user_id' => $userid,
                'item' => sprintf("item = 'delete %s'", $row['name'], $id)
            ]);
        }

        if ($func == "update") {
            $done = array();
            $pids = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
            foreach ($pids as $pid) {
                if (!empty($pid)) {
                    if (array_key_exists($pid, $done))
                        continue;
                    $done[$pid] = 1;
                    $query = "select * from privileges where id = '$pid'";
                    $stmt = DoQuery($query);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $acts = array();

                    $tag = "p_${pid}_name";
                    if (strcmp($_POST[$tag], $row['name']))
                        $acts[] = "name = '" . addslashes($_POST[$tag]) . "'";

                    $tag = "p_${pid}_level";
                    if ($_POST[$tag] !== $row['level'])
                        $acts[] = "level = '" . $_POST[$tag] . "'";

                    $tag = "p_${pid}_enabled";
                    $val = isset($_POST[$tag]) ? 1 : 0;
                    if ($val !== $row['enabled'])
                        $acts[] = "enabled = '$val'";

                    if (count($acts)) {
                        $query = "update privileges set " . join(',', $acts) . " where id = '$pid'";
                        DoQuery($query);

                        EEventLogRecord( [
                            'type' => 'privilege',
                            'user_id' => $userid,
                            'item' => sprintf("item = 'update %s(%d), set %s'", $row['name'], $pid, addslashes(join(',', $acts)))
                            ]);
                    }
                }
            }
        }
    }


    if ($area == "users") {
        if ($func == "add") {
            $stmt = DoQuery("select max(id)+1 from users");
            list($uid) = $stmt->fetch(PDO::FETCH_NUM);

            $i = 0;
            $fields = $vals = [];

            $i++;
            $fields[] = "id = :v$i";
            $vals[":v$i"] = $uid;

            $i++;
            $fields[] = "username = :v$i";
            $vals[":v$i"] = $_POST["u__0__username"];

            $i++;
            $fields[] = "first = :v$i";
            $vals[":v$i"] = $_POST["u__0__first"];

            $i++;
            $fields[] = "last = :v$i";
            $vals[":v$i"] = $_POST["u__0__last"];

            $i++;
            $fields[] = "email = :v$i";
            $vals[":v$i"] = $_POST["u__0__email"];
            
            $i++;
            $fields[] = "password = :v$i";
            $vals[":v$i"] = substr(md5(uniqid(mt_rand(), true)), 0, 32);
            
            $i++;
            $fields[] = "resetToken = :v$i";
            $vals[":v$i"] = substr(md5(uniqid(mt_rand(), true)), 0, 32);
            
            $i++;
            $fields[] = "active = :v$i";
            $vals[":v$i"] = true;
            
            $i++;
            $fields[] = "lastlogin = :v$i";
            $vals[":v$i"] = '2010-01-01 00:00:00';
            
            DoQuery("insert into users set " . join(',', $fields), $vals);

            $i = 0;
            $fields = $vals = [];

            $i++;
            $fields[] = "id = :v$i";
            $vals[":v$i"] = $uid;

            $i++;
            $fields[] = "priv_id = :v$i";
            $vals[":v$i"] = $_POST["u__0__priv_id"];
            DoQuery("insert into access set " . join(',', $fields), $vals);

            $event = [];
            $event["type"] = "users";
            $event["user_id"] = $_SESSION['userid'];
            $str = sprintf("Created username <%s> for <%s %s>, email: <%s>", $_POST['u__0__username'], $_POST['u__0__first'], $_POST['u__0__last'], $_POST['u__0__email']);
            $event["item"] = $str;
            EventLog('record', $event);
        } elseif ($func == "delete") {
            $uid = $_POST['id'];
            DoQuery("delete from users where id = $uid");
            DoQuery("delete from access where id = $uid");
            EventLogRecord([
                'type' => 'user',
                'user_id' => $uid,
                'item' => sprintf("item = 'deleted user and access for id $uid'")
                ]);

            } elseif ($func == "update") {
            $updates = explode(",", $_POST['fields']);
            foreach ($updates as $str) {
                list( $u, $id, $field ) = explode("_", $str);
                logger("str: [$str]");
                $val = (array_key_exists($str, $_POST)) ? $_POST[$str] : 0;
                if ($field == 'priv_id') {
                    DoQuery("update access set $field = :v1 where id = $id", [":v1" => $val]);
                } elseif( preg_match( '/^Debug/', $field ) ) {
                    switch( $field ) {
                        case "DebugInLine":
                            $bit = 0;
                            break;
                        case "DebugErrorLog":
                            $bit = 1;
                            break;
                        case "DebugWindow":
                            $bit = 2;
                            break;
                        case "DebugHTML":
                            $bit = 3;
                            break;
                    }
                    $mask = 1 << $bit;
                    $stmt = DoQuery( "select debug from users where id = $id" );
                    list( $oldVal ) = $stmt->fetch( PDO::FETCH_NUM);
                    $newVal = $oldVal ^ $mask;
                    $str = sprintf( "debug(before): %d, field: %s, bit: %d, mask: %d, new val: %d",
                            $oldVal, $field, $bit, $mask, $newVal );
                    logger($str);
                    DoQuery("update users set debug = :v1 where id = $id", [":v1" => $newVal]);
                    $_SESSION['debug'] = $newVal;
                } else {
                    DoQuery("update users set $field = :v1 where id = $id", [":v1" => $val]);
                }
            }
        }
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerVerify() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $user = $GLOBALS['user'];
    $username = isset($_POST['username']) ? $_POST['username'] : NULL;
    $password = isset($_POST['password']) ? $_POST['password'] : NULL;
    if ($user->login($username, $password)) {
        Logger('* password match, continue');
        $GLOBALS['gAction'] = 'main';
        $GLOBALS['gMode'] = 'office';
        $GLOBALS['gUserName'] = $username;
        UserManager('load', $_SESSION['userid']);
    } else {
        $gError = [];
        if (empty($username)) {
            $gError[] = "var id = document.getElementById('username_error');";
            $gError[] = "id.innerHTML = 'Please enter a username';";
            $gError[] = "id.style.display = 'block';";
        } else {
            $gError[] = "var id = document.getElementById('username_error');";
            $gError[] = "id.innerHTML = 'Sorry, please try again';";
            $gError[] = "id.style.display = 'block';";
        }
        if (empty($password)) {
            $gError[] = "var id = document.getElementById('password_error');";
            $gError[] = "id.innerHTML = 'The password field can\'t be blank';";
            $gError[] = "id.style.display = 'block';";
        }
        $GLOBALS['gError'] = $gError;
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerVerifyXX() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $gError = array();
    $user = $GLOBALS['user'];
    $username = isset($_POST['username']) ? $_POST['username'] : NULL;
    $password = isset($_POST['password']) ? $_POST['password'] : NULL;

    if (empty($username)) {
        $gError[] = "debugger;";
        $gError[] = "var id = document.getElementById('username_error');";
        $gError[] = "id.innerHTML = 'Please enter a username';";
        $gError[] = "document.getElementById('username_div').style.display = 'block';";
    }
    if (!isset($password)) {
        $gError[] = "var id = document.getElementById('password_error');";
        $gError[] = "id.innerHTML = 'The password field can\'t be blank;";
        $gError[] = "document.getElementById('password_div').style.display = 'block';";
    }

    $GLOBALS['gAction'] = 'start';
    if ($user->isValidUsername($username)) {
        Logger('* valid username');
        if (!isset($password)) {
//            $gError[] = 'A password must be entered';
        }

        if ($user->login($username, $password)) {
            Logger('* password match, continue');
            $_SESSION['username'] = $username;
            $GLOBALS['gAction'] = 'main';
            $GLOBALS['gMode'] = 'office';
            $GLOBALS['gUserName'] = $username;
            UserManager('load', $_SESSION['userid']);
        } elseif (isset($_SESSION['disabled']) && $_SESSION['disabled']) {
            $str = "Your account is currently disabled, please contact " . $admin['email'];
            Logger($str);
 //           $gError[] = $str;
            $GLOBALS['gAction'] = 'login';
            $GLOBALS['gFunc'] = 'user_pass';
        } else {
            $str = "Wrong username/password or your account has not been activated.";
            Logger($str);
 //           $gError[] = $str;
            $GLOBALS['gAction'] = 'login';
            $GLOBALS['gFunc'] = 'user_pass';
        }
    } else {
        Logger('* invalid username');
        echo "<script type=\"text/javascript>";
 //       $gError[] = 'Usernames are required to be Alphanumeric, and between 3-16 characters long';
        $GLOBALS['gAction'] = 'login';
        $GLOBALS['gFunc'] = 'user_pass';
    }
  //  $GLOBALS['gError'] = $gError;
    if ($GLOBALS['gTrace']) {
        array_pop($GLOBALS['gFunction']);
    }
    return;
#===========================================================================

    $ok = 0;
    if ($GLOBALS['gUserVerified'] == 0) {
        $_SESSION['authenticated'] = 0;
        $GLOBALS['gAction'] = "start";
        if (empty($_POST['username']) && $_POST['bypass'] != 1) {
            $GLOBALS['gMessage1'] = "&nbsp;** Please enter your username";
            if ($GLOBALS['gTrace'])
                array_pop($GLOBALS['gFunction']);
            return;
        }

        if (!isset($_POST['userpass']) || $_POST['userpass'] == "empty") {
            $GLOBALS['gMessage2'] = "&nbsp;** Please enter your password";
            if ($GLOBALS['gTrace'])
                array_pop($GLOBALS['gFunction']);
            return;
        }

        $query = "select challenge from challenge_record";
        $query .= " where sess_id = '" . session_id() . "' and timestamp > " . time();
        $stmt = DoQuery($query);
        $c_array = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($_POST['username'])) {
            $query = "select userid, username, password from users where password = '" . $_POST['response'] . "'";
            $stmt = DoQuery($query);
            $ok = $GLOBALS['gPDO_num_rows'] > 0;
            if ($ok) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                UserManager('load', $user['userid']);
                UserManager('newpassword');
            }
        } else {
            $query = "select userid, username, password, pwdexpires from users where username = '" . $_POST['username'] . "'";
            $stmt = DoQuery($query);
            if ($GLOBALS['gPDO_num_rows'] > 0) {
                $now = date('Y-m-d H:i:s');
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $pass = ( strlen($user['password']) == 64 ) ? $user['password'] : SHA256::hash($user['password']);
                $response_string = strtolower($user['username']) . ':' . $pass . ':' . $c_array['challenge'];
                $expected_response = SHA256::hash($response_string);
                $ok = ( $_POST['response'] == $expected_response ) ? 1 : 0;
                if ($now > $user['pwdexpires']) {
                    echo "Your password has expired!  Click on:  Reset Password<br>";
                    $ok = 0;
                }
                if (!$ok) {
                    $GLOBALS['gMessage2'] = "&nbsp;** Invalid password";
                }
            } else {
                $ok = false;
                $GLOBALS['gMessage1'] = "&nbsp;** Invalid username";
            }
        }
        if ($ok > 0) {
            $_SESSION['authenticated'] = 1;
            $_SESSION['userid'] = $user['userid'];

            UserManager('load', $user['userid']);
            $ts = time();
            $expires = date('Y-m-d H:i:s', $ts + 60 * 60 * 24 * 60); # two months
            DoQuery("update users set lastlogin = now(), pwdexpires='$expires' where id = '" . $user['id'] . "'");
            EventLogRecord([
                'type' => 'login',
                'user_id' => $user['userid'],
                'item' => sprintf("item = '%s'", $_SERVER['HTTP_USER_AGENT'])
                ]);
            if ($GLOBALS['gPasswdChanged'] == '0000-00-00 00:00:00') {
                UserManagerPassword();
            }
            $GLOBALS['gAction'] = ( empty($GLOBALS['gEnabled']) || empty($GLOBALS['gActive']) ) ? "Inactive" : "Welcome";
        } else {
            $GLOBALS['gPDO_num_rows'] = 0;
            if (!empty($_POST['username'])) {
                $query = "select id from users where username = '" . $_POST['username'] . "'";
                DoQuery($query);
                if ($GLOBALS['gPDO_num_rows'] == 0) {
                    $GLOBALS['gMessage1'] = "&nbsp;** Invalid username: " . $_POST['username'];
                }
            } else {
                $GLOBALS['gMessage2'] = "&nbsp;** Password verification error.  Please try again";
            }
            $GLOBALS['gAction'] = "start";
        }
    } else {
        $GLOBALS['gAction'] = empty($gActive) ? "Inactive" : "Welcome";
    }
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

