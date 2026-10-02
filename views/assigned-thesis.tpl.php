<?php
$mysqli = DbConnect();

if (getLoggedUser($mysqli)['role'] == 1){

	$student_id = getLoggedUser($mysqli)['id'];
	if (getStudentThesis($student_id, $mysqli)) {
		$thesis_id = getStudentThesis($student_id, $mysqli)['id'];
		$thesis_data = getThesis($thesis_id, $mysqli);
		$uploads = getUploads($thesis_id, $mysqli);
		$professors = getAvailableProfessors($mysqli, $thesis_id);
	}
	
?>
<h1>ASSIGNED THESIS</h1>
<div class="student-homepage">
	<?php 
	if (isset($thesis_data)) {
	?>
	<div class="student-thesis-info">
		<div class="student-thesis-field topic">
			<h2>THESIS TOPIC</h2>
			<div class="wrapper">
				<?php echo $thesis_data[0]['topic'] ?>
			</div>
		</div>
		<div class="student-thesis-field summary">
			<h2>SUMMARY</h2>
			<div class="wrapper">
				<?php echo $thesis_data[0]['summary'] ?>
			</div>
		</div>
		<h2>FULL DESCRIPTION</h2>
		<div class="student-thesis-field full-description">
			<?php 
			foreach ($uploads as $upload ) {
			?>
			<a href="<?php echo UPLOADS_PATH.$upload['filename'] ?>" class="uploaded-file" download>
				<i class="fa-regular fa-file-pdf"></i>
				<span class="uploaded-filename" title="<?php echo $upload['filename'] ?>">
					<?php echo $upload['filename'] ?>
				</span>
			</a>
			<?php
			}
			?>
		</div>
	</div>

	<div class="student-thesis-extra-info">
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
					<?php
						$date = new DateTime($thesis_data[0]['assigned_on']);
						echo $date->format('d/M/Y');
					?>
				</span>
			</div>
			<div class="info thesis-status-<?php echo $thesis_data[0]['status']['id']  ?>">
				<span>Status: </span>
				<span class="status">
					<?php echo $thesis_data[0]['status']['status'] ?>
				</span>
			</div>
			<?php 
			if ($thesis_data[0]['final_text_file'] != NULL) {
			?>
			<div class="info">
				<span>Final text link: </span>
				<a href="<?php echo $thesis_data[0]['final_text_file'] ?>" target="blank">
					Library Link
				</a>
			</div>
			<?php
			}
			if ($thesis_data[0]['status']['id'] >= 4 && $thesis_data[0]['final_grade'] != NULL) {
			?>
			<div class="info">
				<span>Examination report </span>
				<a class="link" href="<?php echo BASE_URL.BASE_PATH.'/examination-report/'.$thesis_id ?>">
					View Report
				</a>
			</div>
			<?php
			}
			?>
		</div>
		<?php 
		if ($thesis_data[0]['status']['id'] == 4 && $thesis_data[0]['final_grade'] != NULL && $thesis_data[0]['final_text_file'] == NULL) {
		?>
		<h2>FINAL TEXT FILE</h2>
		<form id="final-text-form" method="POST" action="<?php echo BASE_URL.BASE_PATH.'/api/set-final-text.php' ?>">
			<input type="hidden" name="thesis" value="<?php echo $thesis_id ?>">
			<input type="text" name="text_link" placeholder="Final text file link">
			<div class="submit-form" data-target="final-text-form">
				Submit Link
			</div>
		</form>
		<?php
		}

		if ($thesis_data[0]['status']['id'] > 3) {
		?>
		<h2>EXAMINATION INFO</h2>
		<div class="extra-info">
			<?php 
			if ($thesis_data[0]['draft_text']) {
			?>
			<div class="info">
				<span>Draft file: </span>
				<a href="<?php echo  UPLOADS_PATH.$thesis_data[0]['draft_text']?>" download>
					Download
				</a>
			</div>
			<?php
			}
			if ($thesis_data[0]['links']) {
			?>
			<div class="info links-info">
				<span>Links: </span>
				<div class="links-wrapper">
					<?php 
					foreach($thesis_data[0]['links'] as $key => $link){
					?>
					<a href="<?php echo $link['file_name'] ?>" style="margin-left: .5rem;" target="blank">
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
			<div class="info">
				<span>Examination method: </span>
				<span>
					<?php echo  $thesis_data[0]['method']['method']?>
				</span>
			</div>
			<?php
			}
			if ($thesis_data[0]['examination_place']) {
			?>
			<div class="info">
				<span>Examination room: </span>
				<span>
					<?php echo  $thesis_data[0]['examination_place']?>
				</span>
			</div>
			<?php
			}
			if ($thesis_data[0]['examination_date']) {
			?>
			<div class="info">
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

		<h2><span>COMMITEE</span>
			<?php 
			if ($thesis_data[0]['status']['id'] < 3) {
			?>
			<i class="fa-solid fa-circle-plus open-committee"></i>
			<?php
			}
			?>
		</h2>
		<?php 
		if ($thesis_data[0]['status']['id'] < 3) {
		?>
			<div class="invite-fields">
				<input id="search-professors" type="text" placeholder="Search professors">
				<div class="professors-list" data-thesis="<?php echo $thesis_id ?>">
					<?php 
					foreach ($professors as $professor) {
					?>
					<div class="professor send-invite" data-user="<?php echo $professor['id'] ?>"><span class="professor-name"><?php echo $professor['name'].' '.$professor['surname'] ?></span></div>
					<?php
					}
					?>
				</div>
			</div>
		<?php
		}
		?>
		<div class="committee-list">
			<?php 
			foreach ($thesis_data[0]['committee_members'] as $committee_member) {
			?>
			<div class="committee-member member-status-<?php echo $committee_member['accepted'] ?>" data-user="<?php echo $committee_member['user_id'] ?>" data-thesis="<?php echo $thesis_id ?>">
				<div class="member-name">
					<?php echo $committee_member['name'].' '.$committee_member['surname'] ?>
				</div>
				<div class="member-date">

					<div class="invited-date">
						Invited on: <?php
							$date = new DateTime($committee_member['invited_on']);
							echo $date->format('d/m/y');
						?>
					</div>
					<?php 
					if ($committee_member['accepted'] == 1 ) {
					?>
					<div class="accepted-date">
						Accepted on: <?php
							$date = new DateTime($committee_member['accepted_on']);
							echo $date->format('d/m/y');
						?>
					</div>
					<?php
					}
					if ($committee_member['accepted'] == 0 && $committee_member['accepted'] != NULL) {
					?>
					<div class="accepted-date">
						Declined on: <?php echo $committee_member['accepted_on'] ?>
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
	</div>
	<?php
	}
	else{
	?>
	<p>
		No thesis is assigned to you at the moment.
	</p>
	<?php
	}
	?>
</div>
<?php
}

?>
