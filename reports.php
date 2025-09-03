<?php
include('config.php');
requireAdminAuth();

// Handle report generation
$report_data = [];
$chart_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) 
{
    $start_date = $connect->real_escape_string($_POST['start_date']);
    $end_date = $connect->real_escape_string($_POST['end_date']);
    $report_type = $connect->real_escape_string($_POST['report_type']);
    $chart_type = $connect->real_escape_string($_POST['chart_type']);
    
    if ($report_type === 'sales') 
	{
        $sql = "SELECT rt.transaction_date, c.customer_name, c.customer_ic, 
                       GROUP_CONCAT(CONCAT(ti.weight, 'kg ', it.type_name) SEPARATOR ', ') as items,
                       SUM(ti.subtotal) as total_amount, SUM(ti.points_earned) as total_points
                FROM recycling_transactions rt
                JOIN customer c ON rt.customer_id = c.customer_id
                JOIN transaction_items ti ON rt.transaction_id = ti.transaction_id
                JOIN item_types it ON ti.type_id = it.type_id
                WHERE rt.transaction_date BETWEEN '$start_date' AND '$end_date'
                GROUP BY rt.transaction_id
                ORDER BY rt.transaction_date DESC";
        
        // Chart data for sales
        $chart_sql = "SELECT it.type_name, SUM(ti.weight) as total_weight, 
                             SUM(ti.subtotal) as total_value
                      FROM transaction_items ti
                      JOIN item_types it ON ti.type_id = it.type_id
                      JOIN recycling_transactions rt ON ti.transaction_id = rt.transaction_id
                      WHERE rt.transaction_date BETWEEN '$start_date' AND '$end_date'
                      GROUP BY it.type_name
                      ORDER BY total_value DESC";
    } 
	else 
	{ // customers report
        $sql = "SELECT c.customer_name, c.customer_ic, c.customer_phone, c.customer_points,
                       COUNT(rt.transaction_id) as total_transactions,
                       SUM(rt.total_amount) as total_spent,
                       MAX(rt.transaction_date) as last_transaction
                FROM customer c
                LEFT JOIN recycling_transactions rt ON c.customer_id = rt.customer_id
                WHERE (rt.transaction_date BETWEEN '$start_date' AND '$end_date' OR rt.transaction_date IS NULL)
                GROUP BY c.customer_id
                ORDER BY total_spent DESC";
        
        // Chart data for customers
        $chart_sql = "SELECT 
                        CASE 
                            WHEN total_spent IS NULL OR total_spent = 0 THEN 'No Transactions'
                            WHEN total_spent < 100 THEN 'Under RM100'
                            WHEN total_spent BETWEEN 100 AND 500 THEN 'RM100 - RM500'
                            WHEN total_spent BETWEEN 501 AND 1000 THEN 'RM501 - RM1000'
                            ELSE 'Over RM1000'
                        END as spending_range,
                        COUNT(*) as customer_count
                      FROM (
                          SELECT c.customer_id, 
                                 COALESCE(SUM(rt.total_amount), 0) as total_spent
                          FROM customer c
                          LEFT JOIN recycling_transactions rt ON c.customer_id = rt.customer_id
                          AND rt.transaction_date BETWEEN '$start_date' AND '$end_date'
                          GROUP BY c.customer_id
                      ) as customer_spending
                      GROUP BY spending_range
                      ORDER BY customer_count DESC";
    }
    
    // Get report data
    $result = $connect->query($sql);
    while ($row = $result->fetch_assoc()) 
	{
        $report_data[] = $row;
    }
    
    // Get chart data
    $chart_result = $connect->query($chart_sql);
    while ($row = $chart_result->fetch_assoc()) 
	{
        $chart_data[] = $row;
    }
}

// Get dashboard statistics
$stats = [];
$sql = "SELECT COUNT(*) as total_customers FROM customer";
$result = $connect->query($sql);
$stats['total_customers'] = $result->fetch_assoc()['total_customers'];

