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
error_reporting( E_ALL );
error_reporting( 0 );
//include the config DB and API.

$dir_panel = realpath(dirname(__FILE__) . '/');

$DEBUG = true;
$DEBUG=false;
// if ( !$DEBUG )
// error_reporting( 0 );
require_once $dir_panel.'/../config.php';
require_once $dir_panel.'/../share/functions.php';

$url_update_rules = 'https://www.update.rules.mkesolutions.net/update.php?c=update';

#------ WHOIS TOOLS
$cfg[ipwhois]='http://noc.hsdn.org/whois/';
$cfg[aswhois]='http://noc.hsdn.org/aswhois/';
$confirm = "onClick=\"return confirm('Est&aacute; seguro que desea continuar?')\"";


mysql_con();

#---- REQUESTS
if ( isset( $_REQUEST[ 'c' ] ) )
    $cmd = trim( $_REQUEST[ 'c' ] ); //command
if ( isset( $_REQUEST[ 'id' ] ) )
    $id = trim( $_REQUEST[ 'id' ] ); //id

if ( isset( $_REQUEST[ 'sid' ] ) )
    $sid = trim( $_REQUEST[ 'sid' ] ); //sid

if ( isset( $_REQUEST[ 'que_id' ] ) )
    $que_id = trim( $_REQUEST[ 'que_id' ] ); //cid


if ( isset( $_REQUEST[ 'sig_name' ] ) )
    $sig_name = trim( $_REQUEST[ 'sig_name' ] ); //sig_name
if ( isset( $_REQUEST[ 'src_or_dst' ] ) )
    $src_or_dst = trim( $_REQUEST[ 'src_or_dst' ] ); //src_or_dst
if ( isset( $_REQUEST[ 'timeout' ] ) )
    $timeout = trim( $_REQUEST[ 'timeout' ] ); //timeout
    $active = trim( $_REQUEST[ 'active' ] ); //active
#---- REQUESTS
 

    if ($cmd)
        {
        if ($cmd == "check_connect_router_API")
            {
            echo check_connect_router_API();
            }
        elseif ($cmd == "edit_rule_save")
            {
            ($active == "on") ? $active_tmp = 1 : $active_tmp = 0;
            if ($id == "new") $sql_query = "INSERT INTO   sigs_to_block ( active, sig_name, src_or_dst,timeout )
                                VALUES ( '$active','$sig_name','$src_or_dst','$timeout' )";
              else $sql_query = "UPDATE sigs_to_block SET active='$active_tmp', sig_name='$sig_name', src_or_dst='$src_or_dst', timeout='$timeout' WHERE id=$id ;";
            if (!$result = $connect->query($sql_query))
                {
                die('There was an error running the query [' . $connect->error . ']');
                } //!$result = $db_->query( $sql_query )
              else
                {
                echo '<div class="alert alert-success"> <strong>OK Saved</strong> <i class="fa fa-refresh fa-spin"></i> Reloading... </div>';
                }
            } //$cmd == "edit_rule_save"
        elseif ($cmd == "delete")
            {
            if (!$id) return false;
            $SQL = "DELETE FROM sigs_to_block WHERE  id='$id'  ;";
            if (!$result = $connect->query($SQL))
                {
                die('There was an error running the query [' . $connect->error . ']');
                } //!$result = $db_->query( $SQL )
            mysqli_free_result($result);
            echo show_active_rules_db(); //show again the list rules
            }
        elseif ($cmd == "add")
            {
            echo show_form_edit_rule();
            }
        elseif ($cmd == "dashboard")
            {
            echo show_server_status();
            echo show_dashboard();
            }
        elseif ($cmd == "list_rule")
            {
            echo show_active_rules_db();
            }
        elseif ($cmd == "edit")
            {
            echo show_form_edit_rule($id);
            }
        elseif ($cmd == "view_event")
            {
            echo show_json_block_rule($que_id);
            }
        elseif ($cmd == "list_alert_found")
            {
            echo show_alert_found();
            }
        elseif ($cmd == "update")
        {     echo show_finish_loading();
              echo show_suricata_update_rules();
        }   elseif ($cmd == "run_suricata_update")
        {     echo run_suricata_update_rules();
             
        }



            echo   show_finish_loading();
            exit;

        }

