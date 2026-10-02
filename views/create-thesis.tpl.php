

<?php 
	$mysqli = DbConnect();
	$connected_user = getUser($id = $_SESSION['user_id'], $mysqli);

	$students = getUsers($mysqli, 1);

?>

<div class="thesis-panel">
	<div class="main-panel">
		<form id="create-thesis-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-thesis.php' ?>" enctype="multipart/form-data">
			<label for="thesis_title">THESIS TOPIC</label>
			<input id="thesis_title" type="text" name="topic" placeholder="Thesis topic here" required>
			<input type="hidden" name="instructor" value="<?php echo $connected_user['id'] ?>" required>
			<input type="hidden" name="student" value="">
			<input type="hidden" name="status" value="1" required>
			<input type="hidden" name="assigned_on" value="">
			<label for="thesis_summary">THESIS SUMMARY</label>
			<textarea id="thesis_summary" name="summary" placeholder="Thesis summary here" rows="8" required></textarea>
			<label for="full-description">UPLOAD FULL DESCRIPTION</label>
			<input id="full-description" type="file" name="files[]" multiple accept=".pdf">
		</form>
		<div id="preview-uploaded-files">
			
		</div>
	</div>


	<div class="extras-panel">
		<h2>EXTRA INFO</h2>
		<div class="top-panel">
			<div class="extras-field status-extra-field">
			<div class="extra-label">Thesis status:</div><span class="status-1">Unassigned</span>
			</div>
			<div class="extras-field">
				<div class="extra-label">Instructor:</div><span class=""><?php echo $connected_user['name'].' '.$connected_user['surname'] ?></span>
			</div>
			<div class="submit-form" data-target="create-thesis-form">
				Publish
			</div>
		</div>
		<h2>ASSIGN TO</h2>
		<div class="bottom-panel">
			<div class="select-student student-status-">
				<span>No student assigned</span>
				
			</div>
			<div class="student-list">
					<div class="student" data-user="" data-status="1" data-status-name="Unassigned" data-time="">No student assigned</div>
					<?php 
					foreach ($students as $student) {
					?>
					<div class="student" data-user="<?php echo $student['id'] ?>" data-status="2" data-status-name="Under Assignment" data-time="<?php echo date("Y-m-d H:i:s") ?>"><?php echo $student['name'].' '.$student['surname'] ?></div>
					<?php
					}
					?>
				</div>
		</div>
	</div>
</div>