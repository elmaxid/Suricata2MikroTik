# Suricata2MikroTik

Module for Suricata to read eve.json file and looking for specified alerts. If found it, then connect to router MikroTik via API and block the Attack with Firewall.

This is similar like ips-mikrotik-suricata  but works with eve.json and not with barnyard2. https://github.com/elmaxid/ips-mikrotik-suricata


Changelog:

31 Octubre 18: v1.0

* Init version


Requeriment:

* Suricata 
* IP and login for router MikroTik RouterOS
* GIT

** Features

* Detect an Alert from Suricata and connect to RouterOS to block de Attack source IP Address
* Notification:
        * Email
        * Telegram (API Bot)

Instalation

Once we have Suricata working and running on our network, the next step is the instalation of Suricata2MikroTik:

To install, Clone the repository and copy to /var/www/html/suricata2mikrotik

cd /var/www/html/

git clone https://github.com/elmaxid/Suricata2MikroTik

cd suricata2mikrotik

-- to Config

* Edit the file config.php  with DB and API Logins

* Create the DB schema 

mysql -u username -p  < schema.sql

 * Copy start_ips and start_suricata to /usr/local/bin

* Give permisions 
chmod +x /usr/local/bin/start*
 

----

How work it

For run Suricata, you need to redirect the traffic from MikroTik RouterOS to Suricata server, to do it just use Packet Sniffer or  Mangle Send To TZSP Action.