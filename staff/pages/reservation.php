<?php

session_start();

include("../../config.php");


// =========================
// CHECK LOGIN
// =========================

if (!isset($_SESSION['staff_id'])) {
    header("Location: ../../auth/staff_login.php");
    exit();
}


// =========================
// UPDATE STATUS
// =========================

if (isset($_POST['update_status'])) {

    $service_id = $_POST['service_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE service
        SET service_status = ?
        WHERE service_id = ?
    ");

    $stmt->execute([
        $status,
        $service_id
    ]);


    $_SESSION['success'] = "Reservation status updated successfully.";


    header("Location: reservation.php");
    exit();

}


// =========================
// FILTER
// =========================

$date = $_GET['date'] ?? "";
$search = $_GET['search'] ?? "";


// =========================
// LIVE SEARCH AJAX
// =========================

if(isset($_GET['ajax'])){


    $stmt = $conn->prepare("

    SELECT

    service.service_id,

    customer.cust_name,
    customer.cust_phonenum,

    vehicle.vehicle_platenum,
    vehicle.vehicle_brand,
    vehicle.vehicle_model,
    vehicle.vehicle_type,

    package.package_name,
    package.package_price,

    service.service_date,
    service.service_time,
    service.service_status


    FROM service


    JOIN customer
    ON service.cust_id = customer.cust_id


    JOIN vehicle
    ON service.vehicle_id = vehicle.vehicle_id


    JOIN package
    ON service.package_id = package.package_id


    WHERE

    (
        customer.cust_name LIKE ?
        OR customer.cust_phonenum LIKE ?
        OR vehicle.vehicle_platenum LIKE ?
        OR package.package_name LIKE ?
    )


    ORDER BY service.service_date ASC,
    service.service_time ASC

    ");



    $stmt->execute([

        "%$search%",
        "%$search%",
        "%$search%",
        "%$search%"

    ]);



    $result = $stmt->fetchAll();



    foreach($result as $row){


        echo "

        <tr>

        <td>
        -
        </td>


        <td>
        <b>{$row['cust_name']}</b>
        </td>


        <td>
        {$row['cust_phonenum']}
        </td>


        <td>
        {$row['vehicle_platenum']}
        <br>
        <small>
        {$row['vehicle_brand']} {$row['vehicle_model']}
        </small>
        <br>
        <small>
        {$row['vehicle_type']}
        </small>
        </td>


        <td>
        {$row['package_name']}
        <br>
        <small>
        RM ".number_format($row['package_price'],2)."
        </small>
        </td>


        <td>
        ".date("d/m/Y",strtotime($row['service_date']))."
        </td>


        <td>
        ".date("h:i A",strtotime($row['service_time']))."
        </td>


        <td>
        <span class='status {$row['service_status']}'>
        {$row['service_status']}
        </span>
        </td>


        <td>
        -
        </td>


        </tr>

        ";

    }


    exit();

}


// =========================
// PAGINATION
// =========================

$limit = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;


// =========================
// COUNT RECORDS
// =========================

$count = $conn->prepare("
    SELECT COUNT(*)
    FROM service

    JOIN customer
    ON service.cust_id = customer.cust_id

    JOIN vehicle
    ON service.vehicle_id = vehicle.vehicle_id

    JOIN package
    ON service.package_id = package.package_id

    WHERE
    (
        service.service_date = ?
        OR ? = ''
    )

    AND
    (
        customer.cust_name LIKE ?
        OR customer.cust_phonenum LIKE ?
        OR vehicle.vehicle_platenum LIKE ?
        OR package.package_name LIKE ?
    )
");

$count->execute([
    $date,
    $date,
    "%$search%",
    "%$search%",
    "%$search%",
    "%$search%"
]);

$total_records = $count->fetchColumn();
$total_pages = ceil($total_records / $limit);


// =========================
// GET RESERVATIONS
// =========================

$stmt = $conn->prepare("
    SELECT

    service.service_id,

    customer.cust_name,
    customer.cust_phonenum,

    vehicle.vehicle_platenum,
    vehicle.vehicle_brand,
    vehicle.vehicle_model,
    vehicle.vehicle_type,

    package.package_name,
    package.package_price,

    service.service_date,
    service.service_time,
    service.service_status

    FROM service

    JOIN customer
    ON service.cust_id = customer.cust_id

    JOIN vehicle
    ON service.vehicle_id = vehicle.vehicle_id

    JOIN package
    ON service.package_id = package.package_id

    WHERE
    (
        service.service_date = ?
        OR ? = ''
    )

    AND
    (
        customer.cust_name LIKE ?
        OR customer.cust_phonenum LIKE ?
        OR vehicle.vehicle_platenum LIKE ?
        OR package.package_name LIKE ?
    )

    ORDER BY service.service_date ASC,
    service.service_time ASC

    LIMIT $limit OFFSET $offset
");


$stmt->execute([
    $date,
    $date,
    "%$search%",
    "%$search%",
    "%$search%",
    "%$search%"
]);

$reservations = $stmt->fetchAll();

?>


<?php include("../includes/header.php"); ?>

<link rel="stylesheet" href="../../CSS/style.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<style>

.page-bg {
    background: url("../../images/hero.jpg") center/cover no-repeat;
    min-height: 100vh;
    padding: 40px 0;
    display: flex;
    justify-content: center;
}

.card {

    width:1200px;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.2);
    background: white;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}


/* HEADER */

.card-header {

    background:#b7cde6;

    color:#4f8cff;

    padding:20px;

    font-size:25px;

    font-weight:bold;

    display:flex;

    justify-content:center;

    align-items:center;

    position:relative;

}

.date-display {

    position:absolute;

    right:20px;

    background:white;

    color:#333;

    padding:8px 15px;

    border-radius:10px;

    font-size:14px;

}


/* FILTER */

.filter-box {
    background: #eaf2fb;
    padding: 20px;
}

.filter-box form {

    display:flex;

    gap:15px;

    align-items:center;

}

.filter-box input {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

.btn-search {

    background:#2f5aa8;

    color:white;

    border:none;

    padding:10px 25px;

    border-radius:20px;

    cursor:pointer;

}



.all-btn {


    background:white;

    color:#2f5aa8;

    padding:10px 20px;

    border-radius:20px;

    text-decoration:none;

    font-weight:bold;

    border:2px solid #2f5aa8;


}



.all-btn:hover {


    background:#2f5aa8;

    color:white;


}


/* TABLE */

.table-wrap {
    padding: 20px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th {
    background: #b7d3ff;
    padding: 12px;
    border: 1px solid #999;
}

td {
    padding: 12px;
    border: 1px solid #999;
    text-align: center;
}


/* STATUS */

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: bold;
    font-size: 12px;
}

.Pending {
    background: #fff3cd;
    color: #856404;
}

.Confirmed {
    background: #d4edda;
    color: #155724;
}

.Cancelled {
    background: #f8d7da;
    color: #721c24;
}


/* ACTION */

.action form {
    display: inline;
}

.action button {
    border: none;
    padding: 6px 10px;
    border-radius: 15px;
    color: white;
    cursor: pointer;
    font-size: 11px;
}

.confirm {
    background: #2ecc71;
}

.cancel {
    background: #e74c3c;
}


.pagination {
    text-align: center;
    padding: 20px;
}

.pagination a {
    text-decoration: none;
    background: #dbe9ff;
    color: #1f2a44;
    padding: 8px 12px;
    margin: 3px;
    border-radius: 8px;
}

</style>


<section class="page-bg">

<div class="card">


<!-- HEADER -->

<div class="card-header">

<div>
    RESERVATION MANAGEMENT
</div>


<div class="date-display">

<?php

if ($date == "") {
    echo "📅 All Reservations";
} else {
    echo "📅 " . date("d/m/Y", strtotime($date));
}

?>

</div>

</div>





<!-- FILTER -->

<div class="filter-box">

<form method="GET">

<input
type="date"
name="date"
value="<?php echo $date; ?>"
>


<input

type="text"

id="live-search"

name="search"

placeholder="Search customer, phone, vehicle..."

value="<?php echo htmlspecialchars($search); ?>"

>


<button class="btn-search">

Search

</button>


<a href="reservation.php" class="all-btn">

📅 All Reservations

</a>


</form>

</div>






<!-- TABLE -->

<div class="table-wrap">

<table>


<thead>

<tr>

<th>No.</th>
<th>Customer</th>
<th>Phone</th>
<th>Vehicle</th>
<th>Package</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody id="reservation-table">


<?php if (count($reservations) > 0) { ?>


<?php

$number = $offset + 1;

foreach ($reservations as $row) {

?>


<tr>


<td>
<?php echo $number++; ?>
</td>



<td>
<b>
<?php echo $row['cust_name']; ?>
</b>
</td>




<td>
<?php echo $row['cust_phonenum']; ?>
</td>





<td>

<?php echo $row['vehicle_platenum']; ?>

<br>

<small>
<?php echo $row['vehicle_brand'] . " " . $row['vehicle_model']; ?>
</small>

<br>

<small>
<?php echo $row['vehicle_type']; ?>
</small>

</td>





<td>

<?php echo $row['package_name']; ?>

<br>

<small>
RM <?php echo number_format($row['package_price'], 2); ?>
</small>

</td>





<td>

<?php echo date("d/m/Y", strtotime($row['service_date'])); ?>

</td>





<td>

<?php echo date("h:i A", strtotime($row['service_time'])); ?>

</td>





<td>

<span class="status <?php echo $row['service_status']; ?>">

<?php echo $row['service_status']; ?>

</span>

</td>







<td class="action">


<?php if ($row['service_status'] == "Pending") { ?>


<form method="POST">

<input
type="hidden"
name="service_id"
value="<?php echo $row['service_id']; ?>"
>


<input
type="hidden"
name="status"
value="Confirmed"
>


<button
class="confirm"
name="update_status">
Confirm
</button>

</form>





<form method="POST">

<input
type="hidden"
name="service_id"
value="<?php echo $row['service_id']; ?>"
>


<input
type="hidden"
name="status"
value="Cancelled"
>


<button
class="cancel"
name="update_status">
Cancel
</button>


</form>


<?php } ?>







<?php if ($row['service_status'] == "Confirmed") { ?>


<form method="POST">

<input
type="hidden"
name="service_id"
value="<?php echo $row['service_id']; ?>"
>


<input
type="hidden"
name="status"
value="Cancelled"
>


<button
class="cancel"
name="update_status">
Cancel
</button>


</form>


<?php } ?>






<?php if ($row['service_status'] == "Cancelled") { ?>

-

<?php } ?>



</td>


</tr>



<?php } ?>



<?php } else { ?>


<tr>

<td colspan="9">

No reservations found.

</td>

</tr>


<?php } ?>



</tbody>

</table>


</div>



<!-- PAGINATION -->

<div class="pagination">


<?php if ($total_pages > 1) { ?>


<?php for ($i = 1; $i <= $total_pages; $i++) { ?>


<a href="?page=<?php echo $i; ?>&date=<?php echo $date; ?>&search=<?php echo $search; ?>">

<?php echo $i; ?>

</a>


<?php } ?>


<?php } ?>


</div>



</div>

</section>

<?php if(isset($_SESSION['success'])): ?>

<script>

Swal.fire({

    icon:"success",

    title:"Updated",

    text:"<?php echo $_SESSION['success']; ?>",

    timer:2000,

    showConfirmButton:false

});

</script>

<?php unset($_SESSION['success']); endif; ?>

<script>


document

.getElementById("live-search")

.addEventListener("keyup",function(){



let keyword = this.value;



fetch(

"reservation.php?ajax=1&search="+keyword

)



.then(response=>response.text())


.then(data=>{


document

.getElementById("reservation-table")

.innerHTML=data;


});



});


</script>

<?php include("../includes/footer.php"); ?>