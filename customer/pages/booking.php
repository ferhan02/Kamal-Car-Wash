<?php
session_start();
include("../../config.php");

if(!isset($_SESSION['cust_id'])){
    echo "<script>alert('Please login first'); window.location='../../auth/cust_login.php';</script>";
    exit();
}

$cust_id = $_SESSION['cust_id'];

$slots = ["08:00", "09:00", "10:00", "11:00", "12:00", "14:00", "15:00", "16:00", "17:00"];

$selected_date = $_GET['date'] ?? "";
$booked_slots = [];

if($selected_date != ""){
    $stmt = $conn->prepare("SELECT TIME_FORMAT(service_time, '%H:%i') AS booked_time 
                            FROM service 
                            WHERE service_date = ? 
                            AND service_status != 'Cancelled'");
    $stmt->execute([$selected_date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$vehicle_stmt = $conn->prepare("SELECT * FROM vehicle WHERE cust_id = ?");
$vehicle_stmt->execute([$cust_id]);
$vehicles = $vehicle_stmt->fetchAll(PDO::FETCH_ASSOC);

$package_basic = $conn->query("SELECT * FROM package WHERE package_type = 1")->fetchAll(PDO::FETCH_ASSOC);
$package_deluxe = $conn->query("SELECT * FROM package WHERE package_type = 2")->fetchAll(PDO::FETCH_ASSOC);
$package_premium = $conn->query("SELECT * FROM package WHERE package_type = 3")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Booking | Kamal Car Wash</title>
<link rel="stylesheet" href="../../css/style.css">

<style>
body{margin:0;background:#eaf2fb;font-family:Arial,sans-serif;}
.booking-hero{
    height:45vh;
    background:url("../../images/background1.jpg") center/cover no-repeat;
    display:flex;justify-content:center;align-items:center;
    text-align:center;color:white;position:relative;
}
.booking-hero::after{content:"";position:absolute;inset:0;background:rgba(0,0,0,0.55);}
.hero-text{position:relative;z-index:2;}
.hero-text h1{font-size:40px;margin-bottom:10px;}
.booking-section{display:flex;justify-content:center;padding:60px 20px;}
.booking-card{
    width:900px;background:white;border-radius:15px;
    display:flex;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.2);
}
.booking-left{
    flex:1;background:#2f5aa8;display:flex;
    justify-content:center;align-items:center;
}
.booking-left img{width:230px;}
.booking-right{flex:1;padding:40px;}
.booking-title{font-size:24px;font-weight:bold;color:#2f5aa8;margin-bottom:20px;}
.input-box{margin-bottom:15px;}
.input-box input,.input-box select{
    width:100%;padding:11px;border:none;
    border-bottom:2px solid #ccc;outline:none;background:white;
}
.input-box input:focus,.input-box select:focus{border-bottom:2px solid #4f8cff;}
.book-btn{
    width:100%;padding:12px;margin-top:10px;border:none;
    background:#2f5aa8;color:white;font-weight:bold;
    border-radius:25px;cursor:pointer;
}
.book-btn:hover{background:#1f3f7a;}
.footer{
    display:flex;justify-content:space-around;background:#1f2a44;
    color:white;padding:40px;margin-top:40px;
}
</style>

<script>
function showPackages(){
    let type = document.getElementById("package_type").value;
    let packageSelect = document.getElementById("package_id");
    let options = document.querySelectorAll(".package-option");

    packageSelect.value = "";

    options.forEach(function(option){
        option.style.display = "none";
        if(option.classList.contains(type)){
            option.style.display = "block";
        }
    });
}

window.onload = showPackages;
</script>
</head>

<body>

<header class="header">
    <div class="logo-section">
        <img src="../../images/logo.png" class="logo">
        <div>
            <h1>KAMAL CAR WASH</h1>
            <span>Online Booking System</span>
        </div>
    </div>

    <nav class="nav">
        <a href="cust_home.php">HOME</a>
        <a href="booking.php">BOOKING</a>
        <a href="vehicle.php">VEHICLE</a>
        <a href="../../auth/cust_login.php">LOGOUT</a>
    </nav>
</header>

<section class="booking-hero">
    <div class="hero-text">
        <h1>Online Reservation</h1>
        <p>Skip the queue and book your car wash instantly</p>
    </div>
</section>

<section class="booking-section">
    <div class="booking-card">

        <div class="booking-left">
            <img src="../../images/reserve.png" alt="Reserve">
        </div>

        <div class="booking-right">
            <div class="booking-title">Check Available Slot</div>

            <form method="GET">
                <div class="input-box">
                    <input type="date" name="date" value="<?php echo $selected_date; ?>" required>
                </div>

                <button class="book-btn" type="submit">Check Available Time</button>
            </form>

            <?php if($selected_date != "") { ?>

            <form method="POST" action="booking_details.php">

                <input type="hidden" name="service_date" value="<?php echo $selected_date; ?>">

                <div class="input-box">
                    <select name="vehicle_id" required>
                        <option value="">Select Vehicle</option>
                        <?php foreach($vehicles as $v){ ?>
                            <option value="<?php echo $v['vehicle_id']; ?>">
                                <?php echo $v['vehicle_platenum']; ?> -
                                <?php echo $v['vehicle_brand']; ?>
                                <?php echo $v['vehicle_model']; ?>
                                (<?php echo $v['vehicle_type']; ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="input-box">
                    <select name="service_time" required>
                        <option value="">Select Available Time</option>
                        <?php foreach($slots as $slot){ ?>
                            <?php if(!in_array($slot, $booked_slots)){ ?>
                                <option value="<?php echo $slot; ?>"><?php echo $slot; ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div class="input-box">
                    <select name="package_type" id="package_type" required onchange="showPackages()">
                        <option value="">Select Package Type</option>
                        <option value="basic">Basic</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>

                <div class="input-box">
                    <select name="package_id" id="package_id" required>
                        <option value="">Select Package</option>

                        <?php foreach($package_basic as $p){ ?>
                            <option class="package-option basic" value="<?php echo $p['package_id']; ?>">
                                <?php echo $p['package_name']; ?> - RM<?php echo $p['package_price']; ?>
                            </option>
                        <?php } ?>

                        <?php foreach($package_deluxe as $p){ ?>
                            <option class="package-option deluxe" value="<?php echo $p['package_id']; ?>">
                                <?php echo $p['package_name']; ?> - RM<?php echo $p['package_price']; ?>
                            </option>
                        <?php } ?>

                        <?php foreach($package_premium as $p){ ?>
                            <option class="package-option premium" value="<?php echo $p['package_id']; ?>">
                                <?php echo $p['package_name']; ?> - RM<?php echo $p['package_price']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <button class="book-btn" type="submit" name="continue">Continue</button>
            </form>

            <?php } ?>

        </div>
    </div>
</section>

<footer class="footer">
    <div>
        <h3>Opening Times</h3>
        <p>Mon-Fri: 8AM - 6PM</p>
        <p>Saturday: 8AM - 6PM</p>
        <p>Sunday: Closed</p>
    </div>

    <div>
        <h3>Kamal Car Wash</h3>
        <p>Drive clean, drive proud ✨</p>
    </div>

    <div>
        <h3>Contact Info</h3>
        <p>Seremban, Negeri Sembilan</p>
        <p>Email: kamalcarwash@gmail.com</p>
    </div>
</footer>

</body>
</html>