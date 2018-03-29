<?php

session_start();

require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

User::route(TRUE);

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title><?php echo User::makePageTitle('Search') ?></title>

	<style>
	</style>
</head>
<body>

<nav class="navbar">
	<a href="home.php">Home</a>
	<?php
	if (User::getType() == User::TYPE_USER) { ?>
		<a href="journal.add.php">Add Journal</a>
	<?php } // user.type == manager ?>
	<a href="journal.view.php">View Journal</a>
	<a href="report.php">Reports</a>
	<a class="active" href="javascript:void(0)">Search</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

<center><h2>Search</h2></center>

<table>
	<tr>
		<td>
			<form method="GET" action="do.search.php">
				<input type="hidden" name="action" value="searchDate">
				<?php $strDate = date("Y-m-d"); ?>
				Search (date) <input type="date" name="date" value="<?php echo $strDate ?>">
				<input type="submit" value="Submit">
			</form>
		</td>
		<td>
			<form method="GET" action="do.search.php">
				<input type="hidden" name="action" value="searchAcc">
				Search (account)
				<input type="text" id="searchAccount" name="account"></input>

				<input type="submit" id="searchAccountBtn" value="Submit" >
			</form>
		</td>
	</tr>
</table>


</div>
</body>
</html>
