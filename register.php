<?php
// Include DB and Header
include 'includes/db_connect.php';
include 'includes/header.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture All Inputs
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $blood_group = $_POST['blood_group'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $last_date = $_POST['last_donation_date'];
    $password = $_POST['password'];
    
    // Checkbox handling: if checked, value is 1, else 0
    $hide_contact = isset($_POST['hide_contact']) ? 1 : 0;

    // 2. Eligibility Logic (The "Smart" Part)
    $is_eligible = 1;
    if (!empty($last_date)) {
        $today = new DateTime();
        $last = new DateTime($last_date);
        $interval = $today->diff($last);
        if ($interval->days < 90) { 
            $is_eligible = 0; 
        }
    }

    // 3. Secure Password Hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 4. Insert into Database
    $sql = "INSERT INTO donors (fullname, email, phone, city, blood_group, gender, dob, last_donation_date, password, is_eligible, hide_contact) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssssssssii", $fullname, $email, $phone, $city, $blood_group, $gender, $dob, $last_date, $hashed_password, $is_eligible, $hide_contact);

        if ($stmt->execute()) {
            $message = "
            <div class='alert alert-success border-0 shadow-sm d-flex align-items-center'>
                <i class='fa-solid fa-circle-check fa-2x me-3'></i>
                <div>
                    <h5 class='mb-0'>Welcome, Hero!</h5>
                    <p class='mb-0'>Registration successful. <a href='login.php' class='fw-bold text-success'>Login now</a></p>
                </div>
            </div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Database Prepare Error: " . $conn->error . "</div>";
    }
}
?>

<style>
    /* Custom Page Styling */
    .register-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    .register-header {
        background-color: var(--primary-color); /* Uses variable from header.php */
        color: white;
        padding: 30px;
        text-align: center;
    }
    .form-floating > label {
        color: #777;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(128, 0, 0, 0.25);
    }
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            
            <?php echo $message; ?>

            <div class="card register-card shadow-lg">
                <div class="register-header">
                    <h2 class="fw-bold" style="font-family: 'Playfair Display', serif;">Join the Donor Network</h2>
                    <p class="mb-0 opacity-75">Fill in your details to help save lives in your area.</p>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST" action="">
                        
                        <h5 class="text-secondary mb-3 pb-2 border-bottom"><i class="fa-solid fa-user me-2"></i> Personal Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="fullname" class="form-control" id="fname" placeholder="Full Name" required>
                                    <label for="fname">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="gender" class="form-select" id="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <label for="gender">Gender</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" name="dob" class="form-control" id="dob" required>
                                    <label for="dob">Date of Birth</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="city" class="form-control" id="city" placeholder="City" required>
                                    <label for="city">Current City</label>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-secondary mb-3 pb-2 border-bottom"><i class="fa-solid fa-address-book me-2"></i> Contact Info</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone" required>
                                    <label for="phone">Phone Number</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="hide_contact" id="privacySwitch">
                                    <label class="form-check-label text-muted" for="privacySwitch">
                                        <small>Hide my phone number from public lists (Only Admin can see)</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <h5 class="text-secondary mb-3 pb-2 border-bottom"><i class="fa-solid fa-heart-pulse me-2"></i> Medical Details</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="blood_group" class="form-select text-danger fw-bold" id="bgroup" required>
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
                                    <label for="bgroup">Blood Group</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date" name="last_donation_date" class="form-control" id="lastDonation">
                                    <label for="lastDonation">Last Donation Date (Optional)</label>
                                </div>
                                <div class="form-text">Leave blank if you are a first-time donor.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-floating">
                                <input type="password" name="password" class="form-control" id="pass" placeholder="Password" required>
                                <label for="pass">Create Password</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-custom py-3 text-uppercase fw-bold" style="letter-spacing: 1px;">
                                Register Now
                            </button>
                        </div>
                        
                        <div class="text-center mt-4">
                            <span class="text-muted">Already have an account?</span> 
                            <a href="login.php" class="fw-bold text-decoration-none" style="color: var(--primary-color);">Log In Here</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>