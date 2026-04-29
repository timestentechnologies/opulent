<?php
// Start output buffering and session handling first
ob_start();

// Enable error reporting for debugging in production
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide errors from users but log them
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

// Increase script execution time for slow connections
ini_set('max_execution_time', 300);

try {
    // Include session handler and other files
    require_once('session_handler.php');
    require_once('head.php');
    require_once('header.php');
    require_once('sidebar.php');
    require_once('connect.php');

    // Form processing
    if(isset($_POST["btn_web"]))
    {
        extract($_POST);
        $target_dir = "uploadImage/Logo/";
        
        // Process website logo
        $website_logo = basename($_FILES["website_image"]["name"]);
        if($_FILES["website_image"]["tmp_name"]!=''){
            $image = $target_dir . basename($_FILES["website_image"]["name"]);
            if (move_uploaded_file($_FILES["website_image"]["tmp_name"], $image)) {
                @unlink("uploadImage/Logo/".$_POST['old_website_image']);
            } else {
                error_log("Failed to upload website logo: " . $_FILES["website_image"]["error"]);
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            $website_logo = $_POST['old_website_image'];
        }

        // Process login logo
        $login_logo = basename($_FILES["login_image"]["name"]);
        if($_FILES["login_image"]["tmp_name"]!=''){
            $image = $target_dir . basename($_FILES["login_image"]["name"]);
            if (move_uploaded_file($_FILES["login_image"]["tmp_name"], $image)) {
                @unlink("uploadImage/Logo/".$_POST['old_login_image']);
            } else {
                error_log("Failed to upload login logo: " . $_FILES["login_image"]["error"]);
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            $login_logo = $_POST['old_login_image'];
        }

        // Process invoice logo
        $invoice_logo = basename($_FILES["invoice_image"]["name"]);
        if($_FILES["invoice_image"]["tmp_name"]!=''){
            $image = $target_dir . basename($_FILES["invoice_image"]["name"]);
            if (move_uploaded_file($_FILES["invoice_image"]["tmp_name"], $image)) {
                @unlink("uploadImage/Logo/".$_POST['old_invoice_image']);
            } else {
                error_log("Failed to upload invoice logo: " . $_FILES["invoice_image"]["error"]);
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            $invoice_logo = $_POST['old_invoice_image'];
        }

        // Process background login image
        $background_login_image = basename($_FILES["back_login_image"]["name"]);
        if($_FILES["back_login_image"]["tmp_name"]!=''){
            $image = $target_dir . basename($_FILES["back_login_image"]["name"]);
            if (move_uploaded_file($_FILES["back_login_image"]["tmp_name"], $image)) {
                @unlink("uploadImage/Logo/".$_POST['old_back_login_image']);
            } else {
                error_log("Failed to upload background image: " . $_FILES["back_login_image"]["error"]);
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            $background_login_image = $_POST['old_back_login_image'];
        }
        
        // Prepare and execute the update query with prepared statements
        $title = mysqli_real_escape_string($conn, $title);
        $short_title = mysqli_real_escape_string($conn, $short_title);
        $footer = mysqli_real_escape_string($conn, $footer);
        $currency_code = mysqli_real_escape_string($conn, $currency_code);
        $currency_symbol = mysqli_real_escape_string($conn, $currency_symbol);
        
        $q1 = "UPDATE `manage_website` SET 
            `title` = '$title',
            `short_title` = '$short_title',
            `logo` = '$website_logo',
            `footer` = '$footer',
            `currency_code` = '$currency_code',
            `currency_symbol` = '$currency_symbol',
            `login_logo` = '$login_logo',
            `invoice_logo` = '$invoice_logo',
            `background_login_image` = '$background_login_image'";
            
        if ($conn->query($q1) === TRUE) {
            $_SESSION['success'] = 'Record Successfully Updated';
            // Use header redirect instead of JavaScript
            header("Location: manage_website.php");
            exit;
        } else {
            error_log("Database update error: " . $conn->error);
            $_SESSION['error'] = 'Something Went Wrong: ' . $conn->error;
        }
    }

    // Fetch website settings
    $que = "SELECT * FROM manage_website LIMIT 1";
    $query = $conn->query($que);
    
    if (!$query) {
        throw new Exception("Database query error: " . $conn->error);
    }

    // Initialize variables with default values
    $title = '';
    $short_title = '';
    $footer = '';
    $currency_code = '';
    $currency_symbol = '';
    $website_logo = '';
    $login_logo = '';
    $invoice_logo = '';
    $background_login_image = '';

    // Populate variables if data exists
    if ($row = mysqli_fetch_array($query)) {
        $title = htmlspecialchars($row['title'] ?? '');
        $short_title = htmlspecialchars($row['short_title'] ?? '');
        $footer = htmlspecialchars($row['footer'] ?? '');
        $currency_code = htmlspecialchars($row['currency_code'] ?? '');
        $currency_symbol = htmlspecialchars($row['currency_symbol'] ?? '');
        $website_logo = htmlspecialchars($row['logo'] ?? '');
        $login_logo = htmlspecialchars($row['login_logo'] ?? '');
        $invoice_logo = htmlspecialchars($row['invoice_logo'] ?? '');
        $background_login_image = htmlspecialchars($row['background_login_image'] ?? '');
    }
} catch (Exception $e) {
    error_log("Error in manage_website.php: " . $e->getMessage());
    echo "An error occurred. Please check the error log for details.";
    exit;
}
?> 

<!-- Page wrapper  -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Website Management</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                <li class="breadcrumb-item active">Website Management</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid  -->
    <div class="container-fluid">
        <!-- Start Page Content -->
        <div class="row">
            <div class="col-lg-8" style="margin-left: 10%;">
                <div class="card">
                    <div class="card-title"></div>
                    <div class="card-body">
                        <div class="input-states">
                            <form class="form-horizontal" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Title</label>
                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $title;?>" name="title" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Short Title</label>
                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $short_title;?>" name="short_title" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Footer</label>
                                        <div class="col-sm-9">
                                            <textarea class="textarea_editor form-control" name="footer" rows="5" placeholder="Enter text ..." style="height:300px;"><?php echo $footer;?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Currency Code</label>
                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $currency_code;?>" name="currency_code" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Currency Symbol</label>
                                        <div class="col-sm-9">
                                            <input type="text" value="<?php echo $currency_symbol;?>" name="currency_symbol" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Website Logo</label>
                                        <div class="col-sm-9">
                                            <image class="profile-img" src="uploadImage/Logo/<?=$website_logo?>" style="height:35%;width:25%;">
                                            <input type="hidden" value="<?=$website_logo?>" name="old_website_image">
                                            <input type="file" class="form-control" name="website_image">
                                        </div>
                                    </div>
                                </div>  

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Invoice Logo</label>
                                        <div class="col-sm-9">
                                            <image class="profile-img" src="uploadImage/Logo/<?=$invoice_logo?>" style="height:35%;width:35%;">
                                            <input type="hidden" value="<?=$invoice_logo?>" name="old_invoice_image">
                                            <input type="file" class="form-control" name="invoice_image">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Login Page Logo</label>
                                        <div class="col-sm-9">
                                            <image class="profile-img" src="uploadImage/Logo/<?=$login_logo?>" style="height:35%;width:35%;">
                                            <input type="hidden" value="<?=$login_logo?>" name="old_login_image">
                                            <input type="file" class="form-control" name="login_image">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <label class="col-sm-3 control-label">Background Image For Login Page</label>
                                        <div class="col-sm-9">
                                            <image class="profile-img" src="uploadImage/Logo/<?=$background_login_image?>" style="height:35%;width:35%;">
                                            <input type="hidden" value="<?=$background_login_image?>" name="old_back_login_image">
                                            <input type="file" class="form-control" name="back_login_image">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="btn_web" class="btn btn-primary btn-flat m-b-30 m-t-30">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Page Content -->
    </div>
</div>

<?php include('footer.php');?>

<link rel="stylesheet" href="popup_style.css">
<?php if(!empty($_SESSION['success'])) {  ?>
<div class="popup popup--icon -success js_success-popup popup--visible">
  <div class="popup__background"></div>
  <div class="popup__content">
    <h3 class="popup__content__title">Success</h3>
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
    <h3 class="popup__content__title">Error</h3>
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

<script type="text/javascript">
function refresh_cls() {
    setTimeout(function() {
        location.reload();
    }, 1000);
}
</script>