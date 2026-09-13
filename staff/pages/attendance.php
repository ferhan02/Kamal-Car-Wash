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
// GET ALL STAFF FOR SELECTOR
// =========================

$stmt = $conn->prepare("
    SELECT staff_id, staff_name
    FROM staff
    ORDER BY staff_id ASC
");

$stmt->execute();

$staff_list = $stmt->fetchAll();



// =========================
// SELECT STAFF
// DEFAULT = FIRST STAFF
// =========================

if(isset($_GET['staff_id'])){

    $selected_id = $_GET['staff_id'];

}
else{

    $selected_id = $staff_list[0]['staff_id'];

}



// =========================
// GET SELECTED STAFF INFO
// =========================

$stmt = $conn->prepare("
    SELECT *
    FROM staff
    WHERE staff_id = ?
");

$stmt->execute([
    $selected_id
]);


$staff = $stmt->fetch();




// =========================
// YEAR SELECT
// =========================

$year = isset($_GET['year'])
? $_GET['year']
: date("Y");




// =========================
// GET ATTENDANCE DATA
// =========================

$stmt = $conn->prepare("
    SELECT *
    FROM attendance
    WHERE staff_id = ?

    AND YEAR(attendance_date) = ?
");

$stmt->execute([

    $selected_id,

    $year

]);


$attendance = $stmt->fetchAll();



// STORE ATTENDANCE

$attendance_data = [];


foreach($attendance as $row){

    $date = strtotime($row['attendance_date']);

    $month = date("n",$date);

    $day = date("j",$date);


    $attendance_data[$month][$day] = $row['is_present'];

}


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

    width:1100px;

    background:white;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}



.card-header{

    background:#b7cde6;

    text-align:center;

    padding:18px;

    font-size:26px;

    font-weight:bold;

    color:#4f8cff;

}



.staff-info{

    padding:25px;

    display:flex;

    justify-content:space-between;

    align-items:center;

}



.info{

    line-height:1.8;

}



/* STAFF SELECT BUTTON */

.staff-select select{

    padding:10px;

    border-radius:8px;

    border:1px solid #ccc;

    font-weight:bold;

}





.table-wrapper{

    padding:20px;

    overflow-x:auto;

}



table{

    width:100%;

    border-collapse:collapse;

    font-size:12px;

    text-align:center;

}



th,td{

    border:1px solid #ccc;

    padding:7px;

}



th{

    background:#e9e9e9;

}



.day{

    background:#f3f3f3;

    font-weight:bold;

}



.present{

    background:#b7f7c5;

    font-size:16px;

}



.absent{

    background:#ffb3b3;

    font-size:16px;

}


</style>




<section class="page-bg">


<div class="card">


<div class="card-header">

ATTENDANCE RECORD

</div>




<!-- STAFF INFORMATION -->

<div class="staff-info">


<div class="info">


<p>
<b>Staff ID:</b>
<?php echo $staff['staff_id']; ?>
</p>


<p>
<b>Email:</b>
<?php echo $staff['staff_email']; ?>
</p>


<p>
<b>Job:</b>
<?php echo $staff['staff_job']; ?>
</p>


<p>
<b>State:</b>
<?php echo $staff['staff_state']; ?>
</p>


</div>




<!-- STAFF BUTTON -->

<div class="staff-select">


<form method="GET">


<input 
type="hidden"
name="year"
value="<?php echo $year; ?>"
>


<label>
<b>Staff Name</b>
</label>


<br>


<select 
name="staff_id" 
onchange="this.form.submit()"
>


<?php foreach($staff_list as $person){ ?>


<option 
value="<?php echo $person['staff_id']; ?>"
<?php 

if($person['staff_id']==$selected_id){

    echo "selected";

}

?>
>


<?php echo $person['staff_name']; ?>


</option>


<?php } ?>


</select>


</form>


</div>



</div>


<!-- YEAR SELECT -->

<div style="text-align:center; padding:20px;">


<b>Select Year:</b>


<br><br>


<?php

$current_year = date("Y");


for($i=$current_year-2; $i<=$current_year; $i++){

?>


<a

href="?staff_id=<?php echo $selected_id; ?>&year=<?php echo $i; ?>"

style="
text-decoration:none;
background:
<?php echo ($year==$i) ? '#2f5aa8' : '#dbe9ff'; ?>;
color:
<?php echo ($year==$i) ? 'white' : '#1f2a44'; ?>;
padding:10px 20px;
border-radius:20px;
margin:5px;
font-weight:bold;
"

>

<?php echo $i; ?>

</a>


<?php } ?>


</div>


<!-- ATTENDANCE TABLE -->


<div class="table-wrapper">


<table>


<tr>

<th class="day">
DAY
</th>


<th>JAN</th>
<th>FEB</th>
<th>MAR</th>
<th>APR</th>
<th>MAY</th>
<th>JUN</th>
<th>JUL</th>
<th>AUG</th>
<th>SEP</th>
<th>OCT</th>
<th>NOV</th>
<th>DEC</th>


</tr>




<?php for($day=1;$day<=31;$day++){ ?>


<tr>


<td class="day">

<?php echo $day; ?>

</td>



<?php for($month=1;$month<=12;$month++){ ?>


<?php

$status = "";

$class = "";


if(isset($attendance_data[$month][$day])){


    if($attendance_data[$month][$day]==1){

        $status="✔";
        $class="present";

    }
    else{

        $status="✘";
        $class="absent";

    }

}


?>


<td class="<?php echo $class; ?>">

<?php echo $status; ?>

</td>



<?php } ?>



</tr>


<?php } ?>



</table>



</div>



</div>


</section>



<?php include("../includes/footer.php"); ?>