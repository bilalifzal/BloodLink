<?php
session_start();
include '../includes/db_connect.php';

if (!isset($_SESSION['admin_id'])) { header("Location: login.php"); exit(); }

$msg = "";

// --- MAINTENANCE ACTIONS ---
if (isset($_POST['purge_expired'])) {
    $conn->query("DELETE FROM donation_history WHERE status = 'Expired'");
    $msg = "All expired units purged from database.";
}

if (isset($_POST['reset_stats'])) {
    // Logic to archive old fulfilled requests
    $conn->query("UPDATE blood_requests SET status = 'Archived' WHERE status = 'Completed' AND updated_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $msg = "Old completed requests archived.";
}

// Fetch Logs
$logs = $conn->query("SELECT l.*, a.username FROM admin_logs l JOIN admins a ON l.admin_id = a.id ORDER BY l.created_at DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Maintenance | BloodLink Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-bg: #1a1c23; --royal-red: #800000; }
        body { background-color: #f8f9fa; display: flex; }
        .sidebar { width: 280px; height: 100vh; background: var(--sidebar-bg); color: #fff; position: fixed; }
        .main { margin-left: 280px; padding: 40px; width: 100%; }
        .log-card { border-left: 4px solid var(--royal-red); background: white; margin-bottom: 10px; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar p-4">
    <h4 class="fw-bold mb-4">BLOODLINK <span class="text-warning">PRO</span></h4>
    <hr>
    <a href="dashboard.php" class="text-white text-decoration-none d-block mb-3"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a>
    <a href="inventory.php" class="text-white text-decoration-none d-block mb-3"><i class="fa-solid fa-boxes-stacked me-2"></i> Inventory</a>
    <a href="maintenance.php" class="text-warning text-decoration-none d-block mb-3 fw-bold"><i class="fa-solid fa-screwdriver-wrench me-2"></i> Maintenance</a>
</div>

<div class="main">
    <h3 class="fw-bold mb-4">System Maintenance & Logs</h3>
    
    <?php if($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-hammer me-2 text-danger"></i> Database Tools</h5>
                <form method="POST">
                    <button type="submit" name="purge_expired" class="btn btn-outline-danger w-100 mb-3 text-start">
                        <i class="fa-solid fa-trash-can me-2"></i> Purge Expired Units
                    </button>
                    <button type="submit" name="reset_stats" class="btn btn-outline-dark w-100 text-start">
                        <i class="fa-solid fa-box-archive me-2"></i> Archive Old Requests
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 rounded-4">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recent Admin Activity</h5>
                <div class="log-container">
                    <?php while($row = $logs->fetch_assoc()): ?>
                    <div class="log-card d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-light text-dark border me-2"><?php echo $row['action_type']; ?></span>
                            <span class="fw-bold"><?php echo $row['username']; ?></span>
                            <small class="text-muted ms-2"><?php echo $row['details']; ?></small>
                        </div>
                        <div class="small text-muted"><?php echo date('H:i', strtotime($row['created_at'])); ?></div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>