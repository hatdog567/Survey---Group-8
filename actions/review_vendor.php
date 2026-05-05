<?php
// review_vendor.php
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vendor_id = $_POST['vendor_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'approve' or 'reject'

    if (!$vendor_id || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid request.']);
        exit;
    }

    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE vendors SET status = 'approved' WHERE id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE vendors SET status = 'rejected' WHERE id = ?");
        }
        $stmt->execute([$vendor_id]);

        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>