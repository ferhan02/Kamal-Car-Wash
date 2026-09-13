<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Home | Kamal Car Wash</title>

    <link rel="stylesheet" href="../../css/style.css">

    <style>
        /* ==========================================
           TOP BAR FIX (Bahagian Biru Paling Atas)
        ========================================== */
        .top-bar {
            background: #6a8caf; /* Warna biru lembut macam Gambar 2 */
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 40px;
            font-size: 13px;
            font-family: Arial, sans-serif;
        }

        .top-bar .left-side {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ==========================================
           NAVBAR FIXES (Logo Kiri, Menu Kanan)
        ========================================== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: ##eaf2fb;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Mengawal saiz logo supaya tidak besar gergasi */
        .logo-section .logo {
            width: 60px; 
            height: auto;
        }

        .logo-section h1 {
            font-size: 20px;
            margin: 0;
            color: #1f2a44;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .logo-section span {
            font-size: 12px;
            color: #777;
            display: block;
        }

        .nav {
            display: flex;
            gap: 25px;
        }

        .nav a {
            text-decoration: none;
            color: #1f2a44;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s ease;
        }

        .nav a:hover {
            color: #4f8cff;
        }

        /* ==========================================
           HOME HERO SECTION (Gambar Penuh Dalam Kotak)
        ========================================== */
        .home-hero {
            height: 70vh; /* Ketinggian ngam-ngam sekotak cantik */
            background: url("../../images/background1.jpg") center center / cover no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            text-align: center;
            color: white;
            width: 100%;
        }

        /* Gelapkan sikit latar belakang supaya tulisan putih senang dibaca */
        .home-hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6); 
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 700px;
            padding: 0 20px;
        }

        .hero-content h1 {
            font-size: 45px;
            margin-bottom: 15px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        /* BOOK NOW BUTTON */
        .book-btn {
            display: inline-block;
            padding: 15px 35px;
            background: #4f8cff;
            color: white;
            font-weight: bold;
            border-radius: 30px;
            text-decoration: none;
            font-size: 16px;
            transition: 0.3s ease;
            box-shadow: 0 4px 15px rgba(79, 140, 255, 0.4);
        }

        .book-btn:hover {
            background: #2f5aa8;
            transform: scale(1.05);
        }
        /* ==========================================
           QUICK INFO SECTION (Kad Kelebihan)
        ========================================== */
        .info-section {
            padding: 60px 40px;
            background: #eaf2fb;
            text-align: center;
        }

        .info-title {
            font-size: 28px;
            color: #1f2a44;
            margin-bottom: 40px;
            font-weight: bold;
        }

        .info-grid {
            display: flex;
            gap: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .info-card {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            width: 280px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            margin-bottom: 12px;
            color: #2f5aa8;
            font-size: 20px;
        }

        .info-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }

        /* ==========================================
           FOOTER SECTION
        ========================================== */
        .footer {
            text-align: center;
            padding: 20px;
            background: #1f2a44;
            color: white;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div class="left-side">
        📞 Have any question? Call Us Now
    </div>
    <div class="right-side">
        More Than a Wash, It's a Revival
    </div>
</div>

<header class="header">
    <div class="logo-section">
        <img src="../../images/logo.png" class="logo" alt="Logo">
        <div>
            <h1>KAMAL CAR WASH</h1>
            <span>Customer Portal</span>
        </div>
    </div>

    <nav class="nav">
    <a href="cust_home.php">HOME</a>
    <a href="vehicle.php" class="home-btn">Vehicle Management</a>
    <a href="../../auth/logout.php" onclick="return confirm('Are you sure you want to log out?');">LOGOUT</a>
</nav>
</header>

<section class="home-hero">
    <div class="hero-content">
        <h1>Eco-Friendly Car Cleaning Services at Kamal Car Wash</h1>
        <p>Your car deserves the best care. Book your wash appointment now and let us bring back the shine ✨</p>
        <a href="booking.php" class="book-btn">BOOK NOW</a>
    </div>
</section>

<section class="info-section">
    <div class="info-title">Why Choose Us?</div>

    <div class="info-grid">
        <div class="info-card">
            <h3>Fast Service</h3>
            <p>We clean your car efficiently without compromising quality.</p>
        </div>

        <div class="info-card">
            <h3>Affordable Price</h3>
            <p>Premium car wash service at budget-friendly rates.</p>
        </div>

        <div class="info-card">
            <h3>Trusted Quality</h3>
            <p>Professional care ensuring your car always looks brand new.</p>
        </div>
    </div>
</section>

<footer class="footer">
    <p>&copy; 2026 Kamal Car Wash. All Rights Reserved.</p>
</footer>

</body>
</html>