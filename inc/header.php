<?php 
$mysqli = DbConnect();
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Thesis Manager</title>
	<script src="https://kit.fontawesome.com/5526e148e6.js" crossorigin="anonymous"></script>
	<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="<?php echo BASE_URL.BASE_PATH.'/assets/css/style.css' ?>">
	
</head>
<body>
	
	<div class="left-bar">
		<div class="close-menu">
			<i class="fa-solid fa-xmark"></i>
		</div>
		<div class="brand">
			<img src="<?php echo BASE_URL.BASE_PATH.'/assets/img/logo.png' ?>">
		</div>
		<ul class="main-menu">
			<?php 
			if (getLoggedUser($mysqli)) {
				$user_role = getLoggedUser($mysqli)['role'];
				foreach ($main_menu as $menu_item) {
					if (in_array($user_role, $menu_item['access'])) {
						?>
						<li>
							<a class="link" href="<?php echo $menu_item['link'] ?>">
								<?php echo $menu_item['icon'] ?><span><?php echo $menu_item['text'] ?></span>
							</a>
						</li>
						<?php
					}
				}
			}
			
			?>
		</ul>
		<form class="logout-form" action="<?php echo BASE_URL.BASE_PATH.'/api/logout.php' ?>" method="GET" data-preload="closeNotifications">
			<button type="submit">LOGOUT</button>
		</form>
	</div>
	<div class="top-bar">
		<div class="mobile-brand">
			<img src="<?php echo BASE_URL.BASE_PATH.'/assets/img/logo.png' ?>">
		</div>
		<?php 
		if (getLoggedUser($mysqli)) {
			$user_role = getLoggedUser($mysqli)['role'];
		?>
		<div class="connected-user">
			<?php echo getLoggedUser($mysqli)['email'] ?>
		</div>
		<?php
		if ($user_role == 2) {
			$all_invites = getInvites($mysqli);
			if(count($all_invites) > 0){
				$bell_icon = 'fa-solid';
			}
			else{
				$bell_icon = 'fa-regular';
			}
		?>
		<div class="notifications">
			<i class="<?php echo $bell_icon ?> fa-bell"></i>
		</div>
		<?php
		}
		}
		?>
		<div class="mobile-toggle">
			<i class="fa-solid fa-bars"></i>
		</div>
	</div>
	<?php 
	if (getLoggedUser($mysqli) && getLoggedUser($mysqli)['role'] == 2) {
		$invites_count = count($all_invites);
	}
	else{
		$invites_count = 0;
	}
	?>
	<div class="notification-bar">
		<div class="invites-wrapper">
			<h2>
			Student Invites
			</h2>
			<div class="all-invites">
				<div class="invites-inner-wrapper" data-count=<?php echo $invites_count ?>>
					
				</div>
			</div>
		</div>
	</div>