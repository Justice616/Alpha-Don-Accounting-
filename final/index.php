<?php

session_start();

require_once __DIR__ . '/inc/user.php';

User::route();
