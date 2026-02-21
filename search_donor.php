<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// --- LOGIC: Fetch Urgent Requests for the Marquee ---
$marquee_sql = "SELECT * FROM blood_requests WHERE status='Pending' ORDER BY created_at DESC LIMIT 5";
$marquee_res = $conn->query($marquee_sql);

// --- LOGIC: Search Variables ---
$search_city = isset($_GET['city']) ? $_GET['city'] : '';
$search_group = isset($_GET['blood_group']) ? $_GET['blood_group'] : '';
$results = null;

// --- SMART COMPATIBILITY ARRAYS ---
$compatibility = [
    'A+'  => ['A+', 'A-', 'O+', 'O-'],
    'A-'  => ['A-', 'O-'],
    'B+'  => ['B+', 'B-', 'O+', 'O-'],
    'B-'  => ['B-', 'O-'],
    'AB+' => ['AB+', 'AB-', 'A+', 'A-', 'B+', 'B-', 'O+', 'O-'],
    'AB-' => ['AB-', 'A-', 'B-', 'O-'],
    'O+'  => ['O+', 'O-'],
    'O-'  => ['O-'] 
];

// --- BUILD SEARCH QUERY ---
// CHANGE: I removed the 90-day check here so ALL approved donors show up
$sql = "SELECT * FROM donors WHERE status = 'Approved'";

// 1. City Filter
if (!empty($search_city)) {
    $sql .= " AND city LIKE '%$search_city%'";
}

// 2. Blood Group Logic (Smart Match)
if (!empty($search_group)) {
    if (isset($compatibility[$search_group])) {
        $compatible_types = $compatibility[$search_group];
        $types_string = "'" . implode("','", $compatible_types) . "'";
        $sql .= " AND blood_group IN ($types_string)";
    } else {
        $sql .= " AND blood_group = '$search_group'";
    }
}

// Order by Match Type first, then ID
$sql .= " ORDER BY CASE WHEN blood_group = '$search_group' THEN 1 ELSE 2 END, id DESC"; 
$results = $conn->query($sql);
?>

