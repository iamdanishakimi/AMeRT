
<?php
// start session for user and admin authentication
session_start();

// database connection settings integration
$db_host = 'localhost'; // database host
$db_username = 'root'; // database username
$db_password = ''; // database password
$db_name = 'amert'; // database name for e-RecycloTrack

// establish database connection
$connect = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($connect->connect_error)
{
    die('Connection failed: ' . $connect->connect_error);
}

// system settings
$link = 'http://localhost/amert'; // base URL for the application
$title = 'AMeRT'; // Application title

// variables for form handling
// sanitize inputs to prevent SQL injection
$ic_number = $connect->real_escape_string(isset($_POST['ic_number']) ? $_POST['ic_number'] : '');
$full_name = $connect->real_escape_string(isset($_POST['full_name']) ? $_POST['full_name'] : '');
$phone_number = $connect->real_escape_string(isset($_POST['phone_number']) ? $_POST['phone_number'] : '');
$item_type = $connect->real_escape_string(isset($_POST['item_type']) ? $_POST['item_type'] : '');
$weight = $connect->real_escape_string(isset($_POST['weight']) ? $_POST['weight'] : '');
$username = $connect->real_escape_string(isset($_POST['username']) ? $_POST['username'] : '');
$password = $connect->real_escape_string(isset($_POST['password']) ? $_POST['password'] : '');
$start_date = $connect->real_escape_string(isset($_POST['start_date']) ? $_POST['start_date'] : '');
$end_date = $connect->real_escape_string(isset($_POST['end_date']) ? $_POST['end_date'] : '');
$report_type = $connect->real_escape_string(isset($_POST['report_type']) ? $_POST['report_type'] : '');

// retrieve semua jenis item that declared in database for pricing and name, save in array
$item_prices = [];
$sql = "SELECT type_name, price_per_kg, points_per_kg FROM item_types";
$result = $connect->query($sql);
if ($result) 
{
	// fetch_assoc = fetch data from database by row but all data
    while ($row = $result->fetch_assoc()) 
	{
        $item_prices[$row['type_name']] = 
		[
            'price' => $row['price_per_kg'],
            'points' => $row['points_per_kg']
        ];
    }
}

// session message for feedback
$_SESSION['message'] = isset($_SESSION['message']) ? $_SESSION['message'] : '';

//utility functions
// set semua harga into 2 decimal points 
function formatCurrency($amount) 
{
    return 'RM' . number_format($amount, 2);
}

// function to show message
function showAlert($message) 
{
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
        ' . $message . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close"></button>
    </div>';
}

// check kalau admin log in
function isAdminLoggedIn() 
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// if authentication fail, system will redirect back to login page by default. session terminate 
function requireAdminAuth() 
{
    if (!isAdminLoggedIn()) 
	{
		// kalau bukan admin yang login, system will redirect to login page
        header('Location: login.php');
        exit();
    }
}

// retrieve current admin data from db
function getAdminInfo() 
{
    if (isAdminLoggedIn()) 
	{
		// kalau authentication success, system will save data admin in array to keep the session stay sampai la user click button logout
        return 
		[
            'id' => $_SESSION['admin_id'],
            'name' => $_SESSION['admin_name'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email']
        ];
    }
    return null;
}

// Function to check if current page matches the link
function isActivePage($pageName) 
{
    $currentPage = basename($_SERVER['PHP_SELF']);
    return ($currentPage == $pageName) ? 'bg-blue-100 text-blue-800 font-semibold' : 'text-gray-700 hover:bg-gray-100';
}

// function to generate PDF report
function generatePDFReport($connect, $start_date, $end_date, $report_type) 
{
    // This function would use Dompdf to generate PDF
    // For now, we'll just redirect to the PDF generator
    header('Location: generate_pdf.php?start_date=' . $start_date . '&end_date=' . $end_date . '&report_type=' . $report_type);
    exit();
}
?>
