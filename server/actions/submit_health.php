<?php
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../client/index.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $head_of_family = trim($_POST['head_of_family'] ?? '');
    $household_number = ($_POST['num_members'] ?? 0) + 1; // total members
    $zone = trim($_POST['zone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($head_of_family) || empty($zone) || empty($address)) {
        die("Error: Head of family, zone, and address are required.");
    }
    
    // Head specific from step 1
    $head_age = $_POST['head_age'] ?? 0;
    $head_gender = $_POST['head_gender'] ?? '';
    $head_blood = $_POST['head_blood_type'] ?? 'Unknown';
    $head_cc = $_POST['head_country_code'] ?? '+63';
    $head_cn = $_POST['head_contact'] ?? '';
    $contact_number = trim($head_cc . ' ' . $head_cn);

    // Extract Head's screening and consent (idx = 0)
    $h_cond = $_POST['cond_0'] ?? 'No';
    $h_drop = $_POST['cond_drop_0'] ?? '';
    $h_other = $_POST['cond_other_0'] ?? '';
    $h_details = ($h_drop === 'Others') ? $h_other : $h_drop;
    
    $h_meds = $_POST['meds_0'] ?? 'No';
    $h_surg = $_POST['surg_0'] ?? 'No';
    $h_preg = $_POST['preg_0'] ?? 'N/A';
    
    $h_consent = $_POST['consent_0'] ?? 'No';
    $h_donated = $_POST['donated_0'] ?? 'No';
    $h_last_don = !empty($_POST['last_date_0']) ? $_POST['last_date_0'] : null;

    $member_names = $_POST['member_name'] ?? [];
    $member_ages = $_POST['member_age'] ?? [];
    $member_genders = $_POST['member_gender'] ?? [];
    $member_relationships = $_POST['member_relationship'] ?? [];
    $member_bloods = $_POST['member_blood_type'] ?? [];
    $member_ccs = $_POST['member_country_code'] ?? [];
    $member_contacts = $_POST['member_contact'] ?? [];

    try {
        $pdo->beginTransaction();

        // 1. Insert Head into health_records
        $stmt = $pdo->prepare("INSERT INTO health_records (user_id, head_of_family, household_number, zone, address, donor_consent, donated_before, last_donation_date, gender, age, blood_type, existing_condition, condition_details, taking_medication, recent_surgery, pregnant, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $head_of_family, $household_number, $zone, $address, $h_consent, $h_donated, $h_last_don, $head_gender, $head_age, $head_blood, $h_cond, $h_details, $h_meds, $h_surg, $h_preg, $contact_number]);
        $health_record_id = $pdo->lastInsertId();

        // 2. Insert Head into donors if consented
        if ($h_consent === 'Yes') {
            $donorStmt = $pdo->prepare("INSERT INTO donors (user_id, name, age, contact_number, blood_type, status, last_donation) VALUES (?, ?, ?, ?, ?, 'screening', ?)");
            $donorStmt->execute([$user_id, $head_of_family, $head_age, $contact_number, $head_blood, $h_last_don]);
        }

        // 3. Insert Members
        if (!empty($member_names)) {
            for ($i = 0; $i < count($member_names); $i++) {
                $m_name = $member_names[$i];
                $m_age = $member_ages[$i] ?? 0;
                $m_gender = $member_genders[$i] ?? '';
                $m_rel = $member_relationships[$i] ?? 'Member';
                $m_blood = $member_bloods[$i] ?? 'Unknown';
                $m_contact = trim(($member_ccs[$i] ?? '+63') . ' ' . ($member_contacts[$i] ?? ''));
                if ($m_contact === '+63' || $m_contact === '+1' || $m_contact === '+44' || $m_contact === '+61' || $m_contact === '+81' || $m_contact === '+86') {
                    $m_contact = null; // Don't save if only country code
                }
                
                if (empty(trim($m_name))) continue;

                $idx = $i + 1; // offset by 1 because 0 is Head
                
                $m_cond = $_POST['cond_'.$idx] ?? 'No';
                $m_drop = $_POST['cond_drop_'.$idx] ?? '';
                $m_other = $_POST['cond_other_'.$idx] ?? '';
                $m_details = ($m_drop === 'Others') ? $m_other : $m_drop;
                
                $m_meds = $_POST['meds_'.$idx] ?? 'No';
                $m_surg = $_POST['surg_'.$idx] ?? 'No';
                $m_preg = $_POST['preg_'.$idx] ?? 'N/A';
                
                $m_consent = $_POST['consent_'.$idx] ?? 'No';
                $m_donated = $_POST['donated_'.$idx] ?? 'No';
                $m_last_don = !empty($_POST['last_date_'.$idx]) ? $_POST['last_date_'.$idx] : null;

                $memStmt = $pdo->prepare("INSERT INTO family_members (health_record_id, name, age, blood_type, existing_condition, condition_details, taking_medication, recent_surgery, pregnant, donor_consent, donated_before, last_donation_date, gender, relationship, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $memStmt->execute([$health_record_id, $m_name, $m_age, $m_blood, $m_cond, $m_details, $m_meds, $m_surg, $m_preg, $m_consent, $m_donated, $m_last_don, $m_gender, $m_rel, $m_contact]);

                // Insert Member into donors if consented
                if ($m_consent === 'Yes') {
                    $donorStmt = $pdo->prepare("INSERT INTO donors (user_id, name, age, contact_number, blood_type, status, last_donation) VALUES (?, ?, ?, ?, ?, 'screening', ?)");
                    $donorStmt->execute([$user_id, $m_name, $m_age, $m_contact ?? $contact_number, $m_blood, $m_last_don]);
                }
            }
        }

        $pdo->commit();
        header('Location: ../../client/user_dashboard.php?health_success=1');
        exit;
    } catch(PDOException $e) {
        $pdo->rollBack();
        die("Error saving health record: " . $e->getMessage());
    }
}
?>
