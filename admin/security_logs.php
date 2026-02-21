<?php
session_start();
include '../includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. FETCH LOGS
$sql = "SELECT * FROM security_logs ORDER BY id DESC LIMIT 50";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Audit | BloodLink Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root { --sidebar-bg: #1a1c23; --sidebar-hover: #2d303b; --royal-gold: #c5a059; --royal-red: #800000; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; }

        /* SIDEBAR (Standard) */
        .sidebar { width: 280px; height: 100vh; position: fixed; top: 0; left: 0; background: var(--sidebar-bg); color: #a0aec0; display: flex; flex-direction: column; z-index: 1000; }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; }
        .nav-item { padding: 15px 25px; color: #a0aec0; text-decoration: none; display: flex; align-items: center; transition: 0.3s; border-left: 4px solid transparent; cursor: pointer; }
        .nav-item:hover, .nav-item.active { background: var(--sidebar-hover); color: white; border-left-color: var(--royal-gold); }
        .nav-item i { width: 35px; font-size: 1.1rem; }
        
        .main-content { margin-left: 280px; padding: 30px; }

        /* LOG TERMINAL STYLE */
        .log-card {
            background: #1e1e1e; color: #00ff00; font-family: 'Courier New', monospace;
            border-radius: 10px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            height: 80vh; overflow-y: auto; border: 1px solid #333;
        }
        .log-entry { border-bottom: 1px solid #333; padding: 10px 0; display: flex; justify-content: space-between; }
        .log-entry:hover { background: #2a2a2a; }
        .text-timestamp { color: #888; font-size: 0.85rem; }
        .event-danger { color: #ff4444; }
        .event-success { color: #00ff00; }
        .event-info { color: #00ccff; }
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
                <h3 class="fw-bold text-dark">Security Audit Log</h3>
                <p class="text-muted">Tracking system access and anomalies.</p>
            </div>
            <button onclick="window.print()" class="btn btn-dark"><i class="fa-solid fa-print me-2"></i>Export Logs</button>
        </div>

        <div class="log-card">
            <div class="mb-3 text-muted small">root@bloodlink-server:~$ tail -f /var/log/auth.log</div>
            
            <?php while($row = $result->fetch_assoc()): 
                $color = 'event-info';
                if(strpos($row['event'], 'Failed') !== false) $color = 'event-danger';
                if(strpos($row['event'], 'Success') !== false) $color = 'event-success';
            ?>
            <div class="log-entry">
                <div>
                    <span class="text-timestamp">[<?php echo $row['timestamp']; ?>]</span> 
                    <span class="<?php echo $color; ?> fw-bold ms-2"><?php echo $row['event']; ?></span>
                </div>
                <div class="text-muted small">IP: <?php echo $row['ip_address']; ?></div>
            </div>
            <?php endwhile; ?>
            
            <div class="mt-3 text-muted small blink">_</div>
        </div>
    </div>

</body>
</html>