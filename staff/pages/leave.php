<?php

session_start();

include("../../config.php");


// =========================
// CHECK LOGIN
// =========================

if(!isset($_SESSION['staff_id'])){

    header("Location: ../../auth/staff_login.php");
    exit();

}



// =========================
// GET STAFF INFORMATION
// =========================

$staff_id = $_SESSION['staff_id'];


$stmt = $conn->prepare("
    SELECT *
    FROM staff
    WHERE staff_id = ?
");


$stmt->execute([
    $staff_id
]);


$staff = $stmt->fetch();




// =========================
// SUBMIT LEAVE
// =========================

$message = "";


if(isset($_POST['submit'])){


    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = $_POST['reason'];



    if($end_date < $start_date){


        $message = "End date cannot be earlier than start date.";


    }

    else{


        $stmt = $conn->prepare("
            INSERT INTO leave_application
            (
                staff_id,
                leave_type,
                start_date,
                end_date,
                reason,
                apply_date,
                status
            )

            VALUES
            (?,?,?,?,?,?,?)
        ");



        $stmt->execute([

            $staff_id,
            $leave_type,
            $start_date,
            $end_date,
            $reason,
            date("Y-m-d"),
            "Pending"

        ]);



        $message = "Leave application submitted successfully.";


    }


}



// =========================
// GET LEAVE HISTORY
// =========================

$stmt = $conn->prepare("
    SELECT *
    FROM leave_application
    WHERE staff_id = ?
    ORDER BY apply_date DESC
");


$stmt->execute([
    $staff_id
]);


$leave_history = $stmt->fetchAll();


?>


<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../../CSS/style.css">



<style>


.page-bg {

    background:url("../../images/hero.jpg") center/cover no-repeat;

    min-height:100vh;

    padding:40px 0;

    display:flex;

    justify-content:center;

}



.container {

    width:950px;

}



.card {

    background:white;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}



.card-header {

    background:#b7cde6;

    text-align:center;

    padding:20px;

    color:#4f8cff;

    font-size:26px;

    font-weight:bold;

}



.card-body {

    display:flex;

    padding:30px;

    gap:30px;

}



.left {

    width:55%;

}



.right {

    width:45%;

    display:flex;

    justify-content:center;

    align-items:center;

}



.right img {

    width:250px;

    height:250px;

    border-radius:50%;

    object-fit:cover;

    border:5px solid #b7cde6;

}



.section-title {

    font-weight:bold;

    color:#1f2a44;

    margin-bottom:15px;

}



.info-box {

    background:#e5e5e5;

    padding:10px;

    border-radius:6px;

    margin-bottom:15px;

}



.input-box {

    margin-bottom:15px;

}



input,
select,
textarea {

    width:100%;

    padding:10px;

    border:1px solid #ccc;

    border-radius:6px;

}



textarea {

    resize:none;

}



.duration {

    color:#4f8cff;

    font-size:13px;

    margin-top:-10px;

    margin-bottom:15px;

}



.btn-submit {

    width:100%;

    padding:12px;

    border:none;

    border-radius:30px;

    background:#2f5aa8;

    color:white;

    font-weight:bold;

    cursor:pointer;

}



.btn-submit:hover {

    background:#1f3f7a;

}



.message {

    text-align:center;

    color:#2f5aa8;

    margin-bottom:15px;

}



.history {

    margin-top:30px;

    background:white;

    padding:25px;

    border-radius:18px;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}



.history h2 {

    text-align:center;

    color:#4f8cff;

}



table {

    width:100%;

    border-collapse:collapse;

}



th,
td {

    border:1px solid #ccc;

    padding:10px;

    text-align:center;

}



th {

    background:#d9e8ff;

}



.pending {

    color:orange;

    font-weight:bold;

}



.approved {

    color:green;

    font-weight:bold;

}



.rejected {

    color:red;

    font-weight:bold;

}


</style>




<section class="page-bg">


<div class="container">


<div class="card">


<div class="card-header">

LEAVE APPLICATION

</div>



<div class="card-body">


<div class="left">

<?php if($message!=""){ ?>

<div class="message">

<?php echo $message; ?>

</div>

<?php } ?>



<div class="section-title">

Employee Detail

</div>



<div class="info-box">

<b>Name:</b>

<?php echo $staff['staff_name']; ?>

</div>



<div class="info-box">

<b>Staff ID:</b>

<?php echo $staff['staff_id']; ?>

</div>



<div class="info-box">

<b>Position:</b>

<?php echo $staff['staff_job']; ?>

</div>





<div class="section-title">

Leave Detail

</div>





<form method="POST">



<div class="input-box">

<label>

Leave Type

</label>


<select name="leave_type" required>


<option>

Annual Leave

</option>


<option>

Sick Leave

</option>


<option>

Emergency Leave

</option>


<option>

Unpaid Leave

</option>


</select>


</div>






<div class="input-box">

<label>

Start Date

</label>


<input

type="date"

name="start_date"

required

>


</div>






<div class="input-box">

<label>

End Date

</label>


<input

type="date"

name="end_date"

required

>


</div>






<div class="input-box">


<textarea

name="reason"

rows="4"

placeholder="Reason"

required

></textarea>


</div>






<button

class="btn-submit"

name="submit"

type="submit"

>

Submit Application

</button>




</form>



</div>







<div class="right">


<img 

src="../../images/<?php echo !empty($staff['staff_image']) ? $staff['staff_image'] : 'uploads/default.png'; ?>"

class="staff-profile-img"

>


</div>



</div>



</div>






<!-- HISTORY -->


<div class="history">


<h2>

Leave History

</h2>



<br>





<table>


<tr>

<th>
Type
</th>


<th>
Date
</th>


<th>
Reason
</th>


<th>
Status
</th>

</tr>





<?php foreach($leave_history as $leave){ ?>



<tr>


<td>

<?php echo $leave['leave_type']; ?>

</td>



<td>

<?php echo $leave['start_date']; ?>

<br>

to

<br>

<?php echo $leave['end_date']; ?>

</td>

<td>

<?php echo $leave['reason']; ?>

</td>

<td class="<?php echo strtolower($leave['status']); ?>">

<?php echo $leave['status']; ?>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</section>


<?php include("../includes/footer.php"); ?>