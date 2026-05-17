<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../client/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $blood_type = trim($_POST['blood_type'] ?? '');

    // Server-side validation
    if (empty($full_name) || empty($email)) {
        header('Location: ../../client/user_settings.php?error=' . urlencode('Name and Email cannot be empty.'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../../client/user_settings.php?error=' . urlencode('Invalid email format.'));
        exit;
    }

    // Fetch current user data to get existing image
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $profile_image = $user['profile_image'] ?? 'default_avatar.png';

    // Handle File Upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $file_name = $_FILES['profile_image']['name'];
        $file_tmp_path = $_FILES['profile_image']['tmp_name'];

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];

        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Check extension
        if (in_array($file_extension, $allowed_extensions)) {
            // Check MIME type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->file($file_tmp_path);

            if (in_array($mime_type, $allowed_mime_types)) {
                // Securely name the file with unique ID
                $new_filename = uniqid('profile_') . '_' . bin2hex(random_bytes(4)) . '.' . $file_extension;
                $target_file = $upload_dir . $new_filename;

                if (move_uploaded_file($file_tmp_path, $target_file)) {
                    $profile_image = $new_filename;
                }
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

    header('Location: ../../client/user_settings.php?success=1');
    exit;
}
?>