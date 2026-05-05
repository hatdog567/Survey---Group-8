<?php
require_once 'config/db.php';
try {
    $pdo->exec("ALTER TABLE family_members ADD COLUMN existing_condition VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN condition_details VARCHAR(255) DEFAULT NULL");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN taking_medication VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN recent_surgery VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN pregnant VARCHAR(10) DEFAULT 'N/A'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN donor_consent VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN donated_before VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE family_members ADD COLUMN last_donation_date DATE DEFAULT NULL");
    
    // Add same fields for Head of Family in health_records if they don't exist, wait they already exist except donor_consent and donated_before
    // We already added condition stuff to health_records. Let's add consent.
    $pdo->exec("ALTER TABLE health_records ADD COLUMN donor_consent VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN donated_before VARCHAR(10) DEFAULT 'No'");
    $pdo->exec("ALTER TABLE health_records ADD COLUMN last_donation_date DATE DEFAULT NULL");

    echo "DB updated successfully.\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
