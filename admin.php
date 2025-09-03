<?php
include('config.php');
requireAdminAuth();

// [Previous PHP code remains unchanged until the HTML section]
// Handle report generation
$report_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_report'])) 
{
    $start_date = $connect->real_escape_string($_POST['start_date']);
    $end_date = $connect->real_escape_string($_POST['end_date']);
    $report_type = $connect->real_escape_string($_POST['report_type']);
    
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
    }
    
    $result = $connect->query($sql);
    while ($row = $result->fetch_assoc()) 
	{
        $report_data[] = $row;
    }
}

// Get dashboard statistics
$stats = []; // declare skali je, and used by all xde data dalam tu. just a reserve place

// get total customer from database
$sql = "SELECT COUNT(*) as total_customers FROM customer";
$result = $connect->query($sql);
$stats['total_customers'] = $result->fetch_assoc()['total_customers'];

// get total product from database
$sql = "SELECT COUNT(*) as total_product FROM item_types";
$result = $connect->query($sql);
$stats['total_product'] = $result->fetch_assoc()['total_product'];

//get total staff from database
$sql = "SELECT COUNT(*) as total_staff FROM staff";
$result = $connect->query($sql);
$stats['total_staff'] = $result->fetch_assoc()['total_staff'];


$sql = "SELECT COUNT(*) as total_transactions FROM recycling_transactions WHERE transaction_date = CURDATE()";
$result = $connect->query($sql);
$stats['today_transactions'] = $result->fetch_assoc()['total_transactions'];

