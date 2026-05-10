<?php
require_once __DIR__ . '/../config/db.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /pages/signin.php');
        exit;
    }
}

function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /pages/dashboard.php');
        exit;
    }
}

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string {
    startSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function flashMessage(string $key, string $message): void {
    startSession();
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): string {
    startSession();
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return '';
}

function calculatePrice(int $pitchTypeId, int $ballTypeId, int $racketTypeId, int $numBalls, int $numRackets, string $startTime, string $endTime): array {
    $db = getDB();

    $start = new DateTime($startTime);
    $end = new DateTime($endTime);
    $hours = ($end->getTimestamp() - $start->getTimestamp()) / 3600;

    $pitchPrice = 0;
    $ballUnitPrice = 0;
    $racketUnitPrice = 0;

    // Fetch pitch price
    $stmt = $db->prepare('SELECT price FROM item_types WHERE id = ?');
    $stmt->bind_param('i', $pitchTypeId);
    $stmt->execute();
    $stmt->bind_result($pitchPrice);
    $stmt->fetch();
    $stmt->close();

    $pitchTotal = $pitchPrice * $hours;

    // Fetch ball price
    if ($ballTypeId > 0 && $numBalls > 0) {
        $stmt = $db->prepare('SELECT price FROM item_types WHERE id = ?');
        $stmt->bind_param('i', $ballTypeId);
        $stmt->execute();
        $stmt->bind_result($ballUnitPrice);
        $stmt->fetch();
        $stmt->close();
    }
    $ballTotal = $ballUnitPrice * $numBalls;

    // Fetch racket price
    if ($racketTypeId > 0 && $numRackets > 0) {
        $stmt = $db->prepare('SELECT price FROM item_types WHERE id = ?');
        $stmt->bind_param('i', $racketTypeId);
        $stmt->execute();
        $stmt->bind_result($racketUnitPrice);
        $stmt->fetch();
        $stmt->close();
    }
    $racketTotal = $racketUnitPrice * $numRackets;

    $grandTotal = $pitchTotal + $ballTotal + $racketTotal;

    return [
        'hours'         => round($hours, 2),
        'pitch_unit'    => $pitchPrice,
        'pitch_total'   => $pitchTotal,
        'ball_unit'     => $ballUnitPrice,
        'ball_total'    => $ballTotal,
        'racket_unit'   => $racketUnitPrice,
        'racket_total'  => $racketTotal,
        'grand_total'   => $grandTotal,
    ];
}

function hasOverlap(int $pitchTypeId, string $startTime, string $endTime, int $excludeId = 0): bool {
    $db = getDB();
    $sql = 'SELECT id FROM reservations
            WHERE pitch_type_id = ?
              AND status != "cancelled"
              AND id != ?
              AND start_time < ?
              AND end_time > ?';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('iiss', $pitchTypeId, $excludeId, $endTime, $startTime);
    $stmt->execute();
    $stmt->store_result();
    $count = $stmt->num_rows;
    $stmt->close();
    return $count > 0;
}
