<?php 
require_once(__DIR__.'/../functions.php');
$mysqli = DbConnect();
$all_invites = getInvites($mysqli);

header('Content-Type: application/json');
echo json_encode($all_invites);
?>