<?php

session_start();

include("../config.php");



// =========================
// STAFF EMAIL LOOKUP
// =========================

if(isset($_GET['check_email'])){


    $email = $_GET['check_email'];



    $stmt = $conn->prepare("

        SELECT

        staff_name,

        staff_job

        FROM staff

        WHERE staff_email = ?

    ");



    $stmt->execute([

        $email

    ]);



    $staff_preview = $stmt->fetch();




    if($staff_preview){


        echo json_encode([

            "status" => "found",

            "name" => $staff_preview['staff_name'],

            "job" => $staff_preview['staff_job']

        ]);


    }

    else{


        echo json_encode([

            "status" => "not_found"

        ]);


    }



    exit();


}






// =========================
// LOGIN PROCESS
// =========================


$error = "";




if($_SERVER["REQUEST_METHOD"] == "POST"){



    $email = $_POST['staff_email'];

    $password = $_POST['staff_password'];






    $stmt = $conn->prepare("

        SELECT *

        FROM staff

        WHERE staff_email = ?

    ");



    $stmt->execute([

        $email

    ]);



    $staff = $stmt->fetch();






    if(!$staff){


        $error = "Staff account does not exist.";


    }



    else if($staff['staff_password'] != $password){


        $error = "Incorrect password.";


    }



    else{



        $_SESSION['staff_id'] = $staff['staff_id'];

        $_SESSION['staff_name'] = $staff['staff_name'];

        $_SESSION['staff_job'] = $staff['staff_job'];




        if(isset($_POST['remember'])){


            setcookie(

                "staff_email",

                $email,

                time() + (86400 * 30),

                "/"

            );


        }





        $_SESSION['login_success'] =

        "Welcome back, ".$staff['staff_name']." 👋";




        header("Location: ../staff/staff_home.php");

        exit();



    }


}



?>





<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">



<title>

Staff Login | CARWASH KAMAL

</title>



<link rel="stylesheet" href="../css/style.css">


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<style>


/* =========================
   STAFF LOGIN PAGE
========================= */


*{

    box-sizing:border-box;

}



body{

    height:100vh;

    margin:0;

    display:flex;

    justify-content:center;

    align-items:center;

    background:#0f172a;

    font-family:'Poppins',sans-serif;

}






.login-wrapper{


    width:900px;

    height:520px;

    display:flex;

    border-radius:20px;

    overflow:hidden;

    box-shadow:

    0 20px 50px rgba(0,0,0,.5);


}






/* =========================
LEFT SIDE
========================= */


.login-left{


    flex:1;


    background:


    linear-gradient(

    135deg,

    #111827,

    #2f5aa8

    );


    color:white;


    display:flex;


    justify-content:center;


    align-items:center;


    flex-direction:column;


    text-align:center;


}






.icon-circle{


    width:100px;


    height:100px;


    border-radius:50%;


    background:

    rgba(255,255,255,.15);


    display:flex;


    justify-content:center;


    align-items:center;


    font-size:40px;


    margin-bottom:20px;


}






.login-left h1{


    font-size:30px;


    letter-spacing:2px;


    margin:10px;


}





.login-left p{


    font-size:15px;


    opacity:.9;


}





.login-left span{


    font-size:12px;


    opacity:.7;


    margin-top:10px;


}







/* =========================
RIGHT SIDE
========================= */


.login-right{


    flex:1;


    background:white;


    padding:45px;


    display:flex;


    flex-direction:column;


    justify-content:center;


}







.login-title{


    font-size:28px;


    font-weight:bold;


    color:#1f2a44;


    margin-bottom:8px;


}






.staff-label{


    color:#777;


    font-size:13px;


    margin-bottom:25px;


}







/* ERROR */


.error{


    background:#ffe5e5;


    color:#c0392b;


    padding:12px;


    border-radius:10px;


    margin-bottom:15px;


    font-size:13px;


}






/* INPUT */


.input-box{


    margin-bottom:18px;


}



.input-box label{


    display:block;


    font-size:13px;


    color:#555;


    margin-bottom:8px;


}






.input-box input{


    width:100%;


    padding:13px;


    border:none;


    border-bottom:2px solid #ccc;


    outline:none;


    font-size:14px;


}






.input-box input:focus{


    border-color:#2f5aa8;


}








/* PROFILE PREVIEW */


.profile-preview{


    display:flex;


    align-items:center;


    gap:15px;


    background:#eaf2fb;


    border-left:5px solid #2f5aa8;


    padding:12px;


    border-radius:10px;


    margin-bottom:18px;


    animation:slide .3s ease;


}






.preview-icon{


    font-size:30px;


}





.profile-preview strong{


    color:#1f2a44;


}





.profile-preview span{


    font-size:13px;


    color:#555;


}






@keyframes slide{


from{


    opacity:0;


    transform:translateY(-10px);


}


to{


    opacity:1;


    transform:translateY(0);


}


}







/* PASSWORD */


.password-wrapper{


    display:flex;


    align-items:center;


    border-bottom:2px solid #ccc;


}





.password-wrapper input{


    border:none;


}






.eye-btn{


    background:none;


    border:none;


    cursor:pointer;


    font-size:18px;


}







/* OPTIONS */


.login-options{


    display:flex;


    justify-content:space-between;


    align-items:center;


    font-size:13px;


    margin:15px 0 25px;


}






.login-options a{


    color:#2f5aa8;


    text-decoration:none;


    cursor:pointer;


}






.login-options a:hover{


    text-decoration:underline;


}






/* BUTTON */


.login-btn{


    width:100%;


    padding:13px;


    border:none;


    border-radius:30px;


    background:#2f5aa8;


    color:white;


    font-weight:bold;


    cursor:pointer;


    transition:.3s;


}






.login-btn:hover{


    background:#1f3f7a;


    transform:translateY(-2px);


}






@media(max-width:800px){



.login-wrapper{


    width:90%;


    height:auto;


    flex-direction:column;


}



.login-left{


    padding:40px;


}



.login-right{


    padding:30px;


}



}



</style>


</head>





<body>





<div class="login-wrapper">






<!-- =========================
LEFT SIDE
========================= -->


<div class="login-left">



<div class="icon-circle">

🚗

</div>



<h1>

CARWASH KAMAL

</h1>



<p>

Staff Management Portal

</p>



<span>

"More Than a Wash, It's a Revival."

</span>



</div>









<!-- =========================
RIGHT SIDE
========================= -->


<div class="login-right">






<div class="login-title">

Staff Login

</div>





<div class="staff-label">

Authorized personnel only

</div>







<?php if($error != ""){ ?>


<div class="error">

❌ <?php echo $error; ?>

</div>


<?php } ?>







<form method="POST">








<div class="input-box">


<label>

Staff Email

</label>



<input

type="email"

name="staff_email"

id="staff_email"

placeholder="Enter staff email"

value="<?php echo isset($_COOKIE['staff_email']) ? $_COOKIE['staff_email'] : ''; ?>"

required

>



</div>







<!-- PROFILE PREVIEW -->


<div id="profile-preview"

class="profile-preview"

style="display:none;"



>


<div class="preview-icon">

👤

</div>



<div>


<strong id="preview-name">

</strong>


<br>


<span id="preview-job">

</span>



</div>



</div>










<div class="input-box">


<label>

Password

</label>



<div class="password-wrapper">



<input

type="password"

name="staff_password"

id="password"

placeholder="Enter password"

required

>



<button

type="button"

id="toggle-password"

class="eye-btn"

>

👁

</button>



</div>


</div>









<div class="login-options">


<label>


<input

type="checkbox"

name="remember"

>


Remember Me


</label>





<a href="#"

onclick="forgotPassword()"

>

Forgot Password?

</a>



</div>







<button

type="submit"

class="login-btn"

>

LOGIN

</button>







</form>





</div>



</div>

<script>


// =========================
// PASSWORD SHOW / HIDE
// =========================


document

.getElementById("toggle-password")

.addEventListener("click",function(){



let password = document.getElementById("password");



if(password.type === "password"){


    password.type="text";


    this.innerHTML="🙈";


}

else{


    password.type="password";


    this.innerHTML="👁";


}


});








// =========================
// STAFF EMAIL PREVIEW
// =========================


document

.getElementById("staff_email")

.addEventListener("keyup",function(){



let email=this.value;



if(email.length < 5){


document.getElementById("profile-preview").style.display="none";


return;


}





fetch(

"staff_login.php?check_email="+email

)


.then(response=>response.json())


.then(data=>{



if(data.status=="found"){



document.getElementById("profile-preview").style.display="flex";



document.getElementById("preview-name").innerHTML=data.name;



document.getElementById("preview-job").innerHTML=

"Position: "+data.job;



}


else{


document.getElementById("profile-preview").style.display="none";


}



});



});










// =========================
// FORGOT PASSWORD
// =========================


function forgotPassword(){



Swal.fire({


icon:"info",


title:"Forgot Password?",


text:"Please contact your manager to reset your password.",


confirmButtonColor:"#2f5aa8"


});


}





</script>





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



<?php unset($_SESSION['login_success']); endif; ?>



</body>


</html>
