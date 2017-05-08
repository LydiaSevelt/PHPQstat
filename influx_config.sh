#!/bin/bash
source opt/sge_root/default/common/settings.sh #find this file in your sge folder
####################init variables##########################
DBURL="http://localhost"
PORT=8086
INFLUXUSER='admin'
INFLUXPASSWORD='admin'
INFLUXCONFIGFILE="/etc/influxdb/influxdb.conf"
#if you change password when you have already started data 
#gathering you have to change the password in influxdb as well!