/**
 * [show_finish_loading show hide loading div]
 * @return [type] [description]
 */
function show_finish_loading() {
         return '<script>hideSpinner();</script>';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suricata2MikroTik > Rules Administrator </title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">    
  <link href="a.css" rel="stylesheet" media="screen">

   <link rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"  >


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
</head>

<body>
    <!-- jQuery -->
    <script src="//code.jquery.com/jquery.js"></script>
    <!-- Bootstrap JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
        <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <nav class="navbar navbar-default navbar-fixed-top topbar">
        <div class="container-fluid">

            <div class="navbar-header">

                <a href="?" class="navbar-brand">
                    <span class="visible-xs">S2M</span>
                    <span class="hidden-xs"><i class="fa fa-shield"></i> Suricata2Mikrotik</span>
                </a>

                <p class="navbar-text">
                    <a href="#" class="sidebar-toggle">
                        <i class="fa fa-bars"></i>
                    </a>
                </p>

            </div>

        </div>
    </nav>

 <div class="animationload">
            <div class="osahanloading"></div>
        </div>

    <article class="wrapper">

        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li><a onclick=" get_data('?c=dashboard','central');" href="#"><i class="fa fa-dashboard"></i> <span>Active Blocked Rules</span></a></li>
            
                <li><a href="#" onclick=" get_data('?c=list_alert_found','central'); "><i class="fa fa-search"></i> <span>Suricata Alerts Found</span></a></li>

 <li><a href="#" onclick=" get_data('?c=list_rule','central'); "><i class="fa fa-edit"></i> <span>Rules Editor</span></a></li>
 <li><hr></li>
 <li><a href="#" onclick=" get_data('?c=update','central'); "><i class="fa fa-download"></i> <span>Update Rules</span></a></li>

            </ul>
        </aside>

        <section class="main">

            <section class="tab-content">

                <section class="tab-pane active fade in content">
                    <div id="central">
                    <?php
                        echo show_server_status();
                        echo show_dashboard();
                        ?>
                    </div>
                </section>

            </section>

        </section>

    </article>

    <script type="text/javascript">
      function get_data(a,b){showSpinner();if(null==b)var c="central";else var c=b;$.get(a,function(a){""!=a&&$("#"+c).html(a)});}$(document).on("click",".sidebar-toggle",function(){$(".wrapper").toggleClass("toggled")});  $(function () {  $("[rel='tooltip']").tooltip({html:true});  });
  
             
            function reloadPage() {window.location.reload();}
            function showSpinner() {
                 $('div.animationload').show();
            }
            function hideSpinner() {
                $('div.animationload').fadeOut('fast');
            }
                


            $(document).ready(function(){
            // $('#animationload').css('display','none');
            $('div.animationload').hide();
          //  $('#wrapper').css('display','block');
        });

</script>



  


</body>

</html>


<?php

function show_tooltip( ) {
    return '  <script type="text/javascript"> $(function () {  $("[rel=\'tooltip\']").tooltip({html:true});  }); </script>';
}
function show_active_rules_db( ) {
    global $connect;
    global $confirm;
    $SQL = "SELECT * FROM sigs_to_block  ORDER by sig_name LIMIT 200;";
    if ( !$result = $connect->query( $SQL ) ) {
        die( 'There was an error running the query [' . $connect->error . ']' );
    } //!$result = $connect->query( $SQL )
    $count = $result->num_rows;
    
    $str .= ' <div class="row">
                       
                         
                       
                       <div class="col-xs-12 col-sm-9">
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                  Active Alerts Rules (' . $count . ') <a  title="Add new rule" onclick="get_data(\'?c=add\',\'central\');" href="#" ><i class="fa fa-plus-circle"></i></a>
                               </div>
                               <div class="panel-body">
                                   <table class="table table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            <th></th> <th>Rule</th> <th>IP Block</th><th>Timeout</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        ( $row[ 'active' ] ) ? $color_str = 'success' : $color_str = 'info';
        $str .= '<tr><td><span class="label label-' . $color_str . '"><i class="fa fa-check"></i></span></td><td onclick="get_data(\'?c=edit&id=' . $row[ 'id' ] . '\',\'central\');" >' . $row[ 'sig_name' ] . '</td><td>' . $row[ 'src_or_dst' ] . '</td><td>' . $row[ 'timeout' ] . '</td><td> <a class="btn btn-xs btn-default" onclick="get_data(\'?c=edit&id=' . $row[ 'id' ] . '\',\'central\');"  href=# >  <i class="fa fa-edit"></i> </a> <a   class="btn btn-xs btn-danger"  onclick="get_data(\'?c=delete&id=' . $row[ 'id' ] . '\',\'central\');"  href=# > <i class="fa fa-trash"></i></a></td></tr>';
    } //$row = $result->fetch_assoc()
    $str .= '
                                    </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       
                       <div class="col-xs-12 col-sm-3">
                         <!---  <div class="panel panel-default">
                               <div class="panel-heading">
                                   Update Channel
                               </div>
                               <div class="panel-body">
                                   
                                   <a href=# onclick="get_data(\'?c=update\',\'central\');" ><i class="fa fa-refresh"></i> Update Rules</a>
                               </div>
                           </div> --->
                           
                           '.show_credits().'
                       </div>
                       
                   </div>';
    return $str;
}
function show_form_edit_rule( $id = NULL, $sid = NULL ) { //SID para importar regla
    global $connect;
    if ( !$id ) {
        $new       = true;
        $str_input = '<input type=hidden name="id" value="new">';
    } //!$id
    else {
        $SQL = "SELECT * FROM sigs_to_block  WHERE id=$id LIMIT 1;";
        if ( !$result = $connect->query( $SQL ) ) {
            die( 'There was an error running the query [' . $connect->error . ']' );
        } //!$result = $connect->query( $SQL )
        $row            = $result->fetch_assoc();
        $str_input      = '<input type=hidden name="id" value="' . $id . '">';
        $str_sig_name   = 'value="' . $row[ sig_name ] . '"';
        $str_src_or_dst = '<option value="' . $row[ src_or_dst ] . '" >' . $row[ src_or_dst ] . '</option>';
        $str_timeout    = 'value="' . $row[ timeout ] . '"';
        ( $row[ 'active' ] == 1 ) ? $str_active = "checked" : $str_active = '';
    }
    
 
    $str .= '
                    <div class="col-xs-12 col-sm-8">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                              
                                &nbsp;
                            </div>
                            <div class="panel-body">
                             <span id="show_result"></span>

                                <form class="form-horizontal" role="form" autocomplete=off  method="post" id="edit" >
                        ' . $str_input . '
                                    <fieldset>
                                        <legend>Add New Alert Rule</legend>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" for="name">Name Alert</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" required ' . $str_sig_name . ' name="sig_name" autofocus id="name" placeholder="Name Alert">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-3 control-label" for="src_or_dst">Target IP to Block</label>

                                            <div class="col-sm-6">
                                                <select name="src_or_dst" id="src_or_dst" class="form-control">
                                                ' . $str_src_or_dst . '
                                                    <option value="src">src</option>
                                                    <option value="dst">dst</option>
                                                </select>
                                                 <span id="helpBlock"></span>

                                            </div>

                                        </div>

                                        <div class="form-group">

                                            <label class="col-sm-3 control-label" for="timeout">Timeout </label>
                                            <div class="col-sm-3">
                                                <input type="text" class="form-control" ' . $str_timeout . ' name="timeout" value="01:00:00">
                                            </div>

                                            <div class="col-sm-2 ">
                                                <label for="active">Active
                                                    <input type="checkbox" name="active" ' . $str_active . ' value=on id="active">
                                                </label>
                                            </div>

                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                  <a onclick="get_data(\'?c=list_rule\',\'central\');" class="btn btn-default btn-lg"><i class="fa fa-backward"></i> Back</a>
                                                <button type="submit" id=save_btn class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save</button>

                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>
                        </div>
                    </div>
                   ' . $str_sidebar . '
                <script type="text/javascript">
                        $(document).ready(function() {
                            $(\'#save_btn\').click(function(e) {                                 
                                var dataS = $(\'form#edit\').serialize();
                                e.preventDefault();
                                $.ajax({
                                    type: "POST",
                                    url: \'index.php?c=edit_rule_save\',
                                    data: dataS,
                                    success: function(data) {
                                          $(\'#show_result\').html(data) ;
                                      
                                          setTimeout("get_data(\'?c=list_rule\',\'central\')", 1000);                                       
                                    }
                                })
                                return false;
                            });
                        });

                     

                    </script>
                  ';
    return $str;
}




/**
 * [get_update_rules Get the last update rule from cloud]
 * @return [type] [description]
 */
function get_update_rules( ) {
    global $url_update_rules;
    $update       = file_get_contents( $url_update_rules );
    $update_array = json_decode( $update, true );
    $db_rules     = get_rules_db();
    // echo var_dump($db_rules);
    // echo var_dump($update_array);
    $str .= '<div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Rules Update</h3>
        </div>
        <div class="panel-body">
            <div class="col-md-10">
           <form class="form-horizontal"  role="form" autocomplete=off  method="post" id="update_rules" >
            ';
    foreach ( $update_array as $value ) {
        
        // if ( array_search( $value[ sig_name ], array_column( $db_rules, 'sig_name' ) ) ) {
        // if ( array_search_partial(array_column( $db_rules, 'sig_name' ),  $value[ sig_name ]  ) ) {
        if ( partial_search_array_special( array_column( $db_rules, 'sig_name' ), $value[ sig_name ] ) ) {
            $value_tmp = '';
        } //partial_search_array_special( array_column( $db_rules, 'sig_name' ), $value[ sig_name ] )
        else {
            $value_tmp = "$value[sig_name]##$value[src_or_dst]##$value[timeout]";
            $str .= " 

             <div class='form-group'>   <label for='$value[sig_name]' >
                    <input type=checkbox checked name='updates_rules[]' value='$value_tmp'> $value[sig_name]
                    </label>
                    </div>";
            //$str.="NUEVO ".$value[sig_name]."<br>";
        }
    } //$update_array as $value
    $str .= '  <div class="form-group">
                                            <div class="col-sm-offset-3 col-sm-9">
                                                  <a onclick="get_data(\'?c=list_rule\',\'central\');" class="btn btn-default btn-lg"><i class="fa fa-backward"></i> Back</a>
                                                <button type="submit" id=save_btn class="btn btn-success btn-lg"><i class="fa fa-save"></i> Save</button>

                                            </div>

                                            <script type="text/javascript">
                        $(document).ready(function() {
                            $(\'#save_btn\').click(function(e) {                                 
                                var dataS = $(\'form#update_rules\').serialize();
                                e.preventDefault();
                                $.ajax({
                                    type: "POST",
                                    url: \'index.php?c=save_rule_db\',
                                    data: dataS,
                                    success: function(data) {
                                          $(\'#show_result\').html(data) ;
                                      
                                          setTimeout("get_data(\'?c=list_rule\',\'central\')", 1000);                                       
                                    }
                                })
                                return false;
                            });
                        });

                        

                    </script>


                    ';
    $str .= '
            </form>
            <div>
        </div>
    </div>';
    return $str;
}
/**
 * [show_dashboard show welcome panel for stats]
 * @return [type] [description]
 */
function show_dashboard( ) {
    global $connect;
    $SQL = "SELECT *,inet_ntoa(que_ip_adr) as ip FROM block_queue group by que_ip_adr order by que_event_timestamp desc LIMIT 50;";
    if ( !$result = $connect->query( $SQL ) ) {
        die( 'There was an error running the query [' . $connect->error . ']' );
    } //!$result = $connect->query( $SQL )
    // $count = $result->num_rows;
   $count = get_total_rules_active();
    $str .= ' <div class="row">
                       
                         
                       
                       <div class="col-xs-12 col-sm-7">
                           <div class="panel panel-default">
                               <div class="panel-heading">
                                Active Alert Blocked (' . $count . ')  - Time: ' . date( "H:i:s", time() ) . '
                               </div>
                               <div class="panel-body" style=" max-height: 900px;
            overflow:auto;">
                                   <table class="table table-condensed table-hover" >
                                    <thead>
                                        <tr>
                                                <th> <i class="fa fa-clock-o"></i> Time</th><th>IP Block</th> <th>Rule</th><th class="hidden-xs">SID</th><th class="hidden-xs">Action</th> 
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        $str .= '<tr><td> ' . format_fecha( $row[ 'que_event_timestamp' ] ) . '</td> <td>' . view_whois_ip($row[ 'ip' ]) . '</td><td >' . $row[ 'que_sig_name' ] . '</td><td class="hidden-xs"><a target=_blank rel=tooltip title="View Rule Alert" href=http://doc.emergingthreats.net/' . $row[ 'que_sig_sid' ] . '>' . $row[ 'que_sig_sid' ] . '</a></small></td><td class="hidden-xs">

        <a class="btn btn-xs btn-default" id="view_event" target=_blank href=# data-cid="index.php?c=view_event&que_id=' . $row[ 'que_id' ] . '" title="View Event" rel="tooltip" ><i class="fa fa-eye"></i></a>

        </small></td> </tr>';
    } //$row = $result->fetch_assoc()
    $str .= '
                                    </tbody>
                                   </table>
                               </div>
                           </div>
                       </div>
                       
                       <div class="col-xs-12 col-sm-5">
                           <div class="panel panel-default">
                               <div class="panel-heading">

                                  Active Top Ten IP Attack
                               </div>

                                <div class="panel-body">
                                 ';
    $str .= show_table_top_ten( 1 );
    $str .= '
                               </div>
                              
                           </div>


                           
                           <div class="panel panel-default">
                               <div class="panel-heading">

                                  Active Top Ten Alert Rules
                               </div>

                                <div class="panel-body">
                                 ';
    $str .= show_table_top_ten( 2 );
    $str .= '
                               </div>
                              
                           </div>
                           
                         '.show_credits().'
                       </div>
                       
                   </div>


<div class="modal fade in slacker-modal " tabindex="-1" role="dialog" id="preview_event" aria-hidden="false">
            <div class="modal-dialog modal-slacker modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <br><br> <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h4 class="modal-title" >Event Viewer </h4>
                    </div>
                    <div class="modal-body"> 


                        <div id="show_event"> </div>


                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div>

                   ';
    
    $str .= "<script>$('a#view_event').click(function(e){
    var anchor = this;
    $('#preview_event').modal({show:true});   
     $('#show_event').load($(anchor).attr('data-cid'));  
    return false;
    });</script>";
    return $str . show_tooltip();
}
/**
 * [show_table_top_ten show tables with TOP TEN]
 * @param  string $type [description]
 * @return [type]       [description]
 */
