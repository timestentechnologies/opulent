<?php
require_once('session_handler.php');
?>
<?php 
require_once('connect.php');

// Check if admin is logged in
if (!isset($_SESSION["id"])) {
    ?>
    <script>
    window.location="login.php";
    </script>
    <?php
    exit();
}

// Handle form submission for adding owner entry
if(isset($_POST['add_entry'])) {
    $entry_type = mysqli_real_escape_string($conn, $_POST['entry_type']);
    $amount = floatval($_POST['amount']);
    $entry_direction = mysqli_real_escape_string($conn, $_POST['entry_direction']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $reference_number = !empty($_POST['reference_number']) ? mysqli_real_escape_string($conn, $_POST['reference_number']) : NULL;
    $transaction_date = mysqli_real_escape_string($conn, $_POST['transaction_date']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $recorded_by = $_SESSION["id"];
    
    $sql = "INSERT INTO owner_statements (entry_type, amount, entry_direction, description, 
            reference_number, transaction_date, notes, recorded_by) 
            VALUES ('$entry_type', $amount, '$entry_direction', '$description', 
            " . ($reference_number ? "'$reference_number'" : "NULL") . ", 
            '$transaction_date', '$notes', $recorded_by)";
    
    if($conn->query($sql)) {
        $_SESSION['success'] = "Owner entry recorded successfully!";
    } else {
        $_SESSION['error'] = "Error recording entry: " . $conn->error;
    }
    header("Location: owner_statements.php");
    exit();
}

// Handle delete
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM owner_statements WHERE id = $delete_id";
    if($conn->query($sql)) {
        $_SESSION['success'] = "Entry deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting entry!";
    }
    header("Location: owner_statements.php");
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

// Get filter parameters
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filter_direction = isset($_GET['filter_direction']) ? $_GET['filter_direction'] : '';
$filter_start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$filter_end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Build query
$query = "SELECT os.*, a.fname as admin_fname, a.lname as admin_lname 
          FROM owner_statements os 
          LEFT JOIN admin a ON os.recorded_by = a.id 
          WHERE os.transaction_date BETWEEN '$filter_start_date' AND '$filter_end_date'";

if($filter_type) {
    $query .= " AND os.entry_type = '$filter_type'";
}
if($filter_direction) {
    $query .= " AND os.entry_direction = '$filter_direction'";
}
$query .= " ORDER BY os.transaction_date DESC, os.id DESC";

$result = $conn->query($query);

// Get summary statistics
$summary_query = "SELECT 
    SUM(CASE WHEN entry_direction = 'in' THEN amount ELSE 0 END) as total_in,
    SUM(CASE WHEN entry_direction = 'out' THEN amount ELSE 0 END) as total_out,
    SUM(CASE WHEN entry_direction = 'in' THEN amount ELSE -amount END) as net_balance,
    COUNT(*) as total_entries
    FROM owner_statements 
    WHERE transaction_date BETWEEN '$filter_start_date' AND '$filter_end_date'";
$summary_result = $conn->query($summary_query);
$summary = $summary_result->fetch_assoc();

// Get overall balance
$overall_query = "SELECT SUM(CASE WHEN entry_direction = 'in' THEN amount ELSE -amount END) as overall_balance FROM owner_statements";
$overall_result = $conn->query($overall_query);
$overall_balance = $overall_result->fetch_assoc()['overall_balance'] ?? 0;

// Entry type labels
$entry_types = [
    'capital_injection' => 'Capital Injection',
    'drawings' => 'Owner Drawings',
    'loan_given' => 'Loan Given',
    'loan_received' => 'Loan Received',
    'profit_share' => 'Profit Share',
    'expense_reimbursement' => 'Expense Reimbursement',
    'salary' => 'Owner Salary',
    'dividend' => 'Dividend',
    'other_in' => 'Other Income',
    'other_out' => 'Other Expense'
];

$type_directions = [
    'capital_injection' => 'in',
    'drawings' => 'out',
    'loan_given' => 'out',
    'loan_received' => 'in',
    'profit_share' => 'in',
    'expense_reimbursement' => 'in',
    'salary' => 'out',
    'dividend' => 'out',
    'other_in' => 'in',
    'other_out' => 'out'
];

$type_badges = [
    'capital_injection' => 'badge-paid',
    'drawings' => 'badge-unpaid',
    'loan_given' => 'badge-cleaning',
    'loan_received' => 'badge-received',
    'profit_share' => 'badge-paid',
    'expense_reimbursement' => 'badge-received',
    'salary' => 'badge-unpaid',
    'dividend' => 'badge-unpaid',
    'other_in' => 'badge-paid',
    'other_out' => 'badge-cleaning'
];
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <!-- Bread crumb -->
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Owner Statements</h3> 
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Owner Statements</li>
            </ol>
        </div>
    </div>
    <!-- End Bread crumb -->
    
    <!-- Container fluid -->
    <div class="container-fluid">
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-arrow-down"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['total_in'] ?? 0, 0); ?></h3>
                        <p>Total In</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-arrow-up"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['total_out'] ?? 0, 0); ?></h3>
                        <p>Total Out</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-navy">
                    <div class="card-icon"><i class="ti-exchange-vertical"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($summary['net_balance'] ?? 0, 0); ?></h3>
                        <p>Net Balance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card card-orange">
                    <div class="card-icon"><i class="ti-wallet"></i></div>
                    <div class="card-info">
                        <h3>Ksh<?php echo number_format($overall_balance, 0); ?></h3>
                        <p>Overall Balance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Add Section -->
        <div class="row m-t-30">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-filter"></i> Filter Entries</h4>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addEntryModal">
                            <i class="fa fa-plus"></i> Add Entry
                        </button>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Entry Type</label>
                                    <select name="filter_type" class="form-control">
                                        <option value="">All Types</option>
                                        <?php foreach($entry_types as $key => $label) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo $filter_type == $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Direction</label>
                                    <select name="filter_direction" class="form-control">
                                        <option value="">All</option>
                                        <option value="in" <?php echo $filter_direction == 'in' ? 'selected' : ''; ?>>Money In</option>
                                        <option value="out" <?php echo $filter_direction == 'out' ? 'selected' : ''; ?>>Money Out</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo $filter_start_date; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo $filter_end_date; ?>">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info btn-block"><i class="fa fa-filter"></i> Filter</button>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <a href="owner_statements.php" class="btn btn-secondary btn-block"><i class="fa fa-refresh"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Owner Statements Table -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-list"></i> Owner Account Entries</h4>
                        <a href="owner_statement_print.php" target="_blank" class="btn btn-success"><i class="fa fa-print"></i> Print Statement</a>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table" id="ownerTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Reference</th>
                                        <th>Direction</th>
                                        <th>Amount</th>
                                        <th>Recorded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $result->fetch_assoc()) { 
                                        $direction_badge = $row['entry_direction'] == 'in' ? 'badge-paid' : 'badge-unpaid';
                                    ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $row['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($row['transaction_date'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $type_badges[$row['entry_type']] ?? 'badge-default'; ?>">
                                                <?php echo $entry_types[$row['entry_type']] ?? ucfirst(str_replace('_', ' ', $row['entry_type'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                                        <td><?php echo $row['reference_number'] ?: '-'; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $direction_badge; ?>">
                                                <?php echo ucfirst($row['entry_direction']); ?>
                                            </span>
                                        </td>
                                        <td class="price-cell <?php echo $row['entry_direction'] == 'in' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $row['entry_direction'] == 'in' ? '+' : '-'; ?>Ksh<?php echo number_format($row['amount'], 2); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['admin_fname'] . ' ' . $row['admin_lname']); ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewModal<?php echo $row['id']; ?>"><i class="fa fa-eye"></i></button>
                                            <a href="owner_statements.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">Entry Details #<?php echo $row['id']; ?></h4>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered">
                                                        <tr><td><strong>Type:</strong></td><td><?php echo $entry_types[$row['entry_type']] ?? ucfirst($row['entry_type']); ?></td></tr>
                                                        <tr><td><strong>Direction:</strong></td><td><?php echo ucfirst($row['entry_direction']); ?></td></tr>
                                                        <tr><td><strong>Amount:</strong></td><td class="<?php echo $row['entry_direction'] == 'in' ? 'text-success' : 'text-danger'; ?>"><?php echo $row['entry_direction'] == 'in' ? '+' : '-'; ?>Ksh<?php echo number_format($row['amount'], 2); ?></td></tr>
                                                        <tr><td><strong>Description:</strong></td><td><?php echo htmlspecialchars($row['description']); ?></td></tr>
                                                        <tr><td><strong>Date:</strong></td><td><?php echo date('F d, Y', strtotime($row['transaction_date'])); ?></td></tr>
                                                        <tr><td><strong>Reference:</strong></td><td><?php echo $row['reference_number'] ?: '-'; ?></td></tr>
                                                        <tr><td><strong>Notes:</strong></td><td><?php echo nl2br(htmlspecialchars($row['notes'])); ?></td></tr>
                                                        <tr><td><strong>Recorded By:</strong></td><td><?php echo htmlspecialchars($row['admin_fname'] . ' ' . $row['admin_lname']); ?></td></tr>
                                                        <tr><td><strong>Recorded At:</strong></td><td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary by Type -->
        <div class="row m-t-20">
            <div class="col-12">
                <div class="table-card">
                    <div class="table-header">
                        <h4><i class="ti-bar-chart"></i> Summary by Entry Type</h4>
                    </div>
                    <div class="table-body">
                        <div class="table-responsive">
                            <table class="table modern-table">
                                <thead>
                                    <tr>
                                        <th>Entry Type</th>
                                        <th>Total In</th>
                                        <th>Total Out</th>
                                        <th>Net</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $type_summary_query = "SELECT 
                                        entry_type,
                                        SUM(CASE WHEN entry_direction = 'in' THEN amount ELSE 0 END) as total_in,
                                        SUM(CASE WHEN entry_direction = 'out' THEN amount ELSE 0 END) as total_out,
                                        SUM(CASE WHEN entry_direction = 'in' THEN amount ELSE -amount END) as net
                                        FROM owner_statements 
                                        WHERE transaction_date BETWEEN '$filter_start_date' AND '$filter_end_date' 
                                        GROUP BY entry_type 
                                        ORDER BY net DESC";
                                    $type_summary_result = $conn->query($type_summary_query);
                                    while($type_row = $type_summary_result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><span class="status-badge <?php echo $type_badges[$type_row['entry_type']] ?? 'badge-default'; ?>"><?php echo $entry_types[$type_row['entry_type']] ?? ucfirst($type_row['entry_type']); ?></span></td>
                                        <td class="text-success">Ksh<?php echo number_format($type_row['total_in'], 2); ?></td>
                                        <td class="text-danger">Ksh<?php echo number_format($type_row['total_out'], 2); ?></td>
                                        <td class="<?php echo $type_row['net'] >= 0 ? 'text-success' : 'text-danger'; ?> font-weight-bold">Ksh<?php echo number_format($type_row['net'], 2); ?></td>
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

<!-- Add Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Owner Account Entry</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Entry Type *</label>
                                <select name="entry_type" class="form-control" required onchange="setDirection(this.value)">
                                    <?php foreach($entry_types as $key => $label) { ?>
                                        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Direction *</label>
                                <select name="entry_direction" class="form-control" required>
                                    <option value="in">Money In (+)</option>
                                    <option value="out">Money Out (-)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount (Ksh) *</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transaction Date *</label>
                                <input type="date" name="transaction_date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description *</label>
                                <input type="text" name="description" class="form-control" required placeholder="Description of this entry...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Reference Number</label>
                                <input type="text" name="reference_number" class="form-control" placeholder="Transaction reference...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Additional Notes</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Any additional information..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_entry" class="btn btn-primary">Add Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Dashboard Cards */
.dashboard-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px 20px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    position: relative;
    overflow: hidden;
    min-height: 100px;
}
.dashboard-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}
.dashboard-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 5px;
}

/* Brand Colors - Navy Blue and Warm Orange */
.card-navy::before { background: #1a365d; }
.card-orange::before { background: #ed8936; }

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    margin-right: 20px;
}
.card-navy .card-icon { background: #1a365d; color: #fff; }
.card-orange .card-icon { background: #ed8936; color: #fff; }

.card-info {
    background: transparent;
}
.card-info h3 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 5px 0;
    color: #2d3748;
    background: transparent;
}
.card-info p {
    font-size: 13px;
    color: #718096;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    background: transparent;
}

/* Table Cards */
.table-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 20px;
}
.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.table-header h4 {
    margin: 0;
    font-size: 18px;
    color: #2d3748;
    display: flex;
    align-items: center;
    gap: 10px;
}
.table-header i {
    color: #1a365d;
}
.table-body {
    padding: 0;
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
.modern-table thead th {
    background: #f7fafc;
    color: #4a5568;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 15px;
    border: none;
    border-bottom: 2px solid #e2e8f0;
}
.modern-table tbody tr {
    transition: background 0.2s;
}
.modern-table tbody tr:hover {
    background: #f7fafc;
}
.modern-table tbody td {
    padding: 15px;
    border: none;
    border-bottom: 1px solid #edf2f7;
    vertical-align: middle;
}
.order-id {
    font-weight: 600;
    color: #1a365d;
}
.price-cell {
    font-weight: 600;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.badge-default { background: #edf2f7; color: #718096; }
.badge-paid { background: #c6f6d5; color: #276749; }
.badge-unpaid { background: #fed7d7; color: #c53030; }
.badge-received { background: #fffff0; color: #d69e2e; }
.badge-cleaning { background: #f3e8ff; color: #805ad5; }
.badge-processing { background: #ebf8ff; color: #3182ce; }
.badge-transit { background: #e6fffa; color: #319795; }
.badge-delivered { background: #f0fff4; color: #38a169; }
</style>

<script>
function setDirection(type) {
    const directionSelect = document.querySelector('select[name="entry_direction"]');
    const inTypes = ['capital_injection', 'loan_received', 'profit_share', 'expense_reimbursement', 'other_in'];
    const outTypes = ['drawings', 'loan_given', 'salary', 'dividend', 'other_out'];
    
    if(inTypes.includes(type)) {
        directionSelect.value = 'in';
    } else if(outTypes.includes(type)) {
        directionSelect.value = 'out';
    }
}
</script>

<?php include('footer.php'); ?>
