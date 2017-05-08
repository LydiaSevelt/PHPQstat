<?php
include("config.php");
function getDataFromDB($table){
	global $Query;#declared in config.php
	$ret=array();
	switch($table){
		case "hosts":
			$result=influxCurl($Query["hosts"]);
			$ret=translateRequest($result);
		break;
		case "jobs":
			$result=influxCurl($Query["jobs"]);
			$ret=translateRequest($result);
		break;
		case "queues":
			$result=influxCurl($Query["queues"]);
			$ret=translateRequest($result);
		break;
	}
	return $ret;
}
#send the request to influxDB to make the query passed by parameter
function influxCurl($query){
	global $DBQuery;
	global $DBUser;
	global $DBPassword;
	$ch = curl_init($DBQuery.urlencode($query));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$DBUser:$DBPassword");
	$result=curl_exec($ch);
	curl_close($ch);
	return $result;

}
#data manipulation (transforms string from InfluxDB in matrix  elem[$column][$row])
function translateRequest($request){
	$JSON=json_decode($request);
	$ret=array();
	foreach ($JSON->results[0]->series[0]->columns as $key => $value){
		$i=strpos($value,'[');
		if($i!==FALSE){
			$colname=substr($value,5,$i-5);#5: name start with "last_" when you select last(*) so i drop first 5 chars
			$rowIndex=substr($value,$i+1,strpos($value,']')-$i-1);
			$ret[$colname][$rowIndex]=$JSON->results[0]->series[0]->values[0][$key];
		}
		if($value=="last_{n_values}"){
			$n_values=$JSON->results[0]->series[0]->values[0][$key];
		}
	}
	foreach ($ret as $key =>$column){
		for($i=0;$i<$n_values;$i++){
			if(!isset($ret[$key][$i])){
				$ret[$key][$i]="";
			}
		}
	}
	return $ret;
}
#function to call from outside to print the final table 
#Params:
#	-$data:   result from getDataFromDB
#	-$format: $Format["..."] (see config.php)
function drawAll($data,$format){
	if(!isset($format)){
		$format=array();
		$format["rename"]=array();
		$format["links"]=array();
	}
	if(!empty($data)){
		foreach($format["rename"] as $key =>$newkey){#renaming columns
			if(isset($data[$key])){
				$data[$newkey]=$data[$key];
				unset($data[$key]);
			}
		}
		$keys=array();#will rappresent list of columns
		if(isset($format["show"])){
			$keys=$format["show"];
		}
		else{
			foreach ($data as $key => $value){
				array_push($keys,$key);
			}
		}
		addLinksToData($data,$format["links"],$keys);
		printTable($data,$keys);
	}
}
#adds <a href... to a cell if defined so in config.php
#Params:
#	-$data:  data in format Matrix[$column][$row]
#	-$links: $Format["..."]["links"] (see config.php)
#	-$keys:  array containing columns names
function addLinksToData(& $data,$links,$keys){
	foreach ($links as $column => $value){
		foreach($data[$column] as $rowIndex =>$element){
			$temp=$value;
			foreach($data as $colIndex=>$colarray){
				$temp=str_replace("{{$colIndex}}",$data[$colIndex][$rowIndex],$temp);
			}
			$data[$column][$rowIndex]="<a href=\"".$temp."\">".$data[$column][$rowIndex]."</a>";
		}
	}
}

#prints the table
#Params:
#	-$data: data in format Matrix[$column][$row]
#	-$keys: array containing columns names
function printTable($data,$keys){
echo "<script type=\"text/javascript\">
	$(document).ready(function() {
		var table=$('#myTable').DataTable({
		  \"paging\": true,
		  \"info\": false,
		  \"searching\": true,
		  dom: 'lrtp'
		});

		$('#myTable tfoot tr').appendTo('#myTable thead');
		// Apply the search
		table.columns().every( function () {
			var that = this;
			$( 'input', this.footer() ).on( 'keyup change', function () {
			    if ( that.search() !== this.value ) {
				that
				    .search( this.value )
				    .draw();
			    }
			} );
		} );
	} );
  </script>";#div is added only because if you have a lot of columns, page buttons and next,previous,ecc are out of screen
echo "<div style=\"width:1600px\">\n
	<table id=\"myTable\" class=\"display\" align=center width=100% border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<thead><tr>";
foreach ($keys as $column){
	echo "<th>$column</th>";
}
echo "</tr></thead>\n<tfoot><tr>";
foreach ($keys as $column){
	echo "<th><input  type=\"text\" placeholder=\"Search $column\" /></th>";#search inputs are created in tfoot and moved in thead after datatable creation(i cant find a simpler way to make them work right)
}
echo "</tr></tfoot>\n<tbody>\n";
foreach ($data[$keys[0]] as $rowIndex=>$value){
	echo "<tr>";
	foreach ($keys as $colIndex){
		echo "<td>";
		echo $data[$colIndex][$rowIndex];
		echo "</td>";
	}
	echo "</tr>\n";
}
echo "</tbody>\n</table>\n</div>";
}
?>

