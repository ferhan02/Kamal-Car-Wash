<?php
include("../config.php");

if(isset($_POST['register'])){

    // 1. Ambil data Account Details
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $gender = $_POST['gender'];
    $state = $_POST['state'];
    $password = $_POST['password'];
    $verify = $_POST['verify_password'];

    // 2. Ambil data Vehicle Details (Sebab kau ada input ni kat bawah)
    $plate = $_POST['plate'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $vehicle_type = $_POST['vehicle_type'];

    if($password != $verify){
        echo "<script>alert('Password does not match!');</script>";
    }else{

        // Semak jika email atau username dah wujud
        $check = $conn->prepare("SELECT * FROM customer WHERE cust_email=? OR cust_username=?");
        $check->execute([$email, $username]);

        if($check->rowCount()>0){
            echo "<script>alert('Email or username already registered');</script>";
        }else{

            $password = password_hash($password, PASSWORD_DEFAULT);

            // Sila pastikan nama column database kau (seperti cust_plate, cust_brand dll) sepadan dengan struktur DB kau
            $insert = $conn->prepare("INSERT INTO customer
            (cust_name, cust_dob, cust_phonenum, cust_email, cust_username, cust_password, cust_gender,cust_state)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            $insert->execute([
                $name,
                $dob,
                $phone,
                $email,
                $username,
                $password,
                $gender,
                $state
            ]);

            $cust_id = $conn->lastInsertId();

            $vehicle = $conn->prepare("INSERT INTO vehicle
            (cust_id, vehicle_platenum, vehicle_brand, vehicle_model, vehicle_type)
            VALUES (?, ?, ?, ?, ?)");

            $vehicle->execute([
            $cust_id,
            $_POST['plate'],
            $_POST['brand'],
            $_POST['model'],
            $_POST['vehicle_type']
        ]);

            echo "<script>
            alert('Register Successful');
            window.location='cust_login.php';
            </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register | Kamal Car Wash</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>
        /* =========================
            REGISTER PAGE STYLE
        ========================= */

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eaf2fb;
            margin: 0;
        }

        .register-wrapper {
            width: 1000px;
            height: 650px;
            display: flex;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* LEFT PANEL */
        .register-left {
            flex: 1;
            background: linear-gradient(135deg, #2f5aa8, #4f8cff);
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: white;
        }

        .register-left h1 {
            font-size: 26px;
            margin-top: 10px;
        }

        .register-left p {
            font-size: 13px;
            opacity: 0.9;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
        }

        /* RIGHT PANEL */
        .register-right {
            flex: 1.2;
            background: white;
            padding: 30px 40px;
            overflow-y: auto;
        }