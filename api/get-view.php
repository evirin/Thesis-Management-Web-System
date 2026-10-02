<?php 
require_once(__DIR__.'/../functions.php');

$return_object = [];

if (isset($_POST['view']) && $_POST['view'] != '') {

	$view = $_POST['view'];
	$subpage = [];

	if (isset($_POST['subpages']) && is_array($_POST['subpages'])) {
		$subpage = ['id' => $_POST['subpages'][0]];
	}
	
	getView($view, $subpage);

}
else{
	getView('home');
}
?>