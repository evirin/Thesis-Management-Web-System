<?php 
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	$thesis_id = $_POST['thesis'];
	$user_id = $_POST['user'];

	$quality = ($_POST['quality'] * 60) / 100;
	$time = ($_POST['time'] * 15) / 100;
	$completeness = ($_POST['completeness'] * 15) / 100;
	$image = ($_POST['image'] * 10) / 100;

	$calculated_grade = $quality + $time + $completeness + $image;

	echo json_encode( setGrade($thesis_id, $user_id, $calculated_grade, $mysqli));
}

?>