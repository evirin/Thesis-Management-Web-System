<?php 
require_once(__DIR__.'/../functions.php');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	if (isset($_POST['thesis_id']) && $_POST['thesis_id'] != '' && isset($_POST['professor_id']) && $_POST['professor_id'] != '') {
		$thesis_id = $_POST['thesis_id'];
		$professor_id = $_POST['professor_id'];

		echo json_encode(sendInvite($thesis_id, $professor_id, $mysqli));
	}
}

?>