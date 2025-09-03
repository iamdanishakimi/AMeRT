<?php
include('config.php');

// Add your customer page code here, but make sure to include the header with logo
$customer = null;
$sales_history = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_customer'])) 
{
    $ic = $connect->real_escape_string($_POST['ic']);
    
    $sql = "SELECT * FROM customer WHERE customer_ic = '$ic'";
    $result = $connect->query($sql);
    
    if ($result->num_rows > 0) 
    {
        $customer = $result->fetch_assoc();
        $_SESSION['customer_id'] = $customer['customer_id'];
        
        // retrieve customer sales history - FIXED QUERY
        $customer_id = $customer['customer_id'];
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
		else 
		{
            $_SESSION['message'] = "Error retrieving transaction history: " . $connect->error;
        }
    } 
    else 
    {
        // kalau dalam row tu xde data yang kita search
        $_SESSION['message'] = "Customer not found with IC: $ic";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Customer Portal</title>
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
	
	 .recycling-item, #grid { transition: transform 0.3s ease; }
    .recycling-item:hover, #grid:hover  { transform: translateY(-5px); }
	 .stat-card { transition: all 0.3s ease; }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- Placeholder for logo - replace with your logo image -->
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500"><?php echo $title;?> - Customer Portal</h1>
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
                <a href="login.php" class="nav-link px-4 py-2 rounded-lg btn-green text-white font-semibold">Login</a>
            </div>
        </nav>
    </header>
    
    <main class="space-y-8">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Customer Login</h2>
            <p class="text-gray-600 mb-4">Enter your IC number to view your recycling history and rewards.</p>
            
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-grow w-full">
                    <label for="ic" class="block text-sm font-medium text-gray-700 mb-1">IC Number:</label>
                    <input type="text" id="ic" name="ic" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., 900101-10-1234" required>
                </div>
                <button type="submit" name="login_customer" class="w-full md:w-auto btn-blue text-white font-bold py-3 px-6 rounded-lg transition">View My Account</button>
            </form>
        </div>

        <?php if ($customer): ?>
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">My Recycling Account</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg" id="grid">
                    <h3 class="text-lg font-semibold text-blue-700">Personal Information</h3>
                    <p class="mt-2"><span class="font-medium">Name:</span> <?php echo $customer['customer_name']; ?></p>
                    <p><span class="font-medium">IC Number:</span> <?php echo $customer['customer_ic']; ?></p>
                    <p><span class="font-medium">Phone:</span> <?php echo $customer['customer_phone']; ?></p>
                </div>
                
				<!-- remove points section -->
				<!--
                <div class="bg-green-50 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-700">Rewards Summary</h3>
                    <p class="mt-2 text-2xl font-bold text-green-600"><?php echo $customer['customer_points']; ?> Points</p>
                    <p class="text-sm text-gray-600">Total points earned from recycling</p>
                </div>
				-->
                
                <div class="bg-yellow-50 p-4 rounded-lg" id="grid">
                    <h3 class="text-lg font-semibold text-yellow-700">Recycling Impact</h3>
                    <?php
                    $total_weight = 0;
                    $total_amount = 0;
                    foreach ($sales_history as $sale) 
                    {
                        $total_weight += $sale['weight'];
                        $total_amount += $sale['subtotal'];
                    }
                    ?>
                    <p class="mt-2"><span class="font-medium">Total Recycled:</span> <?php echo number_format($total_weight, 2); ?> kg</p>
                    <p><span class="font-medium">Total Earned:</span> <?php echo formatCurrency($total_amount); ?></p>
                </div>
            </div>

            <h3 class="text-xl font-semibold mb-4">Recycling History</h3>
            <?php if (!empty($sales_history)): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border border-gray-500 rounded-lg">
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
            <?php else: ?>
            <p class="text-gray-500">No recycling history found.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
		
		<!-- recycling materials section -->
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-2xl font-bold text-blue-800 mb-6 text-center">What We Recycle</h3>
                <p class="text-gray-600 mb-8 text-center">We accept a wide variety of recyclable materials with competitive pricing.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Paper -->
                    <div class="recycling-item bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fa-solid fa-file-lines text-blue-600"></i>
                            </div>
                            <h4 class="font-semibold text-blue-800">Paper & Cardboard</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Newspapers, magazines, cardboard boxes, office paper</p>
                        <p class="font-bold text-blue-600">RM 0.30 - 1.00 / kg</p>
                    </div>
                    
                    <!-- Plastic -->
                    <div class="recycling-item bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fa-solid fa-bottle-water text-green-600"></i>
                            </div>
                            <h4 class="font-semibold text-green-800">Plastic</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Bottles, containers, packaging, PVC, ABS plastics</p>
                        <p class="font-bold text-green-600">RM 0.20 - 0.50 / kg</p>
                    </div>
                    
                    <!-- Metal -->
                    <div class="recycling-item bg-yellow-50 p-4 rounded-lg">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fa-solid fa-wrench text-yellow-600"></i>
                            </div>
                            <h4 class="font-semibold text-yellow-800">Metal</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Aluminum cans, copper wire, steel, electronic waste</p>
                        <p class="font-bold text-yellow-600">RM 0.20 - 22.00 / kg</p>
                    </div>
                </div>
                
                <div class="text-center mt-8">
                    <a href="materials.php" class="btn-blue text-white font-bold py-3 px-6 rounded-lg inline-flex items-center">
                        View All Materials & Pricing <i class="ml-2 fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
    </main>
    
    <?php include('footer.php');?> <!-- footer -->
</div>

<script>
function toggleMenu() 
{
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
    menu.classList.toggle('block');
}
</script>
</body>
</html>