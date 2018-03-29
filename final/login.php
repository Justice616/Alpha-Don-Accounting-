<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

if (User::isLoggedIn())
	User::route();

$err = "";

if (isset($_POST['username']))
{
	$user = $_POST['username'];
	$pass = $_POST['pass'];
	$pdo  = Database::getInstance();

	$stmt = $pdo->prepare("select username, email, password, u.type as type, profile.description as description, u.status as status from users u inner join profile on profile.type = u.type where username = ?");
	$stmt->execute([$user]);

	if ($stmt->rowCount() > 0)
	{
		$row = $stmt->fetch();
		$hash = $row['password'];
		$type = $row['type'];
		$desc = $row['description'];

		if ($row['status'] != 0) {
			$err = "account disabled";
		} else {

			if(password_verify($pass,$hash))
			{
				$_SESSION["username"] = $user;
				$_SESSION["type"] = $type;
				$_SESSION["description"] = $desc;
	
				User::route();
			}
			else
			{
				$err = "Incorrect Password.";
			}

		}
	}
	else
	{
		$err = "User does not exist";
	}
} // isset $_POST['username']

?>


<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/sign-in.css">
	<title>Login</title>
</head>
<body>

<div class="body container">

	<div class="modal modal-login">
		<h1>LOGIN</h1>

		<form action="login.php" method="post">
			<input type="text" placeholder="UserName" name="username" required><br>
			<input type="password" placeholder="Password" name="pass" required><br>
			<input type="submit" name="login" value="Login">

			<div style="color: red;"><?php echo $err;?></div>
		</form>

		<br>
		<a href="signup.php"><button>Sign Up</button></a>
	</div>


</div>
</body>
</html>
