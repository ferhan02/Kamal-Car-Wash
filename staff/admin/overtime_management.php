<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../includes/helpers.php';
if(!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$staff_id=(int)$_SESSION['staff_id'];
$stmt=$conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$staff_id]);
$staff=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$staff||!kcw_admin_role($staff['staff_job'])) {
    header('Location: ../staff_home.php');
    exit();
}
if(isset($_POST['set_status'])) {
    $id=(int)($_POST['overtime_id']??0);
    $status=$_POST['status']??'';
    if($id>0&&in_array($status,['Approved','Rejected'],true)) {
        $stmt=$conn->prepare('UPDATE overtime SET status=? WHERE overtime_id=?');
        $stmt->execute([$status,$id]);
        $_SESSION['success']='Overtime request '.$status.'.';
    } else $_SESSION['error']='Invalid overtime action.';
    header('Location: overtime_management.php');
    exit();
}
$search=trim($_GET['search']??'');
$status=trim($_GET['status']??'');
$where=[];
$params=[];
if($search!=='') {
    $where[]='(staff.staff_name LIKE ? OR staff.staff_email LIKE ? OR overtime.reason LIKE ?)';
    for($i=0;$i<3;$i++)$params[]='%'.$search.'%';
}
if($status!=='') {
    $where[]='overtime.status=?';
    $params[]=$status;
}
$sql = 'SELECT overtime.*, staff.staff_name, staff.staff_email
        FROM overtime
        JOIN staff ON overtime.staff_id = staff.staff_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY overtime.overtime_id DESC';
