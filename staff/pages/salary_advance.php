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
// SALARY LIMIT
// =========================

$salary = $staff['staff_salary'];

$max_advance = $salary * 0.5;



$success = "";
$error = "";




// =========================
// SUBMIT REQUEST
// =========================

if(isset($_POST['submit'])){


    $amount = $_POST['amount'];

    $reason = $_POST['reason'];



    if($amount > $max_advance){


        $error = "Maximum advance allowed is RM " . $max_advance;


    }

    else if($amount <= 0){


        $error = "Please enter a valid amount.";


    }

    else{


        $stmt = $conn->prepare("
            INSERT INTO salary_advance
            (
                staff_id,
                amount,
                reason,
                request_date,
                status
            )

            VALUES
            (?,?,?,?,?)
        ");


        $stmt->execute([

            $staff_id,

            $amount,

            $reason,

            date("Y-m-d"),

            "Pending"

        ]);



        $success = "Salary advance request submitted successfully.";


    }


}



// =========================
// GET REQUEST HISTORY
// =========================


$stmt = $conn->prepare("
    SELECT *
    FROM salary_advance
    WHERE staff_id = ?

    ORDER BY request_date DESC
");


$stmt->execute([
    $staff_id
]);


$history = $stmt->fetchAll();



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



.card{

    width:950px;

    background:white;

    border-radius:15px;

    display:flex;

    overflow:hidden;

    box-shadow:0 10px 30px rgba(0,0,0,0.2);

}



/* LEFT */

.card-left{

    width:40%;

    background:#f3f7ff;

    display:flex;

    justify-content:center;

    align-items:center;

}



.circle-img{

    width:260px;

    height:260px;

    border-radius:50%;

    background:#dbe8ff;

    display:flex;

    justify-content:center;

    align-items:center;

    overflow:hidden;

}



.circle-img img{

    width:200px;

}




/* RIGHT */

.card-right{

    width:60%;

    padding:40px;

}



.title{

    text-align:center;

    font-size:24px;

    font-weight:bold;

    color:#4f8cff;

    margin-bottom:20px;

}



/* STAFF INFO */

.staff-info{

    margin-bottom:20px;

}



.staff-info p{

    border-bottom:2px solid #ccc;

    padding:10px 0;

}



.highlight{

    color:#4f8cff;

    font-size:12px;

}



/* INPUT */

.input-box{

    margin-bottom:15px;

}



.input-box input,

.input-box textarea{

    width:100%;

    border:none;

    border-bottom:2px solid #ccc;

    padding:8px;

    outline:none;

    background:transparent;

}



.input-box input:focus,

.input-box textarea:focus{

    border-bottom:2px solid #4f8cff;

}



/* BUTTON */

.btn-submit{

    width:100%;

    padding:12px;

    background:#2f5aa8;

    color:white;

    border:none;

    border-radius:30px;

    cursor:pointer;

    font-weight:bold;

}



.btn-submit:hover{

    background:#1f3f7a;

}


/* HISTORY */

.history{

    width:950px;

    margin-top:25px;

    background:white;

    border-radius:15px;

    padding:25px;

}



.history h2{

    text-align:center;

    color:#4f8cff;

}



table{

    width:100%;

    border-collapse:collapse;

}



th,td{

    border:1px solid #ccc;

    padding:10px;

    text-align:center;

}



th{

    background:#eaf2fb;

}



.pending{

    color:orange;

    font-weight:bold;

}



.approved{

    color:green;

    font-weight:bold;

}



.rejected{

    color:red;

    font-weight:bold;

}


</style>





<section class="page-bg">



<div>



<div class="card">



<!-- IMAGE -->

<div class="card-left">


<div class="circle-img">


<img 
src="../../images/<?php echo !empty($staff['staff_image']) ? $staff['staff_image'] : 'uploads/default.png'; ?>"
>


</div>


</div>





<!-- FORM -->


<div class="card-right">



<div class="title">

SALARY ADVANCE REQUEST

</div>


<div class="staff-info">


<p>

<b>Name:</b>

<?php echo $staff['staff_name']; ?>


</p>



<p>

<b>Staff ID:</b>

<?php echo $staff['staff_id']; ?>


</p>



<p>

<b>Position:</b>

<?php echo $staff['staff_job']; ?>


</p>



<p>

<b>Basic Salary:</b>

RM <?php echo $salary; ?>


</p>



<div class="highlight">

Maximum 50% of basic salary:
RM <?php echo $max_advance; ?>

</div>


</div>






<form method="POST">



<div class="input-box">


<input

type="number"

name="amount"

placeholder="Amount Request"

required>


</div>




<div class="input-box">


<textarea

name="reason"

rows="3"

placeholder="Reason"

required></textarea>


</div>




<button

class="btn-submit"

name="submit"

type="submit">


Submit Request


</button>



</form>




</div>



</div>





<!-- HISTORY -->

<div class="history">


<h2>

Request History

</h2>


<br>



<table>


<tr>

<th>Date</th>

<th>Amount</th>

<th>Reason</th>

<th>Status</th>


</tr>



<?php foreach($history as $row){ ?>


<tr>


<td>

<?php echo $row['request_date']; ?>

</td>



<td>

RM <?php echo $row['amount']; ?>

</td>



<td>

<?php echo $row['reason']; ?>

</td>



<td class="<?php echo strtolower($row['status']); ?>">


<?php echo $row['status']; ?>


</td>



</tr>


<?php } ?>



</table>


</div>



</div>


</section>





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