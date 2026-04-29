<?php
require_once('session_handler.php');
?>
<?php 
include('connect.php');
$sql = "select * from admin where id = '".$_SESSION["id"]."'";
$result=$conn->query($sql);
$row1=mysqli_fetch_array($result);

$q = "select * from tbl_permission_role where role_id='".$row1['group_id']."'";
$ress=$conn->query($q);
//$row=mysqli_fetch_all($ress);
$name = array();
while($row=mysqli_fetch_array($ress)){
  $sql = "select * from tbl_permission where id = '".$row['permission_id']."'";
  $result=$conn->query($sql);
  $row1=mysqli_fetch_array($result);
  array_push($name,$row1[1]);
}
$_SESSION['name']=$name;
$useroles=$_SESSION['name'];

?>

<!-- Left Sidebar  -->
<div class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                <li class="nav-devider"></li>
                <li class="nav-label">Home</li>
                <li> <a href="index.php" aria-expanded="false"><i class="fa fa-tachometer"></i>Dashboard</a>
                </li> 
           
<?php if(isset($useroles)){  if(in_array("manage_user",$useroles)){ ?> 
<li class="nav-label">Owner</li>
<li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-user-plus"></i><span class="hide-menu">User Management</span></a>
<ul aria-expanded="false" class="collapse">
<?php if(isset($useroles)){  if(in_array("add_user",$useroles)){ ?> 
<li><a href="add_user.php">Add User</a></li>
<?php } } ?>
<li><a href="view_user.php">All Users</a></li>
<li><a href="view_user.php?filter=employees">Employees</a></li>
<li><a href="view_user.php?filter=admins">Admins</a></li>
</ul>
</li>
<?php } } ?>



         <?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-address-book"></i><span class="hide-menu">Customer Management</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="add_customer.php">Add Customer</a></li>
                           <li><a href="view_customer.php">View Customer</a></li>
                        </ul>
                    </li>
                <?php }?>


                <?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-credit-card"></i><span class="hide-menu">Financial Management</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="payments.php">Payments</a></li>
                            <li><a href="expenses.php">Expenses</a></li>
                            <li><a href="invoices.php">Invoices</a></li>
                            <li><a href="customer_statements.php">Customer Statements</a></li>
                            <li><a href="owner_statements.php">Owner Statements</a></li>
                        </ul>
                    </li>
                <?php }?>


                <?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span class="hide-menu">Order Management</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="add_order.php">Add order</a></li>
                           <li><a href="view_order.php">Manage order</a></li>
                        </ul>
                    </li>
                <?php }?>


                <?php if($_SESSION["username"]=='admin') 
         { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-inr"></i><span class="hide-menu">Plans Management</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="view_subscription_plans.php">Manage Plans</a></li>
                            <li><a href="view_user_subscriptions.php">Manage User Plans</a></li>
                            <li><a href="view_subscribers.php">Email Subscribers</a></li>
                        </ul>
                    </li>
                <?php }?>


             


<?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-file" aria-hidden="true"></i><span class="hide-menu">Reports</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="today_delivery.php">Today's Delivery Report</a></li>
                           <li><a href="pending_order.php">Pending Orders</a></li>
                        </ul>
                    </li>
                <?php }?>

         <?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>      <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-question-circle" aria-hidden="true"></i><span class="hide-menu">Inquiries</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="view_inquiries.php">Manage Inquiries</a></li>
                        </ul>
                    </li>
                <?php }?>



                <?php if($_SESSION["username"]=='admin') { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-bandcamp"></i><span class="hide-menu">Services</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="add_services.php">Add Servises </a></li>
                           <li><a href="view_services.php">View Services</a></li>
                        </ul>
                    </li>

                <?php if($_SESSION["username"]=='admin') { ?>
                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-users"></i><span class="hide-menu">Employee Management</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="view_user.php?filter=employees">Employees</a></li>
                            <li><a href="view_user.php?filter=admins">Admins</a></li>
                        </ul>
                    </li>

                     <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-shield"></i><span class="hide-menu">User Permissions</span></a>
                        <ul aria-expanded="false" class="collapse">
                            <li><a href="assign_role.php">Assign Role</a></li>
                           <li><a href="view_role.php">View Role</a></li>
                        </ul>
                    </li>

<?php }?>


                <li> <a class="has-arrow" href="#" aria-expanded="false"><i class="fa fa-cog"></i><span class="hide-menu">Setting</span></a>
                        <ul aria-expanded="false" class="collapse">
                           <?php //if($_SESSION["username"]=='user' || $_SESSION["username"]=='admin') { ?>
                           <li><a href="manage_website.php">Appearance Management</a></li>
                         <?php //} ?>
                          <li><a href="email_config.php">Email Management</a></li>
                          <li><a href="email_test.php">Email Test</a></li>
                          <?php if($_SESSION["username"]=='admin') { ?>
                          <li><a href="payment_config.php">Payment Settings</a></li>
                          <li><a href="financial_backfill.php">Financial Backfill</a></li>
                          <li><a href="debug_backfill.php">Backfill Debug</a></li>
                          <li><a href="setup_groups.php">Setup User Groups</a></li>
                          <?php } ?>
                           
                        </ul>
                    </li> 
                <?php } ?>

                <?php if(in_array($_SESSION["username"], ['user', 'admin'])) { ?>
                     <li> <a target="_blank" href="https://timesten.classeslite.com" aria-expanded="false"><i class="fa fa-info-circle"></i><span class="hide-menu">Know more !</span></a>
                        
                    </li>

<?php }?>


    
                    </ul>   
                </nav>
                <!-- End Sidebar navigation -->
            </div>
            <!-- End Sidebar scroll-->
        </div>
        <!-- End Left Sidebar  -->