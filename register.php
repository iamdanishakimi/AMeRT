<?php
include('config.php');

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) 
{
    header('Location: admin.php');
    exit();
}

// Handle admin account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $name = $connect->real_escape_string($_POST['admin_name']);
    $username = $connect->real_escape_string($_POST['admin_username']);
    $email = $connect->real_escape_string($_POST['admin_email']);
    $password = $connect->real_escape_string($_POST['admin_password']);
    $confirm_password = $connect->real_escape_string($_POST['confirm_password']);
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match!';
    } else {
        // Check if username or email already exists
        $check_sql = "SELECT * FROM admin WHERE admin_username = '$username' OR admin_email = '$email'";
        $check_result = $connect->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $_SESSION['message'] = 'Username or email already exists!';
        } else {
            // Insert new admin account
            $sql = "INSERT INTO admin (admin_name, admin_username, admin_password, admin_email) 
                    VALUES ('$name', '$username', '$password', '$email')";
            
            if ($connect->query($sql)) {
                $_SESSION['message'] = 'Admin account created successfully! You can now login.';
                header('Location: login.php');
                exit();
            } else {
                $_SESSION['message'] = 'Error creating admin account: ' . $connect->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Register</title>
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
            <!-- Placeholder for logo - replace with your logo image -->
            <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
				<!--
				<div class="w-10 h-10 bg-green-500 rounded-full mr-3 flex items-center justify-center text-white font-bold">ER</div>
				-->
            <h1 class="text-2xl font-bold text-green-500">AMeRT - Admin Registration</h1>
        </a>
    </header>

    <main class="max-w-md mx-auto">
        <!-- Create Admin Account Form -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 border-b pb-2">Create Admin Account</h2>
            
            <?php if (isset($_SESSION['message']) &&  $_SESSION['message']): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" id="admin_name" name="admin_name" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="admin_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="admin_username" name="admin_username" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="admin_email" name="admin_email" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="admin_password" name="admin_password" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <button type="submit" name="create_admin" class="w-full btn-blue text-white font-bold py-3 px-6 rounded-lg transition">Create Admin Account</button>
                
                <div class="text-center mt-4">
                    <p class="text-gray-600">Already have an account?</p>
                    <a href="login.php" class="inline-block mt-2 btn-green text-white font-bold py-2 px-6 rounded-lg transition">Login</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>