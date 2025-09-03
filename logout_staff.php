<?php
include('config.php');
include('sessionstaff.php');

// Clear staff session
clearStaffSession();

// Set success message
setSessionMessage('You have been logged out successfully.');

// Redirect to staff login page
header('Location: login_staff.php');
exit();
?>