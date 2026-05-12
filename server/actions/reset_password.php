<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($email) || empty($new_password)) {
        header('Location: ../../client/index.html?error=empty_fields');
        exit;
    }

    try {
        // Check if the email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Update the password in the database
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $updateStmt->execute([$hashed_password, $email]);

            // Redirect with success message
            header('Location: ../../client/index.html?success=password_updated');
            exit;
        } else {
            // Email not found
            header('Location: ../../client/index.html?error=email_not_found');
            exit;
        }
    } catch (PDOException $e) {
        header('Location: ../../client/index.html?error=db_error');
        exit;
    }
} else {
    header('Location: ../../client/index.html');
    exit;
}
?>