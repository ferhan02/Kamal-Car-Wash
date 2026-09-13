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
// GET SALARY ADVANCE REQUESTS
// =========================


$query = "

SELECT


salary_advance.*,


staff.staff_name,


staff.staff_email



FROM salary_advance



JOIN staff


ON salary_advance.staff_id = staff.staff_id



WHERE


(

staff.staff_name LIKE ?

OR staff.staff_email LIKE ?

OR salary_advance.reason LIKE ?

)



";





$params = [


    "%$search%",


    "%$search%",


    "%$search%"


];








if($status != ""){


    $query .= "

    AND salary_advance.status = ?

    ";


    $params[] = $status;


}







$query .= "

ORDER BY salary_advance.advance_id DESC

";








$stmt = $conn->prepare($query);



$stmt->execute($params);



$advance_list = $stmt->fetchAll();











// =========================
// DASHBOARD COUNT
// =========================


$stmt = $conn->prepare("

SELECT COUNT(*)

FROM salary_advance

WHERE status='Pending'

");



$stmt->execute();



$pending = $stmt->fetchColumn();







$stmt = $conn->prepare("

SELECT COUNT(*)

FROM salary_advance

WHERE status='Approved'

");



$stmt->execute();



$approved = $stmt->fetchColumn();








$stmt = $conn->prepare("

SELECT COUNT(*)

FROM salary_advance

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


    margin:0;


    color:#2f5aa8;


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





.search-box input,
.search-box select{


    padding:12px;


    border-radius:8px;


    border:1px solid #ccc;


    font-family:'Poppins',sans-serif;


}





.search-box input{


    flex:1;


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


    border:none;


    cursor:pointer;


    font-size:12px;


    display:inline-block;


    margin:3px;


    font-family:'Poppins',sans-serif;


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





.advance-details{


    text-align:left;


    padding:15px;


    background:#eaf2fb;


    border-radius:10px;


}





.badge{


    display:inline-flex;


    align-items:center;


    justify-content:center;


    min-width:90px;


    padding:8px 15px;


    border-radius:8px;


    color:white;


    font-size:13px;


    font-weight:700;


}





.pending{


    background:#f39c12;


}





.approved{


    background:#27ae60;


}





.rejected{


    background:#c0392b;


}





@media(max-width:800px){



    .container{


        width:90%;


    }



    .dashboard{


        flex-direction:column;


    }



    .search-box form{


        flex-direction:column;


    }


}



</style>





<body>


<section class="page-bg">


<div class="container">


<div class="card">





<div class="card-header">


<h1>

SALARY ADVANCE MANAGEMENT

</h1>


</div>
<div class="dashboard">


<div class="box">

<h2>

<?php echo $pending; ?>

</h2>

<p>
🟡 Pending
</p>

</div>





<div class="box">

<h2>

<?php echo $approved; ?>

</h2>

<p>
🟢 Approved
</p>

</div>





<div class="box">

<h2>

<?php echo $rejected; ?>

</h2>

<p>
🔴 Rejected
</p>

</div>


</div>








<div class="search-box">


<form onsubmit="return false;">



<input

type="text"

id="searchInput"

placeholder="Search staff name, email or reason..."

>





<select id="statusFilter">


<option value="">

All Status

</option>



<option value="Pending">

🟡 Pending

</option>




<option value="Approved">

🟢 Approved

</option>




<option value="Rejected">

🔴 Rejected

</option>



</select>




</form>


</div>








<div class="table-wrapper">


<table>


<tr>


<th>

ID

</th>


<th>

Staff

</th>


<th>

Email

</th>


<th>

Amount

</th>


<th>

Reason

</th>


<th>

Request Date

</th>


<th>

Status

</th>


<th>

Action

</th>


</tr>







<?php if(count($advance_list)>0){ ?>





<?php foreach($advance_list as $advance){ ?>



<tr

class="advance-row"

data-status="<?php echo $advance['status']; ?>"

>


<td>

<?php echo $advance['advance_id']; ?>

</td>





<td>

<b>

<?php echo $advance['staff_name']; ?>

</b>

</td>





<td>

<?php echo $advance['staff_email']; ?>

</td>





<td>

RM <?php echo number_format($advance['amount'],2); ?>

</td>





<td>

<?php echo $advance['reason']; ?>

</td>





<td>

<?php echo $advance['request_date']; ?>

</td>





<td class="status">



<?php if($advance['status']=="Pending"){ ?>


<span class="badge pending">

🟡 Pending

</span>


<?php } ?>





<?php if($advance['status']=="Approved"){ ?>


<span class="badge approved">

🟢 Approved

</span>


<?php } ?>





<?php if($advance['status']=="Rejected"){ ?>


<span class="badge rejected">

🔴 Rejected

</span>


<?php } ?>



</td>







<td>


<button

type="button"

class="action-btn view"

onclick="toggleDetails(<?php echo $advance['advance_id']; ?>)"

>

View

</button>






<?php if($advance['status']=="Pending"){ ?>


<a

href="approve_salary_advance.php?id=<?php echo $advance['advance_id']; ?>"

class="action-btn approve"

>

Approve

</a>





<a

href="reject_salary_advance.php?id=<?php echo $advance['advance_id']; ?>"

class="action-btn reject"

>

Reject

</a>



<?php } ?>



</td>



</tr>









<tr

id="details-<?php echo $advance['advance_id']; ?>"

class="details-row"

style="display:none;"

>


<td colspan="8">


<div class="advance-details">


<p>

<b>Staff:</b>

<?php echo $advance['staff_name']; ?>

</p>




<p>

<b>Requested Amount:</b>

RM <?php echo number_format($advance['amount'],2); ?>

</p>




<p>

<b>Reason:</b>

<?php echo $advance['reason']; ?>

</p>




<p>

<b>Request Date:</b>

<?php echo $advance['request_date']; ?>

</p>



</div>


</td>


</tr>







<?php } ?>







<?php } else { ?>


<tr>

<td colspan="8">

No salary advance request found.

</td>

</tr>


<?php } ?>




</table>


</div>






</div>


</div>


</section>








<?php include("../includes/footer.php"); ?>









<script>


// =========================
// EXPAND DETAILS
// =========================


function toggleDetails(id){


    let row = document.getElementById(

        "details-" + id

    );



    if(row.style.display === "none"){


        row.style.display="table-row";


    }

    else{


        row.style.display="none";


    }


}







// =========================
// LIVE SEARCH + FILTER
// =========================


let searchInput =

document.getElementById("searchInput");



let statusFilter =

document.getElementById("statusFilter");





function filterAdvance(){



    let search =

    searchInput.value.toLowerCase();




    let status =

    statusFilter.value;





    let rows =

    document.querySelectorAll(".advance-row");





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

filterAdvance

);





statusFilter.addEventListener(

"change",

filterAdvance

);



</script>







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






</body>

</html>