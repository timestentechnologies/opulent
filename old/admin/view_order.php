<?php 
session_start();
require_once('connect.php');

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    ?>
    <script>
    window.location="login.php";
    </script>
    <?php
    exit();
}

// Get user roles
$sql = "SELECT * FROM admin WHERE id = '".$_SESSION["id"]."'";
$result = $conn->query($sql);
$row1 = mysqli_fetch_array($result);

$q = "SELECT * FROM tbl_permission_role WHERE role_id='".$row1['group_id']."'";
$ress = $conn->query($q);
$name = array();
while($row = mysqli_fetch_array($ress)) {
    $sql = "SELECT * FROM tbl_permission WHERE id = '".$row['permission_id']."'";
    $result = $conn->query($sql);
    $row1 = mysqli_fetch_array($result);
    array_push($name, $row1[1]);
}
$_SESSION['name'] = $name;
$useroles = $_SESSION['name'];

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
<a href="view_order.php" class="button button--error" data-for="js_success-popup">No</a>
</p>
</div>
</div>
<?php } ?>

<!-- Page wrapper  -->
<div class="page-wrapper">
<!-- Bread crumb -->
<div class="row page-titles">
<div class="col-md-5 align-self-center">
<h3 class="text-primary"> View order</h3> </div>
<div class="col-md-7 align-self-center">
<ol class="breadcrumb">
<li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
<li class="breadcrumb-item active">View order</li>
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
<?php if(isset($useroles) && in_array("add_order",$useroles)){ ?> 
<a href="add_order.php"><button class="btn btn-primary">Add order</button></a>
<?php } ?>
<div class="table-responsive m-t-40">
<table id="myTable" class="table table-bordered table-striped">
<thead>
<tr>
<th>id</th>
<th>Tracking Number</th>
<th>customer Name</th>
<th>service name</th>
<th>Description</th>
<th>Weight (kg)</th>
<th>Price</th>
<th>Delivery Date</th>
<th>Pickup Date</th>
<th>Status</th>
<th>Payment Status</th>
<th>Payment Method</th>
<th>M-Pesa Number</th>
<th>M-Pesa Code</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php 
// Get all orders with customer and service information in a single query
$sql = "SELECT o.*, c.fname, c.lname, s.sname 
        FROM `order` o 
        LEFT JOIN customer c ON o.customer_id = c.id 
        LEFT JOIN service s ON o.service_id = s.id 
        ORDER BY o.id DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Ensure weight is set, default to 0 if not
        $weight = isset($row['weight']) ? $row['weight'] : 0;
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['tracking_number']); ?></td>
<td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
<td><?php echo htmlspecialchars($row['sname']); ?></td>
<td><?php echo htmlspecialchars($row['description']); ?></td>
<td><?php echo number_format($weight, 2); ?> kg</td>
<td>$<?php echo number_format($row['price'], 2); ?></td>
<td><?php echo date('M d, Y', strtotime($row['delivery_date'])); ?></td>
<td><?php echo date('M d, Y', strtotime($row['pickup_date'])); ?></td>
<td>
    <?php 
    $status_class = '';
    switch($row['status']) {
        case 'received':
            $status_class = 'bg-blue-100 text-blue-800';
            break;
        case 'cleaning':
            $status_class = 'bg-yellow-100 text-yellow-800';
            break;
        case 'processing':
            $status_class = 'bg-purple-100 text-purple-800';
            break;
        case 'in_transit':
            $status_class = 'bg-green-100 text-green-800';
            break;
        case 'delivered':
            $status_class = 'bg-gray-100 text-gray-800';
            break;
    }
    ?>
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
        <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
    </span>
</td>
<td>
    <?php 
    $payment_class = '';
    switch($row['payment_status']) {
        case 'paid':
            $payment_class = 'bg-green-100 text-green-800';
            break;
        case 'pending':
            $payment_class = 'bg-yellow-100 text-yellow-800';
            break;
        default:
            $payment_class = 'bg-red-100 text-red-800';
    }
    ?>
    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $payment_class; ?>">
        <?php echo ucfirst($row['payment_status']); ?>
    </span>
</td>
<td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($row['mpesa_number'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($row['mpesa_code'] ?? 'N/A'); ?></td>
<td>
<?php if ($row['status'] != 'delivered') { ?>
<a href="complete_order.php?id=<?=$row['id'];?>"><button type="button" class="btn btn-xs btn-danger"><i class="fa fa-exchange"></i></button></a>
<?php } ?>

<?php if(isset($useroles) && in_array("edit_order",$useroles)) { ?> 
<a href="edit_order.php?id=<?=$row['id'];?>"><button type="button" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></button></a>
<?php } ?>

<?php if(isset($useroles) && in_array("delete_order",$useroles)) { ?> 
<a href="view_order.php?id=<?=$row['id'];?>"><button type="button" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button></a>
<?php } ?>
</td>
</tr>
<?php 
    }
} else {
    echo '<tr><td colspan="11" class="text-center">No orders found</td></tr>';
}
?>
</tbody>
</table>
</div>
</div>
</div>

<?php include('footer.php');?>

<!-- Success and Error Popups -->
<link rel="stylesheet" href="popup_style.css">
<?php if(!empty($_SESSION['success'])) {  ?>
<div class="popup popup--icon -success js_success-popup popup--visible">
<div class="popup__background"></div>
<div class="popup__content">
<h3 class="popup__content__title">Success</h1>
<p><?php echo $_SESSION['success']; ?></p>
<p>
<button class="button button--success" data-for="js_success-popup">Close</button>
</p>
</div>
</div>
<?php unset($_SESSION["success"]); } ?>

<?php if(!empty($_SESSION['error'])) {  ?>
<div class="popup popup--icon -error js_error-popup popup--visible">
<div class="popup__background"></div>
<div class="popup__content">
<h3 class="popup__content__title">Error</h1>
<p><?php echo $_SESSION['error']; ?></p>
<p>
<button class="button button--error" data-for="js_error-popup">Close</button>
</p>
</div>
</div>
<?php unset($_SESSION["error"]); } ?>

<script>
var addButtonTrigger = function addButtonTrigger(el) {
    el.addEventListener('click', function () {
        var popupEl = document.querySelector('.' + el.dataset.for);
        popupEl.classList.toggle('popup--visible');
    });
};

Array.from(document.querySelectorAll('button[data-for]')).forEach(addButtonTrigger);
</script>