<?php
// admin/login.php
declare(strict_types=1);
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header("Location: index.php");
    exit;
}

// Important includes
require_once '../includes/db.php';
require_once '../src/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            
            // Clear old logs on successful login
            $_SESSION['function_logs'] = [];

            f_log_function_call('admin_login_success', ['username' => $username], 'Login successful');

            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid username or password';
            f_log_error('admin_login', 'Failed login attempt', [
                'username' => $username,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        }
    } catch (Exception $e) {
        $error = 'System error. Please try again later.';
        f_log_error('admin_login', $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Ambrose Archery</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .error { color: red; font-weight: bold; }
        form { max-width: 400px; margin: 50px auto; padding: 20px; }
        input { display: block; width: 100%; margin: 10px 0; padding: 10px; box-sizing: border-box; }
        button { padding: 12px; font-size: 16px; width: 100%; }
    </style>
</head>
<body>
    <h1>Ambrose Archery Admin</h1>
    
    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autofocus>
        
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        
        <button type="submit">Login</button>
    </form>
</body>
</html>