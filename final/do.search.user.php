<?php

require './config.php';

search_and_redirect(__FILE__);
$username = $_SESSION["username"];
$conn = mysql_conn();


if ( ! (

	isset($_REQUEST['action']) &&
	(
		$_REQUEST['action'] == "searchDate" ||
		$_REQUEST['action'] == "searchAcc"
	)
)) {
	die("invalid request");
}


if ($_REQUEST['action'] == "searchDate") {
	$sql = "select * from journal where username = '{{username}}' and status = 2 and entry_date = '" . $_REQUEST['date'] . "' order by ref desc;";
} else if ($_REQUEST['action'] == "searchAcc") {

	$sql = "select * from journal where status = 2 and 
		username = '{{username}}' and
		( 
			acc1 like '{k}%' or 
			acc2 like '{k}%' or
			acc3 like '{k}%' or
			acc4 like '{k}%' or
			acc5 like '{k}%' )";

	while(strstr($sql, "{k}") !== FALSE) {
		$sql = str_replace("{k}", $_REQUEST['account'], $sql);
	}
}

$sql = str_replace("{{username}}", $username, $sql);
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
  <a class="active" href="#">Search</a>
  <a href="viewJournal.php">&lt; Go back</a>
  <a style="float: right;" href="do.Logout.php">Logout</a>
</div>

<div class="centerDiv">

<p><h2>Search Journal Posts</h2>
<?php
if ($_REQUEST['action'] == "searchDate") {
	echo "<h4>date: " . $_REQUEST['date'] .  "</h4>";
}

?>

</p>

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

<?php if ($result->num_rows == 0) { ?>
		<tr>
			<td colspan="7"> no entries </td>
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
			$rowDebit .= ($r["DEBIT"] == 0 ? "" :   "$".number_format($r["DEBIT"]  , 3) ) . "<br>";
			$rowCredit .= ($r["CREDIT"] == 0 ? "" : "$".number_format($r["CREDIT"] , 3) ) . "<br>";

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
		<td style="text-align: left;"><?php echo $rowDesc ?></td>
		<td style="text-align: left; padding-left: 20px;"><?php echo $rowAcc ?></td>
		<td><?php echo $rowRef ?></td>
		<td><?php echo $rowDebit ?></td>
		<td><?php echo $rowCredit ?></td>
		<td>
			<?php
				if ($row['status'] == 0) {
					echo "pending";
				} else if ($row['status'] == 1) {
					echo "rejected (" . $row['response'] . ")";
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

	(function() {
		fix_account_names();
	})();
</script>

</body>
</html>
