<?php 
$mysqli = DbConnect();
if (getLoggedUser($mysqli)['role'] == 1) {
	$student_id = getLoggedUser($mysqli)['id'];
	$student_data = getStudent($student_id, $mysqli);
}
?>
<h1>
	Student Profile
</h1>

<div class="student-profile">
	<h2>
		PERSONAL INFORMATION	
	</h2>
	<div class="student-id">
		<div class="contact-wrapper">
			<div class="student-name">
				<?php echo $student_data[0]['name'].' '.$student_data[0]['surname'] ?>
			</div>
			<div class="fathername">
				<?php echo $student_data[0]['father_name'] ?>
			</div>
			<div class="student-number">
				<?php echo $student_data[0]['student_number'] ?>
			</div>
			
		</div>
		<form id="email-info" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/update-email-info.php' ?>">
			<input type="hidden" name="student" value="<?php echo $student_id ?>">
			<input type="email" name="email" placeholder="Your email" value="<?php echo $student_data[0]['email'] ?>">

			<div class="submit-form" data-target="email-info">
				Save Changes
			</div>
		</form>
	</div>

	<h2>
		CONTACT INFORMATION	
	</h2>
	<div class="student-id">
		<form id="contact-info" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/update-contact-info.php' ?>">
			<input type="hidden" name="student" value="<?php echo $student_id ?>">
			<div class="contact-wrapper">
				<input type="text" name="city" placeholder="City"  value="<?php echo $student_data[0]['city'] ?>">
			</div>

			<div class="contact-wrapper">
				<input type="text" name="street" placeholder="Street" value="<?php echo $student_data[0]['street'] ?>">
				<input type="text" name="number" placeholder="Number"  value="<?php echo $student_data[0]['number'] ?>">
				<input type="text" name="postcode" placeholder="Postal code"  value="<?php echo $student_data[0]['postcode'] ?>">
			</div>

			<div class="contact-wrapper">
				<input type="text" name="landline_telephone" placeholder="Landline number"  value="<?php echo $student_data[0]['landline_telephone'] ?>">
				<input type="text" name="mobile_telephone" placeholder="Mobile number"  value="<?php echo $student_data[0]['mobile_telephone'] ?>">
			</div>

			<div class="submit-form" data-target="contact-info">
				Save Changes
			</div>
		</form>
	</div>
</div>
