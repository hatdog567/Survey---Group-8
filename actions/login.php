<?php
// login.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        header('Location: ../index.html?error=empty_fields');
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, email, password, full_name, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Password is correct
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];

            if ($user['role'] === 'admin') {
                header('Location: ../admin_dashboard.php');
            } else {
                header('Location: ../user_dashboard.php');
            }
            exit;
        } else {
            header('Location: ../index.html?error=invalid_credentials');
            exit;
        }
    } catch(PDOException $e) {
        header('Location: ../index.html?error=db_error');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>


