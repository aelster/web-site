<?php
global $gAccessLevel;
global $gAccessLevelEnabled;
global $gAccessLevels;
global $gAccessNameEnabled;
global $gAccessNameToId;
global $gAccessNameToLevel;
global $gActive;
global $gEnabled;
global $gMessage1;
global $gMessage2;
global $gNameFirst;
global $gNameLast;
global $gPasswdChanged;
global $gSupport;
global $gUserId;
global $gUserVerified;

function UserManager() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }    
    $area = func_get_arg(0);

    switch ($area) {
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

        case( 'resend' ):
            UserManagerResend();
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

        default:
            echo "Uh-oh:  Contact Andy regarding UserManager( $area )<br>";
            break;
    }
    
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
    if ($area == 'authorized')
        return $auth;
}

function UserManagerActivate() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

//collect values from the url
$userid = trim($_GET['x']);
$active = trim($_GET['y']);

//if id is number and the active token is not empty carry on
if(is_numeric($userid) && !empty($active)){

	//update users record set the active column to Yes where the memberID and active value match the ones provided in the array
	$query = "UPDATE users SET active = 'Yes' WHERE userid = :userid AND active = :active";
        $stmt = DoQuery( $query, [':userid' => $userid,':active' => $active ] );

        //if the row was updated redirect the user
	if($stmt->rowCount() == 1){

		//redirect to login page
            UserManagerLoad($userid);
            $GLOBALS['gAction'] = 'Start';
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
    $text[] = sprintf("insert into users set first = '%s'", $_POST['first']);
    $text[] = sprintf("last = '%s'", $_POST['last']);
    $text[] = sprintf("email = '%s'", $_POST['email']);
    $text[] = sprintf("username = '%s'", $_POST['username']);
    $text[] = sprintf("password = '%s'", md5(sprintf("%d", time())));
    $text[] = sprintf("active = '1'");
    $query = join(",", $text);
    DoQuery($query);
    $id = mysql_insert_id();

    if ($id) {
        $text = array();
        $text[] = "insert event_log set time=now()";
        $text[] = "type = 'control'";
        $text[] = sprintf("userid = '%d'", $GLOBALS['gUserId']);
        $text[] = sprintf("item = 'added user %s %s, username %s, id %d, e-mail %s'", $_POST['first'], $_POST['last'], $_POST['username'], $id, $_POST['email']);
        $query = join(",", $text);
        DoQuery($query);

        $query = "insert into access set userid = '$id', privid = '" . $_POST['access'] . "'";
        DoQuery($query);
    }

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerAuthorized($privilege) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = sprintf( "%s (%s)", __FUNCTION__, $privilege );
        Logger();
    }
    $level = $GLOBALS['gAccessLevel'];
#    printf( "Level: %d<br>", $level );
#    printf( "gAccessNameToLevel[%s]: %d<br>", $privilege, $GLOBALS['gAccessNameToLevel'][$privilege] );
    $ok = ( $level >= $GLOBALS['gAccessNameToLevel'][$privilege] ) ? 1 : 0;
#    printf( "  ok: [$ok]<br>" );
#    printf( "gAccessLevelEnabled[%s] = %d<br>", $level, $GLOBALS['gAccessLevelEnabled'][$level] );
    $ok = $ok && $GLOBALS['gAccessLevelEnabled'][$level];
    if ($GLOBALS['gTrace']) {
        if( $ok ) {
#            printf( "  %s is authorized<br>", $_SESSION['username'] );
        } else {
#            printf( "  %s is NOT authorized<br>", $_SESSION['username'] );
        }
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

    DoQuery("select * from users where userid = '$id'");
    $user = mysql_fetch_assoc($GLOBALS['mysql_result']);

    DoQuery("delete from users where userid = '$id'");

    $text = array();
    $text[] = "insert event_log set time=now()";
    $text[] = "type = 'control'";
    $text[] = sprintf("userid = '%d'", $GLOBALS['gUserId']);
    $text[] = sprintf("item = 'deleted user %s %s, username %s, e-mail %s'", $user['first'], $user['last'], $user['username'], $user['email']);
    $query = join(",", $text);
    DoQuery($query);

    DoQuery("delete from access where userid = '$id'");
    DoQuery("delete from grades where userid = '$id'");

    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerDisplay() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    echo "<div class=center>";
    
    echo "<h2>User Control</h2>";
    echo "<input type=hidden name=from value=Users>";
    echo sprintf("<input type=hidden name=userid value='%d'>", $GLOBALS['gUserId']);

    $acts = [
        "addAction('Back')"
        ];
    echo sprintf("<input type=button onClick=\"%s\" value=Back>", join(';', $acts));

    $acts = [
        "setValue('area','update')",
        "setValue('id', '" . $GLOBALS['gUserId'] . "')",
        "addAction('Update')"
        ];
    echo sprintf("<input type=button onClick=\"%s\" id=update value=Update>", join(';', $acts));

    $acts = [
        "setValue('area','display')",
        "addAction('New')"
    ];
    echo sprintf("<input type=button onClick=\"%s\" value='New User'>", join(';', $acts));
    echo "<br><br>";
    echo "</div>";
    
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

        $query = "select * from users, access where";
        $query .= " users.userid = access.userid and access.privid = :pid order by users.username ASC";
        $stmt = DoQuery($query, array(':pid' => $level));
        if ($stmt->rowCount() > 0) {
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
            echo "<th>Actions</th>";
            echo "</tr>";
            echo "</thead>";
        }
        echo "<tbody>";
        $j = 1;
        while ($usr = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $usr['userid'];
            $jscript = "onChange=\"addField('$id');toggleBgRed('update');\"";

            echo "<tr>\n";
            printf("  <td>$id</td>\n");
            printf("  <td><input type=text name=u_%d_username value=\"%s\" $jscript size=10></td>\n", $id, $usr['username']);
            printf("  <td><input type=text name=u_%d_first value=\"%s\" $jscript size=10></td>\n", $id, $usr['first']);
            printf("  <td><input type=text name=u_%d_last value=\"%s\" $jscript size=10></td>\n", $id, $usr['last']);
            printf("  <td><input type=text name=u_%d_email value=\"%s\" $jscript size=25></td>\n", $id, $usr['email']);

            printf("  <td><select name=u_%d_privid $jscript>", $id);
            foreach ($vprivs as $name => $privid) {
                if ($vlevels[$name] > $GLOBALS['gAccessLevel'])
                    continue;
                $opt = ( $usr['PrivId'] == $privid ) ? 'selected' : '';
                echo "<option value=$privid $opt>$name</option>";
            }
            echo "</select></td>\n";

            if ($usr['lastlogin'] == '0000-00-00 00:00:00')
                $str = "never";
            else {
                $diff = time() - strtotime($usr['lastlogin']);
                $days = $diff / 60 / 60 / 24;
                if( $days >= 1 ) {
                    $str = sprintf("%d days ago", $days);
                } else {
                    $str = $usr['lastlogin'];
                }
            }
            echo "  <td align=center>$str</td>\n";

            $checked = $usr['disabled'] ? "checked" : "";
            printf("  <td style='text-align: center;'><input type=checkbox name=u_%d_disabled value=1 $checked $jscript ></td>\n", $id);

            echo "  <td>";
            $acts = array();
            $acts[] = "setValue('area','delete')";
            $acts[] = "setValue('id', '$id')";
            $name = sprintf("%s (%s %s)", $usr['username'], $usr['first'], $usr['last']);
            $acts[] = "myConfirm('Are you sure you want to delete user $name')";
            echo sprintf("<input type=button onClick=\"%s\" id=update value=Del>", join(';', $acts));
            echo "</td>\n";

            echo "</tr>\n";
            $j++;
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }
    echo "</div>";
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerEdit() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }

    $id = $_POST['id'];
    $query = "select * from users where userid = '$id'";
    DoQuery($query);
    $user = mysql_fetch_assoc($GLOBALS['mysql_result']);

    echo sprintf("<input type=hidden name=userid value='%d']>", $GLOBALS['gUserId']);
    ?>
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
                <?php
                echo sprintf("<td><input type=text name=first size=20 value='%s'></td>", $user['first']);
                echo sprintf("<td><input type=text name=last size=20 value='%s'></td>", $user['last']);
                echo sprintf("<td><input type=text name=username size=20 value='%s'></td>", $user['username']);
                echo sprintf("<td><input type=text name=email size=20 value='%s'></td>", $user['email']);
                echo "<tr>";
                echo "</table>";
                echo "</div>";
                echo "<input type=submit name=action value=Back>";
                echo "<input type=button onclick=\"setValue( 'id', '$id'); setValue( 'btn_action', 'Update' ); addAction('Update');\" value=Update>";

                if ($GLOBALS['gTrace'])
                    array_pop($GLOBALS['gFunction']);
            }

            function UserManagerForgot() {
                if ($GLOBALS['gTrace']) {
                    $GLOBALS['gFunction'][] = __FUNCTION__;
                    Logger();
                }

                global $gMailAdmin, $gMailAdminName, $gMailLive;
            
                $area = $_POST['area'];
                $error = array();

                if ($area == 'check') {
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
                            
                            $subject = "Password Reset for " . $GLOBALS['gTitle'];
                            $recipients[$row['email']] = $row['first'] . " " . $row['last'];
 
$signature = [];
$signature[] = "";
$signature[] = "";
$signature[] = "<span style='font-family:george,serif; font-size:15px; font-weight:900;'><i>Andy Elster";
$signature[] = "Co-Founder, CFO, Board of Directors</i></span>";
$signature[] = "";
$signature[] = "<img src=\"cid:sigimg\" width='200' height='33'/>";
$signature[] = '<div style="margin-left:52px;"><font face="tahoma, sans-serif" color="#6aa84f" size="small">2625 N. Tustin Avenue';
$signature[] = "Santa Ana, CA 92705";
$signature[] = "Mobile: (949) 295-5443";
$signature[] = "https://irvinehebrewday.org/";
$signature[] = "</font>";
$signature[] = "</div>";

$body = "<p>Someone requested that the password be reset.</p>";
$body .= "<p>If this was a mistake, just ignore this email and nothing will happen.</p>";
$body .= "<p>To reset your password, visit the following address: <a href='" . $GLOBALS['gSourceCode'];
$body .= "?action=Reset&key=$token'>" . $GLOBALS['gSourceCode'] . "</a></p>";
$body .= "<br>" . join('<br>', $signature);

            unset($mail);
            $mail = MyMailerNew();
            try {
                //Receipients
                $mail->setFrom($gMailAdmin, $gMailAdminName);
                if ($gMailLive) {
                    foreach ($recipients as $email => $name) {
                        $mail->addAddress($email, $name);
                    }
                    $mail->AddCC($gMailAdmin, $gMailAdminName);
                } else {
                    $mail->addAddress($gMailAdmin, $gMailAdminName);
                }
                //Attachments
                $mail->AddEmbeddedImage('assets/SignatureImage.png', 'sigimg', 'assets/SignatureImage.png');

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
                ?>
            <div class="container">

                <div class="row">

                    <div class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3">
                        <form role="form" method="post" action="" autocomplete="off">
                            <h2>Reset Password</h2>
                            <p><a href='<?php $GLOBALS['gSourceCode'] ?>'>Back to login page</a></p>
                            <hr>

                            <?php
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
                            ?>

                            <div class="form-group">
                                <input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email" value="" tabindex="1">
                            </div>

                            <hr>
                            <div class="row">
                                <div class="col-xs-6 col-md-6">
                                    <?php
                                    $acts = array();
                                    $acts[] = "setValue('area','check')";
                                    $acts[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
                                    $acts[] = "addAction('forgot')";
                                    echo sprintf("<input type=button onClick=\"%s\" id=update value='Send Reset Link' class=\"btn btn-primary btn-block btn-lg\" tabindex=\"2\">", join(';', $acts));
                                    ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


            </div>

            <?php
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
                DoQuery("select name from privileges where level = '$level'");
                list( $name ) = mysql_fetch_array($GLOBALS['mysql_result']);
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

function UserManagerLoad($userid) {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = sprintf( "%s (%d)", __FUNCTION__, $userid );
        Logger();
    }

    $stmt = DoQuery('SELECT * from users where userid = :uid', array(':uid' => $userid));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $GLOBALS['gUserId'] = $userid;
    $GLOBALS['gNameFirst'] = $row['first'];
    $GLOBALS['gNameLast'] = $row['last'];
    $GLOBALS['gPasswdChanged'] = $row['pwdchanged'];
    $GLOBALS['gUserVerified'] = 1;
    $GLOBALS['gUserName'] = $row['username'];
    $GLOBALS['gLastLogin'] = $row['lastlogin'];
    $GLOBALS['gActive'] = $row['active'];
    $GLOBALS['gDebug'] = $row['debug'];

    $query = 'select privileges.level, privileges.enabled from privileges, access';
    $query .= ' where access.privid = privileges.id and access.userid = :uid';
    $stmt2 = DoQuery($query, array(':uid' => $userid));
    list( $level, $enabled ) = $stmt2->fetch(PDO::FETCH_NUM);
    $GLOBALS['gAccessLevel'] = $level;
    $GLOBALS['gEnabled'] = $enabled;
    $_SESSION['username'] = $row['username'];
    $_SESSION['level'] = $level;
    
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
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
    $jsx[] = "setValue('area','display')";
    $jsx[] = "addAction('forgot')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
    ?>
<div class=center>
    <div class="row" style="margin-left: 25%; width:50%;">
        <div class="form-group">
            <h2>Please Login</h2>
            <input type=button <?php echo $js?> class="form-control input-lg" value="Forgot your password?" style="height:40px;">
    <?php
    
    //check for any errors
    if (isset($GLOBALS['gError'])) {
        foreach ($GLOBALS['gError'] as $error) {
            echo '<h2 class="bg-danger">' . $error . '</h2>';
        }
    }
    
    if (isset($GLOBALS['gArea'])) {

        //check the action
        switch ($GLOBALS['gArea']) {
            case 'active':
                echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                break;
            case 'check':
                echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
                break;
            case 'resetAccount':
                echo "<h2 class='bg-success'>Password changed, you may now login.</h2>";
                break;
        }
    }
    ?>
        </div>
        <hr>
        <div class="form-group">
            <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" 
                   value="<?php
    if (isset($gError)) {
        echo htmlspecialchars($_POST['username'], ENT_QUOTES);
    }
    $jsx = array();
    $jsx[] = "addAction('Login')";
    $js = sprintf("onClick=\"%s\"", join(';', $jsx));
   ?>" tabindex="1">
                            </div>

                            <div class="form-group">
                                <input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
                            </div>


                            <hr>
                                <div class="form-group">
                                    <input type=button value="Login" <?php echo $js?> class="btn btn-primary btn-block btn-lg" tabindex="5">
                                </div>
                        </div>
                    </div>



                </div>
    <?php
    if ($GLOBALS['gTrace'])
        array_pop($GLOBALS['gFunction']);
}

function UserManagerNew() {
    if ($GLOBALS['gTrace']) {
        $GLOBALS['gFunction'][] = __FUNCTION__;
        Logger();
    }
    $user = $GLOBALS['user'];
    $area = filter_input( INPUT_POST, 'area' );
    if( $area == 'verify') {
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

            try {
                $stmt = DoQuery( 'select * from users' );
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

                $id = $GLOBALS['gDb']->lastInsertId('userid');
                if( $num_users == 0 ) {
                    $stmt = DoQuery( 'select max(level) from privileges' ); # first user is privileged
                } else {
                    $stmt = DoQuery( 'select min(level) from privileges' ); # all others minimal at first
                }
                list( $level ) = $stmt->fetch(PDO::FETCH_NUM);
                $stmt = DoQuery('select id from privileges where level = :level', [':level' => $level ] );
                list( $pid ) = $stmt->fetch(PDO::FETCH_NUM);
                DoQuery('insert into access set UserId = :uid, PrivId = :pid', [ ':uid' => $id, ':pid' => $pid]);

                //send email
                $to = $_POST['email'];
                $subject = "Registration Confirmation for " . $GLOBALS['gTitle'];
                $body = "<p>Thank you for registering at " . $GLOBALS['gTitle'] . ".</p>";
                $body .= "<p>To activate your account, please click on this link: ";
                $body .= "<a href='" . $_SERVER['HTTP_REFERER'] . "?action=activate&x=$id&y=$activasion'>";
                $body .= "activate</a></p>";
                $body .= "<p>Regards Site Admin</p>";

                $mail = MyMailerNew();

                $mail->setFrom(SITEEMAIL);
                $mail->addAddress($to);
                $mail->subject = $subject;
                $mail->body = $body;
            if (!$mail->send()) {
                $err = 'Message could not be sent.';
                $err .= 'Mailer Error: ' . $mail->ErrorInfo;
                echo $err;
            }

                //redirect to index page
                $GLOBALS['gAction'] = 'Main';
                $GLOBALS['func'] = 'users';
                $action = 'joined';

                //else catch the exception and show the error.
            } catch (PDOException $e) {
                $error[] = $e->getMessage();
            }
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
				if(isset($error)){
					foreach($error as $error){
						echo '<p class="bg-danger">'.$error.'</p>';
					}
				}

				//if action is joined show sucess
				if(isset($action) && $action == 'joined'){
					echo "<h2 class='bg-success'>Registration successful, please check your email to activate your account.</h2>";
                                        $acts = [
        "setValue('from','" . __FUNCTION__ . "')",
        "addAction('Back')"
    ];
    echo sprintf("<input type=button onClick=\"%s\" value='Back' class='btn btn-primary btn-block btn-lg' tabindex='7'>", join(';', $acts));
				} else {
				?>

				<div class="form-group">
					<input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['username'], ENT_QUOTES); } ?>" tabindex="1">
				</div>
                                				<div class="row">
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="firstName" id="firstName" class="form-control input-lg" placeholder="First Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['firstName'], ENT_QUOTES); } ?>" tabindex="2">
						</div>
					</div>
					<div class="col-xs-6 col-sm-6 col-md-6">
						<div class="form-group">
							<input type="text" name="lastName" id="lastName" class="form-control input-lg" placeholder="Last Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['lastName'], ENT_QUOTES); } ?>" tabindex="3">
						</div>
					</div>
				</div>

				<div class="form-group">
					<input type="email" name="email" id="email" class="form-control input-lg" placeholder="Email Address" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['email'], ENT_QUOTES); } ?>" tabindex="4">
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
            ?>
            <input type=submit name=action value=Continue>
            </div>
    </div>
            <?php
            if ($GLOBALS['gTrace'])
                array_pop($GLOBALS['gFunction']);
        }

        function UserManagerPassword() {
            if ($GLOBALS['gTrace']) {
                $GLOBALS['gFunction'][] = __FUNCTION__;
                Logger();
            }

            $id = $GLOBALS['gUserId'];
            DoQuery("select username from users where userid = '$id'");
            list( $username ) = mysql_fetch_array($GLOBALS['mysql_result']);
            $disabled = empty($username) ? "" : "disabled";
            ?>
            <div align=center>
                <div style="width:5in">
                    <br>
                    You will need to select a username if it is blank<br>
                    You will now need to select a new password.<br>
                    The password is secure and encrypted and never transmitted or stored in clear text.<br>

                    The UPDATE button will be activated once your password, entered twice, has been verified for a match.
                    <br><br>
                </div>
                <input type=hidden name=from value=UserManagerPassword>
                <input type=hidden name=userid id=userid value=<?php echo $id ?>>
                <input type=hidden name=id id=id value=<?php echo $id ?>>
                <input type=hidden name=update_pass value=1>
                <input type=hidden name=nobypass value=1>
                <table class=norm>
                    <tr>
                        <th class=norm>Username</th>
                        <td><input type=text name=username id=username size=20 <?php echo "value=\"$username\" $disabled >" ?></td>
                    </tr>
                    <tr>
                        <th class=norm>Password
                        <td class=norm><input type=password name=newpassword1 id=newpassword1 onKeyUp="verifypwd(1);" value="oneoneone" size=20>
                    </tr>
                    <tr>
                        <th class=norm>One more time
                        <td class=norm><input type=password name=newpassword2 id=newpassword2 onKeyUp="verifypwd(2);" value="twotwotwo" size=20>
                    </tr>
                </table>
                <br>
                <a id=pwdval>&nbsp;</a>
                <br><br>
                <?php
                $acts = array();
                $acts[] = "mungepwd()";
                $acts[] = "setValue('area','new_pass')";
                $acts[] = "addAction('Update')";
                $click = "onClick=\"" . join(';', $acts) . "\"";
                ?>
                <input type=button id=userSettingsUpdate name=userSettingsUpdate disabled <?php echo $click ?> value=Update></th>
                <?php
                if ($GLOBALS['gTrace'])
                    array_pop($GLOBALS['gFunction']);
                exit;
            }

            function UserManagerPrivileges() {
                if ($GLOBALS['gTrace']) {
                    $GLOBALS['gFunction'][] = __FUNCTION__;
                    Logger();
                }
                ?>
                <div class="center">
                <h2>Privilege Control</h2>
                <input type=hidden name=from value=UserManagerPrivileges>
                <input type=hidden name=userid id=userid>
                <?php
                $acts = array();
                $acts[] = "setValue('func','display')";
                $acts[] = "addAction('Back')";
                echo sprintf("<input type=button onClick=\"%s\" value=Back>", join(';', $acts));

                $acts = array();
                $acts[] = "setValue('area','privileges')";
                $acts[] = "setValue('func','modify')";
                $acts[] = "addAction('Update')";
                echo sprintf("<input type=button onClick=\"%s\" id=update value=Update>", join(';', $acts));

                echo "<br><br>";
                echo "</div>";
                
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
                    $acts[] = "myConfirm('$msg')";
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
                $acts[] = "addAction('Update')";
                echo sprintf("<td><input type=button onClick=\"%s\" value=Add></td>", join(';', $acts));

                echo "</tr>";
                echo "</table>";
                echo "</div>";
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

                DoQuery($query);

                while ($user = mysql_fetch_assoc($GLOBALS['mysql_result'])) {
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

            function UserManagerReset() {
                if ($GLOBALS['gTrace']) {
                    $GLOBALS['gFunction'][] = __FUNCTION__;
                    Logger();
                }
                error_log('in reset');
//if logged in redirect to members page
                if ($GLOBALS['user']->is_logged_in()) {
                    error_log('is_logged_in');
                    return;
                }
                error_log('not logged in');
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
    if (array_key_exists('password', $_POST) ) {

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
                        $GLOBALS['gAction'] = 'Start';
                        $GLOBALS['gArea'] = 'active';
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
                                    switch ($_GET['action']) {
                                        case 'active':
                                            echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
                                            break;
                                        case 'reset':
                                            echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
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
                                            $acts[] = "setValue('area','Reset')";
                                            $acts[] = "setValue('id', '" . $GLOBALS['gUserId'] . "')";
                                            $acts[] = "setValue('key', '" . $GLOBALS['gResetKey'] . "')";
                                            $acts[] = "addAction('Reset')";
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

                DoQuery("SELECT * from `users` WHERE `userid` = '$userid'");
                $user = mysql_fetch_assoc($GLOBALS['mysql_result']);

                echo "<input type=hidden name=from value=UserSettings$mode>";
                echo "<input type=hidden name=userid id=userid value=$userid>";
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

                DoQuery("select * from contacts where id = $userid");
                $user = mysql_fetch_assoc($GLOBALS['mysql_result']);

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
                echo "<th align=center><input type=button class=btn id=userSettingsUpdate name=action onClick=\"mungepwd(); setValue( 'userid', '$userid'); addAction('Update');\" value=Update></th>";
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
                    $query = "update users set pwdchanged = now(), password = '$newpwd' where userid = '$id'";
                    DoQuery($query);
                    $GLOBALS['gPasswdChanged'] = date("Y-m-d H:i:s");
                    unset($text);
                    $text[] = "insert event_log set time=now()";
                    $text[] = "type = 'pwd change'";
                    $text[] = "userid = '$userid'";
                    $text[] = "item = 'n/a'";
                    $query = join(",", $text);
                    DoQuery($query);

                    $GLOBALS['gAction'] = 'Main';
                }

                $area = $_POST['area'];
                $func = $_POST['func'];

                if ($area == "add") {
                    $uname = addslashes($_POST['username']);

                    $acts = array();
                    $acts[] = sprintf("username = '%s'", $uname);
                    $acts[] = sprintf("last = '%s'", addslashes($_POST['last']));
                    $acts[] = sprintf("first = '%s'", addslashes($_POST['first']));
                    $acts[] = sprintf("email = '%s'", addslashes($_POST['email']));
                    $acts[] = sprintf("password = '%s'", md5(sprintf("%d", time())));
                    $acts[] = sprintf("disabled = '0'");
                    $query = "insert into users set " . join(',', $acts);
                    DoQuery($query);
                    $uid = mysql_insert_id();

                    $text = array();
                    $text[] = "insert event_log set time=now()";
                    $text[] = "type = 'user'";
                    $text[] = "userid = '$id'";
                    $text[] = sprintf("item = 'add %s(%d), set %s'", $uname, $uid, addslashes(join(',', $acts)));
                    $query = join(',', $text);
                    DoQuery($query);

                    $acc = $_POST['privid'];
                    $acts = array();
                    $acts[] = "userid = '$uid'";
                    $acts[] = "privid = '$acc'";
                    $query = "insert into access set " . join(',', $acts);
                    DoQuery($query);

                    $text = array();
                    $text[] = "insert event_log set time=now()";
                    $text[] = "type = 'access'";
                    $text[] = "userid = '$id'";
                    $text[] = sprintf("item = 'update %s(%d), set %s'", $uname, $uid, addslashes(join(',', $acts)));
                    $query = join(',', $text);
                    DoQuery($query);
                }

                if ($area == "delete") {
                    $id = $_POST['id'];
                    $query = "delete from users where userid = '$id'";
                    DoQuery($query);

                    $text = array();
                    $text[] = "insert event_log set time=now()";
                    $text[] = "type = 'user'";
                    $text[] = "userid = '$userid'";
                    $text[] = sprintf("item = 'delete %s(%d)'", $_POST["u_${id}_username"], $id);
                    $query = join(',', $text);
                    DoQuery($query);

                    DoQuery("delete from access where userid = '$id'");

                    DoQuery("show tables like 'grades'");
                    if ($GLOBALS['mysql_numrows']) {
                        DoQuery("delete from grades where userid = '$id'");
                    }
                }

                if ($area == "privileges") {
                    if ($func == "add") {
                        $acts = array();
                        $acts[] = sprintf("name = '%s'", addslashes($_POST['p_0_name']));
                        $acts[] = sprintf("level = '%d'", $_POST['p_0_level']);
                        $val = isset($_POST['p_0_enabled']) ? 1 : 0;
                        $acts[] = "enabled = '$val'";
                        $query = "insert into privileges set " . join(',', $acts);
                        DoQuery($query);

                        $text = array();
                        $text[] = "insert event_log set time=now()";
                        $text[] = "type = 'privilege'";
                        $text[] = "userid = '$userid'";
                        $text[] = sprintf("item = 'add %s'", $_POST['p_0_name']);
                        $query = join(',', $text);
                        DoQuery($query);
                    }

                    if ($func == "delete") {
                        $id = $_POST['id'];
                        $stmt = DoQuery("select * from privileges where id = '$id'");
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);

                        $query = "delete from privileges where id = '$id'";
                        DoQuery($query);

                        $text = array();
                        $text[] = "insert event_log set time=now()";
                        $text[] = "type = 'privilege'";
                        $text[] = "userid = '$userid'";
                        $text[] = sprintf("item = 'delete %s'", $row['name'], $id);
                        $query = join(',', $text);
                        DoQuery($query);
                    }

                    if ($func == "modify") {
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

                                    $text = array();
                                    $text[] = "insert event_log set time=now()";
                                    $text[] = "type = 'privilege'";
                                    $text[] = "userid = '$userid'";
                                    $text[] = sprintf("item = 'update %s(%d), set %s'", $row['name'], $pid, addslashes(join(',', $acts)));
                                    $query = join(',', $text);
                                    DoQuery($query);
                                }
                            }
                        }
                    }
                }


                if ($area == "update") {
                    $done = array();
                    $uids = preg_split('/,/', $_POST['fields'], NULL, PREG_SPLIT_NO_EMPTY);
                    foreach ($uids as $uid) {
                        if (!empty($uid)) {
                            if (array_key_exists($uid, $done))
                                continue;
                            $done[$uid] = 1;
                            $query = "select * from users where userid = :uid";
                            $stmt = DoQuery($query, array(':uid' => $uid));
                            $user = $stmt->fetch(PDO::FETCH_ASSOC);

                            $acts = array();

                            $tag = "u_${uid}_first";
                            if (strcmp($_POST[$tag], $user['first']))
                                $acts[] = "first = '" . addslashes($_POST[$tag]) . "'";

                            $tag = "u_${uid}_last";
                            if (strcmp($_POST[$tag], $user['last']))
                                $acts[] = "last = '" . addslashes($_POST[$tag]) . "'";

                            $tag = "u_${uid}_username";
                            if (strcmp($_POST[$tag], $user['username']))
                                $acts[] = "username = '" . addslashes($_POST[$tag]) . "'";

                            $tag = "u_${uid}_email";
                            if (strcmp($_POST[$tag], $user['email']))
                                $acts[] = "email = '" . addslashes($_POST[$tag]) . "'";

                            $tag = "u_${uid}_disabled";
                            $val = isset($_POST[$tag]) ? 1 : 0;
                            if ($val != $user['disabled'])
                                $acts[] = "disabled = '${val}'";

                            if (count($acts)) {
                                $query = "update users set " . join(',', $acts) . " where userid = '$uid'";
                                DoQuery($query);
                                if ($GLOBALS['gPDO_num_rows'] == 0) {
                                    $acts = array();
                                    foreach (array('first', 'last', 'email', 'username') as $fld) {
                                        $tag = sprintf("u_%d_%s", $uid, $fld);
                                        $acts[] = sprintf("%s = '%s'", $fld, addslashes($_POST[$tag]));
                                    }
                                    $query = "insert into users set " . join(',', $acts);
                                    DoQuery($query);

                                    $tag = sprintf("u_%d_%s", $uid, 'privid');
                                    $acc = $_POST[$tag];
                                    $acts = array();
                                    $acts[] = "userid = '$uid'";
                                    $acts[] = "privid = '$acc'";
                                    $query = "insert into access set " . join(',', $acts);
                                    DoQuery($query);
                                }

                                $text = array();
                                $text[] = "insert event_log set time=now()";
                                $text[] = "type = 'user'";
                                $text[] = "userid = '$userid'";
                                $text[] = sprintf("item = 'update %s(%d), set %s'", $user['username'], $uid, addslashes(join(',', $acts)));
                                $query = join(',', $text);
                                DoQuery($query);
                            }

                            $query = "select * from access where userid = '$uid'";
                            $stmt = DoQuery($query);
                            $access = $stmt->fetch(PDO::FETCH_ASSOC);

                            $tag = "u_${uid}_privid";
                            if ($access['PrivId'] !== $_POST[$tag]) {
                                $query = sprintf("update access set privid = '%s' where userid = '%s'", $_POST[$tag], $uid);
                                DoQuery($query);

                                $text = array();
                                $text[] = "insert event_log set time=now()";
                                $text[] = "type = 'user'";
                                $text[] = "userid = '$userid'";
                                $text[] = sprintf("item = 'update %s(%d), set privid = %s'", $user['username'], $uid, $_POST[$tag]);
                                $query = join(',', $text);
                                DoQuery($query);
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
                $gError = array();
                $user = $GLOBALS['user'];
                $username = isset($_POST['username']) ? $_POST['username'] : NULL;
                $password = isset($_POST['password']) ? $_POST['password'] : NULL;

                if (!isset($username))
                    $gError[] = "Please fill out all fields";
                if (!isset($password))
                    $gError[] = "Please fill out all fields";

                $GLOBALS['gAction'] = 'Start';
                if ($user->isValidUsername($username)) {
                    MyDebug('* valid username');
                    if (!isset($password)) {
                        $gError[] = 'A password must be entered';
                    }

                    if ($user->login($username, $password)) {
                        $_SESSION['username'] = $username;
                        $GLOBALS['gAction'] = 'Main';
                        $GLOBALS['gUserName'] = $username;
                        UserManager('load', $_SESSION['userid']);
                    } elseif( isset($_SESSION['disabled'] ) && $_SESSION['disabled'] ) {
                        $gError[] = "Your account is currently disabled, please contact " . SITEEMAIL;
                    } else {
                        $gError[] = 'Wrong username or password or your account has not been activated.';
                    }
                } else {
                    $gError[] = 'Usernames are required to be Alphanumeric, and between 3-16 characters long';
                }
                $GLOBALS['gError'] = $gError;
                if ($GLOBALS['gTrace']) {
                    array_pop($GLOBALS['gFunction']);
                }
                return;
#===========================================================================

                $ok = 0;
                if ($GLOBALS['gUserVerified'] == 0) {
                    $_SESSION['authenticated'] = 0;
                    $GLOBALS['gAction'] = "Start";
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
                    DoQuery($query);
                    $c_array = mysql_fetch_assoc($GLOBALS['mysql_result']);
                    if (empty($_POST['username'])) {
                        $query = "select userid, username, password from users where password = '" . $_POST['response'] . "'";
                        DoQuery($query);
                        $ok = $GLOBALS['mysql_numrows'] > 0;
                        if ($ok) {
                            $user = mysql_fetch_assoc($GLOBALS['mysql_result']);
                            UserManager('load', $user['userid']);
                            UserManager('newpassword');
                        }
                    } else {
                        $query = "select userid, username, password, pwdexpires from users where username = '" . $_POST['username'] . "'";
                        DoQuery($query);
                        if ($GLOBALS['mysql_numrows'] > 0) {
                            $now = date('Y-m-d H:i:s');
                            $user = mysql_fetch_assoc($GLOBALS['mysql_result']);
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
                        DoQuery("update users set lastlogin = now(), pwdexpires='$expires' where userid = '" . $user['userid'] . "'");
                        $text = array();
                        $text[] = "insert event_log set time=now()";
                        $text[] = "type = 'login'";
                        $text[] = sprintf("userid = '%d'", $user['userid']);
                        $text[] = sprintf("item = '%s'", $_SERVER['HTTP_USER_AGENT']);
                        $query = join(",", $text);
                        DoQuery($query);
                        if ($GLOBALS['gPasswdChanged'] == '0000-00-00 00:00:00') {
                            UserManagerPassword();
                        }
                        $GLOBALS['gAction'] = ( empty($GLOBALS['gEnabled']) || empty($GLOBALS['gActive']) ) ? "Inactive" : "Welcome";
                    } else {
                        $GLOBALS['mysql_numrows'] = 0;
                        if (!empty($_POST['username'])) {
                            $query = "select userid from users where username = '" . $_POST['username'] . "'";
                            DoQuery($query);
                            if ($GLOBALS['mysql_numrows'] == 0) {
                                $GLOBALS['gMessage1'] = "&nbsp;** Invalid username: " . $_POST['username'];
                            }
                        } else {
                            $GLOBALS['gMessage2'] = "&nbsp;** Password verification error.  Please try again";
                        }
                        $GLOBALS['gAction'] = "Start";
                    }
                } else {
                    $GLOBALS['gAction'] = empty($gActive) ? "Inactive" : "Welcome";
                }
                if ($GLOBALS['gTrace'])
                    array_pop($GLOBALS['gFunction']);
            }
            