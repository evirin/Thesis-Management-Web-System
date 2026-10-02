<?php 
$mysqli = DbConnect();

$instructor_id = getLoggedUser($mysqli)['id'];
$instructor_theses= getInstructorTheses($instructor_id, $mysqli);
$committee_theses = getInstructorThesesAsCommittee($instructor_id, $mysqli);

$total_thesis = count($instructor_theses);
$total_com_thesis = count($committee_theses);
$inst_completed_thesis = [];
$com_completed_thesis = [];
$avg_inst_grade = 0;
$avg_com_grade = 0;
$avg_inst_ttf = 0;
$avg_com_ttf = 0;

foreach ($instructor_theses as $thesis) {
	if ($thesis['completed_on'] != NULL) {
		$start = new DateTime($thesis['assigned_on']);
		$end = new DateTime($thesis['completed_on']);

		// Get the difference
		$interval = $start->diff($end);
		array_push($inst_completed_thesis, ['id' => $thesis['id'], 'assigned_on' => $thesis['assigned_on'], 'completed_on'=> $thesis['completed_on'], 'ttf' => $interval->days, 'final_grade' => $thesis['final_grade']]);
		$avg_inst_grade =+ $thesis['final_grade'];
		$avg_inst_ttf =+ $interval->days;
	}
}

if (count($inst_completed_thesis) > 0) {
	$avg_inst_grade = $avg_inst_grade / count($inst_completed_thesis);
	$avg_inst_ttf = $avg_inst_ttf / count($inst_completed_thesis);
}

foreach ($committee_theses as $thesis) {
	
	if ($thesis['completed_on'] != NULL) {
		$start = new DateTime($thesis['assigned_on']);
		$end = new DateTime($thesis['completed_on']);

		// Get the difference
		$interval = $start->diff($end);
		array_push($com_completed_thesis, ['id' => $thesis['thesis_id'], 'assigned_on' => $thesis['assigned_on'], 'completed_on'=> $thesis['completed_on'], 'ttf' => $interval->days, 'final_grade' => $thesis['final_grade']]);
		$avg_com_grade =+ $thesis['final_grade'];
		$avg_com_ttf =+ $interval->days;
	}
}

if (count($com_completed_thesis) > 0) {
	$avg_com_grade = $avg_com_grade / count($com_completed_thesis);
	$avg_com_ttf =+ $avg_com_ttf / count($com_completed_thesis);
}

?>
<div class="dashboard-wrapper">
	<h1>DASHBOARD</h1>
	<div class="dash-main-panel">
		<div class="left-panel">
			<div class="stat-wrapper grade-wrapper">
				<h2>GRADES COMPARISON</h2>
				<div class="statistics" >
					<canvas id="gradesChart"></canvas>
				</div>
			</div>
			<div class="stat-wrapper">
				<h2>THESES STATUSES</h2>
				<div class="statistics" >
					<canvas id="thesisChart"></canvas>
				</div>
			</div>
			<div class="stat-wrapper">
				<h2>INSTRUCTOR ACTIVITY</h2>
				<div class="statistics" >
					<canvas id="instructorChart"></canvas>
				</div>
			</div>
		
		</div>
		<div class="right-panel">
			<h2>AS INSTRUCTOR</h2>
			<div class="statistics">
				<div class="statistic">
					<span>Total thesis:</span><span><?php echo $total_thesis ?></span>
				</div>
				<div class="statistic">
					<span>Completed thesis:</span><span><?php echo count($inst_completed_thesis) ?></span>
				</div>
				<div class="statistic">
					<span>Avg thesis grade:</span><span><?php echo $avg_inst_grade ?></span>
				</div>
				<div class="statistic">
					<span>Avg completion time<small>(days)</small>:</span><span><?php echo $avg_inst_ttf ?></span>
				</div>
			</div>
			<h2>AS COMMITTEE MEMBER</h2>
			<div class="statistics">
				<div class="statistic">
					<span>Total thesis:</span><span id="tolal-com-theses"><?php echo $total_com_thesis ?></span>
				</div>
				<div class="statistic">
					<span>Completed thesis:</span><span><?php echo count($com_completed_thesis) ?></span>
				</div>
				<div class="statistic">
					<span>Average thesis grade:</span><span><?php echo $avg_com_grade ?></span>
				</div>
				<div class="statistic">
					<span>Avg completion time<small>(days)</small>:</span><span><?php echo $avg_com_ttf ?></span>
				</div>
			</div>
		</div>
	</div>
</div>