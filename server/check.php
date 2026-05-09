<?php require 'config/db.php'; print_r($pdo->query('DESCRIBE vendors')->fetchAll(PDO::FETCH_ASSOC)); ?>
