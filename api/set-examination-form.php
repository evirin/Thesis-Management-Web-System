<?php 
require_once(__DIR__.'/../functions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $thesis_id = $_POST['thesis'];
    $student_id = $_POST['student'];
    $links = $_POST['links'];
    $method = $_POST['examination_method'];
    $place = $_POST['place'];
    $date = $_POST['examination_date'];
    $uploaded_files = isset($_FILES) ? $_FILES['file'] : null;

    $mysqli = DbConnect();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(updateExaminationFields($thesis_id, $student_id, $links, $method, $place, $date, $uploaded_files, $mysqli));
}


?>