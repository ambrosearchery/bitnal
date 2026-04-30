<?php
// src/functions/utils.php

// =============================================
// GLOBAL LOGGING CONTROL
// =============================================
if (!isset($_SESSION['enable_logging'])) {
    $_SESSION['enable_logging'] = true;
}

$ENABLE_LOGGING = $_SESSION['enable_logging'];

function f_toggle_logging(bool $enabled): void {
    $_SESSION['enable_logging'] = $enabled;
    global $ENABLE_LOGGING;
    $ENABLE_LOGGING = $enabled;

    if (!$enabled && isset($_SESSION['function_logs'])) {
        $_SESSION['function_logs'] = [];
    }
}

// =============================================
// LOGGING FUNCTIONS
// =============================================
function f_log_function_call(string $function_name, $input = [], $output = null, string $type = 'PHP'): void {
    global $ENABLE_LOGGING;
    if (!$ENABLE_LOGGING && $type !== 'ERROR') return;

    if (!isset($_SESSION['function_logs'])) {
        $_SESSION['function_logs'] = [];
    }

    $log_entry = [
        'timestamp'      => date('Y-m-d H:i:s'),
        'type'           => $type,
        'function'       => $function_name,
        'input'          => is_array($input) ? json_encode($input, JSON_UNESCAPED_SLASHES) : $input,
        'output'         => is_array($output) || is_object($output) ? json_encode($output, JSON_UNESCAPED_SLASHES) : $output,
        'execution_time' => null,
        'memory_usage'   => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'memory_peak'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB'
    ];

    $_SESSION['function_logs'][] = $log_entry;
    if (count($_SESSION['function_logs']) > 200) {
        $_SESSION['function_logs'] = array_slice($_SESSION['function_logs'], -200);
    }
}

function f_log_error(string $function_name, string $message, $data = null) {
    global $ENABLE_LOGGING;
    
    $log_dir = __DIR__ . '/../../public/logs';
    if (!file_exists($log_dir)) mkdir($log_dir, 0755, true);
    
    $entry = date('Y-m-d H:i:s') . " | ERROR | $function_name | $message\n";
    file_put_contents($log_dir . '/errors.log', $entry, FILE_APPEND);

    if ($ENABLE_LOGGING) {
        f_log_function_call($function_name, $data, 'ERROR: ' . $message, 'ERROR');
    }
}

function f_log_call(string $function_name, callable $callback, $input = [], string $type = 'PHP') {
    global $ENABLE_LOGGING;
    $start = microtime(true);

    try {
        $output = $callback();
        $execution_time = microtime(true) - $start;

        if ($ENABLE_LOGGING) {
            f_log_function_call($function_name, $input, $output, $type);
            if (!empty($_SESSION['function_logs'])) {
                $last = count($_SESSION['function_logs']) - 1;
                $_SESSION['function_logs'][$last]['execution_time'] = round($execution_time, 6);
            }
        }
        return $output;
    } catch (Exception $e) {
        f_log_error($function_name, $e->getMessage(), $input);
        throw $e;
    }
}

// Database helper
function f_db_query(string $query, array $params = [], string $context = '') {
    global $ENABLE_LOGGING;
    $start = microtime(true);

    try {
        global $pdo;
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        $execution_time = microtime(true) - $start;

        if ($ENABLE_LOGGING) {
            f_log_function_call($query, $params, ['rows' => $stmt->rowCount(), 'time' => round($execution_time, 6)], 'SQL');
        }
        return $stmt;
    } catch (Exception $e) {
        f_log_error($context ?: 'f_db_query', $e->getMessage(), ['query' => $query, 'params' => $params]);
        throw $e;
    }
}
?>