<?php

require_once __DIR__ . '/config.php';

try {

	global $mysql_servername, $mysql_username, $mysql_password, $mysql_database;

	$host = $mysql_servername;
	$db   = $mysql_database;
	$user = $mysql_username;
	$pass = $mysql_password;

	$charset = 'utf8mb4';

	$dsn = "mysql:host=$host;charset=$charset";
	$opt = [
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES   => false,
	];
	$pdo = new PDO($dsn, $user, $pass, $opt);

	$file = file_get_contents( __DIR__ . '/tables.sql' );

	$pdo->exec($file);

	echo "ok";

} catch (PDOException $e) {
	die("setup failed: " . $e->getMessage());
}
