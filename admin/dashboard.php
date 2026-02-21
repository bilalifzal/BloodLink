<?php
session_start();
include '../includes/db_connect.php';

// SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// --- FETCH SYSTEM DATA ---
$donors_total = $conn->query("SELECT id FROM donors")->num_rows;
$donors_pending = $conn->query("SELECT id FROM donors WHERE status='Pending'")->num_rows;
$requests_total = $conn->query("SELECT id FROM blood_requests")->num_rows;
$active_requests = $conn->query("SELECT id FROM blood_requests WHERE status='Pending'")->num_rows;
$campaigns_total = $conn->query("SELECT id FROM campaigns")->num_rows;

// Fetch Recent Donors for Table
$recent_donors_sql = "SELECT * FROM donors ORDER BY id DESC LIMIT 5";
$recent_donors = $conn->query($recent_donors_sql);

// Fetch Blood Group Stats for Charts
$bg_stats = $conn->query("SELECT blood_group, COUNT(*) as count FROM donors GROUP BY blood_group");
$bg_labels = [];
$bg_data = [];
while($row = $bg_stats->fetch_assoc()) {
    $bg_labels[] = $row['blood_group'];
    $bg_data[] = $row['count'];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Command Center | BloodLink Pro</title>
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
            padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: center;
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
        
        /* --- TOP HEADER --- */
        .top-header {
            background: white; padding: 20px 30px; border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .admin-profile { display: flex; align-items: center; gap: 15px; }
        .admin-avatar {
            width: 50px; height: 50px; background: var(--royal-red); color: white;
            border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;
            border: 3px solid #f3f4f6; box-shadow: 0 0 10px rgba(128,0,0,0.3);
        }

        /* --- STAT CARDS --- */
        .stat-card {
            background: white; border-radius: 15px; padding: 30px; position: relative; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05); transition: 0.3s; border: none; h-100;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .stat-icon {
            position: absolute; top: 20px; right: 20px; font-size: 3rem; opacity: 0.1;
        }
        .stat-val { font-size: 2.5rem; font-weight: 800; color: #2d3748; margin-bottom: 5px; }
        .stat-label { text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; color: #718096; font-weight: 700; }

        /* --- QUICK ACTIONS --- */
        .action-card {
            background: white; padding: 25px; text-align: center; border-radius: 15px;
            border: 1px solid #eee; transition: 0.3s; cursor: pointer; text-decoration: none; color: inherit; display: block;
        }
        .action-card:hover { border-color: var(--royal-gold); background: #fffdf5; transform: translateY(-3px); }
        .action-icon {
            width: 60px; height: 60px; background: #f7fafc; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;
            font-size: 1.5rem; color: var(--royal-red); transition: 0.3s;
        }
        .action-card:hover .action-icon { background: var(--royal-red); color: white; }

        /* --- CHARTS & TABLES --- */
        .content-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: 100%;
        }
        .card-title { font-weight: 700; color: #2d3748; margin-bottom: 20px; font-size: 1.1rem; }
        
        .table-custom th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; color: #a0aec0; font-weight: 700; border-bottom: 2px solid #eee; padding-bottom: 15px; }
        .table-custom td { padding: 15px 0; vertical-align: middle; border-bottom: 1px solid #f3f4f6; color: #4a5568; font-weight: 500; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-pending { background: #fff5f5; color: #c53030; }
        .badge-approved { background: #f0fff4; color: #2f855a; }
    </style>
</head>
<body>
    

    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold text-white mb-0">BLOOD<span style="color: var(--royal-gold);">LINK</span></h4>
            <small style="letter-spacing: 1px; opacity: 0.6;">ADMIN PANEL v2.0</small>
        </div>
        
       <div class="mt-4">
            <a href="dashboard.php" class="nav-item active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="manage_donors.php" class="nav-item"><i class="fa-solid fa-users"></i> Donors</a>
            <a href="manage_requests.php" class="nav-item"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
   <a href="inventory.php" class="nav-item "><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>            <a href="manage_campaigns.php" class="nav-item"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <div class="mt-4 mb-2 ps-4 text-uppercase small fw-bold" style="opacity: 0.4;">System</div>
            <a href="settings.php" class="nav-item "><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="top-header">
            <div>
                <h4 class="fw-bold mb-0">Dashboard Overview</h4>
                <small class="text-muted">Last Login: <?php echo date('d M Y, h:i A'); ?></small>
            </div>
            <div class="admin-profile">
                <div class="text-end d-none d-md-block">
                    <h6 class="fw-bold mb-0 text-dark">Muhammad Bilal Ifzal</h6>
                    <small class="text-muted text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Super Administrator</small>
                </div>
                <div class="admin-avatar">MB</div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card border-start border-4 border-danger">
                    <i class="fa-solid fa-users stat-icon text-danger"></i>
                    <div class="stat-val"><?php echo $donors_total; ?></div>
                    <div class="stat-label">Total Donors</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-start border-4 border-warning">
                    <i class="fa-solid fa-hourglass-half stat-icon text-warning"></i>
                    <div class="stat-val"><?php echo $active_requests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-start border-4 border-success">
                    <i class="fa-solid fa-flag stat-icon text-success"></i>
                    <div class="stat-val"><?php echo $campaigns_total; ?></div>
                    <div class="stat-label">Active Campaigns</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card border-start border-4 border-primary">
                    <i class="fa-solid fa-server stat-icon text-primary"></i>
                    <div class="stat-val text-success" style="font-size: 1.5rem; line-height: 2.5rem;">Online</div>
                    <div class="stat-label">System Status</div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3">Quick Actions</h5>
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <a href="manage_donors.php" class="action-card">
                    <div class="action-icon"><i class="fa-solid fa-user-check"></i></div>
                    <h6 class="fw-bold mb-0">Verify Donors</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_campaigns.php" class="action-card">
                    <div class="action-icon"><i class="fa-solid fa-plus"></i></div>
                    <h6 class="fw-bold mb-0">Add Campaign</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="manage_requests.php" class="action-card">
                    <div class="action-icon"><i class="fa-solid fa-list-check"></i></div>
                    <h6 class="fw-bold mb-0">Manage Requests</h6>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../index.php" target="_blank" class="action-card">
                    <div class="action-icon"><i class="fa-solid fa-globe"></i></div>
                    <h6 class="fw-bold mb-0">View Website</h6>
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Donation Analytics</h5>
                        <select class="form-select form-select-sm w-auto border-0 bg-light fw-bold">
                            <option>This Month</option>
                            <option>Last 6 Months</option>
                        </select>
                    </div>
                    <canvas id="donationChart" height="150"></canvas>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="content-card">
                    <h5 class="card-title">Blood Group Stocks</h5>
                    <canvas id="bloodPieChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="content-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0">Recent Registrations</h5>
                <a href="manage_donors.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>City</th>
                            <th>Blood Group</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $recent_donors->fetch_assoc()): 
                            // --- FIX: Check correct column names ---
                            $name = isset($row['name']) ? $row['name'] : (isset($row['fullname']) ? $row['fullname'] : 'Unknown');
                            $date = isset($row['registration_date']) ? $row['registration_date'] : (isset($row['created_at']) ? $row['created_at'] : '');
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold text-muted" style="width:35px; height:35px; font-size:0.8rem;">
                                        <?php echo strtoupper(substr($name, 0, 1)); ?>
                                    </div>
                                    <?php echo $name; ?>
                                </div>
                            </td>
                            <td><?php echo $row['city']; ?></td>
                            <td><span class="badge bg-danger"><?php echo $row['blood_group']; ?></span></td>
                            <td class="text-muted small">
                                <?php echo ($date) ? date('d M, Y', strtotime($date)) : 'N/A'; ?>
                            </td>
                            <td>
                                <?php if($row['status'] == 'Approved'): ?>
                                    <span class="status-badge badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="status-badge badge-pending">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="manage_donors.php" class="btn btn-sm btn-light text-primary"><i class="fa-solid fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <script>
        // 1. LINE CHART
        const ctx1 = document.getElementById('donationChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'New Donors',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: '#800000',
                    backgroundColor: 'rgba(128, 0, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // 2. PIE CHART
        const ctx2 = document.getElementById('bloodPieChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($bg_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($bg_data); ?>,
                    backgroundColor: [
                        '#e74c3c', '#3498db', '#f1c40f', '#2ecc71', '#9b59b6', '#34495e', '#e67e22', '#95a5a6'
                    ],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, cutout: '70%' }
        });
    </script>

</body>
</html>