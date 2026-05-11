<?php
// submit_vendor.php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../client/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $business_name = $_POST['business_name'] ?? '';
    $owner_name = $_POST['owner_name'] ?? '';
    $business_type = $_POST['business_type'] ?? '';
    $contact_number = $_POST['contact_number'] ?? '';
    $address = $_POST['address'] ?? '';

    $id_front = '';
    $id_back = '';
    $brgy_clearance = '';

    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['valid_id_front']) && $_FILES['valid_id_front']['error'] == 0) {
        $id_front = time() . '_f_' . basename($_FILES['valid_id_front']['name']);
        move_uploaded_file($_FILES['valid_id_front']['tmp_name'], $upload_dir . $id_front);
    }
    if (isset($_FILES['valid_id_back']) && $_FILES['valid_id_back']['error'] == 0) {
        $id_back = time() . '_b_' . basename($_FILES['valid_id_back']['name']);
        move_uploaded_file($_FILES['valid_id_back']['tmp_name'], $upload_dir . $id_back);
    }
    if (isset($_FILES['barangay_clearance']) && $_FILES['barangay_clearance']['error'] == 0) {
        $brgy_clearance = time() . '_c_' . basename($_FILES['barangay_clearance']['name']);
        move_uploaded_file($_FILES['barangay_clearance']['tmp_name'], $upload_dir . $brgy_clearance);
    }

    try {
        // Insert vendor data with a pending status and file paths
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, owner_name, business_type, contact_number, address, status, id_front, id_back, brgy_clearance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $business_name, $owner_name, $business_type, $contact_number, $address, 'pending', $id_front, $id_back, $brgy_clearance]);
        
        // Return JSON for AJAX response
        echo json_encode(['success' => true]);
        exit;
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

