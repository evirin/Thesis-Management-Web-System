<?php 
require_once(__DIR__.'/../functions.php');

if (isset($_GET['url'])) {
	$url = $_GET['url'];
}



header('Content-Type: application/json; charset=utf-8');

echo json_encode(getRoute($url));

?>