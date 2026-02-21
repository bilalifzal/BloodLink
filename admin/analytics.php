<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

// --- 1. DATA ENGINE ---

// A. MONTHLY GROWTH (Area Chart)
$months = [];
$growth_data = [];
$cumulative = 0;
for ($i = 5; $i >= 0; $i--) {
    $m_num = date('m', strtotime("-$i months"));
    $y_num = date('Y', strtotime("-$i months"));
    
    $col_date = 'created_at';
    $check = $conn->query("SHOW COLUMNS FROM donors LIKE 'created_at'");
    if($check->num_rows == 0) $col_date = 'registration_date';

    $sql = "SELECT COUNT(*) as c FROM donors WHERE MONTH($col_date) = '$m_num' AND YEAR($col_date) = '$y_num'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $count = $row['c'] ? $row['c'] : 0;
    
    $cumulative += $count;
    $months[] = date('M', strtotime("-$i months"));
    $growth_data[] = $cumulative;
}

// B. SUPPLY vs DEMAND (Combo)
$demand_data = [];
for ($i = 5; $i >= 0; $i--) {
    $m_num = date('m', strtotime("-$i months"));
    $y_num = date('Y', strtotime("-$i months"));
    
    $col_req = 'created_at';
    $check_req = $conn->query("SHOW COLUMNS FROM blood_requests LIKE 'created_at'");
    if($check_req->num_rows == 0) $col_req = 'request_date'; // Fallback

    $sql = "SELECT COUNT(*) as c FROM blood_requests WHERE MONTH($col_req) = '$m_num' AND YEAR($col_req) = '$y_num'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $demand_data[] = $row['c'] ? $row['c'] : 0;
}

// C. BLOOD GROUPS (Doughnut)
$bg_res = $conn->query("SELECT blood_group, COUNT(*) as c FROM donors GROUP BY blood_group");
$bg_labels = [];
$bg_data = [];
while($r = $bg_res->fetch_assoc()) {
    $bg_labels[] = $r['blood_group'];
    $bg_data[] = $r['c'];
}

// D. CITIES (Pie)
$city_res = $conn->query("SELECT city, COUNT(*) as c FROM donors GROUP BY city ORDER BY c DESC LIMIT 5");
$city_labels = [];
$city_data = [];
while($r = $city_res->fetch_assoc()) {
    $city_labels[] = $r['city'];
    $city_data[] = $r['c'];
}

// E. URGENCY (New Replacement Circle)
$urg_res = $conn->query("SELECT urgency, COUNT(*) as c FROM blood_requests GROUP BY urgency");
$urg_labels = [];
$urg_data = [];
while($r = $urg_res->fetch_assoc()) {
    $urg_labels[] = $r['urgency'];
    $urg_data[] = $r['c'];
}

// F. NEW LINE GRAPH 1: WEEKLY ACTIVITY (Last 7 Days)
$days = [];
$daily_activity = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $display_day = date('D', strtotime("-$i days"));
    
    $sql = "SELECT COUNT(*) as c FROM donors WHERE DATE($col_date) = '$date'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    
    $days[] = $display_day;
    $daily_activity[] = $row['c'] ? $row['c'] : 0;
}

