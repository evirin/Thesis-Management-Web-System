<?php
require_once(__DIR__.'/../functions.php');
$mysqli = DbConnect();


if (getLoggedUser($mysqli)['role'] == 2) {
	$instructor_id = getLoggedUser($mysqli)['id'];
	$instructor_theses= getInstructorTheses($instructor_id, $mysqli);
	$committee_theses = getInstructorThesesAsCommittee($instructor_id, $mysqli);

	$all_data = [
		'as_instructor' => $instructor_theses,
		'as_committee' => $committee_theses,
	];

	header('Content-Type: application/json; charset=utf-8');

	echo json_encode($all_data);
}



?>
