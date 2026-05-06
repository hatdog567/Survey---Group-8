<?php
// submit_vendor.php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $business_name = $_POST['business_name'] ?? '';
    $owner_name = $_POST['owner_name'] ?? '';
    $business_type = $_POST['business_type'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $address = $_POST['address'] ?? '';

    try {
        // Insert vendor data with a pending status
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, owner_name, business_type, contact_number, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $business_name, $owner_name, $business_type, $contact_number, $address, 'pending']);
        
        // Return JSON for AJAX response
        echo json_encode(['success' => true]);
        exit;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

