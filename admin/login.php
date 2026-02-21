<?php
session_start();
// Include the main website header to match the design perfectly
include '../includes/db_connect.php';
include '../includes/header.php';

$error = "";
$step = isset($_SESSION['login_step']) ? $_SESSION['login_step'] : 1;

// --- SECURITY LOGIC (SAME AS BEFORE) ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // LAYER 1: EMAIL & PASSWORD
    if (isset($_POST['btn_step_1'])) {
        $email = $_POST['email'];
        $pass = $_POST['password'];
        // Credentials
        if ($email === 'mbilalifzal82@gmail.com' && $pass === 'igi23111') {
            $_SESSION['login_step'] = 2;
            $step = 2;
        } else {
            $error = "Access Denied: Invalid Credentials.";
        }
    }

    // LAYER 2: ADMIN KEY
    if (isset($_POST['btn_step_2'])) {
        $key = $_POST['admin_key'];
        // Master Key
        if ($key === '23112311') {
            $_SESSION['login_step'] = 3;
            $step = 3;
        } else {
            $error = "Security Alert: Invalid Admin Key.";
        }
    }

    // LAYER 3: IDENTITY VERIFICATION
    if (isset($_POST['btn_step_3'])) {
        $cnic = $_POST['cnic'];
        $dob = $_POST['dob'];
        $enc_key = $_POST['enc_key'];

        // Identity Check
        if ($cnic === '3310037101209' && $dob === '2005-12-25' && $enc_key === 'bilal2311') {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_role'] = 'SuperAdmin';
            unset($_SESSION['login_step']);
            echo "<script>window.location.href='dashboard.php';</script>";
            exit();
        } else {
            $error = "Authentication Failed: Mismatch.";
        }
    }

    // RESET
    if (isset($_POST['btn_reset'])) {
        session_destroy();
        echo "<script>window.location.href='login.php';</script>";
    }
}
?>

<style>
    /* Match the Homepage Theme */
    body { background-color: #f9fbfd; }
    
    .login-container {
        padding: 80px 0;
        min-height: 80vh;
        background: url('https://www.transparenttextures.com/patterns/cubes.png');
    }

    .admin-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        overflow: hidden;
        border-top: 6px solid #800000; /* Royal Red Top */
    }

    .admin-header {
        background: white;
        padding: 40px 30px 20px;
        text-align: center;
    }

    .step-wizard {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
        position: relative;
    }
    
    /* Connecting Line */
    .step-wizard::before {
        content: ''; position: absolute; top: 50%; left: 20%; right: 20%; height: 2px; background: #eee; z-index: 0;
    }

    .step-dot {
        width: 40px; height: 40px;
        background: white; border: 2px solid #ddd;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; color: #999;
        z-index: 1; margin: 0 15px;
        transition: 0.3s;
    }
    .step-dot.active {
        background: #800000; border-color: #800000; color: white; box-shadow: 0 5px 15px rgba(128,0,0,0.3);
    }
    .step-dot.completed {
        background: #198754; border-color: #198754; color: white;
    }

    .form-control {
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ddd;
        background: #fdfdfd;
    }
    .form-control:focus {
        border-color: #c5a059; /* Gold Accent */
        box-shadow: 0 0 10px rgba(197, 160, 89, 0.2);
    }

    .btn-admin-primary {
        background: linear-gradient(135deg, #800000, #a01010);
        color: white; border: none; padding: 12px;
        border-radius: 50px; width: 100%; font-weight: bold;
        letter-spacing: 1px; transition: 0.3s;
    }
    .btn-admin-primary:hover {
        background: #2c3e50; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); color: white;
    }

    .animate-fade { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="login-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                
                <div class="admin-card animate-fade">
                    
                    <div class="admin-header">
                        <h2 class="fw-bold" style="font-family: 'Playfair Display', serif;">Admin Portal</h2>
                        <p class="text-muted small text-uppercase letter-spacing-2">Secure Access Gateway</p>
                        
                        <div class="step-wizard mt-4">
                            <div class="step-dot <?php echo ($step == 1) ? 'active' : (($step > 1) ? 'completed' : ''); ?>">1</div>
                            <div class="step-dot <?php echo ($step == 2) ? 'active' : (($step > 2) ? 'completed' : ''); ?>">2</div>
                            <div class="step-dot <?php echo ($step == 3) ? 'active' : ''; ?>">3</div>
                        </div>
                    </div>

                    <div class="p-4 pt-0">
                        <?php if($error): ?>
                            <div class="alert alert-danger border-0 shadow-sm text-center rounded-3 mb-4">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if($step == 1): ?>
                        <form method="POST" class="animate-fade">
                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-envelope text-secondary"></i></span>
                                    <input type="email" name="email" class="form-control border-start-0" placeholder="admin@bloodlink.com" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="fw-bold small text-muted mb-1">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-secondary"></i></span>
                                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                                </div>
                            </div>
                            <button type="submit" name="btn_step_1" class="btn-admin-primary">VERIFY CREDENTIALS</button>
                        </form>
                        <?php endif; ?>

                        <?php if($step == 2): ?>
                        <form method="POST" class="animate-fade text-center">
                            <i class="fa-solid fa-key fa-3x text-warning mb-3"></i>
                            <h5 class="fw-bold">Security Clearance</h5>
                            <p class="text-muted small mb-4">Please enter the 8-digit Master Key.</p>
                            
                            <div class="mb-4">
                                <input type="password" name="admin_key" class="form-control text-center fw-bold fs-4 letter-spacing-2" placeholder="X X X X - X X X X" maxlength="8" required>
                            </div>
                            <button type="submit" name="btn_step_2" class="btn-admin-primary">UNLOCK LEVEL 2</button>
                            <button type="submit" name="btn_reset" class="btn btn-link text-muted btn-sm mt-2 text-decoration-none">Cancel Login</button>
                        </form>
                        <?php endif; ?>

                        <?php if($step == 3): ?>
                        <form method="POST" class="animate-fade">
                            <div class="text-center mb-3">
                                <span class="badge bg-success mb-2">FINAL STEP</span>
                                <h5 class="fw-bold">Identity Verification</h5>
                            </div>

                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">CNIC Number</label>
                                <input type="text" name="cnic" class="form-control" placeholder="33100-XXXXXXX-X" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold small text-muted mb-1">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="fw-bold small text-muted mb-1">Encrypted Key</label>
                                <input type="password" name="enc_key" class="form-control" placeholder="Passcode" required>
                            </div>
                            
                            <button type="submit" name="btn_step_3" class="btn-admin-primary">ACCESS DASHBOARD</button>
                        </form>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-muted small text-decoration-none fw-bold">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Homepage
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>