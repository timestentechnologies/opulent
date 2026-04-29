<?php
require_once('session_handler.php');
?>
<?php
require_once('connect.php');

if (!isset($_SESSION["id"])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION["username"]) || $_SESSION["username"] !== 'admin') {
    header('Location: index.php');
    exit();
}

include('head.php');
include('header.php');
include('sidebar.php');

function generateInvoiceNumberFromTracking($trackingNumber, $orderId) {
    $trackingNumber = trim((string)$trackingNumber);
    if ($trackingNumber !== '') {
        return 'INV-' . preg_replace('/[^A-Za-z0-9\-]/', '', $trackingNumber);
    }
    return 'INV-ORD-' . (int)$orderId;
}

function getLastCustomerBalance($conn, $customerId) {
    $customerId = (int)$customerId;
    $sql = "SELECT running_balance FROM customer_statements WHERE customer_id = $customerId ORDER BY id DESC LIMIT 1";
    $res = $conn->query($sql);
    if ($res && $row = $res->fetch_assoc()) {
        return (float)$row['running_balance'];
    }
    return 0.0;
}

function statementExists($conn, $customerId, $referenceType, $referenceId, $transactionType) {
    $customerId = (int)$customerId;
    $referenceId = (int)$referenceId;
    $referenceType = mysqli_real_escape_string($conn, $referenceType);
    $transactionType = mysqli_real_escape_string($conn, $transactionType);
    $sql = "SELECT id FROM customer_statements WHERE customer_id = $customerId AND reference_type = '$referenceType' AND reference_id = $referenceId AND transaction_type = '$transactionType' LIMIT 1";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

function paymentExistsForOrder($conn, $orderId) {
    $orderId = (int)$orderId;
    $sql = "SELECT id FROM payments WHERE order_id = $orderId AND payment_type = 'customer_payment' LIMIT 1";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'preview';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

$ordersSql = "SELECT o.id, o.customer_id, o.price, o.created_at, o.delivery_date, o.payment_status, o.payment_method, o.mpesa_code, o.mpesa_number, o.tracking_number
            FROM `order` o
            WHERE o.customer_id IS NOT NULL
            ORDER BY o.id ASC";

if ($limit > 0) {
    $ordersSql .= " LIMIT $limit";
}

$ordersRes = $conn->query($ordersSql);
$orders = [];
if ($ordersRes) {
    while ($r = $ordersRes->fetch_assoc()) {
        $orders[] = $r;
    }
}

$preview = [
    'orders_total' => count($orders),
    'invoices_to_create' => 0,
    'payments_to_create' => 0,
    'stmt_debits_to_create' => 0,
    'stmt_credits_to_create' => 0
];

foreach ($orders as $o) {
    $orderId = (int)$o['id'];
    $customerId = (int)$o['customer_id'];
    $tracking = $o['tracking_number'];
    $invoiceNumber = generateInvoiceNumberFromTracking($tracking, $orderId);

    $invCheck = $conn->prepare("SELECT id FROM invoices WHERE order_id = ? OR invoice_number = ? LIMIT 1");
    $invCheck->bind_param('is', $orderId, $invoiceNumber);
    $invCheck->execute();
    $invRes = $invCheck->get_result();
    $invoiceExists = ($invRes && $invRes->num_rows > 0);

    if (!$invoiceExists) {
        $preview['invoices_to_create']++;
        $preview['stmt_debits_to_create']++;
    }

    if ($o['payment_status'] === 'paid') {
        if (!paymentExistsForOrder($conn, $orderId)) {
            $preview['payments_to_create']++;
        }
        // credit statement created when payment created; if payment exists but statement missing, we'll create it in run
        $preview['stmt_credits_to_create']++;
    }
}

$runResult = null;

if ($mode === 'run' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $created = [
        'invoices_created' => 0,
        'payments_created' => 0,
        'stmt_debits_created' => 0,
        'stmt_credits_created' => 0,
        'errors' => []
    ];

    $conn->begin_transaction();

    try {
        foreach ($orders as $o) {
            $orderId = (int)$o['id'];
            $customerId = (int)$o['customer_id'];
            $amount = (float)$o['price'];
            $tracking = $o['tracking_number'];
            $invoiceNumber = generateInvoiceNumberFromTracking($tracking, $orderId);

            // resolve invoice
            $invoiceId = null;
            $invCheck = $conn->prepare("SELECT id FROM invoices WHERE order_id = ? OR invoice_number = ? LIMIT 1");
            $invCheck->bind_param('is', $orderId, $invoiceNumber);
            $invCheck->execute();
            $invRes = $invCheck->get_result();
            if ($invRes && $row = $invRes->fetch_assoc()) {
                $invoiceId = (int)$row['id'];
            } else {
                $issueDate = date('Y-m-d', strtotime($o['created_at']));
                $dueDate = $o['delivery_date'] ? date('Y-m-d', strtotime($o['delivery_date'])) : $issueDate;

                $status = ($o['payment_status'] === 'paid') ? 'paid' : 'sent';
                $amountPaid = ($o['payment_status'] === 'paid') ? $amount : 0.0;
                $balanceDue = ($o['payment_status'] === 'paid') ? 0.0 : $amount;

                $notes = "Auto-generated from order #" . (string)$tracking;
                $terms = "";
                $createdBy = (int)$_SESSION['id'];

                $invInsert = $conn->prepare("INSERT INTO invoices (invoice_number, customer_id, order_id, issue_date, due_date, subtotal, tax_amount, discount_amount, total_amount, amount_paid, balance_due, status, notes, terms, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, 0.00, 0.00, ?, ?, ?, ?, ?, ?, ?)");
                $invInsert->bind_param(
                    'siissddddsssi',
                    $invoiceNumber,
                    $customerId,
                    $orderId,
                    $issueDate,
                    $dueDate,
                    $amount,
                    $amount,
                    $amountPaid,
                    $balanceDue,
                    $status,
                    $notes,
                    $terms,
                    $createdBy
                );
                if (!$invInsert->execute()) {
                    throw new Exception('Invoice insert failed for order ' . $orderId . ': ' . $invInsert->error);
                }

                $invoiceId = (int)$conn->insert_id;
                $created['invoices_created']++;

                // create invoice item (basic)
                $desc = 'Laundry Service (Order ' . ($tracking ? $tracking : $orderId) . ')';
                $qty = 1.00;
                $unit = $amount;
                $total = $amount;
                $itemInsert = $conn->prepare("INSERT INTO invoice_items (invoice_id, service_id, description, quantity, unit_price, total_price) VALUES (?, NULL, ?, ?, ?, ?)");
                $itemInsert->bind_param('isddd', $invoiceId, $desc, $qty, $unit, $total);
                $itemInsert->execute();

                // statement debit
                if (!statementExists($conn, $customerId, 'invoice', $invoiceId, 'invoice')) {
                    $prevBalance = getLastCustomerBalance($conn, $customerId);
                    $newBalance = $prevBalance + $amount;
                    $stmtDesc = 'Invoice ' . $invoiceNumber;
                    $stmtDate = $issueDate;

                    $stmtIns = $conn->prepare("INSERT INTO customer_statements (customer_id, transaction_type, reference_id, reference_type, description, debit_amount, credit_amount, running_balance, transaction_date)
                        VALUES (?, 'invoice', ?, 'invoice', ?, ?, 0.00, ?, ?)");
                    $stmtIns->bind_param('iisdds', $customerId, $invoiceId, $stmtDesc, $amount, $newBalance, $stmtDate);
                    if (!$stmtIns->execute()) {
                        throw new Exception('Statement debit insert failed for invoice ' . $invoiceId . ': ' . $stmtIns->error);
                    }
                    $created['stmt_debits_created']++;
                }
            }

            // paid order => payment + statement credit
            if ($o['payment_status'] === 'paid') {
                $paymentId = null;
                $payRes = $conn->query("SELECT id FROM payments WHERE order_id = $orderId AND payment_type = 'customer_payment' LIMIT 1");
                if ($payRes && $r = $payRes->fetch_assoc()) {
                    $paymentId = (int)$r['id'];
                } else {
                    $paymentMethod = $o['payment_method'] ? $o['payment_method'] : 'other';
                    $mpesaCode = $o['mpesa_code'] ? $o['mpesa_code'] : null;
                    $mpesaNumber = $o['mpesa_number'] ? (string)$o['mpesa_number'] : null;
                    $transRef = $tracking ? $tracking : null;
                    $desc = 'Payment for Order ' . ($tracking ? $tracking : $orderId);
                    $payDate = $o['delivery_date'] ? date('Y-m-d', strtotime($o['delivery_date'])) : date('Y-m-d');
                    $recordedBy = (int)$_SESSION['id'];

                    $payInsert = $conn->prepare("INSERT INTO payments (payment_type, customer_id, supplier_name, order_id, invoice_id, amount, payment_method, transaction_reference, mpesa_code, mpesa_number, description, payment_date, recorded_by)
                        VALUES ('customer_payment', ?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $payInsert->bind_param('iiidssssssi', $customerId, $orderId, $invoiceId, $amount, $paymentMethod, $transRef, $mpesaCode, $mpesaNumber, $desc, $payDate, $recordedBy);

                    if (!$payInsert->execute()) {
                        throw new Exception('Payment insert failed for order ' . $orderId . ': ' . $payInsert->error);
                    }

                    $paymentId = (int)$conn->insert_id;
                    $created['payments_created']++;
                }

                if ($paymentId && !statementExists($conn, $customerId, 'payment', $paymentId, 'payment')) {
                    $prevBalance = getLastCustomerBalance($conn, $customerId);
                    $newBalance = $prevBalance - $amount;
                    $stmtDesc = 'Payment for ' . $invoiceNumber;
                    $stmtDate = $o['delivery_date'] ? date('Y-m-d', strtotime($o['delivery_date'])) : date('Y-m-d');

                    $stmtIns = $conn->prepare("INSERT INTO customer_statements (customer_id, transaction_type, reference_id, reference_type, description, debit_amount, credit_amount, running_balance, transaction_date)
                        VALUES (?, 'payment', ?, 'payment', ?, 0.00, ?, ?, ?)");
                    $stmtIns->bind_param('iisdds', $customerId, $paymentId, $stmtDesc, $amount, $newBalance, $stmtDate);

                    if (!$stmtIns->execute()) {
                        throw new Exception('Statement credit insert failed for payment ' . $paymentId . ': ' . $stmtIns->error);
                    }
                    $created['stmt_credits_created']++;
                }

                // update invoice if exists
                if ($invoiceId) {
                    $conn->query("UPDATE invoices SET status='paid', amount_paid = total_amount, balance_due = 0.00 WHERE id = $invoiceId");
                }
            }
        }

        $conn->commit();
        $runResult = $created;
    } catch (Exception $e) {
        $conn->rollback();
        $runResult = $created;
        $runResult['errors'][] = $e->getMessage();
    }
}
?>

<!-- Page wrapper -->
<div class="page-wrapper">
    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-primary">Financial Backfill</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active">Financial Backfill</li>
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-warning">
                            This tool will create invoices, payments and customer statement entries from existing orders. It is designed to be safe to run multiple times.
                        </div>

                        <?php if ($runResult): ?>
                            <?php if (!empty($runResult['errors'])): ?>
                                <div class="alert alert-danger">
                                    <strong>Backfill stopped due to an error.</strong><br>
                                    <?php echo htmlspecialchars($runResult['errors'][0]); ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success">
                                    Backfill completed successfully.
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-3"><strong>Invoices created:</strong> <?php echo (int)$runResult['invoices_created']; ?></div>
                                <div class="col-md-3"><strong>Payments created:</strong> <?php echo (int)$runResult['payments_created']; ?></div>
                                <div class="col-md-3"><strong>Statement debits created:</strong> <?php echo (int)$runResult['stmt_debits_created']; ?></div>
                                <div class="col-md-3"><strong>Statement credits created:</strong> <?php echo (int)$runResult['stmt_credits_created']; ?></div>
                            </div>
                            <hr>
                        <?php endif; ?>

                        <h4>Preview</h4>
                        <div class="row">
                            <div class="col-md-3"><strong>Orders scanned:</strong> <?php echo (int)$preview['orders_total']; ?></div>
                            <div class="col-md-3"><strong>Invoices to create:</strong> <?php echo (int)$preview['invoices_to_create']; ?></div>
                            <div class="col-md-3"><strong>Payments to create (paid orders):</strong> <?php echo (int)$preview['payments_to_create']; ?></div>
                            <div class="col-md-3"><strong>Statement rows (approx):</strong> <?php echo (int)($preview['stmt_debits_to_create'] + $preview['stmt_credits_to_create']); ?></div>
                        </div>

                        <hr>

                        <form method="POST" action="financial_backfill.php?mode=run<?php echo $limit > 0 ? '&limit=' . (int)$limit : ''; ?>" onsubmit="return confirm('Run backfill now? This will write to the database.');">
                            <button type="submit" class="btn btn-danger">Run Backfill</button>
                            <a href="financial_backfill.php" class="btn btn-secondary">Refresh Preview</a>
                        </form>

                        <hr>

                        <form method="GET" action="financial_backfill.php" class="form-inline">
                            <label class="mr-2">Limit orders (0 = all):</label>
                            <input type="number" class="form-control mr-2" name="limit" value="<?php echo (int)$limit; ?>" min="0" step="1">
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
