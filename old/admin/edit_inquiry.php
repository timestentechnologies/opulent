<?php
include('head.php');
include('header.php');
include('sidebar.php');

if(!isset($_GET['id'])) {
    header('Location: view_inquiries.php');
    exit();
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM inquiries WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$inquiry = $result->fetch_assoc();

if(!$inquiry) {
    header('Location: view_inquiries.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = $_POST['response'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE inquiries SET response = ?, status = ?, updated_at = NOW() WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $response, $status, $id);
    
    if($update_stmt->execute()) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: "Success!",
                    text: "Inquiry has been updated successfully",
                    type: "success",
                    showCancelButton: false,
                    confirmButtonColor: "#28a745",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                }, function() {
                    window.location.href = 'view_inquiries.php';
                });
            });
        </script>
        <?php
    } else {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                swal({
                    title: "Error!",
                    text: "Failed to update inquiry",
                    type: "error",
                    showCancelButton: false,
                    confirmButtonColor: "#dc3545",
                    confirmButtonText: "OK"
                });
            });
        </script>
        <?php
    }
}
?>

<div class="pcoded-content">
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-header">
                    <div class="row align-items-end">
                        <div class="col-lg-8">
                            <div class="page-header-title">
                                <div class="d-inline">
                                    <h4>Respond to Inquiry</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="page-header-breadcrumb">
                                <a href="view_inquiries.php" class="btn btn-secondary">Back to List</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-body">
                    <div class="card">
                        <div class="card-body">
                            <form method="post">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($inquiry['name']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($inquiry['email']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Phone</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($inquiry['phone']); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Message</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" rows="4" readonly><?php echo htmlspecialchars($inquiry['message']); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Response</label>
                                    <div class="col-sm-10">
                                        <textarea name="response" class="form-control" rows="6" required><?php echo htmlspecialchars($inquiry['response'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Status</label>
                                    <div class="col-sm-10">
                                        <select name="status" class="form-control" required>
                                            <option value="pending" <?php echo ($inquiry['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="responded" <?php echo ($inquiry['status'] == 'responded') ? 'selected' : ''; ?>>Responded</option>
                                            <option value="closed" <?php echo ($inquiry['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Created At</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" value="<?php echo date('Y-m-d H:i', strtotime($inquiry['created_at'])); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" class="btn btn-primary">Update Inquiry</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?> 