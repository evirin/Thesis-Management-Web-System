<?php 
require_once(__DIR__.'/../functions.php');
$mysqli = DbConnect();
header('Content-Type: application/json');
if (getLoggedUser($mysqli)) {
	$role = getLoggedUser($mysqli)['role'];
}
else{
	$role = 0;
}
echo json_encode($role);
?>