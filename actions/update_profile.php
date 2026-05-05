<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $blood_type = trim($_POST['blood_type']);
    
    // Fetch current user data to get existing image
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $profile_image = $user['profile_image'] ?? 'default_avatar.png';

    // Handle File Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        // Securely name the file with unique ID
        $new_filename = uniqid('profile_') . '.' . $file_extension;
        $target_file = $upload_dir . $new_filename;
        
        // Simple image validation
        $check = getimagesize($_FILES['profile_image']['tmp_name']);
        if($check !== false) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $profile_image = $new_filename;
            }
        }
    }

    // Update Database and set status to pending_review
    $updateStmt = $pdo->prepare("
        UPDATE users 
        SET full_name = ?, email = ?, contact_number = ?, blood_type = ?, profile_image = ?, profile_status = 'pending_review'
        WHERE id = ?
    ");
    
    $updateStmt->execute([
        $full_name, 
        $email, 
        $contact_number, 
        $blood_type, 
        $profile_image, 
        $user_id
    ]);

    // Update session full name
    $_SESSION['full_name'] = $full_name;

    header('Location: ../user_settings.php?success=1');
    exit;
}
?>
