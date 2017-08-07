#!/bin/sh

sudo chattr -i /var/www/xml/app_info.xml
sudo curl --insecure https://anionix.ddns.net/WDMyCloud/WDMyCloud-Gen2/Apps/apps_xml_gen.php > /var/www/xml/app_info.xml
sudo chattr +i /var/www/xml/app_info.xml
