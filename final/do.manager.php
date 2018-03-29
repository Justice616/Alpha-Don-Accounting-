<?php

session_start();

require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

User::route(TRUE);

$type = User::getType();
if ($type != User::TYPE_MANAGER)
	die("only manager can approve of journal entries.");

$username = User::getUsername();
$pdo = Database::getInstance();

$accept = ($_REQUEST['accept'] == "1");
$ref = $_REQUEST['ref'];
$response = $_REQUEST['response'];

$status = $accept ? 2 : 1;

$sql = "select * from journal where ref = " . $ref;

$result = $pdo->query($sql)->fetchAll();

if (count($result) == 0) {
	die("invalid request");
}

$row = $result[0];

$values = [$row['acc1'], $row['acc2'], $row['acc3'], $row['acc4'], $row['acc5']];

$accounts = array();

foreach($values as $v) {
	if ($v == 0)
		break;

	$v = (string) $v;

	$type = substr($v, 0, -5);
	$tableName = 'entries_' . $type;

	$accounts[$tableName] = 1;

	$sql = "update " . $tableName . " set status = " . $status . " where ID = " . $v;
	$res = $pdo->query($sql);
}

$sql = "update journal set status = ?, response = ? where ref = ?";
$stmt = $pdo->prepare($sql);

$stmt->execute([$status, $response, $ref]);

?>
<html>
<head>
<style>
table th {
	font-weight: bold;
}
table { 
	border-collapse: collapse;
}
table tr td, table tr th {
	border: 2px solid black;
	padding: 10px;
}
</style>
</head>
<body>
<?php 
	if (! $accept) {
		$err = <<<EOF
	<h3>the posting was rejected.</h3>

	<br>
	<br>
	<br>
	<a href="home.php"><button>go back</button></a>

EOF;
		die($err);
	}
?>

	<h3>the posting was successful;</h3>

	<br>
	<br>

	<table>
		<tr>
			<th>date</th>
			<th>account</th>
			<th>debit</th>
			<th>credit</th>
			<th>balance</th>
		</tr>
<?php

$account = Account::getAccounts(Account::ACCOUNTS_ARRAY);
foreach($accounts as $k => $v) {
	$strDate = date("Y-m-d");
	$accountid = substr($k, -1);

	$sql = "select sum(debit) as debit, sum(credit) as credit from {{tablename}} where status = 2;";
	$sql = str_replace("{{tablename}}", $k, $sql);

	$result = $pdo->query($sql)->fetchAll();
	if (count($result) == 0)
		continue;

	$row = $result[0];
	$debit = (float) $row["debit"];
	$credit = (float) $row["credit"];

?>
	<tr>
		<td><?php echo $strDate; ?></td>
		<td><span class="account-entry"><?php echo $account[$accountid][1]; ?></span></td>
		<td><?php echo "$".number_format($debit, 3); ?></td>
		<td><?php echo "$".number_format($credit, 3); ?></td>
		<?php if ($debit > $credit) { ?>
		<td><?php echo "$".number_format($debit-$credit, 3); ?></td>
		<?php } else { ?>
		<td><?php echo "$(".number_format($credit-$debit, 3); ?>)</td>
		<?php } ?>
	</tr>
<?php 
} // foreach $accounts as $k => $v
?>
	</table>

	<br>
	<br>
	<br>
	<a href="home.php"><button>go back</button></a>

</body>
</html>
