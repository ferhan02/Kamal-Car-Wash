<?php
session_start();
$pageTitle = "About";
include "../includes/header.php";
?>

<style>
.about-hero {
    position: relative;
    min-height: 480px;
    display: grid;
    align-items: center;
    overflow: hidden;
    background:
        linear-gradient(90deg, rgba(7, 23, 39, .9), rgba(7, 23, 39, .48)),
        url("../../images/hero.jpg") center/cover no-repeat;
    color: #fff;
}
.about-hero-content { max-width: 720px; }
.about-hero h1 {
    margin-top: 10px;
    font-family: 'Oswald', sans-serif;
    font-size: clamp(3rem, 6vw, 5.2rem);
    line-height: .98;
}
.about-hero p {
    max-width: 620px;
    margin-top: 19px;
    color: rgba(255,255,255,.74);
    font-size: 1rem;
}

.story-grid {
    display: grid;
    grid-template-columns: minmax(0, .85fr) minmax(0, 1.15fr);
    gap: 54px;
    align-items: center;
}
.story-visual {
    position: relative;
    min-height: 430px;
    overflow: hidden;
    border-radius: 24px;
    background: linear-gradient(145deg, var(--primary-soft), var(--surface));
    box-shadow: var(--shadow-md);
}
.story-visual img {
    position: absolute;
    left: 50%;
    bottom: 4%;
    width: min(360px, 82%);
    transform: translateX(-50%);
    filter: drop-shadow(0 22px 30px rgba(0,0,0,.14));
}
.story-copy h2 { margin-top: 8px; }
.story-copy > p { margin-top: 18px; color: var(--text-soft); }
.story-points { display: grid; gap: 13px; margin-top: 24px; list-style: none; }
.story-points li {
    display: grid;
    grid-template-columns: 38px 1fr;
    gap: 12px;
    align-items: start;
}
.story-icon {
    display: grid;
    width: 38px;
    height: 38px;
    place-items: center;
    border-radius: 11px;
    background: var(--primary-soft);
    color: var(--primary);
}
.story-points h3 { color: var(--heading); font-size: .9rem; }
.story-points p { margin-top: 2px; color: var(--text-soft); font-size: .8rem; }

.values-section { background: var(--surface-2); }
.values-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    margin-top: 30px;
}
.value-card { padding: 24px; }
.value-icon {
    display: grid;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 13px;
    background: var(--primary-soft);
    color: var(--primary);
}
.value-card h3 { margin-top: 18px; color: var(--heading); font-size: 1rem; }
.value-card p { margin-top: 7px; color: var(--text-soft); font-size: .82rem; }

.cta-band { padding: 66px 0; }
.cta-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    padding: 34px;
    overflow: hidden;
    background:
        linear-gradient(125deg, rgba(22, 67, 112, .96), rgba(61, 117, 169, .86)),
        url("../../images/hero.jpg") center/cover;
    color: #fff;
}
.cta-card h2 {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(1.9rem, 4vw, 3rem);
}
.cta-card p { margin-top: 8px; color: rgba(255,255,255,.72); }
.cta-card .btn-secondary {
    flex: 0 0 auto;
    border-color: rgba(255,255,255,.24);
    background: rgba(255,255,255,.12);
    color: #fff;
    box-shadow: none;
}

@media (max-width: 850px) {
    .story-grid { grid-template-columns: 1fr; gap: 34px; }
    .story-visual { min-height: 350px; }
    .values-grid { grid-template-columns: 1fr; }
}
@media (max-width: 620px) {
    .about-hero { min-height: 400px; }
    .cta-card { align-items: flex-start; flex-direction: column; padding: 26px; }
}
</style>

<section class="about-hero">
    <div class="container about-hero-content">
        <span class="eyebrow" style="color:#b9d5ee;"><i class="fa-solid fa-sparkles"></i> About Kamal Car Wash</span>
        <h1>Car care without the clutter.</h1>
        <p>
            Kamal Car Wash combines a straightforward booking experience with dependable, professional car-care service.
            The customer portal is designed to make every step—from choosing a package to managing your vehicles—clear and easy.
        </p>
    </div>
</section>

<section class="section">
    <div class="container story-grid">
        <div class="story-visual">
            <img src="../../images/reserve.png" alt="Kamal Car Wash booking illustration">
        </div>

        <div class="story-copy">
            <span class="eyebrow"><i class="fa-solid fa-water"></i> Our approach</span>
            <h2 class="section-heading">Simple service, handled with care.</h2>
            <p>
                We focus on a clean experience both on the road and online. Customers can register their vehicles,
                check available reservation times and choose from the wash packages already configured in the system.
            </p>

            <ul class="story-points">
                <li>
                    <div class="story-icon"><i class="fa-solid fa-clock"></i></div>
                    <div><h3>Respect for your time</h3><p>Available booking slots are shown before you commit to a reservation.</p></div>
                </li>
                <li>
                    <div class="story-icon"><i class="fa-solid fa-tags"></i></div>
                    <div><h3>Clear package pricing</h3><p>The customer portal shows the package price before confirmation.</p></div>
                </li>
                <li>
                    <div class="story-icon"><i class="fa-solid fa-car-side"></i></div>
                    <div><h3>Built around your vehicles</h3><p>Multiple vehicles can be kept under one customer account for easier future bookings.</p></div>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="section values-section">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-heart"></i> What matters to us</span>
        <h2 class="section-heading">A cleaner experience from screen to street.</h2>
        <div class="values-grid">
            <article class="card value-card">
                <div class="value-icon"><i class="fa-solid fa-bolt"></i></div>
                <h3>Efficient</h3>
                <p>Fewer unnecessary steps, clear labels and fast access to the actions customers use most.</p>
            </article>
            <article class="card value-card">
                <div class="value-icon"><i class="fa-solid fa-shield"></i></div>
                <h3>Reliable</h3>
                <p>Reservations are tied to the correct account, vehicle and package information in the current system.</p>
            </article>
            <article class="card value-card">
                <div class="value-icon"><i class="fa-solid fa-eye"></i></div>
                <h3>Transparent</h3>
                <p>Important information such as time, vehicle, package and price is visible before a booking is confirmed.</p>
            </article>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container">
        <div class="card cta-card">
            <div>
                <h2>Ready for the next wash?</h2>
                <p><?= isset($_SESSION['cust_id']) ? 'Your vehicles and booking tools are ready in the customer portal.' : 'Create an account or sign in to start booking.' ?></p>
            </div>
            <?php if (isset($_SESSION['cust_id'])): ?>
                <a class="btn btn-secondary" href="booking.php"><i class="fa-solid fa-calendar-plus"></i> Book now</a>
            <?php else: ?>
                <a class="btn btn-secondary" href="../../auth/cust_login.php"><i class="fa-solid fa-right-to-bracket"></i> Customer login</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
