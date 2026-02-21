<?php
session_start();
include '../includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. HANDLE ACTIONS
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    if ($_GET['action'] == 'approve') {
        $conn->query("UPDATE donors SET status='Approved' WHERE id='$id'");
        $msg = "Donor Verified Successfully!";
        $msg_type = "success";
    }
    
    if ($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM donors WHERE id='$id'");
        $msg = "Donor Record Deleted!";
        $msg_type = "danger";
    }
}

// 3. FETCH GLOBAL COUNTS (For Sidebar Badge)
$donors_pending = $conn->query("SELECT id FROM donors WHERE status='Pending'")->num_rows;

// 4. FETCH CHART DATA (Real Analysis)
// A. Blood Group Ratio
$bg_query = $conn->query("SELECT blood_group, COUNT(*) as count FROM donors GROUP BY blood_group");
$bg_labels = [];
$bg_data = [];
while($row = $bg_query->fetch_assoc()) {
    $bg_labels[] = $row['blood_group'];
    $bg_data[] = $row['count'];
}

// B. Status Ratio
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM donors GROUP BY status");
$st_labels = [];
$st_data = [];
while($row = $status_query->fetch_assoc()) {
    $st_labels[] = ($row['status'] == 'Approved') ? 'Verified' : 'Pending';
    $st_data[] = $row['count'];
}

