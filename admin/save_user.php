<?php
require_once('session_handler.php');
?>

<?php
date_default_timezone_set('Asia/Kolkata');
$current_date = date('Y-m-d');
include('../connect.php');

// Sanitize inputs
function sanitizeInput($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

// Extract and sanitize POST data
$email = sanitizeInput($_POST['email']);
$fname = sanitizeInput($_POST['fname']);
$lname = sanitizeInput($_POST['lname']);
$gender = sanitizeInput($_POST['gender']);
$dob = isset($_POST['dob']) ? sanitizeInput($_POST['dob']) : '';
$contact = sanitizeInput($_POST['contact']);
$address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : '';
$group_id = sanitizeInput($_POST['group_id']);

// Generate username from email (part before @)
$username = strtolower(explode('@', $email)[0]);

// Password hashing
$passw = hash('sha256', $_POST['password']);
//$passw = hash('sha256',$p);
//echo $passw;exit;
function createSalt()
{
    return '2123293dsj2hu2nikhiljdsd';
}
$salt = createSalt();
$pass = hash('sha256', $salt . $passw);

// Image processing
$image = '';
if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image = time() . '_' . $_FILES['image']['name']; // Add timestamp to prevent duplicate filenames
    $target = "../uploadImage/Profile/".basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // @unlink("uploadImage/Profile/".$_POST['old_image']);
        $msg = "Image uploaded successfully";
    } else {
        $msg = "Failed to upload image";
    }
}

// Check if username already exists
$check_query = "SELECT * FROM admin WHERE username = '$username'";
$result = $conn->query($check_query);
if($result->num_rows > 0) {
    // Username exists, append a random number
    $username = $username . rand(100, 999);
}

// Database insertion
$sql = "INSERT INTO admin (username, email, password, fname, lname, gender, dob, contact, address, created_on, image, group_id)
        VALUES ('$username', '$email', '$pass', '$fname', '$lname', '$gender', '$dob', '$contact', '$address', '$current_date', '$image', '$group_id')";

if ($conn->query($sql) === TRUE) {
    $_SESSION['success']=' Record Successfully Added';
    ?>
<script type="text/javascript">
window.location="../view_user.php";
</script>
<?php
} else {
    $_SESSION['error']='Something Went Wrong: ' . $conn->error;
    ?>
<script type="text/javascript">
window.location="../view_user.php";
</script>
<?php } ?>