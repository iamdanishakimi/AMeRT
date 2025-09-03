<?php
include('config.php');
include('sessionstaff.php');

// Redirect if already logged in
redirectIfStaffLoggedIn();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) 
{
    $username = $connect->real_escape_string($_POST['username']);
    $password = $connect->real_escape_string($_POST['password']);
    
    // Check staff credentials - GUNAKAN staff_ic BUKAN staff_username
    $sql = "SELECT * FROM staff WHERE staff_ic = '$username' AND staff_password = '$password'";
    $result = $connect->query($sql);
    
    // Check if query was successful
    if ($result === false) {
        setSessionMessage('Database error: ' . $connect->error);
        header('Location: login_staff.php');
        exit();
    }
    
    if ($result->num_rows > 0) 
    {
        $staff = $result->fetch_assoc();
        
        // Set session variables
        setStaffSession($staff);
        
        // Redirect based on role
        if ($staff['staff_dept'] === 'admin') 
        {
            header('Location: admin_staff.php');
        } 
        else 
        {
            header('Location: staff.php');
        }
        exit();
    } 
    else 
    {
        setSessionMessage('Invalid IC number or password!');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Staff Login</title>
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
            <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
            <h1 class="text-2xl font-bold text-green-500">AMeRT - Staff Login</h1>
        </a>
    </header>

    <main class="max-w-md mx-auto">
        <!-- Login Form -->
        <div class="bg-white p-6 rounded-xl shadow-lg">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 border-b pb-2">Staff Login</h2>
            
            <?php $message = getSessionMessage(); if ($message): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $message; ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">IC Number</label>
                    <input type="text" id="username" name="username" class="w-full p-3 border border-gray-300 rounded-lg" placeholder="Your IC number" required>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-lg" required>
                </div>
                
                <button type="submit" name="login" class="w-full btn-green text-white font-bold py-3 px-6 rounded-lg transition">Login</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>