<?php 
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	$thesis_id = $_POST['thesis'];
	$grade_status = $_POST['grade_status'];

	echo json_encode(enableGrading($thesis_id, $grade_status, $mysqli));
}