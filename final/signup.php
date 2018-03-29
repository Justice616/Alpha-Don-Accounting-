<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

if (User::isLoggedIn())
	User::route();

$err = "";
$msg = "";

if (isset($_POST['username']))
{
	$user = $_POST['username'];
	$pass = $_POST['pass'];
	$email = $_POST['email'];
	$type = $_POST['type'];

	$pdo = Database::getInstance();

	$stmt = $pdo->prepare("select * from users where username = ?");
	$stmt->execute([$user]);

	if($stmt->rowCount() > 0)
	{
			$err = "Username already exist";
	}
	else
	{
		$passhash = password_hash($pass, PASSWORD_DEFAULT);
		$stmt = $pdo->prepare("insert into users values (?, ?, ?, ?,0)");
		$result = $stmt->execute([$user, $email, $passhash, $type]);

		if($result)
		{
			$msg = "User added Successfully.";
			User::refresh(3, 'login.php');
		}
		else
		{
			$err = "Could Not add user.";
		}
	}
} // isset $_POST['username']

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/sign-in.css">
	<title>SignUp</title>
</head>
<body>


<div class="body container">

	<div class="modal modal-login">

		<h1>SIGNUP</h1>


		<form action="signup.php" method="POST">
			<input type="text" placeholder="UserName" required name="username"><br>
			<input type="email" placeholder="Email" required name="email"><br>
			<input type="password" placeholder="Password" onchange="validate()" id="p1" required name="pass"><br>
			<input type="password" placeholder="Confirm Password" id="p2" onchange="validate()" required name="pass2"><br>
			<div id="check" style="color: red;"></div>

			<select name="type">
				<option value="0">User</option>
				<option value="1">Manager</option>
				<option value="2">Administrator</option>
			</select>

			<input type="submit" id="submit" name="signup" value="SignUp">
		</form>

		<a href="login.php"><button>Back</button></a>
		<div style="color: red;"><?php echo $err;?></div>
		<div style="color: green;"><?php echo $msg;?></div>
	</div>

</div>
</body>

<script>
	function validate()
	{
		var p1 = document.getElementById("p1").value;
		var p2 = document.getElementById("p2").value;
		

		if(p1!=p2)
		{
			document.getElementById("check").innerHTML = "Passwords Does not Match";
			document.getElementById("submit").disabled = true;
		}
		else
		{
			document.getElementById("check").innerHTML = "";
			document.getElementById("submit").disabled = false;
		}
	}

</script>
</html>
