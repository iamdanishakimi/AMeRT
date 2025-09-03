<?php

// if the session is not riggered by the system, it will lead to login semula
if(!isset($_SESSION['admin_id']))
{
	header('Location:'.$link.'/admin/login.php');
	exit();
}

$sql_login = "SELECT * FROM admin WHERE admin_id = '".$_SESSION['admin_id']."'";
if($result_login = $connect->query($sql_login))
{
	$rows_login = $result_login->fetch_array();
	if(!$total_login = $result_login->num_rows)
	{
		header('Location:'.$link.'/admin/login.php');
		exit();
	}
}
else
{
	header('Location:'.$link.'/admin/login.php');
	exit();
}
?>