$sql = "SELECT COUNT(*) as total_transactions FROM recycling_transactions WHERE transaction_date = CURDATE()";
$result = $connect->query($sql);
$stats['today_transactions'] = $result->fetch_assoc()['total_transactions'];

$sql = "SELECT SUM(total_amount) as total_revenue FROM recycling_transactions WHERE transaction_date = CURDATE()";
$result = $connect->query($sql);
$stats['today_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

$sql = "SELECT SUM(customer_points) as total_points FROM customer";
$result = $connect->query($sql);
$stats['total_points'] = $result->fetch_assoc()['total_points'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Advanced Reports</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; }
    .btn-green { background-color: #16B981; }
    .btn-green:hover { background-color: #059669; }
    .btn-blue { background-color: #1E3A8A; }
    .btn-blue:hover { background-color: #1E40AF; }
    .btn-red { background-color: #EF4444; }
    .btn-red:hover { background-color: #DC2626; }
    .btn-yellow { background-color: #F59E0B; }
    .btn-yellow:hover { background-color: #D97706; }
    
    /* Dropdown menu styles */
    .dropdown { position: relative; display: inline-block; }
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        min-width: 200px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    .dropdown-content a {
        color: #374151;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
        transition: background-color 0.2s;
    }
    .dropdown-content a:hover {
        background-color: #f3f4f6;
    }
    .dropdown:hover .dropdown-content { display: block; }
    
    /* Print Styles */
    @media print {
        .no-print { display: none !important; }
        .printable-area { display: block !important; }
        body { font-size: 12pt; background: white; color: black; }
        .chart-container { page-break-inside: avoid; }
        .page-break { page-break-before: always; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f2f2f2 !important; }
        canvas { max-width: 100% !important; height: auto !important; }
    }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8 no-print">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- Placeholder for logo -->
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500">AMeRT - Advanced Reports</h1>
            </a>
            <button onclick="toggleMenu()" class="md:hidden p-2 rounded-lg hover:bg-green-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16 M4 12h16 M4 18h16"></path>
                </svg>
            </button>
        </div>
        <nav id="mobile-menu" class="mobile-menu md:ml-4 w-full md:w-auto mt-4 md:mt-0 hidden md:block">
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 w-full">
                <a href="index.php" class="nav-link px-4 py-2 rounded-lg <?php echo isActivePage('index.php'); ?>">Home</a>
                <a href="staff.php" class="nav-link px-4 py-2 rounded-lg <?php echo isActivePage('staff.php'); ?>">Staff Portal</a>
                <a href="customer.php" class="nav-link px-4 py-2 rounded-lg <?php echo isActivePage('customer.php'); ?>">Customer Portal</a>
                <a href="admin.php" class="nav-link px-4 py-2 rounded-lg <?php echo isActivePage('admin.php'); ?>">Admin Dashboard</a>
                <!--<a href="reports.php" class="nav-link px-4 py-2 rounded-lg bg-blue-100 text-blue-800 font-semibold">Advanced Reports</a>-->
                
                <!-- Admin Dropdown Menu -->
                <div class="dropdown">
                    <a href="#" class="nav-link px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold flex items-center">
                        Admin <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="edit_profile.php" class="px-4 py-2">Edit Profile</a>
						<a href="reset_password.php" class="px-4 py-2">Reset Password</a>
                        <a href="reports.php" class="px-4 py-2 bg-blue-100 text-blue-800 font-semibold">Advanced Reports</a>
                        <a href="logout.php" class="px-4 py-2 text-red-600 font-semibold">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="space-y-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg no-print">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Generate Advanced Reports</h2>
            
            <form method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select id="report_type" name="report_type" class="w-full p-3 border border-gray-300 rounded-lg" required>
                        <option value="sales">Sales Transactions</option>
                        <option value="customers">Customer Summary</option>
                    </select>
                </div>
                
                <div>
                    <label for="chart_type" class="block text-sm font-medium text-gray-700 mb-1">Chart Type</label>
                    <select id="chart_type" name="chart_type" class="w-full p-3 border border-gray-300 rounded-lg" required>
                        <option value="bar">Bar Chart</option>
                        <option value="pie">Pie Chart</option>
                        <option value="line">Line Chart</option>
                    </select>
                </div>
                
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <button type="submit" name="generate_report" class="btn-blue text-white font-bold py-3 px-6 rounded-lg transition">Generate Report</button>
            </form>
        </div>

        <?php if (!empty($report_data) && !empty($chart_data)): ?>
        <div class="printable-area">
            <!-- Report Header -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-blue-800 mb-2">
                            <?php echo ucfirst($report_type); ?> Report
                        </h2>
                        <p class="text-gray-600">Date Range: <?php echo $start_date . ' to ' . $end_date; ?></p>
                        <p class="text-gray-600">Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    <div class="no-print space-x-2">
                        <button onclick="window.print()" class="btn-blue text-white font-bold py-2 px-4 rounded-lg">
                            <i class="fa-solid fa-print mr-2"></i> Print
                        </button>
                        <a href="generate_pdf.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_type=<?php echo $report_type; ?>" 
                           class="btn-red text-white font-bold py-2 px-4 rounded-lg">
                            <i class="fa-solid fa-file-pdf mr-2"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="bg-white p-6 rounded-xl shadow-lg mb-6 chart-container">
                <h3 class="text-xl font-semibold mb-4">Data Visualization</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <canvas id="chartCanvas" height="300"></canvas>
                    </div>
                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="font-semibold mb-3">Chart Data</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <?php foreach (array_keys($chart_data[0]) as $column): ?>
                                            <th class="py-2 px-4"><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($chart_data as $row): ?>
                                        <tr class="border-b">
                                            <?php foreach ($row as $value): ?>
                                            <td class="py-2 px-4"><?php echo $value; ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Data Table -->
            <div class="bg-white p-6 rounded-xl shadow-lg chart-container">
                <h3 class="text-xl font-semibold mb-4">Detailed Report Data</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <?php foreach (array_keys($report_data[0]) as $column): ?>
                                <th class="py-2 px-4"><?php echo ucwords(str_replace('_', ' ', $column)); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): ?>
                            <tr class="border-b">
                                <?php foreach ($row as $value): ?>
                                <td class="py-2 px-4"><?php echo $value; ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- JavaScript for Chart Rendering -->
        <script>
        document.addEventListener('DOMContentLoaded', function() 
		{
            const chartType = '<?php echo $_POST["chart_type"]; ?>';
            const reportType = '<?php echo $_POST["report_type"]; ?>';
            const chartData = <?php echo json_encode($chart_data); ?>;
            
            let labels = [];
            let data = [];
            let backgroundColor = [];
            
            // Prepare chart data based on report type
            if (reportType === 'sales') 
			{
                chartData.forEach(item => 
				{
                    labels.push(item.type_name);
                    data.push(parseFloat(item.total_value));
                    backgroundColor.push(getRandomColor());
                });
            } 
			else 
			{
                chartData.forEach(item => 
				{
                    labels.push(item.spending_range);
                    data.push(parseInt(item.customer_count));
                    backgroundColor.push(getRandomColor());
                });
            }
            
            // Create chart
            const ctx = document.getElementById('chartCanvas').getContext('2d');
            const chart = new Chart(ctx, 
			{
                type: chartType,
                data: {
                    labels: labels,
                    datasets: 
					[{
                        label: reportType === 'sales' ? 'Sales Value (RM)' : 'Number of Customers',
                        data: data,
                        backgroundColor: backgroundColor,
                        borderColor: backgroundColor.map(color => color.replace('0.6', '1')),
                        borderWidth: 1
                    }]
                },
                options:
				{
                    responsive: true,
                    plugins: 
					{
                        title: 
						{
                            display: true,
                            text: reportType === 'sales' ? 'Sales by Material Type' : 'Customers by Spending Range'
                        },
                        legend: 
						{
                            position: 'right',
                        }
                    }
                }
            });
            
            // Function to generate random colors
            function getRandomColor() 
			{
                const colors = 
				[
                    'rgba(54, 162, 235, 0.6)', // Blue
                    'rgba(255, 99, 132, 0.6)', // Red
                    'rgba(75, 192, 192, 0.6)', // Green
                    'rgba(255, 159, 64, 0.6)', // Orange
                    'rgba(153, 102, 255, 0.6)', // Purple
                    'rgba(255, 205, 86, 0.6)', // Yellow
                    'rgba(201, 203, 207, 0.6)' , // Grey
					'rgba(0, 206, 86, 0.6)',     // Emerald
					'rgba(255, 140, 0, 0.6)',    // Dark Orange
					'rgba(106, 90, 205, 0.6)',   // Slate Blue
					'rgba(220, 20, 60, 0.6)',    // Crimson
					'rgba(50, 205, 50, 0.6)',    // Lime Green
					'rgba(255, 69, 0, 0.6)',     // Red-Orange
					'rgba(32, 178, 170, 0.6)',   // Light Sea Green
					'rgba(218, 112, 214, 0.6)',  // Orchid
					'rgba(255, 215, 0, 0.6)',    // Gold
					'rgba(70, 130, 180, 0.6)',   // Steel Blue
					'rgba(139, 69, 19, 0.6)' ,   // Saddle Brown
					'rgba(199, 21, 133, 0.6)',   // Medium Violet Red
					'rgba(60, 179, 113, 0.6)',   // Medium Sea Green
					'rgba(123, 104, 238, 0.6)',  // Medium Slate Blue
					'rgba(244, 164, 96, 0.6)',   // Sandy Brown
					'rgba(127, 255, 212, 0.6)',  // Aquamarine
					'rgba(205, 92, 92, 0.6)',    // Indian Red
					'rgba(72, 209, 204, 0.6)',    // Medium Turquoise
					
					// add more color in case admin decide to add more accepted product into the system
					'rgba(176, 196, 222, 0.6)',  // Light Steel Blue
					'rgba(0, 191, 255, 0.6)',    // Deep Sky Blue
					'rgba(255, 20, 147, 0.6)',   // Deep Pink
					'rgba(124, 252, 0, 0.6)',    // Lawn Green
					'rgba(210, 105, 30, 0.6)',   // Chocolate
					'rgba(147, 112, 219, 0.6)',  // Medium Purple
					'rgba(30, 144, 255, 0.6)',   // Dodger Blue
					'rgba(233, 150, 122, 0.6)',  // Dark Salmon
					'rgba(100, 149, 237, 0.6)',  // Cornflower Blue
					'rgba(46, 139, 87, 0.6)',    // Sea Green
					'rgba(255, 228, 181, 0.6)',  // Moccasin
					'rgba(70, 130, 90, 0.6)',    // Dark Greenish
					'rgba(238, 130, 238, 0.6)',  // Violet
					'rgba(205, 133, 63, 0.6)',   // Peru
					'rgba(176, 224, 230, 0.6)',  // Powder Blue
					'rgba(199, 21, 21, 0.6)',    // Firebrick
					'rgba(60, 179, 200, 0.6)',   // Light Teal
					'rgba(244, 164, 200, 0.6)',  // Light Pinkish Brown
					'rgba(127, 255, 0, 0.6)',    // Chartreuse
					'rgba(0, 128, 128, 0.6)',    // Teal
					'rgba(255, 160, 122, 0.6)',  // Light Salmon
					'rgba(34, 139, 34, 0.6)',    // Forest Green
					'rgba(219, 112, 147, 0.6)',  // Pale Violet Red
					'rgba(25, 25, 112, 0.6)',    // Midnight Blue
					'rgba(255, 250, 205, 0.6)'   // Lemon Chiffon
                ];
                return colors[Math.floor(Math.random() * colors.length)];
            }
        });
        </script>
        <?php endif; ?>
    </main>
</div>

<script>
function toggleMenu() 
{
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
    menu.classList.toggle('block');
}

// Set default dates for report generation
document.getElementById('start_date').valueAsDate = new Date();
document.getElementById('end_date').valueAsDate = new Date();
</script>
</body>
</html>