<?php 
require_once(__DIR__.'/../functions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	$thesis_id =  $_POST['thesis'];
	$ap_number = $_POST['ap_number'] ?: NULL;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(setAp($thesis_id, $ap_number, $mysqli));
}