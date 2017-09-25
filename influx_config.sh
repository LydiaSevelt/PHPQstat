#!/bin/bash
# find this file in your sge folder
source /ge/default/common/settings.sh
####################init variables##########################
DBURL="http://localhost"
PORT=8086
INFLUXUSER='admin'
INFLUXPASSWORD='admin'
# if you change password when you have already started data 
# gathering you have to change the password in influxdb as well!
