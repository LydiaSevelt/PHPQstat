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
1. for better and more detailed info see the official documentation of the two projects
    https://grafana.com/grafana/download
    http://docs.grafana.org/installation/
 
    https://portal.influxdata.com/downloads
    https://docs.influxdata.com/influxdb/v1.2/introduction/installation/
 
2. Install Apache,php5 and copy web directory in web accessible filesystem:
    ```
    yum install httpd xqilla php
    rpm -Uvh grafana-4.4.1-1.x86_64.rpm influxdb-1.2.4.x86_64.rpm
    ```
    You may need to enable EPEL
 
    CentOS 6 (init.d service)
    ```
    chkconfig httpd on
    service httpd restart
 
    chkconfig influxdb on
    service influxdb start
 
    chkconfig --add grafana-server
    chkconfig grafana-server on
    service grafana-server start
    ```
 
    CentoOS 7 (systemd)
    ```
    systemctl enable httpd
    systemctl start httpd
 
    systemctl enable influxdb
    systemctl start influxdb
 
    systemctl enable grafana-server.service
    systemctl start grafana-server
    ```
 
3. Set sge path and influxDB connection params in `influx_config.sh`
4. find influxDB conf file and set the path in `enable_auth_influx.sh`,
    run `enable_auth_influx.sh` 
	```
	sudo bash enable_auth_influx.sh
	```
5.  Setup retention policy duration in  `init_influx.sh`:
    defalut is INF (old data is not deleted);

	
	for more information click [here](https://docs.influxdata.com/influxdb/v1.2/query_language/database_management/#create-retention-policies-with-create-retention-policy);

    you can set the duration in time of the table containig: 
     1. all measurements from qstat,
     2. the hour mean,
     3. the day mean
	 
	```
	bash init_influx.sh
	```
6. (optional) copy data from previous rrd database
   
   run insert_rrd.sh ( you need to have rrd folder in the same path)
7. start data gathering:

    set $SCRIPTPATH in `insert.sh` with the absolute path of the scripts(this is done because when those script are in crontab relative path fails)
    Add the following line to the proper users crontab, making sure you replace [...]/insert.sh with the proper path : 
    ``` 
    */3 * * * * root [...]/insert.sh > /dev/null 2>&1
    ```
8. set graphana:
    you can access grafana with [YOUR_URL]:3000
    default login admin admin
    - insert data source influxdb with url user:user password:user
    -create your graph as you want
    -usefull queries:
        -template type query (can be multi-value): query="show field keys from min.queue" regex=/.*_(.*)/    should return list of queues    name Queues
        -template type query (can be multi-value): query="show field keys from min.queue" regex=/(.*)_.*/    should return used,max        name Measurement
        -template type query: query="show retention policies"                            should return min.hour,day    name RetPolicy
        -graph : SELECT last(/($Measurement)_($Queues)$/) FROM $RetPolicy.queue WHERE $timeFilter GROUP BY time($interval)
        - you can make all max values be displayed differently by adding series ovverride (display tab)with regex /max_.*/
    -remember to save the dashboard!
 
9. set parameters in config.php :

    set grafana url (go to you dashboard->share dashboard->link to dashboard or just copy paste browser url);

    set Format for hosts,queues,jobs
10. Set users in grafana so that not everyone can modify graph dashboard ecc.(you can also make users be able to modify the dashboard as they want, but they cannot save), you can disable log-in in the configuration file (auth.anonymous enabled=true)