#!/bin/bash
source influx_config.sh
URL=$DBURL:$PORT/query
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode "q=CREATE USER \"user\" WITH PASSWORD 'user' "
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create database QstatDB'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create retention policy min on QstatDB duration INF replication 1'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create retention policy hour on QstatDB duration INF replication 1'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create retention policy day on QstatDB duration INF replication 1'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=CREATE CONTINUOUS QUERY "queue-day-mean" on QstatDB begin select mean(*) as "day-mean" into day.queue from min.queue where time>1 group by time(1d) end'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=CREATE CONTINUOUS QUERY "queue-hour-mean" on QstatDB begin select  mean(*) as "hour-mean" into hour.queue from min.queue where time>1 group by time(1h) end'

curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create database QstatDB_tables'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=create retention policy min on QstatDB_tables duration 1h replication 1 DEFAULT'
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode "q=GRANT READ ON QstatDB TO \"user\""
curl -XPOST "$URL" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode "q=GRANT READ ON QstatDB_tables TO \"user\""
