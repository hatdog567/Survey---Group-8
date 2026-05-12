<?php
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../client/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'approve_vendor') {
            $vendor_id = $_POST['vendor_id'];
            $user_id = $_POST['user_id'];

            $pdo->prepare("UPDATE vendors SET status='approved' WHERE id=?")->execute([$vendor_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Vendor Approved', 'Congratulations! Your vendor application has been approved.')")->execute([$user_id]);
            header('Location: ../../client/admin_dashboard.php?tab=pendings&success=1');
            exit;
        } elseif ($action === 'reject_vendor') {
            $vendor_id = $_POST['vendor_id'];
            $user_id = $_POST['user_id'];

            $pdo->prepare("UPDATE vendors SET status='rejected' WHERE id=?")->execute([$vendor_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Vendor Rejected', 'Unfortunately, your vendor application was rejected.')")->execute([$user_id]);
            header('Location: ../../client/admin_dashboard.php?tab=pendings&success=1');
            exit;
        } elseif ($action === 'approve_profile') {
            $user_id = $_POST['user_id'];

            $pdo->prepare("UPDATE users SET profile_status='approved' WHERE id=?")->execute([$user_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Profile Approved', 'Your recent profile updates have been verified and approved.')")->execute([$user_id]);
            header('Location: ../../client/admin_dashboard.php?tab=pendings&success=1');
            exit;
        } elseif ($action === 'reject_profile') {
            $user_id = $_POST['user_id'];

            // Revert the profile photo and clear status
            $pdo->prepare("UPDATE users SET profile_status='approved', profile_image='default_avatar.png' WHERE id=?")->execute([$user_id]);
            $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Profile Update Rejected', 'Your recent profile photo update was not accepted. Please ensure your avatar meets the guidelines.')")->execute([$user_id]);
            header('Location: ../../client/admin_dashboard.php?tab=pendings&success=1');
            exit;
        } elseif ($action === 'delete_vendor') {
            $vendor_id = $_POST['vendor_id'];
            $pdo->prepare("DELETE FROM vendors WHERE id=?")->execute([$vendor_id]);
            header('Location: ../../client/admin_dashboard.php?tab=vendors&success=1');
            exit;
        } elseif ($action === 'delete_donor') {
            $donor_id = $_POST['donor_id'];
            $pdo->prepare("DELETE FROM donors WHERE id=?")->execute([$donor_id]);
            header('Location: ../../client/admin_dashboard.php?tab=donors&success=1');
            exit;
        } elseif ($action === 'update_donor_status') {
            $donor_id = $_POST['donor_id'];
            $status = $_POST['status'];
            $pdo->prepare("UPDATE donors SET status=? WHERE id=?")->execute([$status, $donor_id]);
            header('Location: ../../client/admin_dashboard.php?tab=donors&success=1');
            exit;
        } elseif ($action === 'delete_health_record') {
            $record_id = $_POST['record_id'];
            $pdo->prepare("DELETE FROM health_records WHERE id=?")->execute([$record_id]);
            header('Location: ../../client/admin_dashboard.php?tab=health&success=1');
            exit;
        }

    } catch (PDOException $e) {
        die("Error processing action: " . $e->getMessage());
    }
}
