#!/bin/bash
####################init variables##########################
SCRIPTPATH="//home/ubuntu123456789/Desktop/qstat_2" #supposed to be the folder where you have this script,
#	this is needed because when cron.d run this script the paths are messed up
source $SCRIPTPATH/influx_config.sh
DATABASE='QstatDB_tables'
RET_POLICY='min'
URL="$DBURL:$PORT/write?db=$DATABASE&rp=$RET_POLICY"
HOSTS_COMMAND="qhost -cb" #set qhost -ncb if you have son of grid engine
DATABASE_GRAPH='QstatDB'
RET_POLICY_GRAPH='min'
URL_GRAPH="$DBURL:$PORT/write?db=$DATABASE_GRAPH&rp=$RET_POLICY_GRAPH"
QUEUE_COMMAND_GRAPH="qstat -g c"
QUEUE_RESULT=$(qstat -g c -xml)
JOB_RESULT=$(qstat -f -u *, -r -t -ext -xml)
######################functions#################
#function parameters:
#	-$1: path in xml file
#	-$2: string containg xml
myxml(){
echo  $((echo $2) | xqilla -i /dev/stdin <(echo $1))
}
#get all data from a vector of xml elements (can be queues or jobs)
getValuesFromXmlVector(){
	#find number of elements
	local VectorNumber=$(myxml "count($1)" "$2" )
	#find number of attributes of main tag and number of node child for every element
	
	local ElemIndex=0
	local ElemIndex2
	local ElemIter
	local temp
	local temp2
	local XPath=$'concat('
	for((ElemIter=1;$ElemIter<=$VectorNumber;ElemIter++,ElemIndex++))
	do
		XPath+="count($1[$ElemIter]/@*),' ',count($1[$ElemIter]/*),' ',"
	done
	XPath+="' ',' ')"
	local NPar=$(myxml "$XPath" "$2")
	
	#get values&names of attributes of main tag and values&names of node child, getting number of attributes of node child for every element
	ElemIter=0
	local RETURN_DATA=$''
	XPath=$'concat('
	local XPath2=$'concat('
	for n in $NPar;
	do
		ElemIndex=$[ ($ElemIter /2) +1]
		ElemIndex2=$[ $ElemIndex -1]
		temp=$[ $ElemIter % 2 ]
		if [ $temp -eq 0 ]
		then
			#getting parameters of root element
			for((cont=1;$cont<=$n;cont++))
			do
				temp2="$1[$ElemIndex]/@*[$cont]"
				XPath+="'_',local-name($temp2),'[$ElemIndex2]=\"',string($temp2),'\",',"
			done
		else
			#getting name and values of children, counting parameters of those children
			for((cont=1;$cont<=$n;cont++))
			do
				temp2="$1[$ElemIndex]/*[$cont]"
				XPath+="local-name($temp2),'[$ElemIndex2]=\"',string($temp2),'\",',"
				XPath2+="'$ElemIndex ',local-name($temp2),' $cont ',count($temp2/@*),' ',"
			done
		fi
		ElemIter=$[ $ElemIter +1]
		#before the string to elaborate becomes too big, i process it now
		if [ $[ $ElemIndex % 30 ] -eq 0 ]
		then
			XPath+="' ',' ')"
			RETURN_DATA+=$(myxml "$XPath" "$2")
			XPath=$'concat('
		fi
			
	done
	XPath+="' ',' ')"
	XPath2+="' ',' ')"
	local RETURN_DATA+=$(myxml "$XPath" "$2")
	local NparChild=$(myxml "$XPath2" "$2")
	ElemIndex=0
	local countPar=1
	local thisJob
	local thisChild
	local i=0
	local type
	#getting child's parameters
	XPath=$'concat('
	for n in $NparChild;
	do
		type=$[ $i % 4 ]
		if [ $type -eq 0 ]
		then
			if [ $ElemIndex -eq $n ]
			then
				countPar=$[ $countPar +1 ]
			else
				ElemIndex=$n
				ElemIndex2=$[ $ElemIndex -1 ]
				countPar=1
			fi
		elif [ $type -eq 1 ] 
		then
			thisJob=$n
		elif [ $type -eq 2 ]
		then
			thisChild=$n
		else
			for ((count=1;$count<=$n;count++))
			do
				temp="$1[$ElemIndex]/*[$thisChild]/@*[$count]"
				XPath+="'$thisJob','_',local-name($temp),'[$ElemIndex2]=\"',string($temp),'\",',"
			done
		fi
		i=$[ $i +1 ]
	done
	XPath+="' ',' ')"
	wait
	RETURN_DATA+=$(myxml "$XPath" "$2")
	RETURN_DATA+="{n_values}=$ElemIndex"
	
	echo $RETURN_DATA
}
#the queue name is not present in the job data, i retreive it here
getQueueFromJobXmlVector(){
	local XPath=$'concat('
	local n=$(myxml "count($1)" "$2")	
	for((i=0;$i<$n;i++))
	do
		XPath+="'queue[$i]=\"',string($1[$i]/../name),'\",',"
	done
	XPath+="' ',' ')"
	echo $(myxml "$XPath" "$2")
}
#for the slot number i have to count the slave jobs
getSlotsFromJobXmlVector(){
	local XPath=$'concat('
	local n=$(myxml "count($1[master=\"MASTER\"])" "$2")	
	for((i=0;$i<$n;i++))
	do
		XPath+="string($1[$i]/JB_job_number),' ',"
	done
	XPath+="' ',' ')"
	local names=$(myxml "$XPath" "$2")
	XPath=$'concat('
	local i=0
	for name in $names;
	do
		XPath+="'slots[$i]=\"',count($1[JB_job_number=\"$name\"]),'\",',"
		i=$[ $i+1 ]
	done
	XPath+="' ',' ')"
	echo $(myxml "$XPath" "$2")
}
#########################gathering data for grafana###################
DATA=$($QUEUE_COMMAND_GRAPH | awk  '{if($1 !~ /CLUSTER/ && $3 ~ /^[0-9]/){printf "queue used_%s=%s,max_%s=%s\n", $1,$3,$1,$6}}')
curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL_GRAPH --data-binary "$DATA"
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
getQueues(){
	local QUEUE_DATA
	QUEUE_DATA=$''
	QUEUE_DATA+="queues "
	QUEUE_DATA+=$(getValuesFromXmlVector "job_info/*" "$QUEUE_RESULT")
	curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$QUEUE_DATA"
}

######################gathering jobs data##########################
getJobs(){
	local JOBS_DATA
	JOBS_DATA=$''
	JOBS_DATA+="jobs "
	#job_info/job_info/* : jobs in w state,
	#job_info/queue_info/Queue-List/job_list[master=\"MASTER\"] : job in r state
	local EXEC=$(myxml "(job_info/job_info/* | job_info/queue_info/Queue-List/job_list[master=\"MASTER\"])" "$JOB_RESULT")
	EXEC="<abc>$EXEC</abc>"
	JOBS_DATA+=$(getValuesFromXmlVector "/abc/*" "$EXEC")
	t=$(getQueueFromJobXmlVector "(job_info/queue_info/Queue-List/job_list[master=\"MASTER\"])" "$JOB_RESULT")
	JOBS_DATA+=",$t"
	t=$(getSlotsFromJobXmlVector "(job_info/queue_info/Queue-List/job_list)" "$JOB_RESULT")
	JOBS_DATA+="$t"
	JOBS_DATA=${JOBS_DATA:0:$[ ${#JOBS_DATA} -1 ]}
	curl -i -u $INFLUXUSER:$INFLUXPASSWORD -XPOST $URL --data-binary "$JOBS_DATA"
}
#######################################################################
getQueues & 
getJobs

