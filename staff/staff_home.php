<?php

session_start();

include("../config.php");


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION['staff_id'])) {

    header("Location: ../auth/staff_login.php");
    exit();

}


$staff_id = $_SESSION['staff_id'];



// =========================
// GET STAFF INFORMATION
// =========================

$stmt = $conn->prepare("
    SELECT *
    FROM staff
    WHERE staff_id = ?
");

$stmt->execute([
    $staff_id
]);

$staff = $stmt->fetch();


if(!$staff){

    session_destroy();

    header("Location: ../auth/staff_login.php");
    exit();

}



// =========================
// TODAY RESERVATIONS
// =========================

$today = date("Y-m-d");


$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM service
    WHERE service_date = ?
");

$stmt->execute([
    $today
]);

$today_reservations = $stmt->fetchColumn();




// =========================
// PENDING RESERVATIONS
// =========================

$stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM service
    WHERE service_status = 'Pending'
");

$stmt->execute();

$pending = $stmt->fetchColumn();




// =========================
// ATTENDANCE CHECK-IN
// =========================

if(isset($_POST['check_attendance'])){


    $stmt = $conn->prepare("
        SELECT *
        FROM attendance
        WHERE staff_id = ?
        AND attendance_date = ?
    ");


    $stmt->execute([

        $staff_id,

        $today

    ]);


    $existing = $stmt->fetch();



    if(!$existing){


        $stmt = $conn->prepare("
            INSERT INTO attendance

            (
                staff_id,
                attendance_date,
                is_present
            )

            VALUES

            (?,?,1)
        ");



        $stmt->execute([

            $staff_id,

            $today

        ]);



        $_SESSION['login_success'] =
        "Attendance recorded successfully.";


    }


    header("Location: staff_home.php");
    exit();

}





// =========================
// ATTENDANCE STATUS
// =========================

$stmt = $conn->prepare("
    SELECT is_present
    FROM attendance
    WHERE staff_id = ?
    AND attendance_date = ?
");


$stmt->execute([

    $staff_id,

    $today

]);


$attendance = $stmt->fetch();



if ($attendance && $attendance['is_present'] == 1) {

    $attendance_status = "Present";

}

else {

    $attendance_status = "Absent";

}



// =========================
// SALARY
// =========================

$salary = $staff['staff_salary'];



?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">


<title>
Staff Dashboard | CARWASH KAMAL
</title>


<link rel="stylesheet" href="../css/style.css">


<!-- SWEETALERT2 -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<style>


body {

    margin:0;

    font-family:'Poppins',sans-serif;

}



/* =========================
TOP BAR
========================= */


.top-bar {

    background:#6d8fb8;

    color:white;

    padding:10px 25px;

    display:flex;

    justify-content:space-between;

    font-size:13px;

}




/* =========================
HEADER
========================= */


.header {


    background:#b7cde6;

    padding:18px 50px;

    display:flex;

    align-items:center;

    justify-content:space-between;

}



.logo-section {

    display:flex;

    align-items:center;

    gap:15px;

}



.logo {

    width:90px;

}



.brand-text h1 {

    margin:0;

    font-family:'Oswald';

    font-size:28px;

}



.brand-text span {

    font-size:12px;

}

.profile-btn {

    background:#2f5aa8;

    color:white;

    padding:12px 22px;

    border-radius:30px;

    text-decoration:none;

    font-weight:600;

    transition:.3s;

}



.profile-btn:hover {

    background:#1f2a44;

}


.logout-btn {

    background:#c0392b;

    color:white;

    padding:12px 22px;

    border-radius:30px;

    text-decoration:none;

    font-weight:600;

    transition:.3s;

    margin-left:15px;

}


.logout-btn:hover {

    background:#922b21;

}


/* =========================
HERO
========================= */


.hero {


    min-height:430px;

    background:

    linear-gradient(
        rgba(0,0,0,0.55),
        rgba(0,0,0,0.55)
    ),

    url("../images/hero.jpg");

    background-size:cover;

    background-position:center;

    display:flex;

    align-items:center;

    justify-content:center;

    text-align:center;

    color:white;

}



.hero h1 {

    font-size:48px;

    margin-bottom:10px;

}



.hero p {

    font-size:18px;

}





/* =========================
STATISTICS
========================= */


.dashboard {

    padding:50px;

    background:#dfeaf8;

}



.stats {


    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));

    gap:25px;

    margin-top:-90px;

    position:relative;

}



.stat-card {


    background:white;

    padding:30px;

    border-radius:18px;

    text-align:center;

    box-shadow:0 10px 25px rgba(0,0,0,.15);

}



.stat-card h2 {

    color:#2f5aa8;

    font-size:35px;

}



.attendance-btn{

    margin-top:15px;

    padding:10px 20px;

    border:none;

    border-radius:25px;

    background:#2f5aa8;

    color:white;

    font-weight:bold;

    cursor:pointer;

    font-family:'Poppins',sans-serif;

}



.attendance-btn:hover{

    background:#1f3f7a;

}





/* =========================
MODULE CARDS
========================= */


.modules {


    margin-top:50px;

    display:grid;

    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));

    gap:25px;

}



