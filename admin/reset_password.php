<?php
require_once('session_handler.php');
include('connect.php');

$popup_type = '';
$popup_message = '';

if (empty($_SESSION['reset_password_email'])) {
  header('Location: forgot_password.php');
  exit;
}

$reset_email = $_SESSION['reset_password_email'];
$reset_name = $_SESSION['reset_password_name'] ?? '';

if (isset($_POST['btn_reset'])) {
  $new_password = $_POST['new_password'] ?? '';
  $confirm_password = $_POST['confirm_password'] ?? '';

  if ($new_password === '' || $confirm_password === '') {
    $popup_type = 'error';
    $popup_message = 'Please fill in all password fields.';
  } elseif ($new_password !== $confirm_password) {
    $popup_type = 'error';
    $popup_message = 'Passwords do not match.';
  } else {
    $otp1 = hash('sha256', $new_password);
    function createSalt()
    {
        return '2123293dsj2hu2nikhiljdsd';
    }
    $salt = createSalt();
    $password_hash = hash('sha256', $salt . $otp1);

    $update_stmt = $conn->prepare('UPDATE admin SET password = ? WHERE email = ?');
    $update_stmt->bind_param('ss', $password_hash, $reset_email);

    if ($update_stmt->execute()) {
      $s = 'select * from tbl_email_config';
      $r = $conn->query($s);
      $rr = mysqli_fetch_array($r);

      if (!$rr) {
        $popup_type = 'error';
        $popup_message = 'Password updated, but email configuration is missing. Please contact the administrator.';
      } else {
        $mail_host = $rr['mail_driver_host'];
        $mail_name = $rr['name'];
        $mail_username = $rr['mail_username'];
        $mail_password = $rr['mail_password'];
        $mail_port = $rr['mail_port'];

        require_once('PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = $mail_host;
        $mail->SMTPAuth = true;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->SMTPSecure = 'tls';
        $mail->Port = $mail_port;
        $mail->setFrom($mail_username, $mail_name);
        $mail->addAddress($reset_email, $reset_name);
        $mail->isHTML(true);
        $mail->Subject = 'Password Changed';

        $safe_name = htmlspecialchars($reset_name);
        $safe_email = htmlspecialchars($reset_email);
        $safe_password = htmlspecialchars($new_password);

        $mail->Body = '<!doctype html>'
          .'<html><head><meta charset="utf-8"></head>'
          .'<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">'
          .'<div style="max-width:600px;margin:0 auto;padding:24px;">'
          .'<div style="background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb;">'
          .'<div style="background:#0f766e;padding:18px 20px;color:#ffffff;">'
          .'<div style="font-size:18px;font-weight:700;">'.htmlspecialchars($mail_name).'</div>'
          .'<div style="opacity:0.9;font-size:13px;">Password Reset Confirmation</div>'
          .'</div>'
          .'<div style="padding:20px;color:#111827;font-size:14px;line-height:1.6;">'
          .'<p style="margin:0 0 12px 0;">Hello '.$safe_name.',</p>'
          .'<p style="margin:0 0 12px 0;">Your password has been changed successfully for:</p>'
          .'<p style="margin:0 0 12px 0;"><strong>'.$safe_email.'</strong></p>'
          .'<div style="margin:16px 0;padding:14px;border-radius:10px;background:#f9fafb;border:1px solid #e5e7eb;">'
          .'<div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Your new password</div>'
          .'<div style="font-size:16px;font-weight:700;letter-spacing:0.3px;">'.$safe_password.'</div>'
          .'</div>'
          .'<p style="margin:0 0 12px 0;color:#6b7280;font-size:12px;">If you did not request this change, please contact support immediately.</p>'
          .'</div>'
          .'<div style="padding:14px 20px;background:#f9fafb;color:#6b7280;font-size:12px;border-top:1px solid #e5e7eb;">'
          .'&copy; '.date('Y').' '.htmlspecialchars($mail_name).'. All rights reserved.'
          .'</div>'
          .'</div>'
          .'</div>'
          .'</body></html>';

        if ($mail->send()) {
          unset($_SESSION['reset_password_email'], $_SESSION['reset_password_name']);
          $popup_type = 'success';
          $popup_message = 'Password updated and email sent successfully.';
        } else {
          $popup_type = 'error';
          $popup_message = 'Password updated, but email could not be sent. '.$mail->ErrorInfo;
        }
      }
    } else {
      $popup_type = 'error';
      $popup_message = 'Failed to update password. Please try again.';
    }
  }
}

include('head.php');
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
        <a href="login.php"><button class="button button--success" data-for="js_success-popup">Go to Login</button></a>
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
              <h4>Set New Password</h4>
              <form method="POST">
                <div class="form-group">
                  <label>Email address</label>
                  <input type="email" class="form-control" value="<?php echo htmlspecialchars($reset_email); ?>" disabled>
                </div>

                <div class="form-group">
                  <label>New Password</label>
                  <input type="password" name="new_password" class="form-control" placeholder="New Password" required="">
                </div>

                <div class="form-group">
                  <label>Confirm Password</label>
                  <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required="">
                </div>

                <button type="submit" name="btn_reset" class="btn btn-primary btn-flat m-b-30 m-t-30">Reset Password</button>
              </form>
              <div style="margin-top: 10px;">
                <a href="forgot_password.php">Back</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- All Jquery -->
<script src="js/lib/jquery/jquery.min.js"></script>
<script src="js/lib/bootstrap/js/popper.min.js"></script>
<script src="js/lib/bootstrap/js/bootstrap.min.js"></script>
<script src="js/jquery.slimscroll.js"></script>
<script src="js/sidebarmenu.js"></script>
<script src="js/lib/sticky-kit-master/dist/sticky-kit.min.js"></script>
<script src="js/custom.min.js"></script>

</body>
</html>
