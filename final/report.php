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
	<a class="active" href="report.php">Report</a>
	<a href="search.php">Search</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

<?php
if (isset($_REQUEST['accno'])) {

	$accounts = Account::getAccounts(Account::ACCOUNTS_RAW_ASSOC);
	$account = $accounts[$_REQUEST['accno']];
?>

	<center><h2>Report: <?php echo $account['accname'] ?></h2></center>

	<table>
		<tr><td>account number</td>
			<td><?php echo $account['accno'] ?></td>
		</tr>

		<tr><td>added by</td>
			<td><?php echo $account['username_added'] ?></td>
		</tr>

		<tr><td>account type</td>
			<td><?php echo $account['category'] == 1 ? "Long" : "Short" ?></td>
		</tr>

		<tr><td>time created</td>
			<td><?php echo $account['time_added'] ?></td>
		</tr>

		<tr><td>initial debit</td>
			<td><?php echo User::prettyFloat($account['initial_debit']) ?></td>
		</tr>

		<tr><td>initial credit</td>
			<td><?php echo User::prettyFloat($account['initial_credit']) ?></td>
		</tr>

	</table>

	<br>
	<br>

	<table>
		<tr>
			<th></th>
			<th>Date</th>
			<th>Debit</th>
			<th>Credit</th>
		</tr>
<?php
	$acc = new Account($_REQUEST['accno']);
	$records = $acc->getAccountRecords();
	$stats = $acc->getStats();

	foreach($records as $k => $v) {
?>
		<tr>
			<td><?php echo (string)($k + 1) ?></td>
			<td><?php echo $v['CREATED']; ?></td>
			<td><?php echo User::prettyFloat($v['DEBIT']); ?></td>
			<td><?php echo User::prettyFloat($v['CREDIT']); ?></td>
		</tr>
<?php
	} // foreach $records as $k => $v
?>

		<tr>
			<td colspan="2">Total</td>
			<td><?php echo User::prettyFloat($stats['debit'] + $account['initial_debit']) ?></td>
			<td><?php echo User::prettyFloat($stats['credit'] + $account['initial_credit']) ?></td>
		</tr>
	</table>

<?php

} else { // isset $_REQUESTS['accno']
	$accounts = Account::getAccounts(Account::ACCOUNTS_HTMLSELECT);
?>

<center><h2>View Report</h2></center>

<table>
	<tr>
		<td>
			<form method="POST">
				<select name="accno"><?php echo $accounts ?></select>

				<input type="submit" value="Submit">
			</form>
		</td>
	</tr>
</table>

<?php

} // isset $_REQUESTS['accno']

?>

</div>
</body>
</html>
