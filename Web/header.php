<html>
<head>
<title>PHPQstat</title>
<meta name="AUTHOR" content="Jordi Blasco Pallares ">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="KEYWORDS" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost jordi blasco solnu">
<link rel="stylesheet" type="text/css" href="datatable/jquery-ui.min.css"/>
<link rel="stylesheet" type="text/css" href="datatable/datatables.min.css"/>
<script type="text/javascript" src="datatable/datatables.min.js"></script>
</head>
<body><table align=center width=100% border="0" cellpadding="0" cellspacing="0"><tbody>
<tr><td><img src=img/logo.png align=left height=80 width=80></td></tr>
<tr><td align=center>
<a class='ui-button ui-widget ui-corner-all' href="index.php">Home</a> 
<a class='ui-button ui-widget ui-corner-all' href="index.php?page=hosts">Hosts status</a>
<a class='ui-button ui-widget ui-corner-all' href="index.php?page=queues">Queue status</a>
<a class='ui-button ui-widget ui-corner-all' href="index.php?page=jobs">Jobs status</a>
<a class='ui-button ui-widget ui-corner-all' href="about.php?owner=$owner">About PHPQstat</a>
</td></tr>
<?php
/*
if ($qstat_reduce == "yes") {
	if (!file_exists("/tmp/load.xml")) {
		error_log(print_r("if", TRUE));
		
		#exec("./qinfo.sh 2>&1", $output_a,$retval);
        #$output = $output_a[0];
        #var_dump($output);
	#	 error_log(print_r("if_after", TRUE));
	#	error_log(print_r($output, TRUE)); 
	}
	$loadcheck = simplexml_load_file("/tmp/load.xml");
	$lastepoch = strtotime($loadcheck->last) + ($cache_time * 60);
	if ($lastepoch < time() ) {
		exec("./qinfo.sh");
		$loadcheck = simplexml_load_file("/tmp/load.xml");
	}
	if ($loadcheck->load == "Not Available") {
		
		echo "<tr><td align=center><b><font color=red>Unable to get load from master server, check snmpd server. </font></b>";
	} else {
		echo "<tr><td align=center>";
	}
	if ($loadcheck->check == "yes") {
		echo "<b><font color=red>Refresh waiting due to high load. Last refresh: $loadcheck->last - headnode 5 minute load average: $loadcheck->load</font></b></td></tr>";
	} else {
		echo "Last refresh: $loadcheck->last</td></tr>";
	}

}*/
#echo "<tr><td><img src=img/logo.png align=left height=80 width=80></td></tr>";

?>
