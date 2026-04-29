<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('session_config.php');
include('connect.php');

// Debug session state
error_log("Header - Session ID: " . session_id());
error_log("Header - Session Data: " . print_r($_SESSION, true));

// Check if user is logged in
if(!isset($_SESSION["is_logged_in"]) || !isset($_SESSION["email"])) {
    error_log("Header - Session check failed: is_logged_in=" . (isset($_SESSION["is_logged_in"]) ? "true" : "false") . 
              ", email=" . (isset($_SESSION["email"]) ? "set" : "not set"));
    // Clear any output that might have been sent
    ob_clean();
    header("Location: login.php");
    exit();
}

// Verify session data against database
$email = $_SESSION["email"];
$sql = "SELECT * FROM admin WHERE email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    error_log("Header - Database verification failed for email: " . $email);
    // Clear session and redirect
    session_destroy();
    ob_clean();
    header("Location: login.php");
    exit();
}

// If we get here, the session is valid
error_log("Header - Session validation successful for user: " . $email);
?>
<!-- Main wrapper  -->
<div id="main-wrapper">
    <!-- header header  -->
    <div class="header">
        <nav class="navbar top-navbar navbar-expand-md navbar-light">
            <!-- Logo -->
            <div class="navbar-header">
                <a class="navbar-brand" href="index.php">
                    <!-- Logo icon -->
                    <?php
                    $sql_header_logo = "select * from manage_website"; 
                    $result_header_logo = $conn->query($sql_header_logo);
                    $row_header_logo = mysqli_fetch_array($result_header_logo);
                    ?>
                    <b><img src="uploadImage/Logo/<?php echo $row_header_logo['logo'];?>" alt="homepage" class="dark-logo" style="max-width: 180px; max-height: 50px; width: auto; height: auto; object-fit: contain;"/></b>
                </a>
            </div>
            <!-- End Logo -->
            <div class="navbar-collapse">
                <!-- toggle and nav items -->
                <ul class="navbar-nav mr-auto mt-md-0">
                    <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
                    <li class="nav-item m-l-10"> <a class="nav-link sidebartoggler hidden-sm-down text-muted" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                </ul>
                <!-- User profile and search -->
                <ul class="navbar-nav my-lg-0">
                    <!-- Profile -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php 
                            $sql = "SELECT * FROM admin WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $_SESSION["id"]);
                            $stmt->execute();
                            $query = $stmt->get_result();
                            while($row = mysqli_fetch_array($query)) {
                                extract($row);
                                $fname = $row['fname'];
                                $lname = $row['lname'];
                                $email = $row['email'];
                                $contact = $row['contact'];
                                $dob1 = $row['dob'];
                                $gender = $row['gender'];
                                $image = $row['image'];
                            }
                            ?>
                            <img src="uploadImage/Profile/<?=$image?>" alt="user" class="profile-pic" />
                        </a>
                        <div class="dropdown-menu dropdown-menu-right animated zoomIn">
                            <ul class="dropdown-user">
                                <li><a href="profile.php"><i class="ti-user"></i> Profile</a></li>
                                <li><a href="changepassword.php"><i class="ti-key"></i> Changed Password</a></li>
                                <li><a href="logout.php"><i class="fa fa-power-off"></i> Logout</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    <!-- End header header -->
</div>