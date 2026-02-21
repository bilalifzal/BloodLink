<?php
session_start();
include '../includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$msg_type = "";
$popup_patient = null; 

// 2. AUTO-UPDATE EXPIRY (Maintenance)
$conn->query("UPDATE donation_history SET status = 'Expired' WHERE expiry_date < CURDATE() AND status = 'Available'");

// --- ACTION A: RECRUIT DONORS ---
if (isset($_POST['recruit_donors'])) {
    $bg_target = $_POST['blood_group'];
    $sql = "SELECT id FROM donors WHERE blood_group = '$bg_target' AND status = 'Approved' AND (last_donation_date IS NULL OR last_donation_date < DATE_SUB(NOW(), INTERVAL 90 DAY))";
    $donors = $conn->query($sql);
    
    if ($donors->num_rows > 0) {
        while ($d = $donors->fetch_assoc()) {
            $uid = $d['id'];
            $conn->query("INSERT INTO notifications (user_id, type, message) VALUES ('$uid', 'system', 'URGENT: Stock low for $bg_target. Please donate!')");
        }
        $msg = "Alert sent to {$donors->num_rows} eligible donors!";
        $msg_type = "success";
    } else {
        $msg = "No eligible donors found.";
        $msg_type = "warning";
    }
}

// --- ACTION B: ASSIGN UNIT (WITH WHATSAPP TRIGGER) ---
if (isset($_POST['assign_unit'])) {
    $unit_db_id = $_POST['unit_db_id']; 
    $request_id = $_POST['request_id']; 
    
    $donor_id_res = $conn->query("SELECT user_id FROM donation_history WHERE id='$unit_db_id'");
    
    if($donor_id_res->num_rows > 0) {
        $d_id = $donor_id_res->fetch_assoc()['user_id'];
        
        $conn->query("UPDATE donation_history SET status = 'Used', assigned_to_request_id = '$request_id' WHERE id = '$unit_db_id'");
        $conn->query("UPDATE blood_requests SET status = 'Completed', assigned_donor_id = '$d_id' WHERE id = '$request_id'");

        // FETCH PATIENT DETAILS FOR WHATSAPP POPUP
        $pat_res = $conn->query("SELECT patient_name, contact_number, hospital_name FROM blood_requests WHERE id='$request_id'")->fetch_assoc();
        $popup_patient = $pat_res; 

        $msg = "Unit assigned successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error: Donor not found.";
        $msg_type = "danger";
    }
}

// --- ACTION C: NOTIFY DONOR ---
if (isset($_POST['notify_hero'])) {
    $donor_id = $_POST['donor_id'];
    $unit_id = $_POST['unit_name'];
    $notif_msg = "HERO UPDATE: Your donation ($unit_id) was used to save a life today. Thank you!";
    $conn->query("INSERT INTO notifications (user_id, type, message) VALUES ('$donor_id', 'system', '$notif_msg')");
    $msg = "Hero notification sent!";
    $msg_type = "success";
}

// 3. DATA FOR CHARTS & CARDS
$stock = [];
$groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
$chart_labels = []; $chart_data = [];
foreach ($groups as $bg) {
    $count = $conn->query("SELECT COUNT(dh.id) FROM donation_history dh JOIN donors d ON dh.user_id = d.id WHERE d.blood_group = '$bg' AND dh.status = 'Available'")->fetch_row()[0];
    $stock[$bg] = $count;
    $chart_labels[] = $bg;
    $chart_data[] = $count;
}

$status_counts = $conn->query("SELECT status, COUNT(*) as c FROM donation_history GROUP BY status");
$pie_labels = []; $pie_data = []; $pie_colors = [];
while($row = $status_counts->fetch_assoc()) {
    $pie_labels[] = $row['status'];
    $pie_data[] = $row['c'];
    if($row['status']=='Available') $pie_colors[] = '#198754';
    if($row['status']=='Used') $pie_colors[] = '#0d6efd';
    if($row['status']=='Expired') $pie_colors[] = '#dc3545';
}

// 4. FETCH DATA LISTS
$req_res = $conn->query("SELECT id, patient_name, blood_group, urgency, hospital_name FROM blood_requests WHERE status = 'Pending'");
$pending_requests = [];
while($r = $req_res->fetch_assoc()) { $pending_requests[] = $r; }

$inv_sql = "SELECT dh.*, d.fullname, d.blood_group, d.city FROM donation_history dh JOIN donors d ON dh.user_id = d.id WHERE dh.status = 'Available' ORDER BY dh.expiry_date ASC";
$units_res = $conn->query($inv_sql);

