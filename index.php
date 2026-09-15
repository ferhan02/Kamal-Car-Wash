<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10243a">
    <title>Kamal Car Wash</title>

    <script>
        (() => {
            const saved = localStorage.getItem('kamal-theme');
            const dark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.dataset.theme = saved || (dark ? 'dark' : 'light');
        })();
    </script>

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <script defer src="js/customer.js"></script>

    <style>
    .landing-header {
        position: absolute;
        top: 34px;
        left: 0;
        right: 0;
        z-index: 20;
    }
    .landing-nav {
        display: flex;
        min-height: 70px;
        align-items: center;
        gap: 24px;
        padding: 10px 14px 10px 18px;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 18px;
        background: rgba(9, 25, 42, .74);
        backdrop-filter: blur(16px);
        color: #fff;
    }
    .landing-brand {
        display: flex;
        align-items: center;
        gap: 11px;
        color: #fff;
        text-decoration: none;
    }
    .landing-brand img { width: 46px; height: 46px; object-fit: contain; }
    .landing-brand strong { font-family: 'Oswald', sans-serif; letter-spacing: .04em; }
    .landing-links { display: flex; align-items: center; gap: 6px; margin-left: auto; }
    .landing-links a {
        padding: 8px 10px;
        border-radius: 9px;
        color: rgba(255,255,255,.76);
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
    }
    .landing-links a:hover { background: rgba(255,255,255,.1); color: #fff; }
    .landing-nav .icon-btn {
        border-color: rgba(255,255,255,.16);
        background: rgba(255,255,255,.08);
        color: #fff;
        box-shadow: none;
    }

    .landing-hero {
        position: relative;
        min-height: 760px;
        display: grid;
        align-items: center;
        overflow: hidden;
        background:
            linear-gradient(90deg, rgba(5,18,31,.91) 0%, rgba(5,18,31,.67) 48%, rgba(5,18,31,.28) 100%),
            url("images/hero.jpg") center/cover no-repeat;
        color: #fff;
    }
    .landing-content {
        position: relative;
        z-index: 2;
        max-width: 760px;
        padding-top: 90px;
    }
    .landing-content h1 {
        max-width: 740px;
        margin-top: 13px;
        font-family: 'Oswald', sans-serif;
        font-size: clamp(3.6rem, 8vw, 7rem);
        line-height: .9;
        letter-spacing: -.035em;
    }
    .landing-content h1 span { color: #9fc5e8; }
    .landing-content p {
        max-width: 600px;
        margin-top: 24px;
        color: rgba(255,255,255,.72);
        font-size: 1rem;
    }
    .landing-actions { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 30px; }
    .landing-actions .btn-secondary {
        border-color: rgba(255,255,255,.22);
        background: rgba(255,255,255,.08);
        color: #fff;
        box-shadow: none;
    }

    .feature-section { padding: 78px 0; }
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin-top: 30px;
    }
    .feature-card { padding: 25px; }
    .feature-icon {
        display: grid;
        width: 46px;
        height: 46px;
        place-items: center;
        border-radius: 13px;
        background: var(--primary-soft);
        color: var(--primary);
    }
    .feature-card h3 { margin-top: 17px; color: var(--heading); font-size: 1rem; }
    .feature-card p { margin-top: 7px; color: var(--text-soft); font-size: .82rem; }

    .portal-section { padding: 72px 0; background: var(--surface-2); }
    .portal-card {
        display: grid;
        grid-template-columns: 1fr .8fr;
        min-height: 390px;
        overflow: hidden;
    }
    .portal-copy { padding: 42px; align-self: center; }
    .portal-copy h2 { margin-top: 8px; }
    .portal-copy p { margin-top: 14px; color: var(--text-soft); }
    .portal-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
    .portal-visual {
        display: grid;
        place-items: center;
        background: linear-gradient(145deg, var(--primary-soft), var(--surface));
    }
    .portal-visual img { width: min(340px, 74%); filter: drop-shadow(0 24px 34px rgba(0,0,0,.14)); }

    @media (max-width: 850px) {
        .landing-header { top: 14px; }
        .landing-links a:not(.login-link) { display: none; }
        .feature-grid { grid-template-columns: 1fr; }
        .portal-card { grid-template-columns: 1fr; }
        .portal-visual { min-height: 280px; order: -1; }
    }
    @media (max-width: 540px) {
        .landing-hero { min-height: 680px; }
        .landing-brand strong { display: none; }
        .landing-content h1 { font-size: clamp(3.3rem, 17vw, 5.2rem); }
        .portal-copy { padding: 27px; }
    }
    </style>
</head>
<body>
<section class="landing-hero">
    <div class="landing-header">
        <div class="container landing-nav">
            <a class="landing-brand" href="index.php">
                <img src="images/logo.png" alt="Kamal Car Wash logo">
                <strong>KAMAL CAR WASH</strong>
            </a>

            <nav class="landing-links">
                <a href="customer/pages/about.php">About</a>
                <a href="customer/pages/contact.php">Contact</a>
                <a href="auth/staff_login.php">Staff</a>
                <a class="login-link" href="auth/cust_login.php">Customer login</a>
            </nav>

            <button class="icon-btn" type="button" data-theme-toggle aria-label="Switch theme">
                <i class="fa-solid fa-moon"></i>
            </button>
        </div>
    </div>

    <div class="container landing-content">
        <span class="eyebrow" style="color:#b9d5ee;"><i class="fa-solid fa-droplet"></i> Seremban car care</span>
        <h1>Drive clean. <span>Book smarter.</span></h1>
        <p>Professional car care with a customer portal built around fast reservations, clear package pricing and easy vehicle management.</p>
        <div class="landing-actions">
            <a class="btn btn-primary" href="auth/cust_login.php"><i class="fa-solid fa-calendar-check"></i> Book a wash</a>
            <a class="btn btn-secondary" href="auth/cust_register.php"><i class="fa-solid fa-user-plus"></i> Create account</a>
        </div>
    </div>
</section>

<section class="feature-section">
    <div class="container">
        <span class="eyebrow">Why Kamal Car Wash</span>
        <h2 class="section-heading">Clean design. Clear service.</h2>
        <div class="feature-grid">
            <article class="card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-bolt"></i></div>
                <h3>Fast reservations</h3>
                <p>Choose an available date and time without waiting in a physical queue.</p>
            </article>
            <article class="card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-car-side"></i></div>
                <h3>Vehicle management</h3>
                <p>Keep the vehicles you regularly bring to Kamal Car Wash under one account.</p>
            </article>
            <article class="card feature-card">
                <div class="feature-icon"><i class="fa-solid fa-tags"></i></div>
                <h3>Up-front pricing</h3>
                <p>See the package price before the reservation is confirmed.</p>
            </article>
        </div>
    </div>
</section>

<section class="portal-section">
    <div class="container">
        <div class="card portal-card">
            <div class="portal-copy">
                <span class="eyebrow"><i class="fa-solid fa-user"></i> Customer portal</span>
                <h2 class="section-heading">Everything important in one place.</h2>
                <p>Register a vehicle, create bookings, update your profile and view receipts generated for your vehicles.</p>
                <div class="portal-actions">
                    <a class="btn btn-primary" href="auth/cust_register.php">Get started</a>
                    <a class="btn btn-secondary" href="customer/pages/about.php">Learn more</a>
                </div>
            </div>
            <div class="portal-visual">
                <img src="images/reserve.png" alt="Car wash reservation illustration">
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <div>
            <div class="footer-brand">
                <img src="images/logo.png" alt="">
                <div>
                    <div class="footer-title">KAMAL CAR WASH</div>
                    <div class="footer-copy">More Than a Wash, It's a Revival.</div>
                </div>
            </div>
        </div>
        <div>
            <div class="footer-heading">Opening Hours</div>
            <ul class="footer-list">
                <li>Mon–Sat: 8:00 AM–6:00 PM</li>
                <li>Sunday: Closed</li>
            </ul>
        </div>
        <div>
            <div class="footer-heading">Customer</div>
            <ul class="footer-list">
                <li><a href="auth/cust_login.php">Login</a></li>
                <li><a href="auth/cust_register.php">Register</a></li>
            </ul>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> Kamal Car Wash.</span>
            <span>Seremban, Negeri Sembilan</span>
        </div>
    </div>
</footer>
</body>
</html>
