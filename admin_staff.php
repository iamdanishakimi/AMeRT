<?php
include('config.php');
include('sessionstaff.php');

// Redirect to login if not logged in
if (!isStaffLoggedIn()) 
{
    header('Location: login_staff.php');
    exit();
}

// Check if user is admin staff, redirect if not
if (!isAdminStaff())	
{
    setSessionMessage('Access denied. Admin staff only.');
    header('Location: staff.php');
    exit();
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) 
{
    $transaction_id = $connect->real_escape_string($_POST['transaction_id']);
    
    // Update transaction status to paid
    $sql = "UPDATE recycling_transactions SET status = 'paid', verified_by = {$_SESSION['staff_id']},verified_at = NOW() WHERE transaction_id = $transaction_id";
    
    if ($connect->query($sql)) 
	{
        setSessionMessage('Payment verified successfully!');
    } 
	else 
	{
        setSessionMessage('Error verifying payment: ' . $connect->error);
    }
    
    header('Location: admin_staff.php');
    exit();
}

// Get pending transactions
$pending_transactions = [];
$sql = "SELECT rt.*, c.customer_name, c.customer_ic, c.customer_phone 
        FROM recycling_transactions rt 
        JOIN customer c ON rt.customer_id = c.customer_id 
        WHERE rt.status = 'pending' 
        ORDER BY rt.transaction_date DESC";
$result = $connect->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pending_transactions[] = $row;
    }
}

// Get transaction items for a specific transaction
function getTransactionItems($transaction_id) {
    global $connect;
    
    $items = [];
    $sql = "SELECT ti.*, it.type_name 
            FROM transaction_items ti 
            JOIN item_types it ON ti.type_id = it.type_id 
            WHERE ti.transaction_id = $transaction_id";
    $result = $connect->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    return $items;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Admin Staff Portal</title>
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
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500"><?php echo $title;?> - Admin Staff Portal</h1>
            </a>
            <button onclick="toggleMobileMenu()" class="md:hidden p-2 rounded-lg hover:bg-green-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16 M4 12h16 M4 18h16"></path>
                </svg>
            </button>
        </div>
        <nav id="mobile-menu" class="mobile-menu md:ml-4 w-full md:w-auto mt-4 md:mt-0 hidden md:block">
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 w-full">
                <a href="index.php" class="nav-link px-4 py-2 rounded-lg hover:bg-gray-100 transition">Home</a>
                <a href="staff.php" class="nav-link px-4 py-2 rounded-lg hover:bg-gray-100 transition">Staff Portal</a>
                <a href="admin_staff.php" class="nav-link px-4 py-2 rounded-lg bg-blue-100 text-blue-800 font-semibold hover:bg-blue-200 transition">Admin Portal</a>
                
                <!-- Dropdown for user menu with logout -->
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex justify-center items-center px-4 py-2 rounded-lg btn-blue text-white font-semibold hover:bg-blue-800 transition" id="user-menu-button">
                        Hi, <?php echo htmlspecialchars($_SESSION['staff_name']); ?> ▼
                    </button>
                    
                    <div class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" id="user-menu">
                        <div class="py-1" role="menu" aria-orientation="vertical">
                            <span class="block px-4 py-2 text-sm text-gray-700 border-b">
                                Logged in as:<br>
                                <strong><?php echo htmlspecialchars($_SESSION['staff_name']); ?></strong>
                            </span>
                            <a href="logout_staff.php" class="block px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 transition" role="menuitem">
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
            <h2 class="text-2xl font-bold text-blue-800 mb-4 border-b pb-2">Pending Transactions Verification</h2>
            
            <?php if (count($pending_transactions) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-4">Transaction ID</th>
                                <th class="py-2 px-4">Date</th>
                                <th class="py-2 px-4">Customer</th>
                                <th class="py-2 px-4">Amount</th>
                                <th class="py-2 px-4">Points</th>
                                <th class="py-2 px-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_transactions as $transaction): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4">#<?php echo $transaction['transaction_id']; ?></td>
                                <td class="py-2 px-4"><?php echo $transaction['transaction_date']; ?></td>
                                <td class="py-2 px-4">
                                    <?php echo htmlspecialchars($transaction['customer_name']); ?><br>
                                    <small class="text-gray-500">IC: <?php echo htmlspecialchars($transaction['customer_ic']); ?></small>
                                </td>
                                <td class="py-2 px-4"><?php echo formatCurrency($transaction['total_amount']); ?></td>
                                <td class="py-2 px-4"><?php echo $transaction['total_points']; ?></td>
                                <td class="py-2 px-4">
                                    <button onclick="viewTransaction(<?php echo $transaction['transaction_id']; ?>)" class="btn-blue text-white text-sm py-1 px-3 rounded hover:bg-blue-700 transition">View Details</button>
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="transaction_id" value="<?php echo $transaction['transaction_id']; ?>">
                                        <button type="submit" name="verify_payment" class="btn-green text-white text-sm py-1 px-3 rounded hover:bg-green-700 transition">Verify Payment</button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Details row -->
                            <tr id="details-<?php echo $transaction['transaction_id']; ?>" class="hidden bg-blue-50">
                                <td colspan="6" class="py-3 px-4">
                                    <h4 class="font-semibold">Transaction Items:</h4>
                                    <?php $items = getTransactionItems($transaction['transaction_id']); ?>
                                    <ul class="list-disc list-inside mt-2">
                                        <?php foreach ($items as $item): ?>
                                        <li><?php echo $item['weight']; ?>kg of <?php echo htmlspecialchars($item['type_name']); ?> - <?php echo formatCurrency($item['subtotal']); ?> (<?php echo $item['points_earned']; ?> points)</li>
                                        <?php endforeach; ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No pending transactions at the moment.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Toggle mobile menu
function toggleMobileMenu() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
}

// Toggle transaction details
function viewTransaction(transactionId) {
    const detailsRow = document.getElementById('details-' + transactionId);
    if (detailsRow) {
        detailsRow.classList.toggle('hidden');
    }
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
    
    // Check for new transactions every 10 seconds
    setInterval(function() {
        fetch('check_new_transactions.php')
            .then(response => response.json())
            .then(data => {
                if (data.new_transactions > 0) {
                    if (confirm('There are ' + data.new_transactions + ' new transaction(s) waiting for verification. Click OK to view them.')) {
                        location.reload();
                    }
                }
            })
            .catch(error => console.error('Error checking new transactions:', error));
    }, 10000);
});
</script>
</body>
</html>