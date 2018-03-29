<?php

session_start();

require_once __DIR__ . '/inc/user.php';

session_destroy();
session_unset();  

User::redirect("index.php");
