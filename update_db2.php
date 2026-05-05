<?php
require 'config/db.php';
try {
    $pdo->exec("ALTER TABLE health_records ADD COLUMN contact_number VARCHAR(20) DEFAULT NULL");
    echo "Added contact_number to health_records.\n";
} catch (Exception $e) {}

try {
    $pdo->exec("ALTER TABLE family_members ADD COLUMN relationship VARCHAR(50) DEFAULT 'Member'");
    echo "Added relationship to family_members.\n";
} catch (Exception $e) {}

echo "Done.\n";
?>
