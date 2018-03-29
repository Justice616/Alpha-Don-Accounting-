<?php

session_start();

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/user.php';

User::route(TRUE);

$type = User::getType();
if ($type == User::TYPE_USER) {
	include __DIR__ . '/src/home/home.user.php';
} else if ($type == USER::TYPE_MANAGER) {
	include __DIR__ . '/src/home/home.manager.php';
} else if ($type == USER::TYPE_ADMIN) {
	include __DIR__ . '/src/home/home.admin.php';
}
