<?php
// register.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');

    if (empty($email) || empty($password) || empty($fullName)) {
        header('Location: ../index.html?error=empty_fields');
        exit;
    }

    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
        header('Location: ../index.html?error=weak_password');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email is already registered.';
            header('Location: ../index.html');
            exit;
        }

        // Hash password and insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$email, $hashedPassword, $fullName]);

        // Redirect back to login screen
        header('Location: ../index.html?success=registered');
        exit;
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Database error during registration.';
        header('Location: ../index.html');
        exit;
    }
} else {
    header('Location: ../index.html');
    exit;
}
?>

