<?php
// 1. Start Session & DB Connection FIRST (Before any HTML)
session_start();
include 'includes/db_connect.php';

$message = "";

// 2. Handle Login Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $sql = "SELECT id, fullname, password FROM donors WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verify Password
        if (password_verify($password, $row['password'])) {
            // Success! Set Session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['fullname'];
            
            // Redirect (This works now because no HTML has been sent yet)
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Invalid Password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Email not registered. <a href='register.php'>Join now</a></div>";
    }
}

// 3. NOW include the HTML Header (Safe to output HTML now)
include 'includes/header.php';
?>

<style>
    .login-container {
        min-height: 80vh; 
        background: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border-radius: 15px;
        overflow: hidden;
    }
    .image-side {
        background: url('https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80') no-repeat center center;
        background-size: cover;
        position: relative;
    }
    .overlay {
        background: rgba(128, 0, 0, 0.7); 
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        padding: 40px;
    }
</style>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="row login-container">
                
                <div class="col-md-6 image-side d-none d-md-block">
                    <div class="overlay text-center">
                        <div>
                            <h2 class="display-6 fw-bold" style="font-family: 'Playfair Display', serif;">Welcome Back</h2>
                            <p class="lead mt-3">"The blood you donate gives someone another chance at life."</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 p-5 d-flex flex-column justify-content-center">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark">Donor Login</h3>
                        <p class="text-muted small">Access your dashboard to manage donations.</p>
                    </div>

                    <?php echo $message; ?>

                    <form method="POST" action="">
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required>
                            <label for="email">Email Address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember">
                                <label class="form-check-label small" for="remember">Remember me</label>
                            </div>
                            <a href="#" class="small text-danger text-decoration-none">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 py-3 text-uppercase fw-bold">Login to Dashboard</button>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted small">New here?</span> 
                        <a href="register.php" class="fw-bold text-dark text-decoration-none">Create Account</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>