<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE health_records ADD COLUMN existing_condition VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN condition_details VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN taking_medication VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN recent_surgery VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN pregnant VARCHAR(10) DEFAULT 'N/A'");
    $pdo->exec("ALTER TABLE donors ADD COLUMN last_donation DATE DEFAULT NULL");
    echo "DB updated successfully.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
