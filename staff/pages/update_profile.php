<?php

session_start();

include("../../config.php");


// CHECK LOGIN

if(!isset($_SESSION['staff_id'])){

    header("Location: ../../auth/staff_login.php");
    exit();

}


$staff_id = $_SESSION['staff_id'];



// GET STAFF DATA

$stmt = $conn->prepare("
    SELECT *
    FROM staff
    WHERE staff_id = ?
");

$stmt->execute([$staff_id]);

$staff = $stmt->fetch(PDO::FETCH_ASSOC);



if(!$staff){

    die("Staff not found.");

}



// UPDATE PROFILE

if(isset($_POST['update_profile'])){


    $name = $_POST['staff_name'];
    $email = $_POST['staff_email'];
    $phone = $_POST['staff_phonenum'];
    $dob = $_POST['staff_dob'];
    $state = $_POST['staff_state'];
    $gender = $_POST['staff_gender'];


    $image = $staff['staff_image'];



    // IMAGE UPLOAD

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
            "Invalid image format.";


            header("Location:update_profile.php");
            exit();

        }



        if($_FILES['staff_image']['size'] > 2000000){


            $_SESSION['error'] =
            "Image must be below 2MB.";


            header("Location:update_profile.php");
            exit();

        }



        $folder = "../../images/uploads/staff/";



        if(!is_dir($folder)){

            mkdir($folder,0777,true);

        }



        $filename =
        "staff_".$staff_id."_".time().".".$extension;



        move_uploaded_file(

            $_FILES['staff_image']['tmp_name'],

            $folder.$filename

        );



        $image =
        "uploads/staff/".$filename;


    }




    $stmt = $conn->prepare("

        UPDATE staff

        SET

        staff_name=?,

        staff_email=?,

        staff_phonenum=?,

        staff_dob=?,

        staff_state=?,

        staff_gender=?,

        staff_image=?

        WHERE staff_id=?

    ");



    $stmt->execute([

        $name,

        $email,

        $phone,

        $dob,

        $state,

        $gender,

        $image,

        $staff_id

    ]);



    $_SESSION['success'] =
    "Profile updated successfully.";



    header("Location:update_profile.php");
    exit();

}



?>



<?php include("../includes/header.php"); ?>


<link rel="stylesheet" href="../../css/style.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<style>


.page-bg{

    background:url("../../images/hero.jpg") center/cover no-repeat;

    min-height:100vh;

    padding:40px 0;

    display:flex;

    justify-content:center;

}



.profile-card{

    width:950px;

    background:white;

    border-radius:18px;

    overflow:hidden;

    box-shadow:0 12px 30px rgba(0,0,0,0.2);

}



.profile-header{

    background:#b7cde6;

    text-align:center;

    padding:20px;

}



.profile-header h1{

    color:#2f5aa8;

}



.profile-body{

    display:flex;

    gap:30px;

    padding:30px;

}



.profile-left{

    width:35%;

    text-align:center;

}



.profile-left img{

    width:170px;

    height:170px;

    border-radius:50%;

    object-fit:cover;

    border:5px solid #b7cde6;

}



.profile-left h2{

    margin-top:15px;

}



.info-box{

    background:#eaf2fb;

    padding:10px;

    border-radius:8px;

    margin-top:10px;

}



.profile-right{

    width:65%;

}



.input-box{

    margin-bottom:15px;

}



.input-box label{

    font-weight:bold;

    display:block;

    margin-bottom:5px;

}



.input-box input,

.input-box select{

    width:100%;

    padding:10px;

    border:1px solid #ccc;

    border-radius:8px;

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

    text-align:center;

    display:block;

}



.btn:hover{

    background:#1f3f7a;

}



.password-btn{

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



.password-btn:hover{

    background:#000;

}



@media(max-width:800px){

    .profile-body{

        flex-direction:column;

    }


    .profile-left,

    .profile-right{

        width:100%;

    }

}


</style>





<section class="page-bg">


<div class="profile-card">



<div class="profile-header">

<h1>
UPDATE PROFILE
</h1>

</div>




<div class="profile-body">



<div class="profile-left">


<img

src="../../images/<?php echo !empty($staff['staff_image']) ? $staff['staff_image'] : 'uploads/default.png'; ?>"

>


<h2>

<?php echo $staff['staff_name']; ?>

</h2>



<p>

<?php echo $staff['staff_job']; ?>

</p>



<div class="info-box">

<b>Staff ID</b>

<br>

<?php echo $staff['staff_id']; ?>

</div>



<div class="info-box">

<b>Hire Date</b>

<br>

<?php echo $staff['staff_hiredate']; ?>

</div>



</div>






<div class="profile-right">



<form method="POST" enctype="multipart/form-data">



<div class="input-box">

<label>
Profile Picture
</label>

<input

type="file"

name="staff_image"

accept=".jpg,.jpeg,.png,.webp"

>

</div>




<div class="input-box">

<label>
Staff Name
</label>

<input

type="text"

name="staff_name"

value="<?php echo $staff['staff_name']; ?>"

required

>

</div>




<div class="input-box">

<label>
Email
</label>

<input

type="email"

name="staff_email"

value="<?php echo $staff['staff_email']; ?>"

required

>

</div>




<div class="input-box">

<label>
Phone Number
</label>

<input

type="text"

name="staff_phonenum"

value="<?php echo $staff['staff_phonenum']; ?>"

>

</div>




<div class="input-box">

<label>
Date Of Birth
</label>

<input

type="date"

name="staff_dob"

value="<?php echo $staff['staff_dob']; ?>"

>

</div>




<div class="input-box">

<label>
State
</label>

<select name="staff_state">


<?php

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


foreach($states as $s){

?>

<option

value="<?php echo $s; ?>"

<?php

if($staff['staff_state']==$s){

echo "selected";

}

?>

>

<?php echo $s; ?>

</option>


<?php } ?>


</select>

</div>




<div class="input-box">

<label>
Gender
</label>


<select name="staff_gender">


<option value="Male"
<?php if($staff['staff_gender']=="Male") echo "selected"; ?>>
Male
</option>


<option value="Female"
<?php if($staff['staff_gender']=="Female") echo "selected"; ?>>
Female
</option>


</select>


</div>





<button

class="btn"

name="update_profile"

>

Save Changes

</button>



<a

href="change_password.php"

class="password-btn"

>

Change Password

</a>



</form>


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