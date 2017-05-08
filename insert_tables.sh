#!/bin/bash
####################init variables##########################
SCRIPTPATH="//home/abc"
source $SCRIPTPATH/influx_config.sh
DATABASE='QstatDB_tables'
RET_POLICY='min' #change this if there is a different retetion policy ( old data is deleted automatically)
URL="$DBURL:$PORT/write?db=$DATABASE&rp=$RET_POLICY"
HOSTS_COMMAND="cat $SCRIPTPATH/myqhost" #"qhost -(n)cb"
QUEUES_COMMAND="qstat - g c -xml >qstatgc.xml"
JOBS_COMMAND="qstat -f -u *, -r -t -ext -xml >qstat_full.xml"
######################functions#################
#function parameters:
#	-$1: path in xml file
#	-$2: file name
#	-$3: index in the vector returned (getQueueFromJobXmlElement '//mytag' filename.xml 2   ===> queue[2]="...",

getValuesFromXmlElement(){
	local JobString=$''
	JobString+=$(getParameters $1 $2 $3)
	local CountTag=$(xmllint --xpath "count($1/*)" $2 )
	local temp
	local JobIter
	for((JobIter=1;$JobIter<=$CountTag;JobIter++))
	do
		temp=$(xmllint --xpath "local-name($1/*[$JobIter])" $2 )
		temp+="[$3]=\""
		temp+=$(xmllint --xpath "string($1/*[$JobIter])" $2 )
		temp+="\""
		JobString+=$temp,
		JobString+=$(getParameters "$1/*[$JobIter]" $2 $3)
	done
	echo $JobString
}
getQueueFromJobXmlElement(){
	temp="queue[$3]=\""
	temp+=$(xmllint --xpath "string($1/../name)" $2)
	temp+="\","
	echo $temp

}
getParameters(){
	local ParString=$''
	local CountPar=$(xmllint --xpath "count($1/@*)" $2 )
	local baseName=$(xmllint --xpath "local-name($1)" $2)
	local temp
	local ParamIter
	for ((ParamIter=1;$ParamIter<=$CountPar;ParamIter++))
	do
		temp=$baseName
		temp+="_"	
		temp+=$(xmllint --xpath "local-name($1/@*[$ParamIter])" $2)
		temp+="[$3]=\""
		temp+=$(xmllint --xpath "string($1/@*[$ParamIter])" $2)
		temp+="\""
		ParString+=$temp,
	done
	echo $ParString
}
#######################gathering hosts data###########################



DATA=$''
DATA+=$($HOSTS_COMMAND | awk '
function escape_influx(text)
{
     temp=gensub(/,/, "\\\\,","g",text)
     temp=gensub(/=/, "\\\\=","g",temp);
     temp=gensub(/ /, "\\\\ ","g",temp);
     return temp;
}


NR==1{printf "hosts ";sum=0;meta[0]=NF;for(i=1;i<=NF;i++) meta[i]=escape_influx($i)}

NR>2&&NF==meta[0]{for(i=1;i<NF;i++) printf "%s[%d]=\"%s\",",meta[i],sum,escape_influx($i);sum++}

END{printf "{n_values}=%d",sum}')
curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$DATA"

#########################gathering queues data#######################
#$($QUEUES_COMMAND)
DATA=$''
DATA+="queues "
QueueNumber=$(xmllint --xpath "count(job_info/*)" $SCRIPTPATH/qstatgc.xml )
QueueIndex=0
for((QueueIter=1;$QueueIter<=$QueueNumber;QueueIter++,QueueIndex++))
do
	DATA+=$(getValuesFromXmlElement "job_info/*[$QueueIter]" $SCRIPTPATH/qstatgc.xml $QueueIndex)

done
DATA+="{n_values}=$QueueIndex"
curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$DATA"

######################gathering jobs data##########################
#$($JOBS_COMMAND)
DATA=$''
DATA+="jobs "
JobNumber=$(xmllint --xpath "count(job_info/job_info/*)" $SCRIPTPATH/qstat_full.xml )
JobIndex=0
for((JobIter2=1;$JobIter2<=$JobNumber;JobIter2++,JobIndex++))
do
	DATA+=$(getValuesFromXmlElement "job_info/job_info/*[$JobIter2]" $SCRIPTPATH/qstat_full.xml $JobIndex)
done
JobNumber=$(xmllint --xpath "count(job_info/queue_info/Queue-List/job_list[master=\"MASTER\"])" $SCRIPTPATH/qstat_full.xml )
for((JobIter2=1;$JobIter2<=$JobNumber;JobIter2++,JobIndex++))
do
	DATA+=$(getValuesFromXmlElement "(job_info/queue_info/Queue-List/job_list[master=\"MASTER\"])[$JobIter2]" $SCRIPTPATH/qstat_full.xml $JobIndex)
	DATA+=$(getQueueFromJobXmlElement "(job_info/queue_info/Queue-List/job_list[master=\"MASTER\"])[$JobIter2]" $SCRIPTPATH/qstat_full.xml $JobIndex)
	
done
DATA+="{n_values}=$JobIndex"
curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$DATA"

