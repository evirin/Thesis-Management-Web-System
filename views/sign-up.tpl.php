
<form id="sign-up-form" class="login-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-user.php' ?>">
	<input type="text" name="name" placeholder="Firstname" required>
	<input type="text" name="surname" placeholder="Lastname" required>
	<input type="email" name="email" placeholder="Email" required>
	<input type="password" name="password" placeholder="Password" required>
	<input type="number" name="role" placeholder="role">
	<div class="professor_fields">
		<input type="text" name="topic" placeholder="topic">
		<input type="text" name="landline" placeholder="landline">
		<input type="text" name="mobile" placeholder="mobile">
		<input type="text" name="department" placeholder="department">
		<input type="text" name="university" placeholder="university">
	</div>
	<div class="student-fields">
		<input type="text" name="student_number" placeholder="student_number">
		<input type="text" name="street" placeholder="landline">
		<input type="text" name="number" placeholder="number">
		<input type="text" name="city" placeholder="city">
		<input type="text" name="postcode" placeholder="postcode">
		<input type="text" name="mobile_telephone" placeholder="mobile_telephone">
		<input type="text" name="landline_telephone" placeholder="landline_telephone">
		<input type="text" name="father_name" placeholder="father_name">
	</div>
	<button type="submit">Submit</button>
</form>