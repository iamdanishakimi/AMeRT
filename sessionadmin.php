<?php
//session_start();

/* Check if admin is logged in
function isAdminLoggedIn() 
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
*/


// Redirect to admin page if already logged in
function redirectIfLoggedIn() 
{
    if (isAdminLoggedIn()) 
	{
        header('Location: admin.php');
        exit();
    }
}

// Set admin session variables
function setAdminSession($admin) 
{
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_name'] = $admin['admin_name'];
    $_SESSION['admin_username'] = $admin['admin_username'];
    $_SESSION['admin_email'] = $admin['admin_email'];
}

// Clear admin session (logout)
function clearAdminSession()
 {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
}

// Get session message and clear it
function getSessionMessage() 
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return $message;
    }
    return null;
}

// Set session message
function setSessionMessage($message)
{
    $_SESSION['message'] = $message;
}
?>