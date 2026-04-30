<?php
// src/functions.php
// CENTRAL FUNCTION LOADER - Keep this file clean and small

// 1. Database Connection (must be first)
require_once __DIR__ . '/../includes/db.php';

// 2. Core Utilities (Logging, f_db_query, f_log_call, etc.)
require_once __DIR__ . '/functions/utils.php';

// 3. Configuration & Setup Functions (Phase 2)
require_once __DIR__ . '/functions/config.php';

// 4. Availability Engine (Functions 9, 10, 11+)
require_once __DIR__ . '/functions/availability.php';

// 5. Rendering Functions
require_once __DIR__ . '/functions/render.php';

// =============================================
// FUTURE MODULES (uncomment when ready)
// =============================================
// require_once __DIR__ . '/functions/booking.php';
// require_once __DIR__ . '/functions/member.php';
// require_once __DIR__ . '/functions/manage.php';
// require_once __DIR__ . '/functions/admin.php';

// =============================================
// Global Logging Default
// =============================================
if (!isset($_SESSION['enable_logging'])) {
    $_SESSION['enable_logging'] = true;
}
?>