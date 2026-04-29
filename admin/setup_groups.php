<?php
require_once('session_handler.php');
require_once('connect.php');

if (!isset($_SESSION["id"]) || $_SESSION["username"] !== 'admin') {
    header('Location: index.php');
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Default groups to add
$default_groups = [
    ['id' => 1, 'name' => 'admin', 'description' => 'System Administrators with full access'],
    ['id' => 2, 'name' => 'user', 'description' => 'Regular system users'],
    ['id' => 3, 'name' => 'superadmin', 'description' => 'Super Administrator with highest privileges'],
    ['id' => 4, 'name' => 'employee', 'description' => 'Standard employees with limited access']
];

$added = [];
$existing = [];

foreach ($default_groups as $group) {
    // Check if group exists
    $check = $conn->prepare("SELECT id FROM tbl_group WHERE id = ? OR name = ?");
    $check->bind_param('is', $group['id'], $group['name']);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows == 0) {
        // Insert new group
        $insert = $conn->prepare("INSERT INTO tbl_group (id, name, description) VALUES (?, ?, ?)");
        $insert->bind_param('iss', $group['id'], $group['name'], $group['description']);
        if ($insert->execute()) {
            $added[] = $group['name'];
        }
    } else {
        $existing[] = $group['name'];
    }
}

// Get current groups
$groups_result = $conn->query("SELECT * FROM tbl_group ORDER BY id");
$current_groups = [];
while ($g = $groups_result->fetch_assoc()) {
    $current_groups[] = $g;
}
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Setup User Groups</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Setup Groups</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if (!empty($added)): ?>
                            <div class="alert alert-success">
                                <strong>Groups added:</strong> <?php echo implode(', ', $added); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($existing)): ?>
                            <div class="alert alert-info">
                                <strong>Groups already exist:</strong> <?php echo implode(', ', $existing); ?>
                            </div>
                        <?php endif; ?>

                        <h4>Current Groups in System</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Group Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($current_groups as $group): ?>
                                <tr>
                                    <td><?php echo $group['id']; ?></td>
                                    <td><?php echo htmlspecialchars($group['name']); ?></td>
                                    <td><?php echo htmlspecialchars($group['description']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <hr>
                        <h5>Group Structure</h5>
                        <ul>
                            <li><strong>superadmin (ID: 3)</strong> - Highest level administrator</li>
                            <li><strong>admin (ID: 1)</strong> - System administrators</li>
                            <li><strong>employee (ID: 4)</strong> - Standard employees</li>
                            <li><strong>user (ID: 2)</strong> - Regular users</li>
                        </ul>

                        <a href="view_user.php" class="btn btn-primary">View Users</a>
                        <a href="view_role.php" class="btn btn-secondary">Manage Roles</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
