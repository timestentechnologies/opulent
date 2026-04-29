<?php
require_once('session_handler.php');
?>
<?php
include 'connect.php';


$sql = "DELETE FROM customer WHERE id='".$_GET["id"]."'";
$res = $conn->query($sql) ;
 $_SESSION['success']=' Record Successfully Deleted';
?>
<script>
//alert("Delete Successfully");
window.location = "view_customer.php";
</script>

