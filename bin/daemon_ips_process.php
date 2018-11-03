<?php

/*****************************
 *
 * Suricata2MikroTik IPS v1.0
 *
 * 
 * Author: Maximiliano Dobladez info@mkesolutions.net
 *
 * http://maxid.com.ar | http://www.mkesolutions.net  
 *
 * for API MIKROTIK:
 * http://www.mikrotik.com
 * http://wiki.mikrotik.com/wiki/API_PHP_class
 
 *
 * LICENSE: GPLv2 GNU GENERAL PUBLIC LICENSE
 * 
 * v1.0 - 31 oct 18 - initial version
 ******************************/

$dir_panel = realpath(dirname(__FILE__) . '/');

$DEBUG = true;
$DEBUG=false;
if ( !$DEBUG )
    error_reporting( 0 );
require_once $dir_panel.'/../config.php';
require_once $dir_panel.'/../share/functions.php';
require( $dir_panel.'/../share/routeros_api.php' );

if (!is_cli()){
    die("NOT CLI");
}

$API = new RouterosAPI();
 
mysql_con();
   try {
      if ( $DEBUG )   _log("Trying to connect to ".$router['ip']);
        $API->connect( $router['ip'], $router['user'], $router['pass'] );
        $start_connection_api=get_now(); #Define que se inicia ahora la conexion para reiniciarla cada 5 minutos.
         if ( $DEBUG )   _log("OK Connected to ".$router['ip']);
    }
    catch ( Exception $e ) {
        die( 'Unable to connect to RouterOS. Error:' . $e );
    }



while ( file_exists( $cfg['PID_app_file'] ) ) {
    $SQL = "SELECT *,inet_ntoa(que_ip_adr) as ip FROM block_queue WHERE que_processed = 0 LIMIT 10;";
    if ( !$result = $connect->query( $SQL ) ) {
        die( 'There was an error running the query [' . $connect->error . ']' );
    } //!$result = $connect->query( $SQL )

   
    $elapsed=minutos_transcurridos($start_connection_api,get_now());
    if ($elapsed>$router['restart_conn_time']) {
          if ( $DEBUG ) _log("Restarting API Connection. Time elapsed. ".$elapsed);
         $start_connection_api=get_now();  
        
         #Reconexion
         $API->disconnect();
         sleep(1);
         $API->connect( $router['ip'], $router['user'], $router['pass'] );
          if ( $DEBUG )   _log("OK Connected to ".$router['ip']);
    } 


    $count = $result->num_rows;
    if ($count==0) {
         if ( $DEBUG )  _log("Sleeping for new entry ".$elapsed);
        sleep(1);
        continue;
    }

   

    while ( $row = $result->fetch_assoc() ) {
        // if ( strpos( $row[ 'que_ip_adr' ], $cfg[ 'whitelist' ] ) !== true ) {
        if (!   check_ip_in_whilelist($row[ 'ip' ])) {
            /* Does not match local address... */

            if ($last_ip==$row[ 'ip' ]) {
                 if ( $DEBUG )  _log("SAME Last IP.");
                continue;
                 usleep(500);
                
            } else {

                        /* Now add the address into the Blocked address-list group */
                        $comment_tmp="From SuricataIPS, " . $row[ 'que_sig_name' ] . " => " . $row[ 'que_sig_gid' ] . ":" . $row[ 'que_sig_sid' ] . " => event timestamp: " . $row[ 'que_event_timestamp' ] ;
                     if ( $DEBUG )    _log("Pushing to ".$router['ip']);
                        $API->comm( "/ip/firewall/address-list/add", array(
                             "list" => "Blocked",
                            "address" => $row[ 'ip' ],
                            "timeout" => $row[ 'que_timeout' ],
                            "comment" => $comment_tmp
                        ) );
                     

                     
                        //si esta activo el api de telegram, avisar
                        if ($active_api_telegram) {
                            $comment_tmp.=" => IP: ".$row['ip'] . " => Timeout: ".$row[ 'que_timeout' ];
                            send_to_telegram($comment_tmp);
                        }
                        //si esta activo el mail envio por correo el alerta
                        if ($active_mail_report) {
                                /* Send email indicating bad block attempt*/
                                $to      = 'noreply@gmail.com';
                                $subject = 'Suricata on snort-host: attempted block on local address';
                                $message = 'A record in the block_queue indicated a block on a local IP Address (' . $row[ 'ip' ] . ")\r\n";
                                $message = $message . "\r\n";
                                $message = $message . "The signature ID is " . $row[ 'que_sig_id' ] . " named: " . $row[ 'que_sig_name' ] . "\r\n";
                                $message = $message . "    with a que_id of " . $row[ 'que_id' ] . "\r\n\r\n";
                                $message = $message . "Check the src_or_dst field in events_to_block for the signature to make sure it is correct (src/dst).\r\n\r\n";
                                $message = $message . "The record was not processed but marked as completed.\r\n";
                                $headers = 'From: noreply@gmail.com' . "\r\n" . 'Reply-To: noreply@gmail.com' . "\r\n" . 'X-Mailer: PHP/' . phpversion();
                                // mail($to, $subject, $message, $headers);                
                                // 
                        }
                  $last_ip=$row['ip'];
            }
            
         if ( $DEBUG )   _log($comment_tmp);
       
        } //whilelist  

        else {
         if ( $DEBUG )  _log( "Exception IP ".$row[ 'ip' ]);
        }
        

        //$SQL2 = "UPDATE block_queue set que_processed = 1 WHERE que_id = " . $row[ 'que_id' ] . ";";
        
        #actualizo todos los que tengan el mismo ip 
        $SQL2 = "UPDATE block_queue set que_processed = 1 WHERE que_ip_adr = " . ip2long($row[ 'ip' ]) . ";";
        if ( !$result2 = $connect->query( $SQL2 ) ) {
            die( 'There was an error running the query [' . $connect->error . ']' );
        } //!$result2 = $connect->query( $SQL2 )
        mysqli_free_result( $result2 );
    } //eof while
    
    mysqli_free_result( $result );
    usleep(5000);
    // sleep( 1 );
    /* Sleep 2 seconds then do again */
    mysqli_ping( $connect );
} //file_exists( $cfg[PID_app_file] )
   if ( $DEBUG )    _log("Disconnect to ".$router['ip']);
   $API->disconnect();

echo "Shutdown services cron\n";
unlink( $cfg[PID_app_file] );
$connect->close();

?>