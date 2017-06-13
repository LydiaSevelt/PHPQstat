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
		$format["filter"]=array();
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
		addFilterToData($data,$keys,$format["filter"]);
		printTable($data,$keys);
		addScripts($keys,$format["filter"],$format["tableOpt"]);
	}
}

#adds all the js script to init datatables and filter inputs
#Params:
#	-$keys:		array containing columns names
#	-$filter: $Format["$page"]["filter"] (see config.php)
#	-$tableOpt:	string included in dataTables initialization
function addScripts($keys,$filter,$tableOpt){
?>
<script type="text/javascript">
	var table;
	function setFilterText(col,text){
		$('#search_'+col).val(text);
	}
	function search(col,text,isRegex,caseSens){
		table.column(col).search(text,isRegex,!isRegex,!caseSens).draw();
	}
	function defaultFilter(col,text){
		setFilterText(col,text);
		search(col,text,false,false);
	}
	function queueFilter(col,text){
		var newtext='^'+text.substring(0,text.indexOf('@')+1);
		setFilterText(col,text.substring(0,text.indexOf('@')+1));
		search(col,newtext,true,true);
	}
	$(document).ready(function() {
		table=$('#myTable').DataTable({
			"paging": true,
			"orderCellsTop": true,
			"info": false,
			 //scrollY:        "60vh",
			 //scrollX:        true,
			"searching": true,
			dom: 'Brtlp',
			"pageLength": 25,
			"lengthMenu": [[10,20,25,50,75,100,-1],[ 10, 20, 25, 50, 75, 100 ,"All"]],
			buttons: [{
				extend: 'colvis',
				columns: ':not(.noVis)'
	    		}]
<?php
echo $tableOpt
?>
		});
		
		// Apply the search
		var visible=table.columns().visible();
		table.columns().visible(true);
		table.columns().every( function () {
			var that = this;
			var input=$('#search_'+that.index());
			
			//alert(input.attr("placeholder")+that.index());
			//alert(input.attr("placeholder").length);

			input.attr('size',input.attr("placeholder").length-3);
			input.on( 'keyup change', function () {
			    if ( that.search() !== this.value ) {
			       search(that,this.value,false,false);
			    }
			} );
			//alert(visible[that.index()]);
			that.visible(visible[that.index()]);
		} );
		$("#myTable").width("100%");
<?php
	if(isset($_GET['filter']) && isset($_GET['text'])){
		$indexcolumn=array_search($_GET['filter'],$keys);
		if(isset($filter[$_GET['filter']])){
			$function=$filter[$_GET['filter']];

		}else{
			$function="defaultFilter";
		}
		echo "".$function."('".$indexcolumn."','".$_GET['text']."');";
	}
?>
	} );
</script>
<?php
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
#adds a div to make all cells in a column clickable
#Params:
#	-$data:  data in format Matrix[$column][$row]
#	-$keys:		array containing columns names
#	-$filter: $Format["$page"]["filter"] (see config.php)
function addFilterToData(& $data,$keys,$filter){
	foreach($filter as $column=>$function){
		$indexcolumn=array_search($column,$keys);
		foreach($data[$column] as $rowIndex=>$element){
			$data[$column][$rowIndex]="<div class=\"div-filter\" onclick=\"".$function."('".$indexcolumn."',this.innerHTML)\">".$data[$column][$rowIndex]."</div>";
		}
	}

}

#prints the table
#Params:
#	-$data:		data in format Matrix[$column][$row]
#	-$keys:		array containing columns names
function printTable($data,$keys){
echo "		<table id=\"myTable\" class=\"display compact nowrap\">
			<thead>
				<tr>";
foreach ($keys as $column){
	echo "<th class=\"$column\">$column</th>";
}
?>
				</tr>
				<tr>
<?php
foreach ($keys as $key=>$column){
	echo "<td><input id=\"search_$key\" type=\"text\" placeholder=\"Search $column\" /></td>";#search inputs are created in tfoot and moved in thead after datatable creation(i cant find a simpler way to make them work right)
}
?>
				</tr>
			</thead>		
			<tbody>
<?php
foreach ($data[$keys[0]] as $rowIndex=>$value){
	echo "				<tr>";
	foreach ($keys as $key=>$colIndex){
		echo "<td>";
		echo $data[$colIndex][$rowIndex];
		echo "</td>";
	}
	echo "</tr>\n";
}
?>
			</tbody>
		</table>
<?php
}
?>
