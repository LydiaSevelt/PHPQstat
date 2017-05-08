<?php 
include("influx.php");
include("header.php");
echo "<tr>
      <td>
<br>";
if(isset($_GET["page"])){#home page has no param page -> dont draw table,
	drawAll(getDataFromDB($_GET["page"]),$Format[$_GET["page"]]);
}
else{# but get grafana 
	echo "<iframe style=\"width:90%; height:500px\" src=\"$GrafanaUrl\" ></iframe>";
}
echo "
<br>
      </td>
    </tr>";
include("bottom.php");
?>
