<?php

session_start();

require_once __DIR__ . '/src/user.php';

User::route();

?>
