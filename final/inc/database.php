<?php

require_once __DIR__ . '/../config.php';

class Database {
	private static $instance;

	public $pdo;

	private function __construct()
	{
		global $mysql_servername, $mysql_username, $mysql_password, $mysql_database;

		if (!(
			isset($mysql_servername) &&
			isset($mysql_username) &&
			isset($mysql_password) &&
			isset($mysql_database)
		)) {
			die("wrong configuration.");
		}

		try {

			$host = $mysql_servername;
			$db   = $mysql_database;
			$user = $mysql_username;
			$pass = $mysql_password;

			$charset = 'utf8mb4';

			$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
			$opt = [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES   => false,
			];

			$this->pdo = new PDO($dsn, $user, $pass, $opt);
		} catch (PDOException $e) {
			die($e->getMessage());
		}
	} // private __construct

	public static function getInstance()
	{
		if (!isset(self::$instance)) {
			self::$instance = new Database();
		}

		return self::$instance->pdo;
	}

} // Database
