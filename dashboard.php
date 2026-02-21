<?php
session_start();
include 'includes/db_connect.php';
include 'includes/header.php';

// --- 1. SECURITY LOCKDOWN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// --- 2. LOGIC: Admin Approval & Eligibility ---
$user = $conn->query("SELECT * FROM donors WHERE id = $user_id")->fetch_assoc();
$account_status = $user['status']; 
$last_date = $user['last_donation_date'];
$time_eligible = true;
$days_remaining = 0;
$next_date = "Today";

if (!empty($last_date)) {
    $last = new DateTime($last_date);
    $today = new DateTime();
    $next_eligible = clone $last;
    $next_eligible->modify('+90 days'); 
    
    if ($today < $next_eligible) {
        $time_eligible = false;
        $interval = $today->diff($next_eligible);
        $days_remaining = $interval->days;
        $next_date = $next_eligible->format('d M, Y');
    }
}

$can_donate = ($account_status == 'Approved' && $time_eligible);

// --- 3. HANDLE DONATION SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['record_donation'])) {
    if ($can_donate) {
        $date = $_POST['d_date'];
        $hospital = $_POST['d_hospital'];
        $notes = $_POST['d_notes'];
        
        $stmt = $conn->prepare("INSERT INTO donation_history (user_id, donation_date, hospital_name, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $date, $hospital, $notes);
        
        if ($stmt->execute()) {
            $conn->query("UPDATE donors SET last_donation_date = '$date' WHERE id = $user_id");
            echo "<script>window.location.href='dashboard.php';</script>"; 
        }
    } else {
        $message = "<div class='alert alert-danger'>Action Denied.</div>";
    }
}

// Fetch Data
$history = $conn->query("SELECT * FROM donation_history WHERE user_id = $user_id ORDER BY donation_date DESC");
$requests = $conn->query("SELECT * FROM blood_requests WHERE status='Pending' ORDER BY created_at DESC LIMIT 5");
$logs = $conn->query("SELECT * FROM login_history WHERE user_id = $user_id ORDER BY login_time DESC LIMIT 3");
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

$total_donations = $history->num_rows;
$lives_saved = $total_donations * 3;
?>

