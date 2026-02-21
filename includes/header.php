<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BloodLink Pro - Save a Life</title>
    <link rel="icon" type="image/png" href="https://img.icons8.com/ios-filled/50/800000/hearts.png">
    
    <link rel="apple-touch-icon" href="https://img.icons8.com/ios-filled/180/800000/hearts.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #800000; /* Deep Burgundy */
            --secondary-color: #2c3e50; /* Dark Blue-Grey */
            --accent-color: #d4af37; /* Gold */
            --light-bg: #f8f9fa;
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: var(--light-bg); 
            color: #333;
            overflow-x: hidden;
        }

        /* --- Main Navbar --- */
        .navbar {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            padding: 12px 0;
        }
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            color: var(--primary-color) !important;
            font-weight: 700;
        }

        /* --- Link Hover Effects --- */
        .nav-link {
            color: #444 !important;
            font-weight: 500;
            margin: 0 10px;
            transition: 0.3s ease;
            position: relative;
        }
        
        /* Red Hover Effect on Links */
        .nav-link:hover {
            color: var(--primary-color) !important;
            transform: translateY(-2px);
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s;
        }
        .nav-link:hover::after { width: 100%; }

        /* --- Portal / Member Styling --- */
        .member-portal-btn {
            background: rgba(128, 0, 0, 0.05);
            border: 1px solid var(--primary-color);
            color: var(--primary-color) !important;
            border-radius: 50px;
            padding: 8px 20px !important;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }
        .member-portal-btn:hover {
            background: var(--primary-color);
            color: white !important;
            box-shadow: 0 5px 15px rgba(128, 0, 0, 0.2);
        }

        /* --- Mobile Responsive Fixes --- */
        @media (max-width: 991px) {
            .navbar-nav {
                text-align: center;
                padding: 20px 0;
            }
            .nav-item { margin: 10px 0; }
            .member-portal-btn { justify-content: center; }
        }

        .btn-primary-custom {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50px;
            padding: 8px 25px;
            font-weight: 600;
            border: 2px solid var(--primary-color);
            transition: 0.3s;
        }
        .btn-primary-custom:hover { background: #5a0000; color: white; transform: scale(1.05); }
        
        .btn-outline-custom {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 50px;
            padding: 8px 25px;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-outline-custom:hover { background: var(--primary-color); color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fa-solid fa-droplet text-danger me-1"></i> BloodLink<span class="text-dark">Pro</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="fa-solid fa-bars-staggered text-dark"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="search_donor.php">Find Donors</a></li>
                <li class="nav-item"><a class="nav-link" href="campaigns.php">Campaigns</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item ms-lg-3">
                        <a class="nav-link member-portal-btn" href="dashboard.php">
                            <i class="fa-solid fa-shield-heart me-2"></i> 
                            Hero Portal
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php" title="Sign Out">
                            <i class="fa-solid fa-power-off"></i>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-custom btn-sm" href="login.php">Log In</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary-custom btn-sm" href="register.php">Become a Donor</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>