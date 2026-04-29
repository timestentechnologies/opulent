<?php 
include('connect.php');
session_start();
if(!isset($_SESSION["email"])){
    ?>
    <script>
    window.location="login.php";
    </script>
    <?php
} else {

$id = isset($_GET['id']) ? $_GET['id'] : '';
$plan = array(
    'name' => '',
    'price' => '',
    'duration' => '',
    'description' => ''
);

if($id) {
    $sql = "SELECT * FROM subscription_plans WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $plan = $result->fetch_assoc();
    }
    $stmt->close();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $description = $_POST['description'];
    
    if($id) {
        $sql = "UPDATE subscription_plans SET name=?, price=?, duration=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdisi", $name, $price, $duration, $description, $id);
    } else {
        $sql = "INSERT INTO subscription_plans (name, price, duration, description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdis", $name, $price, $duration, $description);
    }
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Subscription plan " . ($id ? "updated" : "added") . " successfully!";
        header("Location: view_subscription_plans.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Subscription Plan - Laundry Admin</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="16x16" href="uploadImage/Logo/favicon.png">

    <!-- Bootstrap Core CSS -->
    <link href="css/lib/bootstrap/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/helper.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/lib/calendar2/semantic.ui.min.css" rel="stylesheet">
    <link href="css/lib/calendar2/pignose.calendar.min.css" rel="stylesheet">
    <link href="css/lib/sweetalert/sweetalert.css" rel="stylesheet">
    <link href="css/lib/owl.carousel.min.css" rel="stylesheet" />
    <link href="css/lib/owl.theme.default.min.css" rel="stylesheet" />
</head>

<body class="fix-header fix-sidebar">
    <!-- Preloader - style you can find in spinners.css -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
    </div>

    <!-- Main wrapper  -->
    <div id="main-wrapper">
        <?php include('header.php'); ?>
        <?php include('sidebar.php'); ?>
        <!-- Page wrapper  -->
        <div class="page-wrapper">
            <!-- Bread crumb -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-primary"><?php echo $id ? 'Edit' : 'Add'; ?> Subscription Plan</h3>
                </div>
                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="view_subscription_plans.php">Subscription Plans</a></li>
                        <li class="breadcrumb-item active"><?php echo $id ? 'Edit' : 'Add'; ?> Plan</li>
                    </ol>
                </div>
            </div>
            <!-- End Bread crumb -->
            <!-- Container fluid  -->
            <div class="container-fluid">
                <!-- Start Page Content -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title"><?php echo $id ? 'Edit' : 'Add'; ?> Subscription Plan</h4>
                                <?php
                                if(isset($_SESSION['error'])) {
                                    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                                    unset($_SESSION['error']);
                                }
                                ?>
                                <form method="post" action="" class="form-horizontal mt-4">
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Plan Name</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" name="name" 
                                                   value="<?php echo htmlspecialchars($plan['name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Price (KES)</label>
                                        <div class="col-sm-10">
                                            <input type="number" step="0.01" class="form-control" name="price" 
                                                   value="<?php echo htmlspecialchars($plan['price']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Duration (days)</label>
                                        <div class="col-sm-10">
                                            <input type="number" class="form-control" name="duration" 
                                                   value="<?php echo htmlspecialchars($plan['duration']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Description</label>
                                        <div class="col-sm-10">
                                            <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($plan['description']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-10 offset-sm-2">
                                            <button type="submit" class="btn btn-primary m-b-0">
                                                <?php echo $id ? 'Update Plan' : 'Add Plan'; ?>
                                            </button>
                                            <a href="view_subscription_plans.php" class="btn btn-secondary m-b-0">Cancel</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End PAge Content -->
            </div>
            <!-- End Container fluid  -->
            <?php include('footer.php'); ?>
        </div>
        <!-- End Page wrapper  -->
    </div>
    <!-- End Wrapper -->

    <!-- All Jquery -->
    <script src="js/lib/jquery/jquery.min.js"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="js/lib/bootstrap/js/popper.min.js"></script>
    <script src="js/lib/bootstrap/js/bootstrap.min.js"></script>
    <!-- slimscrollbar scrollbar JavaScript -->
    <script src="js/jquery.slimscroll.js"></script>
    <!--Menu sidebar -->
    <script src="js/sidebarmenu.js"></script>
    <!--stickey kit -->
    <script src="js/lib/sticky-kit-master/dist/sticky-kit.min.js"></script>
    <!--Custom JavaScript -->
    <script src="js/custom.min.js"></script>
</body>
</html>
<?php } ?> 