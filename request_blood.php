<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$message = "";

// --- LOGIC REMAINS EXACTLY THE SAME ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = $_POST['patient_name'];
    $blood_group = $_POST['blood_group'];
    $city = $_POST['city'];
    $hospital = $_POST['hospital'];
    $doctor = $_POST['doctor'];
    $contact_person = $_POST['contact_person'];
    $contact_number = $_POST['contact_number'];
    $date = $_POST['required_date'];
    $urgency = isset($_POST['is_critical']) ? 'Critical' : 'Normal';
    $reason = $_POST['reason'];

    $sql = "INSERT INTO blood_requests (patient_name, blood_group, city, hospital_name, doctor_name, contact_person, contact_number, required_date, urgency, reason, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $patient_name, $blood_group, $city, $hospital, $doctor, $contact_person, $contact_number, $date, $urgency, $reason);

    if ($stmt->execute()) {
        $message = "
        <div class='alert alert-success border-0 shadow-sm rounded-3 p-4 text-center'>
            <div class='display-4 text-success mb-3'><i class='fa-solid fa-circle-check'></i></div>
            <h4 class='fw-bold'>Request Broadcasted!</h4>
            <p class='text-muted'>Your request is now visible to donors in <strong>$city</strong>.</p>
            <a href='search_donor.php' class='btn btn-outline-success rounded-pill fw-bold mt-2'>Find Donors Manually</a>
        </div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<style>
    /* --- 1. CLASSIC IMAGE BACKGROUND (Parallax) --- */
    .req-header {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
        background-attachment: fixed; /* This makes the image stay still while scrolling */
        background-size: cover;
        background-position: center;
        padding: 120px 0 140px; /* Tall header for classic look */
        color: white;
        text-align: center;
        position: relative;
    }

    /* --- 2. MARQUEE STRIP --- */
    .ticker-bar {
        background: #800000;
        color: white;
        font-family: 'Poppins', sans-serif;
        font-size: 0.9rem;
        padding: 8px 0;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        position: relative;
        z-index: 20;
    }
    .ticker-item { margin-right: 50px; display: inline-flex; align-items: center; }
    .dot { height: 8px; width: 8px; background-color: #4cd137; border-radius: 50%; display: inline-block; margin-right: 8px; }

    /* --- 3. ANIMATIONS --- */
    @keyframes slideUpFade {
        from { transform: translateY(60px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15); /* Deep shadow for 3D effect */
        margin-top: -80px; /* Overlap the image */
        position: relative;
        z-index: 10;
        overflow: hidden;
        animation: slideUpFade 1s ease-out; /* The Entry Animation */
    }

    /* --- FORM STYLING --- */
    .form-section-title {
        color: #800000;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    
    .form-label { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #555; }
    .form-control, .form-select { padding: 12px; border-radius: 8px; border: 1px solid #ddd; background-color: #fcfcfc; transition: 0.3s; }
    .form-control:focus { border-color: #800000; background-color: white; box-shadow: 0 0 0 0.2rem rgba(128,0,0,0.1); }
    
    .urgency-box {
        background: #fff5f5; border: 1px solid #ffcccc; border-radius: 10px; padding: 20px;
        display: flex; align-items: center; justify-content: space-between;
        transition: 0.3s;
    }
    .urgency-box:hover { box-shadow: 0 5px 15px rgba(220, 53, 69, 0.1); }
    
    .submit-btn {
        background: linear-gradient(135deg, #800000, #b01010);
        border: none;
        transition: 0.3s;
    }
    .submit-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(128,0,0,0.3); }
</style>

<div class="ticker-bar">
    <div class="container-fluid">
        <marquee behavior="scroll" direction="left" scrollamount="6">
            <span class="ticker-item"><span class="dot"></span> Live System Active</span>
            <span class="ticker-item"><i class="fa-solid fa-users me-2"></i> 1,250+ Donors Ready</span>
            <span class="ticker-item"><i class="fa-solid fa-clock me-2"></i> Average Response Time: 30 Minutes</span>
            <span class="ticker-item"><i class="fa-solid fa-shield-heart me-2"></i> Your privacy is protected</span>
        </marquee>
    </div>
</div>

<div class="req-header">
    <div class="container">
        <h1 class="display-3 fw-bold mb-3" style="font-family: 'Playfair Display', serif; text-shadow: 2px 2px 8px rgba(0,0,0,0.5);">Post a Blood Request</h1>
        <p class="lead opacity-90" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.5);">Fill the form below. We will notify the nearest heroes instantly.</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php echo $message; ?>

            <div class="form-card">
                <div style="height: 8px; background: linear-gradient(90deg, #800000, #ff4d4d);"></div>
                
                <div class="p-5">
                    <form method="POST" action="">
                        
                        <h4 class="form-section-title"><i class="fa-solid fa-hospital-user me-2"></i> Patient Information</h4>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Patient Name</label>
                                <input type="text" name="patient_name" class="form-control" required placeholder="Full Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Blood Group Needed</label>
                                <select name="blood_group" class="form-select text-danger fw-bold" required>
                                    <option value="">Select Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" required placeholder="e.g. Faisalabad">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Required Date</label>
                                <input type="date" name="required_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <h4 class="form-section-title mt-5"><i class="fa-solid fa-location-dot me-2"></i> Location & Contact</h4>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Hospital Name</label>
                                <input type="text" name="hospital" class="form-control" required placeholder="e.g. Allied Hospital">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Doctor's Name (Optional)</label>
                                <input type="text" name="doctor" class="form-control" placeholder="Dr. Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control" required placeholder="Relative Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="contact_number" class="form-control" required placeholder="0300-1234567">
                            </div>
                        </div>

                        <div class="urgency-box mb-4">
                            <div>
                                <h6 class="fw-bold text-danger mb-0"><i class="fa-solid fa-heart-pulse me-2"></i> Critical Case?</h6>
                                <small class="text-muted">Toggle this ON if the patient needs blood within 24 hours.</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" style="width: 3.5em; height: 1.7em;" type="checkbox" name="is_critical" id="urgencySwitch">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Case Details / Reason</label>
                            <textarea name="reason" class="form-control" rows="3" required placeholder="e.g. Heart Surgery, Dengue, Accident..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger py-3 fw-bold shadow submit-btn" style="font-size: 1.1rem; border-radius: 50px;">
                                SUBMIT REQUEST <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>