<?php
// 1. SECURITY CHECK (Mandatory at top)
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. FETCH DATA
$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM donors WHERE id = $user_id")->fetch_assoc();

// Calculate total donations for more detail
$history_count = $conn->query("SELECT COUNT(*) as total FROM donation_history WHERE user_id = $user_id")->fetch_assoc();
$total_donations = $history_count['total'];
$donor_uuid = "BLP-MEM-" . str_pad($user['id'], 5, '0', STR_PAD_LEFT);

include 'includes/header.php';
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<style>
    /* --- THE CARD DESIGN --- */
    .ultra-luxury-card {
        width: 360px;
        height: 560px;
        background: #fff;
        border-radius: 25px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        border: 1px solid #eee;
        margin: 40px auto;
        font-family: 'Poppins', sans-serif;
    }

    .card-header-bg {
        height: 160px;
        background: linear-gradient(135deg, #800000 0%, #4a0000 100%);
        padding: 25px;
        text-align: center;
        color: white;
    }

    .card-header-bg h4 { font-family: 'Playfair Display', serif; font-weight: 700; letter-spacing: 1px; margin: 0; }
    .card-header-bg span { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 3px; opacity: 0.8; }

    .gold-border-profile {
        width: 120px;
        height: 120px;
        background: white;
        border-radius: 50%;
        margin: -60px auto 15px;
        position: relative;
        z-index: 10;
        padding: 5px;
        border: 3px solid #d4af37; /* Gold Color from website */
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .gold-border-profile img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

    .blood-type-ribbon {
        background: #800000;
        color: white;
        padding: 4px 18px;
        border-radius: 50px;
        font-weight: 800;
        font-size: 0.85rem;
        display: inline-block;
        margin-bottom: 15px;
        box-shadow: 0 4px 8px rgba(128, 0, 0, 0.2);
    }

    .card-details { padding: 0 30px; text-align: center; }
    .card-name { color: #2c3e50; font-weight: 800; font-size: 1.4rem; margin-bottom: 0; }
    .card-id-tag { color: #d4af37; font-size: 0.8rem; font-weight: 700; margin-bottom: 20px; display: block; }

    /* Detailed Info Grid */
    .card-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        background: #fdfdfd;
        border: 1px solid #f1f1f1;
        border-radius: 20px;
        padding: 20px;
        margin-top: 10px;
    }
    .info-item { text-align: left; }
    .info-item label { font-size: 0.6rem; color: #aaa; text-transform: uppercase; font-weight: 700; display: block; margin: 0; }
    .info-item p { font-size: 0.85rem; color: #333; font-weight: 700; margin: 0; }

    .card-footer-branding {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 20px 30px;
        background: #f8f9fa;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid #eee;
    }
    .developer-credit { font-size: 0.55rem; color: #999; line-height: 1.3; }
    .developer-credit strong { color: #800000; }

    /* --- PRINT ONLY THE CARD LOGIC --- */
    @media print {
        body * { visibility: hidden; }
        #printable-card-area, #printable-card-area * { visibility: visible; }
        #printable-card-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .ultra-luxury-card {
            box-shadow: none;
            border: 1px solid #ccc;
            margin: 0;
        }
    }
</style>

<div class="container py-5">
    <div class="text-center mb-4 no-print">
        <h2 class="fw-bold" style="color: #800000;">Member Recognition</h2>
        <p class="text-muted">High-End Digital Identity for BloodLink Pro Donors.</p>
    </div>

    <div class="text-center">
        <div id="printable-card-area">
            <div class="ultra-luxury-card mx-auto">
                <div class="card-header-bg">
                    <h4>BLOODLINK PRO</h4>
                    <span>Premium Life Saver</span>
                </div>

                <div class="gold-border-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['fullname']); ?>&background=800000&color=fff&size=200">
                </div>

                <div class="card-details">
                    <div class="blood-type-ribbon">BLOOD TYPE: <?php echo $user['blood_group']; ?></div>
                    <h3 class="card-name"><?php echo strtoupper(htmlspecialchars($user['fullname'])); ?></h3>
                    <span class="card-id-tag"><?php echo $donor_uuid; ?></span>

                    <div class="card-info-grid">
                        <div class="info-item">
                            <label>Location</label>
                            <p><?php echo htmlspecialchars($user['city']); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <p><?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        <div class="info-item">
                            <label>Impact</label>
                            <p><?php echo ($total_donations * 3); ?> Lives Saved</p>
                        </div>
                        <div class="info-item">
                            <label>Status</label>
                            <p style="color: #198754;">Verified</p>
                        </div>
                    </div>
                </div>

                <div class="card-footer-branding">
                    <div class="developer-credit">
                        DEVELOPED BY:<br>
                        <strong>MUHAMMAD BILAL IFZAL</strong>
                    </div>
                    <div>
                        <i class="fa-solid fa-award fa-2x" style="color: #d4af37;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 no-print mb-5">
            <button onclick="downloadCardImage()" class="btn btn-primary-custom px-5 py-3 shadow">
                <i class="fa-solid fa-file-arrow-down me-2"></i> Download as Image
            </button>
            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 ms-2">
                <i class="fa-solid fa-print me-2"></i> Print Card
            </button>
            <div class="mt-4">
                <a href="dashboard.php" class="text-muted text-decoration-none small">
                    <i class="fa-solid fa-circle-arrow-left me-1"></i> Back to Hero Portal
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function downloadCardImage() {
        const btn = event.currentTarget;
        btn.innerHTML = 'Generating...';
        
        html2canvas(document.querySelector(".ultra-luxury-card"), {
            scale: 3, // High Resolution
            useCORS: true,
            backgroundColor: null
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'BloodLink_Premium_Card.png';
            link.href = canvas.toDataURL("image/png");
            link.click();
            btn.innerHTML = '<i class="fa-solid fa-file-arrow-down me-2"></i> Download as Image';
        });
    }
</script>

<?php include 'includes/footer.php'; ?>