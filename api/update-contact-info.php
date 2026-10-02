<?php  
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();

	$student_id = $_POST['student'];
	$city = $_POST['city'];
	$street = $_POST['street'];
	$number = $_POST['number'];
	$postcode = $_POST['postcode'];
	$landline_telephone = $_POST['landline_telephone'];
	$mobile_telephone = $_POST['mobile_telephone'];

	echo json_encode(updateStudentDetails($student_id, $city, $street, $number, $postcode, $landline_telephone, $mobile_telephone, $mysqli));
}

?>