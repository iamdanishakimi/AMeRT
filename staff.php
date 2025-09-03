<?php
include('config.php');
include('sessionstaff.php');

//Redirect to login if not logged in
if (!isStaffLoggedIn()) 
{
    header('Location: login_staff.php');
    exit();
}

// Check if user is weight staff, redirect if not
if (!isWeightStaff()) 
{
    setSessionMessage('Access denied. Weight staff only.');
    header('Location: admin_staff.php');
    exit();
}
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (isset($_POST['register_customer'])) 
	{
        // Register new customer
        $name = $connect->real_escape_string($_POST['name']);
        $ic = $connect->real_escape_string($_POST['ic']);
        $phone = $connect->real_escape_string($_POST['phone']);
        
        $sql = "INSERT INTO customer (customer_name, customer_ic, customer_phone) 
                VALUES ('$name', '$ic', '$phone')";
        
        if ($connect->query($sql)) 
		{
            $_SESSION['message'] = 'Customer registered successfully!';
            $_SESSION['active_customer'] = $connect->insert_id;
        } 
		else 
		{
            $_SESSION['message'] = 'Error registering customer: ' . $connect->error;
        }
        
        header('Location: staff.php');
        exit();
    }
    
    if (isset($_POST['add_item'])) 
	{
        // Add item to current sale
        if (!isset($_SESSION['sale_items']))			
		{
            $_SESSION['sale_items'] = [];
        }
        
        $type = $connect->real_escape_string($_POST['item_type']);
        $weight = floatval($_POST['weight']);
        
        $price = $item_prices[$type]['price'] * $weight;
        $points = $item_prices[$type]['points'] * $weight;
        
        $_SESSION['sale_items'][] = 
		[
            'type' => $type,
            'weight' => $weight,
            'price' => $price,
            'points' => $points
        ];
        
        header('Location: staff.php');
        exit();
    }
    
    if (isset($_POST['finalize_sale'])) 
	{
        // Finalize the sale
        if (isset($_SESSION['active_customer']) && isset($_SESSION['sale_items']) && count($_SESSION['sale_items']) > 0) 
		{
            $customer_id = $_SESSION['active_customer'];
            $total = array_sum(array_column($_SESSION['sale_items'], 'price'));
            $total_points = array_sum(array_column($_SESSION['sale_items'], 'points'));
            
            // Start transaction
            $connect->begin_transaction();
            
            try 
			{
                // Insert sale - using recycling_transactions table instead of sales
                $sql = "INSERT INTO recycling_transactions (customer_id, transaction_date, total_amount, total_points) 
                        VALUES ($customer_id, CURDATE(), $total, $total_points)";
                $connect->query($sql);
                $transaction_id = $connect->insert_id;
                
                // Insert sale items - using transaction_items table instead of sale_items
                foreach ($_SESSION['sale_items'] as $item) 
				{
                    // Get type_id from item_types table
                    $type_name = $connect->real_escape_string($item['type']);
                    $type_query = "SELECT type_id FROM item_types WHERE type_name = '$type_name'";
                    $type_result = $connect->query($type_query);
                    
                    if ($type_result && $type_result->num_rows > 0) 
					{
                        $type_row = $type_result->fetch_assoc();
                        $type_id = $type_row['type_id'];
                        
                        $sql = "INSERT INTO transaction_items (transaction_id, type_id, weight, subtotal, points_earned) 
                                VALUES ($transaction_id, $type_id, {$item['weight']}, {$item['price']}, {$item['points']})";
                        $connect->query($sql);
                    }
                }
                
                // Update customer points
                $sql = "UPDATE customer SET customer_points = customer_points + $total_points 
                        WHERE customer_id = $customer_id";
                $connect->query($sql);
                
                $connect->commit();
                $_SESSION['message'] = "Transaction finalized successfully! Total: " . formatCurrency($total) . ", Points: $total_points";
                
                // Clear sale data
                unset($_SESSION['sale_items']);
                unset($_SESSION['active_customer']);
                
            } 
			catch (Exception $e) 
			{
                $connect->rollback();
                $_SESSION['message'] = "Error finalizing transaction: " . $e->getMessage();
            }
        } 
		else 
		{
            $_SESSION['message'] = "Cannot finalize transaction. No customer selected or no items added.";
        }
        
        header('Location: staff.php');
        exit();
    }
}

