<?php

$dir_panel_share = realpath(dirname(__FILE__) . '/');
set_include_path(get_include_path() . PATH_SEPARATOR . $dir_panel_share."/"); #share
set_include_path(get_include_path() . PATH_SEPARATOR . $dir_panel_share."/ssh/"); #ssh


define('VERSION', 'v1.1');


/**
 * [is_cli Check if environment is cli]
 * @return boolean [description]
 */

function is_cli()
    {
    return php_sapi_name() === 'cli';
    }

/**
 * [get_now get the date now]
 * @return [type] [description]
 */

function get_now()
    {
    return date("Y-m-d H:i:s", time());
    }

/**
 * [minutos_transcurridos indica la cantidad de minutos que sucedieron desde una fecha a otra]
 * @param  [type] $fecha_i [description]
 * @param  [type] $fecha_f [description]
 * @return [type]          [description]
 */

function minutos_transcurridos($fecha_i, $fecha_f)
    {
    $minutos = (strtotime($fecha_i) - strtotime($fecha_f)) / 60;
    $minutos = abs($minutos);
    $minutos = floor($minutos);
    return $minutos;
    }

function _log($text)
    {
    global $cfg;
    $log = get_now() . " - " . $text . "\n";
    file_put_contents($cfg['file_log'], $log, FILE_APPEND);

    // error_log($log, 3, $cfg[file_log]);

    }

/**
 * [get_json_db get ]
 * @param  [type] $que_id [description]
 * @return [type]         [description]
 */

function get_block_queue_db($que_id = NULL)
    {
    if (!$que_id) return false;
    global $connect;
    $SQL = "SELECT * FROM block_queue  WHERE `que_id` = '$que_id' LIMIT 1;";
    if (!$result = $connect->query($SQL))
        {
        die('There was an error running the query [' . $connect->error . ']');
        } //!$result = $db_->query( $SQL )
    $row = $result->fetch_assoc();
    return $row;
    }

/**
 * [insert_db agrega un nuevo registro en una db]
 * @param  [type] $dbname [description]
 * @param  [type] $datos  [description]
 * @return [type]         [description]
 */

function insert_db($dbname = NULL, $datos = NULL, $delayed = false)
    {
    global $connect;
    if (!$datos) return false;
    if (!$dbname) return false;
    $sql = "INSERT ";
    if ($delayed) $sql.= " delayed ";
    $sql.= " INTO $dbname   ";
    foreach($datos as $key => $valor)

        {
        $sql_init.= "`" . $key . "`,";
        $sql_final.= "'" . $valor . "',";
        } //$datos as $key => $valor

    $sql_init = substr($sql_init, 0, -1);
    $sql_final = substr($sql_final, 0, -1);

 
    $sql.= " ( " . $sql_init . " ) VALUES (" . $sql_final . ")   ;";

    // echo $sql;

    $result = $connect->query($sql);

    if ($connect->error)
        {
        die('Error, insert query failed ' . $connect->error . $sql);
        }
    elseif ($result)
        {
        return (true);
        } //$result
    else
        {
        return (false);
        }
    }

function mysql_con()
    {
    global $cfg;
    global $connect;
    $i = 0;
    while ($i < 100)
        {
        $connect = new mysqli($cfg['db_server'], $cfg['db_user_name'], $cfg['db_password'], $cfg['db_database']);
        if ($connect->connect_error > 0)
            {
            print ('Unable to connect to database [' . $connect->connect_error . ']');
            sleep(10);
            $i = $i + 10;
            } //$connect->connect_errno > 0
          else
            {
            $i = 100;
            $connect_DB = true;
            }
        } //$i < 100+
    return $connect;
    }

/**
 * [array_search_partial busca un string en un valor de un array y devuelve el key]
 * @param  [type] $arr     [description]
 * @param  [type] $keyword [description]
 * @return [type]          [description]
 */

function array_search_partial($arr, $keyword)
    {
    foreach($arr as $index => $string)
        {
        if (strpos($keyword, $string) !== FALSE) return $index;
        } //$arr as $index => $string
    }

/**
 * [partial_search_array Busca si existe parte de un string en un array]
 * @param  [type] $haystack [description]
 * @param  [type] $needle   [description]
 * @return [type]           [description]
 */

function partial_search_array($haystack, $needle)
    {
    foreach($haystack as $item)
        {
        if (strpos($item, $needle) !== false)
            {
            return true;
            } //strpos( $item, $needle ) !== false
        } //$haystack as $item
    return false;
    }

/**
 * [array_search_multiarray_strpos busca en un array por un key y devuelve si encuentra el string ]
 * @param  [type] $array            [description]
 * @param  [type] $field            [description]
 * @param  [type] $string_to_search [description]
 * @return [type]                   [description]
 */

function array_search_multiarray_strpos($array, $field, $string_to_search)
    {
    foreach($array as $key => $p)
        {

        // if ( $p[ $field ] === $value )

        if (strpos($string_to_search, $p[$field]) !== FALSE) return $p;
        } //$array as $key => $product
    return false;
    }

