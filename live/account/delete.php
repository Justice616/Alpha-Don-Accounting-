<?php

session_start();

require_once __DIR__ . '/../src/user.php';

User::setPathDepth(1);

User::route('account_delete');

if (!isset($_REQUEST['id'])) {
    die("invalid request.");
}

$id = $_REQUEST['id'];
$action = $_REQUEST['action'];

if ($action == 'disable') {
    Account::activateAccount($id, Account::STATUS_DEACTIVATED);
} else if ($action == 'enable') {
    Account::activateAccount($id, Account::STATUS_ENABLED);
} else {
    die("invalid request.");
}

User::redirect('home.php');
