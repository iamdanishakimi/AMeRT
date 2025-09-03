<?php
// Session functions for staff - NO session_start() here!

// Check if staff is logged in
function isStaffLoggedIn() 
{
    return isset($_SESSION['staff_logged_in']) && $_SESSION['staff_logged_in'] === true;
}

// Redirect to staff page if already logged in
function redirectIfStaffLoggedIn() 
{
    if (isStaffLoggedIn()) 
	{
        $role = $_SESSION['staff_dept'] ?? '';
        if ($role === 'admin') {
            header('Location: admin_staff.php');
        } else {
            header('Location: staff.php');
        }
        exit();
    }
}

function setStaffSession($staff) 
{
    $_SESSION['staff_logged_in'] = true;
    $_SESSION['staff_id'] = $staff['staff_id'];
    $_SESSION['staff_name'] = $staff['staff_name'];
    $_SESSION['staff_ic'] = $staff['staff_ic']; // Gunakan staff_ic sebagai username
    $_SESSION['staff_dept'] = $staff['staff_dept'];
    // staff_email tidak ada di tabel, jadi dihapus
}

// Clear staff session (logout)
function clearStaffSession() 
{
    unset($_SESSION['staff_logged_in']);
    unset($_SESSION['staff_id']);
    unset($_SESSION['staff_name']);
    unset($_SESSION['staff_username']);
    unset($_SESSION['staff_dept']);
}

// Check if staff has admin role
function isAdminStaff() {
    return ($_SESSION['staff_dept'] ?? '') === 'admin';
}

// Check if staff has weight role
function isWeightStaff() {
    return ($_SESSION['staff_dept'] ?? '') === 'weight';
}

// Get session message and clear it
function getSessionMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

// Set session message
function setSessionMessage($message) {
    $_SESSION['message'] = $message;
}
?>