function show_table_top_ten( $type = '1' ) {
    global $connect;
    global $cfg;
    if ( $type == "1" ) {
        $sql_query = "SELECT  inet_ntoa(que_ip_adr) as ip , count(*) as total FROM block_queue GROUP BY que_ip_adr
                    ORDER BY count(*) DESC LIMIT 10;";
        $str_th    = '  <th>Count</th> <th>IP Block</th>  <th>Country</th>';
    } //$type == "1"
    else {
        $sql_query = "SELECT  que_sig_name,que_sig_sid ,count(*) as total FROM block_queue GROUP BY que_sig_name 
                    ORDER BY count(*) DESC LIMIT 10;";
        $str_th    = '  <th>Count</th> <th>Alert</th>  <th>Sid</th>';
    }
    if ( !$result = $connect->query( $sql_query ) ) {
        die( 'There was an error running the query [' . $connect->error . ']' );
    } //!$result = $connect->query( $sql_query )
    $count = $result->num_rows;
    $str .= '   <table class="table table-condensed table-hover">
                                    <thead>
                                        <tr>
                                            ' . $str_th . '
                                        </tr>
                                    </thead>
                                    <tbody>   ';
    while ( $row = $result->fetch_assoc() ) {
        if ( $type == "1" ) {
            $str .= '<tr><td><small class="label label-default">' . $row[ 'total' ] . '</small></td>  <td ><small> ' . view_whois_ip($row[ 'ip' ]) . ' </small></td> <td ><small>' . geoip_country_name_by_name( $row[ 'ip' ] ) . '</small></td> </tr>';
        } //$type == "1"
        else {
            $str .= '<tr><td><small class="label label-default">' . $row[ 'total' ] . '</small></td>  <td ><small>' . $row[ 'que_sig_name' ] . '</small></td> <td ><small><a target=_blank rel=tooltip title="View Rule Alert" href=http://doc.emergingthreats.net/' . $row[ 'que_sig_sid' ] . '>' . $row[ 'que_sig_sid' ] . '</a></small></td> </tr>';
        }
    } //$row = $result->fetch_assoc()
    $str .= '
                                    </tbody>
                                   </table>';
    return $str . show_tooltip();
}

