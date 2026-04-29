<?php
require_once('session_handler.php');
?>
<?php

require_once('connect.php');

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

if(isset($_GET['id']))
{ ?>
<div class="popup popup--icon -question js_question-popup popup--visible">
<div class="popup__background"></div>
<div class="popup__content">
<h3 class="popup__content__title">
Sure
</h1>
<p>Are You Sure To Delete This Record?</p>
<p>
<a href="del_order.php?id=<?php echo $_GET['id']; ?>" class="button button--success" data-for="js_success-popup">Yes</a>
<a href="today_delivery.php" class="button button--error" data-for="js_success-popup">No</a>
</p>
</div>
</div>
<?php } ?>



<!-- Page wrapper  -->
<div class="page-wrapper">
<!-- Bread crumb -->
<div class="row page-titles">
<div class="col-md-5 align-self-center">
<h3 class="text-primary">Today's Deliveries</h3> </div>
<div class="col-md-7 align-self-center">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
<li class="breadcrumb-item active">Today's Deliveries</li>
</ol>
</div>
</div>
<!-- End Bread crumb -->
<!-- Container fluid  -->
<div class="container-fluid">
<!-- Start Page Content -->

<!-- /# row -->
<div class="card">
<div class="card-body">
<?php if(isset($useroles)){  if(in_array("add_order",$useroles)){ ?> 
<a href="add_order.php"><button class="btn btn-primary">Add order</button></a>
<?php } } ?>
<div class="table-responsive m-t-40">
<table id="myTable" class="table table-bordered table-striped">
<thead>
<tr>
<th>ID</th>
<th>Customer Name</th>
<th>Service</th>
<th>Description</th>
<th>Price</th>
<th>Delivery Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php 
// Get today's deliveries with customer and service information in a single query
$sql = "SELECT o.*, c.fname, c.lname, s.sname 
       FROM `order` o 
       LEFT JOIN customer c ON o.customer_id = c.id 
       LEFT JOIN service s ON o.service_id = s.id 
       WHERE DATE(o.delivery_date) = CURRENT_DATE() 
       ORDER BY o.delivery_date ASC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $status_class = '';
        switch($row['status']) {
            case 'received':
                $status_class = 'bg-primary';
                break;
            case 'cleaning':
                $status_class = 'bg-warning';
                break;
            case 'processing':
                $status_class = 'bg-info';
                break;
            case 'in_transit':
                $status_class = 'bg-purple';
                break;
            case 'delivered':
                $status_class = 'bg-success';
                break;
            default:
                $status_class = 'bg-secondary';
        }
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
<td><?php echo htmlspecialchars($row['sname']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td>$<?php echo number_format($row['price'], 2); ?></td>
<td><?php echo date('M d, Y', strtotime($row['delivery_date'])); ?></td>
<td>
<span class="badge <?php echo $status_class; ?>">
<?php echo ucfirst($row['status']); ?>
</span>
</td>
<td>
<a href="edit_order.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
<a href="today_delivery.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
</td>
</tr>
<?php 
    }
} else {
?>
<tr>
<td colspan="8" class="text-center">No deliveries scheduled for today</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
</div>

<?php include('footer.php');?>

<!--  Author Name: Nikhil Bhalerao - www.nikhilbhalerao.com 
PHP, Laravel and Codeignitor Developer -->

<link rel="stylesheet" href="popup_style.css">
<?php if(!empty($_SESSION['success'])) {  ?>
<div class="popup popup--icon -success js_success-popup popup--visible">
<div class="popup__background"></div>
<div class="popup__content">
<h3 class="popup__content__title">
Success 
</h1>
<p><?php echo $_SESSION['success']; ?></p>
<p>
<button class="button button--success" data-for="js_success-popup">Close</button>
</p>
</div>
</div>
<?php unset($_SESSION["success"]);  
} ?>
<?php if(!empty($_SESSION['error'])) {  ?>
<div class="popup popup--icon -error js_error-popup popup--visible">
<div class="popup__background"></div>
<div class="popup__content">
<h3 class="popup__content__title">
Error 
</h1>
<p><?php echo $_SESSION['error']; ?></p>
<p>
<button class="button button--error" data-for="js_error-popup">Close</button>
</p>
</div>
</div>
<?php unset($_SESSION["error"]);  } ?>
<script>
var addButtonTrigger = function addButtonTrigger(el) {
el.addEventListener('click', function () {
var popupEl = document.querySelector('.' + el.dataset.for);
popupEl.classList.toggle('popup--visible');
});
};

Array.from(document.querySelectorAll('button[data-for]')).
forEach(addButtonTrigger);
</script>