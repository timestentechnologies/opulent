<?php 
// Enable error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent header issues
ob_start();

require_once('session_config.php');

// Debug session at start
error_log("Session status at start: " . session_status());
error_log("Session ID at start: " . session_id());

include('head.php');
include('connect.php');

if(isset($_POST['btn_login']))
{
    $unm = $_POST['email'];
    $passw = hash('sha256', $_POST['password']);
    
    function createSalt()
    {
        return '2123293dsj2hu2nikhiljdsd';
    }
    $salt = createSalt();
    $pass = hash('sha256', $salt . $passw);
    
    // Log login attempt
    error_log("Login attempt for email: " . $unm);
    error_log("Generated password hash: " . $pass);
    
    $sql = "SELECT * FROM admin WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $unm);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->num_rows;
    
    error_log("Query result count: " . $count);
    
    if($count == 1) {
        $row = $result->fetch_array();
        
        // Verify password separately to debug authentication
        if($row['password'] === $pass) {
            error_log("Password match successful");
            
            // Set session variables
            $_SESSION["id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["password"] = $row['password'];
            $_SESSION["email"] = $row['email'];
            $_SESSION["fname"] = $row['fname'];
            $_SESSION["lname"] = $row['lname'];
            $_SESSION["image"] = $row['image'];
            $_SESSION["is_logged_in"] = true;
            
            // Log session data
            error_log("Session data set. Session ID: " . session_id());
            error_log("Session variables: " . print_r($_SESSION, true));
            
            // Ensure all output is cleared
            ob_clean();
            
            // Redirect with both methods
            echo "<script>window.location.href = 'index.php';</script>";
            if(!headers_sent()) {
                header("Location: index.php");
                exit();
            }
        } else {
            error_log("Password mismatch. Expected: " . $row['password'] . ", Got: " . $pass);
            echo '<div class="popup popup--icon -error js_error-popup popup--visible">
                  <div class="popup__background"></div>
                  <div class="popup__content">
                    <h3 class="popup__content__title">Error</h3>
                    <p>Invalid Email or Password</p>
                    <p><a href="login.php"><button class="button button--error" data-for="js_error-popup">Close</button></a></p>
                  </div>
                </div>';
        }
    } else {
        error_log("No user found with email: " . $unm);
        echo '<div class="popup popup--icon -error js_error-popup popup--visible">
              <div class="popup__background"></div>
              <div class="popup__content">
                <h3 class="popup__content__title">Error</h3>
                <p>Invalid Email or Password</p>
                <p><a href="login.php"><button class="button button--error" data-for="js_error-popup">Close</button></a></p>
              </div>
            </div>';
    }
}
?>

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
                                <center><img src="uploadImage/Logo/<?php echo $row_login['login_logo'];?>" style="width:50%;"></center><br><!-- <h4>Login</h4> -->
                                <form method="POST">
                               <div class="form-group">
                                        <label>Email address</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email" required="">
                                    </div>
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" class="form-control" placeholder="Password" required="">
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
                                  <!--   <div class="register-link m-t-15 text-center">
                                        <p>Don't have account ? <a href="#"> Sign Up Here</a></p>
                                    </div> -->
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

</body>

</html>