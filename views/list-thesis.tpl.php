<?php
$mysqli = DbConnect();


if (getLoggedUser($mysqli)['role'] == 2) {
	$prof = true;
}

else{
	$prof = false;
}

$all_theses = getAllTheses($mysqli, $prof);

?>

<div class="list-thesis-wrapper">
	
	<div class="list-thesis-tool-bar">
		<h1>
			List Theses
		</h1>
		<?php 
		if ($prof) {
		?>
		<a class="link create-new-thesis" href="<?php echo BASE_URL.BASE_PATH.'/create-thesis' ?>">
			CREATE NEW
		</a>
		<div class="filter-instructor">
			<div class="holder" >
				Involvement
			</div>
			<div class="instructor-options">
				<div class="option" data-instructor="all">
					Involvement
				</div>
				<div class="option" data-instructor="true">
					As Instructor
				</div>
				<div class="option" data-instructor="false">
					As Committee
				</div>
			</div>
		</div>
		<div class="filter-status">
			<div class="holder">
				Filter Status
			</div>
			<div class="statuses">
				<div class="select-status" data-status="">
					Filter Status
				</div>
				<div class="select-status" data-status="1">
					Unasigned
				</div>
				<div class="select-status" data-status="2">
					Under Assignment
				</div>
				<div class="select-status" data-status="3">
					Active
				</div>
				<div class="select-status" data-status="4">
					Under Examination
				</div>
				<div class="select-status" data-status="5">
					Completed
				</div>
				<div class="select-status" data-status="6">
					Cancelled
				</div>
			</div>
		</div>
		<div class="export-wrapper">
			<div class="holder">
				Export
			</div>
			<div class="export-options">
				<div class="export-option export-csv">
					CSV
				</div>
				<div class="export-option export-json">
					JSON
				</div>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	<div class="v-table">
		<div class="v-head">
			<div>Topic</div>
			<div>Committee</div>
			<div>Student</div>
			<div>Created on</div>
			<div>Status</div>
		</div>
		<div class="v-body">
			<?php 
			foreach ($all_theses as $thesis_key => $thesis) {
				if (getLoggedUser($mysqli)['id'] == $thesis['created_by']['id']) {
					$is_instructor = 'true';
				}
				else{
					$is_instructor = 'false';
				}
			?>
			<div class="v-row" data-status="<?php echo $thesis['status']['id'] ?>" data-instructor="<?php echo $is_instructor ?>">
				<div class="status-indicator status-<?php echo $thesis['status']['id'] ?>"></div>
				<div class="status-filler status-<?php echo $thesis['status']['id'] ?>"></div>
				<div class="v-topic">
					<a class="link" href="<?php echo BASE_URL.BASE_PATH.'/view-thesis/'.$thesis['thesis_id'] ?>"><?php echo $thesis['topic'] ?></a>
				</div>
				<div class="committee-pill-wrapper">
					<?php 
					foreach ($thesis['committee_members'] as $committee_member) {
						if ($committee_member['accepted'] && $committee_member['accepted'] > 0) {
							
							if(number_format($committee_member['user_id']) == number_format($thesis['created_by']['id'])){
								$in_class = 'instructor';
							}
							else{
								$in_class = '';
							}
							?>
						  	<span class="commitee-pill <?php echo $in_class ?>" title="<?php echo $committee_member['name'].' '.$committee_member['surname'] ?>"><?php echo mb_substr($committee_member['name'], 0, 1).'. '.$committee_member['surname']; ?></span>
						  	<?php
						}
					}
					?>
				</div>
				<div class="v-student">
					<?php 
						if (!empty($thesis['assigned_to']['name']) && !empty($thesis['assigned_to']['surname'])) {
    						echo mb_substr($thesis['assigned_to']['name'], 0, 1) . '. ' . $thesis['assigned_to']['surname'];
						}
					?>
				</div>
				<div class="v-date">
					<?php
					  	$date = new DateTime($thesis['created_at']);
						echo $date->format('d/m/Y'); // Output: 21 / 12 / 2024
					  	?>
				</div>
				<div class="v-status">
					<span class="assigned-tag status-<?php echo $thesis['status']['id'] ?>"><?php echo (isset($thesis['status']['status'])) ? $thesis['status']['status'] : 'Unassigned' ?></span>
				</div>
			</div>
			
			<?php
			}
			?>
		</div>
	</div>

</div>