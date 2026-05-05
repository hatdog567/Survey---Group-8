<?php
require 'config/db.php';
foreach($pdo->query("SHOW CREATE TABLE donors") as $row) {
    echo $row['Create Table'] . "\n";
}
?>
