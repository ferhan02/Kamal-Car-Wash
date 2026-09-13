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


$staff_id = $_SESSION['staff_id'];




// =========================
// CHECK PERMISSION
// =========================

$stmt = $conn->prepare("
    SELECT staff_job
    FROM staff
    WHERE staff_id = ?
");


$stmt->execute([

    $staff_id

]);


$current_staff = $stmt->fetch();



if(
    $current_staff['staff_job'] != "Owner" &&
    $current_staff['staff_job'] != "Manager" &&
    $current_staff['staff_job'] != "Supervisor"
){

    header("Location: ../staff_home.php");
    exit();

}




// =========================
// SEARCH & FILTER
// =========================


$search = $_GET['search'] ?? "";

$status = $_GET['status'] ?? "";





// =========================
// GET LEAVE APPLICATIONS
// =========================


$query = "

SELECT 

leave_application.*,

staff.staff_name,

staff.staff_email


FROM leave_application


JOIN staff

ON leave_application.staff_id = staff.staff_id


WHERE

(
staff.staff_name LIKE ?

OR staff.staff_email LIKE ?

OR leave_application.leave_type LIKE ?

)



";


$params = [

"%$search%",

"%$search%",

"%$search%"

];





if($status != ""){


    $query .= "

    AND leave_application.status = ?

    ";


    $params[] = $status;


}




$query .= "

ORDER BY leave_application.leave_id DESC

";




$stmt = $conn->prepare($query);


$stmt->execute($params);


$leave_list = $stmt->fetchAll();





// =========================
// DASHBOARD COUNT
// =========================


$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM leave_application
    WHERE status='Pending'
");


$stmt->execute();


$pending = $stmt->fetchColumn();





$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM leave_application
    WHERE status='Approved'
");


$stmt->execute();


$approved = $stmt->fetchColumn();





$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM leave_application
    WHERE status='Rejected'
");


$stmt->execute();


$rejected = $stmt->fetchColumn();



?>





<?php include("../includes/admin_header.php"); ?>


<?php include("../includes/admin_toolbar.php"); ?>



<link rel="stylesheet" href="../../CSS/style.css">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>





<style>


.page-bg{


background:url("../../images/hero.jpg")

center/cover no-repeat;


min-height:100vh;


padding:40px 0;


display:flex;


justify-content:center;


}



.container{


width:1200px;


}




.card{


background:white;


border-radius:18px;


overflow:hidden;


box-shadow:0 12px 30px rgba(0,0,0,.2);


}



.card-header{


background:#b7cde6;


padding:20px;


}



.card-header h1{


margin:0;


color:#2f5aa8;


}





.dashboard{


display:flex;


gap:20px;


padding:25px;


background:#eaf2fb;


}




.box{


flex:1;


background:white;


padding:20px;


border-radius:15px;


text-align:center;


box-shadow:0 5px 15px rgba(0,0,0,.1);


}



.box h2{


color:#2f5aa8;


margin:0;


font-size:30px;


}



.box p{


margin:5px;


font-weight:bold;


}





.search-box{


padding:25px;


}





.search-box form{


display:flex;


gap:15px;


}





input,
select{


padding:12px;


border-radius:8px;


border:1px solid #ccc;


}





input{


flex:1;


}




button{


background:#2f5aa8;


color:white;


border:none;


padding:12px 25px;


border-radius:25px;


cursor:pointer;


}





.table-wrapper{


padding:25px;


overflow-x:auto;


}



table{


width:100%;


border-collapse:collapse;


font-size:14px;


}



th{


background:#d9e8ff;


}



th,
td{


border:1px solid #ccc;


padding:12px;


text-align:center;


}



.action-btn{


padding:7px 12px;


border-radius:15px;


color:white;


text-decoration:none;


font-size:12px;


display:inline-block;


margin:3px;


}



.view{


background:#3498db;


}



.approve{


background:#2ecc71;


}



.reject{


background:#e74c3c;


}



.status{

font-weight:bold;

}



.details-row td{

background:#f5f9ff;

}



.leave-details{

text-align:left;

padding:15px;

border-radius:10px;

background:#eaf2fb;

}



</style>





<body>


<section class="page-bg">


<div class="container">


<div class="card">



<div class="card-header">


<h1>

LEAVE MANAGEMENT

</h1>


</div>






<div class="dashboard">


<div class="box">

<h2>

<?php echo $pending; ?>

</h2>

<p>
Pending
</p>

</div>



<div class="box">

<h2>

<?php echo $approved; ?>

</h2>

<p>
Approved
</p>

</div>



<div class="box">

<h2>

<?php echo $rejected; ?>

</h2>

<p>
Rejected
</p>

</div>


</div>







<div class="search-box">


<form onsubmit="return false;">


<input

type="text"

id="searchInput"

placeholder="Search staff name, email or leave type..."

>


<select id="statusFilter">


<option value="">
All Status
</option>


<option value="Pending"
<?php if($status=="Pending") echo "selected"; ?>
>
Pending
</option>



<option value="Approved"
<?php if($status=="Approved") echo "selected"; ?>
>
Approved
</option>



<option value="Rejected"
<?php if($status=="Rejected") echo "selected"; ?>
>
Rejected
</option>



</select>

</form>


</div>







<div class="table-wrapper">


<table>


<tr>


<th>ID</th>

<th>Staff</th>

<th>Email</th>

<th>Leave Type</th>

<th>Date</th>

<th>Days</th>

<th>Status</th>

<th>Action</th>


</tr>





<?php if(count($leave_list)>0){ ?>



<?php foreach($leave_list as $leave){ ?>


<tr

class="leave-row"

data-status="<?php echo $leave['status']; ?>"

>



<td>

<?php echo $leave['leave_id']; ?>

</td>




<td>

<b>
<?php echo $leave['staff_name']; ?>
</b>

</td>



<td>

<?php echo $leave['staff_email']; ?>

</td>




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

<?php

$start = new DateTime($leave['start_date']);

$end = new DateTime($leave['end_date']);

$days = $start->diff($end)->days + 1;


echo $days;

?>

</td>




<td class="status">

<?php echo $leave['status']; ?>

</td>




<td>


<button

type="button"

class="action-btn view"

onclick="toggleDetails(<?php echo $leave['leave_id']; ?>)"

>

View

</button>



<a

href="approve_leave.php?id=<?php echo $leave['leave_id']; ?>"

class="action-btn approve"

>

Approve

</a>




<a

href="reject_leave.php?id=<?php echo $leave['leave_id']; ?>"

class="action-btn reject"

>

Reject

</a>



</td>



</tr>



<tr

id="details-<?php echo $leave['leave_id']; ?>"

class="details-row"

style="display:none;"

>

<td colspan="8">


<div class="leave-details">


<p>
<b>Reason:</b>

<?php echo $leave['reason']; ?>

</p>



<p>
<b>Applied Date:</b>

<?php echo $leave['apply_date']; ?>

</p>


</div>


</td>

</tr>



<?php } ?>



<?php } else { ?>


<tr>

<td colspan="8">

No leave application found.

</td>

</tr>


<?php } ?>



</table>



</div>




</div>


</div>


</section>







<?php include("../includes/footer.php"); ?>






<?php if(isset($_SESSION['success'])): ?>


<script>

Swal.fire({

icon:"success",

title:"Success",

text:"<?php echo $_SESSION['success']; ?>",

timer:2000,

showConfirmButton:false

});

</script>


<?php unset($_SESSION['success']); endif; ?>







<?php if(isset($_SESSION['error'])): ?>


<script>

Swal.fire({

icon:"error",

title:"Error",

text:"<?php echo $_SESSION['error']; ?>"

});

</script>


<?php unset($_SESSION['error']); endif; ?>


<script>


function toggleDetails(id){


    let row = document.getElementById(
        "details-" + id
    );


    if(row.style.display === "none"){

        row.style.display = "table-row";

    }

    else{

        row.style.display = "none";

    }


}





// =========================
// LIVE SEARCH + FILTER
// =========================


let searchInput =
document.getElementById("searchInput");


let statusFilter =
document.getElementById("statusFilter");



function filterLeaves(){


    let search = 
    searchInput.value.toLowerCase();


    let status =
    statusFilter.value;



    let rows =
    document.querySelectorAll(".leave-row");



    rows.forEach(function(row){


        let text =
        row.innerText.toLowerCase();



        let rowStatus =
        row.dataset.status;



        let matchSearch =
        text.includes(search);



        let matchStatus =
        status === "" ||
        rowStatus === status;



        if(matchSearch && matchStatus){

            row.style.display="";

        }

        else{

            row.style.display="none";

        }



    });


}



searchInput.addEventListener(

"keyup",

filterLeaves

);



statusFilter.addEventListener(

"change",

filterLeaves

);



</script>


</body>

</html>