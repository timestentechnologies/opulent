<?php
include 'connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$sql = "UPDATE `order` SET `status`='delivered' WHERE `id`='".$_GET['id']."'";
$res = $conn->query($sql);

if($res) {
    $_SESSION['success'] = 'Order successfully marked as delivered';
} else {
    $_SESSION['error'] = 'Error updating order status: ' . $conn->error;
}
?>
<script>
//alert("Delete Successfully");
window.location = "view_order.php";
</script>