.module {


    background:#ffffff;


    padding:25px;


    border-radius:18px;


    text-decoration:none;


    color:#1f2a44;


    border:2px solid #d6e4f7;


    box-shadow:

    0 8px 25px rgba(31,42,68,.15);


    position:relative;


    overflow:hidden;


    transition:.35s ease;


}





.module::before{


    content:"";


    position:absolute;


    top:0;


    left:-100%;


    width:100%;


    height:100%;


    background:

    linear-gradient(

    120deg,

    transparent,

    rgba(47,90,168,.15),

    transparent

    );


    transition:.5s;


}





.module:hover::before{


    left:100%;


}






.module:hover {


    transform:

    translateY(-12px)

    scale(1.03);


    box-shadow:

    0 20px 40px rgba(47,90,168,.3);


}





.module h3 {


    color:#2f5aa8;


    transition:.3s;


}





.module:hover h3{


    color:#1f3f7a;


}

/* =========================
ADMIN TOOLBAR
========================= */


.admin-toolbar{

    position:fixed;

    top:140px;

    right:0;

    z-index:9999;

}



.admin-title{

    background:#2f5aa8;

    color:white;

    padding:12px 22px;

    font-weight:600;

    cursor:pointer;

    border-radius:10px 0 0 10px;

    box-shadow:0 5px 15px rgba(0,0,0,.25);

    display:flex;

    align-items:center;

    gap:8px;

    transition:.3s;

}



.admin-title::after{

    content:"▼";

    font-size:11px;

    transition:.3s;

}



.admin-toolbar:hover .admin-title{

    background:#1f3f7a;

}



.admin-toolbar:hover .admin-title::after{

    transform:rotate(180deg);

}



.admin-menu{

    position:absolute;

    right:0;

    top:55px;

    width:240px;

    background:white;

    box-shadow:0 15px 35px rgba(0,0,0,.25);

    border-radius:12px;

    overflow:hidden;

    opacity:0;

    visibility:hidden;

    transform:translateY(-20px) scale(.95);

    transition:.35s ease;

}



.admin-toolbar:hover .admin-menu{

    opacity:1;

    visibility:visible;

    transform:translateY(0) scale(1);

}



.admin-menu a{

    display:block;

    padding:14px 18px;

    color:#1f2a44;

    text-decoration:none;

    font-size:14px;

    border-bottom:1px solid #eee;

    transition:.25s;

}



.admin-menu a:hover{

    background:#eaf2fb;

    color:#2f5aa8;

    padding-left:28px;

}

/* =========================
FOOTER
========================= */


.footer {

    background:#1f2a44;

    color:white;

    padding:30px;

    text-align:center;

}





@media(max-width:700px){


.header{

    flex-direction:column;

}


.profile-btn,
.logout-btn{

    margin-top:15px;

}


.hero h1{

    font-size:32px;

}


.dashboard{

    padding:20px;

}


}



