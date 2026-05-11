<?php
require 'db.php';
try {
    $pdo->exec('ALTER TABLE vendors ADD COLUMN id_front TEXT');
    $pdo->exec('ALTER TABLE vendors ADD COLUMN id_back TEXT');
    $pdo->exec('ALTER TABLE vendors ADD COLUMN brgy_clearance TEXT');
    echo 'Columns added successfully.';
} catch(Exception $e) {
    echo $e->getMessage();
}
?>
