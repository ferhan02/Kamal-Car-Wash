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
// SEARCH
// =========================

$search = $_GET['search'] ?? "";




// =========================
// GET STAFF LIST
// =========================

$stmt = $conn->prepare("

    SELECT *

    FROM staff

    WHERE

    staff_name LIKE ?

    OR staff_email LIKE ?

    OR staff_job LIKE ?

    ORDER BY staff_id ASC

");


$stmt->execute([

    "%$search%",

    "%$search%",

    "%$search%"

]);


$staff_list = $stmt->fetchAll();



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


    display:flex;


    justify-content:space-between;


    align-items:center;


}





.card-header h1{


    color:#2f5aa8;


    margin:0;


}







.add-btn{


    background:#2f5aa8;


    color:white;


    padding:12px 25px;


    border-radius:25px;


    text-decoration:none;


    font-weight:bold;


}





.add-btn:hover{


    background:#1f3f7a;


}






.search-box{


    padding:25px;


    background:#eaf2fb;


}





.search-box form{


    display:flex;


    gap:15px;


}





.search-box input{


    flex:1;


    padding:12px;


    border-radius:8px;


    border:1px solid #ccc;


}





.search-btn{


    padding:12px 25px;


    border:none;


    border-radius:25px;


    background:#2f5aa8;


    color:white;


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






.staff-img{


    width:70px;


    height:70px;


    border-radius:50%;


    object-fit:cover;


    border:3px solid #b7cde6;


}





.action-btn{


    padding:7px 12px;


    border-radius:15px;


    color:white;


    text-decoration:none;


    font-size:12px;


    margin:3px;


    display:inline-block;


}





.edit{


    background:#2ecc71;


}





.delete{


    background:#e74c3c;


}





</style>

<body>


<section class="page-bg">


<div class="container">


<div class="card">





<!-- HEADER -->

<div class="card-header">


<h1>

STAFF MANAGEMENT

</h1>



<a href="add_staff.php" class="add-btn">

+ Add New Staff

</a>



</div>







<!-- SEARCH -->


<div class="search-box">


<form method="GET">


<input

type="text"

name="search"

placeholder="Search staff name, email or job..."

value="<?php echo htmlspecialchars($search); ?>"

>



<button

class="search-btn"

>

Search

</button>



</form>



</div>







<!-- STAFF TABLE -->


<div class="table-wrapper">



<table>



<tr>


<th>

ID

</th>


<th>

Image

</th>


<th>

Name

</th>


<th>

Email

</th>


<th>

Phone

</th>


<th>

Job

</th>


<th>

Salary

</th>


<th>

Action

</th>


</tr>






<?php if(count($staff_list) > 0){ ?>





<?php foreach($staff_list as $staff){ ?>



<tr>



<td>

<?php echo $staff['staff_id']; ?>

</td>





<td>



<img

class="staff-img"

src="<?php

if(!empty($staff['staff_image'])){


    if(file_exists("../../images/uploads/".$staff['staff_image'])){


        echo "../../images/uploads/".$staff['staff_image'];


    }

    elseif(file_exists("../../images/".$staff['staff_image'])){


        echo "../../images/".$staff['staff_image'];


    }

    else{


        echo "../../images/uploads/default.png";


    }


}
else{


    echo "../../images/uploads/default.png";


}


?>"

>



</td>







<td>

<b>

<?php echo $staff['staff_name']; ?>

</b>

</td>







<td>

<?php echo $staff['staff_email']; ?>

</td>







<td>

<?php echo $staff['staff_phonenum']; ?>

</td>







<td>

<?php echo $staff['staff_job']; ?>

</td>







<td>

RM <?php echo number_format($staff['staff_salary'],2); ?>

</td>







<td>



<a

href="edit_staff.php?id=<?php echo $staff['staff_id']; ?>"

class="action-btn edit"

>

Edit

</a>






<a

href="delete_staff.php?id=<?php echo $staff['staff_id']; ?>"

class="action-btn delete"

>

Delete

</a>





</td>





</tr>





<?php } ?>





<?php } else { ?>





<tr>


<td colspan="8">

No staff found.

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





</body>

</html>