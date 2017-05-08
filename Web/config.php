<?php
$DBUrl="http://localhost";
$DBPort="8086";
$DBName="QstatDB_tables";
$DBUser="user";
$DBPassword="user";
$GrafanaUrl="http://192.168.187.128:3000/dashboard/db/abcd";
$DBQuery="$DBUrl:$DBPort/query?db=$DBName&q=";
$Query=array();
$Query["hosts"]="SELECT last(*) FROM hosts limit 1";#if you change the query or the DB structore 
$Query["queues"]="SELECT last(*) FROM queues limit 1";#you have to change the function translateRequest() in influx.php
$Query["jobs"]="SELECT last(*) FROM jobs limit 1";




#option to modify the tablein page hosts,queues and jobs
#option allowed:
#	-rename: an array where the key is the original name of the column and the value the new name you want to assign the column
#	-show(optional): an array where the values are the names of the columns you want to display, the  order you give is respected;
#		if you renamed a column you have to use the NEW name
#		if the array is not given all columns are displayed (renamed columns are at the end)
#	-links: an array where the key is the column you want to have a url link and the value is the url 
#		you can use placeholder for values of the same row using {column_name} in the url string
#		example "name"=>"index.php?id={id}" will place a link in every cell of the column name 
#		with the link "index.php?id=" followed by the column id
#the options have to be placed in this way $Format["page_name"]["option"]=...
$Format=array();
$Format["hostnames"]=array();
$Format["hosts"]["links"]=array();
$Format["queues"]=array();
$Format["queues"]["links"]=array();
$Format["jobs"]=array();
$Format["jobs"]["links"]=array();



$Format["hosts"]["rename"]=array(
	#"oldname1"=>"newname1",
	#"oldname2"=>"newname2",
);
#$Format["hosts"]["show"]=array("LOAD"); #shows only load column in this page
$Format["hosts"]["links"]=array(
	#"column where link has to be"=>"qstat_job.php?id={a column}"
);

$Format["queues"]["rename"]=array(
	
);

$Format["jobs"]["rename"]=array(
);
#$Format["jobs"]["links"]["colname"]="qstat_job.php?id={colname}...";
?>
