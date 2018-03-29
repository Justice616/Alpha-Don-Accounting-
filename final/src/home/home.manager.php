<?php

require_once __DIR__ . '/../../inc/account.php';
require_once __DIR__ . '/../../inc/database.php';
require_once __DIR__ . '/../../inc/user.php';

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
	<a href="journal.view.php">View Journal</a>
	<a href="search.php">Search</a>
	<a href="report.php">Reports</a>
	<div class="navbar-right">
		<a href="javascript:void(0)"><?php echo User::makeUserPostText() ?></a>
		<a href="do.Logout.php">Logout</a>
	</div>
</nav>

<div class="body container">

<center><h2>Pending Journal Posts</h2></center>

<table>
	<tr>
		<th width="200px">Dates</th>
		<th width="200px">Username</th>
		<th width="200px">Description</th>
		<th width="400px">Accounts</th>
		<th>Post Ref</th>
		<th width="100px">Debit</th>
		<th width="100px">Credit</th>
		<th width="100px"></th>
	</tr>

<?php

$result = User::staticGetJournalRecords(User::STATE_PENDING);
if (count($result) == 0) {

?>
		<tr>
			<td colspan="8"> no entries </td>
		</tr>

<?php } else { 

	foreach($result as $row) {
		$ret = User::formatJournalRecord($row);

?>
	<tr>
		<td><?php echo $ret['date'] ?></td>
		<td><?php echo $ret['username'] ?></td>
		<td style="text-align: left;"><?php echo $ret['desc'] ?></td>
		<td style="text-align: left; padding-left: 20px;"><?php echo $ret['acc'] ?></td>
		<td><?php echo $ret['ref'] ?></td>
		<td><?php echo $ret['debit'] ?></td>
		<td><?php echo $ret['credit'] ?></td>
		<td>
			<form method="POST" action="do.manager.php" id="form<?php echo $row['ref'] ?>">
				<input type="hidden" name="ref" value="<?php echo $row['ref'] ?>">

				<input id="formResponse<?php echo $row['ref'] ?>" type="hidden" name="response">
				<input id="formAccept<?php echo $row['ref'] ?>" type="hidden" name="accept" value="0">
				<input type="button" name="accept" value="Accept" onclick="fill(<?php echo $row['ref'] ?>, 0)"><br>
				<input type="button" name="reject" value="Reject" onclick="fill(<?php echo $row['ref'] ?>, 1)"><br>
			</form>
		</td>
	</tr>
<?php } // while (row = $result->fetch ?>

<?php } // else (result->num_rows == 0) ?>

</table>


</div>
<script type="text/javascript">

	function fill(id, reject) {
		domId = 'formResponse' + id;
		domEl = document.getElementById(domId);

		domForm = 'form' + id;
		domFormEl = document.getElementById(domForm);

		if (!reject) {
			domAccept = 'formAccept' + id;
			domAcceptEl = document.getElementById(domAccept);
			domAcceptEl.value = "1";
			domFormEl.submit();
			return;
		}

		var response = prompt("Please enter your comments", "");
	
		if (response == null || response == "") {
			alert("you need to enter response.");
			return;
		}

		domEl.value = response;
		domFormEl.submit();
	}
</script>

</body>
</html>
