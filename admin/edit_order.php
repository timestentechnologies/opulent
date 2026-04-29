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

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Get order details with customer and service information
    $sql = "SELECT o.*, c.fname, c.lname, c.email, c.contact, c.address, s.sname, s.prize as service_price 
            FROM `order` o 
            LEFT JOIN customer c ON o.customer_id = c.id 
            LEFT JOIN service s ON o.service_id = s.id 
            WHERE o.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if(!$order) {
        $_SESSION['error'] = "Order not found";
        header("Location: view_order.php");
        exit();
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $service_id = $_POST['service_id'];
    $description = $_POST['description'];
    $weight = floatval($_POST['weight']);
    $pickup_date = $_POST['pickup_date'];
    $delivery_date = $_POST['delivery_date'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    
    $sql = "UPDATE `order` SET 
            service_id = ?, 
            description = ?, 
            weight = ?,
            pickup_date = ?, 
            delivery_date = ?, 
            price = ?, 
            status = ?, 
            payment_status = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isdssdssi", 
        $service_id, 
        $description, 
        $weight,
        $pickup_date, 
        $delivery_date, 
        $price, 
        $status, 
        $payment_status,
        $id
    );
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Order updated successfully";
        header("Location: view_order.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating order: " . $conn->error;
    }
}

include('head.php');
include('header.php');
include('sidebar.php');
?>

<!-- Page wrapper  -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Edit Order</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Edit Order</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Order Details</h4>
                        <div class="basic-form">
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Customer Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['fname'] . ' ' . $order['lname']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($order['email']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Contact</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['contact']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Address</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" readonly><?php echo htmlspecialchars($order['address']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Tracking Number</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" readonly>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Service</label>
                                    <div class="col-sm-10">
                                        <select name="service_id" id="service_id" class="form-control" required onchange="updatePrice()">
                                            <option value="">--Select Service--</option>
                                            <?php  
                                            $sql = "SELECT * FROM service WHERE id != 1";
                                            $result = $conn->query($sql); 
                                            while($row = mysqli_fetch_array($result)){
                                            ?>
                                            <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['prize']; ?>" <?php echo $order['service_id'] == $row['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($row['sname']); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($order['description']); ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Weight (kg)</label>
                                    <div class="col-sm-10">
                                        <input type="number" step="0.1" class="form-control" name="weight" id="weight" value="<?php echo $order['weight']; ?>" required onchange="updatePrice()">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Pickup Date</label>
                                    <div class="col-sm-10">
                                        <input type="date" class="form-control" name="pickup_date" value="<?php echo $order['pickup_date']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Delivery Date</label>
                                    <div class="col-sm-10">
                                        <input type="date" class="form-control" name="delivery_date" value="<?php echo $order['delivery_date']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Price</label>
                                    <div class="col-sm-10">
                                        <input type="number" step="0.01" class="form-control" name="price" id="price" value="<?php echo $order['price']; ?>" readonly required>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Status</label>
                                    <div class="col-sm-10">
                                        <select name="status" class="form-control" required>
                                            <option value="received" <?php echo $order['status'] == 'received' ? 'selected' : ''; ?>>Received</option>
                                            <option value="cleaning" <?php echo $order['status'] == 'cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="in_transit" <?php echo $order['status'] == 'in_transit' ? 'selected' : ''; ?>>In Transit</option>
                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Payment Status</label>
                                    <div class="col-sm-10">
                                        <select name="payment_status" class="form-control" required>
                                            <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $order['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="refunded" <?php echo $order['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary">Update Order</button>
                                        <a href="view_order.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePrice() {
    var serviceSelect = document.getElementById('service_id');
    var weightInput = document.getElementById('weight');
    var priceInput = document.getElementById('price');
    var selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
    
    if (selectedOption.value && weightInput.value) {
        var basePrice = parseFloat(selectedOption.getAttribute('data-price'));
        var weight = parseFloat(weightInput.value);
        priceInput.value = (basePrice * weight).toFixed(2);
    } else {
        priceInput.value = '';
    }
}

// Initialize price on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
});
</script>

<?php include('footer.php');?>

<!--  Author Name: Nikhil Bhalerao - www.nikhilbhalerao.com 
PHP, Laravel and Codeignitor Developer -->