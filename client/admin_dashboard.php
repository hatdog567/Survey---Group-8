<?php
require_once '../server/config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.html');
    exit;
}

// Fetch Stats
$totalFamilies = $pdo->query("SELECT COUNT(*) FROM health_records")->fetchColumn();
$totalIndividuals = $pdo->query("SELECT SUM(household_number) FROM health_records")->fetchColumn() ?: 0;
$totalDonors = $pdo->query("SELECT COUNT(*) FROM donors WHERE status='eligible'")->fetchColumn();
$activeVendors = $pdo->query("SELECT COUNT(*) FROM vendors WHERE status='approved'")->fetchColumn();

// Fetch Pendings
$pendingVendors = $pdo->query("
    SELECT vendors.*, users.full_name as user_name 
    FROM vendors 
    JOIN users ON vendors.user_id = users.id 
    WHERE vendors.status = 'pending'
    ORDER BY vendors.created_at DESC
")->fetchAll();

$pendingProfiles = $pdo->query("
    SELECT * FROM users 
    WHERE profile_status = 'pending_review'
")->fetchAll();
$totalPendings = count($pendingVendors) + count($pendingProfiles);

// Fetch Health Records for Cards
$healthRecords = $pdo->query("SELECT * FROM health_records ORDER BY created_at DESC")->fetchAll();

// Fetch All Family Members
$allMembers = $pdo->query("SELECT * FROM family_members")->fetchAll(PDO::FETCH_ASSOC);
$membersByRecord = [];
foreach($allMembers as $m) {
    $membersByRecord[$m['health_record_id']][] = $m;
}

// Fetch Donors for Cards
$donors = $pdo->query("
    SELECT donors.*, hr.zone as h_zone, hr.address as h_address
    FROM donors 
    LEFT JOIN (
        SELECT user_id, zone, address 
        FROM health_records 
        WHERE id IN (SELECT MAX(id) FROM health_records GROUP BY user_id)
    ) hr ON donors.user_id = hr.user_id 
    ORDER BY donors.created_at DESC
")->fetchAll();

// Fetch Approved Vendors for Cards
$approvedVendorsList = $pdo->query("SELECT * FROM vendors WHERE status = 'approved' ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Servicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .tab-content { display: none; }
        .tab-content.active { display: block; animation: fadeIn 0.3s; }
        .sidebar-nav a { cursor: pointer; }
        .sidebar-nav a.active { background: var(--green-light); color: var(--green); border-right: 3px solid var(--green); }
        
        .data-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
            border-top: 4px solid var(--green);
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        .data-card:hover { box-shadow: var(--shadow-md); transform: translateY(-3px); }
        .data-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; font-size: 12px; }
        .data-card-title { font-size: 18px; font-weight: 600; color: var(--text-main); margin-bottom: 8px; }
        .data-card-subtitle { font-size: 14px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; margin-bottom: 16px; }
        .data-card-body { font-size: 14px; color: var(--text-main); line-height: 1.5; flex-grow: 1; }
        
        .grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }

        .meta-badge { background: var(--gray-light); color: var(--text-muted); padding: 4px 8px; border-radius: 6px; display: flex; align-items: center; gap: 4px; font-weight: 500; }
        .zone-badge { background: #e0f2fe; color: #0284c7; padding: 4px 8px; border-radius: 6px; display: flex; align-items: center; gap: 4px; font-weight: 600; }
        
        /* Modal Styles */
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background: white; border-radius: 16px; padding: 32px; max-width: 500px; width: 90%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative;
            animation: slideUp 0.3s ease-out forwards;
        }
        .modal-close {
            position: absolute; top: 20px; right: 20px; background: none; border: none;
            font-size: 28px; color: var(--text-muted); cursor: pointer; transition: 0.2s; line-height: 1;
        }
        .modal-close:hover { color: var(--text-main); }
        .modal-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--gray); }
        .modal-header h2 { font-size: 20px; color: var(--text-main); margin-bottom: 4px; }
        .modal-header p { font-size: 14px; color: var(--text-muted); }
        .data-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed var(--gray); }
        .data-row:last-child { border-bottom: none; }
        .data-label { color: var(--text-muted); font-size: 14px; font-weight: 500; }
        .data-val { color: var(--text-main); font-size: 14px; font-weight: 600; text-align: right; max-width: 60%; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="admin_dashboard.php" class="nav-brand">
                    <span class="surveycio-logo">SURVEYCIO</span> Admin
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <a onclick="switchTab('overview', this)" class="active">
                    <i class="ph ph-squares-four"></i> Dashboard Overview
                </a>
                <a onclick="switchTab('pendings', this)">
                    <i class="ph ph-bell"></i> Pending Approvals 
                    <?php if($totalPendings > 0): ?><span class="badge yellow" style="margin-left:auto;"><?= $totalPendings ?></span><?php endif; ?>
                </a>
                <div style="margin: 20px 0 10px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Records</div>
                <a onclick="switchTab('health', this)">
                    <i class="ph ph-file-text"></i> Health Records
                </a>
                <a onclick="switchTab('donors', this)">
                    <i class="ph ph-drop"></i> Blood Donors
                </a>
                <a onclick="switchTab('vendors', this)">
                    <i class="ph ph-storefront"></i> Active Vendors
                </a>
                <div style="margin: 20px 0 10px 24px; font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">System</div>
                <a href="reports.html">
                    <i class="ph ph-chart-bar"></i> Analytics & Reports
                </a></nav>
            
            <div class="sidebar-footer">
                <div class="user-profile" style="margin-bottom: 16px;">
                    <div class="avatar" style="background: var(--green); color: white;">A</div>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 600; font-size: 14px;">System Admin</span>
                        <span style="font-size: 12px; color: var(--text-muted);">admin@servicio.gov</span>
                    </div>
                </div>
                <a href="../server/actions/logout.php" class="btn btn-outline" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="ph ph-sign-out"></i> Logout
                </a>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="admin-main">

            <?php if(isset($_GET['success'])): ?>
                <div id="successToast" style="background: #dcfce7; color: #166534; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; border-left: 4px solid #166534; animation: slideDown 0.3s ease-out;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
                        <i class="ph ph-check-circle" style="font-size: 20px;"></i> Action completed successfully!
                    </div>
                    <button onclick="document.getElementById('successToast').style.display='none'" style="background:none; border:none; color:#166534; cursor:pointer; font-size:20px;">&times;</button>
                </div>
                <script>
                    setTimeout(() => {
                        const toast = document.getElementById('successToast');
                        if(toast) {
                            toast.style.transition = 'opacity 0.3s ease';
                            toast.style.opacity = '0';
                            setTimeout(() => toast.style.display = 'none', 300);
                        }
                    }, 4000);
                </script>
            <?php endif; ?>

            <!-- OVERVIEW TAB -->
            <div id="overview" class="tab-content active">
                <div class="page-header">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back, Admin. Here is the live data from the database.</p>
                </div>

                <div class="dashboard-grid" style="grid-template-columns: repeat(4, 1fr);">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="ph ph-users-three"></i></div>
                        <div class="stat-details">
                            <h3><?= $totalFamilies ?></h3>
                            <p>Total Health Records</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="ph ph-drop"></i></div>
                        <div class="stat-details">
                            <h3><?= $totalDonors ?></h3>
                            <p>Eligible Donors</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;"><i class="ph ph-storefront"></i></div>
                        <div class="stat-details">
                            <h3><?= $activeVendors ?></h3>
                            <p>Approved Vendors</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #fef08a; color: #a16207;"><i class="ph ph-bell"></i></div>
                        <div class="stat-details">
                            <h3><?= $totalPendings ?></h3>
                            <p>Pending Actions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PENDINGS TAB -->
            <div id="pendings" class="tab-content">
                <div class="page-header">
                    <h1>Pending Approvals</h1>
                    <p>Review and manage user profile updates and vendor applications.</p>
                </div>

                <div class="table-container" style="margin-bottom: 30px;">
                    <div class="table-header">
                        <h2 style="font-size: 18px; font-weight: 600;">Pending Vendor Applications (<?= count($pendingVendors) ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Business Name</th>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Contact</th>
                                <th>Attachments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($pendingVendors) === 0): ?>
                                <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No pending vendor applications.</td></tr>
                            <?php else: ?>
                                <?php foreach($pendingVendors as $v): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($v['business_name']) ?></td>
                                    <td><?= htmlspecialchars($v['owner_name']) ?></td>
                                    <td><span style="text-transform: capitalize;"><?= htmlspecialchars($v['business_type']) ?></span></td>
                                    <td><?= htmlspecialchars($v['contact_number']) ?></td>
                                    <td>
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                            <?php if(!empty($v['id_front'])): ?>
                                                <a href="../server/uploads/<?= htmlspecialchars($v['id_front']) ?>" target="_blank" style="font-size:12px; background:#e2e8f0; padding:2px 6px; border-radius:4px; text-decoration:none; color:#334155;">ID (Front)</a>
                                            <?php endif; ?>
                                            <?php if(!empty($v['id_back'])): ?>
                                                <a href="../server/uploads/<?= htmlspecialchars($v['id_back']) ?>" target="_blank" style="font-size:12px; background:#e2e8f0; padding:2px 6px; border-radius:4px; text-decoration:none; color:#334155;">ID (Back)</a>
                                            <?php endif; ?>
                                            <?php if(!empty($v['brgy_clearance'])): ?>
                                                <a href="../server/uploads/<?= htmlspecialchars($v['brgy_clearance']) ?>" target="_blank" style="font-size:12px; background:#e2e8f0; padding:2px 6px; border-radius:4px; text-decoration:none; color:#334155;">Clearance</a>
                                            <?php endif; ?>
                                            <?php if(empty($v['id_front']) && empty($v['id_back']) && empty($v['brgy_clearance'])): ?>
                                                <span style="font-size:12px; color:#94a3b8;">None</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <form action="../server/actions/admin_action.php" method="POST" style="margin:0;">
                                                <input type="hidden" name="action" value="approve_vendor">
                                                <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $v['user_id'] ?>">
                                                <button class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Approve</button>
                                            </form>
                                            <form action="../server/actions/admin_action.php" method="POST" style="margin:0;">
                                                <input type="hidden" name="action" value="reject_vendor">
                                                <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $v['user_id'] ?>">
                                                <button class="btn btn-outline" style="padding: 6px 12px; font-size: 13px; color: #dc2626; border-color: #dc2626;">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 style="font-size: 18px; font-weight: 600;">Pending Profile Updates (<?= count($pendingProfiles) ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Resident Name</th>
                                <th>Email</th>
                                <th>Contact</th>
                                <th>Blood Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($pendingProfiles) === 0): ?>
                                <tr><td colspan="5" style="text-align:center; color: var(--text-muted);">No pending profile updates.</td></tr>
                            <?php else: ?>
                                <?php foreach($pendingProfiles as $p): ?>
                                <tr>
                                    <td style="font-weight: 500;">
                                        <div style="display:flex; align-items:center; gap: 10px;">
                                            <?php if($p['profile_image'] !== 'default_avatar.png'): ?>
                                                <img src="../server/uploads/<?= htmlspecialchars($p['profile_image']) ?>" style="width:30px; height:30px; border-radius:50%; object-fit:cover;">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($p['full_name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($p['email']) ?></td>
                                    <td><?= htmlspecialchars($p['contact_number'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($p['blood_type'] ?? 'N/A') ?></td>
                                    <td>
                                        <div style="display: flex; gap: 8px;">
                                            <form action="../server/actions/admin_action.php" method="POST" style="margin:0;">
                                                <input type="hidden" name="action" value="approve_profile">
                                                <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                                                <button class="btn btn-primary" style="padding: 6px 12px; font-size: 13px;">Approve</button>
                                            </form>
                                            <form action="../server/actions/admin_action.php" method="POST" style="margin:0;">
                                                <input type="hidden" name="action" value="reject_profile">
                                                <input type="hidden" name="user_id" value="<?= $p['id'] ?>">
                                                <button class="btn btn-outline" style="padding: 6px 12px; font-size: 13px; color: #dc2626; border-color: #dc2626;">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- HEALTH RECORDS TAB -->
            <div id="health" class="tab-content">
                <div class="page-header">
                    <h1>Health Records</h1>
                    <p>Directory of family health profiles registered in the barangay.</p>
                </div>
                
                <?php if(count($healthRecords) === 0): ?>
                    <p style="text-align: center; color: var(--text-muted); margin-top: 40px;">No health records found.</p>
                <?php else: ?>
                    <div class="grid-cards">
                        <?php foreach($healthRecords as $r): ?>
                            <div class="data-card" onclick="openModal('healthModal_<?= $r['id'] ?>')">
                                <div class="data-card-header">
                                    <span class="meta-badge"><i class="ph ph-calendar"></i> <?= date('M d, Y - h:i A', strtotime($r['created_at'])) ?></span>
                                    <span class="zone-badge"><i class="ph ph-map-pin"></i> Zone <?= htmlspecialchars($r['zone']) ?></span>
                                </div>
                                <h3 class="data-card-title"><?= htmlspecialchars($r['head_of_family']) ?> Family</h3>
                                <div class="data-card-subtitle"><i class="ph ph-users"></i> <?= htmlspecialchars($r['household_number']) ?> Family Members</div>
                                <div class="data-card-body" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <strong>Address:</strong> <?= htmlspecialchars($r['address']) ?>
                                </div>
                            </div>

                            <!-- Modal for Health Record -->
                            <div id="healthModal_<?= $r['id'] ?>" class="modal-overlay" onclick="closeModal(event, this)">
                                <div class="modal-content" onclick="event.stopPropagation()">
                                    <button class="modal-close" onclick="closeModal(null, document.getElementById('healthModal_<?= $r['id'] ?>'))">&times;</button>
                                    <div class="modal-header">
                                        <h2><?= htmlspecialchars($r['head_of_family']) ?> Family Data</h2>
                                        <p>Submitted on <?= date('F j, Y \a\t g:i A', strtotime($r['created_at'])) ?></p>
                                    </div>
                                    <div class="data-row"><span class="data-label">Head of Family</span><span class="data-val"><?= htmlspecialchars($r['head_of_family']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Household Size</span><span class="data-val"><?= htmlspecialchars($r['household_number']) ?> Members</span></div>
                                    <div class="data-row"><span class="data-label">Zone / Purok</span><span class="data-val">Zone <?= htmlspecialchars($r['zone']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Complete Address</span><span class="data-val"><?= htmlspecialchars($r['address']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Contact Number</span><span class="data-val"><?= htmlspecialchars($r['contact_number'] ?? 'Not provided') ?></span></div>

                                    <!-- Family Members Table -->
                                    <div style="margin-top: 24px; border-top: 1px solid var(--gray); padding-top: 16px;">
                                        <h3 style="font-size: 16px; margin-bottom: 12px; color: var(--text-main);">Individual Members Data</h3>
                                        <?php if(!empty($membersByRecord[$r['id']])): ?>
                                            <div style="max-height: 250px; overflow-y: auto; border: 1px solid var(--gray-light); border-radius: 6px;">
                                            <table style="width:100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                                <thead style="position: sticky; top: 0; z-index: 1;">
                                                    <tr style="background: var(--gray-light); border-bottom: 1px solid var(--gray);">
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted); border-top-left-radius: 6px; border-bottom-left-radius: 6px;">Name</th>
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted);">Rel</th>
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted);">Age</th>
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted);">Blood</th>
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted);">Condition</th>
                                                        <th style="padding: 8px; font-weight: 600; color: var(--text-muted); border-top-right-radius: 6px; border-bottom-right-radius: 6px;">Donor Consent</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Head of Family Row (since we added them to health_records, we just show them if we have data) -->
                                                    <tr style="border-bottom: 1px dashed var(--gray); background: #f8fafc;">
                                                        <td style="padding: 8px; color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($r['head_of_family']) ?> <span style="font-size:10px; color:#16a34a; border:1px solid #16a34a; padding:1px 4px; border-radius:4px; margin-left:4px;">Head</span></td>
                                                        <td style="padding: 8px; color: var(--text-muted);">Head</td>
                                                        <td style="padding: 8px; color: var(--text-muted);"><?= htmlspecialchars($r['age'] ?? '-') ?> yrs</td>
                                                        <td style="padding: 8px; color: #dc2626; font-weight: 600;"><?= htmlspecialchars($r['blood_type'] ?? '-') ?></td>
                                                        <td style="padding: 8px; color: var(--text-muted);"><?= $r['existing_condition'] === 'Yes' ? htmlspecialchars($r['condition_details']) : 'None' ?></td>
                                                        <td style="padding: 8px; color: <?= $r['donor_consent'] === 'Yes' ? '#16a34a' : 'var(--text-muted)' ?>; font-weight: 500;"><?= htmlspecialchars($r['donor_consent']) ?></td>
                                                    </tr>
                                                    
                                                    <?php foreach($membersByRecord[$r['id']] as $m): ?>
                                                    <tr style="border-bottom: 1px dashed var(--gray);">
                                                        <td style="padding: 8px; color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($m['name']) ?></td>
                                                        <td style="padding: 8px; color: var(--text-muted);"><?= htmlspecialchars($m['relationship'] ?? 'Member') ?></td>
                                                        <td style="padding: 8px; color: var(--text-muted);"><?= htmlspecialchars($m['age']) ?> yrs</td>
                                                        <td style="padding: 8px; color: #dc2626; font-weight: 600;"><?= htmlspecialchars($m['blood_type']) ?></td>
                                                        <td style="padding: 8px; color: var(--text-muted);"><?= $m['existing_condition'] === 'Yes' ? htmlspecialchars($m['condition_details']) : 'None' ?></td>
                                                        <td style="padding: 8px; color: <?= $m['donor_consent'] === 'Yes' ? '#16a34a' : 'var(--text-muted)' ?>; font-weight: 500;"><?= htmlspecialchars($m['donor_consent']) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            </div>
                                        <?php else: ?>
                                            <p style="font-size: 13px; color: var(--text-muted); font-style: italic;">Detailed individual data not recorded for this family.</p>
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--gray); text-align: center;">
                                        <form action="../server/actions/admin_action.php" method="POST" onsubmit="return confirm('Delete this health record? This action cannot be undone.');" style="margin: 0;">
                                            <input type="hidden" name="action" value="delete_health_record">
                                            <input type="hidden" name="record_id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; color: #dc2626; border-color: #dc2626; width: 100%;"><i class="ph ph-trash"></i> Delete Record</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- DONORS TAB -->
            <div id="donors" class="tab-content">
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <h1>Blood Donors</h1>
                        <p>List of registered blood donors for emergency response.</p>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <input type="text" id="searchDonor" placeholder="Search name..." onkeyup="filterDonors()" style="padding: 8px 12px; border: 1px solid var(--gray); border-radius: 6px; width: 200px;">
                        <select id="filterBlood" onchange="filterDonors()" style="padding: 8px 12px; border: 1px solid var(--gray); border-radius: 6px;">
                            <option value="">All Blood Types</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                        <select id="filterStatus" onchange="filterDonors()" style="padding: 8px 12px; border: 1px solid var(--gray); border-radius: 6px;">
                            <option value="">All Statuses</option>
                            <option value="eligible">Eligible</option>
                            <option value="screening">For Screening</option>
                            <option value="not_eligible">Not Eligible</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <table id="donorTable" style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--gray);">
                                <th style="padding: 12px;">Name</th>
                                <th style="padding: 12px;">Blood Type</th>
                                <th style="padding: 12px;">Status</th>
                                <th style="padding: 12px;">Contact Info</th>
                                <th style="padding: 12px;">Zone</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($donors) === 0): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No donors found.</td></tr>
                            <?php else: ?>
                                <?php foreach($donors as $d): ?>
                                <tr class="donor-row" data-name="<?= strtolower(htmlspecialchars($d['name'])) ?>" data-blood="<?= htmlspecialchars($d['blood_type']) ?>" data-status="<?= strtolower(htmlspecialchars($d['status'])) ?>" style="border-bottom: 1px solid var(--gray-light); cursor: pointer;" onclick="openModal('donorModal_<?= $d['id'] ?>')">
                                    <td style="padding: 12px; font-weight: 500; color: var(--text-main);"><?= htmlspecialchars($d['name']) ?></td>
                                    <td style="padding: 12px;">
                                        <span style="background: #fee2e2; color: #dc2626; font-weight: 700; padding: 4px 8px; border-radius: 4px;"><?= htmlspecialchars($d['blood_type']) ?></span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php
                                            $sColor = '#64748b'; $sBg = '#f1f5f9';
                                            if($d['status'] === 'eligible') { $sColor = '#166534'; $sBg = '#dcfce7'; }
                                            elseif($d['status'] === 'screening') { $sColor = '#a16207'; $sBg = '#fef08a'; }
                                            elseif($d['status'] === 'not eligible') { $sColor = '#991b1b'; $sBg = '#fee2e2'; }
                                        ?>
                                        <span style="background: <?= $sBg ?>; color: <?= $sColor ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: capitalize;"><?= htmlspecialchars($d['status']) ?></span>
                                    </td>
                                    <td style="padding: 12px; color: var(--text-muted);"><?= htmlspecialchars($d['contact_number']) ?></td>
                                    <td style="padding: 12px; color: var(--text-muted);"><?= htmlspecialchars($d['h_zone'] ?? 'Unknown') ?></td>
                                </tr>

                                <!-- Modal for Donor -->
                                <div id="donorModal_<?= $d['id'] ?>" class="modal-overlay" onclick="closeModal(event, this)">
                                    <div class="modal-content" onclick="event.stopPropagation()">
                                        <button class="modal-close" onclick="closeModal(null, document.getElementById('donorModal_<?= $d['id'] ?>'))">&times;</button>
                                        <div class="modal-header">
                                            <h2>Donor Information</h2>
                                            <p>Registered on <?= date('F j, Y \a\t g:i A', strtotime($d['created_at'])) ?></p>
                                        </div>
                                        <div class="data-row"><span class="data-label">Donor Name</span><span class="data-val"><?= htmlspecialchars($d['name']) ?></span></div>
                                        <div class="data-row"><span class="data-label">Age</span><span class="data-val"><?= htmlspecialchars($d['age']) ?> yrs old</span></div>
                                        <div class="data-row"><span class="data-label">Blood Type</span><span class="data-val" style="color:#dc2626; font-size:16px;"><?= htmlspecialchars($d['blood_type']) ?></span></div>
                                        <div class="data-row"><span class="data-label">Contact Number</span><span class="data-val"><?= htmlspecialchars($d['contact_number']) ?></span></div>
                                        <div class="data-row"><span class="data-label">Eligibility Status</span><span class="data-val" style="text-transform:capitalize; color: <?= $sColor ?>;"><?= htmlspecialchars($d['status']) ?></span></div>
                                        <?php if(!empty($d['h_address'])): ?>
                                            <div class="data-row"><span class="data-label">Address</span><span class="data-val"><?= htmlspecialchars($d['h_address']) ?></span></div>
                                        <?php endif; ?>
                                        <div class="data-row"><span class="data-label">Last Donation</span><span class="data-val"><?= !empty($d['last_donation']) ? date('M d, Y', strtotime($d['last_donation'])) : 'None recorded' ?></span></div>
                                        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--gray); text-align: center;">
                                            <form action="../server/actions/admin_action.php" method="POST" onsubmit="return confirm('Delete this donor record?');" style="margin: 0;">
                                                <input type="hidden" name="action" value="delete_donor">
                                                <input type="hidden" name="donor_id" value="<?= $d['id'] ?>">
                                                <button type="submit" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; color: #dc2626; border-color: #dc2626; width: 100%;"><i class="ph ph-trash"></i> Delete Record</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- VENDORS TAB -->
            <div id="vendors" class="tab-content">
                <div class="page-header">
                    <h1>Active Vendor Applications</h1>
                    <p>Directory of approved businesses and vendors operating locally.</p>
                </div>

                <?php if(count($approvedVendorsList) === 0): ?>
                    <p style="text-align: center; color: var(--text-muted); margin-top: 40px;">No approved vendors found.</p>
                <?php else: ?>
                    <div class="grid-cards">
                        <?php foreach($approvedVendorsList as $v): ?>
                            <div class="data-card" style="border-top-color: #0284c7;" onclick="openModal('vendorModal_<?= $v['id'] ?>')">
                                <div class="data-card-header">
                                    <span class="meta-badge"><i class="ph ph-calendar"></i> <?= date('M d, Y - h:i A', strtotime($v['created_at'])) ?></span>
                                    <span class="badge" style="background: #dcfce7; color: #166534;"><i class="ph ph-check-circle"></i> Approved</span>
                                </div>
                                <h3 class="data-card-title"><?= htmlspecialchars($v['business_name']) ?></h3>
                                <div class="data-card-subtitle"><i class="ph ph-user"></i> <?= htmlspecialchars($v['owner_name']) ?></div>
                            </div>

                            <!-- Modal for Vendor -->
                            <div id="vendorModal_<?= $v['id'] ?>" class="modal-overlay" onclick="closeModal(event, this)">
                                <div class="modal-content" onclick="event.stopPropagation()">
                                    <button class="modal-close" onclick="closeModal(null, document.getElementById('vendorModal_<?= $v['id'] ?>'))">&times;</button>
                                    <div class="modal-header">
                                        <h2>Vendor Registration Details</h2>
                                        <p>Application Date: <?= date('F j, Y \a\t g:i A', strtotime($v['created_at'])) ?></p>
                                    </div>
                                    <div class="data-row"><span class="data-label">Business Name</span><span class="data-val"><?= htmlspecialchars($v['business_name']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Owner Name</span><span class="data-val"><?= htmlspecialchars($v['owner_name']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Business Type</span><span class="data-val" style="text-transform:uppercase;"><?= htmlspecialchars($v['business_type']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Contact Number</span><span class="data-val"><?= htmlspecialchars($v['contact_number']) ?></span></div>
                                    <div class="data-row"><span class="data-label">Status</span><span class="data-val" style="color: #166534;"><i class="ph ph-check-circle"></i> Approved</span></div>
                                    <div class="data-row" style="flex-direction:column; align-items:flex-start; gap:8px;">
                                        <span class="data-label">Complete Address</span>
                                        <span class="data-val" style="text-align:left; max-width:100%;"><?= htmlspecialchars($v['address']) ?></span>
                                    </div>
                                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--gray); text-align: center;">
                                        <form action="../server/actions/admin_action.php" method="POST" onsubmit="return confirm('Delete this vendor application?');" style="margin: 0;">
                                            <input type="hidden" name="action" value="delete_vendor">
                                            <input type="hidden" name="vendor_id" value="<?= $v['id'] ?>">
                                            <button type="submit" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; color: #dc2626; border-color: #dc2626; width: 100%;"><i class="ph ph-trash"></i> Delete Record</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <script>
        function switchTab(tabId, element) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.sidebar-nav a').forEach(a => a.classList.remove('active'));
            
            document.getElementById(tabId).classList.add('active');
            if (element) {
                element.classList.add('active');
            }
        }

        // Modal Functions
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        function closeModal(event, modalElement) {
            if (!event || event.target === modalElement) {
                modalElement.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restore scrolling
            }
        }
        // Filter Donors function
        function filterDonors() {
            const search = document.getElementById('searchDonor').value.toLowerCase();
            const blood = document.getElementById('filterBlood').value;
            const status = document.getElementById('filterStatus').value;
            
            const rows = document.querySelectorAll('.donor-row');
            rows.forEach(row => {
                const rName = row.getAttribute('data-name');
                const rBlood = row.getAttribute('data-blood');
                const rStatus = row.getAttribute('data-status');
                
                const matchSearch = rName.includes(search);
                const matchBlood = blood === '' || rBlood === blood;
                const matchStatus = status === '' || rStatus === status;
                
                if(matchSearch && matchBlood && matchStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        document.addEventListener("DOMContentLoaded", () => {
            const params = new URLSearchParams(window.location.search);
            const tab = params.get('tab');
            if (tab) {
                const link = document.querySelector(`.sidebar-nav a[onclick*="${tab}"]`);
                if (link) switchTab(tab, link);
            }
        });
    </script>
</body>
</html>
