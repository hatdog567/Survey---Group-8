<?php try { new PDO('mysql:host=localhost', 'root', ''); echo 'NO_PASS'; } catch(Exception $e) { echo $e->getMessage(); } ?>

