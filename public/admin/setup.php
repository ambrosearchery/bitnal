<?php
// admin/setup.php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../src/functions.php';

f_log_function_call('render_setup_page', [], 'Admin Setup page loaded');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup - Ambrose Archery</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .setup-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px; 
            margin-top: 20px;
        }
        .card { 
            border: 1px solid #ddd; 
            padding: 25px; 
            border-radius: 10px;
            background: #fafafa;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        input[type="time"], input[type="number"], input[type="date"], input[type="text"] {
            padding: 8px; width: 100%; box-sizing: border-box;
        }
        button { padding: 12px 20px; font-size: 16px; }
    </style>
</head>
<body>
    <h1>Ambrose Archery - System Setup</h1>
    <p>
        <a href="index.php">← Dashboard</a> | 
        <a href="function-calls.php">View Function Log</a>
    </p>

    <div class="setup-grid">
        
        <!-- Weekly Normal Hours -->
        <div class="card">
            <h2>1. Normal Weekly Hours</h2>
            <form method="POST" action="setup-save.php">
                <input type="hidden" name="action" value="save_normal_hours">
                
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Open Time</th>
                            <th>Close Time</th>
                            <th>Default Lanes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        for ($i = 0; $i < 7; $i++): ?>
                        <tr>
                            <td><?= $dayNames[$i] ?></td>
                            <td><input type="time" name="normal[<?= $i ?>][open]" value="09:00"></td>
                            <td><input type="time" name="normal[<?= $i ?>][close]" value="21:00"></td>
                            <td><input type="number" name="normal[<?= $i ?>][lanes]" value="12" min="1" max="20"></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
                <br>
                <button type="submit">Save Weekly Schedule</button>
            </form>
        </div>

        <!-- Date Overrides -->
        <div class="card">
            <h2>2. Date-Specific Overrides</h2>
            <form method="POST" action="setup-save.php">
                <input type="hidden" name="action" value="save_date_override">
                
                <label>Date:</label>
                <input type="date" name="override_date" required><br><br>
                
                <label>Description / Reason:</label>
                <input type="text" name="description" placeholder="e.g. Christmas Day or Kyudo" required><br><br>
                
                <label>Open Time (leave blank if closed):</label>
                <input type="time" name="open_time"><br><br>
                
                <label>Close Time:</label>
                <input type="time" name="close_time"><br><br>
                
                <label>Available Lanes:</label>
                <input type="number" name="default_lanes" value="12" min="0" max="20"><br><br>
                
                <label><input type="checkbox" name="is_closed"> Fully Closed</label><br><br>
                
                <button type="submit">Save Override</button>
            </form>
        </div>
    </div>
</body>
</html>
