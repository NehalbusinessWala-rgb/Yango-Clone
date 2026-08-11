<?php
// config/db.php - Database connection setup using PDO
 
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Fallback or primary username
define('DB_PASS', '123456');
define('DB_NAME', 'rsoa_rsoa_rsoa324_25');
 
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            // First try connecting directly to the specific database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Fallback attempt with alternative username if needed or server root without dbname
            try {
                $dsn_root = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
                $pdo = new PDO($dsn_root, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `" . DB_NAME . "`");
            } catch (PDOException $ex) {
                // If db fails, send json error if API, or throw
                if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Database connection failed. Please run install.php first. Error: ' . $ex->getMessage()
                    ]);
                    exit;
                }
                die("Database Connection Error: " . $ex->getMessage() . "<br><a href='install.php'>Click here to run Auto-Installer</a>");
            }
        }
    }
    return $pdo;
}
 
// Start session safely across all script loads
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