/**
 * [partial_search_array Busca si existe parte de un string en un array]
 * @param  [type] $haystack [description]
 * @param  [type] $needle   [description]
 * @return [type]           [description]
 */

function partial_search_array_special($haystack, $needle)
    {
    foreach($haystack as $item)
        {
        if (count($needle) > count($item))
            { //busco item en needle
            if (strpos($item, $needle) !== false)
                {
                return true;
                } //strpos( $item, $needle ) !== false
            } //count( $needle ) > count( $item )
          else
            { //busco needle en item
            if (strpos($needle, $item) !== false)
                {
                return true;
                } //strpos( $needle, $item ) !== false
            }
        } //$haystack as $item
    return false;
    }

/**
 * [get_total_rules_active Get the total record of active rules]
 * @return [type] [description]
 */

function get_total_rules_active($status=false)
    {
    global $connect;
    $SQL = "SELECT count(*) as TOTAL FROM `block_queue` ";
    if (is_numeric($status)) $SQL.=" WHERE que_processed='".$status."' ;";
    // echo $SQL;
    if (!$result = $connect->query($SQL))
        {
        die('There was an error running the query [' . $connect->error . ']');
        } //!$result = $connect->query( $SQL )
    $row = $result->fetch_assoc();
    return $row[TOTAL];
    }

/**
 * [get_rules_db Get array with rules on DB]
 * @return [type] [description]
 */

function get_rules2block_db()
    {
    global $connect;
    $SQL = "SELECT * FROM sigs_to_block order by sig_name ;";
    if (!$result = $connect->query($SQL))
        {
        die('There was an error running the query [' . $connect->error . ']');
        } //!$result = $connect->query( $SQL )
    while ($row = $result->fetch_assoc())
        {
        $array_tmp[] = $row;
        } //$row = $result->fetch_assoc()
    return $array_tmp;
    }

/**
 * [check_connect_router_API check API connection ]
 * @return [type] [description]
 */

function check_connect_router_API()
    {
    global $router;
    require ('routeros_api.php');

    $API = new RouterosAPI();
    if ($API->connect($router['ip'], $router['user'], $router['pass'])) return "<span class='label label-success '>OK</span>";
      else return ('Unable to connect to RouterOS. Error:' . $e);
    $API->disconnect();
    }

/**
 * [check_service_running check service running]
 * @param  string $service [description]
 * @return [type]          [description]
 */

function check_service_running($service = "ids")
    {
    global $cfg;
    if ($service == "ids") $cmd = "suricata -c /etc/suricata/suricata.yaml";
    elseif ($service == "ips")
    if (file_exists($cfg['PID_app_file'])) return "<span class='label label-success'>OK</span>";
      else return "<span class='label label-danger'>NO</span>";
    $cmd_exec = "ps ax | grep -v grep | grep '$cmd' | wc -l";

    // echo $cmd_exec;

    $ret = exec($cmd_exec);

    // return $ret;

    if ($ret) return "<span class='label label-success '>OK</span>";
      else return "<span class='label label-danger lead'>NO</span>";
    }

function obtiene_server_status()
    {

    // UPTIME

    $res[uptime] = shell_exec("uptime | grep -ohe 'up .*' | sed 's/,//g' | awk '{ print $2\" \"$3 }'");
    $res[server_uptime] = $res[uptime];

    // MEMORY

    if (false === ($str = @file("/proc/meminfo"))) return false;
    $str = implode("", $str);
    preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
    $res['memTotal'] = round($buf[1][0], 2);
    $res['memFree'] = round($buf[2][0], 2);
    $res['memCached'] = round($buf[3][0], 2);
    $res['memUsed'] = ($res['memTotal'] - $res['memFree']);
    $res['memPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memUsed'] / $res['memTotal'] * 100, 2) : 0;
    $res['memRealUsed'] = ($res['memTotal'] - $res['memFree'] - $res['memCached']);
    $res['memRealPercent'] = (floatval($res['memTotal']) != 0) ? round($res['memRealUsed'] / $res['memTotal'] * 100, 2) : 0;
    $res['swapTotal'] = round($buf[4][0], 2);
    $res['swapFree'] = round($buf[5][0], 2);
    $res['swapUsed'] = ($res['swapTotal'] - $res['swapFree']);
    $res['swapPercent'] = (floatval($res['swapTotal']) != 0) ? round($res['swapUsed'] / $res['swapTotal'] * 100, 2) : 0;

    // LOAD AVG

    if (false === ($str = @file("/proc/loadavg"))) return false;
    $str = explode(" ", implode("", $str));
    $str = array_chunk($str, 4);
    $res['loadAvg'] = implode(" ", $str[0]);
    $res['memTotal'] = filesize_format($res['memTotal'] * 1024);
    $res['memUsed'] = filesize_format($res['memUsed'] * 1024);
    $res['memCached'] = filesize_format($res['memCached'] * 1024);
    $res['swapTotal'] = filesize_format($res['swapTotal'] * 1024, '', 'GB');
    $res['swapFree'] = filesize_format($res['swapFree'] * 1024);
    $res['swapUsed'] = filesize_format($res['swapUsed'] * 1024);
    return $res;
    }

