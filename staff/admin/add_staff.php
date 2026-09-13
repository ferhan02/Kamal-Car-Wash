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


$current_id = $_SESSION['staff_id'];



// =========================
// GET CURRENT STAFF ROLE
// =========================

$stmt = $conn->prepare("
    SELECT staff_job
    FROM staff
    WHERE staff_id = ?
");

$stmt->execute([
    $current_id
]);

$current_staff = $stmt->fetch();

if(!$current_staff){

    header("Location: ../../auth/staff_login.php");
    exit();

}



// =========================
// CHECK ADMIN ACCESS
// =========================

if(
    !in_array(
        $current_staff['staff_job'],
        [
            "Owner",
            "Manager",
            "Supervisor"
        ]
    )
){

    header("Location: ../staff_home.php");
    exit();

}



// =========================
// AVAILABLE JOB POSITIONS
// =========================

if($current_staff['staff_job']=="Owner"){


    $available_jobs = [

        "Manager",
        "Supervisor",
        "Receptionist",
        "Car Washer",
        "Cashier"

    ];


}


elseif($current_staff['staff_job']=="Manager"){


    $available_jobs = [

        "Manager",
        "Supervisor",
        "Receptionist",
        "Car Washer",
        "Cashier"

    ];


}


else{


    $available_jobs = [

        "Receptionist",
        "Car Washer",
        "Cashier"

    ];


}



// =========================
// STATE LIST
// =========================

$states = [

    "Johor",
    "Kedah",
    "Kelantan",
    "Melaka",
    "Negeri Sembilan",
    "Pahang",
    "Penang",
    "Perak",
    "Perlis",
    "Sabah",
    "Sarawak",
    "Selangor",
    "Terengganu"

];




// =========================
// ADD STAFF
// =========================

if(isset($_POST['add_staff'])){


    $name = $_POST['staff_name'];

    $email = $_POST['staff_email'];

    $password = $_POST['staff_password'];

    $phone = $_POST['staff_phonenum'];

    $dob = $_POST['staff_dob'];

    $state = $_POST['staff_state'];

    $hiredate = $_POST['staff_hiredate'];

    $salary = $_POST['staff_salary'];

    $job = $_POST['staff_job'];

    $gender = $_POST['staff_gender'];





    // CHECK JOB PERMISSION

    if(!in_array($job,$available_jobs)){


        $_SESSION['error'] =
        "You are not allowed to create this position.";


        header("Location:add_staff.php");

        exit();

    }





    // CHECK DUPLICATE EMAIL

    $stmt = $conn->prepare("
        SELECT staff_id
        FROM staff
        WHERE staff_email = ?
    ");

    $stmt->execute([

        $email

    ]);


    if($stmt->fetch()){


        $_SESSION['error'] =
        "Email already exists.";


        header("Location:add_staff.php");

        exit();

    }





    // =========================
    // IMAGE UPLOAD
    // =========================

    $image = "uploads/default.png";



    if(!empty($_FILES['staff_image']['name'])){


        $allowed = [

            "jpg",
            "jpeg",
            "png",
            "webp"

        ];



        $extension = strtolower(

            pathinfo(

                $_FILES['staff_image']['name'],

                PATHINFO_EXTENSION

            )

        );



        if(!in_array($extension,$allowed)){


            $_SESSION['error'] =
            "Only JPG, JPEG, PNG and WEBP images are allowed.";


            header("Location:add_staff.php");

            exit();

        }




        if($_FILES['staff_image']['size'] > 2000000){


            $_SESSION['error'] =
            "Image size must be below 2MB.";


            header("Location:add_staff.php");

            exit();

        }





        $folder = "../../images/uploads/staff/";



        if(!is_dir($folder)){


            mkdir($folder,0777,true);

        }





        $filename =

        "staff_"

        .time()

        ."."

        .$extension;





        move_uploaded_file(

            $_FILES['staff_image']['tmp_name'],

            $folder.$filename

        );





        $image = "uploads/staff/".$filename;


    }






    // =========================
    // PASSWORD HASH
    // =========================

    $password = password_hash(

        $password,

        PASSWORD_DEFAULT

    );





    // =========================
    // INSERT STAFF
    // =========================

    $stmt = $conn->prepare("

        INSERT INTO staff

        (

            staff_name,

            staff_email,

            staff_password,

            staff_phonenum,

            staff_dob,

            staff_state,

            staff_hiredate,

            staff_salary,

            staff_job,

            staff_gender,

            staff_image

        )

        VALUES

        (?,?,?,?,?,?,?,?,?,?,?)

    ");



    $stmt->execute([


        $name,

        $email,

        $password,

        $phone,

        $dob,

        $state,

        $hiredate,

        $salary,

        $job,

        $gender,

        $image


    ]);





    $_SESSION['success'] =
    "Staff account created successfully.";


    header("Location:staff_management.php");

    exit();


}



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

    width:1000px;

}



.card{

    background:white;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,.2);

}



.card-header{

    background:#b7cde6;

    padding:25px;

    text-align:center;

}



.card-header h1{

    margin:0;

    color:#2f5aa8;

    font-size:30px;

}





.form-body{

    padding:35px;

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;

}



.input-box{

    display:flex;

    flex-direction:column;

}



.input-box label{

    font-weight:bold;

    margin-bottom:8px;

    color:#1f2a44;

}



.input-box input,
.input-box select{

    padding:12px;

    border-radius:8px;

    border:1px solid #ccc;

    font-family:'Poppins',sans-serif;

}



.input-box input:focus,
.input-box select:focus{

    outline:none;

    border-color:#2f5aa8;

}





.full{

    grid-column:1 / 3;

}



.image-box{

    text-align:center;

}



.image-preview{

    width:150px;

    height:150px;

    border-radius:50%;

    object-fit:cover;

    border:5px solid #b7cde6;

    margin-bottom:15px;

}





.file-input{

    width:auto !important;

}





.submit-btn{

    width:100%;

    padding:14px;

    background:#2f5aa8;

    color:white;

    border:none;

    border-radius:25px;

    font-weight:700;

    font-family:'Poppins',sans-serif;

    cursor:pointer;

    transition:.3s;

}



.submit-btn:hover{

    background:#1f3f7a;

}





.back-btn{

    display:block;

    text-align:center;

    margin-top:15px;

    padding:12px;

    background:#1f2a44;

    color:white;

    border-radius:25px;

    text-decoration:none;

    font-weight:700;

    font-family:'Poppins',sans-serif;

}



.back-btn:hover{

    background:#111827;

}





@media(max-width:800px){


    .container{

        width:90%;

    }


    .form-body{

        grid-template-columns:1fr;

    }


    .full{

        grid-column:auto;

    }


}



</style>


<body>


<section class="page-bg">


<div class="container">


<div class="card">



<div class="card-header">

<h1>

ADD NEW STAFF

</h1>

</div>

<form method="POST" enctype="multipart/form-data">


<div class="form-body">



<!-- PROFILE IMAGE -->

<div class="full image-box">


<img

src="../../images/uploads/default.png"

id="preview"

class="image-preview"

>


<br>


<input

type="file"

name="staff_image"

class="file-input"

accept=".jpg,.jpeg,.png,.webp"

onchange="previewImage(event)"

>


</div>







<!-- NAME -->

<div class="input-box">


<label>

Staff Name

</label>


<input

type="text"

name="staff_name"

placeholder="Enter staff name"

required

>


</div>







<!-- EMAIL -->

<div class="input-box">


<label>

Email

</label>


<input

type="email"

name="staff_email"

placeholder="Enter email address"

autocomplete="new-email"

required

>


</div>







<!-- PASSWORD -->

<div class="input-box">


<label>

Password

</label>


<input

type="password"

name="staff_password"

placeholder="Enter password"

autocomplete="new-password"

required

>


</div>







<!-- PHONE -->

<div class="input-box">


<label>

Phone Number

</label>


<input

type="text"

name="staff_phonenum"

placeholder="Enter phone number"

required

>


</div>







<!-- DOB -->

<div class="input-box">


<label>

Date Of Birth

</label>


<input

type="date"

name="staff_dob"

placeholder="Select date of birth"

required

>


</div>







<!-- GENDER -->

<div class="input-box">


<label>

Gender

</label>


<select name="staff_gender" required>

<option value="" disabled selected>

Select gender

</option>


<option value="Male">

Male

</option>


<option value="Female">

Female

</option>


</select>


</div>







<!-- STATE -->

<div class="input-box">


<label>

State

</label>


<select name="staff_state" required>


<?php foreach($states as $state){ ?>


<option value="<?php echo $state; ?>">

<?php echo $state; ?>

</option>


<?php } ?>


</select>


</div>







<!-- HIRE DATE -->

<div class="input-box">


<label>

Hire Date

</label>


<input

type="date"

name="staff_hiredate"

value="<?php echo date('Y-m-d'); ?>"

required

>


</div>







<!-- SALARY -->

<div class="input-box">


<label>

Salary (RM)

</label>


<input

type="number"

name="staff_salary"

step="0.01"

placeholder="Enter salary amount"

required

>

</div>







<!-- JOB -->

<div class="input-box">


<label>

Job Position

</label>


<select name="staff_job" required>


<option value="" disabled selected>

Select job position

</option>


<?php foreach($available_jobs as $job){ ?>


<option value="<?php echo $job; ?>">

<?php echo $job; ?>

</option>


<?php } ?>


</select>


</div>







<!-- SUBMIT -->

<div class="full">


<button

type="submit"

name="add_staff"

class="submit-btn"

>

Create Staff Account

</button>



<a

href="staff_management.php"

class="back-btn"

>

Back To Staff Management

</a>


</div>





</div>


</form>


</div>


</div>


</section>






<script>


function previewImage(event){


    let image = document.getElementById("preview");


    image.src = URL.createObjectURL(

        event.target.files[0]

    );


}


</script>







<?php include("../includes/footer.php"); ?>







<?php if(isset($_SESSION['success'])): ?>


<script>


Swal.fire({

    icon:"success",

    title:"Success",

    text:"<?php echo $_SESSION['success']; ?>",

    timer:2000,

    showConfirmButton:false


}).then(()=>{


    window.location.href="staff_management.php";


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