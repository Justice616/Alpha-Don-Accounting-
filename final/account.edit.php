<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/user.php';

User::route('edit');

if (isset($_POST['submit'])) {

	$pdo = Database::getInstance();

	$username = $_REQUEST['username'];
	$email = $_REQUEST['email'];
	$pass = trim($_REQUEST['pass']);
	$type = $_REQUEST['type'];

	$sql = "update users set {{pass}} email = :email, type = :type where username = :user";

	$params = ['email' => $email, 'type' => $type, 'user' => $username];

	if (strlen($pass) == 0) {
		$sql = str_replace("{{pass}}", "", $sql);
	} else {
		$sql = str_replace("{{pass}}", "password = :pass, ", $sql);
		$hash = password_hash($pass, PASSWORD_DEFAULT);
		$params['pass'] = $hash;
	}

	$stmt = $pdo->prepare($sql);
	$ret = $stmt->execute($params);

	if ($ret) {
		die("success");
	}

	die("failure");
}

$user = $_GET['username'];
$pdo = Database::getInstance();

// todo: fix this.

$stmt = $pdo->prepare("select * from users where username = ?");
$stmt->execute([$user]);

$data = $stmt->fetch();

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title><?php echo User::getDescription() . " - Add Account" ?></title>
</head>

<body>

<nav class="navbar">
	<a href="home.php">Home</a>
	<a class="active" href="javascript:void(0)">Edit Account</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

	<center><h2>Edit Account</h2></center>

	<form action="account.edit.php" method="POST">
		<table>
			<input type="hidden" name="username" value="<?php echo $data['username'] ?>">
			<tr>
				<td>username</td>
				<td><input type="text" placeholder="UserName" value="<?php echo $data['username'] ?>" disabled><br></td>
			</tr>
			<tr>
				<td>email</td>
				<td><input type="email" placeholder="Email" required name="email" value="<?php echo $data['email'] ?>"><br></td>
			</tr>
			<tr>
				<td>password (leave blank to not change)</td>
				<td><input type="password" placeholder="Password" name="pass"><br></td>
			</tr>
			<tr>
				<td>account type</td>
				<td>

					<select name="type">
						<option value="0" <?php echo $data['type'] == 0 ? "selected" : "" ?>>Standard</option>
						<option value="1" <?php echo $data['type'] == 1 ? "selected" : "" ?>>Manager</option>
						<option value="2" <?php echo $data['type'] == 2 ? "selected" : "" ?>>Administrator</option>
					</select>

				</td>
			</tr>
			<tr>
				<td colspan="2" align="center">
					<input type="submit" name="submit" value="Apply">
				</td>
			</tr>
		</table>
	</form>


</div>

</body>
</html>

