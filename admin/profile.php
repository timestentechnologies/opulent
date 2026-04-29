<?php
require_once('session_handler.php');
include('head.php');
include('header.php');
include('sidebar.php');

include('connect.php');

// Get current user data
$sql = "SELECT * FROM admin WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["id"]);

$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if(isset($_POST["btn_update"]))
{
    extract($_POST);
    $target_dir = "uploadImage/Profile/";
    
    if($_FILES["image"]["tmp_name"] != '') {
        $image = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $image)) {
            $sql = "UPDATE admin SET fname=?, lname=?, email=?, contact=?, dob=?, gender=?, image=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi", $fname, $lname, $email, $contact, $dob, $gender, basename($_FILES["image"]["name"]), $_SESSION["id"]);
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
            return;
        }
    } else {
        $sql = "UPDATE admin SET fname=?, lname=?, email=?, contact=?, dob=?, gender=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $fname, $lname, $email, $contact, $dob, $gender, $_SESSION["id"]);
    }
    
    if($stmt->execute()) {
        $_SESSION["email"] = $email;
        $_SESSION["fname"] = $fname;
        $_SESSION["lname"] = $lname;
        if($_FILES["image"]["tmp_name"] != '') {
            $_SESSION["image"] = basename($_FILES["image"]["name"]);
        }
        echo "<script>alert('Profile Updated Successfully'); window.location.reload();</script>";
    } else {
        echo "<script>alert('Error updating profile');</script>";
    }
}
?> 
   


  <!-- Page wrapper  -->
        <div class="page-wrapper">
            <!-- Bread crumb -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-primary"> Profile</h3> </div>
                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
            <!-- End Bread crumb -->
            <!-- Container fluid  -->
            <div class="container-fluid">
                <!-- Start Page Content -->
                
                <!-- /# row -->
                <div class="row">
                    <div class="col-lg-8" style="margin-left: 10%;">
                        <div class="card">
                            <div class="card-title">
                               
                            </div>
                            <div class="card-body">
                                <div class="input-states">
                                    <form class="form-horizontal" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">First Name</label>
                                                <div class="col-sm-9">
                                                    <input type="text"  value="<?php echo $row['fname'];?>"  name="fname" class="form-control" id="event" onkeydown="return alphaOnly(event);" required>
                                                </div>
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Last Name</label>
                                                <div class="col-sm-9">
                                                    <input type="text"  value="<?php echo $row['lname'];?>"  name="lname" class="form-control" id="event" onkeydown="return alphaOnly(event);" required>
                                                </div>
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Email</label>
                                                <div class="col-sm-9">
                                                    <input type="text" value="<?php echo $row['email'];?>"  name="email" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$"   class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Gender</label>
                                                <div class="col-sm-9">
                                                   <select name="gender" class="form-control" required>
                                                     <option value="Male"  <?php if($row['gender']=="Male"){ echo "selected";}?>>Male</option>
                                                      <option value="Female" <?php if($row['gender']=="Female"){ echo "selected";}?>>Female</option>
                                                   </select>
                                                </div>
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Date Of Birth</label>
                                                <div class="col-sm-9">
                                                    <input type="date" value="<?php echo $row['dob'];?>" name="dob" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Contact</label>
                                                <div class="col-sm-9">
                                                    <input type="text" value="<?php echo $row['contact'];?>"  name="contact" class="form-control" id="tbNumbers" minlength="10" maxlength="10" onkeypress="javascript:return isNumber(event)" required>
                                                </div>
                                            </div>
                                        </div>

                                         <div class="form-group">
                                            <div class="row">
                                                <label class="col-sm-3 control-label">Image</label>
                                                <div class="col-sm-9">
                                  <image class="profile-img" src="uploadImage/Profile/<?=$row['image']?>" style="height:30%;width:50%;">
                  <input type="hidden" value="<?=$row['image']?>" name="old_image">
                          <input type="file" class="form-control" name="image">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" name="btn_update" class="btn btn-primary btn-flat m-b-30 m-t-30">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                  
                </div>
                
               
                <!-- /# row -->

                <!-- End PAge Content -->
           

<?php include('footer.php');?>

<!--  Author Name: Nikhil Bhalerao - www.nikhilbhalerao.com 
PHP, Laravel and Codeignitor Developer -->

<link rel="stylesheet" href="popup_style.css">
<?php if(!empty($_SESSION['success'])) {  ?>
<div class="popup popup--icon -success js_success-popup popup--visible">
  <div class="popup__background"></div>
  <div class="popup__content">
    <h3 class="popup__content__title">
      Success 
    </h1>
    <p><?php echo $_SESSION['success']; ?></p>
    <p>
      <button class="button button--success" data-for="js_success-popup">Close</button>
    </p>
  </div>
</div>
<?php unset($_SESSION["success"]);  
} ?>
<?php if(!empty($_SESSION['error'])) {  ?>
<div class="popup popup--icon -error js_error-popup popup--visible">
  <div class="popup__background"></div>
  <div class="popup__content">
    <h3 class="popup__content__title">
      Error 
    </h1>
    <p><?php echo $_SESSION['error']; ?></p>
    <p>
      <button class="button button--error" data-for="js_error-popup">Close</button>
    </p>
  </div>
</div>
<?php unset($_SESSION["error"]);  } ?>
    <script>
      var addButtonTrigger = function addButtonTrigger(el) {
  el.addEventListener('click', function () {
    var popupEl = document.querySelector('.' + el.dataset.for);
    popupEl.classList.toggle('popup--visible');
  });
};

Array.from(document.querySelectorAll('button[data-for]')).
forEach(addButtonTrigger);
    </script>