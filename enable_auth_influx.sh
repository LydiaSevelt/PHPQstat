#!/bin/bash
source influx_config.sh
URL=$DBURL:$PORT/query
curl -XPOST "$URL" --data-urlencode "q=CREATE USER $INFLUXUSER WITH PASSWORD '$INFLUXPASSWORD' WITH ALL PRIVILEGES"
sed -i 's/# auth-enabled = false/auth-enabled = true/g' $INFLUXCONFIGFILE
systemctl restart influxd
systemctl restart influxdb