function view_whois_ip($ip) {
    global $cfg;
    return '<a  href="'.$cfg[ipwhois].$ip.'" target="_blank" class="text-primary" rel=tooltip title="View IP Info">' . $ip . '</a>';
}

function show_server_status( ) {
    
    $data = obtiene_server_status();
    
    // echo var_dump($data);
    $str .= '  <div class="row">
                         <div class="col-xs-12 col-sm-2">
                            <div class="panel panel-primary">
                                <div class="panel-body">
                                  <h4 class="text-center"><strong><i class="fa fa-shield"></i> Suricata2MikroTik IPS </strong>  </h4> 
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-6">
                            <div class="panel panel-primary">
                                <div class="panel-body">
                                 <i class="fa fa-square"></i> Uptime  <strong class="lead">' . $data[ server_uptime ] . '</strong>  &nbsp;&nbsp;
                                  <i class="fa fa-square"></i>  Load Avr: <strong class="lead">' . $data[ loadAvg ] . '</strong> &nbsp;&nbsp;
                                  <i class="fa fa-square"></i>  MEM Free: <strong class="lead">' . $data[ memPercent ] . '%</strong>  
                                   
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xs-12 col-sm-4">
                            <div class="panel panel-primary">
                                <div class="panel-body">
                                  <strong> Suricata IDS: </strong><span class="lead">' . check_service_running( 'ids' ) . ' </span> &nbsp;&nbsp;
                                
                                <strong>   IPS Daemon: </strong> <span class="lead">' . check_service_running( 'ips' ) . '  </span> &nbsp;&nbsp; 
                                <strong>   API: </strong> <span class="lead" id="check_connect_router_API"> <i class="fa fa-refresh fa-spin"></i> </span>  

                                </div>
                            </div>
                        </div>

                       
                     <script type="text/javascript">
                      $(document).ready(function(){return $.ajax({type:"POST",url:"index.php?c=check_connect_router_API",success:function(a){$("#check_connect_router_API").html(a)}}),!1});
                        </script>
                       
                     
                       
                   </div>';
    return $str;
}

