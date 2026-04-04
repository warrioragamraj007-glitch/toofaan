#!/bin/bash
# $Id: sql_run.sh,v 1.4 2012-09-24 15:13:22 juanca Exp $
# Default SQL language run script for VPL
# Copyright (C) 2012 Juan Carlos Rodríguez-del-Pino
# License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
# Author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>

#load common script and check programs
. common_script.sh
check_program mysql
#Generate execution script
cat common_script.sh > vpl_execution
# Execute the PHP script to get the server list
# SERVERS=$(php get_servers.php)

# # Check if PHP execution was successful
# if [ $? -ne 0 ]; then
#     echo "Error executing PHP script."
#     exit 1
# fi


# Path to the PHP script
#PHP_SCRIPT_PATH="../../vpl_submission_CE.class.php"  # Adjust this path

# Execute the PHP script to get the server list and environment variables
#php_output=$(php $PHP_SCRIPT_PATH)
# Check if PHP execution was successful
#if [ $? -ne 0 ]; then
    #echo "Error executing PHP script."
   # exit 1
#fi

# Export TESSELLATOR_USERNAME from PHP script output
#export $(echo "$php_output" | grep TESSELLATOR_USERNAME)

# Use the TESSELLATOR_USERNAME environment variable
#echo "TESSELLATOR_USERNAME is $TESSELLATOR_USERNAME"
 SERVERS=( "192.168.6.7")
#  SERVERS= ($(mysql --host=db_host --user=db_user --password=db_password --database=db_name --execute="SELECT server FROM mdl_vpl_jailservers;"))
# $sql = "SELECT server FROM mdl_vpl_jailservers;";
# $recordset = $DB->get_record_sql($sql);

#save submission files
for FILENAME in $VPL_SUBFILES
do
	mv $FILENAME $FILENAME.vpl_save
	#security check
	if [ -f $FILENAME ] ; then
		rm $FILENAME
		echo "removed $FILENAME"
	fi
done
for FILENAME in *
do
	NAME=$(basename $FILENAME .sql)
	if [ "$FILENAME" != "$NAME" ] ; then
    RANDOM_SERVER=$(shuf -e "${SERVERS[@]}" -n 1)
		echo "export MYSQL_PWD=student321$;mysql --quick --host=$RANDOM_SERVER --user=student < $FILENAME" >> vpl_execution
	fi
done
#restore submission files
for FILENAME in *.vpl_save
do
	NAME=$(basename $FILENAME .vpl_save)
	mv $FILENAME $NAME
done

#search and add .sql files from submission
for FILENAME in $VPL_SUBFILES
do

	#cat txt1.txt
	NAME=$(basename $FILENAME .sql)
	if [ "$FILENAME" != "$NAME" ] ; then
    	
         RANDOM_SERVER=$(shuf -e "${SERVERS[@]}" -n 1)
		echo "export MYSQL_PWD=student321$;mysql --quick --host=$RANDOM_SERVER --user=student < $FILENAME" >> vpl_execution
	fi
done
#interactive console
#echo "mysql --quick --host=172.20.36.151 --user=kmit --password=some_pass" >> vpl_execution
chmod +x vpl_execution
