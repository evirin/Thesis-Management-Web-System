<?php 
define("BASE_URL","http://localhost");
define("BASE_PATH","/thesis_management");
define("ROOT_PATH", __DIR__);
define("UPLOADS_PATH",BASE_PATH."/uploads/");

define("DB_HOST", "db");
define("DB_USER", 'root');
define("DB_PASSWORD", 'password');
define("DB_NAME", 'thesis_management');


$main_menu = [
	'dashboard' => [
		'link' => BASE_URL.BASE_PATH.'/dashboard',
		'text' => 'Dashboard',
		'access' => [2],
		'icon' => ''
	],
	'assigned-thesis' => [
		'link' => BASE_URL.BASE_PATH.'/assigned-thesis',
		'text' => 'Assigned Thesis',
		'access' => [1],
		'icon' => ''
	],
	'profile' => [
		'link' => BASE_URL.BASE_PATH.'/profile',
		'text' => 'Update Profile',
		'access' => [1],
		'icon' => ''
	],
	'create-thesis' => [
		'link' => BASE_URL.BASE_PATH.'/create-thesis',
		'text' => 'Create thesis',
		'access' => [2],
		'icon' => ''
	],
	'list-thesis' => [
		'link' => BASE_URL.BASE_PATH.'/list-thesis',
		'text' => 'List theses',
		'access' => [2,3],
		'icon' => ''
	],
	'list-users' => [
		'link' => BASE_URL.BASE_PATH.'/list-users',
		'text' => 'List users',
		'access' => [3],
		'icon' => ''
	],
	'examination-fields' => [
		'link' => BASE_URL.BASE_PATH.'/examination-fields',
		'text' => 'Examination Fields',
		'access' => [1],
		'icon' => ''
	],
];
?>