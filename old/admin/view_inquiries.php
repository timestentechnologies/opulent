<?php
include('head.php');
include('header.php');
include('sidebar.php');
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
                                    <h4>Inquiries Management</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="page-body">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive dt-responsive">
                                <table id="dom-jqry" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Customer Info</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT i.*, 
                                               c.fname as customer_fname, 
                                               c.lname as customer_lname,
                                               c.id as customer_id 
                                               FROM inquiries i 
                                               LEFT JOIN customer c ON i.customer_id = c.id 
                                               ORDER BY i.created_at DESC";
                                        $result = $conn->query($sql);
                                        
                                        while($row = $result->fetch_assoc()) {
                                            $statusClass = '';
                                            switch($row['status']) {
                                                case 'pending':
                                                    $statusClass = 'badge badge-warning';
                                                    break;
                                                case 'responded':
                                                    $statusClass = 'badge badge-success';
                                                    break;
                                                case 'closed':
                                                    $statusClass = 'badge badge-secondary';
                                                    break;
                                            }
                                        ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td>
                                                <?php 
                                                if ($row['customer_id']) {
                                                    echo '<span class="badge badge-info">';
                                                    echo 'Customer #' . htmlspecialchars($row['customer_id']) . ': ';
                                                    echo htmlspecialchars($row['customer_fname'] . ' ' . $row['customer_lname']);
                                                    echo '</span>';
                                                } else {
                                                    echo '<span class="badge badge-secondary">Guest Inquiry</span>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>
                                            <td><span class="<?php echo $statusClass; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_inquiry.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fa fa-edit"></i> Respond
                                                </a>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function() {
    $('#dom-jqry').DataTable({
        "order": [[ 6, "desc" ]],
        "pageLength": 25
    });
});
</script> 