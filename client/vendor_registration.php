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
$words = explode(' ', $full_name);
$initials = '';
foreach ($words as $w) {
    if(!empty($w)) $initials .= strtoupper($w[0]);
}
if(strlen($initials)>2) $initials = substr($initials,0,2);

// Fetch all vendor applications for the user
$stmt = $pdo->prepare("SELECT * FROM vendors WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$vendors = $stmt->fetchAll();

$show_form = isset($_GET['new']) || empty($vendors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration - Servicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/vendor.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        .upload-area {
            border: 2px dashed var(--gray);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background: var(--gray-light);
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 24px;
        }
        .upload-area:hover {
            border-color: var(--green);
            background: var(--green-light);
        }
        .upload-icon {
            font-size: 40px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
    </style>
</head>
<body>

    <!-- TOP NAVIGATION -->
    <header class="top-nav">
        <a href="user_dashboard.php" class="nav-brand">
            <span class="surveycio-logo">SURVEYCIO</span>
        </a>
        <nav class="nav-links">
            <a href="user_dashboard.php">Home</a>
            <a href="health_monitoring.php">Health Monitoring</a>
            <a href="vendor_registration.php" class="active">Vendor Registration</a>
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

    <main style="padding: 40px; background: var(--gray-light); min-height: calc(100vh - 70px);">
        <div class="main-content">
            <div class="Container">
            <div id="toast-container" style="display: none;">Please select at least one Product Type</div>
            
            <?php if ($show_form): ?>
            <header>
                <h1>Vendors' E-Registration Form</h1>
                <p>Complete this form to apply for a permit to sell within the barangay area</p>
            </header>

            <div class="Progression-Bar">
                <div class="step active" id="p-1">1. Business Details</div>
                <div class="step" id="p-2">2. Requirement Compliance</div>
                <div class="step" id="p-3">3. Additional Business Information</div>
                <div class="step" id="p-4">4. File Attachment</div>
                <div class="step" id="p-5">5. Approval</div>
                <div class="step" id="p-6">6. Certification</div>
            </div>
            <form id="registrationForm">
                <!-- Step 1: Business Details -->
                <div class="step-container active" id="step-1">
                    <div class="Section-Title">1. Vendor's Detail</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>Vendor's Full Name</label>
                            <input type="text" id="vendor-name" placeholder="Aling Nena" value="<?= htmlspecialchars($full_name) ?>" required>
                        </div>
                        <div class="Field-group">
                            <label>Permit Number (if already have one)</label>
                            <input type="text" placeholder="ABCE-12345">
                        </div>
                    </div>

                    <div class="Section-Title">2. Contact Information</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>Contact Number</label>
                            <div style="display: flex; gap: 8px;">
                                <select id="country-code" style="width: 120px; padding: 12px; border: 1px solid var(--gray); border-radius: 8px;">
                                    <option value="+63">🇵🇭 +63</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+61">🇦🇺 +61</option>
                                    <option value="+81">🇯🇵 +81</option>
                                    <option value="+86">🇨🇳 +86</option>
                                </select>
                                <input type="text" id="contact-number" placeholder="912 345 6789" style="flex: 1;" required>
                            </div>
                        </div>
                        <div class="Field-group">
                            <label>Home Address</label>
                            <input type="text" id="home-address" placeholder="1020 Aling Nena's House" required>
                        </div>
                    </div>

                    <div class="Section-Title">3. Business Details</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>Business Name</label>
                            <input type="text" id="business-name" placeholder="Aling Nena's Veggie Stand" required>
                        </div>
                        <div class="Field-group">
                            <label>Product Category</label>
                            <select id="product-category">
                                <option>Food Products</option>
                                <option>Non-food Products</option>
                            </select>
                        </div>

                        <div class="product-section" id="food-section">
                            <label class="Section-Title">Product Type:</label>
                            <p style="text-align: left;">Select all that applies</p>
                            <div class="Checkbox-group">
                                <input type="checkbox" id="fresh" name="food" value="fresh">
                                <label for="fresh">Fresh Produce</label>
                                <input type="checkbox" id="cooked" name="food" value="cooked">
                                <label for="cooked">Cooked/Prepared Food</label>
                                <input type="checkbox" id="bakery" name="food" value="bakery">
                                <label for="bakery">Bakery Products</label>
                                <input type="checkbox" id="snacks" name="food" value="snacks">
                                <label for="snacks">Snacks</label>
                                <input type="checkbox" id="beverages" name="food" value="beverages">
                                <label for="beverages">Beverages</label>
                                <input type="checkbox" id="meat" name="food" value="meat">
                                <label for="meat">Meat Products</label>
                                <input type="checkbox" id="seafood" name="food" value="seafood">
                                <label for="seafood">Seafood Products</label>
                                <input type="checkbox" id="food-other" name="food" value="other">
                                <label for="food-other">Other</label>
                                <div class="other food-input-box" id="food-other-input">
                                    <label for="other-input-food">Others:</label>
                                    <input type="text" id="other-input-food" name="other_food" placeholder="Please specify...">
                                </div>
                            </div>
                        </div>

                        <div class="product-section" id="non-food-section">
                            <label class="Section-Title">Product Type:</label>
                            <p style="text-align: left;">Select all that applies</p>
                            <div class="Checkbox-group">
                                <input type="checkbox" id="personal" name="non-food" class="other-trigger" value="personal"> 
                                <label for="personal">Personal Care Products</label>
                                <input type="checkbox" id="house" name="non-food" class="other-trigger" value="house">
                                <label for="house">Household Products</label>
                                <input type="checkbox" id="cloth" name="non-food" class="other-trigger" value="cloth">
                                <label for="cloth">Cloths & Accessories</label>
                                <input type="checkbox" id="electronics" name="non-food" class="other-trigger" value="electronics">
                                <label for="electronics">Electronics & Gadgets</label>
                                <input type="checkbox" id="office" name="non-food" class="other-trigger" value="office">
                                <label for="office">Office Supplies</label>
                                <input type="checkbox" id="toys" name="non-food" class="other-trigger" value="toys">
                                <label for="toys">Toys</label>
                                <input type="checkbox" id="tools" name="non-food" class="other-trigger" value="tools">
                                <label for="tools">Tools</label>
                                <input type="checkbox" id="non-food-other" name="non-food" class="other-trigger" value="other">
                                <label for="non-food-other">Other</label>
                                <div class="other non-food-input-box" id="non-food-other-input">
                                    <label for="other-input-nonfood">Others:</label>
                                    <input type="text" id="other-input-nonfood" name="other_nonfood" placeholder="Please specify...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="next-btn" onclick="nextStep(2)">Next Step</button>
                </div> 

                <!-- Step 2: Compliance & Rules -->
                <div class="step-container" id="step-2">
                    <div class="Section-Title">1. Compliance Requirements</div>
                    <div class="compliance-box">
                        <label class="main-label">Legal Requirements</label>
                        <p class="instruction-text">I have the following requirements:</p>
                        <div class="compliance-row">
                            <span class="requirement-text">Barangay Clearance</span>
                            <div class="radio-options">
                                <label><input type="radio" name="req" value="yes" required> Yes</label>
                                <label><input type="radio" name="req" value="no"> No</label>
                            </div>
                        </div>
                        <div class="compliance-row">
                            <span class="requirement-text">Legal ID</span>
                            <div class="radio-options">
                                <label><input type="radio" name="req1" value="yes" required> Yes </label>
                                <label><input type="radio" name="req1" value="no"> No</label>
                            </div>
                        </div>
                    </div>

                    <div class="Section-Title">2. Sanitation Compliance</div>
                    <div class="agreement-card">
                        <label class="main-label">
                            Sanitation Agreement 
                            <span class="view-rules" onclick="openRules('sanitation')">(View Rules)</span>
                        </label>
                        <p class="agreement-text">I agree to the sanitation and cleanliness requirements set by the barangay. I understand that any violation may result in the cancellation of or nullification of my registration.</p>
                        <label class="Radio-single"><input type="radio" name="compliance" value="agree" required> I Agree</label>
                    </div>

                    <div class="Section-Title">3. Product Compliance</div>
                    <div class="agreement-card">
                        <label class="main-label">
                            Product to be Sold 
                            <span class="view-rules" onclick="openRules('product')">(View Rules)</span>
                        </label>
                        <p class="agreement-text">I agree to comply with the product compliance requirements set by the barangay. I understand that I am only allowed to sell the declared and approved products under my registration.</p>
                        <label class="Radio-single"><input type="radio" name="product-compliance" value="agree" required> I Agree</label>
                    </div>

                    <div class="group-btn">
                        <button type="button" class="back-btn" onclick="nextStep(1)">Back</button>
                        <button type="button" class="next-btn" onclick="nextStep(3)">Next Step</button>
                    </div>
                </div>

                <!-- Step 3: Product Information -->
                <div class="step-container" id="step-3">
                    <div class="Section-Title">1. Product Information</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>List all Products</label>
                            <input type="text" placeholder="e.g., Ampalaya, Egg, Fish, Lotion" required>
                        </div>
                        <div class="Field-group">
                            <label>Source of Products</label>
                            <input type="text" placeholder="e.g., Produced, supplier, Market" required>
                        </div>
                        <div class="Field-group">
                            <label>Estimated Total Daily Volume of Good</label>
                            <input type="text" placeholder="e.g., 100, 1000, 10000" required>
                        </div>
                    </div>    
                    
                    <div class="Section-Title">2. Additional Business Information</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>Estimated Operating Hours</label>
                            <div class="time-row">
                                <input type="time" required> <span>to</span> <input type="time" required>
                            </div>
                        </div>
                        <div class="Field-group">
                            <label>Mode of Selling</label>
                            <select id="modeSelling" onchange="toggleOtherMode(this)">
                                <option value="Stall">Stall</option>
                                <option value="Push cart">Push cart</option>
                                <option value="Mobile Cart">Mobile Cart</option>
                                <option value="Sidewalk vending">Sidewalk vending</option>
                                <option value="Walking vendor">Walking vendor</option>
                                <option value="Other">Other</option>
                            </select>
                            <div id="other-mode-container" class="other-input-container">
                                <p class="small-label" style="margin-top:10px;">Other:</p>
                                <input type="text" id="other-mode-input" placeholder="Other mode of selling" required>
                            </div>
                        </div>
                    </div>
                    <div class="group-btn">
                        <button type="button" class="back-btn" onclick="nextStep(2)">Back</button>
                        <button type="button" class="next-btn" onclick="nextStep(4)">Next Step</button>
                    </div>
                </div>

                <!-- Step 4: File Attachment -->
                <div class="step-container" id="step-4">
                    <div class="Section-Title">Legal File Attachment</div>
                    <div class="Form-grid">
                        <div class="Field-group">
                            <label>Valid ID (Front)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="valid-id-front" name="valid_id_front" hidden>
                                <label for="valid-id-front" class="file-label">
                                    <span class="upload-icon">📁</span>
                                    <span class="file-name">Choose File</span>
                                </label>
                            </div>
                        </div>
                        <div class="Field-group">
                            <label>Valid ID (Back)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="valid-id-back" name="valid_id_back" hidden>
                                <label for="valid-id-back" class="file-label">
                                    <span class="upload-icon">📁</span>
                                    <span class="file-name">Choose File</span>
                                </label>
                            </div>
                        </div>
                        <div class="Field-group">
                            <label>Barangay Clearance</label>
                            <div class="file-upload-wrapper">
                                <input type="file" id="brgy-clr" name="barangay_clearance" hidden>
                                <label for="brgy-clr" class="file-label">
                                    <span class="upload-icon">📁</span>
                                    <span class="file-name">Choose File</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="group-btn">
                        <button type="button" class="back-btn" onclick="nextStep(3)">Back</button>
                        <button type="button" class="next-btn" onclick="nextStep(5)">Next Step</button>
                    </div>
                </div>
                
                <!-- Step 5: Approval & Declaration -->
                <div class="step-container" id="step-5">
                    <div class="Section-Title">5. Approval and Certification</div>
                    <div class="certification-card">
                        <div class="cert-header">
                            <span class="cert-icon">📜</span>
                            <h3>Declaration of Applicant</h3>
                        </div>
                        <p class="cert-intro">By submitting this e-registration form, I understand and agree that:</p>
                        <ul class="cert-list">
                            <li>All information provided is true, accurate, and complete to the best of my knowledge.</li>
                            <li>Any false statements or withholding of required information may result in the disapproval or revocation of my vendor permit.</li>
                            <li>I authorize the Barangay Office to verify the authenticity of the documents attached.</li>
                        </ul>
                        <div class="cert-checkbox-wrapper">
                            <input type="checkbox" id="certify-check" onchange="toggleSubmitBtn(this)">
                            <label for="certify-check">I hereby certify that all information provided is true and correct.</label>
                        </div>
                    </div>
                    <div class="group-btn">
                        <button type="button" class="back-btn" onclick="nextStep(4)">Back</button>
                        <button type="submit" class="next-btn" id="submit-reg" disabled>Submit Registration</button>
                    </div>
                </div>

                <!-- Step 6: Post-Submission Status -->
                <div class="step-container" id="step-6">
                    <div class="status-card">
                        <div class="status-icon" id="status-icon">⏳</div>
                        <div class="Section-Title" style="text-align: center;">Registration Under Review</div>
                        <p class="status-text">
                            Your registration has been submitted successfully. Our barangay officials are currently reviewing your documents. 
                            This process usually takes 1-3 working days.
                        </p>
                        <div class="info-box">
                            <p><strong>Application Reference:</strong> <span id="ref-num">BRGY-2026-8842</span></p>
                            <p><strong>Status:</strong> <span id="status-label" class="status-pending">Pending Approval</span></p>
                        </div>
                        <div class="download-section">
                            <p class="small-label">Your permit will be available for download once the status is "Approved".</p>
                            <button type="button" id="downloadPermit" class="next-btn" disabled>Download Permit (PDF)</button>
                            <button type="button" class="back-btn" onclick="nextStep(5)">Back</button>
                        </div>
                    </div>
                </div>
            </form>
            <?php else: ?>
                <!-- Post-Submission Status Views -->
                <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; text-align: left; padding-bottom: 20px; border-bottom: 1px solid var(--gray);">
                    <div style="flex-grow: 1;">
                        <h1 style="margin-bottom: 8px; font-size: 28px; color: var(--text-dark);">Your Permit Applications</h1>
                        <p style="color: var(--text-muted); margin: 0;">Manage your vendor permits and apply for new ones</p>
                    </div>
                    <a href="?new=1" class="next-btn" style="text-decoration: none; padding: 12px 24px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; flex-shrink: 0;">
                        <i class="ph ph-plus" style="font-size: 20px;"></i> Apply for New Permit
                    </a>
                </header>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                <?php foreach ($vendors as $vendor): ?>
                    <div class="step-container active" style="margin: 0;">
                        <div class="status-card" style="margin-top: 0; height: 100%; display: flex; flex-direction: column;">
                            <?php if ($vendor['status'] === 'pending'): ?>
                                <div class="status-icon" id="status-icon">⏳</div>
                                <div class="Section-Title" style="text-align: center;">Registration Under Review</div>
                                <p class="status-text">
                                    Your registration has been submitted successfully. Our barangay officials are currently reviewing your documents. 
                                    This process usually takes 1-3 working days.
                                </p>
                                <div class="info-box">
                                    <p><strong>Business Name:</strong> <?= htmlspecialchars($vendor['business_name']) ?></p>
                                    <p><strong>Status:</strong> <span class="status-pending" style="color: #a16207; font-weight: 600; background: #fef08a; padding: 4px 8px; border-radius: 4px;">Pending Approval</span></p>
                                </div>
                                <div class="download-section">
                                    <p class="small-label">Your permit will be available for download once the status is "Approved".</p>
                                    <button type="button" class="next-btn" disabled style="opacity: 0.5; cursor: not-allowed;">Download Permit (PDF)</button>
                                </div>

                            <?php elseif ($vendor['status'] === 'approved'): ?>
                                <div class="status-icon" style="color: #27ae60;">✅</div>
                                <div class="Section-Title" style="text-align: center;">Registration Approved</div>
                                <p class="status-text">
                                    Congratulations! Your vendor registration has been approved. You can now download your digital permit.
                                </p>
                                <div class="info-box">
                                    <p><strong>Business Name:</strong> <?= htmlspecialchars($vendor['business_name']) ?></p>
                                    <p><strong>Status:</strong> <span class="status-approved" style="color: #166534; font-weight: 600; background: #dcfce7; padding: 4px 8px; border-radius: 4px;">Approved</span></p>
                                </div>
                                <div class="download-section" style="margin-top: auto;">
                                    <button type="button" class="next-btn download-permit-btn" data-business="<?= htmlspecialchars($vendor['business_name']) ?>" data-owner="<?= htmlspecialchars($vendor['owner_name']) ?>" data-type="<?= htmlspecialchars($vendor['business_type']) ?>" style="background-color: #27ae60; cursor: pointer; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; width: 100%;">Download Permit (PDF)</button>
                                </div>
                                
                            <?php elseif ($vendor['status'] === 'rejected'): ?>
                                <div class="status-icon" style="color: #dc2626;">❌</div>
                                <div class="Section-Title" style="text-align: center;">Registration Rejected</div>
                                <p class="status-text">
                                    Unfortunately, your vendor registration has been rejected by the barangay officials.
                                </p>
                                <div class="info-box">
                                    <p><strong>Status:</strong> <span class="status-rejected" style="color: #991b1b; font-weight: 600; background: #fee2e2; padding: 4px 8px; border-radius: 4px;">Rejected</span></p>
                                </div>
                                <div class="download-section">
                                    <p class="small-label">Please contact the barangay hall for more details or submit a new application.</p>
                                    <a href="../server/actions/delete_vendor_application.php?id=<?= $vendor['id'] ?>" class="back-btn" style="text-decoration: none; display: inline-block;">Submit a New Application</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    
            </div>
    </main>
    <script src="assets/js/vendor.js"></script>

</body>
</html>
