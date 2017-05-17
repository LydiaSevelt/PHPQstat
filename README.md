ABOUT PHPQstat
==============================================
**PHPQstat** is a web interface to qstat and other useful commands of the Grid Engine (GE) batch queue system.
With this interface, you and your users can monitor your jobs and your queue status conveniently via a web browser.

**AUTHORS**  
UGE support, HTML5 interface, qstat reduce and remote master options added by Lydia Sevelt (LydiaSevelt@gmail.com)  
Originally written by Jordi Blasco Pallarès (jordi.blasco@hpcnow.com).

**REPORTING BUGS**  
Report bugs to GitHUB issue Tracker https://github.com/LydiaSevelt/PHPQstat/issues

**ADDITIONAL LIBRARIES**  
The HTML5 interface utilizes the excellent datatables (https://datatables.net) and jquery (https://jquery.com) javascript libraries.  

**TESTED WITH**  
GE 6.2u5

**LICENSE**  
This is free software: you are free to change and redistribute it. GNU General Public License version 3.0 (GPLv3).

**Version**  
Influx-alpha 

https://github.com/LydiaSevelt/PHPQstat



DEPENDENCIES
==============================================
Setup (on sge_master host):  
apache, php5, influxDB, xmllint, grafana and awk.

INSTALL
==============================================
1. Install Apache,php5 and copy web directory in web accessible filesystem:
	CentOs:	
		yum install httpd
		chkconfig --levels 235 httpd on
		service httpd restart
		yum -y install php
		systemctl restart httpd
	Ubuntu:
		sudo apt-get install apache2
		sudo apt-get install php
		service apache2 restart
2. Install influxDB 1.2:
	CentOS:	
		wget https://dl.influxdata.com/influxdb/releases/influxdb-1.2.2.x86_64.rpm
		sudo yum localinstall influxdb-1.2.2.x86_64.rpm
	Ubuntu-Debian:
		wget https://dl.influxdata.com/influxdb/releases/influxdb_1.2.2_amd64.deb
		sudo dpkg -i influxdb_1.2.2_amd64.deb
	instructions at:
		https://docs.influxdata.com/influxdb/v1.2/introduction/installation/
3. Install grafana 4.2.0
	1)
	-Ubuntu & Debian(64 Bit)
		wget https://s3-us-west-2.amazonaws.com/grafana-releases/release/grafana_4.2.0_amd64.deb
		sudo dpkg -i grafana_4.2.0_amd64.deb 
	-Standalone Linux Binaries(64 Bit)
		wget https://s3-us-west-2.amazonaws.com/grafana-releases/release/grafana-4.2.0.linux-x64.tar.gz
		tar -zxvf grafana-4.2.0.linux-x64.tar.gz 
	-Redhat & Centos(64 Bit)
		wget https://s3-us-west-2.amazonaws.com/grafana-releases/release/grafana-4.2.0-1.x86_64.rpm
		sudo yum localinstall grafana-4.2.0-1.x86_64.rpm
	2)
	-all:
		service grafana-server start
		sudo systemctl enable grafana-server.service
	instructions at:
		https://grafana.com/grafana/download?platform=linux
4. Install xquilla
5. Set up variables in influx_config.sh
6. run enable_auth_influx-sh or do it mannually (you may need sudo) and wait a few seconds
7. Setup retention policy duration in  init_influx.sh :  
    defalut is INF (old data is not deleted )
    you can set the duration in time of the table containig 1)all measurements from qstat,
    2) the hour mean,
    3)the day mean 
8. run init_influx.sh
9. (optional) copy data from previous rrd database
   
   run insert_rrd.sh ( you need to have rrd folder in the same path)
10. start data gathering
	set $SCRIPTPATH in insert.sh and insert_tables.sh with the absolute path of the scripts(this is done because when those script are in crontab relative path fails)
	Add the following line to the proper users crontab, making sure you replace [...]/insert.sh with the proper path :  
    */3 * * * * [...]/insert.sh > /dev/null 2>&1
11. set graphana:
	you can access grafana with [YOUR_URL]:3000
	default login admin admin
	- insert data source influxdb with url user:user password:user
	-create your graph as you want
	-usefull queries:
		-template type query (can be multi-value): query="show field keys from min.queue" regex=/.*_(.*)/	should return list of queues	name Queues
		-template type query (can be multi-value): query="show field keys from min.queue" regex=/(.*)_.*/	should return used,max		name Measurement
		-template type query: query="show retention policies"							should return min.hour,day	name RetPolicy
		-graph : SELECT last(/($Measurement)_($Queues)$/) FROM $RetPolicy.queue WHERE $timeFilter GROUP BY time($interval)
		- you can make all max values be displayed differently by adding series ovverride (display tab)with regex /max_.*/
	-remember to save the dashboard!

12. set parameters in config.php 
	set grafana url (go to you dashboard->share dashboard->link to dashboard or just copy paste browser url)
	set Format for hosts,queues,jobs
13. Set users in grafana so that not everyone can modify graph dashboard ecc.(you can also make users be able to modify the dashboard as they want, but they cannot save), you can disable log-in in the configuration file (auth.anonymous enabled=true)


  OPTIONAL
  ----------------------------------------------
14. Replace PHPQstat/img/logo.png with the logo of your company/school to brand the page 

