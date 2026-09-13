<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kamal Car Wash</title>

<link rel="stylesheet" href="css/style.css">

<style>

/* =========================
   HERO (MODERN)
========================= */
.hero {
    height: 90vh;
    background: url("assets/hero.jpg") center/cover no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    color: white;
}

.hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.55);
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
}

.hero-content h1 {
    font-size: 60px;
    letter-spacing: 2px;
}

.hero-content p {
    font-size: 18px;
    margin-top: 10px;
    opacity: 0.9;
}

/* CTA BUTTON */
.cta-btn {
    margin-top: 20px;
    display: inline-block;
    padding: 12px 30px;
    background: #2f5aa8;
    color: white;
    text-decoration: none;
    border-radius: 30px;
    font-weight: bold;
}

.cta-btn:hover {
    background: #1f3f7a;
}

/* =========================
   ABOUT (CARD STYLE)
========================= */
.about {
    padding: 80px 10%;
}

.about-title {
    text-align: center;
    font-size: 32px;
    margin-bottom: 40px;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
}

.about-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.about-card h3 {
    color: #2f5aa8;
    margin-bottom: 10px;
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width: 768px) {
    .hero-content h1 {
        font-size: 35px;
    }

    .about-grid {
        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<!-- TOP BAR -->
<div class="top-bar">
    <div>📞 Have any question? Call Us Now</div>
    <div>More Than a Wash, It's a Revival</div>
</div>

<!-- HEADER -->
<header class="header">

    <div class="logo-section">
        <img src="assets/logo.png" class="logo">
        <div>
            <h1>KAMAL CAR WASH</h1>
            <span>More Than a Car Wash</span>
        </div>
    </div>

    <nav class="nav">
        <a href="customer/about.php">ABOUT</a>
        <a href="customer/cust_login.php">LOGIN</a>
        <a href="customer/cust_register.php">REGISTER</a>
        <a href="staff/staff_login.php">STAFF</a>
    </nav>

</header>

<!-- HERO -->
<section class="hero">

    <div class="hero-content">
        <h1>KAMAL CAR WASH</h1>
        <p>Professional Car Care Service — Fast, Clean, Reliable</p>

        <a href="customer/customer_login.php" class="cta-btn">BOOK NOW</a>
    </div>

</section>

<!-- ABOUT -->
<section class="about">

    <div class="about-title">Why Choose Us</div>

    <div class="about-grid">

        <div class="about-card">
            <h3>Fast Service</h3>
            <p>We clean your car quickly without compromising quality.</p>
        </div>

        <div class="about-card">
            <h3>Affordable Price</h3>
            <p>Premium wash at student-friendly pricing.</p>
        </div>

        <div class="about-card">
            <h3>Trusted Quality</h3>
            <p>We treat every car with professional care and attention.</p>
        </div>

    </div>

</section>

</body>
</html>