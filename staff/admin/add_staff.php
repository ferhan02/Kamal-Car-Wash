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
$staff=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$staff||!kcw_admin_role($staff['staff_job'])) {
    header('Location: ../staff_home.php');
    exit();
}
$available_jobs = $staff['staff_job'] === 'Supervisor'
    ? ['Receptionist', 'Car Washer', 'Cashier']
    : ['Manager', 'Supervisor', 'Receptionist', 'Car Washer', 'Cashier'];
$states = [
    'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang',
    'Penang', 'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor',
    'Terengganu', 'Kuala Lumpur', 'Putrajaya', 'Labuan'
];
if(isset($_POST['add_staff'])) {
    $name=trim($_POST['staff_name']??'');
    $email=trim($_POST['staff_email']??'');
    $password=$_POST['staff_password']??'';
    $phone=trim($_POST['staff_phonenum']??'');
    $dob=$_POST['staff_dob']??'';
    $state=$_POST['staff_state']??'';
    $hire=$_POST['staff_hiredate']??'';
    $salary=(float)($_POST['staff_salary']??0);
    $job=$_POST['staff_job']??'';
    $gender=(int)($_POST['staff_gender']??0);
    if(!in_array($job,$available_jobs,true)) {
        $_SESSION['error']='You are not allowed to create that role.';
    } elseif(strlen($password)<8) {
        $_SESSION['error']='Password must contain at least 8 characters.';
    } elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error']='Enter a valid staff email.';
    } else {
        $stmt=$conn->prepare('SELECT staff_id FROM staff WHERE staff_email=?');
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            $_SESSION['error']='That email is already registered.';
        } else {
            $image='uploads/default.png';
            if(isset($_FILES['staff_image'])&&$_FILES['staff_image']['error']!==UPLOAD_ERR_NO_FILE) {
                $ext=strtolower(pathinfo($_FILES['staff_image']['name'],PATHINFO_EXTENSION));
                if (
    $_FILES['staff_image']['error'] !== UPLOAD_ERR_OK
    || $_FILES['staff_image']['size'] > 2000000
    || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)
) {
                    $_SESSION['error']='Use a JPG, PNG or WEBP image below 2 MB.';
                    header('Location: add_staff.php');
                    exit();
                }
                $folder=__DIR__.'/../../images/uploads/staff/';
                if(!is_dir($folder))mkdir($folder,0777,true);
                $filename='staff_'.time().'.'.$ext;
                if(move_uploaded_file($_FILES['staff_image']['tmp_name'],$folder.$filename))$image='uploads/staff/'.$filename;
            }
            $hash=password_hash($password,PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
    'INSERT INTO staff (
        staff_name, staff_email, staff_password, staff_phonenum, staff_dob,
        staff_state, staff_hiredate, staff_salary, staff_job, staff_gender, staff_image
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
            $stmt->execute([$name,$email,$hash,$phone,$dob,$state,$hire,$salary,$job,$gender,$image]);
            $_SESSION['success']='Staff account created.';
            header('Location: staff_management.php');
            exit();
        }
    }
    if(isset($_SESSION['error'])) {
        header('Location: add_staff.php');
        exit();
    }
}
$root_prefix='../../';
$page_title='Add Staff';
include __DIR__.'/../includes/head.php';
?>
<style>
.form-shell {
    max-width:1040px;
    margin:0 auto
}
.staff-form-card {
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
    gap:14px;
    padding:14px;
    border:1px dashed var(--staff-border);
    border-radius:18px;
    background:var(--staff-surface-2)
}
.photo-row img {
    width:72px;
    height:72px;
    border-radius:22px;
    object-fit:cover
}
.form-actions {
    display:flex;
    justify-content:flex-end;
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
}
</style>
</head>
<body class="staff-body">
    <?php include __DIR__.'/../includes/admin_header.php';include __DIR__.'/../includes/admin_toolbar.php'; ?>
    <main class="staff-main form-shell">
        <div class="staff-page-heading">
            <div>
                <span class="staff-eyebrow">
                    <i class="fa-solid fa-user-plus">
                    </i> Administration</span>
                    <h1>Add staff member</h1>
                    <p>Create an account and assign the role used by the staff portal.</p>
                </div>
            </div>
            <article class="ios-card staff-form-card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="ios-field span-2">
                            <label>Photo</label>
                            <div class="photo-row">
                                <img src="../../images/uploads/default.png" data-add-preview alt="Preview">
                                <div>
                                    <input type="file" name="staff_image" accept="image/jpeg,image/png,image/webp" data-file-preview="[data-add-preview]">
                                    <div class="ios-helper">Optional · JPG, PNG or WEBP · maximum 2 MB</div>
                                </div>
                            </div>
                        </div>
                        <div class="ios-field">
                            <label>Full name</label>
                            <input class="ios-input" name="staff_name" required>
                        </div>
                        <div class="ios-field">
                            <label>Email</label>
                            <input class="ios-input" type="email" name="staff_email" required>
                        </div>
                        <div class="ios-field">
                            <label>Password</label>
                            <div class="password-wrap">
                                <input class="ios-input" type="password" id="newStaffPassword" name="staff_password" minlength="8" required>
                                <button class="password-eye" type="button" data-password-toggle="newStaffPassword">
                                    <i class="fa-solid fa-eye">
                                    </i>
                                </button>
                            </div>
                            <span class="ios-helper">At least 8 characters.</span>
                        </div>
                        <div class="ios-field">
                            <label>Phone number</label>
                            <input class="ios-input" name="staff_phonenum" inputmode="numeric" required>
                        </div>
                        <div class="ios-field">
                            <label>Date of birth</label>
                            <input class="ios-input" type="date" name="staff_dob" required>
                        </div>
                        <div class="ios-field">
                            <label>State</label>
                            <select class="ios-select" name="staff_state" required>
                                <?php foreach($states as $s): ?>
                                    <option>
                                        <?= kcw_h($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ios-field">
                            <label>Hire date</label>
                            <input class="ios-input" type="date" name="staff_hiredate" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="ios-field">
                            <label>Basic salary (RM)</label>
                            <input class="ios-input" type="number" name="staff_salary" min="0" step="0.01" required>
                        </div>
                        <div class="ios-field">
                            <label>Role</label>
                            <select class="ios-select" name="staff_job" required>
                                <?php foreach($available_jobs as $j): ?>
                                    <option>
                                        <?= kcw_h($j) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ios-field">
                            <label>Gender</label>
                            <select class="ios-select" name="staff_gender">
                                <option value="0">Male</option>
                                <option value="1">Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <a class="ios-btn ios-btn-secondary" href="staff_management.php">Cancel</a>
                        <button class="ios-btn ios-btn-primary" name="add_staff">
                            <i class="fa-solid fa-user-plus">
                            </i> Create staff account</button>
                        </div>
                    </form>
                </article>
            </main>
            <?php include __DIR__.'/../includes/footer.php'; ?>
        </body>
    </html>
