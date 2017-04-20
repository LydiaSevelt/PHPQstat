<html>

<head>
  <title>PHPQstat</title>
  <meta name="AUTHOR" content="Jordi Blasco Pallares ">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
  <link rel="stylesheet" type="text/css" href="jquery-ui.min.css"/>
  <link rel="stylesheet" type="text/css" href="datatables.min.css"/>
  <script type="text/javascript" src="datatables.min.js"></script>
  <script type="text/javascript" class="init">
    $(document).ready(function() {
        $('#jobtable').DataTable({
          "paging": false,
          "info": false,
          "searching": false,
        });
        $('#jobinfo').DataTable({
          "paging": false,
          "info": false,
          "searching": false,
<?php
	if ($UGE != "yes") {
          echo "          \"order\": [[ 3, \"asc\" ]]";
	} else {
          echo "          \"order\": [[ 7, \"asc\" ]]";
	}
?>
        });
        $('#jobstats').DataTable({
          "paging": false,
          "info": false,
          "searching": false,
        });
    } );
  </script>
</head>

<?php
require('time_duration.php');
if (isset($_GET['owner'])) {
	$owner = $_GET['owner'];
} else {
	$owner = 'all';
}
if (isset($_GET['jobid'])) {
	$jobid = $_GET['jobid'];
} else {
	$jobid = '';
}
echo "<body><table align=center width=100% border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>";
include("header.php");
echo "<tr><td align=center>
<a class='ui-button ui-widget ui-corner-all' href=\"index.php\">Home</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qhost.php?owner=$owner\">Hosts status</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qstat.php?owner=$owner\">Queue status</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qstat_user.php?owner=$owner\">Jobs status ($owner)</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qacct_user.php?owner=$owner\">Completed Jobs ($owner)</a>
<a class='ui-button ui-widget ui-corner-all' href=\"about.php?owner=$owner\">About PHPQstat</a>
</td></tr>";

?>
      <td>
<br>


<?php

$token = null;
$token = tempnam(sys_get_temp_dir(), 'PHPQstat-');
$out = exec("./gactxml -j $jobid -o $token");
$qstat = simplexml_load_file("$token");

foreach ($qstat->xpath('//job_list') as $job_list) {
	$job_project = $job_list->JB_project;
	if ("$job_list->JB_project" == "NONE") {
		$job_project = "";
	}
	echo "	<table id=\"jobtable\" class=\"display\" align=left cellspacing=\"0\" width=\"100%\">
        	<thead>
		<tr>
		<th>JobID</th>
                <th>Name</th>
                <th>Owner</th>
                <th>Group</th>
                <th>Project</th>
                <th>Queue</th>
                <th>Submit Time</th>
                <th>Start Time</th>
                <th>End Time</th>
                </tr></thead>
		<tbody>
                <tr>
                <td>$jobid</td>
                <td>$job_list->JB_name</td>
                <td>$job_list->JB_owner</td>
                <td>$job_list->JB_group</td>
                <td>$job_list->JB_project</td>
                <td>$job_list->queue_name</td>
                <td>$job_list->JAT_submit_time</td>
                <td>$job_list->JAT_start_time</td>
                <td>$job_list->JAT_end_time</td>
              </tr>
           </tbody>
	</table><br>";

	if ("$job_list->exit_status" == "0") {
		$exit_status = $job_list->exit_status;
	} else {
		$exit_status = "<font color=red><b>" . $job_list->exit_status . "</font></b>";
	}

	$job_pe = $job_list->granted_pe;
	if ("$job_list->granted_pe" == "NONE") {
		$job_pe = "";
	}
	
	if ($UGE != "yes") {

		echo "	<table id=\"jobinfo\" class=\"display\" align=left cellspacing=\"0\" width=\"100%\">
			<thead>
			<tr>
			<th>Compute Node</th>
			<th>PE</th>
			<th>Slots</th>
			<th>Exit Status</th>
			</tr></thead>
			<tbody>
			<tr>
			<td>$job_list->hostname</td>
			<td>$job_pe</td>
			<td>$job_list->slots</td>
			<td>$exit_status</td>
		      </tr>
		   </tbody>
		</table><br>";
	} else {

		$job_deleted_by = $job_list->deleted_by;
		if ("$job_list->deleted_by" == "NONE") {
			$job_deleted_by = "";
		}

		echo "	<table id=\"jobinfo\" class=\"display\" align=left cellspacing=\"0\" width=\"100%\">
			<thead>
			<tr>
			<th>Command</th>
			<th>Working Directory</th>
			<th>Submit Host</th>
			<th>Compute Node</th>
			<th>Deleted By</th>
			<th>PE</th>
			<th>Slots</th>
			<th>Exit Status</th>
			</tr></thead>
			<tbody>
			<tr>
			<td>$job_list->submit_cmd</td>
			<td>$job_list->cwd</td>
			<td>$job_list->submit_host</td>
			<td>$job_list->hostname</td>
			<td>$job_deleted_by</td>
			<td>$job_pe</td>
			<td>$job_list->slots</td>
			<td>$exit_status</td>
		      </tr>
		   </tbody>
		</table><br>";
		echo "	<table id=\"jobinfo\" class=\"display\" align=left cellspacing=\"0\" width=\"100%\">
			<thead>
			<tr>
			<th>Compute Node</th>
			<th>PE</th>
			<th>Slots</th>
			<th>Exit Status</th>
			</tr></thead>
			<tbody>
			<tr>
			<td>$job_list->hostname</td>
			<td>$job_pe</td>
			<td>$job_list->slots</td>
			<td>$exit_status</td>
		      </tr>
		   </tbody>
		</table><br>";
	}

	echo "	<table id=\"jobstats\" class=\"display\" align=center width=100% cellspacing=\"0\">
		<thead>
			<tr>
			<th>Runtime (Seconds)</br><font size=2>(wallclock)<font></th>
			<th>Total CPU Time (seconds)</th>
			<th>I/O (Gigabytes)</th>
			<th>I/O Toal Wait Time (seconds)</th>
			<th>Max Virtual Memory Utilized (Gigabytes)</th>
			</tr>
		</thead>
		</tbody>
			<tr>
			<td>$job_list->ru_wallclock</td>
			<td>$job_list->cpu_usage</td>
			<td>$job_list->io_usage</td>
			<td>$job_list->iow</td>
			<td>$job_list->maxvmem</td>
		      </tr>
		   </tbody>
		</table><br>";
}
unlink($token);
?>


<br>

      </td>
    </tr>
<?php
include("bottom.php");
?>
  </tbody>
</table>


</body>
</html>

