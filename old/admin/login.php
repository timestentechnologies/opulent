<?php 
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

include('session_config.php');
include('head.php');
include('connect.php');

// Debug session information
error_log("Session status: " . session_status());
error_log("Session ID: " . session_id());

// Check if already logged in
if(isset($_SESSION['id'])) {
    error_log("User already logged in, redirecting to index");
    header("Location: index.php");
    exit();
}

if(isset($_POST['btn_login'])) {
    error_log("Login form submitted");
    error_log("Email: " . $_POST['email']);
    
    $unm = $_POST['email'];
    $passw = hash('sha256', $_POST['password']);
    
    function createSalt() {
        return '2123293dsj2hu2nikhiljdsd';
    }
    
    $salt = createSalt();
    $pass = hash('sha256', $salt . $passw);
    
    error_log("Hashed password: " . $pass);
    
    $sql = "SELECT * FROM admin WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $unm, $pass);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        error_log("User found in database: " . $row['email']);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION["id"] = $row['id'];
        $_SESSION["username"] = $row['username'];
        $_SESSION["email"] = $row['email'];
        $_SESSION["fname"] = $row['fname'];
        $_SESSION["lname"] = $row['lname'];
        $_SESSION["image"] = $row['image'];
        
        // Set last activity time
        $_SESSION['last_activity'] = time();
        
        error_log("Session variables set, redirecting to index");
        
        // Clear any output
        ob_clean();
        
        // Redirect to dashboard
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid email or password";
        error_log("Login failed - no matching user found");
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="popup_style.css">
</head>
<body>
    <!-- Main wrapper  -->
    <div id="main-wrapper">
        <div class="unix-login">
             <?php
             $sql_login = "select * from manage_website"; 
             $result_login = $conn->query($sql_login);
             $row_login = mysqli_fetch_array($result_login);
             ?>
            <div class="container-fluid" style="background-image: url('uploadImage/Logo/<?php echo $row_login['background_login_image'];?>');
 background-color: #cccccc;">
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="login-content card">
                            <div class="login-form">
                                <center><img src="uploadImage/Logo/<?php echo $row_login['login_logo'];?>" style="width:50%;"></center><br>
                                <?php if(isset($error)): ?>
                                    <div class="popup popup--icon -error js_error-popup popup--visible">
                                        <div class="popup__background"></div>
                                        <div class="popup__content">
                                            <h3 class="popup__content__title">Error</h3>
                                            <p><?php echo $error; ?></p>
                                            <p>
                                                <button class="button button--error" data-for="js_error-popup">Close</button>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <form method="POST" action="">
                                    <div class="form-group">
                                        <label>Email address</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox"> Remember Me
                                        </label>
                                        <label class="pull-right">
                                            <a href="forgot_password.php">Forgotten Password?</a>
                                        </label>   
                                    </div>
                                    <button type="submit" name="btn_login" class="btn btn-primary btn-flat m-b-30 m-t-30">Sign in</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <script>
        var addButtonTrigger = function addButtonTrigger(el) {
            el.addEventListener('click', function () {
                var popupEl = document.querySelector('.' + el.dataset.for);
                popupEl.classList.toggle('popup--visible');
            });
        };
        Array.from(document.querySelectorAll('button[data-for]')).forEach(addButtonTrigger);
    </script>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>