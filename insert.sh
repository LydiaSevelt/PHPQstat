#!/bin/bash
SCRIPTPATH="//home/abc"
source $SCRIPTPATH/influx_config.sh
DATABASE='QstatDB'
RET_POLICY='min' #change this if there is a different retetion policy ( old data is deleted automatically)
URL="$DBURL:$PORT/write?db=$DATABASE&rp=$RET_POLICY"
QUEUE_COMMAND="cat //home/abc/myqstatgc" #"qstat -g c"

DATA=$($QUEUE_COMMAND | awk  '{if($1 !~ /CLUSTER/ && $3 ~ /^[0-9]/){printf "queue used_%s=%s,max_%s=%s\n", $1,$3,$1,$6}}')
curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$DATA"
