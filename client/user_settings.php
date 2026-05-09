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

$initials = '';
$words = explode(' ', $user['full_name']);
foreach ($words as $w) {
    if(!empty($w)) $initials .= strtoupper($w[0]);
}
if(strlen($initials)>2) $initials = substr($initials,0,2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Servicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .settings-container { display: flex; gap: 30px; margin-top: 20px; }
        .settings-sidebar { width: 250px; background: white; border-radius: 16px; padding: 20px 0; box-shadow: var(--shadow-sm); height: fit-content; }
        .settings-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: var(--text-muted); text-decoration: none; font-weight: 500; transition: all 0.2s; }
        .settings-nav a:hover, .settings-nav a.active { background: var(--green-light); color: var(--green); border-right: 3px solid var(--green); }
        .settings-nav a i { font-size: 20px; }
        .settings-content { flex: 1; background: white; border-radius: 16px; padding: 40px; box-shadow: var(--shadow-sm); }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; animation: fadeIn 0.3s; }
        .profile-img-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; background: var(--green-light); color: var(--green); display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 600; margin-bottom: 16px; border: 2px solid var(--gray); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>
    <header class="top-nav">
        <a href="user_dashboard.php" class="nav-brand">
            <span class="surveycio-logo">SURVEYCIO</span>
        </a>
        <nav class="nav-links">
            <a href="user_dashboard.php">Home</a>
            <a href="health_monitoring.php">Health Monitoring</a>
            <a href="vendor_registration.php">Vendor Registration</a>
        </nav>
        <div class="user-profile">
            <div class="avatar" style="overflow:hidden;">
                <?php if($user['profile_image'] !== 'default_avatar.png' && !empty($user['profile_image'])): ?>
                    <img src="../server/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <?= $initials ?>
                <?php endif; ?>
            </div>
            <span style="font-weight: 500;"><?= htmlspecialchars($user['full_name']) ?></span>
            <a href="index.html" style="margin-left: 16px; color: var(--text-muted); text-decoration: none;"><i class="ph ph-sign-out"></i> Logout</a>
        </div>
    </header>

    <main style="padding: 40px; max-width: 1200px; margin: 0 auto;">
        <div class="page-header">
            <h1>Account Settings</h1>
            <p>Manage your profile, preferences, and privacy.</p>
        </div>

        <?php if(isset($_GET['success'])): ?>
            <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                <i class="ph ph-check-circle"></i> Profile updated successfully! Changes are pending admin review.
            </div>
        <?php endif; ?>

        <div class="settings-container">
            <!-- Sidebar -->
            <div class="settings-sidebar">
                <nav class="settings-nav">
                    <a href="javascript:void(0)" class="active" onclick="switchTab('profile', this)"><i class="ph ph-user"></i> My Profile</a>
                    <a href="javascript:void(0)" onclick="switchTab('security', this)"><i class="ph ph-lock-key"></i> Security</a>
                    <a href="javascript:void(0)" onclick="switchTab('faq', this)"><i class="ph ph-question"></i> FAQ</a>
                    <a href="javascript:void(0)" onclick="switchTab('privacy', this)"><i class="ph ph-shield-check"></i> Privacy Policy</a>
                </nav>
            </div>

            <!-- Content -->
            <div class="settings-content">
                
                <!-- PROFILE TAB -->
                <div id="profile" class="tab-pane active">
                    <h2 style="margin-bottom: 24px;">Personal Information</h2>
                    <?php if(($user['profile_status'] ?? 'approved') === 'pending_review'): ?>
                        <div style="background: #fef08a; color: #854d0e; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
                            <i class="ph ph-warning-circle"></i> Your recent profile changes are currently pending review by the admin.
                        </div>
                    <?php endif; ?>

                    <form action="../server/actions/update_profile.php" method="POST" enctype="multipart/form-data">
                        
                        <div style="display: flex; flex-direction: column; align-items: flex-start; margin-bottom: 24px;">
                            <div class="profile-img-preview">
                                <?php if(($user['profile_image'] ?? 'default_avatar.png') !== 'default_avatar.png' && !empty($user['profile_image'] ?? '')): ?>
                                    <img src="../server/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar" style="width:100%; height:100%; border-radius: 50%; object-fit:cover;">
                                <?php else: ?>
                                    <?= $initials ?>
                                <?php endif; ?>
                            </div>
                            <label style="cursor:pointer; color: var(--green); font-weight: 500; font-size: 14px;">
                                <i class="ph ph-upload-simple"></i> Change Photo
                                <input type="file" name="profile_image" accept="image/*" style="display:none;">
                            </label>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Mobile Number</label>
                                <input type="text" name="contact_number" value="<?= htmlspecialchars($user['contact_number'] ?? '') ?>" placeholder="e.g. 09123456789">
                            </div>
                            <div class="form-group">
                                <label>Blood Type</label>
                                <select name="blood_type">
                                    <option value="">Select Blood Type</option>
                                    <?php
                                    $types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    foreach($types as $t) {
                                        $sel = ($user['blood_type'] === $t) ? 'selected' : '';
                                        echo "<option value='$t' $sel>$t</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div style="margin-top: 30px; text-align: right;">
                            <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Save Changes</button>
                        </div>
                    </form>
                </div>

                <!-- SECURITY TAB -->
                <div id="security" class="tab-pane">
                    <h2 style="margin-bottom: 24px;">Security & Password</h2>
                    <?php if(isset($_GET['pwd_success'])): ?>
                        <div style="background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
                            <i class="ph ph-check-circle"></i> Password successfully changed!
                        </div>
                    <?php elseif(isset($_GET['pwd_error'])): ?>
                        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 14px;">
                            <i class="ph ph-warning-circle"></i> <?= htmlspecialchars(urldecode($_GET['pwd_error'])) ?>
                        </div>
                    <?php endif; ?>

                    <form action="../server/actions/change_password.php" method="POST">
                        <div style="display: grid; grid-template-columns: 1fr; gap: 20px; max-width: 400px;">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required>
                                <small style="color: var(--text-muted); display: block; margin-top: 4px;">Must be at least 8 chars, 1 uppercase, 1 number, 1 special character.</small>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required>
                            </div>
                        </div>

                        <div style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary"><i class="ph ph-lock-key"></i> Update Password</button>
                        </div>
                    </form>
                </div>

                <!-- FAQ TAB -->
                <div id="faq" class="tab-pane">
                    <h2 style="margin-bottom: 24px;">Frequently Asked Questions</h2>
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin-bottom: 8px;">How long does vendor approval take?</h4>
                        <p style="color: var(--text-muted); font-size: 15px;">Admin usually reviews vendor applications within 2-3 business days. You will receive a notification once approved.</p>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin-bottom: 8px;">Why are my profile changes pending?</h4>
                        <p style="color: var(--text-muted); font-size: 15px;">To ensure the integrity of the barangay records, sensitive information like your name and contact details must be verified by the admin.</p>
                    </div>
                </div>

                <!-- PRIVACY POLICY TAB -->
                <div id="privacy" class="tab-pane">
                    <h2 style="margin-bottom: 24px;">Privacy Policy</h2>
                    <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 16px;">At Servicio, we take your privacy seriously. All health records and personal information submitted through this portal are strictly confidential and are only accessible by authorized barangay health officials.</p>
                    <p style="color: var(--text-muted); font-size: 15px; margin-bottom: 16px;">We do not share your mobile number or email address with third parties without your explicit consent.</p>
                </div>

            </div>
        </div>
    </main>

    <script>
        function switchTab(tabId, element) {
            document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.settings-nav a').forEach(a => a.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            element.classList.add('active');
        }
    </script>
</body>
</html>