// * Format a number of bytes into a human readable format.
// * Optionally choose the output format and/or force a particular unit

function filesize_format($size, $level = 0, $precision = 2, $base = 1024)
    {
    $unit = array(
        'B',
        'kB',
        'MB',
        'GB',
        'TB',
        'PB',
        'EB',
        'ZB',
        'YB'
    );
    $times = floor(log($size, $base));
    return sprintf("%." . $precision . "f", $size / pow($base, ($times + $level))) . " " . $unit[$times + $level];
    }

// Facebook like

function format_fecha($time)
    {
    if ($time !== intval($time))
        {
        $time = strtotime($time);
        } //$time !== intval( $time )
    $d = time() - $time;
    if ($time < strtotime(date('Y-m-d 00:00:00')) - 60 * 60 * 24 * 3)
        {
        $format = 'F j';
        if (date('Y') !== date('Y', $time))
            {
            $format.= ", Y";
            } //date( 'Y' ) !== date( 'Y', $time )
        return date($format, $time);
        } //$time < strtotime( date( 'Y-m-d 00:00:00' ) ) - 60 * 60 * 24 * 3
    if ($d >= 60 * 60 * 24)
        {
        $day = 'Ayer';
        if (date('l', time() - 60 * 60 * 24) !== date('l', $time))
            {
            $day = date('l', $time);
            } //date( 'l', time() - 60 * 60 * 24 ) !== date( 'l', $time )
        return $day . " a las " . date('g:ia', $time);
        } //$d >= 60 * 60 * 24
    if ($d >= 60 * 60 * 2)
        {
        return intval($d / (60 * 60)) . " hours ago";
        } //$d >= 60 * 60 * 2
    if ($d >= 60 * 60)
        {
        return "1 hour ago";
        } //$d >= 60 * 60
    if ($d >= 60 * 2)
        {
        return intval($d / 60) . " minutes ago";
        } //$d >= 60 * 2
    if ($d >= 60)
        {
        return "a minute ago";
        } //$d >= 60
    if ($d >= 2)
        {
        return intval($d) . " seconds";
        } //$d >= 2
    return "a few seconds ago";
    }

function get_server_uptime()
    {
    $exec_uptime = preg_split("/[\s]+/", trim(shell_exec('uptime')));
    $uptime = $exec_uptime[2] . ' Days';
    return $uptime;
    }

/**
 * [is_IPv4_IPv6 chequea si es un ipv4 o ipv6]
 * @param  [type]  $ip [description]
 * @return boolean     [description]
 */

function is_IPv4_IPv6($ip = NULL)
    {
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
        {

        // echo "Valid IPv4";

        return 4;
        }
    elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
        {

        // echo "Valid IPv6";

        return 6;
        }

    return false;
    }

/**
 * [check_ip_in_whilelist check if ip is in whilelist]
 * @param  [type] $ip [description]
 * @return [type]     [description]
 */

function check_ip_in_whilelist($ip = NULL)
    {
    global $cfg;
    foreach($cfg['whitelist'] as $range)
        {
        if (ip_in_range($ip, $range)) return true;
        }

    return false;
    }

/**
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */

function ip_in_range($ip, $range)
    {
    if (strpos($range, '/') == false)
        {
        $range.= '/32';
        }

    // $range is in IP/CIDR format eg 127.0.0.1/24

    list($range, $netmask) = explode('/', $range, 2);
    $range_decimal = ip2long($range);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal = ~ $wildcard_decimal;
    return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

/**
 * [json_object_to_html show json on html]
 * @param  [type] $json_object_string [description]
 * @return [type]                     [description]
 */

function json_object_to_html($json_object_string)
    {
    $json_object = json_decode($json_object_string);
    if (!is_object($json_object))
        {
        if (is_array($json_object))
            {
            $result = "[ <br />";
            foreach($json_object as $json_obj)
                {
                $result.= "<div style='margin-left:30px'>" . json_object_to_html(json_encode($json_obj)) . "</div>";
                if (end($json_object) != $json_obj) $result.= ",";
                }

            return $result . "  ] <br />";
            }
          else return json_decode($json_object_string);
        }

    $result = "";
    foreach($json_object as $key => $value)
        {
        $str_value = json_object_to_html(json_encode($value));
        $result.= "<span><span style='font-weight: bold'>$key : </span>$str_value</span><br />";
        }

    return $result;
    }
/**
 * [show_credits credits]
 * @return [type] [description]
 */
function show_credits()
    {
    return '<div class="panel panel-default">
                               <div class="panel-heading">
                                MKE Solutions
                               </div>
                               <div class="panel-body">
                                 <p> <b>Suricata2Mikrotik CE</b> -Community Edition- '.VERSION.' </p>
                                <p>   Designed by <a href="http://maxid.com.ar" target="_blank">Maximiliano Dobladez</a></p>
                               </div>
                           </div>';
    }