<?php
session_start();
include '../includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. FETCH CURRENT SETTINGS
$settings_query = $conn->query("SELECT * FROM settings WHERE id=1");

if ($settings_query->num_rows > 0) {
    $settings = $settings_query->fetch_assoc();
} else {
    // FALLBACK: If no settings exist in DB, use default values to stop errors
    $settings = [
        'site_title' => 'BloodLink Pro',
        'helpline_number' => '',
        'admin_email' => '',
        'address' => '',
        'urgent_mode' => 0,
        'urgent_message' => ''
    ];
}

// 3. HANDLE ACTIONS

// A. UPDATE GENERAL SETTINGS
if (isset($_POST['update_general'])) {
    $title = $_POST['site_title'];
    $phone = $_POST['helpline'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $urgent_msg = $_POST['urgent_msg'];
    $urgent_mode = isset($_POST['urgent_mode']) ? 1 : 0;

    // Check if row 1 exists, if not INSERT, else UPDATE
    $check = $conn->query("SELECT id FROM settings WHERE id=1");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO settings (id, site_title, helpline_number, admin_email, address, urgent_mode, urgent_message) VALUES (1, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssis", $title, $phone, $email, $address, $urgent_mode, $urgent_msg);
    } else {
        $stmt = $conn->prepare("UPDATE settings SET site_title=?, helpline_number=?, admin_email=?, address=?, urgent_mode=?, urgent_message=? WHERE id=1");
        $stmt->bind_param("ssssis", $title, $phone, $email, $address, $urgent_mode, $urgent_msg);
    }
    
    if ($stmt->execute()) {
        $msg = "System configuration updated successfully!";
        $msg_type = "success";
        // Refresh data immediately
        $settings = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();
    } else {
        $msg = "Error updating settings: " . $conn->error;
        $msg_type = "danger";
    }
}

// B. CHANGE PASSWORD
if (isset($_POST['change_pass'])) {
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if ($new_pass === $confirm_pass) {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("UPDATE admin SET password=? WHERE id=?");
        $stmt->bind_param("si", $new_pass, $admin_id);
        
        if ($stmt->execute()) {
            $msg = "Admin password changed successfully!";
            $msg_type = "success";
        } else {
            $msg = "Database error.";
            $msg_type = "danger";
        }
    } else {
        $msg = "New passwords do not match.";
        $msg_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings | BloodLink Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-bg: #1a1c23; --sidebar-hover: #2d303b; --royal-gold: #c5a059; --royal-red: #800000; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

        /* SIDEBAR */
        .sidebar { width: 280px; height: 100vh; position: fixed; top: 0; left: 0; background: var(--sidebar-bg); color: #a0aec0; display: flex; flex-direction: column; box-shadow: 5px 0 15px rgba(0,0,0,0.1); z-index: 1000; }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; }
        .nav-item { padding: 15px 25px; color: #a0aec0; text-decoration: none; display: flex; align-items: center; transition: 0.3s; border-left: 4px solid transparent; cursor: pointer; }
        .nav-item:hover, .nav-item.active { background: var(--sidebar-hover); color: white; border-left-color: var(--royal-gold); }
        .nav-item i { width: 35px; font-size: 1.1rem; }
        
        .main-content { margin-left: 280px; padding: 30px; }

        /* CARDS */
        .settings-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; }
        .card-header-custom { background: linear-gradient(135deg, #1a1c23, #2c3e50); color: white; padding: 20px 25px; font-weight: bold; font-family: 'Playfair Display', serif; letter-spacing: 1px; }
        .form-label { font-size: 0.85rem; text-transform: uppercase; font-weight: 700; color: #888; margin-bottom: 8px; }
        .form-control:focus { border-color: var(--royal-gold); box-shadow: 0 0 10px rgba(197, 160, 89, 0.2); }

        /* EMERGENCY SWITCH */
        .emergency-box { background: #fff5f5; border: 2px solid #fed7d7; padding: 20px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; }
        .form-switch .form-check-input { width: 3.5em; height: 1.75em; cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: var(--royal-red); border-color: var(--royal-red); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold text-white mb-0">BLOOD<span style="color: var(--royal-gold);">LINK</span></h4>
            <small style="letter-spacing: 1px; opacity: 0.6;">ADMIN PANEL</small>
        </div>
        <div class="mt-4">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="manage_donors.php" class="nav-item"><i class="fa-solid fa-users"></i> Donors</a>
            <a href="manage_requests.php" class="nav-item"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
            <a href="manage_campaigns.php" class="nav-item"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <div class="mt-4 mb-2 ps-4 text-uppercase small fw-bold" style="opacity: 0.4;">System</div>
            <a href="settings.php" class="nav-item active"><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark">System Configuration</h3>
                <p class="text-muted">Manage global settings and security protocols.</p>
            </div>
        </div>

        <?php if(isset($msg)) echo "<div class='alert alert-$msg_type rounded-pill px-4 mb-4'><i class='fa-solid fa-info-circle me-2'></i> $msg</div>"; ?>

        <div class="row g-4">
            
            <div class="col-lg-7">
                <div class="settings-card">
                    <div class="card-header-custom"><i class="fa-solid fa-sliders me-2 text-warning"></i> General Configuration</div>
                    <div class="p-4">
                        <form method="POST">
                            
                            <div class="emergency-box mb-4">
                                <div>
                                    <h6 class="fw-bold text-danger mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Emergency Broadcast Mode</h6>
                                    <p class="text-muted small mb-0">Enables a high-visibility alert banner on the homepage.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="urgent_mode" id="urgentSwitch" <?php echo (isset($settings['urgent_mode']) && $settings['urgent_mode'] == 1) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Broadcast Message</label>
                                <input type="text" name="urgent_msg" class="form-control" value="<?php echo isset($settings['urgent_message']) ? htmlspecialchars($settings['urgent_message']) : ''; ?>" placeholder="e.g. Critical: O- Blood needed at Allied Hospital">
                            </div>

                            <hr class="my-4 text-muted">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Website Name</label>
                                    <input type="text" name="site_title" class="form-control" value="<?php echo isset($settings['site_title']) ? htmlspecialchars($settings['site_title']) : ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Helpline Number</label>
                                    <input type="text" name="helpline" class="form-control" value="<?php echo isset($settings['helpline_number']) ? htmlspecialchars($settings['helpline_number']) : ''; ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Official Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo isset($settings['admin_email']) ? htmlspecialchars($settings['admin_email']) : ''; ?>" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Organization Address</label>
                                    <textarea name="address" class="form-control" rows="2"><?php echo isset($settings['address']) ? htmlspecialchars($settings['address']) : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" name="update_general" class="btn btn-dark px-4 py-2 fw-bold">SAVE CHANGES</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="settings-card">
                    <div class="card-header-custom bg-danger"><i class="fa-solid fa-shield-halved me-2 text-warning"></i> Security & Access</div>
                    <div class="p-4">
                        <div class="text-center mb-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fa-solid fa-user-lock fa-2x text-dark"></i>
                            </div>
                            <h6 class="fw-bold">Admin Password</h6>
                            <p class="text-muted small">Update your access credentials regularly.</p>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_pass" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_pass" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" name="change_pass" class="btn btn-outline-danger w-100 py-2 fw-bold">UPDATE PASSWORD</button>
                        </form>

                        <div class="alert alert-warning mt-4 small mb-0">
                            <i class="fa-solid fa-lock me-2"></i> <strong>Note:</strong> Changes will take effect immediately. You may be asked to log in again.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>