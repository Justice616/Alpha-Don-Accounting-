<?php

session_start();

require_once __DIR__ . '/../src/user.php';
require_once __DIR__ . '/../src/journal.php';

User::setPathDepth(1);

User::route(TRUE);

$type = User::getUserType();
if ($type != User::TYPE_MANAGER)
    die("only manager can approve of journal entries.");

$accept = ($_REQUEST['accept'] == "1");
$ref = $_REQUEST['ref'];
$response = $_REQUEST['response'] == "" ? NULL : $_REQUEST['response'];

$status = $accept ? 2 : 1;

$entry = Journal::getJournalEntry($ref);

if ($entry === FALSE) {
    die("invalid request");
}

Journal::setJournalEntryStatus($ref, $status, $response);


if ($accept) {
    echo "<h3> the posting was accepted </h3>";
} else {
    echo "<h3> the posting was rejected </h3>";
}

User::refresh(3, 'journal/view.php');
?>

<br>
<br>
<br>
<a href="view.php"><button>go back</button></a>