<style>
    /* PREVIOUS DESIGN STYLES */
    body { background-color: #f0f2f5; }
    .sidebar-card { border: none; border-radius: 20px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
    .profile-bg { height: 100px; background: linear-gradient(135deg, var(--primary-color), #5a0000); }
    .profile-img { width: 90px; height: 90px; background: white; border-radius: 50%; margin: -45px auto 10px; border: 4px solid white; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary-color); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .stat-card { border: none; border-radius: 20px; background: white; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
    .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 15px; }
    .side-link { display: block; padding: 10px 20px; color: #555; text-decoration: none; transition: 0.3s; border-left: 3px solid transparent; }
    .side-link:hover { background-color: #f9f9f9; color: var(--primary-color); border-left-color: var(--primary-color); }
    .side-link.active { font-weight: bold; color: var(--primary-color); border-left-color: var(--primary-color); }
    .logout-btn { color: #dc3545; font-weight: 600; }
    
    /* NEW: Certificate Message Style */
    .cert-msg {
        background: linear-gradient(to right, #fffbf0, #fff);
        border: 1px solid #f0e6cc;
        border-left: 4px solid #c5a059;
    }
</style>

<div class="container my-5">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, <strong><?php echo $user['fullname']; ?></strong></p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <?php if($account_status == 'Pending'): ?>
                <span class="badge bg-warning text-dark px-3 py-2 me-2 rounded-pill shadow-sm">Pending Approval</span>
            <?php elseif($account_status == 'Rejected'): ?>
                <span class="badge bg-danger px-3 py-2 me-2 rounded-pill shadow-sm">Rejected</span>
            <?php elseif(!$time_eligible): ?>
                <span class="badge bg-secondary px-3 py-2 me-2 rounded-pill shadow-sm">Wait <?php echo $days_remaining; ?> days</span>
            <?php else: ?>
                <span class="badge bg-success px-3 py-2 me-2 rounded-pill shadow-sm">Eligible</span>
            <?php endif; ?>
            
            <button class="btn btn-primary-custom shadow-sm" data-bs-toggle="modal" data-bs-target="#donationModal"
                    <?php if(!$can_donate) echo 'disabled style="opacity: 0.6; cursor: not-allowed;"'; ?>>
                <i class="fa-solid fa-hand-holding-medical me-2"></i> I Donated Today
            </button>
        </div>
    </div>

    <?php echo $message; ?>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="sidebar-card text-center mb-4">
                <div class="profile-bg"></div>
                <div class="profile-img"><i class="fa-solid fa-user"></i></div>
                <h4 class="fw-bold"><?php echo $user['fullname']; ?></h4>
                <p class="text-muted small mb-3"><?php echo $user['city']; ?> | <?php echo $user['blood_group']; ?></p>
                <div class="d-flex justify-content-around border-top py-3 bg-light">
                    <div><h5 class="fw-bold mb-0"><?php echo $total_donations; ?></h5><small class="text-muted" style="font-size: 0.75rem;">Donations</small></div>
                    <div><h5 class="fw-bold mb-0 text-success"><?php echo $lives_saved; ?></h5><small class="text-muted" style="font-size: 0.75rem;">Lives Saved</small></div>
                </div>
                <div class="text-start border-top pt-2 pb-2">
                    <a href="#" class="side-link active"><i class="fa-solid fa-chart-pie me-2"></i> Overview</a>
                    <a href="edit_profile.php" class="side-link"><i class="fa-solid fa-user-pen me-2"></i> Edit Profile</a>
                    
  <a href="edit_profile.php" class="side-link"><i class="fa-solid fa-file-medical me-2"></i> Medical Report</a>
  <a href="donor_card.php" class="side-link"><i class="fa-solid fa-id-card me-2"></i> My Donor ID Card</a>
                    <div class="border-top my-2"></div>
                    <a href="logout.php" class="side-link logout-btn"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>
            <div class="sidebar-card p-4">
                <h6 class="fw-bold text-secondary mb-3">Security Log</h6>
                <ul class="list-unstyled small mb-0">
                    <?php while($log = $logs->fetch_assoc()): ?>
                    <li class="d-flex justify-content-between mb-2 text-muted">
                        <span><?php echo date("M d, H:i", strtotime($log['login_time'])); ?></span>
                        <span>IP: <?php echo $log['ip_address']; ?></span>
                    </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-md-4"><div class="stat-card h-100"><div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fa-solid fa-droplet"></i></div><h6 class="text-muted small fw-bold">Blood Group</h6><h3 class="fw-bold mb-0"><?php echo $user['blood_group']; ?></h3></div></div>
                <div class="col-md-4"><div class="stat-card h-100"><div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-calendar-day"></i></div><h6 class="text-muted small fw-bold">Next Eligible</h6><h5 class="fw-bold mb-0"><?php echo $next_date; ?></h5></div></div>
                <div class="col-md-4"><div class="stat-card h-100"><div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-user-check"></i></div><h6 class="text-muted small fw-bold">Account Status</h6><h5 class="fw-bold mb-0"><?php echo $account_status; ?></h5></div></div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
                    <li class="nav-item"><button class="nav-link active" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests">Live Requests</button></li>
                    <li class="nav-item"><button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history">My History</button></li>
                    <li class="nav-item"><button class="nav-link" id="notif-tab" data-bs-toggle="tab" data-bs-target="#notif">Messages <span class="badge bg-danger rounded-circle small">!</span></button></li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="requests">
                        <?php if ($requests->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="bg-light small"><tr><th class="ps-3 border-0 rounded-start">Patient</th><th class="border-0">Location</th><th class="pe-3 border-0 rounded-end">Action</th></tr></thead>
                                    <tbody>
                                        <?php while($req = $requests->fetch_assoc()): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold"><?php echo $req['patient_name']; ?><br><small class="text-muted fw-normal"><?php echo $req['blood_group']; ?></small></td>
                                            <td><?php echo $req['hospital_name']; ?></td>
                                            <td class="pe-3"><a href="tel:<?php echo $req['contact_number']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">Call</a></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted"><p>No pending requests.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="history">
                        <?php if ($history->num_rows > 0): ?>
                            <ul class="list-group list-group-flush">
                                <?php while($h = $history->fetch_assoc()): ?>
                                <li class="list-group-item border-0 ps-0 mb-2"><div class="d-flex align-items-center"><div class="me-3 text-success fs-4"><i class="fa-solid fa-circle-check"></i></div><div><h6 class="mb-0 fw-bold"><?php echo $h['hospital_name']; ?></h6><small class="text-muted"><?php echo date("d M Y", strtotime($h['donation_date'])); ?></small></div></div></li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center py-5"><p class="text-muted">No history yet.</p></div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="notif">
                        <ul class="list-unstyled">
                            <?php while($n = $notifications->fetch_assoc()): ?>
                                
                                <?php if($n['type'] == 'certificate'): ?>
                                <li class="mb-3 p-3 rounded-3 cert-msg shadow-sm">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3 pt-1"><i class="fa-solid fa-award fa-2x text-warning"></i></div>
                                        <div class="w-100">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1 fw-bold text-dark">Certificate Awarded</h6>
                                                <small class="text-muted"><?php echo date("M d", strtotime($n['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-2 text-secondary small"><?php echo $n['message']; ?></p>
                                            <a href="certificate.php?id=<?php echo $n['id']; ?>" target="_blank" class="btn btn-sm btn-warning text-dark fw-bold shadow-sm">
                                                <i class="fa-solid fa-eye me-1"></i> View & Print Certificate
                                            </a>
                                        </div>
                                    </div>
                                </li>
                                
                                <?php else: ?>
                                <li class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex">
                                        <div class="mt-2 me-2 text-danger"><i class="fa-solid fa-circle text-danger" style="font-size: 8px;"></i></div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">System Message</h6>
                                            <p class="mb-1 small text-secondary"><?php echo $n['message']; ?></p>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo date("M d", strtotime($n['created_at'])); ?></small>
                                        </div>
                                    </div>
                                </li>
                                <?php endif; ?>

                            <?php endwhile; ?>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="donationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0"><h5 class="modal-title fw-bold">Record Donation</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="POST"><div class="modal-body p-4"><div class="mb-3"><label class="fw-bold small">Date</label><input type="date" name="d_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>"></div><div class="mb-3"><label class="fw-bold small">Hospital</label><input type="text" name="d_hospital" class="form-control" required placeholder="Hospital Name"></div><div class="mb-3"><label class="fw-bold small">Notes</label><textarea name="d_notes" class="form-control" rows="2"></textarea></div><input type="hidden" name="record_donation" value="1"><div class="d-grid"><button type="submit" class="btn btn-danger rounded-pill fw-bold">Confirm</button></div></div></form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>