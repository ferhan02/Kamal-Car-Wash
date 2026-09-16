<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($_SESSION['staff_id'])) {
    header('Location: ../../auth/staff_login.php');
    exit();
}

$current_id = (int) $_SESSION['staff_id'];
$stmt = $conn->prepare('SELECT * FROM staff WHERE staff_id = ?');
$stmt->execute([$current_id]);
$staff = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staff || !kcw_admin_role($staff['staff_job'])) {
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
$fieldErrors = [];
$error = '';

if (isset($_POST['add_staff'])) {
    $name = trim($_POST['staff_name'] ?? '');
    $email = strtolower(trim($_POST['staff_email'] ?? ''));
    $password = $_POST['staff_password'] ?? '';
    $phone = trim($_POST['staff_phonenum'] ?? '');
    $dob = trim($_POST['staff_dob'] ?? '');
    $state = trim($_POST['staff_state'] ?? '');
    $hire = trim($_POST['staff_hiredate'] ?? '');
    $salaryRaw = trim($_POST['staff_salary'] ?? '');
    $salary = (float) $salaryRaw;
    $job = trim($_POST['staff_job'] ?? '');
    $gender = $_POST['staff_gender'] ?? '';

    if ($name === '') $fieldErrors['staff_name'] = 'Enter the staff member’s full name.';
    if ($email === '') {
        $fieldErrors['staff_email'] = 'Enter the staff email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['staff_email'] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) $fieldErrors['staff_password'] = 'Use at least 8 characters.';
    if ($phone === '') $fieldErrors['staff_phonenum'] = 'Enter the phone number.';
    if ($dob === '') $fieldErrors['staff_dob'] = 'Choose the date of birth.';
    if (!in_array($state, $states, true)) $fieldErrors['staff_state'] = 'Choose a state from the list.';
    if ($hire === '') $fieldErrors['staff_hiredate'] = 'Choose the hire date.';
    if ($salaryRaw === '' || $salary < 0) $fieldErrors['staff_salary'] = 'Enter a valid salary amount.';
    if (!in_array($job, $available_jobs, true)) $fieldErrors['staff_job'] = 'Choose an allowed role.';
    if (!in_array((string) $gender, ['0', '1'], true)) $fieldErrors['staff_gender'] = 'Choose one gender option.';

    if (!$fieldErrors) {
        $stmt = $conn->prepare('SELECT staff_id FROM staff WHERE staff_email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $fieldErrors['staff_email'] = 'That email is already registered.';
    }

    $image = 'uploads/default.png';
    $newImagePath = null;
    if (!$fieldErrors && isset($_FILES['staff_image']) && $_FILES['staff_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $ext = strtolower(pathinfo($_FILES['staff_image']['name'], PATHINFO_EXTENSION));
        if ($_FILES['staff_image']['error'] !== UPLOAD_ERR_OK
            || $_FILES['staff_image']['size'] > 2000000
            || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $fieldErrors['staff_image'] = 'Use a JPG, PNG or WEBP image below 2 MB.';
        } else {
            $folder = __DIR__ . '/../../images/uploads/staff/';
            if (!is_dir($folder)) mkdir($folder, 0777, true);
            $filename = 'staff_' . time() . '.' . $ext;
            $newImagePath = $folder . $filename;
            if (move_uploaded_file($_FILES['staff_image']['tmp_name'], $newImagePath)) {
                $image = 'uploads/staff/' . $filename;
            } else {
                $fieldErrors['staff_image'] = 'The profile image could not be saved.';
                $newImagePath = null;
            }
        }
    }

    if (!$fieldErrors) {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                'INSERT INTO staff (
                    staff_name, staff_email, staff_password, staff_phonenum, staff_dob,
                    staff_state, staff_hiredate, staff_salary, staff_job, staff_gender, staff_image
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $email, $hash, $phone, $dob, $state, $hire, $salary, $job, (int) $gender, $image]);
            $_SESSION['success'] = 'Staff account created.';
            header('Location: staff_management.php');
            exit();
        } catch (Throwable $e) {
            if ($newImagePath && is_file($newImagePath)) @unlink($newImagePath);
            $error = 'The staff account could not be created. Please try again.';
        }
    }
}

$root_prefix = '../../';
$page_title = 'Add Staff';
include __DIR__ . '/../includes/head.php';
?>
<style>
.form-shell { max-width: 1040px; margin: 0 auto; }
.staff-form-card { padding: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.span-2 { grid-column: 1 / -1; }
.photo-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    border: 1px dashed var(--staff-border);
    border-radius: 18px;
    background: var(--staff-surface-2);
}
.photo-row img { width: 72px; height: 72px; border-radius: 22px; object-fit: cover; }
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid var(--staff-border);
}
.password-wrap { position: relative; }
.password-wrap .ios-input { padding-right: 48px; }
.password-eye {
    position: absolute;
    right: 7px;
    top: 50%;
    width: 36px;
    height: 36px;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: var(--staff-muted);
    cursor: pointer;
}
@media (max-width: 700px) {
    .form-grid { grid-template-columns: 1fr; }
    .span-2 { grid-column: auto; }
    .photo-row { align-items: flex-start; flex-direction: column; }
}
</style>
</head>
<body class="staff-body">
<?php include __DIR__ . '/../includes/admin_header.php'; ?>
<?php include __DIR__ . '/../includes/admin_toolbar.php'; ?>

