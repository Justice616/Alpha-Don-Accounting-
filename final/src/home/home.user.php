<?php

require_once __DIR__ . '/../../inc/account.php';
require_once __DIR__ . '/../../inc/database.php';
require_once __DIR__ . '/../../inc/user.php';

$user = new User(User::getUsername());

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<title><?php echo User::makePageTitle('Home') ?></title>

	<style>
	</style>
</head>
<body>

<nav class="navbar">
	<a class="active" href="javascript:void(0)">Home</a>
	<a href="journal.add.php">Add Journal</a>
	<a href="journal.view.php">View Journal</a>
	<a href="search.php">Search</a>
	<a href="report.php">Reports</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

<center><h2>Rejected Journal Posts</h2></center>

<table>
	<tr>
		<th width="200px">Dates</th>
		<th width="200px">Description</th>
		<th width="400px">Accounts</th>
		<th>Post Ref</th>
		<th width="100px">Debit</th>
		<th width="100px">Credit</th>
		<th width="100px">status</th>
	</tr>

<?php

$result = $user->getJournalRecords(User::STATE_REJECTED);
if (count($result) == 0) {

?>
		<tr>
			<td colspan="8"> no entries </td>
		</tr>
<?php

} else {

	foreach($result as $row) {
		$ret = User::formatJournalRecord($row);

?>
	<tr>
		<td><?php echo $ret['date'] ?></td>
		<td style="text-align: left;"><?php echo $ret['desc'] ?></td>
		<td style="text-align: left; padding-left: 20px;"><?php echo $ret['acc'] ?></td>
		<td><?php echo $ret['ref'] ?></td>
		<td><?php echo $ret['debit'] ?></td>
		<td><?php echo $ret['credit'] ?></td>
		<td>
			<?php
				if ($ret['status'] == 0) {
					echo "pending";
				} else if ($ret['status'] == 1) {
					echo "rejected (" . $ret['response'] . ")";
				} else {
					echo "accepted";
				}
			?>
		</td>
	</tr>
<?php } // while (row = $result->fetch ?>

<?php } // else (result->num_rows == 0) ?>
</table>


<center><h2>Pending Journal Posts</h2></center>

<table>
	<tr>
		<th width="200px">Dates</th>
		<th width="200px">Description</th>
		<th width="400px">Accounts</th>
		<th>Post Ref</th>
		<th width="100px">Debit</th>
		<th width="100px">Credit</th>
		<th width="100px">status</th>
	</tr>

<?php

$result = $user->getJournalRecords(User::STATE_PENDING);
if (count($result) == 0) {

?>
		<tr>
			<td colspan="8"> no entries </td>
		</tr>
<?php

} else {

	foreach($result as $row) {
		$ret = User::formatJournalRecord($row);

?>
	<tr>
		<td><?php echo $ret['date'] ?></td>
		<td style="text-align: left;"><?php echo $ret['desc'] ?></td>
		<td style="text-align: left; padding-left: 20px;"><?php echo $ret['acc'] ?></td>
		<td><?php echo $ret['ref'] ?></td>
		<td><?php echo $ret['debit'] ?></td>
		<td><?php echo $ret['credit'] ?></td>
		<td>
			<?php
				if ($ret['status'] == 0) {
					echo "pending";
				} else if ($ret['status'] == 1) {
					echo "rejected (" . $ret['response'] . ")";
				} else {
					echo "accepted";
				}
			?>
		</td>
	</tr>
<?php } // while (row = $result->fetch ?>

<?php } // else (result->num_rows == 0) ?>
</table>


</div>
</body>
</html>
