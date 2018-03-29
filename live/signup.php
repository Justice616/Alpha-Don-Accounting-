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
    $email = $_POST['email'];
    $password = $_POST['password'];
    $type = $_POST['usertype'];

    $ret = User::createUser($user, $email, $password, $type);
    if ($ret == User::CREATE_OK) {
        $msg = "User has been created!";
    } else if ($ret == User::CREATE_EEXIST) {
        $err = "Username already taken!";
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

    <title><?=Template::title("Signup")?></title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 col-xl-4">

                <form action="signup.php" method="post" onsubmit="return validateForm()">

                    <img class="img-fluid" src="public/images/logo.png" alt="<?=Template::COMPANY_NAME?>">

                    <div class="form-group">
                        <label for="username" class="bmd-label-floating">Username</label>
                        <input id="username" type="text" class="form-control" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="bmd-label-floating">Email</label>
                        <input id="email" type="email" class="form-control" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="bmd-label-floating">Password</label>
                        <input id="password" type="password" class="form-control" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password" class="bmd-label-floating">Confirm Password</label>
                        <input id="confirm-password" type="password" class="form-control" required>
                        <div class="invalid-feedback">
                            Passwords do not match.
                        </div>
                    </div>

                    <div class="form-group">
                        <select class="custom-select" name="usertype">
                            <?php foreach (User::getUserTypes() as $type) { ?>
                                <option value="<?=$type->id?>"><?=$type->title?></option>
                            <?php } // foreach UserTypes() as $type ?>
                        </select>
                    </div>

                    <?php if ($msg != "") { ?>
                        <div class="alert alert-success" role="alert"><?=$msg?></div>
                    <?php } ?>
                    <?php if ($err != "") { ?>
                        <div class="alert alert-danger" role="alert"><?=$err?></div>
                    <?php } ?>

                    <div class="form-group text-center">
                        <a class="btn btn-primary btn-outline-secondary" href="login.php">Go Back</a>
                        <button type="submit" class="btn btn-primary btn-outline-primary">Signup</button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <?=Template::footer()?>
    <script type="text/javascript">
        var validateForm = function() {
            if ($("#password").val() == $("#confirm-password").val()) {
                return true;
            }

            $("#confirm-password").addClass("is-invalid");
            return false;
        }
    </script>
</body>
</html>
