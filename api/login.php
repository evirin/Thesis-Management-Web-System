<?php 
require_once(__DIR__.'/../functions.php');

if (isset($_POST['email']) && isset($_POST['password']) && $_POST['email'] != '' && $_POST['password'] != '') {

	$email = $_POST['email'];
	$password = $_POST['password'];

	$mysqli = DbConnect();

	$return_object = login($email, $password, $mysqli);

}
else{
	$return_object['message']['type'] = 'error';
	$return_object['message']['content'] = "Please fill all the fields";
}

header('Content-Type: application/json; charset=utf-8');

echo json_encode($return_object);

?>