<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE health_records");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $c) {
    echo $c['Field'] . " - " . $c['Type'] . "\n";
}
?>
