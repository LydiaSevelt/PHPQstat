<?php 
include("influx.php");
include("header.php");
?>

<table id="job_table" class="display compact nowrap">
	<thead>
		<tr><th>name</th><th>value</th>
		</tr>
	</thead>
	<tbody>
<?php
$id  = $_GET['id'];
$jobs=getDataFromDB("jobs");
$index=array_search($id,$jobs["JB_job_number"]);
foreach ($jobs as $key=>$value){
	echo "<tr><td>";
	echo $key;
	echo "</td><td>";
	echo $jobs[$key][$index];
	echo "</td></tr>\n";
}
?>
		</tbody>
	</table>
		<script>
			$(document).ready(function() {
				var table=$('#job_table').DataTable({
					"paging": false,
					"info": false,
					"searching": true
			
				});
			});
		</script>
<?php
include("bottom.php");
?>
