<?php
session_start();
require_once "../../config.php";

if (!isset($_SESSION['cust_id'])) {
    header("Location: ../../auth/cust_login.php");
    exit();
}

$custId = (int) $_SESSION['cust_id'];
$selectedReceiptId = (int) ($_GET['receipt_id'] ?? 0);

$listStmt = $conn->prepare("
    SELECT r.receipt_id, r.receipt_date, r.receipt_time,
           v.vehicle_platenum, v.vehicle_brand, v.vehicle_model,
           p.package_name, p.package_price,
           st.staff_name,
           pay.payment_id
    FROM receipt r
    JOIN vehicle v ON v.vehicle_id = r.vehicle_id
    JOIN package p ON p.package_id = r.package_id
    LEFT JOIN staff st ON st.staff_id = r.staff_id
    LEFT JOIN payment pay ON pay.receipt_id = r.receipt_id
    WHERE v.cust_id = ?
    ORDER BY r.receipt_date DESC, r.receipt_time DESC
");
$listStmt->execute([$custId]);
$receipts = $listStmt->fetchAll();

$selectedReceipt = null;
if ($selectedReceiptId) {
    foreach ($receipts as $receiptRow) {
        if ((int)$receiptRow['receipt_id'] === $selectedReceiptId) {
            $selectedReceipt = $receiptRow;
            break;
        }
    }
}

$pageTitle = "Receipts";
include "../includes/header.php";
?>

<style>
.receipt-hero {
    padding: 54px 0 40px;
    border-bottom: 1px solid var(--border);
    background:
        radial-gradient(circle at 80% 10%, color-mix(in srgb, var(--primary) 12%, transparent), transparent 27%),
        var(--surface);
}
.receipt-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 24px;
    align-items: start;
}
.receipt-list { display: grid; gap: 12px; }
.receipt-card {
    display: grid;
    grid-template-columns: 54px 1fr auto;
    gap: 15px;
    align-items: center;
    padding: 18px;
    text-decoration: none;
    transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.receipt-card:hover {
    transform: translateY(-1px);
    border-color: color-mix(in srgb, var(--primary) 35%, var(--border));
    box-shadow: var(--shadow-md);
}
.receipt-card.active {
    border-color: var(--primary);
    background: var(--primary-soft);
}
.receipt-icon {
    display: grid;
    width: 50px;
    height: 50px;
    place-items: center;
    border-radius: 14px;
    background: var(--surface-2);
    color: var(--primary);
}
.receipt-main h3 { color: var(--heading); font-size: .92rem; }
.receipt-main p { margin-top: 4px; color: var(--text-soft); font-size: .77rem; }
.receipt-price { color: var(--heading); font-weight: 800; white-space: nowrap; }

.receipt-preview {
    position: sticky;
    top: 128px;
    overflow: hidden;
}
.receipt-paper {
    padding: 26px;
    background: var(--surface);
}
.receipt-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 20px;
    border-bottom: 1px dashed var(--border);
}
.receipt-brand img { width: 45px; height: 45px; object-fit: contain; }
.receipt-brand h2 { color: var(--heading); font-family: 'Oswald', sans-serif; font-size: 1.1rem; }
.receipt-brand p { color: var(--text-soft); font-size: .7rem; }
.receipt-number { margin-top: 20px; color: var(--text-soft); font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.receipt-lines { display: grid; gap: 11px; margin-top: 17px; }
.receipt-line { display: flex; justify-content: space-between; gap: 20px; font-size: .78rem; }
.receipt-line span { color: var(--text-soft); }
.receipt-line strong { color: var(--heading); text-align: right; }
.receipt-total {
    display: flex;
    justify-content: space-between;
    align-items: end;
    gap: 15px;
    margin-top: 20px;
    padding-top: 17px;
    border-top: 1px dashed var(--border);
}
.receipt-total span { color: var(--text-soft); font-size: .76rem; font-weight: 700; }
.receipt-total strong { color: var(--heading); font-size: 1.35rem; }
.receipt-note {
    margin-top: 20px;
    padding: 12px;
    border-radius: 11px;
    background: var(--surface-2);
    color: var(--text-soft);
    text-align: center;
    font-size: .72rem;
}

@media (max-width: 850px) {
    .receipt-layout { grid-template-columns: 1fr; }
    .receipt-preview { position: static; }
}
@media (max-width: 520px) {
    .receipt-card { grid-template-columns: 46px 1fr; }
    .receipt-price { grid-column: 2; }
}
</style>

<section class="receipt-hero">
    <div class="container">
        <span class="eyebrow"><i class="fa-solid fa-receipt"></i> Service history</span>
        <h1 class="section-heading">Your receipts</h1>
        <p class="section-copy" style="margin-top:10px;">Receipts shown here come from records already created in the existing receipt table for your registered vehicles.</p>
    </div>
</section>

<section class="section">
    <div class="container receipt-layout">
        <main>
            <?php if ($receipts): ?>
                <div class="receipt-list">
                    <?php foreach ($receipts as $receiptRow): ?>
                        <a class="card receipt-card <?= $selectedReceiptId === (int)$receiptRow['receipt_id'] ? 'active' : '' ?>"
                           href="?receipt_id=<?= (int)$receiptRow['receipt_id'] ?>">
                            <div class="receipt-icon"><i class="fa-solid fa-receipt"></i></div>
                            <div class="receipt-main">
                                <h3>Receipt #<?= (int)$receiptRow['receipt_id'] ?> · <?= htmlspecialchars($receiptRow['package_name']) ?></h3>
                                <p><?= date('d M Y', strtotime($receiptRow['receipt_date'])) ?> · <?= htmlspecialchars($receiptRow['vehicle_platenum']) ?></p>
                            </div>
                            <div class="receipt-price">RM<?= number_format((float)$receiptRow['package_price'], 2) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card empty-state">
                    <i class="fa-solid fa-receipt"></i>
                    <h3>No receipt records yet</h3>
                    <p>A receipt will appear here after one is created for one of your registered vehicles.</p>
                    <a class="btn btn-primary" href="booking.php">Book a wash</a>
                </div>
            <?php endif; ?>
        </main>

        <aside class="card receipt-preview">
            <?php if ($selectedReceipt): ?>
                <div class="receipt-paper">
                    <div class="receipt-brand">
                        <img src="../../images/logo.png" alt="">
                        <div>
                            <h2>KAMAL CAR WASH</h2>
                            <p>Seremban, Negeri Sembilan</p>
                        </div>
                    </div>

                    <div class="receipt-number">Receipt #<?= (int)$selectedReceipt['receipt_id'] ?></div>
                    <div class="receipt-lines">
                        <div class="receipt-line"><span>Date</span><strong><?= date('d M Y', strtotime($selectedReceipt['receipt_date'])) ?></strong></div>
                        <div class="receipt-line"><span>Time</span><strong><?= date('g:i A', strtotime($selectedReceipt['receipt_time'])) ?></strong></div>
                        <div class="receipt-line"><span>Vehicle</span><strong><?= htmlspecialchars($selectedReceipt['vehicle_platenum']) ?><br><?= htmlspecialchars($selectedReceipt['vehicle_brand'] . ' ' . $selectedReceipt['vehicle_model']) ?></strong></div>
                        <div class="receipt-line"><span>Package</span><strong><?= htmlspecialchars($selectedReceipt['package_name']) ?></strong></div>
                        <div class="receipt-line"><span>Handled by</span><strong><?= htmlspecialchars($selectedReceipt['staff_name'] ?: 'Staff') ?></strong></div>
                        <div class="receipt-line"><span>Payment record</span><strong><?= $selectedReceipt['payment_id'] ? 'Recorded' : 'Not linked' ?></strong></div>
                    </div>

                    <div class="receipt-total">
                        <span>Package price</span>
                        <strong>RM<?= number_format((float)$selectedReceipt['package_price'], 2) ?></strong>
                    </div>
                    <div class="receipt-note">Thank you for choosing Kamal Car Wash.</div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-arrow-pointer"></i>
                    <h3>Select a receipt</h3>
                    <p>Choose a receipt from the list to preview its details.</p>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</section>

<?php include "../includes/footer.php"; ?>
