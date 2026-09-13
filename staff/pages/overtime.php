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
// GET STAFF DATA
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
// DEFAULT OVERTIME RATE
// =========================

// You can change this later
// or calculate from salary

$ot_rate = 10;



$success = "";
$error = "";



// =========================
// SUBMIT OVERTIME
// =========================

if(isset($_POST['submit'])){


    $date = $_POST['overtime_date'];

    $hours = $_POST['hours'];

    $reason = $_POST['reason'];



    if($hours <= 0){


        $error = "Please enter valid overtime hours.";


    }

    else{


        $total = $hours * $ot_rate;



        $stmt = $conn->prepare("
            INSERT INTO overtime

            (
                staff_id,
                overtime_date,
                hours,
                rate,
                total_amount,
                reason,
                status
            )

            VALUES
            (?,?,?,?,?,?,?)

        ");



        $stmt->execute([


            $staff_id,

            $date,

            $hours,

            $ot_rate,

            $total,

            $reason,

            "Pending"


        ]);



        $success = "Overtime submitted successfully.";


    }


}



// =========================
// MONTH FILTER
// =========================

$month = isset($_GET['month'])
? $_GET['month']
: date("m");


$year = date("Y");




// =========================
// OVERTIME HISTORY
// =========================


$stmt = $conn->prepare("
    SELECT *
    FROM overtime

    WHERE staff_id = ?

    AND MONTH(overtime_date)=?

    AND YEAR(overtime_date)=?

    ORDER BY overtime_date DESC

");


$stmt->execute([

    $staff_id,

    $month,

    $year

]);


$overtime_history = $stmt->fetchAll();




// =========================
// OVERTIME SUMMARY
// =========================


$stmt = $conn->prepare("
    SELECT

    SUM(hours) AS total_hours,

    SUM(total_amount) AS total_pay

    FROM overtime

    WHERE staff_id=?

    AND MONTH(overtime_date)=?

    AND YEAR(overtime_date)=?

    AND status='Approved'

");


$stmt->execute([

    $staff_id,

    $month,

    $year

]);


$summary = $stmt->fetch();



$total_hours = $summary['total_hours'] ?? 0;

$total_pay = $summary['total_pay'] ?? 0;



?>


<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../../CSS/style.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>


.page-bg{

    background:url("../../images/hero.jpg") center/cover no-repeat;

    min-height:100vh;

    padding:40px 0;

    display:flex;

    justify-content:center;

}



.container{

    width:1050px;

}



/* MAIN CARD */

.card{

    background:white;

    border-radius:20px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}



/* HEADER */

.card-header{

    background:#b7cde6;

    text-align:center;

    padding:20px;

    font-size:26px;

    font-weight:bold;

    color:#4f8cff;

}





/* SUMMARY */

.summary{

    display:flex;

    gap:20px;

    padding:25px;

}



.summary-card{

    flex:1;

    background:#eaf2fb;

    border-radius:15px;

    padding:20px;

    text-align:center;

}



.summary-card h3{

    font-size:15px;

}



.summary-card h2{

    color:#2f5aa8;

}





/* CONTENT */

.content{

    display:flex;

    gap:25px;

    padding:25px;

}



.left{

    width:45%;

}



.right{

    width:55%;

}





.section-title{

    font-weight:bold;

    margin-bottom:10px;

    color:#1f2a44;

}





.info-box{

    background:#e5e5e5;

    padding:10px;

    border-radius:8px;

    margin-bottom:10px;

}





input,
textarea{

    width:100%;

    padding:10px;

    border:1px solid #ccc;

    border-radius:8px;

    margin-bottom:15px;

}





.btn-submit{

    width:100%;

    padding:12px;

    border:none;

    border-radius:30px;

    background:#2f5aa8;

    color:white;

    font-weight:bold;

    cursor:pointer;

}



.btn-submit:hover{

    background:#1f3f7a;

}


/* TABLE */

table{

    width:100%;

    border-collapse:collapse;

    font-size:13px;

}



th,td{

    border:1px solid #aaa;

    padding:10px;

    text-align:center;

}



th{

    background:#d9e8ff;

}





.status{

    padding:5px 10px;

    border-radius:20px;

    font-weight:bold;

}



.Pending{

    background:#fff3cd;

    color:#856404;

}



.Approved{

    background:#d4edda;

    color:#155724;

}



.Rejected{

    background:#f8d7da;

    color:#721c24;

}



@media(max-width:800px){


    .content{

        flex-direction:column;

    }


    .left,
    .right{

        width:100%;

    }


    .summary{

        flex-direction:column;

    }


}


</style>





<section class="page-bg">


<div class="container">



<div class="card">



<div class="card-header">

OVERTIME MANAGEMENT

</div>




<!-- SUMMARY -->


<div class="summary">


<div class="summary-card">

<h3>

Approved Hours

</h3>


<h2>

<?php echo $total_hours; ?>

hrs

</h2>


</div>



<div class="summary-card">

<h3>

Overtime Pay

</h3>


<h2>

RM <?php echo number_format($total_pay,2); ?>

</h2>


</div>



</div>






<div class="content">



<!-- FORM -->

<div class="left">


<div class="section-title">

Staff Information

</div>


<div class="info-box">

<b>Name:</b>

<?php echo $staff['staff_name']; ?>

</div>


<div class="info-box">

<b>ID:</b>

<?php echo $staff['staff_id']; ?>

</div>


<div class="info-box">

<b>Position:</b>

<?php echo $staff['staff_job']; ?>

</div>





<form method="POST">


<div class="section-title">

Overtime Date

</div>


<input

type="date"

name="overtime_date"

required>





<div class="section-title">

Hours Worked

</div>


<input

type="number"

step="0.5"

name="hours"

id="hours"

oninput="calculateOT()"

required>





<div class="section-title">

Rate

</div>


<input

type="text"

value="RM <?php echo $ot_rate; ?>/hour"

readonly>





<div class="section-title">

Estimated Pay

</div>


<input

type="text"

id="total"

readonly>





<div class="section-title">

Reason

</div>


<textarea

name="reason"

rows="4"

required></textarea>





<button

class="btn-submit"

name="submit"

type="submit">


Submit Overtime


</button>



</form>



</div>







<!-- HISTORY -->

<div class="right">


<div class="section-title">

Overtime History

</div>



<table>


<tr>

<th>Date</th>

<th>Hours</th>

<th>Amount</th>

<th>Status</th>

</tr>




<?php foreach($overtime_history as $row){ ?>


<tr>


<td>

<?php echo $row['overtime_date']; ?>

</td>


<td>

<?php echo $row['hours']; ?>

hrs

</td>


<td>

RM <?php echo number_format($row['total_amount'],2); ?>

</td>


<td>


<span class="status <?php echo $row['status']; ?>">

<?php echo $row['status']; ?>

</span>


</td>


</tr>



<?php } ?>



</table>



</div>




</div>



</div>


</div>


</section>






<script>


function calculateOT(){


    let hours = document.getElementById("hours").value;


    let rate = <?php echo $ot_rate; ?>;


    let total = hours * rate;


    document.getElementById("total").value =
    "RM " + total.toFixed(2);


}


</script>




<?php if($success!=""){ ?>

<script>

Swal.fire({

    icon:"success",

    title:"Success",

    text:"<?php echo $success; ?>",

    timer:2000,

    showConfirmButton:false

});

</script>

<?php } ?>



<?php if($error!=""){ ?>

<script>

Swal.fire({

    icon:"error",

    title:"Error",

    text:"<?php echo $error; ?>"

});

</script>

<?php } ?>



<?php include("../includes/footer.php"); ?>