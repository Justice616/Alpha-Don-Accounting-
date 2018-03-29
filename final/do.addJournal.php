<?php

require './config.php';

search_and_redirect(__FILE__);
$username = $_SESSION["username"];
$conn = mysql_conn();

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

// disable autocommit
mysqli_autocommit($conn, FALSE);

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

foreach( $values as $v ) {
	$tablePrefix = "accounts";
	$tablePostfix = $v[0];
	$tableName = $tablePrefix . $tablePostfix;
	$tableStart = $tablePostfix . '00001';

	$tableCreateQuery1 = <<<EOF
create table if not exists {{tablename}} (
	ID int auto_increment,
	DEBIT float not null,
	CREDIT float not null,
	STATUS int default 0,
	PRIMARY KEY (ID)
);
EOF;
	$tableCreateQuery2 = "alter table {{tablename}} auto_increment = {{increment}}";

	$tableCreateQuery1 = str_replace("{{tablename}}", $tableName, $tableCreateQuery1); 
	$tableCreateQuery2 = str_replace("{{tablename}}", $tableName, $tableCreateQuery2); 
	$tableCreateQuery2 = str_replace("{{increment}}", $tableStart, $tableCreateQuery2); 

	$queryCheck = "SELECT ID FROM {{tablename}}";
	$queryCheck = str_replace("{{tablename}}", $tableName, $queryCheck);
	$result = mysqli_query($conn, $queryCheck);

	if(empty($result)) {
		// create table
		if (!mysqli_query($conn, $tableCreateQuery1))
			die(mysqli_error($conn));

		// add auto_increment start
		if (!mysqli_query($conn, $tableCreateQuery2))
			die(mysqli_error($conn));
	}

	// we now have a table.
	$debit = $v[1];
	$credit = $v[2];

	$sql = "INSERT INTO {{tablename}} (debit, credit) values(?, ?)";
	$sql = str_replace("{{tablename}}", $tableName, $sql);

	$stmt = $conn->prepare($sql);
	$stmt->bind_param("dd", $debit, $credit);

	if ($stmt->execute() === TRUE) {
		$last_id = $stmt->insert_id;
		$inserted_records[] = $last_id;
	} else {
		mysqli_rollback($conn);
		die("sql failed: " . $conn->error);
	}

}

$sql = "insert into journal (username, entry_date, acc1, acc2, acc3, acc4, acc5, description, files) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$accounts = [0, 0, 0, 0, 0];
$inserted_records_len = count($inserted_records);
for($idx = 0; $idx < 5; $idx++) {
	if ($idx < $inserted_records_len)
		$accounts[$idx] = $inserted_records[$idx];
}
$files_json = json_encode($files);

$stmt = $conn->prepare($sql);

// TODO: bind userid/username
$stmt->bind_param(
	"ssdddddss", 
	$username,
	$date,
	$accounts[0],
	$accounts[1],
	$accounts[2],
	$accounts[3],
	$accounts[4],
	$description,
	$files_json
);

if ($stmt->execute() !== TRUE) {
	mysqli_rollback($conn);
	die("sql failed: " . $conn->error);
}

// commit changes
mysqli_commit($conn);

$last_id = $stmt->insert_id;
?>
<html>
<body>
successfully created journal record <?php echo $last_id ?>
<br>
<br>
<br>
<a href="addJournal.php"><button>go back</button></a>
</body>
</html>
