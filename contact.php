<?php include 'includes/header.php'; ?>

<style>
    .contact-header { background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1516738901171-8eb4fc13bd20?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80'); background-size: cover; padding: 80px 0; text-align: center; color: white; border-bottom: 5px solid #800000; }
    .contact-title { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 3rem; }
    
    .info-box { background: #800000; color: white; padding: 40px; border-radius: 10px; height: 100%; }
    .info-item { display: flex; align-items: flex-start; margin-bottom: 30px; }
    .info-icon { width: 50px; height: 50px; background: #c5a059; color: #800000; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; margin-right: 20px; flex-shrink: 0; }
    
    .form-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    .form-control { padding: 12px; border-radius: 5px; border: 1px solid #ddd; }
    .form-control:focus { border-color: #800000; box-shadow: none; }
    .btn-submit { background: #800000; color: white; padding: 12px 30px; border: none; font-weight: bold; border-radius: 5px; transition: 0.3s; }
    .btn-submit:hover { background: #5a0000; }
</style>

<div class="contact-header">
    <div class="container">
        <h1 class="contact-title">Contact Support</h1>
        <p class="lead">We are here to help. Reach out to us for any queries.</p>
    </div>
</div>

<div class="container my-5">
    <div class="row g-5">
        
        <div class="col-lg-4">
            <div class="info-box">
                <h3 class="fw-bold mb-4">Get In Touch</h3>
                
                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div>
                        <h5 class="fw-bold">Our Office</h5>
                        <p class="mb-0">D-Ground, Faisalabad, Punjab, Pakistan</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div>
                        <h5 class="fw-bold">Phone</h5>
                        <p class="mb-0">+92 300 1234567</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                    <div>
                        <h5 class="fw-bold">Email</h5>
                        <p class="mb-0">support@bloodlinkpro.com</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <h5 class="fw-bold">Working Hours</h5>
                        <p class="mb-0">Mon - Sat: 9:00 AM - 9:00 PM</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="form-box">
                <h3 class="fw-bold text-dark mb-4">Send Us a Message</h3>
                <form>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Your Name</label>
                            <input type="text" class="form-control" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" class="form-control" placeholder="john@example.com" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Subject</label>
                            <input type="text" class="form-control" placeholder="How can we help?" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Message</label>
                            <textarea class="form-control" rows="5" placeholder="Write your message here..." required></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-submit">Send Message</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>