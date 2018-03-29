<?php

session_start();

require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

User::route(TRUE);

$type = User::getType();
if ($type != User::TYPE_USER)
	die("only user can add journal entries.");

$username = User::getUsername();
$pdo = Database::getInstance();

$total_fields = 0;
$date = $_REQUEST['date'];
$description = $_REQUEST['desc'];
$values = [];
$files = [];

$inserted_records = [];

// get total fields count
if (isset($_REQUEST['acc0'])) $total_fields = 1;
if (isset($_REQUEST['acc1'])) $total_fields = 2;
if (isset($_REQUEST['acc2'])) $total_fields = 3;
if (isset($_REQUEST['acc3'])) $total_fields = 4;
if (isset($_REQUEST['acc4'])) $total_fields = 5;

// copy uploaded files
$total_files = count($_FILES['documents']['name']);
for ($i = 0; $i < $total_files; $i++) {
	$tmpFilePath = $_FILES['documents']['tmp_name'][$i];

	// TODO: upload error handling
	if ($tmpFilePath != "") {
		$collisionAvoidance = bin2hex(random_bytes(4)) . "-";
		$newFilePath = "./files/" . $collisionAvoidance . $_FILES['documents']['name'][$i];

		$files[] =  $collisionAvoidance . $_FILES['documents']['name'][$i];

		if ( ! move_uploaded_file($tmpFilePath, $newFilePath)) {
			// TODO: fix copy handling
		}
	}
}

// TODO: pre-check which tables are needed and create them
for ($i = 0; $i < $total_fields; $i++) {
	$type_k = 'acc' . $i;
	$debit_k = 'debit' . $i;
	$credit_k = 'credit' . $i;

	$type = $_REQUEST[$type_k];
	$debit = $_REQUEST[$debit_k];
	$credit = $_REQUEST[$credit_k];

	$debit = (float)$debit;
	$credit = (float)$credit;

	$values[] = [$type, $debit, $credit];
}

$inserted_records = array();
foreach( $values as $v ) {

	$accno = $v[0];
	$debit = $v[1];
	$credit = $v[2];

	$acc = new Account($accno);

	$inserted_records[] = $acc->insert($debit, $credit);
}

try {
	$pdo = Database::getInstance();

	$sql = "insert into journal (username, entry_date, acc1, acc2, acc3, acc4, acc5, description, files) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

	$accounts = [0, 0, 0, 0, 0];
	$inserted_records_len = count($inserted_records);
	for($idx = 0; $idx < 5; $idx++) {
		if ($idx < $inserted_records_len)
			$accounts[$idx] = $inserted_records[$idx];
	}
	$files_json = json_encode($files);

	$stmt = $pdo->prepare($sql);
	$params = [
		$username,
		$date,
		$accounts[0],
		$accounts[1],
		$accounts[2],
		$accounts[3],
		$accounts[4],
		$description,
		$files_json
	];

	if ($stmt->execute($params)) {
		$last_id = $pdo->lastInsertId();
	} else {
		die("sql failed: " . $pdo->getMessage());
	}



} catch (Exception $e) {
	die("sql failed: " . $e->getMessage());
}

$last_id = $pdo->lastInsertId();
?>

<html>
<body>
successfully created journal record <?php echo $last_id ?>
<br>
<br>
<br>
<a href="journal.add.php"><button>go back</button></a>
</body>
</html>
