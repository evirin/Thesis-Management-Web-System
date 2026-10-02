<?php 
require_once(__DIR__.'/../functions.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic = $_POST['topic'];
    $summary = $_POST['summary'];
    $instructor = $_POST['instructor'];
    $student = (isset($_POST['student']) && $_POST['student'] != '') ? $_POST['student'] : null;
    $status = (isset($_POST['status']) && $_POST['status'] != '') ? $_POST['status'] : null;
    $assigned_on = (isset($_POST['assigned_on']) && $_POST['assigned_on'] != '') ? $_POST['assigned_on'] : null;
    $created_at = date('Y-m-d H:i:s');
    $uploaded_files = isset($_FILES) ? $_FILES['files'] : null;

    $mysqli = DbConnect();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(createThesis($topic, $summary, $instructor, $student, $status, $uploaded_files, $assigned_on, $mysqli));
}


?>