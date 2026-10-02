<?php
require_once(__DIR__ . '/../functions.php');

// Connect to the database
$mysqli = DbConnect();

// Fetch all theses
$all_theses = getAllTheses($mysqli, true);

// Set headers to indicate file download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="theses.json"');

// Output the JSON data
echo json_encode($all_theses, JSON_PRETTY_PRINT);
?>
