<?php

/*****************************
 *
 * Suricata2MikroTik IPS v1.0
 *
 * This script is the daemon to clean DB
 * 
 * Author: Maximiliano Dobladez info@mkesolutions.net
 *
 * http://maxid.com.ar | http://www.mkesolutions.net  
 *
 *
 * LICENSE: GPLv2 GNU GENERAL PUBLIC LICENSE
 
 * v1.0 -  
 ******************************/

$dir_panel = realpath(dirname(__FILE__) . '/');

$DEBUG = true;
$DEBUG=false;
if ( !$DEBUG )
    error_reporting( 0 );
require_once $dir_panel.'/../config.php';
require_once $dir_panel.'/../share/functions.php';

if (!is_cli()){
    die("NOT CLI");
}

mysql_con();
/* Wait for a connection to the database */

while ( file_exists( $cfg[PID_app_file] ) ) {
    // Borra los bloqueos procesados que tenga como fecha la hora de agregado mas el timeout para eliminarlo y que se vuelva a agregar luego 
    $SQL = "DELETE FROM block_queue WHERE  que_processed=1 AND (que_added + INTERVAL que_timeout HOUR_SECOND) <= NOW()  ;";
    if ( !$result = $connect->query( $SQL ) ) {
        die( 'There was an error running the query [' . $connect->error . ']' );
    } //!$result = $connect->query( $SQL )
    mysqli_free_result( $result );
    sleep( 10 );
    /* Sleep 10 seconds then do again */
    mysqli_ping( $connect );
} //file_exists( $PID_app_file )
echo "Shutdown services Clean DB\n";
unlink( $cfg[PID_app_file] );
$connect->close();
?>