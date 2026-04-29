<?php
require_once('session_handler.php');

// Include auto email checker to ensure notifications are processed
require_once('auto_check_emails.php');
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
?>

<!-- Page wrapper  -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Add New Order</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Add Order</li>
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
                            <form method="POST" action="pages/save_order.php">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Customer</label>
                                    <div class="col-sm-10">
                                        <select name="customer_id" class="form-control" required>
                                            <option value="">--Select Customer--</option>
                                            <?php  
                                            $sql = "SELECT * FROM customer WHERE id != 1";
                                            $result = $conn->query($sql); 
                                            while($row = mysqli_fetch_array($result)){
                                            ?>
                                            <option value="<?php echo $row['id']; ?>">
                                                <?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
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
                                            <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['prize']; ?>">
                                                <?php echo htmlspecialchars($row['sname']); ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" name="description" rows="4" required></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Weight (kg)</label>
                                    <div class="col-sm-10">
                                        <input type="number" step="0.1" class="form-control" name="weight" id="weight" required onchange="updatePrice()">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Pickup Date</label>
                                    <div class="col-sm-10">
                                        <input type="date" class="form-control" name="pickup_date" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Delivery Date</label>
                                    <div class="col-sm-10">
                                        <input type="date" class="form-control" name="delivery_date" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Price</label>
                                    <div class="col-sm-10">
                                        <input type="number" step="0.01" class="form-control" name="price" id="price" readonly required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary">Submit</button>
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

<?php include('footer.php');?>

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
</script>