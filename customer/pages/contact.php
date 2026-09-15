<?php
session_start();
$pageTitle = "Contact";
include "../includes/header.php";
?>

<style>
.contact-hero {
    padding: 62px 0 48px;
    border-bottom: 1px solid var(--border);
    background:
        radial-gradient(circle at 82% 8%, color-mix(in srgb, var(--primary) 13%, transparent), transparent 28%),
        var(--surface);
}
.contact-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.contact-card {
    min-height: 200px;
    padding: 24px;
}
.contact-icon {
    display: grid;
    width: 46px;
    height: 46px;
    place-items: center;
    border-radius: 13px;
    background: var(--primary-soft);
    color: var(--primary);
}
.contact-card h2 { margin-top: 18px; color: var(--heading); font-size: 1rem; }
.contact-card p { margin-top: 6px; color: var(--text-soft); font-size: .83rem; }
.contact-card a { display: inline-flex; margin-top: 13px; color: var(--primary); font-size: .8rem; font-weight: 800; text-decoration: none; }

.contact-lower {
    display: grid;
    grid-template-columns: 1.05fr .95fr;
    gap: 24px;
    margin-top: 24px;
}
.hours-card, .help-card { padding: 28px; }
.hours-card h2, .help-card h2 { color: var(--heading); font-size: 1.15rem; }
.hours-card > p, .help-card > p { margin-top: 6px; color: var(--text-soft); font-size: .83rem; }
.hours-list { display: grid; gap: 0; margin-top: 20px; list-style: none; }
.hours-list li {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 13px 0;
    border-bottom: 1px solid var(--border);
    font-size: .83rem;
}
.hours-list li:last-child { border-bottom: 0; }
.hours-list span { color: var(--text-soft); }
.hours-list strong { color: var(--heading); }

.help-steps { display: grid; gap: 12px; margin-top: 20px; }
.help-step {
    display: grid;
    grid-template-columns: 36px 1fr;
    gap: 11px;
    align-items: start;
}
.help-number {
    display: grid;
    width: 34px;
    height: 34px;
    place-items: center;
    border-radius: 10px;
    background: var(--surface-2);
    color: var(--primary);
    font-size: .75rem;
    font-weight: 800;
}
.help-step h3 { color: var(--heading); font-size: .87rem; }
.help-step p { margin-top: 2px; color: var(--text-soft); font-size: .78rem; }

@media (max-width: 850px) {
    .contact-grid { grid-template-columns: 1fr; }
    .contact-lower { grid-template-columns: 1fr; }
}
</style>

<section class="contact-hero">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-comments"></i> Contact</span>
        <h1 class="section-heading">Need help with your visit?</h1>
        <p class="section-copy" style="margin-top:10px;">
            Use the contact details already referenced by the Kamal Car Wash project, or manage your booking directly from the customer portal.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="contact-grid">
            <article class="card contact-card">
                <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
                <h2>Location</h2>
                <p>Seremban, Negeri Sembilan</p>
                <a href="#hours">View opening hours <i class="fa-solid fa-arrow-down" style="margin-left:7px;"></i></a>
            </article>

            <article class="card contact-card">
                <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
                <h2>Email</h2>
                <p>For general enquiries about Kamal Car Wash.</p>
                <a href="mailto:kamalcarwash@gmail.com">kamalcarwash@gmail.com <i class="fa-solid fa-arrow-up-right-from-square" style="margin-left:7px;"></i></a>
            </article>

            <article class="card contact-card">
                <div class="contact-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <h2>Bookings</h2>
                <p>Logged-in customers can check available slots and create reservations online.</p>
                <a href="<?= isset($_SESSION['cust_id']) ? 'booking.php' : '../../auth/cust_login.php' ?>">
                    <?= isset($_SESSION['cust_id']) ? 'Book a wash' : 'Sign in to book' ?>
                    <i class="fa-solid fa-arrow-right" style="margin-left:7px;"></i>
                </a>
            </article>
        </div>

        <div class="contact-lower" id="hours">
            <article class="card hours-card">
                <span class="eyebrow">Opening times</span>
                <h2>Plan your visit</h2>
                <p>The booking slots in the current system run during these operating hours.</p>
                <ul class="hours-list">
                    <li><span>Monday–Friday</span><strong>8:00 AM–6:00 PM</strong></li>
                    <li><span>Saturday</span><strong>8:00 AM–6:00 PM</strong></li>
                    <li><span>Sunday</span><strong>Closed</strong></li>
                </ul>
            </article>

            <article class="card help-card">
                <span class="eyebrow">Quick help</span>
                <h2>Before contacting us</h2>
                <p>Many customer tasks can already be handled inside the portal.</p>
                <div class="help-steps">
                    <div class="help-step">
                        <div class="help-number">01</div>
                        <div><h3>Manage your vehicles</h3><p>Add another vehicle or check the ones already registered to your account.</p></div>
                    </div>
                    <div class="help-step">
                        <div class="help-number">02</div>
                        <div><h3>Check available slots</h3><p>The booking page shows times that are not occupied by active reservations.</p></div>
                    </div>
                    <div class="help-step">
                        <div class="help-number">03</div>
                        <div><h3>View generated receipts</h3><p>Any receipt linked to one of your vehicles can be viewed from the Receipts page.</p></div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
