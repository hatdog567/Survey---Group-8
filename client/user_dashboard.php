<?php
require_once '../server/config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$full_name = $user['full_name'];
// Extract initials
$words = explode(' ', $full_name);
$initials = '';
foreach ($words as $w) {
    if(!empty($w)) $initials .= strtoupper($w[0]);
}
if(strlen($initials)>2) $initials = substr($initials,0,2);

// Count Health Records
$stmt = $pdo->prepare("SELECT COUNT(*) FROM health_records WHERE user_id = ?");
$stmt->execute([$user_id]);
$health_count = $stmt->fetchColumn();

// Count Vendor Applications
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vendors WHERE user_id = ?");
$stmt->execute([$user_id]);
$vendor_count = $stmt->fetchColumn();

// Count Unread Notifications
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_notifs = $stmt->fetchColumn();

// Fetch Notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Servicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>

    <!-- TOP NAVIGATION -->
    <header class="top-nav">
        <a href="user_dashboard.php" class="nav-brand">
            <span class="surveycio-logo">SURVEYCIO</span>
        </a>
        <nav class="nav-links">
            <a href="user_dashboard.php" class="active">Home</a>
            <a href="health_monitoring.php">Health Monitoring</a>
            <a href="vendor_registration.php">Vendor Registration</a>
        </nav>
        <div class="user-profile">
            <a href="user_settings.php" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                <div class="avatar" style="overflow:hidden;">
                    <?php if($user['profile_image'] !== 'default_avatar.png' && !empty($user['profile_image'])): ?>
                        <img src="../server/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <span style="font-weight: 500;"><?= htmlspecialchars($full_name) ?></span>
            </a>
            <a href="user_settings.php" style="margin-left: 16px; color: var(--text-muted); text-decoration: none;" title="Account Settings"><i class="ph ph-gear"></i> Settings</a>
            <a href="index.html" style="margin-left: 16px; color: var(--text-muted); text-decoration: none;"><i class="ph ph-sign-out"></i> Logout</a>
        </div>
    </header>

    <main style="padding: 40px; max-width: 1200px; margin: 0 auto;">
        <div class="page-header">
            <h1>Welcome to your Dashboard</h1>
            <p>Manage your health records and vendor applications efficiently.</p>
        </div>

        <div class="dashboard-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="ph ph-file-text"></i></div>
                <div class="stat-details">
                    <h3><?= $health_count ?></h3><p>Health Records</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;"><i class="ph ph-storefront"></i></div>
                <div class="stat-details">
                    <h3><?= $vendor_count ?></h3><p>Active Vendor Applications</p>
                </div>
            </div>
            <div class="stat-card" style="cursor: pointer; border: 2px solid transparent; transition: all 0.2s;" onmouseover="this.style.borderColor='#fef08a'" onmouseout="this.style.borderColor='transparent'" onclick="document.getElementById('notifModal').style.display='flex'"><div class="stat-icon" style="background: #fef08a; color: #a16207;"><i class="ph ph-bell"></i></div>
                <div class="stat-details">
                    <h3><?= $unread_notifs ?></h3><p>New Notifications</p>
                </div>
            </div>
        </div>

        <!-- REQUIRED ACTION: HEALTH -->
        <div class="page-header" style="margin-top: 40px;">
            <h2>Resident Requirements</h2>
            <p style="color: var(--text-muted);">Please ensure your family's health records are up to date.</p>
        </div>
        
        <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid var(--green); box-shadow: var(--shadow-sm); display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 40px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 60px; height: 60px; background: var(--green-light); color: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0;">
                    <i class="ph ph-heartbeat"></i>
                </div>
                <div>
                    <h3 style="margin-bottom: 4px; font-size: 18px;">Update Health Records</h3>
                    <p style="color: var(--text-muted); font-size: 14px;"><b>Required for all residents.</b> Fill up the health monitoring form for you and your family members.</p>
                </div>
            </div>
            <button class="btn btn-primary" onclick="window.location.href='health_monitoring.php'" style="white-space: nowrap;">Start Health Form</button>
        </div>

        <!-- OPTIONAL ACTION: VENDOR -->
        <div class="page-header">
            <h2>Optional Services</h2>
            <p style="color: var(--text-muted);">For local business owners and sellers only.</p>
        </div>

        <div style="background: white; padding: 30px; border-radius: 16px; border-left: 5px solid #0284c7; box-shadow: var(--shadow-sm);">
            
            <!-- WARNING BANNER -->
            <div style="background: #fffbeb; border: 1px solid #fef08a; color: #92400e; padding: 12px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 10px; font-weight: 500;">
                <i class="ph-fill ph-warning-circle" style="font-size: 20px; color: #d97706; margin-top: 2px;"></i>
                <div>
                    <strong style="display: block; margin-bottom: 4px; color: #b45309;">Note for Residents:</strong>
                    Only fill this out if you are applying to sell goods or operate a business in the barangay. If you are just a regular resident, you do not need to fill this out.
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="width: 60px; height: 60px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; flex-shrink: 0;">
                        <i class="ph ph-storefront"></i>
                    </div>
                    <div>
                        <h3 style="margin-bottom: 4px; font-size: 18px;">Register as Vendor</h3>
                        <p style="color: var(--text-muted); font-size: 14px;">Submit an application to become a registered barangay vendor.</p>
                    </div>
                </div>
                <button class="btn btn-primary" onclick="window.location.href='vendor_registration.php'" style="background: #0284c7; white-space: nowrap;">Apply as Vendor</button>
            </div>
        </div>
    </main>

    <!-- NOTIFICATION MODAL -->
    <div id="notifModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-size: 20px;">Your Notifications</h2>
                <button onclick="document.getElementById('notifModal').style.display='none'" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted);">&times;</button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if (empty($notifications)): ?>
                    <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                        <i class="ph ph-bell-slash" style="font-size: 32px; margin-bottom: 8px;"></i>
                        <p>No notifications yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div style="padding: 16px; border-radius: 12px; background: <?= $notif['is_read'] ? 'var(--gray-light)' : '#f0f9ff' ?>; border: 1px solid <?= $notif['is_read'] ? 'var(--gray)' : '#bae6fd' ?>;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <strong style="color: var(--text-main); font-size: 15px;"><?= htmlspecialchars($notif['title']) ?></strong>
                                <span style="font-size: 12px; color: var(--text-muted);"><?= date('M d, g:i A', strtotime($notif['created_at'])) ?></span>
                            </div>
                            <p style="color: var(--text-muted); font-size: 14px; margin: 0;"><?= htmlspecialchars($notif['message']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Script to mark notifs as read when opened -->
    <script>
        document.querySelector('.stat-card i.ph-bell').parentElement.parentElement.addEventListener('click', function() {
            if (<?= $unread_notifs ?> > 0) {
                fetch('../server/actions/mark_notifs_read.php', { method: 'POST' })
                    .then(() => {
                        // Dynamically decrease unread badge to 0
                        document.querySelector('.stat-card i.ph-bell').parentElement.nextElementSibling.querySelector('h3').innerText = '0';
                    });
            }
        });
    </script>
</body>
</html>