<main class="staff-main form-shell">
    <div class="staff-page-heading">
        <div>
            <span class="staff-eyebrow"><i class="fa-solid fa-user-plus"></i> Administration</span>
            <h1>Add staff member</h1>
            <p>Create an account and assign the role used by the staff portal.</p>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="ios-toast error"><strong>Could not create account</strong><span><?= kcw_h($error) ?></span></div>
    <?php endif; ?>

    <article class="ios-card staff-form-card">
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="ios-field span-2<?= isset($fieldErrors['staff_image']) ? ' has-error' : '' ?>">
                    <label>Photo</label>
                    <div class="photo-row">
                        <img src="../../images/uploads/default.png" data-add-preview alt="Preview">
                        <div>
                            <input type="file" name="staff_image" accept="image/jpeg,image/png,image/webp" data-file-preview="[data-add-preview]">
                            <div class="ios-helper">Optional · JPG, PNG or WEBP · maximum 2 MB</div>
                            <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_image'] ?? '') ?></span>
                        </div>
                    </div>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_name']) ? ' has-error' : '' ?>">
                    <label for="staff_name">Full name</label>
                    <input class="ios-input" id="staff_name" name="staff_name" value="<?= kcw_h($_POST['staff_name'] ?? '') ?>" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_name'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_email']) ? ' has-error' : '' ?>">
                    <label for="staff_email">Email</label>
                    <input class="ios-input" id="staff_email" type="email" name="staff_email" value="<?= kcw_h($_POST['staff_email'] ?? '') ?>" placeholder="name@example.com" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_email'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_password']) ? ' has-error' : '' ?>">
                    <label for="newStaffPassword">Password</label>
                    <div class="password-wrap">
                        <input class="ios-input" type="password" id="newStaffPassword" name="staff_password" minlength="8" required>
                        <button class="password-eye" type="button" data-password-toggle="newStaffPassword" aria-label="Show password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                    <span class="ios-helper">At least 8 characters.</span>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_password'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_phonenum']) ? ' has-error' : '' ?>">
                    <label for="staff_phonenum">Phone number</label>
                    <input class="ios-input" id="staff_phonenum" name="staff_phonenum" inputmode="numeric" value="<?= kcw_h($_POST['staff_phonenum'] ?? '') ?>" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_phonenum'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_dob']) ? ' has-error' : '' ?>">
                    <label for="staff_dob">Date of birth</label>
                    <input class="ios-input" id="staff_dob" type="date" name="staff_dob" value="<?= kcw_h($_POST['staff_dob'] ?? '') ?>" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_dob'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_state']) ? ' has-error' : '' ?>">
                    <label for="staff_state">State</label>
                    <input
                        class="ios-input"
                        id="staff_state"
                        name="staff_state"
                        list="staff-state-options"
                        autocomplete="address-level1"
                        value="<?= kcw_h($_POST['staff_state'] ?? '') ?>"
                        placeholder="Type or choose a state"
                        required
                    >
                    <datalist id="staff-state-options">
                        <?php foreach ($states as $s): ?><option value="<?= kcw_h($s) ?>"></option><?php endforeach; ?>
                    </datalist>
                    <span class="ios-helper">Type to search or open the list and scroll.</span>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_state'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_hiredate']) ? ' has-error' : '' ?>">
                    <label for="staff_hiredate">Hire date</label>
                    <input class="ios-input" id="staff_hiredate" type="date" name="staff_hiredate" value="<?= kcw_h($_POST['staff_hiredate'] ?? date('Y-m-d')) ?>" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_hiredate'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_salary']) ? ' has-error' : '' ?>">
                    <label for="staff_salary">Basic salary (RM)</label>
                    <input class="ios-input" id="staff_salary" type="number" name="staff_salary" min="0" step="0.01" value="<?= kcw_h($_POST['staff_salary'] ?? '') ?>" required>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_salary'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_job']) ? ' has-error' : '' ?>">
                    <label for="staff_job">Role</label>
                    <select class="ios-select" id="staff_job" name="staff_job" required>
                        <option value="">Choose role</option>
                        <?php foreach ($available_jobs as $j): ?>
                            <option value="<?= kcw_h($j) ?>" <?= ($_POST['staff_job'] ?? '') === $j ? 'selected' : '' ?>><?= kcw_h($j) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_job'] ?? '') ?></span>
                </div>

                <div class="ios-field<?= isset($fieldErrors['staff_gender']) ? ' has-error' : '' ?>">
                    <label>Gender</label>
                    <div class="kcw-choice-grid">
                        <label class="kcw-choice">
                            <input type="radio" name="staff_gender" value="0" <?= ($_POST['staff_gender'] ?? '') === '0' ? 'checked' : '' ?> required>
                            <span><i class="fa-solid fa-person"></i> Male</span>
                        </label>
                        <label class="kcw-choice">
                            <input type="radio" name="staff_gender" value="1" <?= ($_POST['staff_gender'] ?? '') === '1' ? 'checked' : '' ?>>
                            <span><i class="fa-solid fa-person-dress"></i> Female</span>
                        </label>
                    </div>
                    <span class="kcw-field-error"><?= kcw_h($fieldErrors['staff_gender'] ?? '') ?></span>
                </div>
            </div>

            <div class="form-actions">
                <a class="ios-btn ios-btn-secondary" href="staff_management.php">Cancel</a>
                <button class="ios-btn ios-btn-primary" name="add_staff"><i class="fa-solid fa-user-plus"></i> Create staff account</button>
            </div>
        </form>
    </article>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
