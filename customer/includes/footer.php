<?php
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;

unset($_SESSION['success'], $_SESSION['error']);
?>

<footer class="site-footer">
    <div class="container">
        <div>
            <div class="footer-brand">
                <img src="../../images/logo.png" alt="">
                <div>
                    <div class="footer-title">KAMAL CAR WASH</div>
                    <div class="footer-copy">
                        Clean cars, simple bookings, dependable service.
                    </div>
                </div>
            </div>

            <p class="footer-copy">
                Professional car-care service in Seremban with a customer portal
                built for quick reservations and easy vehicle management.
            </p>
        </div>

        <div>
            <div class="footer-heading">Opening Hours</div>
            <ul class="footer-list">
                <li>Monday–Friday: 8:00 AM–6:00 PM</li>
                <li>Saturday: 8:00 AM–6:00 PM</li>
                <li>Sunday: Closed</li>
            </ul>
        </div>

        <div>
            <div class="footer-heading">Contact</div>
            <ul class="footer-list">
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    Seremban, Negeri Sembilan
                </li>
                <li>
                    <a href="mailto:kamalcarwash@gmail.com">
                        <i class="fa-solid fa-envelope"></i>
                        kamalcarwash@gmail.com
                    </a>
                </li>
                <li>
                    <a href="contact.php">
                        <i class="fa-solid fa-message"></i>
                        Contact us
                    </a>
                </li>
            </ul>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Kamal Car Wash. All rights reserved.</span>
            <span>More Than a Wash, It's a Revival.</span>
        </div>
    </div>
</footer>

<?php if ($flashSuccess): ?>
    <div
        data-kcw-toast="<?= htmlspecialchars($flashSuccess) ?>"
        data-kcw-toast-title="Done"
        data-kcw-toast-tone="success"
    ></div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div
        data-kcw-toast="<?= htmlspecialchars($flashError) ?>"
        data-kcw-toast-title="Something needs attention"
        data-kcw-toast-tone="danger"
    ></div>
<?php endif; ?>

<link rel="stylesheet" href="../../css/customer-motion.css">
<script src="../../js/customer-motion.js"></script>

</body>
</html>
