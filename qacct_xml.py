#!/usr/bin/python

#import commands
import re
import sys

#data = commands.getoutput("cat qacct_sample")
#data = data.split('\n')
data = sys.stdin

# mimicing the qstat xml output for simplicity sake

# header
print "<?xml version='1.0'?>"
print "<job_info>"
print "  <queue_info>"

def echo_job_xml(job):
	print "    <job_list exit_state=\"" + job["failed"] + "\">"
	print "      <JB_job_number>" + job["jobnumber"] + "</JB_job_number>"
	print "      <JB_name>" + job["jobname"] + "</JB_name>"
	print "      <JB_group>" + job["group"] + "</JB_group>"
	print "      <JB_owner>" + job["owner"] + "</JB_owner>"
	print "      <JB_project>" + job["project"] + "</JB_project>"
	print "      <JB_department>" + job["department"] + "</JB_department>"
	print "      <JAT_prio>" + job["priority"] + "</JAT_prio>"
	print "      <JAT_submit_time>" + job["qsub_time"] + "</JAT_submit_time>"
	print "      <JAT_start_time>" + job["start_time"] + "</JAT_start_time>"
	print "      <JAT_end_time>" + job["end_time"] + "</JAT_end_time>"
	print "      <cpu_usage>" + job["cpu"] + "</cpu_usage>"
	print "      <mem_usage>" + job["mem"] + "</mem_usage>"
	print "      <io_usage>" + job["io"] + "</io_usage>"
	print "      <queue_name>" + job["qname"] + "</queue_name>"
	print "      <slots>" + job["slots"] + "</slots>"
	print "      <granted_pe>" + job["granted_pe"] + "</granted_pe>"
	####
	print "      <hostname>" + job["hostname"] + "</hostname>"
	print "      <taskid>" + job["taskid"] + "</taskid>"
	print "      <account>" + job["account"] + "</account>"
	print "      <failed>" + job["failed"] + "</failed>"
	print "      <exit_status>" + job["exit_status"] + "</exit_status>"
	print "      <ru_wallclock>" + job["ru_wallclock"] + "</ru_wallclock>"
	print "      <ru_utime>" + job["ru_utime"] + "</ru_utime>"
	print "      <ru_stime>" + job["ru_stime"] + "</ru_stime>"
	print "      <ru_maxrss>" + job["ru_maxrss"] + "</ru_maxrss>"
	print "      <ru_ixrss>" + job["ru_ixrss"] + "</ru_ixrss>"
	print "      <ru_ismrss>" + job["ru_ismrss"] + "</ru_ismrss>"
	print "      <ru_idrss>" + job["ru_idrss"] + "</ru_idrss>"
	print "      <ru_isrss>" + job["ru_isrss"] + "</ru_isrss>"
	print "      <ru_minflt>" + job["ru_minflt"] + "</ru_minflt>"
	print "      <ru_majflt>" + job["ru_majflt"] + "</ru_majflt>"
	print "      <ru_nswap>" + job["ru_nswap"] + "</ru_nswap>"
	print "      <ru_inblock>" + job["ru_inblock"] + "</ru_inblock>"
	print "      <ru_oublock>" + job["ru_oublock"] + "</ru_oublock>"
	print "      <ru_msgsnd>" + job["ru_msgsnd"] + "</ru_msgsnd>"
	print "      <ru_msgrcv>" + job["ru_msgrcv"] + "</ru_msgrcv>"
	print "      <ru_nsignals>" + job["ru_nsignals"] + "</ru_nsignals>"
	print "      <ru_nvcsw>" + job["ru_nvcsw"] + "</ru_nvcsw>"
	print "      <ru_nivcsw>" + job["ru_nivcsw"] + "</ru_nivcsw>"
	print "      <iow>" + job["iow"] + "</iow>"
	print "      <maxvmem>" + job["maxvmem"] + "</maxvmem>"
	print "      <arid>" + job["arid"] + "</arid>"
	if job.has_key("submit_host"):
		# UGE - probably not the best way to detect UGE
		print "      <jclass_name>" + job["jc_name"] + "</jclass_name>"
		print "      <cwd>" + job["cwd"] + "</cwd>"
		print "      <submit_host>" + job["submit_host"] + "</submit_host>"
		print "      <submit_cmd>" + job["submit_cmd"] + "</submit_cmd>"
		print "      <deleted_by>" + job["deleted_by"] + "</deleted_by>"
		print "      <wallclock>" + job["wallclock"] + "</wallclock>"
		print "      <maxrss>" + job["maxrss"] + "</maxrss>"
		print "      <maxpss>" + job["maxpss"] + "</maxpss>"
		print "      <ioops>" + job["ioops"] + "</ioops>"
	else:
		# SGE
		print "      <ar_sub_time>" + job["ar_sub_time"] + "</ar_sub_time>"
		print "      <category>" + job["category"] + "</category>"
	print "    </job_list>"


job = {}
# parse output
for line in data:
	if re.match("^=======", line):
		# new line
		if job:
			echo_job_xml(job)
			job = {}
		continue
	if re.match("^Total System Usage", line):
		# finished
		break
	# split field from value
	#print line
	match = re.compile(" ").search(line)
	# assign to job dict
	job[ line[:match.start()] ] = line[match.start():].strip()

echo_job_xml(job)
# finished
print "  </queue_info>"
print "  <job_info>"
print "  </job_info>"
print "</job_info>"

