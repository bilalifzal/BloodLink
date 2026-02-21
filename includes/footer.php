<style>
    .footer-section {
        background-color: #1a1a1a;
        color: #b0b0b0;
        padding: 60px 0 20px;
        font-size: 0.9rem;
    }
    .footer-title {
        color: white;
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 1.2rem;
    }
    .footer-links li {
        margin-bottom: 10px;
    }
    .footer-links a {
        color: #b0b0b0;
        text-decoration: none;
        transition: 0.3s;
    }
    .footer-links a:hover {
        color: #d4af37; /* Gold on hover */
        padding-left: 5px;
    }
    .footer-bottom {
        border-top: 1px solid #333;
        margin-top: 40px;
        padding-top: 20px;
        text-align: center;
    }
    .newsletter-input {
        background: #333;
        border: none;
        color: white;
        padding: 10px 15px;
        border-radius: 5px 0 0 5px;
        width: 70%;
    }
    .newsletter-btn {
        background: #800000;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 0 5px 5px 0;
        font-weight: 600;
    }

    /* NEW: Red WhatsApp Button Style */
    .whatsapp-btn-footer {
        background: #800000; /* Matching your website's Burgundy/Red */
        color: white !important;
        padding: 12px 25px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
        text-decoration: none !important;
        font-weight: 700;
        margin-top: 15px;
        transition: 0.3s;
        box-shadow: 0 5px 15px rgba(128, 0, 0, 0.3);
    }
    .whatsapp-btn-footer:hover {
        background: #a00000;
        transform: translateY(-3px);
    }
</style>

<footer class="footer-section mt-auto">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title">BloodLink Pro</h5>
                <p>We are a high-value community initiative designed to connect blood donors with patients in critical need. Fast, secure, and reliable.</p>
                
             
                
                <div class="mt-4">
                    <a href="https://facebook.com/" class="text-white me-3"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://twitter.com/bilalifzal" class="text-white me-3"><i class="fa-brands fa-twitter"></i></a>
                    <a href="https://instagram.com/" class="text-white me-3"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://www.linkedin.com/in/muhammad-bilal-ifzal-a82649375/" class="text-white"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                   <a href="https://wa.me/923260102121" target="_blank" class="whatsapp-btn-footer">
                    <i class="fa-brands fa-whatsapp me-2 fs-5"></i> Contact With Bilal Ifzal
                </a>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">Quick Links</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="search_donor.php">Find Blood</a></li>
                    <li><a href="register.php">Register as Donor</a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">Support</h5>
                <ul class="list-unstyled footer-links">
                    <li><a href="privacy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="cookie_policy.php">Cookie Policy</a></li>
                    <li><a href="contact.php">Contact Support</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title">Stay Updated</h5>
                <p>Subscribe to our newsletter for the latest campaigns and health tips.</p>
                <form class="d-flex">
                    <input type="email" class="newsletter-input" placeholder="Enter your email">
                    <button class="newsletter-btn" type="button">Join</button>
                </form>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="mb-1">&copy; <?php echo date("Y"); ?> BloodLink Pro Management System. All rights reserved. (Admin Access on Bilal Ifzal)</p>
            <p class="small opacity-75">Developed by: <strong class="text-white">Muhammad Bilal Ifzal</strong></p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>