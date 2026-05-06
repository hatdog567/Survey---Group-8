<?php
require 'config/db.php';
try {
    $stmt = $pdo->prepare('INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(['test@example.com', 'testpass', 'Test User', 'user']);
    echo 'Success';
} catch (Exception $e) {
    echo $e->getMessage();
}
?>