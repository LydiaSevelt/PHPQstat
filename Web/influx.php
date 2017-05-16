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
#data manipulation (transforms json output from InfluxDB in matrix  elem[$column][$row])
#matrix is not of the formt M[$row][$column] because i need all the column names for the thead tag of the table
function translateRequest($request){
	$JSON=json_decode($request);
	$ret=array();
	$n_values=$JSON->results[0]->series[0]->values[0][array_search("{n_values}",$JSON->results[0]->series[0]->columns)];
	foreach ($JSON->results[0]->series[0]->columns as $key => $value){#every key is in this format : $colname[$rowNumber]
		$i=strpos($value,'[');#getting $colname
		if($i!==FALSE){
			$colname=substr($value,0,$i);
			$rowIndex=substr($value,$i+1,strpos($value,']')-$i-1);#getting $rowNumber
			if($rowIndex<$n_values){
				$ret[$colname][$rowIndex]=$JSON->results[0]->series[0]->values[0][$key];#getting value
			}
		}
	}
	foreach ($ret as $key =>$column){#setting empty string where value is missing
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
#	-$format: $Format["$page"] (see config.php)
function drawAll($data,$format){
	if(!isset($format)){
		$format=array();
		$format["rename"]=array();
		$format["links"]=array();
		$format["tableOpt"]="";
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
		addLinksToData($data,$format["links"]);
		printTable($data,$keys,$format["tableOpt"]);
	}
}
#adds <a href... to a cell if defined so in config.php
#Params:
#	-$data:  data in format Matrix[$column][$row]
#	-$links: $Format["$page"]["links"] (see config.php)
function addLinksToData(& $data,$links){
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
#	-$data:		data in format Matrix[$column][$row]
#	-$keys:		array containing columns names
#	-$tableOpt:	string included in dataTables initialization
function printTable($data,$keys,$tableOpt){
echo "		<table id=\"myTable\" class=\"display compact nowrap\">
			<thead>
				<tr>";
foreach ($keys as $column){
	echo "<th class=\"$column\">$column</th>";
}
echo "</tr>
			</thead>
			<tfoot>
				<tr>";
foreach ($keys as $column){
	echo "<th><input  type=\"text\" placeholder=\"Search $column\" /></th>";#search inputs are created in tfoot and moved in thead after datatable creation(i cant find a simpler way to make them work right)
}
echo "				</tr>
			</tfoot>
			<tbody>\n";
foreach ($data[$keys[0]] as $rowIndex=>$value){
	echo "				<tr>";
	foreach ($keys as $colIndex){
		echo "<td>";
		echo $data[$colIndex][$rowIndex];
		echo "</td>";
	}
	echo "</tr>\n";
}
echo "			</tbody>
		</table>
		<script type=\"text/javascript\">
			$(document).ready(function() {
				var table=$('#myTable').DataTable({
					\"paging\": true,
					\"info\": false,
					\"searching\": true,
					dom: 'Brtlp',
					\"pageLength\": 25,
					\"lengthMenu\": [ 10, 20, 25, 50, 75, 100 ],
					buttons: [{
						extend: 'colvis',
						columns: ':not(.noVis)'
			    		}]
					$tableOpt
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
		</script>";
}
?>

