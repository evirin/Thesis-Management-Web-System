<?php 
require_once(__DIR__.'/../functions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	$thesis_id = $_POST['thesis'];
	$text_link = $_POST['text_link'];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(setFinalText($thesis_id, $text_link, $mysqli));
}