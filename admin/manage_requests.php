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
    
    // --- NEW: APPROVE ACTION ---
    if ($_GET['action'] == 'approve') {
        $conn->query("UPDATE blood_requests SET status='Approved' WHERE id='$id'");
        $msg = "Request Verified & Approved!";
        $msg_type = "info";
    }

    // FULFILL ACTION (Mark as Completed)
    if ($_GET['action'] == 'fulfill') {
        $conn->query("UPDATE blood_requests SET status='Completed' WHERE id='$id'");
        $msg = "Request marked as Fulfilled!";
        $msg_type = "success";
    }
    
    // DELETE ACTION
    if ($_GET['action'] == 'delete') {
        $conn->query("DELETE FROM blood_requests WHERE id='$id'");
        $msg = "Request Deleted Successfully!";
        $msg_type = "danger";
    }
}

// 3. FETCH CHART DATA (Live Analysis)
// Chart A: Urgency (Include Approved in count)
$urgency_query = $conn->query("SELECT urgency, COUNT(*) as count FROM blood_requests WHERE status IN ('Pending', 'Approved') GROUP BY urgency");
$u_labels = [];
$u_data = [];
while($row = $urgency_query->fetch_assoc()) {
    $u_labels[] = $row['urgency'];
    $u_data[] = $row['count'];
}

// Chart B: Status
$status_query = $conn->query("SELECT status, COUNT(*) as count FROM blood_requests GROUP BY status");
$s_labels = [];
$s_data = [];
while($row = $status_query->fetch_assoc()) {
    $s_labels[] = $row['status'];
    $s_data[] = $row['count'];
}

// 4. STATS
$total_req = $conn->query("SELECT id FROM blood_requests")->num_rows;
$critical_req = $conn->query("SELECT id FROM blood_requests WHERE urgency='Critical' AND status IN ('Pending', 'Approved')")->num_rows;
$active_req = $conn->query("SELECT id FROM blood_requests WHERE status IN ('Pending', 'Approved')")->num_rows;

