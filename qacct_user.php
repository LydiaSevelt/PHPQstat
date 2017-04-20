<html>

<head>
  <title>PHPQstat</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=Edge">
  <meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
  <link rel="stylesheet" type="text/css" href="jquery-ui.min.css"/>
  <link rel="stylesheet" type="text/css" href="datatables.min.css"/>
  <script type="text/javascript" src="datatables.min.js"></script>
  <script type="text/javascript" class="init">
    $(document).ready(function() {
        $('#jobtable').DataTable({
          "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
	  "order": [[ 9, "desc" ]]
        });
    } );
  </script>
 
</head>

<?php
if (isset($_GET['owner'])) {
	$owner = $_GET['owner'];
} else {
	$owner = 'all';
}
if (isset($_GET['queue'])) {
	$queue = $_GET['queue'];
} else {
	$queue = '';
}
if (isset($_GET['length'])) {
	$length = $_GET['length'];
} else {
	$length = '1';
}

echo "<body><table align=center width=100% border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tbody>";
include("header.php");

function show_all($qstat,$owner,$queue,$length) {
  echo "<table id=\"jobtable\" class=\"display\" align=center cellspacing=\"0\" width=\"100%\">
	  <thead>
		  <tr>
		  <th>JobID</th>
		  <th>Owner</th>
		  <th>Name</th>
		  <th>Status</th>
		  <th>Project</th>
		  <th>Queue</th>
		  <th>Compute Node</th>
		  <th>Submit Time</th>
		  <th>Start Time</th>
		  <th>End Time</th>
		  <th>Runtime (Seconds)</br><font size=2>(wallclock)<font></th>
		  <th>PE</th>
		  <th>Slots</th>
		  </tr></thead>
	  <tfoot>
		  <tr>
		  <th>JobID</th>
		  <th>Owner</th>
		  <th>Name</th>
		  <th>Status</th>
		  <th>Project</th>
		  <th>Queue</th>
		  <th>Compute Node</th>
		  <th>Submit Time</th>
		  <th>Start Time</th>
		  <th>End Time</th>
		  <th>Runtime (wallclock)</th>
		  <th>PE</th>
		  <th>Slots</th>
		  </tr></tfoot><tbody>";

  
  foreach ($qstat->xpath('//job_list') as $job_list) {
	  if ($owner != "all" && $job_list->JB_owner != $owner) {
	    continue;
	  }
	  if ($queue != "" && $job_list->queue_name != $queue) {
	    continue;
	  }

	  $pe = "";
	  $job_num=$job_list->JB_job_number;
	  if ("$job_list->failed" == "0") {
	  	$job_result="Success";
	  } else {
	  	$job_result="<font color=red><b>" . $job_list->failed . "</font></b>";
	  }
	  if ("$job_list->granted_pe" != "NONE") {
		$pe =$job_list->granted_pe;
	  }
	  echo "          <tr>
			  <td><a href=qacct_job.php?jobid=$job_list->JB_job_number&owner=$owner>$job_list->JB_job_number</a></td>
			  <td><a href=qacct_user.php?owner=$job_list->JB_owner&length=$length>$job_list->JB_owner</a></td>
			  <td>$job_list->JB_name</td>
			  <td>$job_result</td>
			  <td>$job_list->JB_project</td>
			  <td><a href=qacct_user.php?queue=$job_list->queue_name&owner=$owner&length=$length>$job_list->queue_name</a></td>
			  <td>$job_list->hostname</a></td>
			  <td>$job_list->JAT_submit_time</td>
			  <td>$job_list->JAT_start_time</td>
			  <td>$job_list->JAT_end_time</td>
			  <td>$job_list->ru_wallclock</td>
			  <td>$pe</td>
			  <td>$job_list->slots</td>
			  </tr>";
  }
  echo "</tbody></table><br><br>";

}

echo "<tr><td align=center>
<a class='ui-button ui-widget ui-corner-all' href=\"index.php\">Home</a> 
<a class='ui-button ui-widget ui-corner-all' href=\"qhost.php?owner=$owner\">Hosts status</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qstat.php?owner=$owner\">Queue status</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qstat_user.php?owner=$owner\">Jobs status ($owner)</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qacct_user.php?owner=$owner\">Completed Jobs ($owner)</a>
<a class='ui-button ui-widget ui-corner-all' href=\"about.php?owner=$owner\">About PHPQstat</a>
</td></tr>";

//Buttons for differnt time lengths
echo "
<tr><td align=center><br><font size=4>Completed jobs are queried according to <b>Submit time</b></font></td></tr>
<tr><td align=center><br>
<a class='ui-button ui-widget ui-corner-all' href=\"qacct_user.php?owner=$owner\">Completed Jobs ($owner) 24 hours (Default)</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qacct_user.php?owner=$owner&length=7\">Completed Jobs ($owner) 7 days</a>
<a class='ui-button ui-widget ui-corner-all' href=\"qacct_user.php?owner=$owner&length=30\">Completed Jobs ($owner) 30 days (Updated once a day)</a>
<br><br></td></tr>
";


if ($qstat_reduce != "yes" ) {
	$token = null;
	$token = tempnam(sys_get_temp_dir(), 'PHPQstat-');
	$out = exec("./gactxml -d $length -o $token -j");
	$qstat = simplexml_load_file("$token");
	show_all($qstat,$owner,$queue,$length);
	unlink($token);
} else {
	$qstat = simplexml_load_file("/tmp/qacct_" . $length . "_day.xml");
	show_all($qstat,$owner,$queue,$length);
}
?>
	  

      </td>
    </tr>
<?php
include("bottom.php");
?>
  </tbody>
</table>



</body>
</html>

