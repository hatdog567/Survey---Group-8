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
    if (!empty($w))
        $initials .= strtoupper($w[0]);
}
if (strlen($initials) > 2)
    $initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Monitoring - Servicio</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <style>
        .member-card {
            background: var(--gray-light);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            border: 1px solid var(--gray);
        }

        .member-card h4 {
            margin-bottom: 16px;
            color: var(--green-dark);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }
    </style>
</head>

<body>

    <header class="top-nav">
        <a href="user_dashboard.php" class="nav-brand">
            <span class="surveycio-logo">SURVEYCIO</span>
        </a>
        <nav class="nav-links">
            <a href="user_dashboard.php">Home</a>
            <a href="health_monitoring.php" class="active">Health Monitoring</a>
            <a href="vendor_registration.php">Vendor Registration</a>
        </nav>
        <div class="user-profile">
            <a href="user_settings.php"
                style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                <div class="avatar" style="overflow:hidden;">
                    <?php if ($user['profile_image'] !== 'default_avatar.png' && !empty($user['profile_image'])): ?>
                        <img src="../server/uploads/<?= htmlspecialchars($user['profile_image']) ?>" alt="Avatar"
                            style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <span style="font-weight: 500;"><?= htmlspecialchars($full_name) ?></span>
            </a>
            <a href="user_settings.php" style="margin-left: 16px; color: var(--text-muted); text-decoration: none;"
                title="Account Settings"><i class="ph ph-gear"></i> Settings</a>
            <a href="index.html" style="margin-left: 16px; color: var(--text-muted); text-decoration: none;"><i
                    class="ph ph-sign-out"></i> Logout</a>
        </div>
    </header>

    <main style="padding: 40px; background: var(--gray-light); min-height: calc(100vh - 70px);">

        <div class="form-container">
            <div class="page-header" style="text-align: center;">
                <h1>Family Health Survey</h1>
                <p>Comprehensive health profiling for your entire household.</p>
            </div>

            <!-- STEPPER UI -->
            <div class="stepper">
                <div class="step active">
                    <div class="step-circle">1</div><span class="step-label">Household</span>
                </div>
                <div class="step">
                    <div class="step-circle">2</div><span class="step-label">Members</span>
                </div>
                <div class="step">
                    <div class="step-circle">3</div><span class="step-label">Screening</span>
                </div>
                <div class="step">
                    <div class="step-circle">4</div><span class="step-label">Blood Donor</span>
                </div>
            </div>

            <!-- FORM CONTENT -->
            <form id="healthForm" action="../server/actions/submit_health.php" method="POST">

                <!-- STEP 1 -->
                <div id="step1">
                    <h3 style="margin-bottom: 24px; font-size: 20px;">1. Household Information</h3>
                    <div class="form-group">
                        <label>Head of Family</label>
                        <input type="text" id="head_name" name="head_of_family" placeholder="Full Name"
                            value="<?= htmlspecialchars($full_name) ?>" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Age of Head</label>
                            <input type="number" id="head_age" name="head_age" required>
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select name="head_gender" id="head_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Blood Type of Head</label>
                            <select name="head_blood_type" id="head_blood">
                                <option value="Unknown">Unknown</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Contact Number</label>
                            <div style="display: flex; gap: 8px;">
                                <select id="country-code" name="head_country_code"
                                    style="width: 120px; padding: 12px; border: 1px solid var(--gray); border-radius: 8px;">
                                    <option value="+63">🇵🇭 +63</option>
                                    <option value="+1">🇺🇸 +1</option>
                                    <option value="+44">🇬🇧 +44</option>
                                    <option value="+61">🇦🇺 +61</option>
                                    <option value="+81">🇯🇵 +81</option>
                                    <option value="+86">🇨🇳 +86</option>
                                </select>
                                <input type="text" id="contact-number" name="head_contact" placeholder="912 345 6789" style="flex: 1;"
                                    required>
                            </div>
                        </div>

                        <!-- 
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" name="contact_number" placeholder="09xxxxxxxxx" required>
                        </div>
                        -->

                        <div class="form-group">
                            <label>Additional Members</label>
                            <input type="number" id="num_members" name="num_members" value="0" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Zone / Purok</label>
                            <select name="zone" required>
                                <option value="">Select Zone</option>
                                <option value="1">Zone 1</option>
                                <option value="2">Zone 2</option>
                                <option value="3">Zone 3</option>
                                <option value="4">Zone 4</option>
                                <option value="5">Zone 5</option>
                                <option value="6">Zone 6</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Complete Address</label>
                        <textarea name="address" rows="3" placeholder="Street, House No." required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline"
                            onclick="window.location.href='user_dashboard.php'">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(2)">Next <i
                                class="ph ph-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div id="step2" style="display: none;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;">2. Additional Family Members</h3>
                    <div id="membersContainer"></div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="goToStep(1)">Back</button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(3)">Next <i
                                class="ph ph-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div id="step3" style="display: none;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;">3. Individual Health Screening</h3>
                    <p style="margin-bottom: 20px; color: var(--text-muted);">Please answer the following for each
                        member of the household.</p>
                    <div id="screeningContainer"></div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="goToStep(2)">Back</button>
                        <button type="button" class="btn btn-primary" onclick="goToStep(4)">Next <i
                                class="ph ph-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 4 -->
                <div id="step4" style="display: none;">
                    <h3 style="margin-bottom: 24px; font-size: 20px;">4. Voluntary Blood Donation</h3>
                    <div
                        style="background: #e0f2fe; padding: 20px; border-radius: 8px; margin-bottom: 24px; color: #0284c7; border: 1px solid #bae6fd;">
                        <i class="ph ph-drop" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <p style="font-weight: 500;">Emergency Donor Directory</p>
                        <p style="font-size: 14px; margin-top: 5px;">Indicate which family members agree to be contacted
                            for emergency blood donation.</p>
                    </div>
                    <div id="donorContainer"></div>

                    <div class="form-actions" style="margin-top: 40px;">
                        <button type="button" class="btn btn-outline" onclick="goToStep(3)">Back</button>
                        <button type="submit" class="btn btn-primary">Save Complete Profile</button>
                    </div>
                </div>

            </form>
        </div>
    </main>

    <script>
        function toggleCondition(idx) {
            const val = document.getElementById('cond_' + idx).value;
            document.getElementById('cond_details_' + idx).style.display = (val === 'Yes') ? 'block' : 'none';
        }

        function toggleOther(idx) {
            const val = document.getElementById('cond_drop_' + idx).value;
            document.getElementById('cond_other_' + idx).style.display = (val === 'Others') ? 'block' : 'none';
        }

        function toggleDonation(idx) {
            const val = document.getElementById('donated_' + idx).value;
            document.getElementById('last_don_' + idx).style.display = (val === 'Yes') ? 'block' : 'none';
        }

        function toggleConsent(idx) {
            const val = document.getElementById('consent_' + idx).value;
            document.getElementById('history_' + idx).style.display = (val === 'Yes') ? 'block' : 'none';
        }

        function goToStep(targetStep) {
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                const el = document.getElementById('step' + i);
                if (el) el.style.display = 'none';
            }

            const num = parseInt(document.getElementById('num_members').value) || 0;
            const headName = document.getElementById('head_name').value || 'Head of Family';

            // Collect member names dynamically for later steps
            let members = [{ name: headName, isHead: true }];

            if (targetStep >= 2) {
                const container = document.getElementById('membersContainer');
                if (targetStep === 2) {
                    if (num === 0) {
                        container.innerHTML = '<p style="color:var(--text-muted); font-style:italic;">No additional members specified. Click Next.</p>';
                    } else {
                        // Only generate if empty or count changed (simplified for prototype)
                        if (container.children.length !== num) {
                            container.innerHTML = '';
                            for (let i = 1; i <= num; i++) {
                                container.innerHTML += `
                                    <div class="member-card">
                                        <h4>Member ${i}</h4>
                                        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr; gap: 16px;">
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Full Name</label>
                                                <input type="text" name="member_name[]" class="mem-name-input" required>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Relationship</label>
                                                <select name="member_relationship[]" required>
                                                    <option value="Spouse">Spouse</option>
                                                    <option value="Child">Child</option>
                                                    <option value="Parent">Parent</option>
                                                    <option value="Sibling">Sibling</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Age</label>
                                                <input type="number" name="member_age[]" required>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Gender</label>
                                                <select name="member_gender[]" required>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="form-group" style="margin-bottom: 0;">
                                                <label>Blood Type</label>
                                                <select name="member_blood_type[]">
                                                    <option value="Unknown">Unknown</option>
                                                    <option value="A+">A+</option>
                                                    <option value="A-">A-</option>
                                                    <option value="B+">B+</option>
                                                    <option value="B-">B-</option>
                                                    <option value="O+">O+</option>
                                                    <option value="O-">O-</option>
                                                    <option value="AB+">AB+</option>
                                                    <option value="AB-">AB-</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                    }
                }

                // Fetch current values
                const nameInputs = document.querySelectorAll('.mem-name-input');
                nameInputs.forEach(input => {
                    if (input.value) members.push({ name: input.value, isHead: false });
                    else members.push({ name: 'Unnamed Member', isHead: false });
                });
            }

            if (targetStep === 3) {
                const container = document.getElementById('screeningContainer');
                if (container.children.length !== members.length) {
                    container.innerHTML = '';
                    members.forEach((m, idx) => {
                        const badge = m.isHead ? '<span style="background:var(--green); color:white; padding:2px 8px; border-radius:12px; font-size:12px;">Head</span>' : '';
                        container.innerHTML += `
                            <div class="member-card">
                                <h4><i class="ph ph-user"></i> ${m.name} ${badge}</h4>
                                <div class="form-group">
                                    <label>Existing Condition?</label>
                                    <select name="cond_${idx}" id="cond_${idx}" onchange="toggleCondition(${idx})">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                                <div class="form-group" id="cond_details_${idx}" style="display: none; padding-left: 20px; border-left: 3px solid var(--green);">
                                    <label>Specify:</label>
                                    <select name="cond_drop_${idx}" id="cond_drop_${idx}" onchange="toggleOther(${idx})">
                                        <option value="">Select Condition</option>
                                        <option value="Hypertension">Hypertension</option>
                                        <option value="Diabetes">Diabetes</option>
                                        <option value="Anemia">Anemia</option>
                                        <option value="Heart condition">Heart condition</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <input type="text" name="cond_other_${idx}" id="cond_other_${idx}" placeholder="Please specify" style="display: none; margin-top: 10px;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Taking Meds?</label>
                                        <select name="meds_${idx}">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Recent Surgery?</label>
                                        <select name="surg_${idx}">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom:0;">
                                        <label>Pregnant?</label>
                                        <select name="preg_${idx}">
                                            <option value="N/A">N/A</option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    const titles = container.querySelectorAll('h4');
                    members.forEach((m, idx) => {
                        const badge = m.isHead ? '<span style="background:var(--green); color:white; padding:2px 8px; border-radius:12px; font-size:12px;">Head</span>' : '';
                        titles[idx].innerHTML = `<i class="ph ph-user"></i> ${m.name} ${badge}`;
                    });
                }
            }

            if (targetStep === 4) {
                const container = document.getElementById('donorContainer');
                if (container.children.length !== members.length) {
                    container.innerHTML = '';
                    members.forEach((m, idx) => {
                        const badge = m.isHead ? '<span style="background:var(--green); color:white; padding:2px 8px; border-radius:12px; font-size:12px;">Head</span>' : '';
                        container.innerHTML += `
                            <div class="member-card" style="border-left: 4px solid #0ea5e9;">
                                <h4><i class="ph ph-drop" style="color:#0ea5e9;"></i> ${m.name} ${badge}</h4>
                                <div class="form-group">
                                    <label>Consent to be contacted for blood donation?</label>
                                    <select name="consent_${idx}" id="consent_${idx}" onchange="toggleConsent(${idx})">
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                </div>
                                <div id="history_${idx}" style="display:none; padding-top: 10px; border-top: 1px dashed var(--gray);">
                                    <div class="form-group">
                                        <label>Donated before?</label>
                                        <select name="donated_${idx}" id="donated_${idx}" onchange="toggleDonation(${idx})">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="last_don_${idx}" style="display:none;">
                                        <label>Last donation date:</label>
                                        <input type="date" name="last_date_${idx}" max="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    const titles = container.querySelectorAll('h4');
                    members.forEach((m, idx) => {
                        const badge = m.isHead ? '<span style="background:var(--green); color:white; padding:2px 8px; border-radius:12px; font-size:12px;">Head</span>' : '';
                        titles[idx].innerHTML = `<i class="ph ph-drop" style="color:#0ea5e9;"></i> ${m.name} ${badge}`;
                    });
                }
            }

            // Show target step
            document.getElementById('step' + targetStep).style.display = 'block';

            // Update Stepper UI classes
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                const stepNum = index + 1;
                step.classList.remove('active', 'completed');
                if (stepNum < targetStep) {
                    step.classList.add('completed');
                } else if (stepNum === targetStep) {
                    step.classList.add('active');
                }
            });
        }
    </script>
</body>

</html>