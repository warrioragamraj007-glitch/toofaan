#the below code for mongoshell
. common_script.sh
cat common_script.sh > vpl_execution
echo "/usr/bin/mongosh --file connectdb.js --nodb --norc --quiet -f $VPL_SUBFILE0" >> vpl_execution
chmod +x vpl_execution
