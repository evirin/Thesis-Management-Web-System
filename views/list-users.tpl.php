<?php 
$mysqli = DbConnect();

$students = getStudents($mysqli, false);
$professors = getUsers($mysqli, 2);

?>
<div class="users-header">
	<h1>
		VIEW USERS
	</h1>
	<label>UPLOAD USER FILE</label>
	<form id="set-users" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-users.php' ?>" enctype="multipart/form-data" data-preload="preload">
    	<input type="file" name="files[]" multiple accept=".json">
    	<div class="submit-form" data-target="set-users">
			Upload Users
		</div>
	</form>
	
</div>
<h2>
	STUDENTS
</h2>
<div class="user-listing">
	<?php
	foreach ($students as $student) {
	?>
	<div class="user">
		<div class="user-wrapper">
			<div class="name"><?php echo $student['name'].' '.$student['surname']; ?></div>
			<div class="student-number"><?php echo $student['student_number']; ?></div>
		</div>
		<div class="user-email">
			<?php echo $student['email']; ?>
		</div>
	</div>
	<?php
	}
	?>
</div>
<h2>
	Professors
</h2>
<div class="user-listing">
	<?php
	foreach ($professors as $professor) {
	?>
	<div class="user">
		<div class="user-wrapper">
			<div class="name"><?php echo $professor['name'].' '.$professor['surname']; ?></div>
		</div>
		<div class="user-email">
			<?php echo $professor['email']; ?>
		</div>
	</div>
	<?php
	}
	?>
</div>

