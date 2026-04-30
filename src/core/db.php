<?php
// includes/db.php
// Central Database Connection for Ambrose Archery

declare(strict_types=1);

if (!isset($pdo)) {
    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=ambrose_archery;charset=utf8mb4',
            'root',           // Change this in production
            '',               // Change this in production
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        
        // Optional: Set timezone to Australia/Melbourne
        $pdo->exec("SET time_zone = '+10:00'");
        
    } catch (PDOException $e) {
        // Log critical database error
        error_log("Database Connection Failed: " . $e->getMessage());
        
        // Show friendly message in development
        if (ini_get('display_errors')) {
            die("Database connection failed. Please check your settings.");
        } else {
            die("System is temporarily unavailable. Please try again later.");
        }
    }
}

// Make $pdo available globally
global $pdo;