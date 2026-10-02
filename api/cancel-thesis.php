<?php 
require_once(__DIR__.'/../functions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$mysqli = DbConnect();
	$thesis_id =  $_POST['thesis'];
	$gen_number = $_POST['gen_number'] ?: NULL;
	$gen_year = $_POST['gen_year'] ?: NULL;
	$reason = $_POST['can_reason'] ?: NULL;

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(cancelThesis($mysqli, $thesis_id, $gen_number, $gen_year, $reason));
}