<?php
$mysqli = DbConnect();

if (getLoggedUser($mysqli)['role'] == 1){

	$student_id = getLoggedUser($mysqli)['id'];
	if (getStudentThesis($student_id, $mysqli)) {
		$thesis_id = getStudentThesis($student_id, $mysqli)['id'];
		$thesis_data = getThesis($thesis_id, $mysqli);
	}
	
?>
<h1>EXAMINATION FIELDS</h1>
<div class="examination-fields">
	<?php 
	if (isset($thesis_data) && $thesis_data[0]['status']['id'] == 4) {
	?>
	<form id="examination-student-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-examination-form.php' ?>" enctype="multipart/form-data">
		<input type="hidden" name="thesis" value="<?php echo $thesis_id ?>">
		<input type="hidden" name="student" value="<?php echo $student_id ?>">
		<label for="draft-file">DRAFT FILE</label>
		<div class="draft-wrapper">
			<input id="draft-file" type="file" name="file">
			<?php 
			if ($thesis_data[0]['draft_text']) {
			?>
			<div class="file-wrapper">
				<i class="fa-regular fa-file-lines" title="<?php echo $thesis_data[0]['draft_text']; ?>"></i>
			</div>
			<?php
			}
			?>
		</div>
		
		<label for="extra-links">EXTRA LINKS</label>
		<div class="links-wrapper">
		<?php 
		if (is_array($thesis_data[0]['links']) && count($thesis_data[0]['links']) > 0) {
			foreach ($thesis_data[0]['links'] as $link) {
			?>
			<input id="extra-links" type="text" name="links[]" placeholder="Your link here (Youtube, Drive files, etc.)" value="<?php echo $link['file_name'] ?>">
			<?php
			}
		}
		else{
		?>
		<input id="extra-links" type="text" name="links[]" placeholder="Your link here (Youtube, Drive files, etc.)">
		<?php
		}
		?>
		</div>
		<button type="button" class="add-links">Add more links</button>
		<label>EXAMINATION METHOD</label>
		<div class="method-wrapper">
			<select name="examination_method" id="cars">
			    <option value="1">In-Person</option>
			    <option value="2">Online</option>
			 </select>
			 <input type="text" name="place" placeholder="Your link or room here" value="<?php echo $thesis_data[0]['examination_place'] ?>">
		</div>
		<label for="birthdaytime">EXAMINATION DATE</label>
		<input type="datetime-local" id="examination-date" name="examination_date" value="<?php echo $thesis_data[0]['examination_date'] ?>"> 
		<small>* Please select the examination date and time that you agreed with the committee </small>
	</form>
	<div class="examination-fields-extras">
		
		<h2>THESIS INFO</h2>
		<div class="extra-info">
			<div class="info">
				<span>Instructor: </span>
				<span>
					<?php echo  $thesis_data[0]['instructor']['name'].' '.$thesis_data[0]['instructor']['surname'] ?>
				</span>
			</div>
			<div class="info">
				<span>Assigned on: </span>
				<span>
					<?php echo 'Create assign on field' ?>
				</span>
			</div>
			<div class="info thesis-status-<?php echo $thesis_data[0]['status']['id']  ?>">
				<span>Status: </span>
				<span>
					<?php echo $thesis_data[0]['status']['status'] ?>
				</span>
			</div>
			<div class="info committee-info">
				<div>Committee: </div>
				<div class="committee">
					<?php 
					foreach ($thesis_data[0]['committee_members'] as $committee_member) {
					?>
					<div class="committee-member">
						<div class="member-name">
							<?php echo $committee_member['name'].' '.$committee_member['surname'] ?>
						</div>
					</div>
					<?php
					}
					?>
				</div>
				
			</div>
		</div>
		<div class="submit-form" data-target="examination-student-form">
			Submit
		</div>
	</div>
	<?php
	}
	else{
	?>
	<p>
		Your thesis is not under examination yet or no thesis is assigned to you.
	</p>
	<?php
	}
	?>
</div>
<?php
}

?>
