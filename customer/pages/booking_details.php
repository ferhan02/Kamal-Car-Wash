<?php
session_start();
include("../../config.php");

if(!isset($_SESSION['cust_id'])){
    echo "<script>alert('Please login first'); window.location='../../auth/cust_login.php';</script>";
    exit();
}

$cust_id = $_SESSION['cust_id'];

if(isset($_POST['confirm_booking'])){

    $vehicle_id = $_POST['vehicle_id'];
    $package_id = $_POST['package_id'];
    $service_date = $_POST['service_date'];
    $service_time = $_POST['service_time'];

    $check = $conn->prepare("SELECT * FROM service 
        WHERE service_date = ? 
        AND service_time = ? 
        AND service_status != 'Cancelled'");
    $check->execute([$service_date, $service_time]);

    if($check->rowCount() > 0){
        echo "<script>
            alert('This time slot is already booked. Please choose another time.');
            window.location='booking.php?date=$service_date';
        </script>";
        exit();
    }

    $insert = $conn->prepare("INSERT INTO service
        (cust_id, package_id, vehicle_id, service_date, service_time, service_status)
        VALUES (?, ?, ?, ?, ?, 'Pending')");

    $insert->execute([$cust_id, $package_id, $vehicle_id, $service_date, $service_time]);

    $_SESSION['service_id'] = $conn->lastInsertId();

    echo "<script>
        alert('Booking Confirmed. You will be redirected to payment page.');
        window.location='payment.php';
    </script>";
    exit();
}

if(!isset($_POST['vehicle_id'], $_POST['package_id'], $_POST['service_date'], $_POST['service_time'])){
    echo "<script>alert('Invalid booking details'); window.location='booking.php';</script>";
    exit();
}

$vehicle_id = $_POST['vehicle_id'];
$package_id = $_POST['package_id'];
$service_date = $_POST['service_date'];
$service_time = $_POST['service_time'];

$vehicle = $conn->prepare("SELECT * FROM vehicle WHERE vehicle_id = ? AND cust_id = ?");
$vehicle->execute([$vehicle_id, $cust_id]);
$v = $vehicle->fetch(PDO::FETCH_ASSOC);

$package = $conn->prepare("SELECT * FROM package WHERE package_id = ?");
$package->execute([$package_id]);
$p = $package->fetch(PDO::FETCH_ASSOC);

if(!$v || !$p){
    echo "<script>alert('Vehicle or package not found'); window.location='booking.php';</script>";
    exit();
}

function vehicleTypeName($type){
    if($type == "A") return "Motorcycle";
    if($type == "B") return "Normal Car";
    if($type == "C") return "4x4 / Van";
    return "Unknown";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Booking Details</title>
<link rel="stylesheet" href="../../css/style.css">

<style>
body{
    background:#eaf2fb;
    font-family:Arial, sans-serif;
    margin:0;
}

.container{
    width:520px;
    background:white;
    margin:80px auto;
    padding:35px;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

h2{
    text-align:center;
    color:#1f2a44;
    margin-bottom:25px;
}

.detail{
    margin:15px 0;
    padding:13px;
    border-bottom:1px solid #ddd;
}

.detail strong{
    color:#2f5aa8;
}

.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:25px;
    font-weight:bold;
    cursor:pointer;
    margin-top:10px;
}

.confirm{
    background:#2f5aa8;
    color:white;
}

.change{
    background:#777;
    color:white;
    display:block;
    text-align:center;
    text-decoration:none;
    box-sizing:border-box;
}
</style>
</head>

<body>

<div class="container">

    <h2>Booking Details</h2>

    <div class="detail">
        <strong>Vehicle:</strong><br>
        <?php echo $v['vehicle_platenum']; ?> -
        <?php echo $v['vehicle_brand']; ?>
        <?php echo $v['vehicle_model']; ?>
        <br>
        Type: <?php echo vehicleTypeName($v['vehicle_type']); ?>
    </div>

    <div class="detail">
        <strong>Date:</strong><br>
        <?php echo $service_date; ?>
    </div>

    <div class="detail">
        <strong>Time:</strong><br>
        <?php echo $service_time; ?>
    </div>

    <div class="detail">
        <strong>Package:</strong><br>
        <?php echo $p['package_name']; ?>
    </div>

    <div class="detail">
        <strong>Price:</strong><br>
        RM<?php echo $p['package_price']; ?>
    </div>

    <form method="POST">
        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
        <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
        <input type="hidden" name="service_date" value="<?php echo $service_date; ?>">
        <input type="hidden" name="service_time" value="<?php echo $service_time; ?>">

        <button type="submit" name="confirm_booking" class="btn confirm">Confirm Booking</button>
    </form>

    <a href="booking.php?date=<?php echo $service_date; ?>" class="btn change">Change Details</a>

</div>

</body>
</html>