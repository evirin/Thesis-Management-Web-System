<?php 
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	if (isset($_POST['thesis_id']) && $_POST['thesis_id'] != '') {
		$thesis_id = $_POST['thesis_id'];

		echo json_encode(acceptInvite($thesis_id, $mysqli));
	}
}

?>