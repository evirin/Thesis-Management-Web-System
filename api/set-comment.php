<?php 
require_once(__DIR__.'/../functions.php');

if (isset($_POST['instructor']) && $_POST['instructor'] != '' && isset($_POST['comment']) && $_POST['comment'] != '' && isset($_POST['thesis_id']) && $_POST['thesis_id'] != '') {
	
	$mysqli = DbConnect();
	$thesis_id = $_POST['thesis_id'];
	$instructor = $_POST['instructor'];
	$comment = $_POST['comment'];

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode(setComment($thesis_id, $instructor, $comment, $mysqli));
}
?>