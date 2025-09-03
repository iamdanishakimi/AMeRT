<?php
include('config.php');
include('sessionstaff.php');

// Only allow AJAX requests
if (!isStaffLoggedIn() || !isAdminStaff()) 
{
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Get count of new transactions since last check
$last_check = $_SESSION['last_transaction_check'] ?? 0;
$sql = "SELECT COUNT(*) as count FROM recycling_transactions WHERE status = 'pending' AND created_at > FROM_UNIXTIME($last_check)";
$result = $connect->query($sql);
$count = 0;

if ($result && $row = $result->fetch_assoc()) 
{
    $count = $row['count'];
}

// Update last check time
$_SESSION['last_transaction_check'] = time();

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['new_transactions' => $count]);
?>