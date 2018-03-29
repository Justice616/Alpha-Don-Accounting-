<?php

session_start();

require_once __DIR__ . '/src/template.php';
require_once __DIR__ . '/src/user.php';

if (User::isLoggedIn())
    User::route();

$msg = "";
$err = "";
if (isset($_POST['username'])) {
    $user = $_POST['username'];
    $password = $_POST['password'];

    $data = array();
    $ret = User::userLogin($user, $password, $data);

    if ($ret == User::LOGIN_OK) {
        $msg = "Login success!";
        User::initiateSession($data);
        User::route();
    } else if ($ret == User::LOGIN_EEXIST) {
        $err = "User does not exist!";
    } else if ($ret == User::LOGIN_AUTH_FAILURE) {
        $err = "Authentication failure.";
    } else {
        $err = "Something went wrong. Please try again later.";
    }

}

?>
<!doctype html>
<html lang="en">
<head>
    <?=Template::header()?>
    <style>
        body {
            padding-top: 4em;
        }
    </style>

    <title><?=Template::title("Login")?></title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">

                <form action="login.php" method="post">

                    <img class="img-fluid" src="public/images/logo.png" alt="<?=Template::COMPANY_NAME?>">

                    <div class="form-group">
                        <label for="username" class="bmd-label-floating">Username</label>
                        <input id="username" type="text" class="form-control" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="bmd-label-floating">Password</label>
                        <input id="password" type="password" class="form-control" name="password" required>
                    </div>

                    <?php if ($msg != "") { ?>
                        <div class="alert alert-success" role="alert"><?=$msg?></div>
                    <?php } ?>
                    <?php if ($err != "") { ?>
                        <div class="alert alert-danger" role="alert"><?=$err?></div>
                    <?php } ?>

                    <div class="form-group text-center">
                        <a class="btn btn-primary btn-outline-secondary" href="signup.php">Signup</a>
                        <button type="submit" class="btn btn-primary btn-outline-primary">Login</button>
                    </div>


                </form>


            </div>
        </div>
    </div>


    <?=Template::footer()?>
</body>
</html>
