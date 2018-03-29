<?php

session_start();

require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

User::route(TRUE);

if ( ! (

	isset($_REQUEST['action']) &&
	(
		$_REQUEST['action'] == "searchDate" ||
		$_REQUEST['action'] == "searchAcc"
	)
)) {
	die("invalid request");
}

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title><?php echo User::makePageTitle('Search Results') ?></title>

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
	<a class="active" href="search.php">Search</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

<center><h2>Search Results</h2></center>

<table>
<?php

if ($_REQUEST['action'] == "searchAcc") {
	include __DIR__ . '/src/search/user.php';
} else if ($_REQUEST['action'] == "searchDate") {
	include __DIR__ . '/src/search/date.php';
}


?>
</table>


</div>
</body>
</html>
