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
    if (!is_dir($upload_dir))
        mkdir($upload_dir, 0777, true);

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'application/pdf'];

    function secure_upload($file_input_name, $prefix, $upload_dir, $allowed_extensions, $allowed_mime_types)
    {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
            $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
            $file_name = $_FILES[$file_input_name]['name'];
            $file_size = $_FILES[$file_input_name]['size'];

            // Check file extension
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            if (!in_array($file_extension, $allowed_extensions)) {
                return false; // Invalid extension
            }

            // Check MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file_tmp_path);

            if (!in_array($mime_type, $allowed_mime_types)) {
                return false; // Invalid MIME type
            }

            // Generate secure filename
            $new_file_name = time() . '_' . $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $file_extension;
            move_uploaded_file($file_tmp_path, $upload_dir . $new_file_name);
            return $new_file_name;
        }
        return '';
    }

    $id_front = secure_upload('valid_id_front', 'f', $upload_dir, $allowed_extensions, $allowed_mime_types);
    $id_back = secure_upload('valid_id_back', 'b', $upload_dir, $allowed_extensions, $allowed_mime_types);
    $brgy_clearance = secure_upload('barangay_clearance', 'c', $upload_dir, $allowed_extensions, $allowed_mime_types);


    try {
        // Insert vendor data with a pending status and file paths
        $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, owner_name, business_type, contact_number, address, status, id_front, id_back, brgy_clearance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $business_name, $owner_name, $business_type, $contact_number, $address, 'pending', $id_front, $id_back, $brgy_clearance]);

        // Return JSON for AJAX response
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>