</style>


</head>



<body>


<?php if(

    $staff['staff_job']=="Owner" ||

    $staff['staff_job']=="Manager" ||

    $staff['staff_job']=="Supervisor"

){ ?>


<div class="admin-toolbar">


<div class="admin-title">

⚙ Admin Panel

</div>





<div class="admin-menu">



<a href="admin/staff_management.php">

👥 Staff Management

</a>




<a href="admin/leave_management.php">

📝 Leave Management

</a>




<a href="admin/overtime_management.php">

⏱ Overtime Management

</a>

<a href="admin/salary_advance_management.php">

💵 Salary Advance

</a>

</div>


</div>



<?php } ?>


<!-- TOP BAR -->


<div class="top-bar">

<div>
Have any question? 📞 Get Help Now!
</div>


<div>
More Than a Wash, It's a Revival.
</div>


</div>





<!-- HEADER -->


<header class="header">


<div class="logo-section">


<img 
src="../images/logo.png"
class="logo"
>


<div class="brand-text">

<h1>
CARWASH KAMAL
</h1>


<span>
World Best Car Wash
</span>


</div>


</div>

<div>

<a href="pages/update_profile.php" class="profile-btn">
Update Profile
</a>


<a href="../auth/logout.php" class="logout-btn">
Logout
</a>

</div>


</header>







<!-- HERO -->


<section class="hero">


<div>


<h1>
Welcome Back,
<?php echo $staff['staff_name']; ?> 👋
</h1>


<p>

<?php echo $staff['staff_job']; ?>

<br>

Manage your car wash operations from here.

</p>


</div>


</section>







<!-- DASHBOARD -->


<section class="dashboard">



<div class="stats">



<div class="stat-card">

<h3>
Today's Reservations
</h3>

<h2>
<?php echo $today_reservations; ?>
</h2>

</div>





<div class="stat-card">

<h3>
Pending Reservations
</h3>

<h2>
<?php echo $pending; ?>
</h2>

</div>





<div class="stat-card">

<h3>
Attendance
</h3>


<h2>
<?php echo $attendance_status; ?>
</h2>



<?php if($attendance_status == "Absent"){ ?>


<form method="POST">


<button

type="submit"

name="check_attendance"

class="attendance-btn"

>

Check In

</button>


</form>


<?php } else { ?>


<p>

✅ Attendance Recorded

</p>


<?php } ?>


</div>





<div class="stat-card">

<h3>
Monthly Salary
</h3>

<h2>
RM <?php echo number_format($salary,2); ?>
</h2>

</div>



</div>







<div class="modules">



<a href="pages/reservation.php" class="module">

<h3>
🚗 Reservation
</h3>

<p>
Manage customer bookings and schedules.
</p>

</a>





<a href="pages/attendance.php" class="module">

<h3>
📅 Attendance
</h3>

<p>
View attendance records.
</p>

</a>





<a href="pages/salary_summary.php" class="module">

<h3>
💰 Salary Summary
</h3>

<p>
View monthly salary details.
</p>

</a>





<a href="pages/overtime.php" class="module">

<h3>
⏱ Overtime
</h3>

<p>
Manage overtime records.
</p>

</a>





<a href="pages/leave.php" class="module">

<h3>
📝 Leave
</h3>

<p>
Submit and view leave applications.
</p>

</a>





<a href="pages/salary_advance.php" class="module">

<h3>
💵 Salary Advance
</h3>

<p>
Request salary advance.
</p>

</a>



</div>



</section>







<footer class="footer">

<p>
© <?php echo date("Y"); ?> CARWASH KAMAL
</p>


<p>
More Than a Wash, It's a Revival.
</p>


</footer>







<?php if(isset($_SESSION['login_success'])): ?>


<script>


Swal.fire({

    icon:"success",

    title:"Welcome!",

    text:"<?php echo $_SESSION['login_success']; ?>",

    timer:2500,

    showConfirmButton:false

});


</script>


<?php

unset($_SESSION['login_success']);

endif;

?>





</body>

</html>