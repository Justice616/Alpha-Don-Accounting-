<?php

require_once __DIR__ . '/../src/database.php';
require_once __DIR__ . '/../src/account.php';

Class User
{

    const TYPE_USER = 0;
    const TYPE_MANAGER = 1;
    const TYPE_ADMIN = 2;

    const STATE_PENDING = 0;
    const STATE_REJECTED = 1;
    const STATE_ACCEPTED = 2;

    const CREATE_OK = 0;
    const CREATE_EEXIST = 1;
    const CREATE_ERROR = 2;

    const LOGIN_OK = 0;
    const LOGIN_AUTH_FAILURE = 1;
    const LOGIN_EEXIST = 2;
    const LOGIN_ERROR = 3;

    const CAN_ACCOUNT_ADD = 0;
    const CAN_ACCOUNT_DELETE = 1;
    const CAN_ACCOUNT_EDIT = 2;
    const CAN_ACCOUNT_VIEW = 3;
    const CAN_ACCOUNT_SEARCH = 4;

    public static $path_depth = 0;

    public static function setPathDepth($depth) {
        self::$path_depth = $depth;
    }

    /* ------------------------------------- Helper Methods --------------------------------------------------------- */

    public static function redirect($url)
    {
        ob_start();
        header('Location: ' . str_repeat("../", self::$path_depth) . $url);
        ob_end_flush();
        die();
    }

    public static function refresh($nsec, $url)
    {
        header("Refresh: $nsec; url=" . str_repeat("../", self::$path_depth) . $url);
    }

    public static function permissibleUser($type, $do) {
        switch($type) {
            case self::TYPE_USER: {
                switch ($do) {
                    case self::CAN_ACCOUNT_ADD:
                        return false;
                    case self::CAN_ACCOUNT_DELETE:
                        return false;
                    case self::CAN_ACCOUNT_EDIT:
                        return false;
                    case self::CAN_ACCOUNT_SEARCH:
                        return true;
                    case self::CAN_ACCOUNT_VIEW:
                        return true;
                }
                break;
            }
            case self::TYPE_MANAGER: {
                switch ($do) {
                    case self::CAN_ACCOUNT_ADD:
                        return true;
                    case self::CAN_ACCOUNT_DELETE:
                        return true;
                    case self::CAN_ACCOUNT_EDIT:
                        return true;
                    case self::CAN_ACCOUNT_SEARCH:
                        return true;
                    case self::CAN_ACCOUNT_VIEW:
                        return true;
                }
                break;
            }
            case self::TYPE_ADMIN: {
                switch ($do) {
                    case self::CAN_ACCOUNT_ADD:
                        return true;
                    case self::CAN_ACCOUNT_DELETE:
                        return true;
                    case self::CAN_ACCOUNT_EDIT:
                        return true;
                    case self::CAN_ACCOUNT_SEARCH:
                        return true;
                    case self::CAN_ACCOUNT_VIEW:
                        return true;
                }
                break;
            }
        }
        return false;
    }

    /* ------------------------------------- Login Methods ---------------------------------------------------------- */

    public static function getUserTypes()
    {
        $pdo = Database::getInstance();
        $res = $pdo->query("select * from user_types order by id ASC");
        return $res->fetchAll(PDO::FETCH_CLASS);
    }

    public static function createUser($user, $email, $password, $type)
    {
        $pdo = Database::getInstance();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("insert into users (name, email, password, type) values (?, ?, ?, ?)");
        try {
            $stmt->execute([$user, $email, $hash, $type]);
            if ($stmt->rowCount() == 1) {
                return self::CREATE_OK;
            }
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                return self::CREATE_EEXIST;
            }
        }
        return self::CREATE_ERROR;
    }

    public static function userLogin($user, $password, &$data = NULL)
    {
        $pdo = Database::getInstance();
        $query = <<<EOF
select 
  u.id, u.name, u.email, u.password, u.type, t.title 
from 
  users u 
inner join 
  user_types t 
  on 
    u.type = t.id 
where u.name = ?
EOF;

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user]);

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch();
                $hash = $row['password'];

                if (password_verify($password, $hash)) {
                    if (isset($data)) {
                        $data = $row;
                    }
                    return self::LOGIN_OK;
                } else {
                    return self::LOGIN_AUTH_FAILURE;
                }
            } else {
                return self::LOGIN_EEXIST;
            }
        } catch (Exception $e) {
            return self::LOGIN_ERROR;
        }

        return self::LOGIN_ERROR;
    }

    /* -------------------------------- Session Helper Methods ------------------------------------------------------ */

    public static function getUserID()
    {
        return $_SESSION['id'];
    }

    public static function getUserName()
    {
        return $_SESSION['name'];
    }

    public static function getUserType()
    {
        return $_SESSION['type'];
    }

    public static function getUserTitle()
    {
        return $_SESSION['title'];
    }

    /* ------------------------------------- Session Methods -------------------------------------------------------- */

    public static function initiateSession($user)
    {

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['type'] = $user['type'];
        $_SESSION['title'] = $user['title'];
    }

    public static function destroySession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            return;
        }

        session_destroy();
        session_unset();
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['id']) === TRUE;
    }

    public static function route($arg = FALSE)
    {
        if (!self::isLoggedIn()) {
            self::redirect('login.php');
        }

        $permissions = array(
            'account_add' => User::CAN_ACCOUNT_ADD,
            'account_delete' => User::CAN_ACCOUNT_DELETE,
            'account_edit' => User::CAN_ACCOUNT_EDIT,
            'account_view' => User::CAN_ACCOUNT_VIEW,
            'account_search' => User::CAN_ACCOUNT_SEARCH,
        );

        if (gettype($arg) == "string" && array_key_exists($arg, $permissions)) {
            $ret = self::permissibleUser(self::getUserType(), $permissions[$arg]);
            if ($ret == FALSE) {
                die("you are not allowed to access this resource.");
            }
        } else {
            if ($arg === TRUE)
                return;

            self::redirect('home.php');
        }
    }

} // User