$used_sql = "SELECT dh.*, d.fullname as donor_name, d.id as donor_id, d.blood_group, d.city as donor_city,
                    r.patient_name, r.hospital_name, r.contact_number as patient_phone, r.updated_at as fulfillment_date
            FROM donation_history dh 
            JOIN donors d ON dh.user_id = d.id 
            LEFT JOIN blood_requests r ON dh.assigned_to_request_id = r.id
            WHERE dh.status = 'Used' ORDER BY dh.id DESC LIMIT 50";
$used_res = $conn->query($used_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory | BloodLink Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root { --sidebar-bg: #1a1c23; --royal-gold: #c5a059; --royal-red: #800000; }
        body { background-color: #f3f4f6; font-family: 'Segoe UI', sans-serif; }

        .sidebar { width: 280px; height: 100vh; position: fixed; background: var(--sidebar-bg); color: #a0aec0; display: flex; flex-direction: column; z-index: 1000; }
        .sidebar-header { padding: 30px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; }
        .nav-item { padding: 15px 25px; color: #a0aec0; text-decoration: none; display: flex; align-items: center; transition: 0.3s; border-left: 4px solid transparent; }
       
        .nav-item i { width: 35px; }
        .main-content { margin-left: 280px; padding: 30px; }

        /* Classic Stock Cards */
        .stock-card { 
            background: white; border-radius: 12px; padding: 20px; text-align: center; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); transition: 0.3s; position: relative; 
            border-bottom: 4px solid transparent; overflow: hidden;
        }
        .stock-card:hover { transform: translateY(-5px); }
        .stock-val { font-size: 2.5rem; font-weight: 800; color: #333; }
        .stock-lbl { font-size: 0.9rem; text-transform: uppercase; color: #888; font-weight: bold; }
        .is-low { border-color: #dc3545; background: #fff5f5; }
        .is-good { border-color: #198754; }
        .icon-bg { position: absolute; top: -10px; right: -10px; font-size: 4rem; opacity: 0.1; }
        .btn-recruit { margin-top: 10px; font-size: 0.75rem; padding: 5px 15px; border-radius: 20px; background: #2c3e50; color: white; border: none; width: 100%; }
        .btn-recruit:hover { background: var(--royal-red); }

        .chart-box { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 100%; }
        .nav-tabs .nav-link { color: #555; font-weight: 600; border: none; padding: 15px 25px; }
        .nav-tabs .nav-link.active { color: var(--royal-red); border-bottom: 3px solid var(--royal-red); background: transparent; }
        .table th { background: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; color: #666; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold text-white mb-0">BLOOD<span style="color: var(--royal-gold);">LINK</span></h4>
            <small style="letter-spacing: 1px; opacity: 0.6;">ADMIN PANEL</small>
        </div> <div class="mt-4">
            <a href="dashboard.php" class="nav-item "><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
            <a href="manage_donors.php" class="nav-item"><i class="fa-solid fa-users"></i> Donors</a>
            <a href="manage_requests.php" class="nav-item"><i class="fa-solid fa-bed-pulse"></i> Blood Requests</a>
   <a href="inventory.php" class="nav-item active"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>            <a href="manage_campaigns.php" class="nav-item"><i class="fa-solid fa-bullhorn"></i> Campaigns</a>
            <a href="analytics.php" class="nav-item"><i class="fa-solid fa-chart-pie"></i> Analytics</a>
            
            <div class="mt-4 mb-2 ps-4 text-uppercase small fw-bold" style="opacity: 0.4;">System</div>
            <a href="settings.php" class="nav-item "><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="logout.php" class="nav-item text-danger mt-3"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main-content">
        <h3 class="fw-bold text-dark mb-4">Inventory Management</h3>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?> shadow-sm border-0 rounded-3 mb-4">
                <i class="fa-solid fa-info-circle me-2"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <h5 class="fw-bold mb-3">Live Stock Overview</h5>
        <div class="row g-3 mb-4">
            <?php foreach($stock as $bg => $cnt): 
                $status_class = ($cnt < 5) ? 'is-low' : 'is-good';
                $text_color = ($cnt < 5) ? 'text-danger' : 'text-success';
            ?>
            <div class="col-xl-3 col-md-4 col-6">
                <div class="stock-card <?php echo $status_class; ?>">
                    <i class="fa-solid fa-droplet icon-bg text-dark"></i>
                    <h2 class="stock-val <?php echo $text_color; ?>"><?php echo $cnt; ?></h2>
                    <div class="stock-lbl"><?php echo $bg; ?> Blood</div>
                    <form method="POST">
                        <input type="hidden" name="blood_group" value="<?php echo $bg; ?>">
                        <button type="submit" name="recruit_donors" class="btn-recruit shadow-sm">
                            <i class="fa-solid fa-bullhorn me-1"></i> Call Donors
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-8"><div class="chart-box"><h6 class="fw-bold mb-3">Inventory Distribution</h6><canvas id="barChart" height="100"></canvas></div></div>
            <div class="col-lg-4"><div class="chart-box"><h6 class="fw-bold mb-3">Stock Health</h6><canvas id="pieChart" height="150"></canvas></div></div>
        </div>

        <div class="bg-white rounded-top-4 px-3 pt-2 border-bottom">
            <ul class="nav nav-tabs" id="invTabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#live">Available Units</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history">Utilization Log</button></li>
            </ul>
        </div>
        
        <div class="tab-content bg-white rounded-bottom-4 shadow-sm p-4">
            
            <div class="tab-pane fade show active" id="live">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Unit ID</th><th>Group</th><th>Donor</th><th>Expiry</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php if ($units_res->num_rows > 0): while($unit = $units_res->fetch_assoc()): 
                            $days_left = floor((strtotime($unit['expiry_date']) - time()) / (86400));
                            $cls = ($days_left < 10) ? 'bg-danger' : 'bg-success';
                        ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?php echo $unit['unit_id'] ? $unit['unit_id'] : 'UN-'.$unit['id']; ?></td>
                            <td><span class="badge bg-dark rounded-pill"><?php echo $unit['blood_group']; ?></span></td>
                            <td><?php echo $unit['fullname']; ?><br><small class="text-muted"><?php echo $unit['city']; ?></small></td>
                            <td><span class="badge text-white <?php echo $cls; ?> p-2"><?php echo $days_left; ?> Days Left</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-danger rounded-pill fw-bold px-3 shadow-sm" onclick="openAssignModal('<?php echo $unit['id']; ?>', '<?php echo $unit['blood_group']; ?>', '<?php echo $unit['unit_id'] ? $unit['unit_id'] : 'UN-'.$unit['id']; ?>')">Assign</button>
                            </td>
                        </tr>
                        <?php endwhile; else: echo "<tr><td colspan='5' class='text-center py-5'>Inventory empty.</td></tr>"; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="history">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Unit Info</th><th>Assigned Patient</th><th>Source Donor</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php if ($used_res->num_rows > 0): while($used = $used_res->fetch_assoc()): 
                            $wa_link = "https://wa.me/" . str_replace(['+',' ','-'], '', $used['patient_phone']);
                        ?>
                        <tr>
                            <td><div class="fw-bold">#<?php echo $used['unit_id'] ? $used['unit_id'] : 'UN-'.$used['id']; ?></div><span class="badge bg-danger"><?php echo $used['blood_group']; ?></span></td>
                            <td>
                                <div class="fw-bold text-primary"><?php echo $used['patient_name']; ?></div>
                                <div class="d-flex align-items-center mt-1">
                                    <small class="text-muted me-2"><?php echo $used['hospital_name']; ?></small>
                                    <a href="<?php echo $wa_link; ?>" target="_blank" class="btn btn-sm btn-success rounded-circle px-1 py-0"><i class="fa-brands fa-whatsapp"></i></a>
                                </div>
                            </td>
                            <td><div class="fw-bold"><?php echo $used['donor_name']; ?></div><small class="text-muted"><?php echo $used['donor_city']; ?></small></td>
                            <td><?php echo date('d M, Y', strtotime($used['fulfillment_date'])); ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-dark rounded-pill" onclick="generateInvoice('<?php echo $used['unit_id'] ?? 'UN-'.$used['id']; ?>','<?php echo $used['blood_group']; ?>','<?php echo $used['patient_name']; ?>','<?php echo $used['hospital_name']; ?>','<?php echo $used['donor_name']; ?>','<?php echo date('d M, Y', strtotime($used['fulfillment_date'])); ?>')"><i class="fa-solid fa-file-invoice"></i></button>
                                <form method="POST" style="display:inline;"><input type="hidden" name="donor_id" value="<?php echo $used['donor_id']; ?>"><input type="hidden" name="unit_name" value="<?php echo $used['unit_id'] ?? 'UN-'.$used['id']; ?>"><button type="submit" name="notify_hero" class="btn btn-sm btn-outline-success rounded-pill"><i class="fa-solid fa-bell"></i></button></form>
                            </td>
                        </tr>
                        <?php endwhile; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><div class="modal-header bg-danger text-white"><h5 class="modal-title fw-bold">Assign Unit</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body p-4"><form method="POST"><input type="hidden" name="unit_db_id" id="modalUnitId"><div class="mb-3"><label class="fw-bold">Select Request (<span id="modalBg"></span>)</label><select name="request_id" class="form-select" required id="reqSelect"></select></div><button type="submit" name="assign_unit" class="btn btn-danger w-100 fw-bold">Confirm</button></form></div></div></div></div>

    <div class="modal fade" id="waModal" tabindex="-1" data-bs-backdrop="static"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><div class="modal-body p-5 text-center"><div class="mb-3 text-success"><i class="fa-brands fa-whatsapp fa-5x"></i></div><h3 class="fw-bold mb-2">Unit Assigned!</h3><p class="text-muted mb-4" id="waName"></p><a id="waLink" href="#" target="_blank" class="btn btn-success btn-lg w-100 fw-bold">Notify via WhatsApp</a><button class="btn btn-link text-muted mt-3" data-bs-dismiss="modal">Close</button></div></div></div></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    new Chart(document.getElementById('barChart'), { type: 'bar', data: { labels: <?php echo json_encode($chart_labels); ?>, datasets: [{ label: 'Units', data: <?php echo json_encode($chart_data); ?>, backgroundColor: 'rgba(128, 0, 0, 0.7)', borderRadius: 5 }] } });
    new Chart(document.getElementById('pieChart'), { type: 'doughnut', data: { labels: <?php echo json_encode($pie_labels); ?>, datasets: [{ data: <?php echo json_encode($pie_data); ?>, backgroundColor: <?php echo json_encode($pie_colors); ?>, borderWidth: 0 }] }, options: { cutout: '60%', plugins: { legend: { position: 'bottom' } } } });

    const requests = <?php echo json_encode($pending_requests); ?>;
    function openAssignModal(id, bg, code) {
        document.getElementById('modalUnitId').value = id;
        document.getElementById('modalBg').innerText = bg;
        let s = document.getElementById('reqSelect'); s.innerHTML = '<option value="">Select...</option>';
        requests.forEach(r => { if(r.blood_group === bg) { let o = document.createElement('option'); o.value = r.id; o.text = r.patient_name; s.appendChild(o); } });
        new bootstrap.Modal(document.getElementById('assignModal')).show();
    }

    <?php if($popup_patient): ?>
        var waModal = new bootstrap.Modal(document.getElementById('waModal'));
        document.getElementById('waName').innerText = "Notify <?php echo $popup_patient['patient_name']; ?>";
        var phone = "<?php echo str_replace(['+',' ','-'], '', $popup_patient['contact_number']); ?>";
        var text = "Hello <?php echo $popup_patient['patient_name']; ?>, your blood request at BloodLink Pro has been fulfilled.";
        document.getElementById('waLink').href = "https://wa.me/" + phone + "?text=" + encodeURIComponent(text);
        waModal.show();
    <?php endif; ?>

    function generateInvoice(unitId, bg, patient, hospital, donor, date) {
        var w = window.open('', '', 'width=850,height=950');
        w.document.write(`
            <html><head><title>Invoice</title><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"><style>
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Playfair+Display:wght@900&display=swap');
                body { font-family: 'Inter', sans-serif; padding: 50px; }
                .header { display: flex; justify-content: space-between; border-bottom: 3px solid #800000; padding-bottom: 20px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 40px; }
                .info-box { background: #fafafa; padding: 20px; border-radius: 8px; border: 1px solid #eee; }
                .blood-badge { font-size: 60px; font-weight: 900; color: #800000; text-align: center; margin: 40px 0; border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; }
                .stamp { border: 4px double #198754; color: #198754; padding: 10px; transform: rotate(-15deg); display: inline-block; font-weight: 900; }
            </style></head>
            <body>
                <div class="header"><div><h1 style="color:#800000;margin:0;">BLOODLINK PRO</h1><p style="margin:0;color:#c5a059;">OFFICIAL TRANSFUSION MANIFEST</p></div><div style="text-align:right;"><b>ID: #INV-${unitId}</b><br>${date}</div></div>
                <div class="info-grid">
                    <div class="info-box"><h4 style="color:#800000;margin-top:0;">RECIPIENT</h4><b>Name:</b> ${patient}<br><b>Hospital:</b> ${hospital}</div>
                    <div class="info-box"><h4 style="color:#800000;margin-top:0;">DONOR</h4><b>Name:</b> ${donor}<br><b>Status:</b> Verified</div>
                </div>
                <div class="blood-badge">${bg}</div>
                <div style="background:#fff9e6;padding:15px;border-left:5px solid #c5a059;"><b>Disclaimer:</b> Facility must verify cross-matching before transfusion.</div>
                <div style="margin-top:50px;display:flex;justify-content:space-between;align-items:center;"><div class="stamp">MATCHED & FULFILLED</div><div style="border-top:1px solid #000;width:200px;text-align:center;">Authorized Signature</div></div>
            </body></html>
        `);
        w.document.close(); setTimeout(() => { w.print(); }, 500);
    }
    </script>
</body>
</html>