$stmt=$conn->prepare($sql);
$stmt->execute($params);
$items=$stmt->fetchAll(PDO::FETCH_ASSOC);
$counts=['Pending'=>0,'Approved'=>0,'Rejected'=>0];
foreach($conn->query('SELECT status,COUNT(*) total FROM overtime GROUP BY status')->fetchAll(PDO::FETCH_ASSOC) as $r) {
    if(isset($counts[$r['status']]))$counts[$r['status']]=(int)$r['total'];
}
$approvedTotal=(float)$conn->query("SELECT COALESCE(SUM(total_amount),0) FROM overtime WHERE status='Approved'")->fetchColumn();
$root_prefix='../../';
$page_title='Overtime Management';
include __DIR__.'/../includes/head.php';
?>
<style>
.admin-stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:10px;
    margin-bottom:14px
}
.admin-stat {
    padding:17px
}
.admin-stat span {
    color:var(--staff-muted);
    font-size:.67rem;
    font-weight:850
}
.admin-stat strong {
    display:block;
    margin-top:3px;
    font-size:1.4rem
}
.filter-card {
    display:grid;
    grid-template-columns:1fr 210px auto;
    gap:10px;
    padding:15px;
    margin-bottom:14px
}
.ot-table {
    padding:14px
}
.person strong {
    display:block
}
.person span {
    display:block;
    color:var(--staff-muted);
    font-size:.66rem
}
.reason {
    max-width:300px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    color:var(--staff-muted)
}
.actions {
    display:flex;
    gap:5px
}
.mobile-list {
    display:none
}
.mobile-card {
    padding:16px
}
.mobile-top {
    display:flex;
    justify-content:space-between;
    gap:10px
}
.mobile-meta {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:8px;
    margin-top:12px
}
.mobile-meta div {
    padding:9px;
    border-radius:12px;
    background:var(--staff-surface-2);
    font-size:.7rem
}
.mobile-meta span {
    display:block;
    color:var(--staff-muted);
    font-size:.63rem
}
.mobile-reason {
    margin:11px 0;
    color:var(--staff-muted);
    font-size:.72rem
}
@media(max-width:900px) {
    .admin-stats {
        grid-template-columns:repeat(2,1fr)
    }
    .filter-card {
        grid-template-columns:1fr 1fr
    }
    .filter-card .search {
        grid-column:1/-1
    }
    .desktop-list {
        display:none
    }
    .mobile-list {
        display:grid;
        gap:9px
    }
    .ot-table {
        padding:0;
        background:transparent;
        border:0;
        box-shadow:none
    }
}
@media(max-width:560px) {
    .filter-card {
        grid-template-columns:1fr
    }
    .filter-card .search {
        grid-column:auto
    }
}
</style>
</head>
<body class="staff-body">
    <?php include __DIR__.'/../includes/admin_header.php';include __DIR__.'/../includes/admin_toolbar.php'; ?>
    <main class="staff-main">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow">
                    <i class="fa-solid fa-business-time">
                    </i> Approval queue</span>
                    <h1>Overtime management</h1>
                    <p>Review hours, rates and calculated overtime pay before approving a request.</p>
                </div>
            </div>
            <section class="admin-stats">
                <article class="ios-card admin-stat">
                    <span>PENDING</span>
                    <strong style="color:var(--staff-orange)">
                        <?= $counts['Pending'] ?>
                    </strong>
                </article>
                <article class="ios-card admin-stat">
                    <span>APPROVED</span>
                    <strong style="color:var(--staff-green)">
                        <?= $counts['Approved'] ?>
                    </strong>
                </article>
                <article class="ios-card admin-stat">
                    <span>REJECTED</span>
                    <strong style="color:var(--staff-red)">
                        <?= $counts['Rejected'] ?>
                    </strong>
                </article>
                <article class="ios-card admin-stat">
                    <span>APPROVED VALUE</span>
                    <strong>RM <?= number_format($approvedTotal,2) ?>
                    </strong>
                </article>
            </section>
            <form class="ios-card filter-card" method="GET">
                <div class="ios-field search">
                    <label>Search</label>
                    <input class="ios-input" name="search" value="<?= kcw_h($search) ?>" placeholder="Staff, email or reason">
                </div>
                <div class="ios-field">
                    <label>Status</label>
                    <select class="ios-select" name="status">
                        <option value="">All statuses</option>
                        <?php foreach(['Pending','Approved','Rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="ios-btn ios-btn-primary">
                    <i class="fa-solid fa-filter">
                    </i> Filter</button>
                </form>
                <section class="ios-card ot-table">
                    <div class="ios-table-wrap desktop-list">
                        <table class="ios-table">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Date</th>
                                    <th>Hours</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!$items): ?>
                                    <tr>
                                        <td colspan="8">
                                            <div class="ios-empty">
                                                <i class="fa-regular fa-clock">
                                                </i>
                                                <strong>No overtime requests</strong>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach($items as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="person">
                                                <strong>
                                                    <?= kcw_h($r['staff_name']) ?>
                                                </strong>
                                                <span>
                                                    <?= kcw_h($r['staff_email']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <?= date('d M Y',strtotime($r['overtime_date'])) ?>
                                        </td>
                                        <td>
                                            <?= number_format((float)$r['hours'],2) ?> h</td>
                                            <td>RM <?= number_format((float)$r['rate'],2) ?>
                                            </td>
                                            <td>
                                                <strong>RM <?= number_format((float)$r['total_amount'],2) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="reason" title="<?= kcw_h($r['reason']) ?>">
                                                    <?= kcw_h($r['reason']?:'—') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ios-badge <?= kcw_status_class($r['status']) ?>">
                                                    <?= kcw_h($r['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($r['status']==='Pending'): ?>
                                                    <div class="actions">
                                                        <form method="POST">
                                                            <input type="hidden" name="overtime_id" value="<?= $r['overtime_id'] ?>">
                                                            <input type="hidden" name="status" value="Approved">
                                                            <button class="ios-btn ios-btn-success ios-btn-sm" name="set_status">
                                                                <i class="fa-solid fa-check">
                                                                </i>
                                                            </button>
                                                        </form>
                                                        <form method="POST">
                                                            <input type="hidden" name="overtime_id" value="<?= $r['overtime_id'] ?>">
                                                            <input type="hidden" name="status" value="Rejected">
                                                            <button class="ios-btn ios-btn-danger ios-btn-sm" name="set_status">
                                                                <i class="fa-solid fa-xmark">
                                                                </i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>—<?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mobile-list">
                                <?php foreach($items as $r): ?>
                                    <article class="ios-card mobile-card">
                                        <div class="mobile-top">
                                            <div>
                                                <strong>
                                                    <?= kcw_h($r['staff_name']) ?>
                                                </strong>
                                                <div style="color:var(--staff-muted);font-size:.67rem">
                                                    <?= date('d M Y',strtotime($r['overtime_date'])) ?>
                                                </div>
                                            </div>
                                            <span class="ios-badge <?= kcw_status_class($r['status']) ?>">
                                                <?= kcw_h($r['status']) ?>
                                            </span>
                                        </div>
                                        <div class="mobile-meta">
                                            <div>
                                                <span>Hours</span>
                                                <?= number_format((float)$r['hours'],2) ?> h</div>
                                                <div>
                                                    <span>Amount</span>RM <?= number_format((float)$r['total_amount'],2) ?>
                                                </div>
                                            </div>
                                            <p class="mobile-reason">
                                                <?= kcw_h($r['reason']?:'No reason provided') ?>
                                            </p>
                                            <?php if($r['status']==='Pending'): ?>
                                                <div class="actions">
                                                    <form method="POST">
                                                        <input type="hidden" name="overtime_id" value="<?= $r['overtime_id'] ?>">
                                                        <input type="hidden" name="status" value="Approved">
                                                        <button class="ios-btn ios-btn-success ios-btn-sm" name="set_status">Approve</button>
                                                    </form>
                                                    <form method="POST">
                                                        <input type="hidden" name="overtime_id" value="<?= $r['overtime_id'] ?>">
                                                        <input type="hidden" name="status" value="Rejected">
                                                        <button class="ios-btn ios-btn-danger ios-btn-sm" name="set_status">Reject</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        </main>
                        <?php include __DIR__.'/../includes/footer.php'; ?>
                    </body>
                </html>
