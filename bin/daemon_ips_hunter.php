<?php
/*****************************
 *
 * Suricata2MikroTik IPS 
 * 
 * Daemon to looking for specifics Alerts and block the source o target.
 * 
 * Author: Maximiliano Dobladez info@mkesolutions.net
 *
 * http://maxid.com.ar | http://www.mkesolutions.net  
 *
 *
 * LICENSE: GPLv2 GNU GENERAL PUBLIC LICENSE
 *
 
 ******************************/
$dir_panel = realpath(dirname(__FILE__) . '/');

error_reporting( E_ALL );
error_reporting( 0 );
require_once $dir_panel.'/../config.php';
require_once $dir_panel.'/../share/functions.php';

if (!is_cli()){
    die("NOT CLI");
}

 

mysql_con();
  
#Abrir el json
#
#chequear si es un alerta y si esta en la lista de bloquear
#chequear si es un whilelist 
#   sino > bloquear
#   
#   conectarse via API
#   conectarse via SSH
#   
#   TODO: Hacer que se quede la regla o se borre con el clean


/*
ALERTA


{"timestamp":"2018-10-30T16:40:33.914426+0000","flow_id":1176372373961457,"pcap_cnt":1642642,"event_type":"alert","src_ip":"217.160.0.187","src_port":80,"dest_ip":"192.168.10.17","dest_port":50101,"proto":"TCP","alert":{"action":"allowed","gid":1,"signature_id":2100498,"rev":7,"signature":"GPL ATTACK_RESPONSE id check returned root","category":"Potentially Bad Traffic","severity":2},"http":{"hostname":"testmyids.com","url":"\/","http_user_agent":"curl\/7.43.0","http_content_type":"text\/html","http_method":"GET","protocol":"HTTP\/1.1","status":200,"length":39},"app_proto":"http","flow":{"pkts_toserver":12,"pkts_toclient":5,"bytes_toserver":953,"bytes_toclient":644,"start":"2018-10-30T16:40:25.998129+0000"}}


HTTP

{"timestamp":"2018-10-30T16:40:33.672909+0000","flow_id":1176372373961457,"pcap_cnt":1642193,"event_type":"http","src_ip":"192.168.10.17","src_port":50101,"dest_ip":"217.160.0.187","dest_port":80,"proto":"TCP","tx_id":0,"http":{"hostname":"testmyids.com","url":"\/","http_user_agent":"curl\/7.43.0","http_content_type":"text\/html","accept":"*\/*","connection":"keep-alive","content_length":"39","content_type":"text\/html","date":"Tue, 30 Oct 2018 16:40:33 GMT","last_modified":"Mon, 15 Jan 2007 23:11:55 GMT","server":"Apache","http_method":"GET","protocol":"HTTP\/1.1","status":200,"length":39}}


*/

touch( $cfg['PID_app_file'] );


$handle = popen("tail -f /var/log/suricata/eve.json 2>&1", 'r');
// $handle = popen("tail -n 200 /var/log/suricata/eve.json 2>&1", 'r');
while(!feof($handle)) {
    #Rule to block, from cache with 3 minutes to refresh
    $block_rules=get_cache_rules_to_block();

    unset($datos_to_db);    unset($need_block);

    $buffer = fgets($handle);
    // $line=json_decode($buffer,true);
        if (!file_exists( $cfg['PID_app_file'] )) {
            exit(0);
        }
    // echo "$buffer<br/>\n";
        $array = json_decode($buffer, true);
        $time = $array['timestamp'];
        $date = strtotime($time); 
        $fixed = date('l, F d Y g:iA', $date);
        $date_db = date("Y-m-d H:i:s", $date);
        if (isset($array['alert']['signature']) && !empty($array['alert']['signature'])) {
         
  // echo var_dump($buffer)."<br/>\n";;
                    // $need_block= array_search_partial($block_rules, $array['alert']['signature']);
                    #Busco si existe la firma en el array de las --firmas a bloquear---
                    $need_block= array_search_multiarray_strpos( $block_rules,'sig_name', $array['alert']['signature'] );
                 if ($need_block  ) {

                        // if ($block_rules)
                        $datos_to_db[que_added]=get_now();
                        $datos_to_db[que_timeout]=$need_block[timeout];
                        ($need_block[src_or_dst]=='src') ? $datos_to_db[que_ip_adr]=ip2long($array['src_ip']):$datos_to_db[que_ip_adr]=ip2long($array['dest_ip']);
                        $datos_to_db[que_sig_name]=$array['alert']['signature'];
                        $datos_to_db[que_sig_sid]=$array['alert']['signature_id'];
                        $datos_to_db[que_sig_gid]=$array['alert']['gid'];
                        $datos_to_db[que_event_timestamp]=$date_db;
                        $datos_to_db[json_raw]=serialize($array);
                        insert_db( 'block_queue', $datos_to_db  );

                     if ( $DEBUG )      _log("Alert Found: ". $datos_to_db[que_sig_name]. " FROM ".$array['src_ip']. " TO: ".$array['dest_ip']);



                   // echo var_dump($datos_to_db)."BLOCK <br/>\n";;

                    // echo "BLOCKEDDD.......".$need_block." \n";
                  /*     echo "<table width='90%' valign='top' align='center'>
                        <tr bgcolor = '#CACACA'>
                        <td width='220px'>" . $fixed . "</td>
                        <td width='150px'>" . $array['src_ip'] . "</td>
                        <td width='50px'>" . $array['src_port'] . "</td>
                        <td width='150px'>" . $array['dest_ip'] . "</td>
                        <td width='50px'>" . $array['dest_port'] . "</td>
                        <td width='360px'>" . $array['alert']['signature'] . "</td>
                        <td width='260px' align='center'> - </td>
                        <td width='360px' align='center'> - </td>
                        </tr></table>\n";*/
            
                 }

        }
     
    // ob_flush();
    // flush();
    usleep(400);
}
pclose($handle);
unlink( $cfg['PID_app_file'] );

/**
 * [get_cache_rules_to_block get rules to block from cache and refresh it every 2 minutes]
 * @return [type] [description]
 */
function get_cache_rules_to_block(){
    global $CACHE;
    $elapsed=minutos_transcurridos($CACHE[rules_block][time],get_now());
    if ($elapsed>1) {
        _log("Reloading Rules to block. Getting from DB. ".$elapsed);
        $CACHE[rules_block][time]=get_now();
        $CACHE[rules_block][rules]=get_rules2block_db();
        return $CACHE[rules_block][rules];

    }else {
     //   _log("Rules to block. Getting from cache ".$elapsed);
        return $CACHE[rules_block][rules];
    }

}
