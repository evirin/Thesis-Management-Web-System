<?php
if (isset($id)) {
	$mysqli = DbConnect();

	$thesis_data = getThesis($id, $mysqli);
	$students = getStudents($mysqli, true);
	$uploads = getUploads($id, $mysqli);
	$connected_user = getLoggedUser($mysqli)['id'];
	$role = getLoggedUser($mysqli)['role'];
	$instructor_mode = false;
	$editable = false;
	$editable_attr = 'disabled';
	$editable_class = 'disabled';
	$editabled_icon = '<i class="fa-solid fa-lock"></i>';
	$committee_ids = array_column($thesis_data[0]['committee_members'], 'user_id');
	$committee_mode = false;
	$sec_mode = false;

	if ($thesis_data[0]['assigned_on']) {
		$assigned = strtotime($thesis_data[0]['assigned_on']);
		$now =  strtotime(date('Y-m-d H:i:s'));
		$time_passed = getTimePassed($assigned, $now);
	}
	
	if ($thesis_data[0]['instructor']['id'] == $connected_user) {
		$instructor_mode = true;
	}

	if ($instructor_mode && $thesis_data[0]['status']['id'] < 3) {
		$editable = true;
		$editable_attr = '';
		$editable_class = '';
		$editabled_icon = '<i class="fa-solid fa-lock-open"></i>';
	}

	if (in_array($connected_user, $committee_ids)) {
		$committee_mode = true;
	}

	if ($role == 3) {
		$sec_mode = true;
	}

?>
<div class="thesis-panel">
	<div class="main-panel">
		<form id="update-thesis-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/update-thesis.php' ?>" enctype="multipart/form-data">
			<label class="<?php echo $editable_class ?>" for="thesis_title">THESIS TOPIC <?php echo $editabled_icon ?></label>
			<input id="thesis_title" type="text" name="topic" placeholder="Thesis title" value="<?php echo $thesis_data[0]['topic'] ?>" required <?php echo $editable_attr ?> >
			<input type="hidden" name="instructor" value="<?php echo $thesis_data[0]['instructor']['id'] ?>" required <?php echo $editable_attr ?>>
			<input type="hidden" name="student" value="<?php echo $thesis_data[0]['student']['id'] ?>" <?php echo $editable_attr ?>>
			<input type="hidden" name="status" value="<?php echo $thesis_data[0]['status']['id'] ?>" <?php echo $editable_attr ?>>
			<input type="hidden" name="old_status" value="<?php echo $thesis_data[0]['status']['id'] ?>" <?php echo $editable_attr ?>>
			<input type="hidden" name="assigned_on" value="<?php echo $thesis_data[0]['assigned_on'] ?>" <?php echo $editable_attr ?>>
			<input type="hidden" name="thesis_id" value="<?php echo $id ?>" <?php echo $editable_attr ?>>
			<label class="<?php echo $editable_class ?>" for="thesis_summary">THESIS SUMMARY <?php echo $editabled_icon ?></label>
			<textarea id="thesis_summary" name="summary" placeholder="Summary" rows="8" <?php echo $editable_attr ?>><?php echo $thesis_data[0]['summary'] ?></textarea>
			<label class="<?php echo $editable_class ?>" for="full-description">UPLOAD FULL DESCRIPTION <?php echo $editabled_icon ?></label>
			<input type="file" name="files[]" multiple <?php echo $editable_attr ?> accept=".pdf">
		</form>
		<div id="preview-uploaded-files">
			<?php 
			foreach ($uploads as $upload ) {
			?>
			<div class="uploaded-file">
				<i class="fa-regular fa-file-pdf"></i>
				<span class="uploaded-filename" title="<?php echo $upload['filename'] ?>">
					<?php echo $upload['filename'] ?>
				</span>
			</div>
			<?php
			}
			?>
		</div>

		<?php 
		if ($thesis_data[0]['status']['id'] == 3 && $committee_mode) {
		?>
		<div class="comments-panel">
			<form id="add-new-comment" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-comment.php' ?>">
				<input type="hidden" name="instructor" value="<?php echo $connected_user ?>">
				<input type="hidden" name="thesis_id" value="<?php echo $id ?>">
				<label for="comment">ADD COMMENT</label>
				<textarea id="comment" name="comment" placeholder="Add a comment" rows="8" maxlength="300"></textarea>
				<div class="submit-form" data-target="add-new-comment">
					Add comment
				</div>
			</form>
			<h2>VIEW YOUR COMMENTS</h2>
			<div class="comments">
				<?php
				$comments =  getComments($id, $connected_user, $mysqli);
				if ($comments) {
					foreach ($comments as $comment) {
					?>
					<div class="comment" data-comment="<?php echo $comment[0] ?>">
						<div class="comment-date">
							<?php echo $comment[2] ?>
						</div>
						<div class="comment-text">
							<?php echo $comment[1] ?>
						</div>
					</div>
					<?php
					}
				}
				?>
			</div>
		</div>
		<?php
		}
		?>

		
	</div>

	<div class="extras-panel">
		<h2>EXTRA INFO</h2>
		<div class="top-panel">
			<div class="extras-field status-extra-field">
				<div>Status: <i class="fa-regular fa-pen-to-square"></i></div>
				<span class="status-<?php echo $thesis_data[0]['status']['id'] ?>">
					<?php echo $thesis_data[0]['status']['status'] ?>		
				</span>
				<?php 
				if ($instructor_mode) {
					if ($thesis_data[0]['status']['id'] == 3 || $thesis_data[0]['status']['id'] == 4 ) {
					?>	
					<div class="change-status-panel">
						<div class="change-status" data-thesis="<?php echo $id ?>" data-status="3">Active</div>
						<?php 
						if ($thesis_data[0]['ap_number']){
						?>
						<div class="change-status" data-thesis="<?php echo $id ?>" data-status="4">Under Examination</div>
						<?php
						}
						if (isset($time_passed) && $time_passed >= 2) {
						?>
						<div class="change-status cancel-thesis" data-status="6" data-reason="By professor">Cancelled</div>
						<?php
						}
						?>
					</div>
					<?php
					}
				}
				if ($sec_mode && $thesis_data[0]['status']['id'] == 3) {
				?>
				<div class="change-status-panel">
					<div class="change-status" data-thesis="<?php echo $id ?>" data-status="3">Active</div>
					<div class="change-status cancel-thesis" data-status="6">Cancelled</div>
				</div>
				<?php
				}
				if ($sec_mode && $thesis_data[0]['final_text_file'] != NULL) {
				?>
				<div class="change-status-panel">
					<div class="change-status" data-thesis="<?php echo $id ?>" data-status="4">Under Examination</div>
					<div class="change-status" data-thesis="<?php echo $id ?>" data-status="5">Completed</div>
				</div>
				<?php
				}
				?>
			</div>
			<div class="extras-field">
				<div>Instructor:</div><span class=""><?php echo $thesis_data[0]['instructor']['name'].' '.$thesis_data[0]['instructor']['surname'] ?></span>
			</div>
			<div class="extras-field">
				<?php 
				$date = new DateTime($thesis_data[0]['created_on']);
				?>
				<div>Created on:</div><span class=""><?php echo $date->format('d M Y'); ?></span>
			</div>
			<?php
			if ($thesis_data[0]['assigned_on']) {
			?>
			<div class="extras-field">
				<?php 
				$date = new DateTime($thesis_data[0]['assigned_on']);
				?>
				<div>Assigned on:</div><span class=""><?php echo $date->format('d M Y'); ?></span>
			</div>
			<?php
			}
			if ($thesis_data[0]['ap_number']) {
			?>
			<div class="extras-field">
				<div>AP Number:</div><span class=""><?php echo $thesis_data[0]['ap_number']; ?></span>
			</div>
			<?php
			}
			if ($thesis_data[0]['final_grade'] && $thesis_data[0]['status']['id'] > 3 && $thesis_data[0]['status']['id'] < 6) {
			?>
			<div class="extras-field">
				<div>Final Grade:</div><span class=""><?php echo $thesis_data[0]['final_grade'] ?></span>
			</div>
			<?php
			}
			if ($thesis_data[0]['status']['id'] >= 4 && $thesis_data[0]['final_grade'] != NULL) {
			?>
			<div class="extras-field">
				<span>Examination report </span>
				<a class="link" href="<?php echo BASE_URL.BASE_PATH.'/examination-report/'.$id ?>">
					View Report
				</a>
			</div>
			<?php
			}
			if ($thesis_data[0]['final_text_file'] != NULL) {
			?>
			<div class="extras-field">
				<div>Final Text File:</div><a href="<?php echo $thesis_data[0]['final_text_file'] ?>" target="blank">View File</a>
			</div>
			<?php
			}
			?>
			
			<div class="submition-wrapper">
				<?php 
				if ($thesis_data[0]['status']['id'] == 4 && $thesis_data[0]['grading_enabled'] == 0 && $instructor_mode) {
				?>
				<form id="enable-grading" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/enable-grading.php' ?>">
					<input type="hidden" name="thesis" value="<?php echo $id ?>">
					<input type="hidden" name="grade_status" value="1">
					<div class="submit-form" data-target="enable-grading">
						Enable Grading
					</div>
				</form>
				<?php
				}
				if ($thesis_data[0]['status']['id'] <= 3 && $instructor_mode) {
				?>
				<div class="submit-form" data-target="update-thesis-form">
					Save Changes
				</div>
				<?php
				}
				?>
			</div>
			
		</div>
		<?php 
		if ($sec_mode && $thesis_data[0]['status']['id'] == 6) {
		?>
		<h2>CANCELLATION INFO</h2>
		<div class="top-panel">
			<?php 
			if (isset(getCancelInfo($id, $mysqli)['gen_number'])) {
			?>
			<div class="extras-field">
				<span>General Assembly Number: </span>
				<span>
					<?php 
					echo getCancelInfo($id, $mysqli)['gen_number'];
					?>
				</span>
			</div>
			<?php
			}
			if (isset(getCancelInfo($id, $mysqli)['gen_date'])) {
			?>
			<div class="extras-field">
				<span>General Assembly Year: </span>
				<span>
					<?php 
					echo getCancelInfo($id, $mysqli)['gen_date'];
					?>
				</span>
			</div>
			<?php
			}
			if (isset(getCancelInfo($id, $mysqli)['reason'])) {
			?>
			<div class="extras-field">
				<span>Cancellation reason: </span>
				<span>
					<?php 
					echo getCancelInfo($id, $mysqli)['reason'];
					?>
				</span>
			</div>
			<?php
			}
			?>
		</div>
		<?php
		}
		if ($sec_mode && $thesis_data[0]['status']['id'] == 3 || $instructor_mode && isset($time_passed) && $time_passed >= 2 && $thesis_data[0]['status']['id'] == 3) {
			if ($instructor_mode) {
				$reason_placeholder = 'By instructor';
			}
			else{
				$reason_placeholder = '';
			}
		?>
		<div class="cancellation-wrapper">
			<h2>CANCEL THESIS</h2>
			<form id="cancel-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/cancel-thesis.php' ?>">
				<input type="hidden" name="thesis" value="<?php echo $id ?>">
				<input type="text" name="gen_number" placeholder="General Assembly Number" required>
				<input type="date" name="gen_year" placeholder="General Assembly Year" required>
				<textarea name="can_reason" placeholder="Cancellation reason" required><?php echo $reason_placeholder ?></textarea>
				<div class="submit-form" data-target="cancel-form">
					Cancel Thesis
				</div>
			</form>
		</div>
		<?php
		}
		if ($thesis_data[0]['grading_enabled'] == 1 && $committee_mode && $thesis_data[0]['status']['id'] < 5) {
		?>
		<h2>SET GRADE</h2>
		<form id="set-grade" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-grade.php' ?>">
			<input type="hidden" name="thesis" value="<?php echo $id ?>">
			<input type="hidden" name="user" value="<?php echo $connected_user ?>">
			<input type="number" name="quality" placeholder="Quiality of thesis" required min="0" max="10">
			<input type="number" name="time" placeholder="Preparation time" required min="0" max="10">
			<input type="number" name="completeness" placeholder="Completeness of text" required min="0" max="10">
			<input type="number" name="image" placeholder="Overall image" required min="0" max="10">
			<div class="submit-form" data-target="set-grade">
				Submit Grade
			</div>
		</form>
		<?php
		}
		if ($sec_mode && $thesis_data[0]['status']['id'] == 3) {
		?>
		<h2>SET AP NUMBER</h2>
		<form id="set-ap" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-ap.php' ?>">
			<input type="hidden" name="thesis" value="<?php echo $id ?>">
			<input type="number" name="ap_number" placeholder="Set the AP number" value="<?php echo $thesis_data[0]['ap_number'] ?>">
			<div class="submit-form" data-target="set-ap">
				Submit AP
			</div>
		</form>
		<?php
		}
		if ($thesis_data[0]['status']['id'] == 4) {
		?>
		<h2>EXAMINATION INFO</h2>
		<div class="top-panel">
			<?php 
			if ($thesis_data[0]['draft_text']) {
			?>
			<div class="extras-field">
				<span>Draft file: </span>
				<a href="<?php echo  UPLOADS_PATH.$thesis_data[0]['draft_text']?>" download>
					Download
				</a>
			</div>
			<?php
			}
			if ($thesis_data[0]['links']) {
			?>
			<div class="extras-field links-info">
				<span>Links: </span>
				<div class="links-wrapper">
					<?php 
					foreach($thesis_data[0]['links'] as $key => $link){
					?>
					<a href="<?php echo $link['file_name'] ?>" style="margin-left: .5rem;">
						<?php echo 'Link '.$key+1 ?>
					</a>
					<?php
					}
					?>
				</div>
			</div>
			<?php
			}
			if ($thesis_data[0]['method']['method']) {
			?>
			<div class="extras-field">
				<span>Examination method: </span>
				<span>
					<?php echo  $thesis_data[0]['method']['method']?>
				</span>
			</div>
			<?php
			}
			if ($thesis_data[0]['examination_place']) {
			?>
			<div class="extras-field">
				<span>Examination room: </span>
				<span>
					<?php echo  $thesis_data[0]['examination_place']?>
				</span>
			</div>
			<?php
			}
			if ($thesis_data[0]['examination_date']) {
			?>
			<div class="extras-field">
				<span>Examination date: </span>
				<span>
					<?php echo  $thesis_data[0]['examination_date']?>
				</span>
			</div>
			<?php
			}
			?>
		</div>
		<?php
		}
		?>
		<h2>ASSIGNED TO</h2>
		<div class="bottom-panel">
			<div class="select-student student-status-<?php echo $thesis_data[0]['status']['id'] ?>">
				<?php 
				if (isset($thesis_data[0]['student']['id'])) {
				?>
					<span><?php echo $thesis_data[0]['student']['name'].' '.$thesis_data[0]['student']['surname'] ?></span>
				<?php
				}
				else{
				?>
					<span>No student assigned</span>
				<?php
				}
				?>
			</div>
			<?php 
			if ($editable) {
			?>
			<input id="search-students" type="text" placeholder="Search students">
			<div class="student-list">
					<div class="student" data-user="" data-status="1" data-status-name="Unassigned" data-time="">No student assigned</div>
					<?php 
					foreach ($students as $student) {
					?>
					<div class="student" data-user="<?php echo $student['id'] ?>" data-status="2" data-status-name="Under Assignment" data-time="<?php echo date("Y-m-d H:i:s") ?>">
						<span class="student-name"><?php echo $student['name'].' '.$student['surname'] ?></span>
						<span class="student-number"><?php echo $student['student_number'] ?></span>
					</div>
					<?php
					}
					?>
			</div>
			<?php
			}
			?>
			
		</div>
		
		<div class="committee-panel">
			<h2>COMMITTEE MEMBERS</h2>
			<?php 
			foreach ($thesis_data[0]['committee_members'] as $committee_member) {
			?>
			<div class="committee-member member-status-<?php echo $committee_member['accepted'] ?>">
				<?php 
				if ($committee_member['user_id'] == $thesis_data[0]['instructor']['id']) {
				?>
				<div class="instructor-label">
					INSTRUCTOR
				</div>
				<?php
				}
				?>
				<div class="member-name">
					<?php echo $committee_member['name'].' '.$committee_member['surname'] ?>
				</div>
				<div class="member-date">

					<div class="invited-date">
						<?php $date = new DateTime($committee_member['invited_on']); ?>
						Invited on: <?php echo $date->format('d/m/y'); ?>
					</div>
					<?php 
					if ($committee_member['accepted'] == 1 ) {
					?>
					<div class="accepted-date">
						<?php $date = new DateTime($committee_member['accepted_on']); ?>
						Accepted on: <?php echo $date->format('d/m/y'); ?>
					</div>
					<?php
					}
					if ($committee_member['accepted'] == '0' ) {
					?>
					<div class="accepted-date">
						Declined on: <?php
							$date = new DateTime($committee_member['accepted_on']);
							echo $date->format('d/m/y');
						?>
					</div>
					<?php
					}
					if ($committee_member['accepted'] == null ) {
						echo 'Pending...';
					}
					if ($thesis_data[0]['grading_enabled'] == 1) {
					?>
					<div class="committee-grade">
						<?php
						if ($committee_member['grade']) {
						 	$grade = $committee_member['grade'];
						}
						else{
							$grade = 'Pending';
						} 
						?>
						Grade: <?php echo $grade ?>
					</div>
					<?php
					}
					?>
					
				</div>
			</div>
			<?php
			}
			?>
		</div>
		<h2>STATUS LOGS</h2>
		<div class="top-panel">
			<?php 
			$logs = getLogs($id, $mysqli);
			foreach ($logs as $log) {
			?>
			<div class="log">
				<div class="log-date">
					<?php 
					$date = new DateTime($log['updated_on']);
					?>
					<small>On</small>
					<span><?php echo $date->format('d/m/y'); ?></span>
					<small>(<?php echo $date->format('H:i'); ?>)</small>	
				</div>
				<div class="log-text"><small>Updated to</small></div>
				<div class="log-status <?php echo 'status-'.$log['status']['id'] ?>"><?php echo $log['status']['status'] ?></div>
			</div>
			<?php
			}
			?>
		</div>
	</div>
</div>

<?php
}
else{
	echo "No Thesis Selected";
}
	
?>

