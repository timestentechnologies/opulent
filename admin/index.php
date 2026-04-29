<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Include session config first
require_once('session_handler.php');

// Include auto email checker to ensure notifications are processed
require_once('auto_check_emails.php');

// Verify login status
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

include('head.php');
include('header.php');
include('connect.php');
include('sidebar.php');

// Rest of your existing code
date_default_timezone_set('Asia/Kolkata');
$current_date = date('Y-m-d');

$sql_currency = "select * from manage_website"; 
$result_currency = $conn->query($sql_currency);
$row_currency = mysqli_fetch_array($result_currency);
?>    
        <!-- Page wrapper  -->
        <div class="page-wrapper">
            
            <!-- Bread crumb -->
            <div class="row page-titles">
                <div class="col-md-5 align-self-center">
                    <h3 class="text-primary">Owner Dashboard</h3> 
                </div>
                <div class="col-md-7 align-self-center">
                    <ol class="breadcrumb">
                        <marquee scrollamount=4><b>Alert : Make sure to check the orders and update the status as needed</b></marquee>
                    </ol>
                </div>
            </div>
            <!-- End Bread crumb -->
            <!-- Container fluid  -->
            <div class="container-fluid">
                <!-- Start Page Content -->
                
                <!-- Dashboard Stats Cards -->
                <div class="row">
                    <?php
                    // Get all stats in one go
                    $today_orders = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['total'];
                    $in_progress = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE status IN ('received', 'cleaning', 'processing')")->fetch_assoc()['total'];
                    $completed = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE status = 'delivered'")->fetch_assoc()['total'];
                    $total_revenue = $conn->query("SELECT COALESCE(SUM(price), 0) as total FROM `order` WHERE payment_status = 'paid'")->fetch_assoc()['total'];
                    $total_customers = $conn->query("SELECT COUNT(*) as total FROM `customer`")->fetch_assoc()['total'];
                    $pending_payment = $conn->query("SELECT COALESCE(SUM(price), 0) as total FROM `order` WHERE payment_status = 'pending'")->fetch_assoc()['total'];
                    
                    // Status breakdown for pie chart
                    $status_counts = [];
                    $status_result = $conn->query("SELECT status, COUNT(*) as count FROM `order` GROUP BY status");
                    while($row = $status_result->fetch_assoc()) {
                        $status_counts[$row['status']] = $row['count'];
                    }
                    
                    // Weekly orders for bar chart
                    $weekly_data = [];
                    for($i = 6; $i >= 0; $i--) {
                        $date = date('Y-m-d', strtotime("-$i days"));
                        $count = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE DATE(created_at) = '$date'")->fetch_assoc()['total'];
                        $weekly_data[date('D', strtotime($date))] = $count;
                    }
                    ?>
                    
                    <!-- New Orders Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-navy">
                            <div class="card-icon"><i class="ti-bag"></i></div>
                            <div class="card-info">
                                <h3><?php echo $today_orders; ?></h3>
                                <p>New Orders</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- In Progress Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-orange">
                            <div class="card-icon"><i class="ti-reload"></i></div>
                            <div class="card-info">
                                <h3><?php echo $in_progress; ?></h3>
                                <p>In Progress</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Completed Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-navy">
                            <div class="card-icon"><i class="ti-check-box"></i></div>
                            <div class="card-info">
                                <h3><?php echo $completed; ?></h3>
                                <p>Completed</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Revenue Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-orange">
                            <div class="card-icon"><i class="ti-money"></i></div>
                            <div class="card-info">
                                <h3>Ksh<?php echo number_format($total_revenue, 0); ?></h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Total Customers Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-navy">
                            <div class="card-icon"><i class="ti-user"></i></div>
                            <div class="card-info">
                                <h3><?php echo $total_customers; ?></h3>
                                <p>Customers</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pending Payment Card -->
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card card-orange">
                            <div class="card-icon"><i class="ti-wallet"></i></div>
                            <div class="card-info">
                                <h3>Ksh<?php echo number_format($pending_payment, 0); ?></h3>
                                <p>Pending Payment</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row m-t-30">
                    <!-- Pie Chart - Order Status -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h4><i class="ti-pie-chart"></i> Order Status Distribution</h4>
                            </div>
                            <div class="chart-body">
                                <canvas id="orderStatusPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bar Chart - Weekly Orders -->
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h4><i class="ti-bar-chart"></i> Weekly Orders Trend</h4>
                            </div>
                            <div class="chart-body">
                                <canvas id="weeklyOrdersBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table Section -->
                <div class="row m-t-30">
                    <div class="col-12">
                        <div class="table-card">
                            <div class="table-header">
                                <h4><i class="ti-list"></i> Recent Orders</h4>
                                <a href="view_order.php" class="btn-view-all">View All</a>
                            </div>
                            <div class="table-body">
                                <div class="table-responsive">
                                    <table id="myTable" class="table modern-table">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Service</th>
                                                <th>Description</th>
                                                <th>Price</th>
                                                <th>Delivery</th>
                                                <th>Pickup</th>
                                                <th>Status</th>
                                                <th>Payment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $sql = "SELECT o.*, c.fname as customer_name, c.lname as customer_lname, s.sname as service_name 
                                                   FROM `order` o 
                                                   JOIN `customer` c ON o.customer_id = c.id 
                                                   JOIN `service` s ON o.service_id = s.id 
                                                   ORDER BY o.created_at DESC LIMIT 10";
                                            $result = $conn->query($sql);

                                            while($row = $result->fetch_assoc()) {
                                                // Status badge class
                                                $status_class = '';
                                                switch($row['status']) {
                                                    case 'received': $status_class = 'badge-received'; break;
                                                    case 'cleaning': $status_class = 'badge-cleaning'; break;
                                                    case 'processing': $status_class = 'badge-processing'; break;
                                                    case 'in_transit': $status_class = 'badge-transit'; break;
                                                    case 'delivered': $status_class = 'badge-delivered'; break;
                                                    default: $status_class = 'badge-default';
                                                }
                                                
                                                // Payment badge class
                                                $payment_class = $row['payment_status'] == 'paid' ? 'badge-paid' : 'badge-unpaid';
                                            ?>
                                            <tr>
                                                <td class="order-id"><?php echo htmlspecialchars($row['tracking_number'] ?: 'N/A'); ?></td>
                                                <td class="customer-name"><?php echo htmlspecialchars($row['customer_name'] . ' ' . $row['customer_lname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                                                <td class="description-cell" title="<?php echo htmlspecialchars($row['description']); ?>"><?php echo htmlspecialchars(strlen($row['description']) > 30 ? substr($row['description'], 0, 30) . '...' : $row['description']); ?></td>
                                                <td class="price-cell">Ksh<?php echo number_format($row['price'], 2); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['delivery_date'])); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['pickup_date'])); ?></td>
                                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                                                <td><span class="status-badge <?php echo $payment_class; ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
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
            <!-- End Container fluid  -->
            
            <!-- Dashboard Styles -->
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
                
                /* Chart Cards */
                .chart-card {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                    margin-bottom: 20px;
                }
                .chart-header {
                    padding: 20px 25px;
                    border-bottom: 1px solid #edf2f7;
                }
                .chart-header h4 {
                    margin: 0;
                    font-size: 16px;
                    color: #2d3748;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .chart-header i {
                    color: #667eea;
                }
                .chart-body {
                    padding: 25px;
                    height: 300px;
                }
                
                /* Table Card */
                .table-card {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                    overflow: hidden;
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
                    color: #667eea;
                }
                .btn-view-all {
                    background: #1a365d;
                    color: #fff;
                    padding: 8px 20px;
                    border-radius: 20px;
                    font-size: 13px;
                    text-decoration: none;
                    transition: background 0.2s;
                }
                .btn-view-all:hover {
                    background: #2c5282;
                    color: #fff;
                    text-decoration: none;
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
                    color: #667eea;
                }
                .customer-name {
                    font-weight: 500;
                    color: #2d3748;
                }
                .price-cell {
                    font-weight: 600;
                    color: #38a169;
                }
                .description-cell {
                    color: #718096;
                    max-width: 200px;
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
                .badge-received { background: #ebf8ff; color: #3182ce; }
                .badge-cleaning { background: #fffff0; color: #d69e2e; }
                .badge-processing { background: #f3e8ff; color: #805ad5; }
                .badge-transit { background: #e6fffa; color: #319795; }
                .badge-delivered { background: #f0fff4; color: #38a169; }
                .badge-default { background: #edf2f7; color: #718096; }
                .badge-paid { background: #c6f6d5; color: #276749; }
                .badge-unpaid { background: #fed7d7; color: #c53030; }
                
                @media (max-width: 768px) {
                    .dashboard-card { margin-bottom: 15px; }
                    .chart-body { height: 250px; padding: 15px; }
                }
            </style>
            
            <!-- Chart Scripts -->
            <script>
                // Order Status Pie Chart
                var ctxPie = document.getElementById("orderStatusPieChart").getContext('2d');
                var orderStatusPieChart = new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ['Received', 'Cleaning', 'Processing', 'In Transit', 'Delivered'],
                        datasets: [{
                            data: [
                                <?php echo $status_counts['received'] ?? 0; ?>,
                                <?php echo $status_counts['cleaning'] ?? 0; ?>,
                                <?php echo $status_counts['processing'] ?? 0; ?>,
                                <?php echo $status_counts['in_transit'] ?? 0; ?>,
                                <?php echo $status_counts['delivered'] ?? 0; ?>
                            ],
                            backgroundColor: [
                                'rgba(102, 126, 234, 0.8)',
                                'rgba(240, 147, 251, 0.8)',
                                'rgba(128, 90, 213, 0.8)',
                                'rgba(56, 178, 172, 0.8)',
                                'rgba(72, 187, 120, 0.8)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        cutoutPercentage: 60
                    }
                });
                
                // Weekly Orders Bar Chart
                var ctxBar = document.getElementById("weeklyOrdersBarChart").getContext('2d');
                var weeklyOrdersBarChart = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_keys($weekly_data)); ?>,
                        datasets: [{
                            label: 'Orders',
                            data: <?php echo json_encode(array_values($weekly_data)); ?>,
                            backgroundColor: 'rgba(102, 126, 234, 0.8)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 0,
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { display: false },
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    stepSize: 1
                                },
                                gridLines: {
                                    color: 'rgba(0,0,0,0.05)',
                                    drawBorder: false
                                }
                            }],
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                    drawBorder: false
                                }
                            }]
                        }
                    }
                });
            </script>
            <?php include('footer.php');?>

