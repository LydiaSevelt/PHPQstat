<!DOCTYPE html>
<html>
	<head>
		<title>PHPQstat</title>
		<meta name="author" content="R. Pancheri, L. Sevelt">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta name="keywords" content="gridengine sge sun hpc supercomputing batch queue linux xml qstat qhost">
		<link rel="stylesheet" type="text/css" href="datatable/jquery-ui.min.css"/>
		<link rel="stylesheet" type="text/css" href="datatable/datatables.min.css" />
		<link rel="stylesheet" type="text/css" href="qstat.css" />
		<script type="text/javascript" src="datatable/datatables.min.js" ></script>
		<script>
			function resizeIframe(obj){
				//obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
				//$(obj).css("width","100vw");
				var h=($(window).height())-($('#nav').outerHeight(true)+$('.footer').outerHeight(true)+40/*bar height */);
				$(obj).css("height",h+"px");
			}
			$( window ).resize(function() {
				resizeIframe($( "iframe" ));
			});
		</script>
	</head>
	<body>
		<ul id="nav">
			<li><img src="img/logo.png" height=80 width=80 class="" alt="logo"></li>
			<li><a class="ui-button ui-widget ui-corner-all" href="index.php">Home</a></li>
			<li><a class="ui-button ui-widget ui-corner-all" href="index.php?page=hosts">Hosts status</a></li>
			<li><a class="ui-button ui-widget ui-corner-all" href="index.php?page=queues">Queue status</a></li>
			<li><a class="ui-button ui-widget ui-corner-all" href="index.php?page=jobs">Jobs status</a></li>
		</ul>