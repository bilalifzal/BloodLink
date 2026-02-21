<?php
session_start();
include '../includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// 2. HANDLE ACTIONS
if (isset($_POST['add_campaign'])) {
    $title = $_POST['title'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $org = $_POST['organizer'];
    $desc = $_POST['description'];
    
    // In a real app, you would handle image uploads here. For now, we use a random medical image.
    $sql = "INSERT INTO campaigns (title, location, event_date, start_time, organizer, description) 
            VALUES ('$title', '$location', '$date', '$time', '$org', '$desc')";
            
    if ($conn->query($sql)) {
        $msg = "Campaign Created Successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error: " . $conn->error;
        $msg_type = "danger";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    $conn->query("DELETE FROM campaigns WHERE id='$id'");
    $msg = "Campaign Deleted!";
    $msg_type = "warning";
}

// 3. FETCH CAMPAIGNS
$result = $conn->query("SELECT * FROM campaigns ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Campaigns | BloodLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
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

        /* --- FORM CARD --- */
        .card-custom {
            background: white; border-radius: 15px; padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%;
        }
        .form-label { font-size: 0.8rem; text-transform: uppercase; font-weight: 700; color: #888; }
        .form-control:focus { border-color: var(--royal-gold); box-shadow: 0 0 10px rgba(197, 160, 89, 0.2); }

        /* --- PREVIEW CARD (MATCHES WEBSITE STYLE) --- */
        .camp-card-preview {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); position: relative;
        }
        .camp-img {
            height: 180px; background: #ddd; display: flex; align-items: center; justify-content: center; color: #aaa;
            background-size: cover; background-position: center;
        }
        .camp-date-badge {
            position: absolute; top: 15px; right: 15px; background: var(--royal-red); color: white;
            padding: 5px 15px; border-radius: 50px; font-weight: bold; font-size: 0.8rem;
        }

        /* TABLE */
        .table th { font-size: 0.8rem; text-transform: uppercase; color: #888; font-weight: 700; border-bottom: 2px solid #eee; background: #fafafa; }
        .table td { vertical-align: middle; padding: 15px; }
        .btn-trash { background: #fef2f2; color: #dc2626; border: none; width: 35px; height: 35px; border-radius: 50%; }
        .btn-trash:hover { background: #dc2626; color: white; }
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
            <a href="inventory.php" class="nav-item "><i class="fa-solid fa-boxes-stacked"></i> Inventory</a> 
            <a href="manage_campaigns.php" class="nav-item active"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <div class="mt-4 mb-2 ps-4 text-uppercase small fw-bold" style="opacity: 0.4;">System</div>
            <a href="settings.php" class="nav-item"><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold text-dark">Campaign Manager</h3>
                <p class="text-muted">Create and manage blood donation drives.</p>
            </div>
        </div>

        <?php if(isset($msg)) echo "<div class='alert alert-$msg_type rounded-pill px-4'>$msg</div>"; ?>

        <div class="row g-4">
            
            <div class="col-lg-8">
                <div class="card-custom">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-pen-nib me-2 text-warning"></i> Create New Event</h5>
                    <form method="POST" id="campForm">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Campaign Title</label>
                                <input type="text" name="title" id="in_title" class="form-control" placeholder="e.g. Mega Blood Drive 2026" required oninput="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" id="in_loc" class="form-control" placeholder="City Center" required oninput="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Organizer</label>
                                <input type="text" name="organizer" id="in_org" class="form-control" placeholder="Red Crescent" required oninput="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="date" id="in_date" class="form-control" required oninput="updatePreview()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="time" id="in_time" class="form-control" required oninput="updatePreview()">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="in_desc" class="form-control" rows="3" placeholder="Join us to save lives..." required oninput="updatePreview()"></textarea>
                            </div>
                            <div class="col-12 text-end mt-4">
                                <button type="submit" name="add_campaign" class="btn btn-dark px-5 py-2 fw-bold">PUBLISH EVENT</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <h6 class="text-uppercase fw-bold text-muted mb-3 small">Website Live Preview</h6>
                <div class="camp-card-preview">
                    <div class="camp-img" style="background-image: url('https://images.unsplash.com/photo-1615461166324-cd1f91f73756?ixlib=rb-4.0.3');">
                        <span class="camp-date-badge" id="prev_date">DATE</span>
                    </div>
                    <div class="p-4">
                        <h4 class="fw-bold mb-2" id="prev_title">Campaign Title</h4>
                        <div class="mb-3 text-muted small">
                            <i class="fa-solid fa-location-dot me-2 text-danger"></i> <span id="prev_loc">Location</span>
                        </div>
                        <p class="text-muted small mb-4" id="prev_desc">Description will appear here...</p>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div class="small fw-bold text-dark"><i class="fa-solid fa-user-doctor me-2"></i> <span id="prev_org">Organizer</span></div>
                            <span class="badge bg-light text-dark border" id="prev_time">TIME</span>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-4 small border-0 bg-white shadow-sm">
                    <i class="fa-solid fa-info-circle me-2"></i> This card shows exactly how the event will appear to users on the Campaigns page.
                </div>
            </div>

        </div>

        <div class="card-custom mt-5">
            <h5 class="fw-bold mb-4">Active Campaigns</h5>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Date & Time</th>
                            <th>Organizer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?php echo $row['title']; ?></div>
                                <div class="small text-muted"><i class="fa-solid fa-map-pin me-1"></i> <?php echo $row['location']; ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo date('d M, Y', strtotime($row['event_date'])); ?></div>
                                <div class="small text-muted"><?php echo date('h:i A', strtotime($row['start_time'])); ?></div>
                            </td>
                            <td><span class="badge bg-light text-dark border"><?php echo $row['organizer']; ?></span></td>
                            <td>
                                <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-trash d-flex align-items-center justify-content-center" onclick="return confirm('Delete event?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function updatePreview() {
            document.getElementById('prev_title').innerText = document.getElementById('in_title').value || 'Campaign Title';
            document.getElementById('prev_loc').innerText = document.getElementById('in_loc').value || 'Location';
            document.getElementById('prev_org').innerText = document.getElementById('in_org').value || 'Organizer';
            document.getElementById('prev_desc').innerText = document.getElementById('in_desc').value || 'Description will appear here...';
            
            let dateVal = document.getElementById('in_date').value;
            document.getElementById('prev_date').innerText = dateVal ? new Date(dateVal).toDateString() : 'DATE';

            let timeVal = document.getElementById('in_time').value;
            document.getElementById('prev_time').innerText = timeVal || 'TIME';
        }
    </script>

</body>
</html>