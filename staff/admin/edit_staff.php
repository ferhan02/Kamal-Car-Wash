<?php
session_start();
require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../includes/helpers.php';
if(!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}
$current_id=(int)$_SESSION['staff_id'];
$stmt=$conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$current_id]);
$current=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$current||!kcw_admin_role($current['staff_job'])) {
    header('Location: ../staff_home.php');
    exit();
}
$edit_id=(int)($_GET['id']??0);
if($edit_id<=0) {
    header('Location: staff_management.php');
    exit();
}
$stmt=$conn->prepare('SELECT * FROM staff WHERE staff_id=?');
$stmt->execute([$edit_id]);
$staff=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$staff) {
    $_SESSION['error']='Staff account not found.';
    header('Location: staff_management.php');
    exit();
}
if ($current['staff_job'] === 'Owner') {
    $available_jobs = ['Owner', 'Manager', 'Supervisor', 'Receptionist', 'Car Washer', 'Cashier'];
} elseif ($current['staff_job'] === 'Manager') {
    $available_jobs = ['Manager', 'Supervisor', 'Receptionist', 'Car Washer', 'Cashier'];
} else {
    $available_jobs = ['Receptionist', 'Car Washer', 'Cashier'];
}
$states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
    'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor',
    'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
];
if(isset($_POST['update_staff'])) {
    $name=trim($_POST['staff_name']??'');
    $email=trim($_POST['staff_email']??'');
    $phone=trim($_POST['staff_phonenum']??'');
    $dob=$_POST['staff_dob']??'';
    $state=$_POST['staff_state']??'';
    $hire=$_POST['staff_hiredate']??'';
    $salary=(float)($_POST['staff_salary']??0);
    $job=$_POST['staff_job']??'';
    $gender=(int)($_POST['staff_gender']??0);
    $newPassword=$_POST['staff_password']??'';
    if(!in_array($job,$available_jobs,true)) {
        $_SESSION['error']='You are not allowed to assign that role.';
        header('Location: edit_staff.php?id='.$edit_id);
        exit();
    }
    $stmt=$conn->prepare('SELECT staff_id FROM staff WHERE staff_email=? AND staff_id<>?');
    $stmt->execute([$email,$edit_id]);
    if($stmt->fetch()) {
        $_SESSION['error']='That email already belongs to another staff account.';
        header('Location: edit_staff.php?id='.$edit_id);
        exit();
    }
    $image=$staff['staff_image']?:'uploads/default.png';
    if(isset($_FILES['staff_image'])&&$_FILES['staff_image']['error']!==UPLOAD_ERR_NO_FILE) {
        $ext=strtolower(pathinfo($_FILES['staff_image']['name'],PATHINFO_EXTENSION));
        if (
    $_FILES['staff_image']['error'] !== UPLOAD_ERR_OK
    || $_FILES['staff_image']['size'] > 2000000
    || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)
) {
            $_SESSION['error']='Use a JPG, PNG or WEBP image below 2 MB.';
            header('Location: edit_staff.php?id='.$edit_id);
            exit();
        }
        $folder=__DIR__.'/../../images/uploads/staff/';
        if(!is_dir($folder))mkdir($folder,0777,true);
        $filename='staff_'.$edit_id.'_'.time().'.'.$ext;
        if(move_uploaded_file($_FILES['staff_image']['tmp_name'],$folder.$filename))$image='uploads/staff/'.$filename;
    }
    $password=$staff['staff_password'];
    if($newPassword!=='') {
        if(strlen($newPassword)<8) {
            $_SESSION['error']='New password must contain at least 8 characters.';
            header('Location: edit_staff.php?id='.$edit_id);
            exit();
        }
        $password=password_hash($newPassword,PASSWORD_DEFAULT);
    }
    $stmt = $conn->prepare(
    'UPDATE staff SET
        staff_name = ?, staff_email = ?, staff_password = ?, staff_phonenum = ?,
        staff_dob = ?, staff_state = ?, staff_hiredate = ?, staff_salary = ?,
        staff_job = ?, staff_gender = ?, staff_image = ?
     WHERE staff_id = ?'
);
    $stmt->execute([$name,$email,$password,$phone,$dob,$state,$hire,$salary,$job,$gender,$image,$edit_id]);
    $_SESSION['success']='Staff account updated.';
    header('Location: staff_management.php');
    exit();
}
$root_prefix='../../';
$page_title='Edit Staff';
include __DIR__.'/../includes/head.php';
?>
<style>
.edit-shell {
    max-width:1040px;
    margin:0 auto
}
.edit-card {
    padding:24px
}
.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px
}
.span-2 {
    grid-column:1/-1
}
.photo-row {
    display:flex;
    align-items:center;
    gap:15px;
    padding:14px;
    border:1px dashed var(--staff-border);
    border-radius:18px;
    background:var(--staff-surface-2)
}
.photo-row img {
    width:76px;
    height:76px;
    border-radius:23px;
    object-fit:cover
}
.form-actions {
    display:flex;
    justify-content:space-between;
    gap:8px;
    margin-top:22px;
    padding-top:18px;
    border-top:1px solid var(--staff-border)
}
.password-wrap {
    position:relative
}
.password-wrap .ios-input {
    padding-right:48px
}
.password-eye {
    position:absolute;
    right:7px;
    top:50%;
    transform:translateY(-50%);
    width:36px;
    height:36px;
    border:0;
    background:transparent;
    color:var(--staff-muted);
    cursor:pointer
}
.identity-note {
    display:flex;
    align-items:center;
    gap:9px;
    padding:11px 12px;
    border-radius:15px;
    background:var(--staff-blue-soft);
    color:var(--staff-blue);
    font-size:.74rem
}
@media(max-width:700px) {
    .form-grid {
        grid-template-columns:1fr
    }
    .span-2 {
        grid-column:auto
    }
    .photo-row {
        align-items:flex-start;
        flex-direction:column
    }
    .form-actions {
        flex-direction:column-reverse
    }
}
</style>
</head>
<body class="staff-body">@@KCWBLOCK2@@
    <main class="staff-main edit-shell">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow">
                    <i class="fa-solid fa-user-pen">
                    </i> Administration</span>
                    <h1>Edit <?= kcw_h($staff['staff_name']) ?>
                    </h1>
                    <p>Update staff details while preserving their existing account history.</p>
                </div>
            </div>
            <article class="ios-card edit-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="span-2 identity-note">
                            <i class="fa-solid fa-circle-info">
                            </i>
                            Staff ID #<?= $staff['staff_id'] ?> ·
                            <?= kcw_h($staff['staff_job']) ?> ·
                            hired <?= date('d M Y', strtotime($staff['staff_hiredate'])) ?>
                        </div>
                        <div class="ios-field span-2">
                            <label>Photo</label>
                            <div class="photo-row">
                                <img
                                    src="../../images/<?= kcw_h(ltrim($staff['staff_image'] ?: 'uploads/default.png', '/')) ?>"
                                    data-edit-preview
                                    onerror="this.src='../../images/uploads/default.png'"
                                    alt="Preview"
                                >
                                <div>
                                    <input type="file" name="staff_image" accept="image/jpeg,image/png,image/webp" data-file-preview="[data-edit-preview]">
                                    <div class="ios-helper">Leave empty to keep the current image.</div>
                                </div>
                            </div>
                        </div>
                        <div class="ios-field">
                            <label>Full name</label>
                            <input class="ios-input" name="staff_name" value="<?= kcw_h($staff['staff_name']) ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>Email</label>
                            <input class="ios-input" type="email" name="staff_email" value="<?= kcw_h($staff['staff_email']) ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>New password</label>
                            <div class="password-wrap">
                                <input class="ios-input" type="password" id="editPassword" name="staff_password" minlength="8">
                                <button class="password-eye" type="button" data-password-toggle="editPassword">
                                    <i class="fa-solid fa-eye">
                                    </i>
                                </button>
                            </div>
                            <span class="ios-helper">Leave empty to keep the current password.</span>
                        </div>
                        <div class="ios-field">
                            <label>Phone</label>
                            <input class="ios-input" name="staff_phonenum" value="<?= kcw_h($staff['staff_phonenum']) ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>Date of birth</label>
                            <input class="ios-input" type="date" name="staff_dob" value="<?= kcw_h($staff['staff_dob']) ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>State</label>
                            <select class="ios-select" name="staff_state">
                                <?php foreach($states as $s): ?>
                                    <option <?= $staff['staff_state']===$s?'selected':'' ?>>
                                        <?= kcw_h($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ios-field">
                            <label>Hire date</label>
                            <input class="ios-input" type="date" name="staff_hiredate" value="<?= kcw_h($staff['staff_hiredate']) ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>Basic salary (RM)</label>
                            <input
                                class="ios-input"
                                type="number"
                                name="staff_salary"
                                step="0.01"
                                min="0"
                                value="<?= kcw_h($staff['staff_salary']) ?>"
                                required
                            >
                        </div>
                        <div class="ios-field">
                            <label>Role</label>
                            <select class="ios-select" name="staff_job">
                                <?php foreach($available_jobs as $j): ?>
                                    <option <?= $staff['staff_job']===$j?'selected':'' ?>>
                                        <?= kcw_h($j) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ios-field">
                            <label>Gender</label>
                            <select class="ios-select" name="staff_gender">
                                <option value="0" <?= (int)$staff['staff_gender']===0?'selected':'' ?>>Male</option>
                                <option value="1" <?= (int)$staff['staff_gender']===1?'selected':'' ?>>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a class="ios-btn ios-btn-secondary" href="staff_management.php">
                            <i class="fa-solid fa-chevron-left">
                            </i> Back</a>
                            <button class="ios-btn ios-btn-primary" name="update_staff">
                                <i class="fa-solid fa-check">
                                </i> Save changes</button>
                            </div>
                        </form>
                    </article>
                </main>@@KCWBLOCK3@@</body>
            </html>
