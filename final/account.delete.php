<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/user.php';

User::route('delete');

if (isset($_REQUEST['action']) && isset($_REQUEST['username'])) {

	$pdo = Database::getInstance();

	$action = $_REQUEST['action'];
	$username = $_REQUEST['username'];

	if ($action == 'disable') {
		$sql = "update users set status = 2 where username = :user";
	} else if ($action == 'enable') {
		$sql = "update users set status = 0 where username = :user";
	} else {
		die('invalid request');
	}

	$stmt = $pdo->prepare($sql);
	$ret = $stmt->execute([$username]);

	if ($ret) {
		die("success");
	}

	die("ok");
}

die("failure");
