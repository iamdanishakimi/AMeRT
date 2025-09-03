<?php
include('config.php');

// Get all item types from database
$items = [];
$sql = "SELECT type_name, price_per_kg, points_per_kg FROM item_types ORDER BY type_name";
$result = $connect->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) 
	{
        $items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $title;?> - Materials & Pricing</title>
<link rel="icon" href="favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; }
    .btn-green { background-color: #16B981; }
    .btn-green:hover { background-color: #059669; }
    .btn-blue { background-color: #1E3A8A; }
    .btn-blue:hover { background-color: #1E40AF; }
    .material-row { transition: background-color 0.2s ease; }
    .material-row:hover { background-color: #f9fafb; }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<div class="container mx-auto p-4 md:p-8">
    <header class="flex flex-col md:flex-row justify-between items-center bg-white p-4 rounded-xl shadow-lg mb-8">
        <div class="flex justify-between items-center w-full md:w-auto">
            <a href="index.php" class="flex items-center">
                <!-- Logo with recycling symbol -->
                <img src="logo.jpg" alt="Logo" class="w-10 h-10 mr-3 rounded-full">
                <h1 class="text-2xl font-bold text-green-500"><?php echo $title;?> - Materials & Pricing</h1>
            </a>
            <button onclick="toggleMenu()" class="md:hidden p-2 rounded-lg hover:bg-green-100">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16 M4 12h16 M4 18h16"></path>
                </svg>
            </button>
        </div>
        <nav id="mobile-menu" class="mobile-menu md:ml-4 w-full md:w-auto mt-4 md:mt-0 hidden md:block">
            <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-2 w-full">
                <a href="index.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100">Home</a>
                <a href="staff.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100">Staff Portal</a>
                <a href="customer.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100">Customer Portal</a>
                <a href="admin.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-100">Admin Dashboard</a>
                <!--<a href="materials.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg bg-blue-100 text-blue-800 font-semibold">Materials & Pricing</a>-->
                <a href="login.php" class="nav-link text-gray-700 px-4 py-2 rounded-lg btn-green text-white font-semibold">Login</a>
            </div>
        </nav>
    </header>

    <main class="bg-white p-6 rounded-xl shadow-lg">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <h2 class="text-2xl font-bold text-blue-800 mb-4 md:mb-0">Recycling Materials & Pricing</h2>
			<!--
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">Last updated: <?php echo date('d/m/Y'); ?></span>
                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Live Prices</span>
            </div>
			-->
        </div>
        
        <p class="text-gray-600 mb-6">We offer competitive pricing for a wide variety of recyclable materials. Prices are subject to change based on market conditions.</p>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xl border border-gray-500">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-3 px-4 font-semibold text-gray-700">Material Type</th>
                        <th class="py-3 px-4 font-semibold text-gray-700">Price per kg</th>
						<!--
                        <th class="py-3 px-4 font-semibold text-gray-700">Points per kg</th>
						-->
                        <th class="py-3 px-4 font-semibold text-gray-700">Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $categories = 
					[
                        'Metal' => ['AB', 
												'Aluminium Enjin', 
												'Aluminium Grea', 
												'Aluminium Rim', 
												'Aluminium Sari', 
												'Aluminium Tangki', 
												'Aluminium Tin', 
												'Aluminium Wayar', 
												'Aluminium Wayar Kulit',
												'Besi No.1', 
												'Besi No.2', 
												'Besi No.3', 
												'Steel 304', 
												'Steel 202', 
												'Steel Jaring',
												'Tembaga Wayar (A1)', 
												'Tembaga Wayar (A)', 
												'Tembaga Bakar(Wayar B)', 
												'Tembaga Tokol (Kuning)', 
												'Tembaga Nipis', 
												'Tembaga Tangki', 
												'Tembaga Wayar Kulit',
												'Timah'],
                        'Plastic' => ['Plastik (K)', 'Plastik (L)', 'Plastik (PVC)', 'Plastik (ABS)', 'Plastik Tali'],
                        'Paper' => ['Kertas', 'Kotak'],
                        'Other' => ['Awning', 
												'Linen Brek', 
												'Guni/Jumbo Bag', 
												'Tong Drum', 
												'E-Waste / TV LCD', 
												'Komputer', 
												'TV', 'Aircond', 
												'Mesin Basuh (Plastik)', 
												'Peti Sejuk (Besi No.3)']
                    ];
                    
                    foreach ($items as $item): 
                        $category = 'Other';
                        foreach ($categories as $cat => $materials) 
						{
                            if (in_array($item['type_name'], $materials)) 
							{
                                $category = $cat;
                                break;
                            }
                        }
                        
                        // Determine icon based on category
                        $icon = 'fa-cube';
                        $color = 'gray';
                        if ($category === 'Metal') 
						{
                            $icon = 'fa-wrench'; // icon logo item
                            $color = 'yellow';
                        } 
						elseif ($category === 'Plastic') 
						{
                            $icon = 'fa-bottle-water'; // icon logo item
                            $color = 'green';
                        }
						elseif ($category === 'Paper')
						{
                            $icon = 'fa-file-lines'; // icon logo item
                            $color = 'blue';
                        }
                    ?>
                    <tr class="material-row border-b">
                        <td class="py-3 px-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-<?php echo $color; ?>-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fa-solid <?php echo $icon; ?> text-<?php echo $color; ?>-600"></i>
                                </div>
                                <?php echo $item['type_name']; ?>
                            </div>
                        </td>
                        <td class="py-3 px-4 font-semibold text-green-600">RM <?php echo number_format($item['price_per_kg'], 2); ?></td>
						<!--
                        <td class="py-3 px-4">
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                <?php echo number_format($item['points_per_kg']); ?> pts
                            </span>
                        </td>
						-->
                        <td class="py-3 px-4">
                            <span class="bg-<?php echo $color; ?>-100 text-<?php echo $color; ?>-800 text-xl font-medium px-3.5 py-1.5 rounded">
                                <?php echo $category; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 bg-blue-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">How Pricing Works</h3>
            <p class="text-sm text-gray-600 mb-2">Our pricing is based on current market rates for recycled materials. Prices may fluctuate based on:</p>
            <ul class="text-sm text-gray-600 list-disc list-inside">
                <li>Market demand for specific materials</li>
                <li>Quality and contamination level of materials</li>
                <li>Global commodity prices</li>
                <li>Quantity of materials being recycled</li>
            </ul>
        </div>
        
        <div class="mt-6 bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-semibold text-green-800 mb-2">Points Rewards System</h3>
            <p class="text-sm text-gray-600">For every kilogram of material recycled, you earn points that can be redeemed for various rewards:</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                <div class="flex items-center">
                    <i class="fa-solid fa-gift text-green-600 mr-2"></i>
                    <span class="text-sm">Vouchers & Discounts</span>
                </div>
                <div class="flex items-center">
                    <i class="fa-solid fa-ticket text-green-600 mr-2"></i>
                    <span class="text-sm">Event Tickets</span>
                </div>
                <div class="flex items-center">
                    <i class="fa-solid fa-store text-green-600 mr-2"></i>
                    <span class="text-sm">Partner Rewards</span>
                </div>
            </div>
        </div>
        
        <div class="mt-8 flex flex-col md:flex-row justify-between items-center">
            <div class="text-gray-600 text-sm">
                <p>Have questions about our pricing? <a href="#" class="text-blue-600 hover:underline">Contact us</a> for more information.</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="customer.php" class="btn-green text-white font-bold py-2 px-6 rounded-lg inline-flex items-center">
                    <i class="fa-solid fa-recycle mr-2"></i> Start Recycling
                </a>
            </div>
        </div>
    </main>
	<?php include('footer.php');?> <!-- footer -->
</div>

<script>
    function toggleMenu() 
	{
		// kan declare md:block kat css tailwind tu, so whenever the screen size of the webpage below medium which is 768px, all those menubar atas tu will shrink into hamburger menu
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
        menu.classList.toggle('block');
    }
</script>
</body>
</html>