<style>
    /* --- CLASSIC IMAGE BACKGROUND (UNCHANGED) --- */
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1579154204601-01588f351e67?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        padding: 100px 0 120px;
        color: white;
        text-align: center;
        position: relative;
    }

    /* --- MARQUEE TICKER STYLE --- */
    .ticker-wrap {
        background: #800000;
        color: white;
        padding: 10px 0;
        font-family: 'Poppins', sans-serif;
        font-size: 0.9rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        position: relative;
        z-index: 20;
    }
    .ticker-item { display: inline-block; margin-right: 50px; }
    .ticker-tag { background: white; color: #800000; font-weight: bold; padding: 2px 8px; border-radius: 4px; margin-right: 5px; font-size: 0.8rem; }

    /* --- ANIMATIONS --- */
    @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

    .search-card-container { margin-top: -60px; z-index: 10; position: relative; animation: slideUp 0.8s ease-out; }

    .donor-card {
        border: none;
        border-radius: 15px;
        background: white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: 0.4s;
        animation: slideUp 0.6s ease-out backwards;
        overflow: hidden;
    }
    .donor-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
    .col-md-4:nth-child(1) .donor-card { animation-delay: 0.1s; }
    .col-md-4:nth-child(2) .donor-card { animation-delay: 0.2s; }
    .col-md-4:nth-child(3) .donor-card { animation-delay: 0.3s; }
    
    .blood-badge {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #dc3545, #800000);
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: bold;
        box-shadow: 0 5px 15px rgba(128,0,0,0.3);
        border: 3px solid white;
    }
    .match-badge { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
    
    /* Disabled Card State */
    .donor-card.unavailable { opacity: 0.8; background-color: #f9f9f9; }
    .donor-card.unavailable .blood-badge { filter: grayscale(100%); }
</style>

<div class="ticker-wrap">
    <div class="container">
        <marquee behavior="scroll" direction="left" onmouseover="this.stop();" onmouseout="this.start();">
            <?php if($marquee_res->num_rows > 0): ?>
                <?php while($mq = $marquee_res->fetch_assoc()): ?>
                    <span class="ticker-item">
                        <span class="ticker-tag">URGENT</span> 
                        <?php echo $mq['blood_group']; ?> Blood needed for <?php echo $mq['patient_name']; ?> in <?php echo $mq['city']; ?> (<?php echo $mq['hospital_name']; ?>)
                    </span>
                <?php endwhile; ?>
            <?php else: ?>
                <span class="ticker-item">Welcome to BloodLink Pro. Every drop counts. Donate today!</span>
            <?php endif; ?>
        </marquee>
    </div>
</div>

<div class="hero-section">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif; text-shadow: 2px 2px 10px rgba(0,0,0,0.5);">Find a Hero. Save a Life.</h1>
        <p class="lead opacity-90" style="text-shadow: 1px 1px 5px rgba(0,0,0,0.5);">Connect with medically approved donors in real-time.</p>
    </div>
</div>

<div class="container search-card-container mb-5">
    <div class="card border-0 shadow-lg rounded-4 p-4">
        <form method="GET" action="search_donor.php">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="fw-bold small text-secondary">CITY</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-map-location-dot text-danger"></i></span>
                        <input type="text" name="city" class="form-control border-start-0 ps-0" placeholder="Enter City (e.g. Faisalabad)" value="<?php echo htmlspecialchars($search_city); ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="fw-bold small text-secondary">PATIENT NEEDS</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-droplet text-danger"></i></span>
                        <select name="blood_group" class="form-select border-start-0 ps-0">
                            <option value="">Select Blood Group</option>
                            <option value="A+" <?php if($search_group=='A+') echo 'selected'; ?>>A+</option>
                            <option value="A-" <?php if($search_group=='A-') echo 'selected'; ?>>A-</option>
                            <option value="B+" <?php if($search_group=='B+') echo 'selected'; ?>>B+</option>
                            <option value="B-" <?php if($search_group=='B-') echo 'selected'; ?>>B-</option>
                            <option value="O+" <?php if($search_group=='O+') echo 'selected'; ?>>O+</option>
                            <option value="O-" <?php if($search_group=='O-') echo 'selected'; ?>>O-</option>
                            <option value="AB+" <?php if($search_group=='AB+') echo 'selected'; ?>>AB+</option>
                            <option value="AB-" <?php if($search_group=='AB-') echo 'selected'; ?>>AB-</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-danger py-2 fw-bold shadow-sm" style="background-color: #800000; border:none;">
                        FIND DONORS <i class="fa-solid fa-magnifying-glass ms-2"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container pb-5">
    
    <?php if(!empty($search_group)): ?>
        <div class="alert alert-light border shadow-sm mb-4 d-flex align-items-center">
            <i class="fa-solid fa-info-circle text-primary me-2"></i>
            <div>
                <strong>Smart Match Active:</strong> Searching for donors compatible with <span class="fw-bold text-danger"><?php echo $search_group; ?></span>.
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if ($results && $results->num_rows > 0): ?>
            <?php while($row = $results->fetch_assoc()): ?>
                <?php
                    // --- CALCULATE MEDICAL ELIGIBILITY IN PHP ---
                    $last_date = $row['last_donation_date'];
                    $is_available = true;
                    $next_date_str = "";
                    
                    if (!empty($last_date)) {
                        $last = new DateTime($last_date);
                        $today = new DateTime();
                        $interval = $today->diff($last);
                        if ($interval->days < 90) {
                            $is_available = false;
                            $available_date = clone $last;
                            $available_date->modify('+90 days');
                            $next_date_str = $available_date->format('d M');
                        }
                    }
                ?>

                <div class="col-md-4">
                    <div class="donor-card h-100 p-4 <?php echo !$is_available ? 'unavailable' : ''; ?>">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="blood-badge me-3">
                                    <?php echo $row['blood_group']; ?>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?php echo $row['fullname']; ?></h5>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i> <?php echo $row['city']; ?></small>
                                </div>
                            </div>
                            
                            <?php if($row['blood_group'] == $search_group): ?>
                                <span class="badge bg-success match-badge">Exact Match</span>
                            <?php elseif(!empty($search_group)): ?>
                                <span class="badge bg-warning text-dark match-badge">Compatible</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                             <div class="progress" style="height: 5px;">
                                <div class="progress-bar <?php echo $is_available ? 'bg-success' : 'bg-warning'; ?>" role="progressbar" style="width: 100%"></div>
                             </div>
                             
                             <?php if($is_available): ?>
                                <small class="text-success fw-bold"><i class="fa-solid fa-circle-check"></i> Medically Verified</small>
                             <?php else: ?>
                                <small class="text-warning fw-bold text-dark"><i class="fa-solid fa-hourglass-half"></i> Recovery Mode (Available <?php echo $next_date_str; ?>)</small>
                             <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2">
                            <?php if ($row['hide_contact'] == 1): ?>
                                <button class="btn btn-light btn-sm text-muted" disabled><i class="fa-solid fa-lock"></i> Number Private</button>
                                <a href="request_blood.php" class="btn btn-outline-dark btn-sm fw-bold">Request via Admin</a>
                            <?php else: ?>
                                <?php if($is_available): ?>
                                    <a href="tel:<?php echo $row['phone']; ?>" class="btn btn-danger btn-sm fw-bold shadow-sm" style="background-color: #800000; border:none;">
                                        <i class="fa-solid fa-phone me-2"></i> Call Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary btn-sm fw-bold" disabled>
                                        <i class="fa-solid fa-bed me-2"></i> Resting
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/6195/6195698.png" width="100" class="mb-3 opacity-50">
                <h4 class="fw-bold text-muted">No Donors Found</h4>
                <p class="text-secondary">We checked for exact and compatible donors, but no one is available right now.</p>
                <a href="request_blood.php" class="btn btn-warning fw-bold px-4 shadow-sm mt-2">Post a Request</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>