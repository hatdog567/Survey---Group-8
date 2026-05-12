<?php
// db.php
// Set secure session parameters before starting the session
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '', // Let the browser infer the domain to prevent localhost port issues
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // True if using HTTPS
    'httponly' => true, // Prevent JavaScript access to session cookie
    'samesite' => 'Lax' // Protect against CSRF
]);

session_start();

// Generate CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$host = 'localhost';
$dbname = 'servicio_db';
$username = 'root'; // Change if your MySQL user is different
$password = 'bana_night';     // Change if your MySQL has a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback: try with empty password for portability to other PCs (like fresh XAMPP installs)
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e2) {
        die("Database connection failed. Please check your username and password in db.php. Error: " . $e2->getMessage());
    }
}
?>