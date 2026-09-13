<?php

session_start();

include("../../config.php");


// CHECK LOGIN

if(!isset($_SESSION['staff_id'])){

    header("Location: ../../auth/staff_login.php");
    exit();

}


$staff_id = $_SESSION['staff_id'];



// GET STAFF PASSWORD

$stmt = $conn->prepare("
    SELECT staff_password
    FROM staff
    WHERE staff_id = ?
");


$stmt->execute([
    $staff_id
]);


$staff = $stmt->fetch(PDO::FETCH_ASSOC);



if(!$staff){

    die("Staff not found.");

}




// CHANGE PASSWORD

if(isset($_POST['change_password'])){


    $current_password = $_POST['current_password'];

    $new_password = $_POST['new_password'];

    $confirm_password = $_POST['confirm_password'];




    // CHECK CURRENT PASSWORD

    if($current_password != $staff['staff_password']){


        $_SESSION['error'] =
        "Current password is incorrect.";


        header("Location:change_password.php");

        exit();

    }





    // CHECK SAME PASSWORD

    if($new_password != $confirm_password){


        $_SESSION['error'] =
        "New passwords do not match.";


        header("Location:change_password.php");

        exit();

    }





    // PASSWORD LENGTH

    if(strlen($new_password) < 3){


        $_SESSION['error'] =
        "Password must contain at least 3 characters.";


        header("Location:change_password.php");

        exit();

    }




    // UPDATE PASSWORD


    $stmt = $conn->prepare("

        UPDATE staff

        SET staff_password = ?

        WHERE staff_id = ?

    ");



    $stmt->execute([

        $new_password,

        $staff_id

    ]);





    $_SESSION['success'] =
    "Password changed successfully.";


    header("Location:change_password.php");

    exit();


}


?>



<?php include("../includes/header.php"); ?>


<link rel="stylesheet" href="../../css/style.css">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<style>


.page-bg{

    background:url("../../images/hero.jpg")
    center/cover no-repeat;

    min-height:100vh;

    padding:50px 0;

    display:flex;

    justify-content:center;

    align-items:center;

}



.password-card{

    width:450px;

    background:white;

    border-radius:18px;

    padding:35px;

    box-shadow:0 12px 30px rgba(0,0,0,.2);

}



.password-header{

    text-align:center;

    margin-bottom:30px;

}



.password-icon{

    font-size:60px;

}



.password-header h1{

    color:#2f5aa8;

    margin-top:10px;

}



.input-box{

    margin-bottom:20px;

}



.input-box label{

    display:block;

    font-weight:bold;

    margin-bottom:6px;

}



.input-box input{

    width:100%;

    padding:12px;

    border-radius:8px;

    border:1px solid #ccc;

    font-family:'Poppins',sans-serif;

}



.btn{

    width:100%;

    padding:12px;

    border:none;

    border-radius:25px;

    background:#2f5aa8;

    color:white;

    font-weight:bold;

    font-size:16px;

    font-family:'Poppins',sans-serif;

    cursor:pointer;

}



.btn:hover{

    background:#1f3f7a;

}



.back-btn{

    display:block;

    text-align:center;

    margin-top:15px;

    padding:12px;

    border-radius:25px;

    background:#111827;

    color:white;

    text-decoration:none;

    font-weight:bold;

    font-size:16px;

    font-family:'Poppins',sans-serif;

}



.back-btn:hover{

    background:#000;

}


</style>





<section class="page-bg">


<div class="password-card">



<div class="password-header">


<div class="password-icon">

🔒

</div>


<h1>

CHANGE PASSWORD

</h1>


<p>

Keep your account secure.

</p>


</div>







<form method="POST">





<div class="input-box">

<label>

Current Password

</label>


<input

type="password"

name="current_password"

required

>

</div>







<div class="input-box">

<label>

New Password

</label>


<input

type="password"

name="new_password"

required

>

</div>







<div class="input-box">

<label>

Confirm New Password

</label>


<input

type="password"

name="confirm_password"

required

>

</div>







<button

class="btn"

name="change_password"

>

Change Password

</button>






<a

href="update_profile.php"

class="back-btn"

>

Back To Profile

</a>





</form>


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