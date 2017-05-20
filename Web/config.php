<?php
$DBUrl="http://localhost";
$DBPort="8086";
$DBName="QstatDB_tables";
$DBUser="user";
$DBPassword="user";
$GrafanaUrl="http://192.168.187.128:3000/dashboard/db/abcd";
$DBQuery="$DBUrl:$DBPort/query?db=$DBName&q=";
$Query=array();
$Query["hosts"]="SELECT * FROM hosts group by * order by time DESC limit 1";#if you change the query or the DB structore 
$Query["queues"]="SELECT * FROM queues group by * order by time DESC limit 1";#you have to change the function 
$Query["jobs"]="SELECT * FROM jobs group by * order by time DESC limit 1";#translateRequest() in influx.php


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
#	-tableOpt: a string that is placed in the javascript code that declares the dataTable 
#		you can use it to specify datatable option for a single page
#		the string needs to start with ,
#	-filter: an array where the values are the names of the columns where the cells are going filter the table if you click on it
#the options have to be in this way $Format["page_name"]["option"]=...
$Format=array();
$Format["hostnames"]=array();
$Format["hosts"]["links"]=array();
$Format["queues"]=array();
$Format["queues"]["links"]=array();
$Format["jobs"]=array();
$Format["jobs"]["links"]=array();



$Format["hosts"]["rename"]=array(
	"HOSTNAME"=>"Hostname",
	"LOAD"=>"Load",
	"MEMUSE"=>"Memory Used",
	"MEMTOT"=>"Total Memory",
	"NCOR"=>"Core Number",
	"NCPU"=>"CPU Number",
	"NSOC"=>"Socked Number",
	"ARCH"=>"Architecture"
);
$Format["hosts"]["show"]=array("Hostname","Load","Memory Used","Total Memory","Core Number","CPU Number","Socked Number","Architecture"); #shows only load column in this page
$Format["hosts"]["links"]=array(
	#"column where link has to be"=>"qstat_job.php?id={a column}"
);
$Format["hosts"]["tableOpt"]="";
$Format["hosts"]["filter"]=array();

$Format["queues"]["rename"]=array(
	"name"=>"Name",
	"load"=>"Load",
	"used"=>"Slots Used",
	"total"=>"Total Slots",
	"available"=>"Available Slots",
	"temp_disabled"=>"Temp disabled",
	"manual_intervention"=>"Manual intervention",
	"resv"=>"Resv"
	
);
$Format["queues"]["show"]=array("Name","Load","Slots Used","Total Slots","Available Slots","Temp disabled","Manual intervention","Resv");
$Format["queues"]["tableOpt"]="";
$Format["queues"]["filter"]=array();
$Format["jobs"]["rename"]=array(
	"JAT_ntix"=>"ntix",
	"JAT_prio"=>"Priority",
	"JAT_share"=>"share",
	"JAT_start_time"=>"start time",
	"JB_department"=>"department",
	"JB_job_number"=>"Job Number",
	"JB_jobshare"=>"Job share",
	"JB_name"=>"Job name",
	"JB_override_tickets"=>"override tickets",
	"JB_owner"=>"Owner",
	"JB_project"=>"project",
	"JB_submission_time"=>"submission time",
	#"_state"=>"state",
	#"binding"=>"",
	"cpu_usage"=>"CPU Usage",
	#"def_hard_request"=>"",
	#"def_hard_request_name"=>"",
	#"ftickets"=>"",
	#"full_job_name"=>"",
	#"granted_pe"=>"",
	#"granted_pe_name"=>"",
	#"hard_req_queue"=>"",
	#"hard_request"=>"",
	#"hard_request_name"=>"",
	#"hard_request_resource_contribution"=>"",
	"io_usage"=>"I/O Usage",
	#"master"=>"",
	"mem_usage"=>"Memory Usage",
	#"otickets"=>"",
	"queue"=>"Queue",
	"requested_pe"=>"Requested slots",
	#"requested_pe_name"=>"",
	"slots"=>"Slots",
	"state"=>"State",
	#"stickets"=>"",
	#"tickets"=>""
);
$Format["jobs"]["show"]=array("Job Number","Job name","State","Queue","Requested slots","Priority","Owner",
	"_state","ftickets","stickets",
	"tickets","CPU Usage","I/O Usage","Memory Usage","Job share","department","granted_pe","hard_req_queue",
	"hard_request","hard_request_name","hard_request_resource_contribution",
	"master","otickets","full_job_name","requested_pe_name","share","ntix","project",
	"start time","submission time","Slots","granted_pe_name","override tickets");
$Format["jobs"]["links"]["Job Number"]="qstat_job.php?id={Job Number}";
$Format["jobs"]["tableOpt"]=",\"columnDefs\": [
	{ 
		\"visible\": false,
		targets: ['_state','def_hard_request','def_hard_request_name',
			'ftickets','stickets','tickets','CPU Usage','I/O Usage','Memory Usage',
			'job share','department','granted_pe','hard_req_queue','hard_request',
			'hard_request_name','hard_request_resource_contribution',
			'master','otickets','full_job_name','requested_pe_name','share','ntix',
			'project','start time','submission time','granted_pe_name','Slots'] 
	},
	{
		\"render\": function ( data, type, row ) {return data==''?1:data;},//set to 1 the slot number if no value is in the cell
		targets:['Requested slots']
	}
]";
$Format["jobs"]["filter"]=array("Owner");
?>
