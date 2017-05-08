<?php 
include("influx.php");
    include("header.php");
  ?>


<?php
$id  = $_GET['id'];

?>
    <tr>
      <td>
<br>
	<?php
$jobs=getDataFromDB("jobs");
$index=array_search($id,$jobs["JB_job_number"]);
echo "<table border=\"1\" style=\"margin:0 auto\">";
foreach ($jobs as $key=>$value){
	echo "<tr><td>";
	echo $key;
	echo "</td><td>";

	echo $jobs[$key][$index];
	echo "</td></tr>";
}
echo "</table>";
?>
<br>
      </td>
    </tr>
<?php
include("bottom.php");
?>
