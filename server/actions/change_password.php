<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../client/index.html');
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        header('Location: ../../client/user_settings.php?pwd_error=' . urlencode('All fields are required.'));
        exit;
    }

    if ($new_password !== $confirm_password) {
        header('Location: ../../client/user_settings.php?pwd_error=' . urlencode('New passwords do not match.'));
        exit;
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        header('Location: ../../client/user_settings.php?pwd_error=' . urlencode('Password must be at least 8 characters long, include an uppercase letter, a number, and a special character.'));
        exit;
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password'])) {
        header('Location: ../../client/user_settings.php?pwd_error=' . urlencode('Current password is incorrect.'));
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hashed_password, $user_id]);

    header('Location: ../../client/user_settings.php?pwd_success=1');
    exit;
} else {
    header('Location: ../../client/user_settings.php');
    exit;
}