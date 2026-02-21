<?php
include 'includes/db_connect.php';
include 'includes/header.php';

$message = "";

// --- 1. HANDLE VOLUNTEER REGISTRATION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join_campaign'])) {
    $camp_id = $_POST['campaign_id'];
    $name = $_POST['v_name'];
    $phone = $_POST['v_phone'];
    $email = $_POST['v_email'];

    $stmt = $conn->prepare("INSERT INTO campaign_volunteers (campaign_id, name, phone, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $camp_id, $name, $phone, $email);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success border-0 shadow-lg fixed-top m-3 rounded-pill text-center animate__animated animate__bounceInDown' style='z-index: 1050; background: #d1e7dd; color: #0f5132;'><i class='fa-solid fa-circle-check me-2'></i> <strong>Success!</strong> You are registered.</div>";
    }
}

// --- 2. SEARCH LOGIC ---
$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$sql = "SELECT * FROM campaigns WHERE event_date >= CURDATE()";

if (!empty($search_query)) {
    $sql .= " AND (title LIKE '%$search_query%' OR location LIKE '%$search_query%' OR organizer LIKE '%$search_query%')";
}
$sql .= " ORDER BY event_date ASC";
$result = $conn->query($sql);

// --- 3. FETCH NEXT EVENT FOR COUNTDOWN ---
$next_event = $conn->query("SELECT * FROM campaigns WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 1")->fetch_assoc();
?>

