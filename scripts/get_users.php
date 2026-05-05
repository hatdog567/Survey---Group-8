<?php require 'db.php'; $stmt = $pdo->query('SELECT email, password FROM users'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); ?>

