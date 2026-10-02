<?php 
require_once(__DIR__.'/../functions.php');

if (isset($_POST['name']) && isset($_POST['surname']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['role'])) {
	
	$name = $_POST['name'];
	$surname = $_POST['surname'];
	$email = $_POST['email'];
	$password = $_POST['password'];
	$role = $_POST['role'];

	//Professor fields
	$topic = isset($_POST['topic']) ? $_POST['topic'] : NULL;
	$landline = isset($_POST['landline']) ? $_POST['landline'] : NULL;
	$mobile = isset($_POST['mobile']) ? $_POST['mobile'] : NULL;
	$department = isset($_POST['department']) ? $_POST['department'] : NULL;
	$university = isset($_POST['university']) ? $_POST['university'] : NULL;

	//Student fields
	$student_number = isset($_POST['student_number']) ? $_POST['student_number'] : NULL;
	$street = isset($_POST['street']) ? $_POST['street'] : NULL;
	$number = isset($_POST['number']) ? $_POST['number'] : NULL;
	$city = isset($_POST['city']) ? $_POST['city'] : NULL;
	$postcode = isset($_POST['postcode']) ? $_POST['postcode'] : NULL;
	$mobile_telephone = isset($_POST['mobile_telephone']) ? $_POST['mobile_telephone'] : NULL;
	$landline_telephone = isset($_POST['landline_telephone']) ? $_POST['landline_telephone'] : NULL;
	$father_name = isset($_POST['father_name']) ? $_POST['father_name'] : NULL;

	$mysqli = DbConnect();

	header('Content-Type: application/json; charset=utf-8');

	echo json_encode(setUser($mysqli, $name, $surname, $email, $password, $role, $topic, $landline, $mobile, $department, $university, $student_number, $street, $number, $city, $postcode, $mobile_telephone, $landline_telephone, $father_name));
	
}