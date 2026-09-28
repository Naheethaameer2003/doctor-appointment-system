<?php
/* ============================================================
   db.php — Database Connection & Auth Helper
   Project  : Dr. Zamzameer Appointment Booking System
   Database : MySQL (zamzameer_db)
   Requires : PHP 7.4+ with PDO & pdo_mysql extension enabled
   Usage    : require_once 'db.php';  (top of every PHP page)
   ============================================================ */

// ── 1. SESSION START ─────────────────────────────────────────
// Start the session so $_SESSION is available across all pages.
// Must be called before any output (HTML/echo).
// Used for: admin login state ($_SESSION['admin_logged_in'])
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ── 2. DATABASE CREDENTIALS ──────────────────────────────────
// Change these values to match your hosting environment.
//
// Local (XAMPP)  → host: localhost, user: root, pass: ''
// cPanel hosting → host: localhost, user: cpanel_dbuser, pass: yourpassword

define('DB_HOST', 'localhost');       // MySQL server hostname
define('DB_NAME', 'zamzameer_db');    // Database name (create this in phpMyAdmin)
define('DB_USER', 'root');            // MySQL username
define('DB_PASS', '');                // MySQL password (empty for XAMPP default)
define('DB_CHARSET', 'utf8mb4');      // UTF-8 full charset — supports Tamil, Arabic, emoji


// ── 3. PDO CONNECTION ────────────────────────────────────────
// PDO (PHP Data Objects) is used instead of mysqli for:
//   • Prepared statements → prevents SQL injection
//   • Exception-based error handling
//   • Easy to switch DB driver later if needed
//
// DSN = Data Source Name string that PDO uses to connect.
$dsn = 'mysql:host=' . DB_HOST
     . ';dbname='    . DB_NAME
     . ';charset='   . DB_CHARSET;

// PDO options array — best-practice settings
$options = [
    // Throw exceptions on DB errors instead of silent failures
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Return rows as associative arrays: $row['column_name']
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Disable PDO emulated prepares → use real MySQL prepared statements
    // This is more secure and gives accurate param type binding
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create the global $pdo object used by all pages
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // ── Connection failed ──
    // In production: log $e->getMessage() to a file, show a friendly error.
    // Never expose raw DB errors to the public — it leaks server info.
    error_log('[zamzameer] DB connection failed: ' . $e->getMessage());

    // Show a clean error page and stop execution
    http_response_code(503);
    echo '<!DOCTYPE html><html><head>
            <title>Service Unavailable</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
          </head><body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
            <div class="text-center p-5">
              <div class="fs-1 mb-3">⚠️</div>
              <h4 class="fw-bold">Database Unavailable</h4>
              <p class="text-secondary">Could not connect to the database. Please try again later.</p>
              <a href="index.php" class="btn btn-primary rounded-pill">← Back to Home</a>
            </div>
          </body></html>';
    exit;
}


// ── 4. ADMIN AUTH GUARD ──────────────────────────────────────
// requireLogin() — call this at the top of admin-only pages.
//
// How it works:
//   1. Checks if $_SESSION['admin_logged_in'] is true
//   2. If not → redirects to login.php and stops execution
//
// Usage example (in admin.php):
//   require_once 'db.php';
//   requireLogin();   ← must come before any DB queries or HTML output
//
function requireLogin(): void
{
    if (empty($_SESSION['admin_logged_in'])) {
        // Not logged in → send to login page
        header('Location: login.php');
        exit; // IMPORTANT: always exit after header redirect
    }
}
