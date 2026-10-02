<?php  
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();

	$student_id = $_POST['student'];
	$email = $_POST['email'];

	echo json_encode(updateStudentEmail($student_id, $email, $mysqli));
}

?>