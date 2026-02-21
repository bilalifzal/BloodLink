<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// 1. Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = "";

// 2. HANDLE PROFILE UPDATE// 3. HANDLE MEDICAL REPORT UPLOAD
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_report'])) {
    $target_dir = "uploads/reports/";

    // ADD THIS: Automatically create folders if they don't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
}
    // ... rest of your existing upload code ...
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $dob = $_POST['dob'];
    $hide_contact = isset($_POST['hide_contact']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE donors SET phone=?, city=?, dob=?, hide_contact=? WHERE id=?");
    $stmt->bind_param("sssii", $phone, $city, $dob, $hide_contact, $user_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success shadow-sm rounded-3'><i class='fa-solid fa-check-circle me-2'></i> Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating profile.</div>";
    }
}

// 3. HANDLE MEDICAL REPORT UPLOAD
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_report'])) {
    $target_dir = "uploads/reports/";
    // Create unique filename to prevent overwriting: ID_timestamp_filename
    $filename = $user_id . "_" . time() . "_" . basename($_FILES["report_file"]["name"]);
    $target_file = $target_dir . $filename;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf" ) {
        $message = "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG & PDF files are allowed.</div>";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["report_file"]["tmp_name"], $target_file)) {
            // Update Database
            $stmt = $conn->prepare("UPDATE donors SET medical_report=?, report_uploaded_at=NOW() WHERE id=?");
            $stmt->bind_param("si", $filename, $user_id);
            $stmt->execute();
            $message = "<div class='alert alert-success shadow-sm rounded-3'><i class='fa-solid fa-file-medical me-2'></i> Medical Report uploaded successfully! Admin will review it.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Sorry, there was an error uploading your file. (Check folder permissions)</div>";
        }
    }
}

// Fetch Current User Data
$user = $conn->query("SELECT * FROM donors WHERE id = $user_id")->fetch_assoc();
?>

<style>
    /* Classic Edit Profile Styles */
    body { background-color: #f8f9fa; }
    
    .settings-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        overflow: hidden;
        background: white;
    }
    .settings-header {
        background: linear-gradient(135deg, var(--primary-color), #5a0000);
        color: white;
        padding: 30px;
    }
    
    /* Vertical Tabs Style */
    .nav-pills .nav-link {
        color: #555;
        font-weight: 600;
        padding: 15px 20px;
        border-radius: 10px;
        transition: 0.3s;
        text-align: left;
    }
    .nav-pills .nav-link:hover {
        background-color: #f0f2f5;
        color: var(--primary-color);
    }
    .nav-pills .nav-link.active {
        background-color: var(--primary-color);
        color: white;
        box-shadow: 0 4px 10px rgba(128,0,0,0.2);
    }
    .nav-pills .nav-link i { width: 25px; }

    .form-label { font-size: 0.9rem; font-weight: 600; color: #666; }
    .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 0.2rem rgba(128,0,0,0.1); }
</style>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="settings-card p-3">
                <div class="d-flex align-items-center mb-4 px-2 mt-2">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $user['fullname']; ?>&background=800000&color=fff" class="rounded-circle me-3" width="50">
                    <div>
                        <h6 class="fw-bold mb-0"><?php echo $user['fullname']; ?></h6>
                        <small class="text-muted">Settings</small>
                    </div>
                </div>
                <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <button class="nav-link active mb-2" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button">
                        <i class="fa-solid fa-user-pen"></i> Edit Profile
                    </button>
                    <button class="nav-link mb-2" id="v-pills-medical-tab" data-bs-toggle="pill" data-bs-target="#v-pills-medical" type="button">
                        <i class="fa-solid fa-file-medical"></i> Medical Report
                    </button>
                    <button class="nav-link text-danger" onclick="window.location.href='dashboard.php'">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <?php echo $message; ?>
            
            <div class="tab-content" id="v-pills-tabContent">
                
                <div class="tab-pane fade show active" id="v-pills-profile">
                    <div class="settings-card">
                        <div class="settings-header">
                            <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">Profile Information</h4>
                            <p class="mb-0 opacity-75">Update your contact details and privacy settings.</p>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control bg-light" value="<?php echo $user['fullname']; ?>" disabled>
                                        <small class="text-muted">Name cannot be changed for security.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control bg-light" value="<?php echo $user['email']; ?>" disabled>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" value="<?php echo $user['city']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="dob" class="form-control" value="<?php echo $user['dob']; ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Blood Group</label>
                                        <input type="text" class="form-control bg-light fw-bold text-danger" value="<?php echo $user['blood_group']; ?>" disabled>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <div class="form-check form-switch p-3 bg-light rounded">
                                            <input class="form-check-input ms-0 me-3" type="checkbox" name="hide_contact" id="privacySwitch" <?php if($user['hide_contact']) echo "checked"; ?>>
                                            <label class="form-check-label fw-bold" for="privacySwitch">Hide Contact Number</label>
                                            <p class="small text-muted mb-0 ms-0">If enabled, your phone number will not be visible to the public search.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 mt-4">
                                        <input type="hidden" name="update_profile" value="1">
                                        <button type="submit" class="btn btn-primary-custom px-5 py-2">Save Changes</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="v-pills-medical">
                    <div class="settings-card">
                        <div class="settings-header bg-dark">
                            <h4 class="fw-bold mb-0" style="font-family: 'Playfair Display', serif;">Medical Center</h4>
                            <p class="mb-0 opacity-75">Upload your recent CBC or fitness report.</p>
                        </div>
                        <div class="card-body p-4 text-center">
                            
                            <?php if (!empty($user['medical_report'])): ?>
                                <div class="bg-light p-4 rounded-3 border mb-4">
                                    <i class="fa-solid fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h5 class="fw-bold">Current Report Available</h5>
                                    <p class="text-muted small">Uploaded on: <?php echo date("d M Y", strtotime($user['report_uploaded_at'])); ?></p>
                                    <a href="uploads/reports/<?php echo $user['medical_report']; ?>" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill">View Report</a>
                                </div>
                                <hr>
                            <?php else: ?>
                                <div class="alert alert-warning border-0 rounded-3 text-start">
                                    <i class="fa-solid fa-triangle-exclamation me-2"></i> 
                                    <strong>No report found.</strong> Please upload a report to verify your health status.
                                </div>
                            <?php endif; ?>

                            <h5 class="fw-bold text-start mb-3">Upload New Report</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="border-2 border-dashed border-secondary rounded-3 p-5" style="border-style: dashed; background-color: #fafafa;">
                                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-secondary mb-3"></i>
                                    <p class="mb-3">Drag and drop your file here or click to browse</p>
                                    <input type="file" name="report_file" class="form-control w-75 mx-auto" required>
                                    <small class="text-muted d-block mt-2">Accepted formats: PDF, JPG, PNG (Max 5MB)</small>
                                </div>
                                <div class="text-end mt-4">
                                    <input type="hidden" name="upload_report" value="1">
                                    <button type="submit" class="btn btn-danger px-4">Upload Document</button>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>