<?php 
require_once(__DIR__.'/../functions.php');
$mysqli = DbConnect();
$all_theses = getAllTheses($mysqli, true);

header('Content-Type: application/json');
echo json_encode($all_theses);
?>