<?php
require_once '../config/db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../client/index.html');
    exit;
}

if (isset($_GET['id'])) {
    $vendor_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Only allow deletion of their own rejected applications
    $stmt = $pdo->prepare("DELETE FROM vendors WHERE id = ? AND user_id = ? AND status = 'rejected'");
    $stmt->execute([$vendor_id, $user_id]);
}

header('Location: ../../client/vendor_registration.php');
exit;
?>
