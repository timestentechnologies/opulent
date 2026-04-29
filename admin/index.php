<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Include session config first
require_once('session_handler.php');

// Include auto email checker to ensure notifications are processed
require_once('auto_check_emails.php');

// Verify login status
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include('head.php');
include('header.php');
include('connect.php');
include('sidebar.php');

// Rest of your existing code
date_default_timezone_set('Asia/Kolkata');
$current_date = date('Y-m-d');

$sql_currency = "select * from manage_website"; 
$result_currency = $conn->query($sql_currency);
$row_currency = mysqli_fetch_array($result_currency);
?>    
        <!-- Page wrapper  -->
        <div class="page-wrapper">
            
            <!-- Bread crumb -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-primary">Owner Dashboard</h3> 
                </div>
                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <marquee scrollamount=4><b>Alert : Make sure to check the orders and update the status as needed</b></marquee>
                    </ol>
                </div>
            </div>
            <!-- End Bread crumb -->
            <!-- Container fluid  -->
            <div class="container-fluid">
                <!-- Start Page Content -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-primary p-20">
                            <div class="media widget-ten">
                                <div class="media-left meida media-middle">
                                    <span><i class="ti-bag f-s-40"></i></span>
                                </div>
                                <div class="media-body media-text-right">
                                    <?php 
                                    $sql = "SELECT COUNT(*) as total FROM `order` WHERE DATE(created_at) = CURDATE()";
                                    $res = $conn->query($sql);
                                    $row = $res->fetch_assoc();
                                    $num_rows = $row['total'];
                                    ?>
                                    <h2 class="color-white"><?php echo $num_rows; ?></h2>
                                    <p class="m-b-0">New orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-pink p-20">
                            <div class="media widget-ten">
                                <div class="media-left meida media-middle">
                                    <span><i class="ti-comment f-s-40"></i></span>
                                </div>
                                <div class="media-body media-text-right">
                                    <?php 
                                    $sql = "SELECT COUNT(*) as total FROM `order` WHERE status IN ('received', 'cleaning', 'processing')";
                                    $res = $conn->query($sql);
                                    $row = $res->fetch_assoc();
                                    $num_rows = $row['total'];
                                    ?>
                                    <h2 class="color-white"><?php echo $num_rows; ?></h2>
                                    <p class="m-b-0">Inprogress</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-danger p-20">
                            <div class="media widget-ten">
                                <div class="media-left meida media-middle">
                                    <span><i class="ti-vector f-s-40"></i></span>
                                </div>
                                <div class="media-body media-text-right">
                                    <?php 
                                    $sql = "SELECT COUNT(*) as total FROM `order` WHERE status = 'delivered'";
                                    $res = $conn->query($sql);
                                    $row = $res->fetch_assoc();
                                    $num_rows = $row['total'];
                                    ?>
                                    <h2 class="color-white"><?php echo $num_rows; ?></h2>
                                    <p class="m-b-0">Completed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="col-md-5 align-self-center">
                            <h3 class="text-primary">Orders Status</h3>
                        </div>
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
                                        <th>Pickup Date</th>
                                        <th>Status</th>
                                        <th>Payment Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $sql = "SELECT o.*, c.fname as customer_name, c.lname as customer_lname, s.sname as service_name 
                                           FROM `order` o 
                                           JOIN `customer` c ON o.customer_id = c.id 
                                           JOIN `service` s ON o.service_id = s.id 
                                           ORDER BY o.created_at DESC";
                                    $result = $conn->query($sql);

                                    while($row = $result->fetch_assoc()) {
                                    ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['customer_name'] . ' ' . $row['customer_lname']; ?></td>
                                        <td><?php echo $row['service_name']; ?></td>
                                        <td><?php echo $row['description']; ?></td>
                                        <td><?php echo $row['price']; ?></td>
                                        <td><?php echo $row['delivery_date']; ?></td>
                                        <td><?php echo $row['pickup_date']; ?></td>
                                        <td><?php echo ucfirst($row['status']); ?></td>
                                        <td><?php echo ucfirst($row['payment_status']); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Container fluid  -->
            <?php include('footer.php');?>

