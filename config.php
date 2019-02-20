<?php
/*****************************
 *
  *
 * This file is the webgui for update and manager rules of project:
 *
 * https://github.com/elmaxid/Suricata2MikroTik *
 * 
 * Author: Maximiliano Dobladez info@mkesolutions.net
 *
 * http://maxid.com.ar | http://www.mkesolutions.net  
 *
 *
 * LICENSE: GPLv2 GNU GENERAL PUBLIC LICENSE
 *
 * 
 * v1.0 -   initial version
 ******************************/

define('VERSION', '1.1');
#
# Reload Rules suricata
#kill -USR2 $(pidof suricata)
#
 

// header( 'Content-Type: text/plain' );
$cfg['db_user_name']    = "root";
/* Database username */
$cfg['db_password']     = "p4c0tilla";
/* Database password */
$cfg['db_database']     = "suricata2ips";
$cfg['db_server']       = "localhost";

$cfg['PID_app_file'] = '/tmp/suricata2mikrotik.pid';
$cfg['PID_reload_file'] = '/tmp/suricata2mikrotik.reload.pid'; //para recargar las reglas
$cfg['file_log'] = '/tmp/suricata2mikrotik.log'; //log


// TELEGRAM API
$url_api_telegram="https://api.telegram.org/bot314xxxxxxxx10yxLxxxxR13an4wk/sendMessage?chat_id=-1xxx847xx3&text=";
//$active_api_telegram=true;
$active_api_telegram=false;

//mail report
$active_mail_report=false;

$cfg[ 'whitelist' ] = array(
    '10.0.0.0/8',
    '192.168.0.0/16',
    '172.16.0.0/16',
    '0.0.0.0' #bugfix
    ); 

# Time in minutes to restart API connection 
# Tiempo para reiniciar la conexión API 

$router['restart_conn_time'] = 5; 

// $router['conn']="API"; //API o SSH
// $router['ip']="10.200.200.1"; //IP Router

$router['conn'] = "SSH"; //API o SSH
$router['ip']   = "192.168.10.1"; //IP Router
$router['user'] = "api"; // user login
$router['pass'] = "api123";  //pass
$router['port'] = "22";  //port ssh

$router['address_list_block'] = "Blocked";  //Address list to add blocked IP
