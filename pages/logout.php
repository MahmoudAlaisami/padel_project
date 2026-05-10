<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
session_destroy();
header('Location: /pages/signin.php');
exit;