<style>
    /* --- HERO HEADER --- */
    .camp-header {
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1559027615-cd4628902d4a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
        background-attachment: fixed;
        background-size: cover;
        background-position: center;
        padding: 140px 0 180px;
        color: white;
        text-align: center;
        position: relative;
    }

    /* --- SEARCH BAR (Floating) --- */
    .search-box-container {
        margin-top: -35px;
        z-index: 20;
        position: relative;
    }
    .search-input {
        border: none;
        border-radius: 50px 0 0 50px;
        padding: 15px 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .search-btn {
        border-radius: 0 50px 50px 0;
        padding: 0 30px;
        background: #800000;
        color: white;
        border: none;
        font-weight: bold;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* --- COUNTDOWN TIMER --- */
    .countdown-section { margin-bottom: 60px; }
    .timer-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        border-top: 4px solid #800000;
        text-align: center;
    }
    .time-val { font-size: 2.5rem; font-weight: 800; color: #333; font-family: 'Playfair Display', serif; }
    .time-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #999; }

    /* --- EVENT CARDS --- */
    .event-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        transition: 0.3s;
        border: 1px solid #f0f0f0;
        overflow: hidden;
    }
    .event-card:hover { transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.1); border-color: #ffe6e6; }
    
    .event-date-badge {
        background: #800000;
        color: white;
        text-align: center;
        padding: 10px;
        width: 70px;
        border-radius: 0 0 15px 15px;
        position: absolute;
        top: 0; left: 20px;
        box-shadow: 0 5px 15px rgba(128,0,0,0.3);
    }
    .date-num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
    .date-mon { font-size: 0.8rem; text-transform: uppercase; font-weight: 600; }

    /* --- ACCORDION (Preparation Guide) --- */
    .accordion-button:not(.collapsed) {
        color: #800000;
        background-color: #fff5f5;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
    .accordion-button:focus { box-shadow: none; border-color: rgba(128,0,0,.1); }

    /* Animations */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-up { animation: fadeIn 0.8s ease-out; }
</style>

<div class="camp-header">
    <div class="container">
        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3 fw-bold">COMMUNITY IMPACT</span>
        <h1 class="display-3 fw-bold" style="font-family: 'Playfair Display', serif; text-shadow: 2px 2px 10px rgba(0,0,0,0.6);">Blood Donation Drives</h1>
        <p class="lead opacity-90">Find a camp near you and join the mission.</p>
    </div>
</div>

<div class="container search-box-container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="GET">
                <div class="d-flex">
                    <input type="text" name="q" class="form-control search-input" placeholder="Search by City, Location or Organizer..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass me-2"></i> SEARCH</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php echo $message; ?>

<div class="container pb-5">
    <div class="row">
        
        <div class="col-lg-8">
            <h4 class="fw-bold mb-4" style="font-family: 'Playfair Display', serif;"><i class="fa-solid fa-calendar-check me-2 text-danger"></i> Upcoming Campaigns</h4>
            
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        $dateObj = DateTime::createFromFormat('Y-m-d', $row['event_date']);
                    ?>
                    <div class="event-card position-relative mb-4 animate-up">
                        <div class="event-date-badge">
                            <div class="date-mon"><?php echo $dateObj->format('M'); ?></div>
                            <div class="date-num"><?php echo $dateObj->format('d'); ?></div>
                        </div>
                        
                        <div class="p-4 ps-5 ms-4"> <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <small class="text-uppercase text-danger fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">
                                        <i class="fa-solid fa-flag me-1"></i> <?php echo $row['organizer']; ?>
                                    </small>
                                    <h3 class="fw-bold mt-1 text-dark"><?php echo $row['title']; ?></h3>
                                </div>
                                
                                <button class="btn btn-light rounded-circle shadow-sm btn-sm" title="Share">
                                    <i class="fa-solid fa-share-nodes"></i>
                                </button>
                            </div>
                            
                            <p class="text-secondary mt-2 mb-3"><?php echo $row['description']; ?></p>
                            
                            <div class="row g-2 text-muted small mb-4">
                                <div class="col-md-6">
                                    <i class="fa-regular fa-clock me-2 text-warning"></i> 
                                    <?php echo date('h:i A', strtotime($row['start_time'])); ?> - <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                                </div>
                                <div class="col-md-6">
                                    <i class="fa-solid fa-map-location-dot me-2 text-primary"></i> 
                                    <?php echo $row['location']; ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" style="background: #800000; border:none;" 
                                        data-bs-toggle="modal" data-bs-target="#joinModal" 
                                        onclick="setDetails(<?php echo $row['id']; ?>, '<?php echo $row['title']; ?>')">
                                    Volunteer Now
                                </button>
                                
                                <button class="btn btn-outline-dark rounded-pill px-4 fw-bold" 
                                        onclick="openMap('<?php echo $row['location']; ?>')">
                                    <i class="fa-solid fa-map me-2"></i> View Map
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-light rounded-3">
                    <p class="text-muted">No campaigns found matching your search.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            
            <?php if($next_event): ?>
            <div class="timer-card mb-4 animate-up">
                <h6 class="fw-bold text-danger text-uppercase mb-3">Next Big Event</h6>
                <p class="small text-muted mb-4"><?php echo $next_event['title']; ?></p>
                
                <div class="row g-2 justify-content-center">
                    <div class="col-3">
                        <div class="time-val" id="days">0</div><div class="time-label">Days</div>
                    </div>
                    <div class="col-3">
                        <div class="time-val" id="hours">0</div><div class="time-label">Hrs</div>
                    </div>
                    <div class="col-3">
                        <div class="time-val" id="minutes">0</div><div class="time-label">Mins</div>
                    </div>
                </div>
            </div>
            
            <script>
                var countDownDate = new Date("<?php echo $next_event['event_date'] . ' ' . $next_event['start_time']; ?>").getTime();
                var x = setInterval(function() {
                    var now = new Date().getTime();
                    var distance = countDownDate - now;
                    document.getElementById("days").innerText = Math.floor(distance / (1000 * 60 * 60 * 24));
                    document.getElementById("hours").innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    document.getElementById("minutes").innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                }, 1000);
            </script>
            <?php endif; ?>

            <div class="bg-white p-4 rounded-4 shadow-sm border mb-4 animate-up">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-book-medical me-2 text-success"></i> Donor Guide</h5>
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseOne">
                                Before Donation
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Eat iron-rich foods like spinach, red meat, or beans. Drink plenty of water and get a good night's sleep. Avoid alcohol 24hrs before.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingTwo">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseTwo">
                                On the Day
                            </button>
                        </h2>
                        <div id="flush-collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Wear a shirt with loose sleeves. Bring your ID card. Tell the doctor if you are taking any medication.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingThree">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseThree">
                                Post Donation
                            </button>
                        </h2>
                        <div id="flush-collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body small text-secondary">
                                Drink juice and eat cookies. Avoid heavy lifting for the rest of the day. Keep the bandage on for 4 hours.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="joinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">Register as Volunteer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <p class="text-muted small">Event: <strong class="text-dark" id="modalCampTitle"></strong></p>
                    <input type="hidden" name="campaign_id" id="modalCampId">
                    <input type="hidden" name="join_campaign" value="1">
                    
                    <div class="mb-3"><label class="form-label small fw-bold">Name</label><input type="text" name="v_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Phone</label><input type="text" name="v_phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label small fw-bold">Email</label><input type="email" name="v_email" class="form-control"></div>
                    <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold mt-2">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0">
                <h6 class="fw-bold"><i class="fa-solid fa-location-dot me-2 text-danger"></i> Event Location</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="mapFrame" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
    function setDetails(id, title) {
        document.getElementById('modalCampId').value = id;
        document.getElementById('modalCampTitle').innerText = title;
    }
    
    function openMap(location) {
        // Encodes the location for Google Maps URL
        var mapUrl = "https://maps.google.com/maps?q=" + encodeURIComponent(location) + "&t=&z=13&ie=UTF8&iwloc=&output=embed";
        document.getElementById('mapFrame').src = mapUrl;
        var myModal = new bootstrap.Modal(document.getElementById('mapModal'));
        myModal.show();
    }
</script>

<?php include 'includes/footer.php'; ?>