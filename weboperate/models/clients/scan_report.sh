#!/bin/bash
cd /data/
wget http://update.aegis.aliyun.com/download/install.sh 
chmod +x install.sh
bash install.sh
/usr/local/aegis/alihids/AliHids -scan -dir /data/www
sleep 3
mkdir /data/filebackup
mv /data/www/*.sql /data/filebackup
mv /data/www/*.zip /data/filebackup
cat /usr/local/aegis/globalcfg/reports/scanfiles.txt|grep 'yes_webshell' >/data/report-error.log
Reports=`cat /usr/local/aegis/globalcfg/reports/scanfiles.txt|grep 'yes_webshell' |wc -l`
if [ $Reports -ne '0' ];then
        echo "##efast-shell-error1##"
else
        echo "##efast-ok##"
fi