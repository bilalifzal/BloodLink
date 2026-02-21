<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Fetch real stats
$donors_count = $conn->query("SELECT id FROM donors")->num_rows;
$requests_count = $conn->query("SELECT id FROM blood_requests")->num_rows;
$camps_count = $conn->query("SELECT id FROM campaigns")->num_rows;
?>

<style>
    /* --- 1. GLOBAL ANIMATION SETTINGS --- */
    .hidden-element, .reveal {
        opacity: 0;
        transform: translateY(50px);
        transition: all 1s ease-out;
    }
    .show-element, .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* --- 2. PARALLAX HERO --- */
    .about-hero {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?ixlib=rb-4.0.3');
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        padding: 160px 0;
        text-align: center;
        color: white;
    }

    /* --- 3. IMPACT MARQUEE --- */
    .impact-ticker {
        background: #800000;
        color: white;
        padding: 15px 0;
        font-family: 'Playfair Display', serif;
        font-size: 1.2rem;
        letter-spacing: 1px;
    }

    /* --- 4. HISTORY TIMELINE --- */
    .timeline-section {
        position: relative;
        padding: 80px 0;
    }
    .timeline-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #f0f0f0;
        transform: translateX(-50%);
    }
    .timeline-item {
        margin-bottom: 60px;
        position: relative;
    }
    .timeline-dot {
        width: 20px; height: 20px;
        background: #800000;
        border: 4px solid #fff;
        border-radius: 50%;
        position: absolute;
        left: 50%;
        top: 0;
        transform: translateX(-50%);
        z-index: 2;
        box-shadow: 0 0 0 4px rgba(128,0,0,0.2);
    }
    .timeline-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        width: 45%;
        border-top: 4px solid #c5a059; /* Gold Accent */
    }
    .timeline-left { margin-right: auto; text-align: right; }
    .timeline-right { margin-left: auto; text-align: left; }

    /* --- 5. TRUSTED BY MARQUEE --- */
    .hospital-ticker-wrap {
        background: #f8f9fa;
        padding: 40px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }
    .hospital-logo {
        display: inline-block;
        font-size: 1.5rem;
        font-weight: bold;
        color: #555;
        margin: 0 60px;
        font-family: 'Playfair Display', serif;
    }
    .hospital-logo i { color: #800000; margin-right: 10px; }

    /* --- 6. DEVELOPER CARD --- */
    .dev-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        transition: 0.4s;
    }
    .dev-card:hover { transform: translateY(-10px); }
    .dev-header { height: 150px; background: linear-gradient(135deg, #1a1a1a, #4a4a4a); }
    .dev-img {
        width: 140px; height: 140px;
        border-radius: 50%;
        border: 5px solid white;
        margin: -70px auto 20px;
        background: white;
        overflow: hidden;
    }
    .dev-img img { width: 100%; height: 100%; object-fit: cover; }

    /* --- NEW: ARCHITECT SECTION STYLES --- */
    .architect-section { padding: 80px 0; background-color: #fff; }
    .glass-profile {
        background: white;
        border-radius: 40px;
        padding: 60px;
        box-shadow: 0 30px 70px rgba(0,0,0,0.1);
        border-left: 8px solid #800000;
        position: relative;
    }
    .profile-frame {
        width: 200px; height: 200px;
        border-radius: 50%;
        border: 5px solid #d4af37;
        padding: 8px;
        margin: 0 auto 30px;
        background: #fff;
    }
    .profile-frame img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }

    @media (max-width: 768px) {
        .timeline-line { left: 20px; }
        .timeline-dot { left: 20px; }
        .timeline-content { width: 85%; margin-left: 50px; text-align: left; }
        .glass-profile { padding: 30px; }
    }
</style>

<div class="about-hero">
    <div class="container hidden-element">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fw-bold tracking-wide">ESTABLISHED 2025</span>
        <h1 class="display-2 fw-bold" style="font-family: 'Playfair Display', serif;">The Heart of Humanity</h1>
        <p class="lead opacity-90 mt-3" style="font-size: 1.25rem;">Connecting drops of blood to oceans of hope.</p>
    </div>
</div>

<div class="impact-ticker">
    <marquee behavior="scroll" direction="left" scrollamount="10">
        <span class="mx-5"><i class="fa-solid fa-heart me-2"></i> <?php echo $donors_count; ?>+ Registered Heroes</span>
        <span class="mx-5"><i class="fa-solid fa-bed-pulse me-2"></i> <?php echo $requests_count; ?>+ Lives Impacted</span>
        <span class="mx-5"><i class="fa-solid fa-hand-holding-medical me-2"></i> <?php echo $camps_count; ?>+ Community Drives</span>
        <span class="mx-5"><i class="fa-solid fa-city me-2"></i> Serving All Major Cities</span>
        <span class="mx-5"><i class="fa-solid fa-shield-halved me-2"></i> 100% Verified & Secure</span>
    </marquee>
</div>

<div class="container my-5 py-5">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-5 mb-lg-0 hidden-element">
            <h5 class="text-danger fw-bold text-uppercase">Our Mission</h5>
            <h2 class="display-5 fw-bold text-dark mb-4" style="font-family: 'Playfair Display', serif;">Digitizing the Act of Saving Lives</h2>
            <p class="text-secondary lead" style="line-height: 1.8;">
                We realized that the problem wasn't a lack of blood, but a lack of connection.
            </p>
            <p class="text-muted">
                BloodLink Pro was born from a simple idea: What if finding a blood donor was as easy as calling a cab? We built a bridge between those in need and those willing to give, removing the panic from emergencies.
            </p>
            <div class="mt-4 border-start border-4 border-danger ps-4">
                <p class="fst-italic text-dark fw-bold">"Blood is the only medicine that cannot be manufactured. It must come from the generosity of another human."</p>
            </div>
        </div>
        <div class="col-lg-6 ps-lg-5 hidden-element" style="transition-delay: 0.2s;">
            <div class="position-relative">
                <img src="https://images.unsplash.com/photo-1584515933487-779824d29309?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="w-100 rounded-4 shadow-lg">
                <div class="position-absolute bottom-0 start-0 bg-white p-4 rounded-top-end-4 shadow m-4 border-start border-5 border-warning">
                    <h2 class="fw-bold text-dark mb-0">24/7</h2>
                    <small class="text-muted text-uppercase fw-bold">Active Support</small>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bg-light timeline-section">
    <div class="container">
        <div class="text-center mb-5 hidden-element">
            <h5 class="text-danger fw-bold text-uppercase">Our Journey</h5>
            <h2 class="fw-bold" style="font-family: 'Playfair Display', serif;">How We Started</h2>
        </div>
        
        <div class="position-relative">
            <div class="timeline-line"></div>
            
            <div class="timeline-item d-flex justify-content-between w-100 hidden-element">
                <div class="timeline-content timeline-left">
                    <h4 class="fw-bold text-dark">The Idea Born</h4>
                    <span class="badge bg-secondary mb-2">Jan 2024</span>
                    <p class="text-muted small">Noticed the difficulty people faced finding donors in local hospitals. The concept of a digital directory was conceptualized.</p>
                </div>
                <div class="timeline-dot"></div>
                <div class="w-50"></div> </div>

            <div class="timeline-item d-flex justify-content-between w-100 hidden-element">
                <div class="w-50"></div> <div class="timeline-dot"></div>
                <div class="timeline-content timeline-right">
                    <h4 class="fw-bold text-dark">Beta Launch</h4>
                    <span class="badge bg-secondary mb-2">June 2025</span>
                    <p class="text-muted small">Launched the first version in Faisalabad. Successfully connected 50 donors in the first week.</p>
                </div>
            </div>

            <div class="timeline-item d-flex justify-content-between w-100 hidden-element">
                <div class="timeline-content timeline-left">
                    <h4 class="fw-bold text-dark">Going Global</h4>
                    <span class="badge bg-danger mb-2">Today</span>
                    <p class="text-muted small">BloodLink Pro now serves multiple cities with advanced features like "Smart Match" and "Campaign Management".</p>
                </div>
                <div class="timeline-dot bg-danger"></div>
                <div class="w-50"></div> </div>
        </div>
    </div>
</div>

<div class="hospital-ticker-wrap">
    <div class="container">
        <p class="text-center text-muted text-uppercase small fw-bold mb-4">Trusted by Leading Institutes</p>
        <marquee behavior="scroll" direction="left" scrollamount="8">
            <span class="hospital-logo"><i class="fa-solid fa-hospital"></i> Allied Hospital</span>
            <span class="hospital-logo"><i class="fa-solid fa-hospital-user"></i> DHQ Hospital</span>
            <span class="hospital-logo"><i class="fa-solid fa-star-of-life"></i> Red Crescent</span>
            <span class="hospital-logo"><i class="fa-solid fa-heart-pulse"></i> Shaukat Khanum</span>
            <span class="hospital-logo"><i class="fa-solid fa-notes-medical"></i> Sundas Foundation</span>
            <span class="hospital-logo"><i class="fa-solid fa-user-doctor"></i> FIC Faisalabad</span>
        </marquee>
    </div>
</div>

<section class="architect-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="glass-profile hidden-element">
                    <div class="row align-items-center">
                        <div class="col-md-5 text-center">
                            <div class="profile-frame">
                                <img src="https://ui-avatars.com/api/?name=Muhammad+Bilal+Ifzal&background=800000&color=fff&size=250" alt="Muhammad Bilal Ifzal">
                            </div>
                            <h3 class="fw-bold mb-1" style="color: #800000;">Muhammad Bilal Ifzal</h3>
                            <p class="text-muted text-uppercase small fw-bold tracking-widest">System Architect & Developer</p>
                        </div>
                        <div class="col-md-7 ps-md-5">
                            <h4 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif;">The Visionary Behind BloodLink Pro</h4>
                            <p class="text-secondary mb-4" style="line-height: 1.8;">
                                "I designed BloodLink Pro with a single goal: to ensure that technology serves a purpose higher than itself. By creating a seamless digital bridge for blood donation, we are not just writing code; we are writing a future where no one has to lose a loved one due to blood unavailability."
                            </p>
                            <div class="d-flex gap-3">
                                <a href="#" class="btn btn-outline-dark rounded-pill px-4"><i class="fa-brands fa-github"></i> GitHub</a>
                                <a href="#" class="btn btn-dark rounded-pill px-4" style="background: #800000; border: none;">Contact Me</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<div class="position-relative py-5 mt-5 text-center text-white" 
     style="background: linear-gradient(rgba(88, 0, 0, 0.9), rgba(44, 0, 0, 0.9)), url('https://images.unsplash.com/photo-1615461166324-cd1f91f73756?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; border-top: 5px solid #c5a059;">
    
    <div class="container hidden-element py-5">
        <i class="fa-solid fa-hand-holding-heart fa-4x mb-4 text-warning" style="filter: drop-shadow(0 0 10px rgba(0,0,0,0.5));"></i>
        
        <h2 class="display-5 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Your Blood, Their Life.</h2>
        <p class="lead mb-5 opacity-75 mx-auto" style="max-width: 600px;">
            Join the elite community of heroes who don't wear capes—they roll up their sleeves.
        </p>
        
        <a href="register.php" class="btn btn-outline-light btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg" 
           style="border: 2px solid white; transition: 0.3s;" 
           onmouseover="this.style.background='white'; this.style.color='#800000';" 
           onmouseout="this.style.background='transparent'; this.style.color='white';">
            BECOME A DONOR
        </a>
    </div>
</div>

<script>
    // Simple script to check when elements are in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('show-element');
            }
        });
    }, { threshold: 0.1 });

    const hiddenElements = document.querySelectorAll('.hidden-element');
    hiddenElements.forEach((el) => observer.observe(el));
</script>

<?php include 'includes/footer.php'; ?>