// 5. FETCH TABLE DATA
$filter_urgency = isset($_GET['urgency']) ? $_GET['urgency'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM blood_requests WHERE 1=1";
if($filter_urgency) $sql .= " AND urgency = '$filter_urgency'";
if($filter_status) $sql .= " AND status = '$filter_status'";

// Sort: Pending/Approved first, then by urgency
$sql .= " ORDER BY CASE WHEN status = 'Pending' THEN 1 WHEN status = 'Approved' THEN 2 ELSE 3 END, urgency DESC, created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Management | BloodLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --sidebar-bg: #1a1c23;
            --sidebar-hover: #2d303b;
            --royal-gold: #c5a059;
            --royal-red: #800000;
        }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }

        /* --- SIDEBAR --- */
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
        
        .main-content { margin-left: 280px; padding: 30px; }

        /* --- CARDS & CHARTS --- */
        .chart-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; height: 100%;
        }
        .stat-card {
            background: white; border-radius: 12px; padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-left: 5px solid #ddd; position: relative; overflow: hidden;
        }
        .border-critical { border-left-color: #dc3545; }
        .border-total { border-left-color: #0d6efd; }

        /* --- TABLE STYLING --- */
        .card-custom { background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border: none; overflow: hidden; }
        .table th { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: 700; border-bottom: 2px solid #eee; background: #fafafa; }
        .table td { vertical-align: middle; padding: 15px; }

        /* PULSE ANIMATION FOR CRITICAL */
        @keyframes pulse-red {
            0% { box-shadow: inset 4px 0 0 0 rgba(220, 53, 69, 0.8); background: rgba(220, 53, 69, 0.05); }
            50% { box-shadow: inset 4px 0 0 0 rgba(220, 53, 69, 0.2); background: transparent; }
            100% { box-shadow: inset 4px 0 0 0 rgba(220, 53, 69, 0.8); background: rgba(220, 53, 69, 0.05); }
        }
        .is-critical { animation: pulse-red 2s infinite; }

        /* ACTIONS */
        .btn-action { width: 35px; height: 35px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; border: none; margin-left: 5px; }
        .btn-call { background: #e0f2fe; color: #0284c7; }
        .btn-call:hover { background: #0284c7; color: white; }
        
        /* NEW APPROVE BUTTON STYLE */
        .btn-approve { background: #e0f7fa; color: #00bcd4; }
        .btn-approve:hover { background: #00bcd4; color: white; }
        
        /* FULFILL BUTTON STYLE */
        .btn-check { background: #f0fdf4; color: #16a34a; }
        .btn-check:hover { background: #16a34a; color: white; }
        
        .btn-trash { background: #fef2f2; color: #dc2626; }
        .btn-trash:hover { background: #dc2626; color: white; }
        .btn-report { background: #fffcf0; color: #d4af37; border: 1px solid #f0e6cc; }
        .btn-report:hover { background: #d4af37; color: white; }
        
        .live-ticker { background: #2c3e50; color: white; font-size: 0.8rem; padding: 8px 15px; border-radius: 50px; display: inline-block; margin-bottom: 20px; }
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
            <a href="manage_requests.php" class="nav-item active"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
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
                <h3 class="fw-bold text-dark">Request Center</h3>
                <p class="text-muted">Live patient data and emergency response.</p>
            </div>
            <div class="live-ticker">
                <i class="fa-solid fa-circle text-danger me-2 fa-beat-fade"></i> Live System: Monitoring <?php echo $total_req; ?> Requests
            </div>
        </div>

        <?php if(isset($msg)) echo "<div class='alert alert-$msg_type rounded-pill px-4'>$msg</div>"; ?>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card border-total mb-3">
                    <h6 class="text-primary text-uppercase small fw-bold">Total Volume</h6>
                    <h2 class="fw-bold mb-0"><?php echo $total_req; ?></h2>
                </div>
                <div class="stat-card border-critical">
                    <h6 class="text-danger text-uppercase small fw-bold">Active Critical</h6>
                    <h2 class="fw-bold mb-0"><?php echo $critical_req; ?></h2>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="chart-card d-flex flex-column align-items-center justify-content-center">
                    <h6 class="fw-bold text-dark mb-3">Urgency Distribution</h6>
                    <div style="height: 150px; width: 100%;">
                        <canvas id="urgencyChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="chart-card d-flex flex-column align-items-center justify-content-center">
                    <h6 class="fw-bold text-dark mb-3">Completion Rate</h6>
                    <div style="height: 150px; width: 100%;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-custom">
            <div class="p-4 border-bottom bg-light d-flex gap-3 align-items-center flex-wrap">
                <h5 class="fw-bold mb-0 me-auto"><i class="fa-solid fa-list-check me-2 text-danger"></i>Patient Database</h5>
                
                <form class="d-flex gap-2">
                    <select name="urgency" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Urgency</option>
                        <option value="Critical">Critical Only</option>
                        <option value="Normal">Normal</option>
                    </select>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Approved">Verified (Active)</option>
                        <option value="Completed">Fulfilled</option>
                    </select>
                    <a href="manage_requests.php" class="btn btn-sm btn-outline-dark">Reset</a>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Patient Info</th>
                            <th>Requirement</th>
                            <th>Location / Urgency</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="<?php echo ($row['urgency'] == 'Critical' && $row['status'] != 'Completed') ? 'is-critical' : ''; ?>">
                            <td>
                                <div class="fw-bold text-dark"><?php echo $row['patient_name']; ?></div>
                                <div class="small text-muted">Reason: <?php echo substr($row['reason'], 0, 30); ?>...</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger rounded-circle p-2 me-2" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><?php echo $row['blood_group']; ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold small"><?php echo $row['hospital_name']; ?></div>
                                <div class="d-flex gap-2 mt-1">
                                    <span class="badge bg-light text-dark border"><?php echo $row['city']; ?></span>
                                    <?php if($row['urgency'] == 'Critical'): ?>
                                        <span class="badge bg-danger">CRITICAL</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Completed'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Fulfilled</span>
                                <?php elseif($row['status'] == 'Approved'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button onclick="generateCaseReport('<?php echo $row['patient_name']; ?>', '<?php echo $row['blood_group']; ?>', '<?php echo $row['hospital_name']; ?>', '<?php echo $row['city']; ?>', '<?php echo $row['reason']; ?>', '<?php echo $row['contact_number']; ?>', '<?php echo $row['urgency']; ?>')" class="btn-action btn-report" title="Generate Case Report">
                                    <i class="fa-solid fa-file-prescription"></i>
                                </button>

                                <a href="tel:<?php echo $row['contact_number']; ?>" class="btn-action btn-call" title="Call Contact"><i class="fa-solid fa-phone"></i></a>

                                <?php if($row['status'] == 'Pending'): ?>
                                    <a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn-action btn-approve" title="Verify & Approve"><i class="fa-solid fa-check"></i></a>
                                <?php elseif($row['status'] == 'Approved'): ?>
                                    <a href="?action=fulfill&id=<?php echo $row['id']; ?>" class="btn-action btn-check" title="Mark as Fulfilled"><i class="fa-solid fa-check-double"></i></a>
                                <?php endif; ?>

                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn-action btn-trash" title="Delete" onclick="return confirm('Delete record?');"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
    function generateCaseReport(name, bg, hospital, city, reason, contact, urgency) {
        var printWindow = window.open('', '', 'width=800,height=800');
        var color = urgency === 'Critical' ? '#800000' : '#2c3e50';
        var urgencyBadge = urgency === 'Critical' ? '⚠️ CRITICAL EMERGENCY' : 'STANDARD REQUEST';
        
        printWindow.document.write(`
            <html>
            <head>
                <title>Case Report - ${name}</title>
                <style>
                    body { font-family: 'Arial', sans-serif; padding: 40px; background: #fff; }
                    .header { border-bottom: 4px solid ${color}; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
                    .brand { font-size: 24px; font-weight: bold; color: ${color}; }
                    .title { font-size: 30px; font-weight: 900; text-transform: uppercase; color: #333; margin-bottom: 10px; }
                    .badge { background: ${color}; color: white; padding: 5px 15px; font-weight: bold; display: inline-block; margin-bottom: 30px; }
                    .row { display: flex; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
                    .label { width: 150px; font-weight: bold; color: #555; }
                    .val { font-weight: bold; font-size: 18px; }
                    .blood-group { font-size: 60px; font-weight: bold; color: ${color}; border: 3px solid ${color}; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: absolute; top: 160px; right: 50px; }
                    .footer { margin-top: 50px; font-size: 12px; color: #888; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="brand">BLOODLINK PRO</div>
                    <div>${new Date().toLocaleDateString()}</div>
                </div>
                
                <div class="title">Official Blood Request</div>
                <div class="badge">${urgencyBadge}</div>
                <div class="blood-group">${bg}</div>

                <div class="row"><div class="label">Patient Name:</div><div class="val">${name}</div></div>
                <div class="row"><div class="label">Required Type:</div><div class="val" style="color:${color}">${bg}</div></div>
                <div class="row"><div class="label">Hospital:</div><div class="val">${hospital}, ${city}</div></div>
                <div class="row"><div class="label">Medical Reason:</div><div class="val">${reason}</div></div>
                <div class="row"><div class="label">Contact Person:</div><div class="val">${contact}</div></div>

                <br>
                <p><strong>Note to Donors:</strong> Please verify the patient name at the hospital reception before donation. This document is system generated.</p>

                <div class="footer">
                    Authorized by BloodLink Administrator • ID: <?php echo $_SESSION['admin_id']; ?>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }

    const ctx1 = document.getElementById('urgencyChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($u_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($u_data); ?>,
                backgroundColor: ['#dc3545', '#6c757d'], 
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    const ctx2 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($s_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($s_data); ?>,
                backgroundColor: ['#f1c40f', '#0d6efd', '#2ecc71'], 
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    </script>

</body>
</html>