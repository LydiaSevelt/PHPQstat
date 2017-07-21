<?php 
include("influx.php");
include("header.php");
if(isset($_GET["page"])){#home page has no param page -> dont draw table,
	drawAll(getDataFromDB($_GET["page"]),$Format[$_GET["page"]]);
}
else{# but get grafana 
	echo "		<iframe src=\"$GrafanaUrl\" onload=\"resizeIframe(this)\"></iframe>";
}
include("bottom.php");
?>
