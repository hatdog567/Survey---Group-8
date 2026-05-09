<?php
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // 1. Blood Type Distribution
    $bloodTypeQuery = $pdo->query("SELECT blood_type, COUNT(*) as count FROM donors WHERE blood_type IS NOT NULL AND blood_type != 'Unknown' GROUP BY blood_type");
    $bloodTypes = [];
    $bloodCounts = [];
    while ($row = $bloodTypeQuery->fetch(PDO::FETCH_ASSOC)) {
        $bloodTypes[] = $row['blood_type'];
        $bloodCounts[] = (int)$row['count'];
    }

    // 2. Donors per Month
    $monthlyDonorsQuery = $pdo->query("
        SELECT MONTHNAME(created_at) as month, COUNT(*) as count 
        FROM donors 
        WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
        GROUP BY MONTH(created_at), MONTHNAME(created_at)
        ORDER BY MONTH(created_at)
    ");
    $donorMonths = [];
    $donorMonthlyCounts = [];
    while ($row = $monthlyDonorsQuery->fetch(PDO::FETCH_ASSOC)) {
        $donorMonths[] = substr($row['month'], 0, 3);
        $donorMonthlyCounts[] = (int)$row['count'];
    }

    // 3. Vendor Status
    $vendorStatusQuery = $pdo->query("SELECT status, COUNT(*) as count FROM vendors GROUP BY status");
    $vendorStatuses = [];
    $vendorStatusCounts = [];
    while ($row = $vendorStatusQuery->fetch(PDO::FETCH_ASSOC)) {
        $vendorStatuses[] = ucfirst($row['status']);
        $vendorStatusCounts[] = (int)$row['count'];
    }

    // 4. Quick Stats
    $totalFamilies = $pdo->query("SELECT COUNT(*) FROM health_records")->fetchColumn();
    $totalMembers = $pdo->query("SELECT COUNT(*) FROM family_members")->fetchColumn();
    $totalDonors = $pdo->query("SELECT COUNT(*) FROM donors")->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'totalFamilies' => $totalFamilies,
            'totalMembers' => $totalMembers,
            'totalDonors' => $totalDonors
        ],
        'charts' => [
            'blood' => ['labels' => $bloodTypes, 'data' => $bloodCounts],
            'donors' => ['labels' => $donorMonths, 'data' => $donorMonthlyCounts],
            'vendors' => ['labels' => $vendorStatuses, 'data' => $vendorStatusCounts]
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