$sql = "SELECT SUM(total_amount) as total_revenue FROM recycling_transactions WHERE transaction_date = CURDATE()";
$result = $connect->query($sql);
$stats['today_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;

$sql = "SELECT SUM(customer_points) as total_points FROM customer";
$result = $connect->query($sql);
$stats['total_points'] = $result->fetch_assoc()['total_points'] ?? 0;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    // Create Customer
    if (isset($_POST['create_customer'])) 
	{
        $name = $connect->real_escape_string($_POST['customer_name']);
        $ic = $connect->real_escape_string($_POST['customer_ic']);
        $phone = $connect->real_escape_string($_POST['customer_phone']);
        
        $sql = "INSERT INTO customer (customer_name, customer_ic, customer_phone) 
                VALUES ('$name', '$ic', '$phone')";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Customer created successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error creating customer: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Update Customer
    if (isset($_POST['update_customer'])) 
	{
        $id = $connect->real_escape_string($_POST['customer_id']);
        $name = $connect->real_escape_string($_POST['customer_name']);
        $ic = $connect->real_escape_string($_POST['customer_ic']);
        $phone = $connect->real_escape_string($_POST['customer_phone']);
        
        $sql = "UPDATE customer SET customer_name='$name', customer_ic='$ic', customer_phone='$phone' 
                WHERE customer_id=$id";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Customer updated successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error updating customer: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Delete Customer
    if (isset($_POST['delete_customer']))		
	{
        $id = $connect->real_escape_string($_POST['customer_id']);
        
        $sql = "DELETE FROM customer WHERE customer_id=$id";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Customer deleted successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error deleting customer: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Create Staff
    if (isset($_POST['create_staff']))
	{
        $name = $connect->real_escape_string($_POST['staff_name']);
        $dept = $connect->real_escape_string($_POST['staff_dept']);
        
        $sql = "INSERT INTO staff (staff_name, staff_dept) VALUES ('$name', '$dept')";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Staff created successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error creating staff: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Update Staff
    if (isset($_POST['update_staff'])) 
	{
        $id = $connect->real_escape_string($_POST['staff_id']);
        $name = $connect->real_escape_string($_POST['staff_name']);
        $dept = $connect->real_escape_string($_POST['staff_dept']);
        
        $sql = "UPDATE staff SET staff_name='$name', staff_dept='$dept' WHERE staff_id=$id";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Staff updated successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error updating staff: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Delete Staff
    if (isset($_POST['delete_staff'])) 
	{
        $id = $connect->real_escape_string($_POST['staff_id']);
        
        $sql = "DELETE FROM staff WHERE staff_id=$id";
        
        if ($connect->query($sql))
		{
            $_SESSION['message'] = 'Staff deleted successfully!';
        } else {
            $_SESSION['message'] = 'Error deleting staff: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
	
	// Create Product
    if (isset($_POST['create_product'])) 
	{
        $type = $connect->real_escape_string($_POST['type_name']);
        $price = $connect->real_escape_string($_POST['price_per_kg']);
        
        $sql = "INSERT INTO item_types (type_name, price_per_kg) 
                VALUES ('$type', '$price')";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Product created successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error creating product: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Update Product
    if (isset($_POST['update_product'])) 
	{
        $id = $connect->real_escape_string($_POST['type_id']);
        $type = $connect->real_escape_string($_POST['type_name']);
        $price = $connect->real_escape_string($_POST['price_per_kg']);
        
        $sql = "UPDATE item_types SET type_name='$type', price_per_kg='$price'
                WHERE type_id=$id";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Product updated successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error updating product: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
    
    // Delete Product
    if (isset($_POST['delete_product']))		
	{
        $id = $connect->real_escape_string($_POST['type_id']);
        
        $sql = "DELETE FROM item_types WHERE type_id=$id";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Product deleted successfully!';
        } 
		else 
		{
            $_SESSION['message'] = 'Error deleting product: ' . $connect->error;
        }
        header('Location: admin.php');
        exit();
    }
}

// Get all CUSTOMERS for CRUD operations
$customers = [];
$sql = "SELECT * FROM customer ORDER BY customer_name";
$result = $connect->query($sql);
while ($row = $result->fetch_assoc()) 
{
    $customers[] = $row;
}

// Get all STAFF for CRUD operations
$staff_members = [];
$sql = "SELECT * FROM staff ORDER BY staff_name";
$result = $connect->query($sql);
while ($row = $result->fetch_assoc())
{
    $staff_members[] = $row;
}

// Get all PRODUCT for CRUD operations (trial)
$product_list = [];
$sql = "SELECT * FROM item_types ORDER BY type_name";
$result = $connect->query($sql);
while ($row = $result->fetch_assoc())
{
    $product_list[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Admin Dashboard</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    @media print 
    {
        .no-print { display: none !important; }
        .printable-area { display: block !important; }
        body { font-size: 12pt; background: white; color: black; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f2f2f2 !important; }
    }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- space for logo -->
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500">AMeRT - Admin Dashboard</h1>
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
                
                <!-- Admin Dropdown Menu -->
                <div class="dropdown">
                    <a href="#" class="nav-link px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold flex items-center">
                        Admin <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="edit_profile.php" class="px-4 py-2">Edit Profile</a>
						<a href="reset_password.php" class="px-4 py-2">Reset Password</a>
                        <a href="reports.php" class="px-4 py-2">Advanced Reports</a>
                        <a href="logout.php" class="px-4 py-2 text-red-600 font-semibold">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- [Rest of the content remains unchanged] -->
	    <main class="space-y-8">
         <?php if (isset($_SESSION['message']) &&  $_SESSION['message']): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Dashboard Overview</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6 text-center">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-700">Total Customers</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2"><?php echo $stats['total_customers']; ?></p>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-700">Today's Transactions</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2"><?php echo $stats['today_transactions']; ?></p>
                </div>
                
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-700">Today's Revenue</h3>
                    <p class="text-3xl font-bold text-yellow-600 mt-2"><?php echo formatCurrency($stats['today_revenue']); ?></p>
                </div>
                <!--
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-700">Total Points</h3>
                    <p class="text-3xl font-bold text-purple-600 mt-2"><?php echo $stats['total_points']; ?></p>
                </div>
				-->
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Generate Reports</h2>
            
            <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select id="report_type" name="report_type" class="w-full p-3 border border-gray-300 rounded-lg" required>
                        <option value="sales">Sales Transactions</option>
                        <option value="customers">Customer Summary</option>
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
            
            <?php if (!empty($report_data)): ?>
            <div class="mt-6 printable-area">
                <div class="flex justify-between items-center mb-4 no-print">
                    <h3 class="text-xl font-semibold">Report Results</h3>
                    <div class="space-x-2">
                        <button onclick="window.print()" class="btn-blue text-white font-bold py-2 px-4 rounded-lg">
                            <i class="fa-solid fa-print mr-2"></i> Print
                        </button>
                        <a href="generate_pdf.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&report_type=<?php echo $report_type; ?>" 
                           class="btn-red text-white font-bold py-2 px-4 rounded-lg">
                            <i class="fa-solid fa-file-pdf mr-2"></i> Export PDF
                        </a>
                    </div>
                </div>
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
                
                <!-- Report Metadata -->
                <div class="mt-4 text-sm text-gray-600 no-print">
                    <p>Report generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
                    <p>Report type: <?php echo ucfirst($report_type); ?></p>
                    <p>Date range: <?php echo $start_date . ' to ' . $end_date; ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Customer Management Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Customer Management</h2>
            
            <!-- Create Customer Form -->
            <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-700 mb-3">Add New Customer</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="customer_name" name="customer_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label for="customer_ic" class="block text-sm font-medium text-gray-700 mb-1">IC Number</label>
                        <input type="text" id="customer_ic" name="customer_ic" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="text" id="customer_phone" name="customer_phone" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" name="create_customer" class="w-full btn-green text-white font-bold py-3 px-6 rounded-lg transition">Add Customer</button>
                    </div>
                </form>
            </div>
            
            <!-- Customers Table -->
            <h3 class="text-xl font-semibold mb-3">All Customers: <u><?php echo $stats['total_customers']; ?></u></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4">ID</th>
                            <th class="py-2 px-4">Name</th>
                            <th class="py-2 px-4">IC Number</th>
                            <th class="py-2 px-4">Phone</th>
                            <!--<th class="py-2 px-4">Points</th>-->
                            <th class="py-2 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?php echo $customer['customer_id']; ?></td>
                            <td class="py-2 px-4"><?php echo $customer['customer_name']; ?></td>
                            <td class="py-2 px-4"><?php echo $customer['customer_ic']; ?></td>
                            <td class="py-2 px-4"><?php echo $customer['customer_phone']; ?></td>
                            <!--<td class="py-2 px-4"><?php echo $customer['customer_points']; ?></td>-->
                            <td class="py-2 px-4">
                                <div class="flex space-x-2">
                                    <!-- Edit Button -->
                                    <button onclick="openEditCustomerModal(<?php echo $customer['customer_id']; ?>, '<?php echo $customer['customer_name']; ?>', '<?php echo $customer['customer_ic']; ?>', '<?php echo $customer['customer_phone']; ?>')" class="btn-yellow text-white font-bold py-1 px-3 rounded text-sm">Edit</button>
                                    
                                    <!-- Delete Form -->
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                        <input type="hidden" name="customer_id" value="<?php echo $customer['customer_id']; ?>">
                                        <button type="submit" name="delete_customer" class="btn-red text-white font-bold py-1 px-3 rounded text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Staff Management Section -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Staff Management</h2>
            
            <!-- Create Staff Form -->
            <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-700 mb-3">Add New Staff</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="staff_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="staff_name" name="staff_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label for="staff_dept" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" id="staff_dept" name="staff_dept" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" name="create_staff" class="w-full btn-green text-white font-bold py-3 px-6 rounded-lg transition">Add Staff</button>
                    </div>
                </form>
            </div>
            
            <!-- Staff Table -->
            <h3 class="text-xl font-semibold mb-3">All Staff Members: <u><?php echo $stats['total_staff']; ?></u></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4">ID</th>
                            <th class="py-2 px-4">Name</th>
                            <th class="py-2 px-4">Department</th>
                            <th class="py-2 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staff_members as $staff): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?php echo $staff['staff_id']; ?></td>
                            <td class="py-2 px-4"><?php echo $staff['staff_name']; ?></td>
                            <td class="py-2 px-4"><?php echo $staff['staff_dept']; ?></td>
                            <td class="py-2 px-4">
                                <div class="flex space-x-2">
                                    <!-- Edit Button -->
                                    <button onclick="openEditStaffModal(<?php echo $staff['staff_id']; ?>, '<?php echo $staff['staff_name']; ?>', '<?php echo $staff['staff_dept']; ?>')" class="btn-yellow text-white font-bold py-1 px-3 rounded text-sm">Edit</button>
                                    
                                    <!-- Delete Form -->
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                        <input type="hidden" name="staff_id" value="<?php echo $staff['staff_id']; ?>">
                                        <button type="submit" name="delete_staff" class="btn-red text-white font-bold py-1 px-3 rounded text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
		
		 <!-- product management section -->
		<div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Product Management</h2>
            
            <!-- create product form -->
            <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-700 mb-3">Add New Product</h3>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="type_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="type_name" name="type_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label for="price_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Price/Kg</label>
                        <input type="number" id="price_per_kg" name="price_per_kg" class="w-full p-3 border border-gray-300 rounded-lg" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" name="create_product" class="w-full btn-green text-white font-bold py-3 px-6 rounded-lg transition">Add Product</button>
                    </div>
                </form>
            </div>
            
            <!-- Product Table -->
            <h3 class="text-xl font-semibold mb-3">All Product: <u><?php echo $stats['total_product']; ?></u></h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-4">ID</th>
                            <th class="py-2 px-4">Product</th>
                            <th class="py-2 px-4">Price</th>
                            <th class="py-2 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product_list as $product): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?php echo $product['type_id']; ?></td>
                            <td class="py-2 px-4"><?php echo $product['type_name']; ?></td>
                            <td class="py-2 px-4"><?php echo $product['price_per_kg']; ?></td>
                            <td class="py-2 px-4">
                                <div class="flex space-x-2">
                                    <!-- Edit Button -->
									<button onclick="openEditProductModal(<?php echo $product['type_id']; ?>, '<?php echo $product['type_name']; ?>', '<?php echo $product['price_per_kg']; ?>')" class="btn-yellow text-white font-bold py-1 px-3 rounded text-sm">Edit</button>
                                    
                                    <!-- Delete Form -->
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="type_id" value="<?php echo $product['type_id']; ?>">
                                        <button type="submit" name="delete_product" class="btn-red text-white font-bold py-1 px-3 rounded text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- Edit Customer Modal -->
<div id="editCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Edit Customer</h3>
        <form method="POST" id="editCustomerForm">
            <input type="hidden" id="edit_customer_id" name="customer_id">
            
            <div class="mb-4">
                <label for="edit_customer_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="edit_customer_name" name="customer_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="mb-4">
                <label for="edit_customer_ic" class="block text-sm font-medium text-gray-700 mb-1">IC Number</label>
                <input type="text" id="edit_customer_ic" name="customer_ic" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="mb-4">
                <label for="edit_customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="text" id="edit_customer_phone" name="customer_phone" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeEditCustomerModal()" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">Cancel</button>
                <button type="submit" name="update_customer" class="btn-green text-white font-bold py-2 px-4 rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Staff Modal -->
<div id="editStaffModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Edit Staff</h3>
        <form method="POST" id="editStaffForm">
            <input type="hidden" id="edit_staff_id" name="staff_id">
            <div class="mb-4">
                <label for="edit_staff_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="edit_staff_name" name="staff_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="mb-4">
                <label for="edit_staff_dept" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <input type="text" id="edit_staff_dept" name="staff_dept" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeEditStaffModal()" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">Cancel</button>
                <button type="submit" name="update_staff" class="btn-green text-white font-bold py-2 px-4 rounded">Update</button>
            </div>
        </form>
    </div>
</div>


<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h3 class="text-xl font-semibold mb-4">Edit Product</h3>
        <form method="POST" id="editProductForm">
            <input type="hidden" id="edit_type_id" name="type_id">
            
            <div class="mb-4">
                <label for="edit_type_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                <input type="text" id="edit_type_name" name="type_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="mb-4">
                <label for="edit_price_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Price/Kg</label>
                <input type="text" id="edit_price_per_kg" name="price_per_kg" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeEditProductModal()" class="bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">Cancel</button>
                <button type="submit" name="update_product" class="btn-green text-white font-bold py-2 px-4 rounded">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
// [JavaScript functions remain unchanged]
function toggleMenu() 
{
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
    menu.classList.toggle('block');
}

function openEditCustomerModal(id, name, ic, phone) 
{
    document.getElementById('edit_customer_id').value = id;
    document.getElementById('edit_customer_name').value = name;
    document.getElementById('edit_customer_ic').value = ic;
    document.getElementById('edit_customer_phone').value = phone;
    document.getElementById('editCustomerModal').classList.remove('hidden');
}

function closeEditCustomerModal() {
    document.getElementById('editCustomerModal').classList.add('hidden');
}

function openEditStaffModal(id, name, dept) 
{
    document.getElementById('edit_staff_id').value = id;
    document.getElementById('edit_staff_name').value = name;
    document.getElementById('edit_staff_dept').value = dept;
    document.getElementById('editStaffModal').classList.remove('hidden');
}

function closeEditStaffModal()
 {
    document.getElementById('editStaffModal').classList.add('hidden');
}

// edit product function
function openEditProductModal(id, name, price) 
{
    document.getElementById('edit_type_id').value = id;
    document.getElementById('edit_type_name').value = name;
    document.getElementById('edit_price_per_kg').value = price;
    document.getElementById('editProductModal').classList.remove('hidden');
}

function closeEditProductModal()
 {
    document.getElementById('editProductModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('editCustomerModal').addEventListener('click', function(e) 
{
    if (e.target === this) closeEditCustomerModal();
});

document.getElementById('editStaffModal').addEventListener('click', function(e) 
{
    if (e.target === this) closeEditStaffModal();
});

document.getElementById('editProductModal').addEventListener('click', function(e) 
{
    if (e.target === this) closeEditProductModal();
});

// Set default dates for report generation
document.getElementById('start_date').valueAsDate = new Date();
document.getElementById('end_date').valueAsDate = new Date();


// Mobile menu toggle function
function toggleMenu() 
{
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}
</script>
</body>
</html>
