<?php
require 'config/db.php';
try {
    $pdo->exec("ALTER TABLE health_records ADD COLUMN age INT DEFAULT NULL");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN blood_type VARCHAR(10) DEFAULT 'Unknown'");
    echo "Columns added to health_records.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
