<?php
include('config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title><?php echo $title;?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
    .hero-section { 
        background: linear-gradient(to right, #16B981, #1E3A8A);
        background-size: cover;
        background-position: center;
    }
    .btn-green { background-color: #16B981; }
    .btn-green:hover { background-color: #059669; }
    .btn-blue { background-color: #1E3A8A; }
    .btn-blue:hover { background-color: #1E40AF; }
    footer{ border-radius: 6px; padding: 20px; margin-top: 15px;}
    .recycling-item { transition: transform 0.3s ease; }
    .recycling-item:hover { transform: translateY(-5px); }
    .stat-card { transition: all 0.3s ease; }
    .stat-card:hover { 
										transform: translateY(-3px); 
										box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- logo amert -->
				<img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
				<!--
                <div class="w-12 h-12 bg-green-500 rounded-full mr-3 flex items-center justify-center text-white">
                    <i class="fa-solid fa-recycle text-xl"></i>
                </div>
				-->
                <h1 class="text-2xl font-bold text-green-500"><?php echo $title;?></h1>
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

    <main>
        <div class="space-y-8">
            <!-- Hero Section -->
            <div class="hero-section text-white text-center py-16 rounded-xl shadow-2xl">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Welcome to <?php echo $title;?></h2>
                <p class="text-xl mb-8 max-w-2xl mx-auto">Transforming waste into resources through efficient recycling management and rewarding eco-friendly practices</p>
                <a href="customer.php" class="inline-block mt-4 btn-green text-white font-bold py-3 px-8 rounded-lg text-lg shadow-lg hover:shadow-xl transition">Get Started <i class="ml-2 fa-solid fa-arrow-right"></i></a>
            </div>
            
            <!-- Stats Section -->
            <div class="bg-white p-6 rounded-xl shadow-2xl">
                <h3 class="text-2xl font-bold text-blue-800 mb-6 text-center">Our Impact</h3>
                <p class="text-gray-600 mb-8 text-center">Join our growing community in making a difference through responsible recycling practices.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="stat-card bg-blue-50 p-6 rounded-lg text-center">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">
                            <?php
							// kira total customer by retrieve data from db
                            $sql = "SELECT COUNT(*) as total_customers FROM customer";
                            $result = $connect->query($sql);
                            $row = $result->fetch_assoc();
                            echo number_format($row['total_customers'] ?? 0, 0);
                            ?>
                        </p>
                        <p class="text-gray-600">Community Members</p>
                    </div>
                    
                    <div class="stat-card bg-green-50 p-6 rounded-lg text-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-weight-scale text-green-600 text-2xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-green-600">
                            <?php
                            $sql = "SELECT SUM(weight) as total_weight FROM transaction_items ti 
                                    JOIN recycling_transactions rt ON ti.transaction_id = rt.transaction_id";
                            $result = $connect->query($sql);
                            $row = $result->fetch_assoc();
                            echo number_format($row['total_weight'] ?? 0, 0);
                            ?>
                        </p>
                        <p class="text-gray-600">Kgs Recycled</p>
                    </div>
                    
                    <div class="stat-card bg-yellow-50 p-6 rounded-lg text-center">
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-coins text-yellow-600 text-2xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-yellow-600">
                            <?php
                            $sql = "SELECT SUM(total_amount) as total_revenue FROM recycling_transactions";
                            $result = $connect->query($sql);
                            $row = $result->fetch_assoc();
                            echo 'RM' . number_format($row['total_revenue'] ?? 0, 0);
                            ?>
                        </p>
                        <p class="text-gray-600">Total Revenue</p>
                    </div>
                    
					<!--
                    <div class="stat-card bg-purple-50 p-6 rounded-lg text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-award text-purple-600 text-2xl"></i>
                        </div>
                        <p class="text-3xl font-bold text-purple-600">
                            <?php
                            $sql = "SELECT SUM(customer_points) as total_points FROM customer";
                            $result = $connect->query($sql);
                            $row = $result->fetch_assoc();
                            echo number_format($row['total_points'] ?? 0, 0);
                            ?>
                        </p>
                        <p class="text-gray-600">Points Rewarded</p>
                    </div>
					-->
                </div>
            </div>
            
            <!-- How It Works Section -->
            <div class="bg-white p-6 rounded-xl shadow-2xl">
                <h3 class="text-2xl font-bold text-blue-800 mb-6 text-center">How It Works</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-user-plus text-blue-600 text-3xl"></i>
                        </div>
                        <h4 class="font-semibold text-lg mb-2">1. Register</h4>
                        <p class="text-gray-600">Create your account with your basic information and IC number</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-truck-ramp-box text-green-600 text-3xl"></i>
                        </div>
                        <h4 class="font-semibold text-lg mb-2">2. Recycle</h4>
                        <p class="text-gray-600">Bring your recyclables to our collection center for weighing</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fa-solid fa-gift text-purple-600 text-3xl"></i>
                        </div>
                        <h4 class="font-semibold text-lg mb-2">3. Get Rewarded</h4>
                        <p class="text-gray-600">Earn cash and points based on the type and weight of materials</p>
                    </div>
                </div>
            </div>
            
            <!-- Testimonials Section
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-2xl font-bold text-blue-800 mb-6 text-center">What Our Customers Say</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fa-solid fa-user text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold">Ahmad R.</h4>
                                <div class="flex text-yellow-400">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">"e-RecycloTrack has made recycling so convenient. I've earned over 5000 points that I've redeemed for vouchers!"</p>
                    </div>
                    
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fa-solid fa-user text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold">Siti M.</h4>
                                <div class="flex text-yellow-400">
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                    <i class="fa-solid fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">"The staff is very helpful and the process is quick. I appreciate how they educate about proper waste separation."</p>
                    </div>
                </div>
            </div>
			-->
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
    
    // Simple animation for stats counting
    document.addEventListener('DOMContentLoaded', function() 
	{
        const statCards = document.querySelectorAll('.stat-card');
        statCards.forEach(card => 
		{
            card.addEventListener('mouseover', function() 
			{
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
            });
            
            card.addEventListener('mouseout', function() 
			{
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    });
</script>
</body>
</html>
