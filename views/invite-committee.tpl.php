<?php 
$mysqli = DbConnect();

$professors = getUsers(2, $mysqli);
$connected_student_id = getLoggedUser($mysqli)['id'];

$student_thesis = getStudentThesis($connected_student_id, $mysqli);

?>
<div class="invite-committee" data-thesis="<?php echo $student_thesis['id'] ?>">
	<div class="professors">
		<?php 
		foreach ($professors as $professor) {
		?>
		<div class="professor" data-professor="<?php echo $professor['id'] ?>" style="display: flex; margin-top: 1rem;">
			<div class="name" style="margin-right: 1rem;">
				<?php echo $professor['name'].' '.$professor['surname']; ?>
			</div>	
			<div class="actions">
				<span class="send-invite" style="cursor: pointer; margin-right: 1rem;">Invite</span>
				<span class="cancel-invite" style="cursor: pointer; margin-right: 1rem;">Cancel</span>
			</div>
		</div>
		<?php
		}
		?>
	</div>
</div>