// 5. FETCH DONORS WITH FILTERS
$filter_bg = isset($_GET['bg']) ? $_GET['bg'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM donors WHERE 1=1";
if($filter_bg) $sql .= " AND blood_group = '$filter_bg'";
if($filter_status) $sql .= " AND status = '$filter_status'";
$sql .= " ORDER BY id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Management | BloodLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <style>
        :root {
            --sidebar-bg: #1a1c23;
            --sidebar-hover: #2d303b;
            --royal-gold: #c5a059;
            --royal-red: #800000;
        }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

        /* --- SIDEBAR (MATCHING DASHBOARD EXACTLY) --- */
        .sidebar {
            width: 280px; height: 100vh; position: fixed; top: 0; left: 0;
            background: var(--sidebar-bg); color: #a0aec0;
            display: flex; flex-direction: column;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1); z-index: 1000;
        }
        .sidebar-header {
            padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;
        }
        .nav-item {
            padding: 15px 25px; color: #a0aec0; text-decoration: none;
            display: flex; align-items: center; transition: 0.3s;
            border-left: 4px solid transparent; cursor: pointer;
        }
        .nav-item:hover, .nav-item.active {
            background: var(--sidebar-hover); color: white; border-left-color: var(--royal-gold);
        }
        .nav-item i { width: 35px; font-size: 1.1rem; }
        
        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 280px; padding: 30px; }

        /* --- CHART CARDS --- */
        .chart-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px;
        }

        /* --- TABLE STYLES --- */
        .card-custom { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: none; overflow: hidden; }
        .table th { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: 700; border-bottom: 2px solid #eee; background: #fafafa; }
        .table td { vertical-align: middle; padding: 15px; }
        
        /* ACTION BUTTONS */
        .btn-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; border: none; margin-left: 5px; }
        .btn-cert { background: #fffcf0; color: #d4af37; border: 1px solid #f0e6cc; }
        .btn-cert:hover { background: #d4af37; color: white; }
        .btn-whatsapp { background: #e8fce8; color: #25D366; }
        .btn-whatsapp:hover { background: #25D366; color: white; }
        .btn-approve { background: #e6fffa; color: #2f855a; }
        .btn-approve:hover { background: #2f855a; color: white; }
        .btn-delete { background: #fff5f5; color: #c53030; }
        .btn-delete:hover { background: #c53030; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold text-white mb-0">BLOOD<span style="color: var(--royal-gold);">LINK</span></h4>
            <small style="letter-spacing: 1px; opacity: 0.6;">ADMIN PANEL v2.0</small>
        </div>
        
     <div class="mt-4">
            <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="manage_donors.php" class="nav-item active"><i class="fa-solid fa-users"></i> Donors</a>
            <a href="manage_requests.php" class="nav-item"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
            <a href="inventory.php" class="nav-item "><i class="fa-solid fa-boxes-stacked"></i> Inventory</a> 
            <a href="manage_campaigns.php" class="nav-item"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <div class="mt-4 mb-2 ps-4 text-uppercase small fw-bold" style="opacity: 0.4;">System</div>
            <a href="settings.php" class="nav-item "><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark">Donor Management</h3>
                <p class="text-muted">Analyze data, verify users, and award certificates.</p>
            </div>
        </div>

        <?php if(isset($msg)) echo "<div class='alert alert-$msg_type rounded-pill px-4'>$msg</div>"; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="chart-card d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark">Blood Group Ratio</h5>
                        <p class="text-muted small">Distribution of available blood.</p>
                    </div>
                    <div style="width: 150px; height: 150px;">
                        <canvas id="bgChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark">Verification Status</h5>
                        <p class="text-muted small">Pending vs Verified Users.</p>
                    </div>
                    <div style="width: 150px; height: 150px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="p-4 border-bottom bg-light d-flex gap-3 align-items-center">
                <h5 class="fw-bold mb-0 me-auto"><i class="fa-solid fa-list me-2 text-warning"></i>Donor Database</h5>
                
                <form class="d-flex gap-2">
                    <select name="bg" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Blood Groups</option>
                        <option value="A+">A+</option>
                        <option value="B+">B+</option>
                        <option value="O+">O+</option>
                        <option value="AB+">AB+</option>
                        <option value="A-">A-</option>
                        <option value="B-">B-</option>
                        <option value="O-">O-</option>
                    </select>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Verified</option>
                    </select>
                    <a href="manage_donors.php" class="btn btn-sm btn-outline-dark">Reset</a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>User Profile</th>
                            <th>Blood</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                             // Normalize columns
                             $name = isset($row['name']) ? $row['name'] : (isset($row['fullname']) ? $row['fullname'] : 'Unknown');
                             $phone = isset($row['phone']) ? $row['phone'] : (isset($row['contact_no']) ? $row['contact_no'] : '');
                             $city = isset($row['city']) ? $row['city'] : 'N/A';
                             $date = isset($row['registration_date']) ? $row['registration_date'] : date('Y-m-d');
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width:40px; height:40px;">
                                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo $name; ?></div>
                                        <div class="text-muted small">ID: #<?php echo $row['id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-danger rounded-pill"><?php echo $row['blood_group']; ?></span></td>
                            <td><?php echo $city; ?></td>
                            <td class="small fw-bold"><?php echo $phone; ?></td>
                            <td>
                                <?php if($row['status'] == 'Approved'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button onclick="printCertificate('<?php echo $name; ?>', '<?php echo $row['blood_group']; ?>', '<?php echo date('d M Y', strtotime($date)); ?>')" class="btn-action btn-cert" title="Award Certificate">
                                    <i class="fa-solid fa-trophy"></i>
                                </button>

                                <a href="https://wa.me/<?php echo str_replace(['+',' '], '', $phone); ?>" target="_blank" class="btn-action btn-whatsapp"><i class="fa-brands fa-whatsapp"></i></a>
                                
                                <?php if($row['status'] != 'Approved'): ?>
                                <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn-action btn-approve"><i class="fa-solid fa-check"></i></a>
                                <?php endif; ?>

                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this donor?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function printCertificate(name, bloodGroup, date) {
        var printWindow = window.open('', '', 'width=900,height=700');
        printWindow.document.write(`
            <html>
            <head>
                <title>Hero Certificate - ${name}</title>
                <style>
                    body { font-family: 'Times New Roman', serif; text-align: center; padding: 40px; background: #fffdf5; border: 20px solid #7a0000; }
                    .border-inner { border: 5px solid #d4af37; padding: 40px; height: 85%; position: relative; }
                    h1 { font-size: 50px; color: #7a0000; text-transform: uppercase; margin-bottom: 10px; }
                    h3 { font-size: 25px; color: #555; margin-top: 0; }
                    .name { font-size: 60px; font-weight: bold; color: #d4af37; margin: 30px 0; font-style: italic; border-bottom: 2px solid #ddd; display: inline-block; padding: 0 50px; }
                    .content { font-size: 20px; color: #333; line-height: 1.6; max-width: 700px; margin: 0 auto; }
                    .badge { margin-top: 40px; width: 100px; }
                    .footer { margin-top: 60px; display: flex; justify-content: space-between; padding: 0 100px; }
                    .sig-line { border-top: 2px solid #333; width: 200px; padding-top: 10px; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class="border-inner">
                    <h1>Certificate of Heroism</h1>
                    <h3>Awarded To</h3>
                    <div class="name">${name}</div>
                    <div class="content">
                        For your selfless commitment to saving lives. Your registration as a 
                        <strong>${bloodGroup}</strong> donor on <strong>${date}</strong> makes you a vital part of our lifesaving network.
                        <br><br>
                        "The gift of blood is the gift of life."
                    </div>
                    <br>
                    <img src="https://cdn-icons-png.flaticon.com/512/2913/2913004.png" class="badge">
                    <div class="footer">
                        <div class="sig-line">BloodLink Director</div>
                        <div class="sig-line">Date Issued</div>
                    </div>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
    
    // CHART JS INITIALIZATION
    const ctx1 = document.getElementById('bgChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($bg_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($bg_data); ?>,
                backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#2ecc71', '#9b59b6', '#34495e'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($st_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($st_data); ?>,
                backgroundColor: ['#2ecc71', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });
    </script>

</body>
</html>