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
// STAFF ID
// =========================

$staff_id = $_SESSION['staff_id'];



// =========================
// MONTH SELECT
// =========================

$month = isset($_GET['month']) 
? $_GET['month'] 
: date("m");


$year = isset($_GET['year']) 
? $_GET['year'] 
: date("Y");




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




// =========================
// GET SALARY DATA
// =========================

$stmt = $conn->prepare("
    SELECT *
    FROM salary_summary
    WHERE staff_id = ?

    AND salary_month = ?

    AND salary_year = ?
");


$stmt->execute([

    $staff_id,

    $month,

    $year

]);


$salary = $stmt->fetch();



// DEFAULT VALUES

$allowance = 0;

$deduction = 0;

$bonus = 0;



if($salary){

    $allowance = $salary['allowance'];

    $deduction = $salary['deduction'];

    $bonus = $salary['bonus'];

}





// =========================
// GET APPROVED OVERTIME
// =========================


$stmt = $conn->prepare("

    SELECT

    SUM(total_amount) AS overtime_pay,

    SUM(hours) AS overtime_hours


    FROM overtime


    WHERE staff_id = ?

    AND MONTH(overtime_date)=?

    AND YEAR(overtime_date)=?

    AND status='Approved'

");


$stmt->execute([

    $staff_id,

    $month,

    $year

]);


$overtime = $stmt->fetch();



$overtime_pay = $overtime['overtime_pay'] ?? 0;

$overtime_hours = $overtime['overtime_hours'] ?? 0;





// =========================
// SALARY CALCULATION
// =========================


$basic_salary = $staff['staff_salary'];



$gross_salary =

$basic_salary

+ $allowance

+ $overtime_pay;



$net_salary =

$gross_salary

- $deduction

+ $bonus;






// =========================
// ATTENDANCE SUMMARY
// =========================


$stmt = $conn->prepare("

    SELECT

    SUM(is_present = 1) AS present_days,

    SUM(is_present = 0) AS absent_days


    FROM attendance


    WHERE staff_id = ?

    AND MONTH(attendance_date)=?

    AND YEAR(attendance_date)=?


");


$stmt->execute([

    $staff_id,

    $month,

    $year

]);


$attendance = $stmt->fetch();



$present_days = $attendance['present_days'] ?? 0;

$absent_days = $attendance['absent_days'] ?? 0;



?>



<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../../css/style.css">



<style>


.page-bg{

    background:url("../../images/hero.jpg") center/cover no-repeat;

    min-height:100vh;

    padding:40px 0;

    display:flex;

    justify-content:center;

}



.card{

    width:1000px;

    background:white;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}




.card-header{

    background:#b7cde6;

    text-align:center;

    padding:20px;

}



.card-header h1{

    color:#4f8cff;

    font-size:25px;

}



.card-header h2{

    color:#2f5aa8;

    margin-top:5px;

}




.section{

    padding:25px;

}




.month-box{

    text-align:center;

    margin-bottom:20px;

}



.month-box select{

    padding:8px 20px;

    border-radius:8px;

    border:1px solid #ccc;

}





.staff-info{

    background:#eaf2fb;

    padding:20px;

    border-radius:12px;

    line-height:1.8;

}






/* SALARY TABLE */


table{

    width:100%;

    border-collapse:collapse;

    margin-top:20px;

    font-size:13px;

}



th,td{

    border:1px solid #aaa;

    padding:12px;

    text-align:center;

}



th{

    background:#d9e8ff;

}





.total{

    background:#b7cde6;

    font-weight:bold;

}





/* SUMMARY CARDS */


.summary{

    display:flex;

    gap:15px;

    margin-top:25px;

}



.summary-card{

    flex:1;

    background:#eaf2fb;

    padding:20px;

    border-radius:12px;

    text-align:center;

}



.summary-card h2{

    color:#2f5aa8;

}



@media(max-width:800px){


    .card{

        width:95%;

    }


    .summary{

        flex-direction:column;

    }


    table{

        font-size:11px;

    }


}


</style>





<section class="page-bg">


<div class="card">



<div class="card-header">


<h1>

SALARY SUMMARY VIEWING

</h1>



<h2>

FOR 

<?php echo strtoupper(date("F",mktime(0,0,0,$month,1))); ?>

<?php echo $year; ?>


</h2>


</div>





<div class="section">





<!-- MONTH -->


<div class="month-box">


<form method="GET">


<select name="month" onchange="this.form.submit()">



<?php for($i=1;$i<=12;$i++){ ?>


<option

value="<?php echo $i; ?>"

<?php

if($i==$month){

echo "selected";

}

?>

>


<?php echo date("F",mktime(0,0,0,$i,1)); ?>


</option>



<?php } ?>


</select>



<input type="hidden" name="year" value="<?php echo $year; ?>">



</form>



</div>







<!-- STAFF -->


<div class="staff-info">


<b>Employee Detail</b>


<br><br>


<b>Name:</b>

<?php echo $staff['staff_name']; ?>


<br>


<b>Staff ID:</b>

<?php echo $staff['staff_id']; ?>


<br>


<b>Position:</b>

<?php echo $staff['staff_job']; ?>


</div>








<!-- SALARY TABLE -->


<table>


<tr>

<th>Basic Salary</th>

<th>Allowance</th>

<th>Overtime Pay</th>

<th>Gross Salary</th>

<th>Deduction</th>

<th>Bonus</th>

<th>Net Salary</th>


</tr>



<tr>


<td>

RM <?php echo number_format($basic_salary,2); ?>

</td>



<td>

RM <?php echo number_format($allowance,2); ?>

</td>




<td>

RM <?php echo number_format($overtime_pay,2); ?>

<br>

<small>

<?php echo $overtime_hours; ?> hrs

</small>


</td>




<td>

RM <?php echo number_format($gross_salary,2); ?>

</td>




<td>

RM <?php echo number_format($deduction,2); ?>

</td>




<td>

RM <?php echo number_format($bonus,2); ?>

</td>




<td class="total">

RM <?php echo number_format($net_salary,2); ?>

</td>



</tr>


</table>








<!-- SUMMARY -->


<div class="summary">


<div class="summary-card">

<h3>

Present Days

</h3>


<h2>

<?php echo $present_days; ?>

</h2>


</div>




<div class="summary-card">


<h3>

Absent Days

</h3>


<h2>

<?php echo $absent_days; ?>

</h2>


</div>





<div class="summary-card">


<h3>

OT Hours

</h3>


<h2>

<?php echo $overtime_hours; ?>

</h2>


</div>





<div class="summary-card">


<h3>

OT Pay

</h3>


<h2>

RM <?php echo number_format($overtime_pay,2); ?>

</h2>


</div>



</div>





</div>



</div>


</section>





<?php include("../includes/footer.php"); ?>