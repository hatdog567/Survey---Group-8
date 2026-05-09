<?php
// register.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    session_start();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');

    if (empty($email) || empty($password) || empty($fullName)) {
        header('Location: ../../client/index.html?error=empty_fields');
        exit;
    }


    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header('Location: ../../client/index.html?error=email_exists');
            exit;
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$email, $hashedPassword, $fullName]);

        // Redirect back to login screen
        header('Location: ../../client/index.html?success=registered');
        exit;
    } catch(PDOException $e) {
        header('Location: ../../client/index.html?error=db_error');
        exit;
    }
} else {
    header('Location: ../../client/index.html');
    exit;
}
?>

