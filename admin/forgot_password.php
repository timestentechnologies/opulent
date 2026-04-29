<?php
require_once('session_handler.php');
?>
<?php ?>
<?php include('head.php');?>

  <?php
  include('connect.php');
  $popup_type = '';
  $popup_message = '';
if(isset($_POST['btn_forgot']))
{
$text_email = trim($_POST['email']);

$realemail = '';
$personname = '';
$user_name = '';

$stmt = $conn->prepare("SELECT email, fname, lname, username FROM admin WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $text_email);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
  $popup_type = 'error';
  $popup_message = 'Email address not found.';
} else {
  $realemail = $res['email'];
  $person_fname = $res['fname'];
  $person_lname = $res['lname'];
  $personname = trim($person_fname.' '.$person_lname);
  $user_name = $res['username'];

  $_SESSION['reset_password_email'] = $realemail;
  $_SESSION['reset_password_name'] = $personname;
  header('Location: reset_password.php');
  exit;
}
}
  

?> 

<?php if(!empty($popup_type) && !empty($popup_message)) { ?>
<link rel="stylesheet" href="popup_style.css">
<div class="popup popup--icon -<?php echo htmlspecialchars($popup_type); ?> js_<?php echo htmlspecialchars($popup_type); ?>-popup popup--visible">
  <div class="popup__background"></div>
  <div class="popup__content">
    <h3 class="popup__content__title">
      <?php echo ($popup_type === 'success') ? 'Success' : 'Error'; ?>
    </h1>
    <p><?php echo htmlspecialchars($popup_message); ?></p>
    <p>
      <?php if($popup_type === 'success') { ?>
        <a href="reset_password.php"><button class="button button--success" data-for="js_success-popup">Continue</button></a>
      <?php } else { ?>
        <button class="button button--error" data-for="js_error-popup">Close</button>
      <?php } ?>
    </p>
  </div>
</div>
<script>
  var addButtonTrigger = function addButtonTrigger(el) {
    el.addEventListener('click', function () {
      var popupEl = document.querySelector('.' + el.dataset.for);
      popupEl.classList.toggle('popup--visible');
    });
  };
  Array.from(document.querySelectorAll('button[data-for]')).forEach(addButtonTrigger);
</script>
<?php } ?>


    <!-- Main wrapper  -->
    <div id="main-wrapper">


        <div class="unix-login">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="login-content card">
                            <div class="login-form">
                                <h4>Forgot Password</h4>
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Email address</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email" required="">
                                    </div>
                                    
                                   
                                    <button type="submit" name="btn_forgot" class="btn btn-primary btn-flat m-b-30 m-t-30">Submit</button>
                                </form>
                                <div class="d-flex justify-content-center gap-2 m-t-15">
                                    <a href="login.php" class="btn btn-secondary btn-flat btn-sm" style="background-color: #6c757d; border-color: #6c757d; color: #fff; padding: 6px 12px; font-size: 13px; margin-right: 10px;">Back</a>
                                    <a href="login.php" class="btn btn-warning btn-flat btn-sm" style="background-color: #f39c12; border-color: #f39c12; color: #fff; padding: 6px 12px; font-size: 13px;">Back to Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- End Wrapper -->
    <!-- Font Awesome for password toggle icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
    <!-- Password Toggle Script -->
    <script src="../js/password-toggle.js"></script>

</body>

</html>