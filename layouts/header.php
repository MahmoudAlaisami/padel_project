<?php
require_once __DIR__ . '/../includes/functions.php';
startSession();
$pageTitle = $pageTitle ?? 'Padel Pitch Reservation';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | PadelPro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
