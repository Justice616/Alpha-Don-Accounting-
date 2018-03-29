<?php

require './config.php';

search_and_redirect(__FILE__);
$username = $_SESSION["username"];
$conn = mysql_conn();


$sql = "select * from journal where status = 0 order by ref desc;";

$result = $conn->query($sql);

?>


<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style1.css">
	<title>Accounting - Home</title>
</head>
<body>

<div class="topnav">
  <a class="active" href="#">Home</a>
  <a href="viewJournalM.php">View Journal</a>
  <a style="float: right;" href="do.Logout.php">Logout</a>
</div>

<div class="centerDiv">

<p><h2>Pending Journal Posts</h2></p>

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

<?php if ($result->num_rows == 0) { ?>
		<tr>
			<td colspan="8"> no entries </td>
		</tr>
<?php } else { ?>

<?php
	while($row = $result->fetch_assoc()) {
		$rowDate = $row["entry_date"];
		$rowDesc = '<strong>&nbsp;#' . $row['ref'] . ': </strong>';
		if ($row['description'] == "") {
			$rowDesc .= "no description";
		} else {
			$rowDesc .= $row['description'];
		}

		$rowAcc = '';
		$rowRef = '';
		$rowDebit = '';
		$rowCredit = '';

		$values = [$row['acc1'], $row['acc2'], $row['acc3'], $row['acc4'], $row['acc5']];

		foreach($values as $v) {
			if ($v == 0)
				break;

			$tableName = 'accounts' . $v[0];

			$sql = "select * from " . $tableName . " where ID = " . $v;
			$res = $conn->query($sql);

			// TODO: handle this
			if ($res->num_rows == 0) {
				continue;
			}

			$r = $res->fetch_assoc();

			$rowRef .= $r["ID"] . "<br>";
			$rowDebit .= ($r["DEBIT"] == 0 ? "" : "$".number_format($r["DEBIT"], 3) ) . "<br>";
			$rowCredit .= ($r["CREDIT"] == 0 ? "" : "$".number_format($r["CREDIT"], 3) ) . "<br>";

			$s = '<span class="{{class}}">{{str}}</span><br>';
			if ($r["DEBIT"] == 0) {
				$s = str_replace("{{class}}", "account-entry entry-credit", $s);
			} else {
				$s = str_replace("{{class}}", "account-entry entry-debit", $s);
			}
			$s = str_replace("{{str}}", $v[0], $s);
			$rowAcc .= $s;
		}

		$files = json_decode($row['files']);
		$rowDesc .= '<br><br><ul>';

		foreach($files as $f) {
			$rowDesc .= '<li><a href="./files/' . $f . '">' . $f . '</a></li>';
		}

		$rowDesc .= '</ul>';


?>
	<tr>
		<td><?php echo $rowDate ?></td>
		<td><?php echo $row['username'] ?></td>
		<td style="text-align: left;"><?php echo $rowDesc ?></td>
		<td style="text-align: left; padding-left: 20px;"><?php echo $rowAcc ?></td>
		<td><?php echo $rowRef ?></td>
		<td><?php echo $rowDebit ?></td>
		<td><?php echo $rowCredit ?></td>
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

<script type="text/javascript" src="accounts.js"></script>
<script type="text/javascript">

	function fix_account_names() {
		var elems = document.getElementsByClassName("account-entry");

		for (var i = 0; i < elems.length; i++) {
			var el = elems[i];

			var idx = parseInt(el.innerHTML) - 1;
			el.innerHTML = accounts[idx];
		}
	}

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

	(function() {
		fix_account_names();
	})();
</script>

</body>
</html>
