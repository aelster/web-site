<?php

include('password.php');

class User extends Password {

    private $_db;

    function __construct($db) {
        parent::__construct();

        $this->_db = $db;
    }

    private function get_user_hash($username) {
        try {
            $stmt = DoQuery('SELECT * FROM users WHERE username = :username AND active=:active ',
                    ['username' => $username, 'active' => 'YES'] );
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo '<p class="bg-danger">' . $e->getMessage() . '</p>';
        }
    }

    public function isValidUsername($username) {
        if (strlen($username) < 3)
            return false;
        if (strlen($username) > 17)
            return false;
        if (!ctype_alnum($username))
            return false;
        return true;
    }

    public function login($username, $password) {
        if (!$this->isValidUsername($username))
            return false;
        if (strlen($password) < 3)
            return false;

        $row = $this->get_user_hash($username);

        if ($this->password_verify($password, $row['password']) == 1) {
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['disabled'] = $row['disabled'];
            $_SESSION['debug'] = $row['debug'];
            if( $_SESSION['disabled'] ) {
                $_SESSION['loggedin'] = true;
                return false;
            } else {
                $str = date('Y-m-d H:i:s');
                DoQuery( 'update users set lastlogin = :ll where userid = :id', array( ':ll' => $str, ':id' => $row['userid']));
                
                $_SESSION['loggedin'] = true;
                return true;
            }
        }
    }

    public function logout() {
        session_destroy();
    }

    public function is_logged_in() {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            return true;
        }
    }
}
