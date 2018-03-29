<?php

require_once __DIR__ . '/../../inc/account.php';
require_once __DIR__ . '/../../inc/database.php';
require_once __DIR__ . '/../../inc/user.php';


$pdo = Database::getInstance();

if (isset($_REQUEST['date'])) {

	$type = User::getType();
	$sql = "select * from journal where {{user}} status = 2 and entry_date = ? order by ref desc;";


	if ($type == User::TYPE_MANAGER || $type == User::TYPE_ADMIN) {
		$sql = str_replace("{{user}}", "", $sql);
		$params = [$_REQUEST['date']];
	} else {
		$sql = str_replace("{{user}}", "username = ? and ", $sql);
		$params = [User::getUsername(), $_REQUEST['date']];
	}

	error_log($sql);

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);

	$result = $stmt->fetchAll();

	echo "<tr><td colspan=\"8\"><h4>date: " . $_REQUEST['date'] .  "</h4></td></tr>";
	$out = <<<EOF

	<tr>
		<th width="200px">Dates</th>
		<th width="200px">Username</th>
		<th width="200px">Description</th>
		<th width="400px">Accounts</th>
		<th>Post Ref</th>
		<th width="100px">Debit</th>
		<th width="100px">Credit</th>
		<th width="100px">status</th>
	</tr>

EOF;
	echo ($out);

	if (count($result) == 0) {
$out = <<<EOF
		<tr>
			<td colspan="8"> no entries </td>
		</tr>
EOF;
		echo ($out);
	} else {

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

<?php
} // foreach $result as $row

	}


} else {
	echo "invalid request";
}
