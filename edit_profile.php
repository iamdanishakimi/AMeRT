<?php
include('config.php'); // include file config to acces all the variable declared
requireAdminAuth();

$admin_info = getAdminInfo();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) 
{
    $name = $connect->real_escape_string($_POST['admin_name']);
    $username = $connect->real_escape_string($_POST['admin_username']);
    $email = $connect->real_escape_string($_POST['admin_email']);
    $admin_id = $admin_info['id'];
    
    $sql = "UPDATE admin SET admin_name='$name', admin_username='$username', admin_email='$email' 
            WHERE admin_id=$admin_id";
    
    if ($connect->query($sql)) 
	{
        // Update session data
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_email'] = $email;
        
        $_SESSION['message'] = 'Profile updated successfully!'; // message appear 
        header('Location: edit_profile.php'); // where user will be lead after the session ended
        exit();
    } 
	else 
	{
        $_SESSION['message'] = 'Error updating profile: ' . $connect->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Edit Profile</title>
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
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
				<img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500">AMeRT - Edit Profile</h1>
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
                <!--<a href="reports.php" class="nav-link px-4 py-2 rounded-lg <?php echo isActivePage('reports.php'); ?>">Advanced Reports</a>-->
                
                <!-- Admin Dropdown Menu -->
                <div class="dropdown">
                    <a href="#" class="nav-link px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold flex items-center">
                        Admin <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </a>
                    <div class="dropdown-content">
                        <a href="edit_profile.php" class="px-4 py-2 bg-blue-100 text-blue-800 font-semibold">Edit Profile</a>
                        <a href="reset_password.php" class="px-4 py-2">Reset Password</a>
						<a href="reports.php" class="px-4 py-2">Generate Report</a>
                        <a href="logout.php" class="px-4 py-2 text-red-600 font-semibold">Logout</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-2xl font-bold text-blue-800 mb-6 border-b pb-2">Edit Profile</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="admin_name" name="admin_name" value="<?php echo $admin_info['name']; ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div>
                <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="admin_username" name="admin_username" value="<?php echo $admin_info['username']; ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div>
                <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="admin_email" name="admin_email" value="<?php echo $admin_info['email']; ?>" class="w-full p-3 border border-gray-300 rounded-lg" required>
            </div>
            
            <div class="flex justify-end">
                <a href="admin.php" class="bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg mr-3">Cancel</a>
                <button type="submit" name="update_profile" class="btn-green text-white font-bold py-2 px-6 rounded-lg">Update Profile</button>
            </div>
        </form>
    </main>
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