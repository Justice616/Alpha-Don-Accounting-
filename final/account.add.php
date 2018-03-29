<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/user.php';

User::route('add');

if (isset($_POST['accname'])) {

	$name = $_POST['accname'];
	$category = (int) $_POST['category'];

	$debit = 0;
	$credit = 0;

	if ($_POST['balance'] != "") {
		if ($_POST['initial'] == "credit") {
			$credit = (float) $_POST['balance'];
		} else {
			$debit = (float) $_POST['balance'];
		}
	}

	Account::addAccount($name, $category, User::getUsername(), $debit, $credit);

	User::refresh(5, "/home.php");
	die("account added successfully.");
}

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
	<a class="active" href="javascript:void(0)">Add Account</a>
	<a href="report.php">Reports</a>
	<a href="search.php">Search</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

	<center><h2>Add new Account</h2></center>


	<form method="POST" action="account.add.php" >

		<table>
			<tr>
				<td><b>Account Name</b></td>
				<td>
					<input class="form" type="text" name="accname" required>
				</td>
			</tr>

			<tr>
				<td><b>Account Category</b></td>
				<td>
					<select class="form" name="category" required>
						<option value="1">Long Term</option>
						<option value="2">Short Term</option>
					</select>
				</td>
			</tr>

			<tr>
				<td><b>Initial Balance</b></td>
				<td>
					<select name="initial" required>
						<option value="credit">CREDIT</option>
						<option value="debit">DEBIT</option>
					</select>
					<input name="balance" type="number" step="0.001" min="0"><br>
				</td>
			</tr>
			
			<tr>
				<td colspan="2" class="has-center">
					<input class="form" type="submit" value="Submit" id="submit" >
				</td>
			</tr>

		</table>

	</form>

</div>

</body>
</html>

