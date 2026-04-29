<?php
require_once('session_handler.php');
?>
<?php
include 'connect.php';

// Check if trying to delete a superadmin
$is_superadmin = false;
if(isset($_GET['id'])) {
    $check_sql = "SELECT group_id FROM admin WHERE id = " . intval($_GET['id']);
    $check_result = $conn->query($check_sql);
    if($check_result && $row = $check_result->fetch_assoc()) {
        if($row['group_id'] == 3) {
            $is_superadmin = true;
        }
    }
}

if($is_superadmin) {
    $_SESSION['error']='Superadmin users cannot be deleted.';
} else {
    $sql = "DELETE FROM admin WHERE id='".$_GET["id"]."'";
    $res = $conn->query($sql) ;
    $_SESSION['success']=' Record Successfully Deleted';
}
?>
<script>
window.location = "view_user.php";
</script>
