. common_script.sh
cat common_script.sh > vpl_execution
echo "cp -r /usr/local/mongoose-libs/node_modules /home/$(whoami)/" >> vpl_execution
echo "/usr/local/bin/node  $VPL_SUBFILE0" >> vpl_execution
chmod +x vpl_execution