// Get customer by IC
$customer = null;
if (isset($_GET['search_customer'])) 
{
    $ic = $connect->real_escape_string($_GET['ic_search']);
    $sql = "SELECT * FROM customer WHERE customer_ic = '$ic'";
    $result = $connect->query($sql);
    
    if ($result && $result->num_rows > 0) 
	{
        $customer = $result->fetch_assoc();
        $_SESSION['active_customer'] = $customer['customer_id'];
    } 
	else 
	{
        $_SESSION['message'] = "Customer not found with IC: $ic";
    }
}

// Get customer sales history
$sales_history = [];
if (isset($_SESSION['active_customer'])) 
{
    $customer_id = $_SESSION['active_customer'];
    $sql = "SELECT rt.*, ti.weight, ti.subtotal, ti.points_earned, it.type_name 
            FROM recycling_transactions rt 
            JOIN transaction_items ti ON rt.transaction_id = ti.transaction_id 
            JOIN item_types it ON ti.type_id = it.type_id 
            WHERE rt.customer_id = $customer_id 
            ORDER BY rt.transaction_date DESC";
    $result = $connect->query($sql);
    
    if ($result) 
	{
        while ($row = $result->fetch_assoc()) 
		{
            $sales_history[] = $row;
        }
    }
}

// Get current sale items
$sale_items = $_SESSION['sale_items'] ?? [];
$sale_total = array_sum(array_column($sale_items, 'price'));
$sale_points = array_sum(array_column($sale_items, 'points'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Staff Portal</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
    body { font-family: 'Inter', sans-serif; }
    .btn-green { background-color: #16B981; }
    .btn-green:hover { background-color: #059669; }
    .btn-blue { background-color: #1E3A8A; }
    .btn-blue:hover { background-color: #1E40AF; }
    .btn-red { background-color: #EF4444; }
    .btn-red:hover { background-color: #DC2626; }
	
	 /* Style untuk dropdown */
    .relative { position: relative; }
    .origin-top-right { transform-origin: top right; }
    .absolute { position: absolute; }
    .hidden { display: none; }
    .block { display: block; }
    
    /* Ensure dropdown appears above other content */
    #user-menu {
        z-index: 1000;
    }
    
    /* Mobile menu styling */
    .mobile-menu {
        transition: all 0.3s ease;
    }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- Placeholder for logo - replace with your logo image -->
				<img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
				<!--
				<div class="w-10 h-10 bg-green-500 rounded-full mr-3 flex items-center justify-center text-white font-bold">ER</div>
				-->
                <h1 class="text-2xl font-bold text-green-500"><?php echo $title;?> - Staff Portal</h1>
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
        
        <!-- Dropdown for user menu with logout -->
        <div class="relative inline-block text-left">
            <button type="button" class="inline-flex justify-center items-center nav-link px-4 py-2 rounded-lg btn-green text-white font-semibold" id="user-menu-button">
                Hi, <?php echo $_SESSION['staff_dept']; ?> ▼
            </button>
            
            <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" id="user-menu">
                <div class="py-1" role="menu" aria-orientation="vertical">
                    <span class="block px-4 py-2 text-sm text-gray-700 border-b">
                        Logged in as:<br>
                        <strong><?php echo $_SESSION['staff_name']; ?></strong>
                    </span>
                    <a href="logout_staff.php" class="block px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700" role="menuitem">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
    </header>

    <main class="space-y-8">
        <?php if (isset($_SESSION['message']) &&  $_SESSION['message']): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Customer Check-In</h2>
            <p class="text-gray-600 mb-4">Search for a customer by IC number to begin a recycling transaction.</p>
            <form method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-grow w-full">
                    <label for="ic_search" class="block text-sm font-medium text-gray-700 mb-1">Customer IC Number:</label>
                    <input type="text" id="ic_search" name="ic_search" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., 900101-10-1234" required>
                </div>
                <button type="submit" name="search_customer" class="w-full md:w-auto btn-blue text-white font-bold py-3 px-6 rounded-lg transition">Search</button>
                <button type="button" onclick="document.getElementById('new-customer-form').classList.toggle('hidden');" class="w-full md:w-auto btn-green text-white font-bold py-3 px-6 rounded-lg transition">Register New</button>
            </form>

            <?php if ($customer): ?>
            <div class="mt-6 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-700">Active Customer: <?php echo $customer['customer_name']; ?></h3>
                <p class="text-gray-600">IC: <?php echo $customer['customer_ic']; ?> | Phone: <?php echo $customer['customer_phone']; ?> | Points: <?php echo $customer['customer_points']; ?></p>
                
                <?php if (!empty($sales_history)): ?>
                <h4 class="text-lg font-semibold mt-4 mb-2">Recycling History</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Item</th>
                                <th class="py-2 px-4">Weight</th>
                                <th class="py-2 px-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales_history as $sale): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?php echo $sale['transaction_date']; ?></td>
                                <td class="py-2 px-4"><?php echo $sale['type_name']; ?></td>
                                <td class="py-2 px-4"><?php echo $sale['weight']; ?>kg</td>
                                <td class="py-2 px-4"><?php echo formatCurrency($sale['subtotal']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div id="new-customer-form" class="mt-6 hidden space-y-4 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-xl font-semibold text-blue-700">New Customer Registration</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Full Name" class="w-full p-3 border rounded-lg" required>
                        </div>
                        <div>
                            <label for="ic" class="block text-sm font-medium text-gray-700 mb-1">IC Number</label>
                            <input type="text" id="ic" name="ic" placeholder="IC Number" class="w-full p-3 border rounded-lg" required>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" id="phone" name="phone" placeholder="Phone Number" class="w-full p-3 border rounded-lg" required>
                        </div>
                    </div>
                    <button type="submit" name="register_customer" class="mt-4 btn-green text-white font-bold py-3 px-6 rounded-lg">Save Customer</button>
                </form>
            </div>
        </div>

        <?php if (isset($_SESSION['active_customer'])): ?>
        <div class="bg-white p-6 rounded-xl shadow-2xl">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">New Recycling Transaction</h2>
            <p class="text-gray-600 mb-4">Add recyclable items for the active customer. Rewards are calculated based on weight.</p>
            
            <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-6">
                <div>
                    <label for="item_type" class="block text-sm font-medium text-gray-700 mb-1">Item Type</label>
                    <select id="item_type" name="item_type" class="w-full p-3 border border-gray-300 rounded-lg" required>
                        <?php foreach ($item_prices as $type => $details): ?>
                        <option value="<?php echo $type; ?>"><?php echo $type; ?> (RM<?php echo $details['price']; ?>/kg)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                    <input type="number" id="weight" name="weight" step="0.01" min="0.01" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="e.g., 5.5" required>
                </div>
                <button type="submit" name="add_item" class="btn-blue text-white font-bold py-3 px-6 rounded-lg transition">Add Item</button>
            </form>

            <div class="mt-6">
                <h3 class="text-xl font-semibold">Current Recycling Items</h3>
                <div class="mt-2 bg-gray-100 p-4 rounded-lg min-h-[100px]">
                    <?php if (!empty($sale_items)): ?>
                    <ul class="space-y-2">
                        <?php foreach ($sale_items as $index => $item): ?>
                        <li class="flex justify-between items-center bg-white p-2 rounded">
                            <span><?php echo $item['weight']; ?>kg of <?php echo $item['type']; ?> @ <?php echo formatCurrency($item_prices[$item['type']]['price']); ?>/kg (<?php echo $item_prices[$item['type']]['points']; ?> points/kg)</span>
                            <span class="font-semibold"><?php echo formatCurrency($item['price']); ?> | <?php echo $item['points']; ?> points</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-gray-500">No items added yet.</p>
                    <?php endif; ?>
                </div>
                <div class="text-right mt-4 text-2xl font-bold">
                    Total: <span class="text-green-600"><?php echo formatCurrency($sale_total); ?></span> | 
                    Points: <span class="text-blue-600"><?php echo $sale_points; ?></span>
                </div>
            </div>

            <div class="text-right mt-6">
                <form method="POST">
                    <button type="submit" name="finalize_sale" class="btn-green text-white font-bold py-3 px-8 rounded-lg transition text-lg">Finalize Transaction</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </main>
	<?php include('footer.php');?> <!-- footer -->
</div>

<script>
// Toggle mobile menu
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// Toggle user dropdown menu
function toggleUserMenu(event) {
    event.stopPropagation();
    const userMenu = document.getElementById('user-menu');
    if (userMenu) {
        userMenu.classList.toggle('hidden');
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Setup user menu button
    const userMenuButton = document.getElementById('user-menu-button');
    if (userMenuButton) {
        userMenuButton.addEventListener('click', toggleUserMenu);
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.getElementById('user-menu');
        const userMenuButton = document.getElementById('user-menu-button');
        
        if (userMenu && userMenuButton && 
            !userMenu.contains(event.target) && 
            !userMenuButton.contains(event.target)) {
            userMenu.classList.add('hidden');
        }
    });
});
</script>
</body>
</html>
