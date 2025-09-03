<?php
include('config.php');

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) 
{
    header('Location: admin.php');
    exit();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) 
{
    $username = $connect->real_escape_string($_POST['username']);
    $password = $connect->real_escape_string($_POST['password']);
    
    // Check admin credentials
    $sql = "SELECT * FROM admin WHERE admin_username = '$username' AND admin_password = '$password'";
    $result = $connect->query($sql);
    
    if ($result->num_rows > 0) 
	{
        $admin = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        $_SESSION['admin_username'] = $admin['admin_username'];
        $_SESSION['admin_email'] = $admin['admin_email'];
        
        header('Location: admin.php');
        exit();
    } 
	else 
	{
        $_SESSION['message'] = 'Invalid username or password!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Login</title>
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
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex justify-center items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <a href="index.php" class="flex items-center">
            <!-- stakeholder logo -->
            <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
				<!--
				<div class="w-10 h-10 bg-green-500 rounded-full mr-3 flex items-center justify-center text-white font-bold">ER</div>
				-->
            <h1 class="text-2xl font-bold text-green-500">AMeRT - Admin Login</h1>
        </a>
    </header>

    <main class="max-w-md mx-auto">
        <!-- Login Form -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 border-b pb-2">Admin Login</h2>
            
            <?php if (isset($_SESSION['message'])): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <button type="submit" name="login" class="w-full btn-green text-white font-bold py-3 px-6 rounded-lg transition">Login</button>
                
                <div class="text-center mt-4">
                    <p class="text-gray-600">Don't have an account?</p>
                    <a href="register.php" class="inline-block mt-2 btn-blue text-white font-bold py-2 px-6 rounded-lg transition">Register</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
