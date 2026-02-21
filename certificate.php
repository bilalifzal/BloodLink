<?php
session_start();
include 'includes/db_connect.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// 2. CHECK DONATION STATUS
// We check if the user has at least one record in donation_history
$history = $conn->query("SELECT * FROM donation_history WHERE user_id = '$user_id'");
$has_donated = ($history->num_rows > 0);

// Fetch User Details
$user = $conn->query("SELECT * FROM donors WHERE id = '$user_id'")->fetch_assoc();
$fullname = isset($user['fullname']) ? $user['fullname'] : "Valued Donor";
$date_issued = date("F j, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Appreciation | BloodLink Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Pinyon+Script&family=Cinzel:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: #2c3e50; /* Dark background to make certificate pop */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Playfair Display', serif;
            margin: 0;
        }

        /* --- PAPER TEXTURE & SHAPE --- */
        .cert-container {
            width: 1100px; /* A4 Landscape Ratio */
            height: 750px;
            background-color: #fff;
            /* Subtle paper texture */
            background-image: radial-gradient(#fffdf5 20%, #fff 80%); 
            position: relative;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            padding: 25px;
            text-align: center;
            color: #1a1a1a;
            box-sizing: border-box;
        }

        /* --- ORNAMENTAL BORDERS --- */
        .border-outer {
            width: 100%;
            height: 100%;
            border: 5px solid #800000; /* Royal Red */
            padding: 5px;
            position: relative;
            box-sizing: border-box;
        }
        
        .border-inner {
            width: 100%;
            height: 100%;
            border: 3px solid #c5a059; /* Gold */
            padding: 40px;
            box-sizing: border-box;
            background: url('https://www.transparenttextures.com/patterns/cream-paper.png'); /* Subtle pattern */
            position: relative;
        }

        /* CORNER DECORATIONS */
        .corner {
            position: absolute;
            width: 60px;
            height: 60px;
            border-top: 5px solid #800000;
            border-left: 5px solid #800000;
        }
        .top-left { top: -5px; left: -5px; }
        .top-right { top: -5px; right: -5px; transform: rotate(90deg); }
        .btm-right { bottom: -5px; right: -5px; transform: rotate(180deg); }
        .btm-left { bottom: -5px; left: -5px; transform: rotate(270deg); }

        /* --- TYPOGRAPHY --- */
        .header-title {
            font-family: 'Cinzel', serif;
            font-size: 50px;
            color: #1a1a1a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-bottom: 5px;
            margin-top: 20px;
        }
        
        .sub-header {
            font-size: 20px;
            color: #800000;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: bold;
            margin-bottom: 40px;
            border-bottom: 1px solid #c5a059;
            display: inline-block;
            padding-bottom: 10px;
        }

        .presented-text {
            font-style: italic;
            font-size: 18px;
            color: #555;
        }

        .recipient-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 80px;
            color: #800000;
            margin: 10px 0 20px 0;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.1);
            line-height: 1;
        }

        .cert-body {
            font-size: 18px;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto 50px auto;
            color: #444;
        }

        /* --- SIGNATURES --- */
        .signatures {
            display: flex;
            justify-content: space-between;
            padding: 0 100px;
            margin-top: 60px;
        }
        
        .sig-block { text-align: center; }
        
        .sig-text {
            font-family: 'Pinyon Script', cursive;
            font-size: 30px;
            color: #1a1a1a;
            margin-bottom: 5px;
        }
        
        .sig-line {
            border-top: 2px solid #333;
            width: 220px;
            margin: 0 auto;
        }
        
        .sig-label {
            font-family: 'Cinzel', serif;
            font-size: 12px;
            font-weight: bold;
            margin-top: 8px;
            color: #800000;
            text-transform: uppercase;
        }

        /* --- GOLD SEAL --- */
        .gold-seal {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 110px;
            height: 110px;
            background: #c5a059;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 5px #fff, 0 0 0 8px #c5a059;
            color: #800000;
            font-weight: bold;
            font-family: 'Cinzel', serif;
            text-align: center;
            font-size: 12px;
            line-height: 1.4;
        }

        /* --- PRINT BUTTONS --- */
        .btn-panel {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 15px;
            z-index: 999;
        }

        @media print {
            body { background: white; }
            .cert-container { box-shadow: none; width: 100%; height: 100%; margin: 0; }
            .btn-panel { display: none; }
            /* Force Background Colors Print */
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<?php if($has_donated): ?>
    <div class="cert-container">
        <div class="border-outer">
            <div class="corner top-left"></div>
            <div class="corner top-right"></div>
            <div class="corner btm-left"></div>
            <div class="corner btm-right"></div>

            <div class="border-inner">
                
                <i class="fa-solid fa-heart-pulse fa-3x" style="color: #800000; opacity: 0.8; margin-bottom: 10px;"></i>

                <div class="header-title">Certificate</div>
                <div class="sub-header">Of Appreciation</div>
                
                <p class="presented-text">This honor is proudly presented to</p>
                
                <div class="recipient-name"><?php echo $fullname; ?></div>
                
                <p class="cert-body">
                    In grateful recognition of your selfless donation of blood. 
                    Your generosity serves as a beacon of hope and has made a vital difference in saving lives. 
                    The BloodLink Pro community deeply values your humanitarian spirit.
                </p>
                
                <div class="gold-seal">
                    OFFICIAL<br>DONOR<br>AWARD
                </div>

                <div class="signatures">
                    <div class="sig-block">
                        <div class="sig-text"><?php echo $date_issued; ?></div>
                        <div class="sig-line"></div>
                        <div class="sig-label">Date Issued</div>
                    </div>
                    
                    <div class="sig-block">
                        <div class="sig-text" style="font-size: 34px;">M. Bilal Ifzal</div>
                        <div class="sig-line"></div>
                        <div class="sig-label">Founder & Authority</div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="btn-panel">
        <button onclick="window.print()" class="btn btn-warning fw-bold shadow-lg rounded-pill px-4">
            <i class="fa-solid fa-print me-2"></i> Print Certificate
        </button>
        <a href="dashboard.php" class="btn btn-dark fw-bold shadow-lg rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>

<?php else: ?>
    <div class="card text-center shadow-lg p-5" style="max-width: 500px; border-top: 5px solid #800000;">
        <div class="mb-3">
            <i class="fa-solid fa-lock fa-4x text-secondary"></i>
        </div>
        <h2 class="fw-bold text-dark mb-3">Certificate Locked</h2>
        <p class="text-muted mb-4">
            Thank you for joining BloodLink Pro! <br>
            To earn your <strong>Certificate of Heroism</strong>, you must complete your first confirmed blood donation.
        </p>
        <a href="dashboard.php" class="btn btn-danger rounded-pill px-4 fw-bold">Return to Dashboard</a>
    </div>
<?php endif; ?>

</body>
</html>