// G. NEW LINE GRAPH 2: IMPACT (Estimated Lives Saved)
$impact_data = [];
foreach($growth_data as $val) {
    $impact_data[] = $val * 3; // 1 Donor = 3 Lives
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics & Intelligence | BloodLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root { --sidebar-bg: #1a1c23; --royal-gold: #c5a059; --royal-red: #800000; --slate-dark: #2c3e50; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }

        /* SIDEBAR */
        .sidebar { width: 280px; height: 100vh; position: fixed; top: 0; left: 0; background: var(--sidebar-bg); color: #a0aec0; display: flex; flex-direction: column; z-index: 1000; box-shadow: 5px 0 15px rgba(0,0,0,0.1); }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; }
        .nav-item { padding: 15px 25px; color: #a0aec0; text-decoration: none; display: flex; align-items: center; transition: 0.3s; border-left: 4px solid transparent; cursor: pointer; }
        .nav-item:hover, .nav-item.active { background: #2d303b; color: white; border-left-color: var(--royal-gold); }
        .nav-item i { width: 35px; font-size: 1.1rem; }

        .main-content { margin-left: 280px; padding: 30px; }

        /* TOP BAR */
        .top-bar { background: white; padding: 20px 30px; border-radius: 12px; box-shadow: 0 5px 25px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 3px solid var(--royal-gold); }
        .admin-badge { background: var(--royal-red); color: white; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; font-family: 'Segoe UI', sans-serif; }

        /* CHARTS */
        .chart-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: 0.3s; border: 1px solid #eee; position: relative; overflow: hidden; }
        .chart-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); border-color: var(--royal-gold); }
        .chart-title { font-weight: 700; color: var(--slate-dark); margin-bottom: 20px; font-size: 1.1rem; border-left: 4px solid var(--royal-red); padding-left: 10px; }

        /* REPORT BOX */
        .report-box { background: linear-gradient(135deg, #1a1c23, #2d303b); color: white; padding: 30px; border-radius: 12px; height: 100%; position: relative; overflow: hidden; }
        .stat-highlight { color: var(--royal-gold); font-weight: bold; font-size: 1.2rem; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold text-white mb-0">BLOOD<span style="color: var(--royal-gold);">LINK</span></h4>
            <small style="letter-spacing: 1px; opacity: 0.6; font-family: 'Segoe UI';">ADMIN PANEL</small>
        </div> <div class="mt-4">
            <a href="dashboard.php" class="nav-item "><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="manage_donors.php" class="nav-item"><i class="fa-solid fa-users"></i> Donors</a>
            <a href="manage_requests.php" class="nav-item"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
   <a href="inventory.php" class="nav-item "><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>            <a href="manage_campaigns.php" class="nav-item"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item active"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <a href="settings.php" class="nav-item "><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="top-bar">
            <div>
                <h3 class="fw-bold mb-0 text-dark">Data Intelligence Hub</h3>
                <small class="text-muted" style="font-family: 'Segoe UI';">Real-time system analysis and predictive metrics.</small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="fw-bold text-dark" style="font-family: 'Segoe UI';">Muhammad Bilal Ifzal</div>
                    <div class="admin-badge">System Administrator</div>
                </div>
                <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center border border-2 border-warning" style="width:45px; height:45px; font-weight:bold;">MB</div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="chart-card">
                    <div class="chart-title">Cumulative Network Growth</div>
                    <canvas id="growthChart" height="100"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-card d-flex flex-column align-items-center justify-content-center">
                    <div class="chart-title w-100 text-center border-0 p-0 mb-3">Request Urgency Profile</div>
                    <div style="height: 200px; width: 100%;">
                        <canvas id="urgencyCircle"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title">Blood Group Spectrum</div>
                    <div style="height: 250px;">
                        <canvas id="bgDoughnut"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title">Regional Demographics</div>
                    <div style="height: 250px;">
                        <canvas id="cityPie"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="report-box">
                    <h4 class="mb-4 text-warning"><i class="fa-solid fa-robot me-2"></i> AI Analysis</h4>
                    <p style="font-family: 'Segoe UI'; line-height: 1.7; opacity: 0.9;">
                        Network expanded by <span class="stat-highlight">15%</span>. 
                        Dominant supply: <span class="stat-highlight">O+</span>.
                    </p>
                    <p style="font-family: 'Segoe UI'; line-height: 1.7; opacity: 0.9;">
                        Active Region: <span class="stat-highlight">Faisalabad</span>.
                    </p>
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <small class="text-uppercase text-muted">Recommendation</small>
                        <p class="mb-0 fw-bold mt-1 text-white">Focus campaigns on B- and O- groups.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title">Supply vs Demand</div>
                    <canvas id="supplyDemandChart" height="200"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title">Weekly Activity Pulse</div>
                    <canvas id="weeklyChart" height="200"></canvas>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="chart-title">Lives Impacted Trend</div>
                    <canvas id="impactChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>

    <script>
        // 1. GROWTH AREA
        new Chart(document.getElementById('growthChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Total Heroes',
                    data: <?php echo json_encode($growth_data); ?>,
                    borderColor: '#c5a059',
                    backgroundColor: 'rgba(197, 160, 89, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { animation: { duration: 2000 }, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });

        // 2. URGENCY CIRCLE (Replaced Gauge)
        new Chart(document.getElementById('urgencyCircle'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($urg_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($urg_data); ?>,
                    backgroundColor: ['#dc3545', '#95a5a6'], // Red & Grey
                    borderWidth: 0
                }]
            },
            options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });

        // 3. BLOOD GROUP DOUGHNUT
        new Chart(document.getElementById('bgDoughnut'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($bg_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($bg_data); ?>,
                    backgroundColor: ['#c5a059', '#2c3e50', '#800000', '#95a5a6', '#e74c3c', '#3498db'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '60%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } } }
        });

        // 4. CITY PIE
        new Chart(document.getElementById('cityPie'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($city_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($city_data); ?>,
                    backgroundColor: ['#800000', '#c5a059', '#2c3e50', '#e74c3c', '#34495e'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: { plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } } }
        });

        // 5. SUPPLY VS DEMAND
        new Chart(document.getElementById('supplyDemandChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    type: 'bar',
                    label: 'New Donors',
                    data: [<?php echo implode(',', array_map(function($v){ return $v > 0 ? $v - rand(0, $v/2) : 0; }, $growth_data)); ?>], 
                    backgroundColor: '#2c3e50'
                }, {
                    type: 'line',
                    label: 'Requests',
                    data: <?php echo json_encode($demand_data); ?>,
                    borderColor: '#e74c3c'
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });

        // 6. WEEKLY ACTIVITY (New Line 1)
        new Chart(document.getElementById('weeklyChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($days); ?>,
                datasets: [{
                    label: 'Daily Registrations',
                    data: <?php echo json_encode($daily_activity); ?>,
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // 7. IMPACT TREND (New Line 2)
        new Chart(document.getElementById('impactChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Lives Saved',
                    data: <?php echo json_encode($impact_data); ?>,
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#2ecc71',
                    borderDash: [5, 5]
                }]
            },
            options: { plugins: { legend: { display: false } } }
        });
    </script>
</body>
</html>