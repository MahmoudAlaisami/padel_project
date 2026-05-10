<?php
$pageTitle = 'Sign In';
require_once __DIR__ . '/../includes/functions.php';
startSession();

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? '/admin/index.php' : '/pages/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission.';
    } else {
        $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Please enter your email and password.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id, full_name, password, role FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($id, $fullName, $hash, $role);
            $stmt->fetch();
            $stmt->close();

            if ($id && password_verify($password, $hash)) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $id;
                $_SESSION['full_name'] = $fullName;
                $_SESSION['role']      = $role;

                header('Location: ' . ($role === 'admin' ? '/admin/index.php' : '/pages/dashboard.php'));
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | PadelPro</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">&#127934; PadelPro</div>
        <p class="auth-subtitle">Welcome back — sign in to continue</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php $flash = getFlash('success'); if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                    placeholder="you@example.com" required
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                    placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">Sign In</button>
        </form>

        <p class="auth-footer">Don't have an account? <a href="/pages/signup.php">Sign up free</a></p>
        <p class="auth-footer" style="margin-top:.5rem;">
            <small style="color:var(--muted);">Demo admin: admin@padel.com / password</small>
        </p>
    </div>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
