<?php
// admin/index.php - Admin Dashboard
declare(strict_types=1);
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../src/functions.php';

f_log_function_call('render_admin_dashboard', [], 'Admin dashboard loaded');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ambrose Archery</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
        }
        .card h3 { margin: 0 0 15px 0; }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #0066cc;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
        }
        .btn:hover { background: #0055aa; }
    </style>
</head>
<body>
    <h1>Ambrose Archery Admin Dashboard</h1>
    
    <p>
        Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong> | 
        <a href="function-calls.php">View Function Logs</a> | 
        <a href="setup.php">System Setup</a> | 
        <a href="login.php?logout=1">Logout</a>
    </p>

    <div class="dashboard-grid">
        
        <div class="card">
            <h3>📅 Booking System</h3>
            <p>Manage bookings and availability</p>
            <a href="#" class="btn">View Today's Bookings</a>
        </div>

        <div class="card">
            <h3>⚙️ Configuration</h3>
            <p>Business hours &amp; overrides</p>
            <a href="setup.php" class="btn">Go to Setup</a>
        </div>

        <div class="card">
            <h3>👥 Members</h3>
            <p>Manage memberships</p>
            <a href="#" class="btn">View Members</a>
        </div>

        <div class="card">
            <h3>📊 Reports</h3>
            <p>No-shows, revenue, etc.</p>
            <a href="#" class="btn">View Reports</a>
        </div>

    </div>
</body>
</html>