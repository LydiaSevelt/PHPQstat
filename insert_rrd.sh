#!/bin/bash
source influx_config.sh
DATABASE='QstatDB'
RET_POLICY='min'
URL="$DBURL:$PORT/write?db=$DATABASE&rp=$RET_POLICY"
TABLENAME='queue'
TIMESTAMP=1490713018 #data newer than this timestamp will be ignored
QUEUES=$(ls -l rrd/qacct* |awk '{print $9}')
influxd backup -database $DATABASE ./
for q in $QUEUES ;do
	rrdtool dump  $q |awk -v x=$q -v y=$TABLENAME -v z=$TIMESTAMP '
		BEGIN{match(x,"rrd/qacct_(.*).rrd",b)}
		{
		match($8,"^<row><v>([^N]*)</v></row>$",a);
		if(a[1] ~ /^[0-9]/ && $6<z)
			{printf "%s used_%s=%s %s000000000\n",y, b[1] ,a[1],$6}
		}' >file.txt
	curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary @file.txt
	rm file.txt
done
curl -XPOST "$DBURL:$PORT/query?db=$DATABASE" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=select mean(*) as "day_mean_used" into day.queue from min.queue where time>1 group by time(1d)'
curl -XPOST "$DBURL:$PORT/query?db=$DATABASE" -u $INFLUXUSER:$INFLUXPASSWORD --data-urlencode 'q=select  mean(*) as "hour_mean_used" into hour.queue from min.queue where time>1 group by time(1h)'
