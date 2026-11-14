<?php
// ============================================================
// Database configuration — EXAMPLE TEMPLATE
// ============================================================
// This is the template for config/db.php.
//
// To set up locally:
//   cp config/db.example.php config/db.php
//   Edit config/db.php with real credentials
//
// The real config/db.php is .gitignored — it never goes to GitHub.
//
// For InfinityFree:
//   1. Login to https://dash.infinityfree.com/
//   2. Your account → MySQL Databases
//   3. Copy the credentials to config/db.php
// ============================================================

$host     = 'YOUR_DB_HOST';       // e.g. sqlXXX.infinityfree.com
$dbname   = 'YOUR_DB_NAME';       // e.g. if0_XXXXXXXX_portfolio
$username = 'YOUR_DB_USER';       // e.g. if0_XXXXXXXX
$password = 'YOUR_DB_PASS';       // Set on InfinityFree → MySQL → Change Password
$port     = '3306';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log instead of echoing (prevents leaking credentials in errors)
    error_log('DB connection failed: ' . $e->getMessage());
    die('Database connection failed. Please check config/db.php');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
