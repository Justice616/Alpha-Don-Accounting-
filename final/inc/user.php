<?php

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/../inc/account.php';

Class User {

	const TYPE_USER = 0;
	const TYPE_MANAGER = 1;
	const TYPE_ADMIN = 2;

	const STATE_PENDING = 0;
	const STATE_REJECTED = 1;
	const STATE_ACCEPTED = 2;

	public static function isLoggedIn() {
		return isset($_SESSION['username']) === TRUE;
	}

	public static function redirect($url) {
		ob_start();
		header('Location: '.$url);
		ob_end_flush();
		die();
	}

	public static function refresh($nsec, $url) {
		header("Refresh: $nsec; url=$url");
	}

	public static function getUsername() {
		return $_SESSION['username'];
	}

	public static function getType() {
		return $_SESSION['type'];
	}

	public static function getDescription() {
		return $_SESSION['description'];
	}

	public static function makePageTitle($page) {
		return sprintf('[%s] - %s', $page, self::getDescription());
	}

	public static function makeUserPostText() {
		return sprintf('%s - %s', self::getUsername(), self::getDescription());
	}

	public static function prettyFloat($fl) {
		return "$" . number_format($fl, 3);
	}

	public static function route($arg = FALSE) {

		if (!self::isLoggedIn()) {
			self::redirect('login.php');
		}

		$values = ['add', 'delete', 'edit', 'view', 'search'];

		if (in_array($arg, $values, TRUE)) {

			$pdo = Database::getInstance();
			$type = self::getType();

			$stmt = $pdo->prepare("select * from profile where type = ?");
			$stmt->execute([$type]);
			$row = $stmt->fetch();

			$key = 'can_' . $arg;

			if ($row[$key] == 0) {
				die("you are not allowed to access this resource.");
			}

		} else {
			if ($arg === TRUE)
				return;

			self::redirect('home.php');
		}
	} // route

	public static function formatJournalRecord($row) {
		$ret = array();
		$pdo = Database::getInstance();
		$accounts = Account::getAccounts(Account::ACCOUNTS_ARRAY);

		$ret['date'] = $row['entry_date'];
		$ret['username'] = $row['username'];
		$ret['status'] = $row['status'];
		$ret['response'] = $row['response'];
		$ret['desc'] = '<strong>&nbsp;#' . $row['ref'] . ': </strong>';

		if ($row['description'] == "") {
			$ret['desc'] .= "no description";
		} else {
			$ret['desc'] .= $row['description'];
		}

		$ret['acc'] = '';
		$ret['ref'] = '';
		$ret['debit'] = '';
		$ret['credit'] = '';

		$values = [$row['acc1'], $row['acc2'], $row['acc3'], $row['acc4'], $row['acc5']];

		foreach($values as $v) {
			if ($v == 0)
				break;

			$v = (string) $v;

			$type = substr($v, 0, -5);
			$tableName = 'entries_' . $type;

			$sql = "select * from " . $tableName . " where ID = " . $v;
			$res = $pdo->query($sql)->fetchAll();

			if (count($res) == 0)
				continue;

			$r = $res[0];

			$ret['ref'] .= $r["ID"] . "<br>";
			$ret['debit'] .= ($r["DEBIT"] == 0 ? "" : "$".number_format($r["DEBIT"]  , 3) ) . "<br>";
			$ret['credit'] .= ($r["CREDIT"] == 0 ? "" : "$".number_format($r["CREDIT"]  , 3) ) . "<br>";

			$s = '<span class="{{class}}">{{str}}</span><br>';
			if ($r["DEBIT"] == 0) {
				$s = str_replace("{{class}}", "account-entry entry-credit", $s);
			} else {
				$s = str_replace("{{class}}", "account-entry entry-debit", $s);
			}
			$s = str_replace("{{str}}", $accounts[$v[0]][1], $s);
			$ret['acc'] .= $s;
		}

		$files = json_decode($row['files']);
		$ret['desc'] .= '<br><br><ul>';

		foreach($files as $f) {
			$ret['desc'] .= '<li><a href="./files/' . $f . '">' . $f . '</a></li>';
		}

		$ret['desc'] .= '</ul>';

		return $ret;
	}

	public static function staticGetJournalRecords($state = self::STATE_PENDING, $username = FALSE) {
		try {
			$pdo = Database::getInstance();
			$params = array();

			$query = "select * from journal where
				  {{user}}
				  status = ? 
				order by ref desc;";
			if ($username === FALSE) {
				$query = str_replace("{{user}}", "", $query);
				$params = [$state];
			} else {
				$query = str_replace("{{user}}", " username = ? and ", $query);
				$params = [$username, $state];
			}

			$stmt = $pdo->prepare($query);

			$stmt->execute($params);

			return $stmt->fetchAll();
		} catch (Exception $e){
			die("sql failed: " . $e->getMessage());
		}
	}

	public $username;

	public function __construct($username) {
		$this->username = $username;
	}

	public function getJournalRecords($state = self::STATE_PENDING) {
		return self::staticGetJournalRecords($state, $this->username);
	}

} // User
