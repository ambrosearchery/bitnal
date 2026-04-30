<?php
// admin/function-calls.php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.php");
    exit;
}

require_once '../src/functions.php';

// Handle Logging Toggle
if (isset($_GET['toggle_logging'])) {
    $enabled = $_GET['toggle_logging'] === '1';
    f_toggle_logging($enabled);
    header("Location: function-calls.php");
    exit;
}

// Handle Export (available anytime there are logs)
if (isset($_GET['export']) && !empty($_SESSION['function_logs'])) {
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="ambrose_logs_' . date('Y-m-d_H-i-s') . '.txt"');
    
    echo "Ambrose Archery - Function & SQL Logs\n";
    echo "Exported on: " . date('Y-m-d H:i:s') . "\n\n";
    echo str_repeat("=", 80) . "\n\n";

    foreach ($_SESSION['function_logs'] as $log) {
        echo $log['timestamp'] . " | " . ($log['type'] ?? 'PHP') . " | " . ($log['function'] ?? '') . "\n";
        if (!empty($log['input']))  echo "Input:  " . $log['input'] . "\n";
        if (!empty($log['output'])) echo "Output: " . $log['output'] . "\n";
        if (isset($log['execution_time'])) echo "Time:   " . $log['execution_time'] . "s\n";
        echo str_repeat("-", 70) . "\n";
    }
    exit;
}

f_log_function_call('render_function_calls_page', $_GET, 'Log page viewed', 'PHP');

$logs = $_SESSION['function_logs'] ?? [];

// Filters
$errors_only = isset($_GET['errors_only']) && $_GET['errors_only'] == 1;
$php_only    = isset($_GET['php_only']) && $_GET['php_only'] == 1;
$sql_only    = isset($_GET['sql_only']) && $_GET['sql_only'] == 1;
$js_only     = isset($_GET['js_only']) && $_GET['js_only'] == 1;

if ($errors_only) $logs = array_filter($logs, fn($l) => ($l['type']??'') === 'ERROR' || stripos($l['output']??'', 'ERROR') !== false);
if ($php_only)    $logs = array_filter($logs, fn($l) => ($l['type']??'') === 'PHP');
if ($sql_only)    $logs = array_filter($logs, fn($l) => ($l['type']??'') === 'SQL');
if ($js_only)     $logs = array_filter($logs, fn($l) => ($l['type']??'') === 'JS');

$logs = array_reverse($logs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Function Calls Log - Ambrose Archery</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50 !important; color: white !important; }
        .PHP   { background: #e6f7ff; }
        .SQL   { background: #f0fff0; }
        .ERROR { background: #ffecec; font-weight: bold; }
        .JS    { background: #fffbe6; }
        
        .memory-low    { color: #27ae60; font-weight: bold; }
        .memory-medium { color: #f39c12; font-weight: bold; }
        .memory-high   { color: #e74c3c; font-weight: bold; }

        .time, .memory { font-family: monospace; }
        pre { margin: 0; white-space: pre-wrap; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Function & SQL Calls Log</h1>
    
    <p>
        <a href="index.php">← Dashboard</a> | 
        <a href="login.php?logout=1">Logout</a>
    </p>

    <!-- Logging Toggle -->
    <p>
        <strong>Logging Status:</strong> 
        <?php if ($ENABLE_LOGGING): ?>
            <span style="color:green">● ON</span>
            <a href="?toggle_logging=0" style="color:white; background:#e74c3c; padding:6px 12px; border-radius:4px; text-decoration:none;">Turn OFF</a>
        <?php else: ?>
            <span style="color:red">● OFF</span>
            <a href="?toggle_logging=1" style="color:white; background:#27ae60; padding:6px 12px; border-radius:4px; text-decoration:none;">Turn ON</a>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['function_logs'])): ?>
            <a href="?export=1" style="margin-left:20px; color:blue; text-decoration:underline;">↓ Export Logs to .txt</a>
        <?php endif; ?>
    </p>

    <input type="text" id="searchBox" placeholder="Search functions, queries, or output..." 
           style="padding:10px; width:400px; margin:10px 0;" onkeyup="filterTable()">

    <p>
        <label><input type="checkbox" id="errorsOnly" onchange="applyFilters()" <?= $errors_only ? 'checked' : '' ?>> Errors Only</label> &nbsp;
        <label><input type="checkbox" id="phpOnly" onchange="applyFilters()" <?= $php_only ? 'checked' : '' ?>> PHP Only</label> &nbsp;
        <label><input type="checkbox" id="sqlOnly" onchange="applyFilters()" <?= $sql_only ? 'checked' : '' ?>> SQL Only</label> &nbsp;
        <label><input type="checkbox" id="jsOnly" onchange="applyFilters()" <?= $js_only ? 'checked' : '' ?>> Javascript Only</label>
    </p>

    <?php if (empty($logs)): ?>
        <p><em>No logs found. Try turning logging ON or changing filters.</em></p>
    <?php else: ?>
        <table id="logTable">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>Function / Query</th>
                    <th>Input</th>
                    <th>Output</th>
                    <th>Execution Time</th>
                    <th>Memory Usage</th>
                    <th>Peak Memory</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): 
                    $type = $log['type'] ?? 'PHP';
                    $mem = isset($log['memory_usage']) ? (float)$log['memory_usage'] : 0;
                    $memClass = $mem < 2 ? 'memory-low' : ($mem < 5 ? 'memory-medium' : 'memory-high');
                ?>
                    <tr class="<?= htmlspecialchars($type) ?>">
                        <td><?= htmlspecialchars($log['timestamp'] ?? '') ?></td>
                        <td><strong><?= htmlspecialchars($type) ?></strong></td>
                        <td><?= htmlspecialchars($log['function'] ?? '') ?></td>
                        <td><pre><?= htmlspecialchars(substr($log['input'] ?? '', 0, 120)) ?></pre></td>
                        <td><pre><?= htmlspecialchars(substr($log['output'] ?? '', 0, 120)) ?></pre></td>
                        <td class="time"><?= isset($log['execution_time']) ? number_format($log['execution_time'], 6) . 's' : '-' ?></td>
                        <td class="memory <?= $memClass ?>"><?= $log['memory_usage'] ?? '-' ?></td>
                        <td class="memory"><?= $log['memory_peak'] ?? '-' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <script>
        function filterTable() {
            const input = document.getElementById('searchBox').value.toLowerCase();
            const rows = document.querySelectorAll('#logTable tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
            });
        }

        function applyFilters() {
            let url = 'function-calls.php?';
            if (document.getElementById('errorsOnly').checked) url += 'errors_only=1&';
            if (document.getElementById('phpOnly').checked) url += 'php_only=1&';
            if (document.getElementById('sqlOnly').checked) url += 'sql_only=1&';
            if (document.getElementById('jsOnly').checked) url += 'js_only=1&';
            window.location.href = url;
        }
    </script>
</body>
</html>