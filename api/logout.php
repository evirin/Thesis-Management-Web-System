<?php 
require_once(__DIR__.'/../functions.php');

$return_object = logout();

header('Content-Type: application/json; charset=utf-8');

echo json_encode($return_object);