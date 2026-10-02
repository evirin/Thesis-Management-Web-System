<?php 
require_once(__DIR__.'/../functions.php');
$mysqli = DbConnect();
$thsis_id = $_GET['thesis_id'];
$the_thesis = getThesis($thsis_id, $mysqli);

header('Content-Type: application/json');
echo json_encode($the_thesis);
?>