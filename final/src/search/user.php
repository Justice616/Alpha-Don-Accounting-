<?php

require_once __DIR__ . '/../../inc/account.php';
require_once __DIR__ . '/../../inc/database.php';
require_once __DIR__ . '/../../inc/user.php';


$pdo = Database::getInstance();

if (isset($_REQUEST['account'])) {

	if (User::getType() == User::TYPE_ADMIN)
		$sql = "select * from users where username like ?";
	else
		$sql = "select * from users where username like ? and type = 0";

	$stmt = $pdo->prepare($sql);
	$stmt->execute([ "%" . $_REQUEST['account'] . "%" ]);

	$users = $stmt->fetchAll();

	if (count($users) == 0)
		die("no users found");

	echo "<tr>";
	echo "<th>Username</th>";

	if (User::getType() == User::TYPE_MANAGER || User::getType() == User::TYPE_ADMIN) {
		echo "<th>Edit Account</th>";
	}

	if (User::getType() == User::TYPE_ADMIN) {
		echo "<th>Account Status</th>";
	}


	echo "</tr>";

	foreach($users as $u) {
		$tpl = '<td><a href="{{url}}">{{name}}</a></td>';
		$td = array();

		$ahref = '/do.search.php?action=searchAcc&username=%s';
		$link = sprintf($ahref, $u['username']);

		$t = str_replace("{{url}}", $link, $tpl);
		$t = str_replace("{{name}}", $u['username'], $t);

		$td[] = $t;

		if (User::getType() == User::TYPE_MANAGER || User::getType() == User::TYPE_ADMIN) {
			$ahref = '/account.edit.php?action=edit&username=%s';
			$link = sprintf($ahref, $u['username']);
			$t = str_replace("{{url}}", $link, $tpl);
			$t = str_replace("{{name}}", "edit", $t);
	
			$td[] = $t;
		}

		if (User::getType() == User::TYPE_ADMIN) {
			$tpl = '<td>{{status}} (<a href="{{url}}">{{name}}</a>)</td>';

			if ($u['status'] == 0) {
				$t = str_replace("{{status}}", "Active", $tpl);
				$t = str_replace("{{name}}", "de-activate?", $t);

				$ahref = '/account.delete.php?action=disable&username=%s';
				$link = sprintf($ahref, $u['username']);

				$t = str_replace("{{url}}", $link, $t);


			} else {
				$t = str_replace("{{status}}", "Deactive", $tpl);
				$t = str_replace("{{name}}", "activate?", $t);

				$ahref = '/account.delete.php?action=enable&username=%s';
				$link = sprintf($ahref, $u['username']);

				$t = str_replace("{{url}}", $link, $t);
			}

			$td[] = $t;
		}

		echo sprintf("<tr>%s</tr>", implode("", $td));
	} // foreach

} else if (isset($_REQUEST['username'])) {

	$stmt = $pdo->prepare("select * from journal where username = ? and status = 2");
	$stmt->execute([$_REQUEST['username']]);

	$result = $stmt->fetchAll();

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