/**
 * [show_json_block_rule show json information of blocked rule]
 * @param  [type] $que_id [description]
 * @return [type]         [description]
 */
function show_json_block_rule($que_id=NULL) {
    if (!$que_id) return false;
    $info=get_block_queue_db($que_id);
    $raw_raw=unserialize($info[json_raw]);
    $raw=json_encode($raw_raw);
    echo '  <pre id="json_tmp">'.json_object_to_html($raw).'</pre>'; 
 
}

function show_alert_found(){
   
    if ( isset( $_REQUEST[ 'severity' ] ) )
             $severity = intval( $_REQUEST[ 'severity' ] ); 

        $return.='
 
        

        <div class="panel panel-default">
  
            <div class="panel-heading">Alerts Rule Found</div>
            <div class="panel-body">
                <p>Last Alert Found at Eve.json - Suricata File</p>
                <div class="btn-toolbar">
                    <div class="btn-group">
                      <button onclick=" get_data(\'?c=list_alert_found\',\'central\'); " type="button" class="btn btn-xs btn-default">ALL</button>
                    </div>
                    <div class="btn-group">
                    <button onclick=" get_data(\'?c=list_alert_found&severity=1\',\'central\'); " type="button" class="btn btn-xs btn-default">Severity 1</button>
                    <button onclick="  get_data(\'?c=list_alert_found&severity=2\',\'central\'); " type="button" class="btn btn-xs btn-default">Severity 2</button>
                    <button onclick=" get_data(\'?c=list_alert_found&severity=3\',\'central\'); "  type="button" class="btn btn-xs btn-default">Severity 3</button>
                    </div>
 
                     
                </div>
               
            </div>
        
       
            <table id="table_found" class="table table-condensed table-hover table-striped">
                <thead>
                    <tr>
                        <th>&nbsp;&nbsp;&nbsp;</th> 
                        <th>Date</th> 
                        <th>Protocol</th>
                        <th>Source</th>
                        <th>Destination</th>
                         <th>Signature</th>
                         <th>Category</th>
                         <th>Severity</th>
                    </tr>
                </thead>
                <tbody> 
              ';

        // $handle = popen("tail  -n 9999 /var/log/suricata/eve.json 2>&1", 'r');
        $eve_json_path="/var/log/suricata/eve.json";

        if (is_integer($severity)) {
            $handle = popen("tail -n 99999 $eve_json_path|jq -c 'select(.alert.severity==$severity)' 2>&1", 'r');
        }else {
            $handle = popen("tail -n 9999 $eve_json_path|jq -c 'select(.event_type==\"alert\")' 2>&1", 'r');
        }
        // $handle = popen("tail -n 9999 $eve_json_path|jq -c 'select(.event_type==\"alert\")|select(.alert.signature==\"ET\")' 2>&1", 'r');
        // select(.event_type==\"alert\")|
        // $handle = popen("tail -n 200 /var/log/suricata/eve.json 2>&1", 'r');
        while(!feof($handle)) {
          
               $buffer = fgets($handle);
      
            
                $array = json_decode($buffer, true);
                $array=array_reverse($array,true);
                $time = $array['timestamp'];
                $date = strtotime($time);
                $fixed = date('l, F d Y g:iA', $date);
                $date_db = date("Y-m-d H:i:s", $date);

                if (isset($array['alert']['signature']) && !empty($array['alert']['signature'])) {
                    $return.= "<tr>    <td>&nbsp;&nbsp;&nbsp;</td>                    
                        <td width='220px'><span rel=tooltip title='".$fixed."'> " . format_fecha($date_db) . "</span></td>
                        <td width='10px' >" . $array['proto'] . "</td>
                        <td >" . $array['src_ip'] . ":<small>" . $array['src_port'] . "</small></td>
                        <td >" . $array['dest_ip'] . ":<small>" . $array['dest_port'] . "</small></td>
                        <td >" . $array['alert']['signature'] . "</td>
                        <td >" . $array['alert']['category'] . "</td>
                        <td  >" . $array['alert']['severity'] . "</td>
                      
                      </tr>";

                         // $return.=serialize($array);
                }
 
            }
       $return.='  </tbody>
            </table>
        </div>
             <script>$(document).ready( function () {
                    $(\'#table_found\').DataTable({ order: [ 1, \'desc\' ],   responsive: true,  select: \'single\', stateSave: true });
                    } );</script>
        '.show_tooltip( ) ;
        pclose($handle);
       return $return;
}
function show_suricata_update_rules(){
    echo ' <div class="panel panel-primary">
       
        <div class="panel-body">
             <div class="col-md-12">
        <h3><span id="title_loading">Loading data... Please wait.</span></h3>
        <div class="progress progress-striped active page-progress-bar">
            <div class="progress-bar" style="width: 100%;"></div>
        </div>
        </div>
        <div class="col-md-12"> 
         <script type="text/javascript">
                      $(document).ready(function(){return $.ajax({type:"POST",url:"index.php?c=run_suricata_update",success:function(a){$("#show_div_update").html(a)}}),!1});
                        </script>
            <span id="show_div_update"></span>
        </div>

        </div>
    </div>';
}
function run_suricata_update_rules() {
        echo show_finish_loading();
        ob_flush();
        flush();
       $handle = popen("cd /var/www/html/suricata2mikrotik/bin/; ./php_root /var/www/html/suricata2mikrotik/bin/suricata-update.cron    2>&1", 'r');
        
        while(!feof($handle)) {
                $buffer = fgets($handle);
                echo "$buffer<br/>\n";
                ob_flush();
                flush();
        }
        pclose($handle);

        echo '<script type="text/javascript">
                       $("#title_loading").html(\'OK - Finished\')} 
                        </script>';

}
?>