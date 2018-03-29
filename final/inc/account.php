<?php

require_once __DIR__ . '/../inc/database.php';

Class Account {

	const ACCOUNTS_RAW = 0;
	const ACCOUNTS_ARRAY = 1;
	const ACCOUNTS_HTMLSELECT = 2;
	const ACCOUNTS_RAW_ASSOC = 3;

	const GET_ACTIVE = 0;
	const GET_ALL = 1;

	const TABLE_PREFIX = "entries_";

	public static function _getActiveAccounts() {
		$pdo = Database::getInstance();
		$r = $pdo->query("select * from accounts where accstatus = 0");
		return $r->fetchAll();
	}

	public static function _getAllAccounts() {
		$pdo = Database::getInstance();
		$r = $pdo->query("select * from accounts where 1");
		return $r->fetchAll();
	}

	public static function getAccounts($method = self::ACCOUNTS_RAW, $state = self::GET_ACTIVE) {
		if ($state == self::GET_ACTIVE)
			$r = self::_getActiveAccounts();
		else
			$r = self::_getAllAccounts();

		if ($method == self::ACCOUNTS_RAW)
			return $r;

		if ($method == self::ACCOUNTS_ARRAY) {
			$accounts = [];
			foreach($r as $x) {
				$accounts[$x['accno']] = [$x['accno'], $x['accname']];
			}
			return $accounts;
		}

		if ($method == self::ACCOUNTS_HTMLSELECT) {
			$ret = "";
			$tpl = '<option value="%s">%s</option>';
			foreach($r as $x) {
				$ret .= sprintf($tpl, $x['accno'], $x['accname']);
			}
			return $ret;
		}

		if ($method == self::ACCOUNTS_RAW_ASSOC) {
			$accounts = [];
			foreach($r as $x) {
				$accounts[$x['accno']] = $x;
			}
			return $accounts;
		}

		return FALSE;
	}

	public static function addAccount($name, $category, $username, $debit, $credit) {
		try {
			$pdo = Database::getInstance();
			$pdo->beginTransaction();
			$r = $pdo->prepare("
			insert into accounts 
				(accname, category, 
				username_added, time_added, initial_debit,
				initial_credit)
			values (?, ?, ?, now(), ?, ?)");

			$r->execute([$name, $category, $username, $debit, $credit]);

			$account = $pdo->lastInsertId();

			$tablePrefix = "entries_";
			$tablePostfix = $account;
			$tableName = $tablePrefix . $tablePostfix;
			$tableStart = $tablePostfix . '00001';

			$tableCreateQuery1 = <<<EOF
create table if not exists {{tablename}} (
	ID int auto_increment,
	DEBIT float not null,
	CREDIT float not null,
	STATUS int default 0,
	CREATED DATETIME DEFAULT CURRENT_TIMESTAMP,

	PRIMARY KEY (ID)
);
EOF;
			$tableCreateQuery2 = "alter table {{tablename}} auto_increment = {{increment}}";

			$tableCreateQuery1 = str_replace("{{tablename}}", $tableName, $tableCreateQuery1); 
			$tableCreateQuery2 = str_replace("{{tablename}}", $tableName, $tableCreateQuery2); 
			$tableCreateQuery2 = str_replace("{{increment}}", $tableStart, $tableCreateQuery2); 


			$pdo->query($tableCreateQuery1);
			$pdo->query($tableCreateQuery2);

			$pdo->commit();
		} catch (Exception $e){
			$pdo->rollback();
			die($e->getMessage());
		}
	}


	public $accno;
	private $tableName;

	public function __construct($accno) {
		$this->accno = (string) $accno;

		$this->tableName = self::TABLE_PREFIX . $this->accno;
	}

	public function getAccountRecords() {
		$pdo = Database::getInstance();
		$sql = "select * from {{tablename}} order by CREATED desc";
		$sql = str_replace("{{tablename}}", $this->tableName, $sql);

		return $pdo->query($sql)->fetchAll();
	}

	public function getStats() {
		$pdo = Database::getInstance();
		$sql = "select sum(debit) as debit, sum(credit) as credit from {{tablename}}";
		$sql = str_replace("{{tablename}}", $this->tableName, $sql);

		return $pdo->query($sql)->fetch();
	}

	public function insert($debit, $credit) {
		$debit = (float) $debit;
		$credit = (float) $credit;

		$last_id = FALSE;

		try {
			$pdo = Database::getInstance();
			$pdo->beginTransaction();

			$sql = "INSERT INTO {{tablename}} (debit, credit) values(?, ?)";
			$sql = str_replace("{{tablename}}", (string) $this->tableName, $sql);

			$stmt = $pdo->prepare($sql);

			if ($stmt->execute([$debit, $credit])) {
				$last_id = $pdo->lastInsertId();
			} else {
				die("sql failed: " . $pdo->getMessage());
			}

			$pdo->commit();
		} catch (Exception $e){
			$pdo->rollback();
			die("sql failed: " . $e->getMessage());
		}

		return $last_id;
	}

} // Account
