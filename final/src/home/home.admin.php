<?php

require_once __DIR__ . '/../../inc/database.php';
require_once __DIR__ . '/../../inc/user.php';

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title><?php echo User::makePageTitle('Home') ?></title>

	<style>
		table.admin-links td {
			text-align: center;
		}
	</style>

</head>
<body>

<nav class="navbar">
	<a class="active" href="javascript:void(0)">Home</a>
	<a href="journal.view.php">View Journal</a>
	<a href="search.php">Search</a>
	<a href="report.php">Reports</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">


	<center><h2>Admin Panel</h2></center>

	<table class="admin-links">
		<tr>
			<th colspan="5"><h4>Users</h4></th>
		</tr>
		<tr>
			
			<td><a href="search.php">Search Account</a></td>
		</tr>
	</table>

	<br>
	<br>
	<br>

	<table class="admin-links">
		<tr>
			<th colspan="5"><h4>Chart of Account</h4></th>
		</tr>
		<tr>
			<td><a href="account.add.php">Add Account</a></td>
			<td><a href="report.php">View Account</a></td>
			<td><a href=""><!--"edit2.php"-->Edit Account</a></td>
			<td><a href=""><!--"account.delete.php"-->Deactivate Account</a></td>
			
		</tr>
	</table>

</div>

</body>
</html>
