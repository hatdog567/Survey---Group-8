<?php
require 'config/db.php';
$stmt = $pdo->query('SELECT id, email, password, full_name FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($users);
?>
