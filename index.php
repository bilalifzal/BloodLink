<?php
include 'includes/db_connect.php';
include 'includes/header.php';


// Fetch stock levels for each blood group
$stock_query = $conn->query("SELECT blood_group, COUNT(*) as units FROM donors 
                             JOIN donation_history ON donors.id = donation_history.user_id 
                             WHERE donation_history.status = 'Available' 
                             GROUP BY blood_group");
$stock_levels = [];
while($row = $stock_query->fetch_assoc()) {
    $stock_levels[$row['blood_group']] = $row['units'];
}

// Define groups to ensure all show up even if 0
$all_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

// --- FETCH LIVE DATA ---
$donors_count = $conn->query("SELECT id FROM donors")->num_rows;
$requests_count = $conn->query("SELECT id FROM blood_requests")->num_rows;
$camps_count = $conn->query("SELECT id FROM campaigns")->num_rows;
$lives_saved_est = $donors_count * 3; 

// Fetch 3 Recent Urgent Requests
$urgent_sql = "SELECT * FROM blood_requests WHERE status='Pending' ORDER BY urgency DESC, created_at DESC LIMIT 3";
$urgent_res = $conn->query($urgent_sql);
?>

<style>
    /* --- GLOBAL TYPOGRAPHY --- */
    :root {
        --primary-dark: #800000;
        --gold-accent: #c5a059;
        --text-dark: #2c3e50;
        --bg-off-white: #fdfdfd;
    }
    body { background-color: var(--bg-off-white); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
    h1, h2, h3, h4, h5 { font-family: 'Playfair Display', serif; }
    
    /* --- ANIMATIONS --- */
    .reveal-up { opacity: 0; transform: translateY(50px); transition: all 0.8s ease-out; }
    .reveal-up.active { opacity: 1; transform: translateY(0); }
    .reveal-zoom { opacity: 0; transform: scale(0.9); transition: all 1s ease; }
    .reveal-zoom.active { opacity: 1; transform: scale(1); }

    /* --- 1. HERO SECTION (CLASSIC IMAGE) --- */
    .hero-section {
        position: relative;
        height: 100vh;
        width: 100%;
        /* Excellent Classic Image */
        background: linear-gradient(rgba(190, 173, 173, 0.11), rgba(141, 110, 110, 0.57)), url('https://images.unsplash.com/photo-1579154204601-01588f351e67?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed; /* Parallax */
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: white;
    }
    
    .hero-content { 
        position: relative; z-index: 3; 
        border: 1px solid rgba(255,255,255,0.15); padding: 60px 40px;
        backdrop-filter: blur(4px); background: rgba(218, 212, 212, 0);
        box-shadow: 0 30px 60px rgba(0,0,0,0.6);
        max-width: 950px;
        border-radius: 4px;
        margin: 0 15px; /* Mobile safe margin */
    }
    /* Gold Corners */
    .hero-content::before { content: ''; position: absolute; top: -2px; left: -2px; width: 40px; height: 40px; border-top: 4px solid var(--gold-accent); border-left: 4px solid var(--gold-accent); }
    .hero-content::after { content: ''; position: absolute; bottom: -2px; right: -2px; width: 40px; height: 40px; border-bottom: 4px solid var(--gold-accent); border-right: 4px solid var(--gold-accent); }

    /* CLASSIC BUTTONS */
    .btn-classic-gold {
        background: linear-gradient(135deg, #c5a059, #e6c88a);
        color: #800000;
        font-family: 'Playfair Display', serif; /* Classic Font */
        padding: 15px 45px;
        border: none;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        display: inline-flex; align-items: center;
    }
    .btn-classic-gold:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(197, 160, 89, 0.6);
        color: #800000;
        background: white;
    }
    .btn-classic-gold a{
        text-decoration:none;
    }

    .btn-classic-outline {
        background: transparent;
        border: 2px solid white;
        color: white;
        font-family: 'Playfair Display', serif;
        padding: 13px 45px;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: 0.3s;
        display: inline-flex; align-items: center;
    }
    .btn-classic-outline:hover {
        background: white;
        color: var(--primary-dark);
        transform: translateY(-3px);
    }

    /* --- 2. 3D STATS --- */
    .stats-container { margin-top: -80px; position: relative; z-index: 10; }
    .stats-card { background: white; border-radius: 0; padding: 50px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); border-top: 5px solid var(--primary-dark); }
    .stat-num { font-size: 3.5rem; font-weight: 800; color: var(--text-dark); line-height: 1; font-family: 'Playfair Display', serif; }

    /* --- 3. MISSION & SECTIONS --- */
    .mission-highlight { border-left: 5px solid var(--gold-accent); padding-left: 25px; font-style: italic; color: var(--primary-dark); font-weight: 600; margin-top: 30px; font-size: 1.2rem; }
    .req-card { background: white; border: 1px solid #eee; transition: 0.4s; position: relative; height: 100%; padding: 30px; }
    .req-card:hover { transform: translateY(-15px); box-shadow: 0 25px 50px rgba(128,0,0,0.15); border-color: var(--gold-accent); }
    .urgency-ribbon { position: absolute; top: 0; right: 0; background: #dc3545; color: white; font-size: 0.7rem; font-weight: bold; padding: 5px 15px; }
    
    /* --- 4. PROTOCOL & SAFETY --- */
    .protocol-card { background: #2c3e50; color: white; padding: 30px; height: 100%; transition: 0.3s; position: relative; overflow: hidden; }
    .protocol-card:hover { background: var(--primary-dark); transform: scale(1.02); }
    .protocol-num { font-size: 4rem; font-weight: 900; opacity: 0.1; position: absolute; top: 10px; right: 20px; }
    .safety-section { background: #111; color: white; padding: 120px 0; background-image: radial-gradient(#222 1px, transparent 1px); background-size: 30px 30px; }
    .safety-icon { font-size: 3.5rem; color: var(--gold-accent); margin-bottom: 25px; }

    /* --- 5. PARALLAX STRIP --- */
    .impact-parallax {
        background: linear-gradient(rgba(128,0,0,0.8), rgba(128,0,0,0.8)), url('https://images.unsplash.com/photo-1516574187841-69301976e499?ixlib=rb-4.0.3');
        background-attachment: fixed; background-size: cover; padding: 100px 0; color: white; text-align: center;
    }

    /* --- 6. WHY CHOOSE US (CARDS) --- */
    .feature-card { text-align: center; padding: 40px 20px; transition: 0.3s; height: 100%; border: 1px solid #eee; }
    .feature-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.1); border-bottom: 4px solid var(--gold-accent); }
    .feature-icon { font-size: 3rem; color: var(--primary-dark); margin-bottom: 20px; }

    /* --- 7. MARQUEE --- */
    .urgent-ticker { background: var(--primary-dark); color: white; font-size: 0.95rem; padding: 12px 0; position: relative; z-index: 100; border-bottom: 2px solid var(--gold-accent); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
    .ticker-badge { background: white; color: var(--primary-dark); font-weight: 800; font-size: 0.75rem; padding: 2px 10px; border-radius: 4px; margin-right: 10px; text-transform: uppercase; }
    .partner-strip { background: white; padding: 50px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; }
    .partner-item { margin: 0 50px; font-size: 1.8rem; color: #ccc; font-weight: bold; font-family: 'Playfair Display', serif; transition: 0.3s; }
    .partner-item:hover { color: var(--primary-dark); }
</style>

<div class="urgent-ticker">
    <div class="container-fluid">
        <marquee behavior="scroll" direction="left" scrollamount="9" onmouseover="this.stop();" onmouseout="this.start();">
            <span class="mx-5"><span class="ticker-badge">CRITICAL</span> Urgent: O- Blood needed at Allied Hospital</span>
            <span class="mx-5"><span class="ticker-badge">LIVE</span> <?php echo $requests_count; ?> Active Requests in Faisalabad</span>
            <span class="mx-5"><span class="ticker-badge">EVENT</span> Mega Blood Drive This Sunday @ GC University</span>
            <span class="mx-5"><span class="ticker-badge">SYSTEM</span> Smart Match Algorithm is Active</span>
        </marquee>
    </div>
</div>

<div class="hero-section">
    <div class="hero-content container animate-float">
        <h5 class="text-uppercase letter-spacing-2 mb-3" style="color: var(--gold-accent); letter-spacing: 4px; font-weight: 600;">Since 2025</h5>
        <h1 class="display-1 fw-bold mb-4">The Gift of Life<br>Flows Through You.</h1>
        <p class="lead mb-5 opacity-90 mx-auto" style="max-width: 750px; font-weight: 300; font-size: 1.25rem; font-family: 'Playfair Display', serif;">
            Pakistan's premier digital blood bank. Connecting voluntary heroes with patients in critical need. Secure. Fast. Lifesaving.
        </p>
        
        <div class="d-flex justify-content-center gap-3 flex-wrap mb-4">
            <a href="search_donor.php" class="btn-classic-gold">
                <i class="fa-solid fa-magnifying-glass me-2"></i> Find Blood
            </a>
            <a href="register.php" class="btn-classic-outline">
                <i class="fa-solid fa-user-plus me-2"></i> Join Us
            </a>
        </div>
        
        <div class="mt-4 pt-4 border-top border-white-50 d-flex justify-content-center gap-4 text-uppercase small letter-spacing-2 flex-wrap">
            <span><i class="fa-solid fa-check text-warning me-2"></i> Verified</span>
            <span><i class="fa-solid fa-clock text-warning me-2"></i> Instant</span>
            <span><i class="fa-solid fa-shield text-warning me-2"></i> Secure</span>
        </div>
    </div>
</div>

<div class="container stats-container">
    <div class="stats-card reveal-up">
        <div class="row text-center divide-cols">
            <div class="col-md-3 mb-4 mb-md-0">
                <div class="stat-num counter" data-target="<?php echo $donors_count; ?>">0</div>
                <div class="stat-label">Registered Heroes</div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0 border-start">
                <div class="stat-num text-danger counter" data-target="<?php echo $lives_saved_est; ?>">0</div>
                <div class="stat-label">Lives Impacted</div>
            </div>
            <div class="col-md-3 mb-4 mb-md-0 border-start">
                <div class="stat-num counter" data-target="<?php echo $camps_count; ?>">0</div>
                <div class="stat-label">Campaigns Held</div>
            </div>
            <div class="col-md-3 border-start">
                <div class="stat-num text-warning">100%</div>
                <div class="stat-label">Verified Data</div>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 reveal-up">
            <h5 class="text-danger fw-bold text-uppercase letter-spacing-2">Our Philosophy</h5>
            <h2 class="display-4 fw-bold mb-4">Bridging the Gap Between Life and Death</h2>
            <p class="text-muted" style="font-size: 1.1rem; line-height: 1.8;">
                In medical emergencies, time is the biggest enemy. Traditional methods—phone calls, social media—are too slow. 
                BloodLink Pro was built to solve this logistical nightmare.
            </p>
            <p class="text-muted" style="font-size: 1.1rem;">
                We use advanced geolocation matching to connect a patient in need with the nearest available donor in seconds, not hours.
            </p>
            <div class="mission-highlight">
                "We don't just build software; we build the infrastructure of hope for the entire nation."
            </div>
        </div>
        <div class="col-lg-5 offset-lg-1 reveal-zoom" style="transition-delay: 0.2s;">
            <div class="position-relative">
                <div style="position: absolute; top: -20px; right: -20px; width: 100%; height: 100%; border: 3px solid var(--gold-accent); z-index: 0;"></div>
                <img src="https://images.unsplash.com/photo-1579154204601-01588f351e67?ixlib=rb-4.0.3" class="img-fluid shadow-lg position-relative" style="z-index: 1;" alt="Medical Professional">
            </div>
        </div>
    </div>
</div>
<div class="container my-5 reveal">
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-4" style="color: #800000;">
            <i class="fa-solid fa-chart-simple me-2"></i> Live Blood Inventory Status
        </h5>
        <div class="row g-3">
            <?php foreach($all_groups as $group): 
                $units = $stock_levels[$group] ?? 0;
                $status_color = ($units < 2) ? '#dc3545' : (($units < 5) ? '#ffc107' : '#198754');
                $status_text = ($units < 2) ? 'Critical' : (($units < 5) ? 'Low' : 'Stable');
            ?>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 border-start border-4" style="background: #f8f9fa; border-color: <?php echo $status_color; ?> !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="fw-bold mb-0"><?php echo $group; ?></h4>
                        <span class="badge" style="background: <?php echo $status_color; ?>; font-size: 0.7rem;">
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2"><?php echo $units; ?> Units Available</small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="small text-muted mt-3 mb-0">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> 
            Auto-updated based on verified donations.
        </p>
    </div>
</div>
<div class="bg-light py-5">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-end mb-5 reveal-up">
            <div>
                <h5 class="text-danger fw-bold text-uppercase letter-spacing-2">Live Feed</h5>
                <h2 class="display-5 fw-bold text-dark">Urgent Calls</h2>
            </div>
            <a href="search_donor.php" class="btn btn-outline-dark px-4 fw-bold" style="border-radius: 0;">VIEW ALL <i class="fa-solid fa-arrow-right ms-2"></i></a>
        </div>

        <div class="row g-4">
            <?php if ($urgent_res->num_rows > 0): ?>
                <?php while($req = $urgent_res->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 reveal-up">
                        <div class="req-card">
                            <div class="urgency-ribbon"><?php echo $req['urgency']; ?></div>
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow" style="width: 60px; height: 60px; font-size: 1.5rem; font-weight: bold;">
                                    <?php echo $req['blood_group']; ?>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?php echo $req['patient_name']; ?></h5>
                                    <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i> <?php echo $req['city']; ?></small>
                                </div>
                            </div>
                            <div class="p-3 bg-light rounded-0 mb-4 border-start border-3 border-danger">
                                <p class="mb-1 text-dark fw-bold"><?php echo $req['hospital_name']; ?></p>
                                <p class="mb-0 text-muted small"><?php echo $req['reason']; ?></p>
                            </div>
                            <div class="d-grid">
                                <a href="tel:<?php echo $req['contact_number']; ?>" class="btn btn-danger fw-bold py-2" style="background: var(--primary-dark); border-radius: 0;">
                                    CONTACT DONOR
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 reveal-up">
                    <h4 class="text-muted">No Critical Cases Currently.</h4>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container my-5 py-5">
    <div class="text-center mb-5 reveal-up">
        <h5 class="text-danger fw-bold text-uppercase">The Advantage</h5>
        <h2 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif;">Why BloodLink Pro?</h2>
        <p class="text-muted">Technology meets Compassion.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-3 reveal-up">
            <div class="feature-card">
                <i class="fa-solid fa-bolt feature-icon"></i>
                <h4 class="fw-bold">Fastest Match</h4>
                <p class="text-muted small">Our algorithm finds donors in your exact location within seconds.</p>
            </div>
        </div>
        <div class="col-md-3 reveal-up" style="transition-delay: 0.1s;">
            <div class="feature-card">
                <i class="fa-solid fa-user-shield feature-icon"></i>
                <h4 class="fw-bold">Secure Data</h4>
                <p class="text-muted small">Your contact info is private. You decide when to share it.</p>
            </div>
        </div>
        <div class="col-md-3 reveal-up" style="transition-delay: 0.2s;">
            <div class="feature-card">
                <i class="fa-solid fa-notes-medical feature-icon"></i>
                <h4 class="fw-bold">Health Log</h4>
                <p class="text-muted small">Track your donation history and eligibility dates automatically.</p>
            </div>
        </div>
        <div class="col-md-3 reveal-up" style="transition-delay: 0.3s;">
            <div class="feature-card">
                <i class="fa-solid fa-award feature-icon"></i>
                <h4 class="fw-bold">Recognition</h4>
                <p class="text-muted small">Earn Gold and Silver badges and certificates for your service.</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 py-5">
    <div class="text-center mb-5 reveal-up">
        <h5 class="text-danger fw-bold text-uppercase">While You Wait</h5>
        <h2 class="display-5 fw-bold">Emergency Protocol</h2>
        <p class="text-muted">What to do while waiting for a donor.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4 reveal-up">
            <div class="protocol-card">
                <div class="protocol-num">01</div>
                <h4 class="fw-bold mb-3"><i class="fa-solid fa-file-medical text-warning me-2"></i> Prepare</h4>
                <p class="small opacity-75">Have the patient's file number, exact ward location, and cross-match sample ready.</p>
            </div>
        </div>
        <div class="col-md-4 reveal-up" style="transition-delay: 0.1s;">
            <div class="protocol-card">
                <div class="protocol-num">02</div>
                <h4 class="fw-bold mb-3"><i class="fa-solid fa-users text-warning me-2"></i> Backup</h4>
                <p class="small opacity-75">Ask family members to be tested. Even if not a match, they can do a 'swap donation'.</p>
            </div>
        </div>
        <div class="col-md-4 reveal-up" style="transition-delay: 0.2s;">
            <div class="protocol-card">
                <div class="protocol-num">03</div>
                <h4 class="fw-bold mb-3"><i class="fa-solid fa-share-nodes text-warning me-2"></i> Share</h4>
                <p class="small opacity-75">Use our 'Share Request' button to post the case on WhatsApp groups instantly.</p>
            </div>
        </div>
    </div>
</div>

<div class="impact-parallax">
    <div class="container reveal-zoom">
        <i class="fa-solid fa-heart-pulse fa-4x mb-4 text-warning"></i>
        <h2 class="display-4 fw-bold mb-3">One Pint. Three Lives.</h2>
        <p class="lead mb-5 opacity-90">The blood you donate gives someone another birthday, another anniversary, another chance.</p>
        <a href="register.php" class="btn btn-outline-light px-5 py-3 fw-bold rounded-0">START YOUR JOURNEY</a>
    </div>
</div>

<div class="safety-section">
    <div class="container text-center reveal-up">
        <h5 class="text-white-50 fw-bold text-uppercase letter-spacing-2">Our Commitment</h5>
        <h2 class="display-5 fw-bold mb-5">Medical Safety First</h2>
        <div class="row g-5">
            <div class="col-md-4">
                <i class="fa-solid fa-pump-medical safety-icon"></i>
                <h4 class="fw-bold">100% Sterile</h4>
                <p class="text-white-50">Single-use needles only.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-user-nurse safety-icon"></i>
                <h4 class="fw-bold">Expert Care</h4>
                <p class="text-white-50">Screening by professionals.</p>
            </div>
            <div class="col-md-4">
                <i class="fa-solid fa-database safety-icon"></i>
                <h4 class="fw-bold">Secure Data</h4>
                <p class="text-white-50">Encrypted personal history.</p>
            </div>
        </div>
    </div>
</div>

<div class="container my-5 py-5 reveal-up">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5">Frequently Asked Questions</h2>
    </div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Who can donate?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Healthy adults 18-60 years, >50kg.</div></div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Is it safe?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Yes, completely safe and sterile.</div></div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">How often can I donate?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Every 56 days for whole blood.</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="partner-strip">
    <div class="container">
        <p class="text-center text-muted text-uppercase small fw-bold mb-4 letter-spacing-2">Trusted By Healthcare Institutes</p>
        <marquee behavior="scroll" direction="left" scrollamount="6">
            <span class="partner-item"><i class="fa-solid fa-hospital"></i> Allied Hospital</span>
            <span class="partner-item"><i class="fa-solid fa-star-of-life"></i> Red Crescent</span>
            <span class="partner-item"><i class="fa-solid fa-notes-medical"></i> DHQ Faisalabad</span>
            <span class="partner-item"><i class="fa-solid fa-user-doctor"></i> FIC Hospital</span>
            <span class="partner-item"><i class="fa-solid fa-heart-pulse"></i> Sundas Foundation</span>
        </marquee>
    </div>
</div>

<script>
    // 1. Scroll Reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
    });
    document.querySelectorAll('.reveal-up, .reveal-zoom').forEach(el => observer.observe(el));

    // 2. Counter
    document.querySelectorAll('.counter').forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText;
            const inc = target / 200;
            if(count < target) { counter.innerText = Math.ceil(count + inc); setTimeout(updateCount, 20); }
            else { counter.innerText = target + "+"; }
        };
        updateCount();
    });
</script>

<?php include 'includes/footer.php'; ?>