<?php
require_once '../Lib/db.inc.php';

$user_id = intval($_GET['user_id']);
$offset = $_GET['offset']?intval($_GET['offset']):0;

if (!$_GET){
    echo '错误 101';
    exit();
} else {
    $conn = mysql_connect(DB_HOST,DB_ADMIN,DB_PWD);
    if ($conn){
        $prefix = DB_TB_PRE;
        $display = 15;
        mysql_select_db(DB_NAME);
        mysql_query("SET NAMES utf8mb4;");
        
        $sql = "SELECT COUNT(*) FROM `{$prefix}chat` WHERE `user_id` = {$user_id};";
        $res = mysql_query($sql);
        $page = 0;
        
        while($row = mysql_fetch_assoc($res)){
            if ($offset){
                $page = $row['COUNT(*)']-($offset+1)*$display;
                if ($page<=0){
                    $page += $display;
                    $sql = "SELECT * FROM `{$prefix}chat` WHERE `user_id` = {$user_id} ORDER BY `chat_time` LIMIT {$page};";
                } else {
                    $sql = "SELECT * FROM `{$prefix}chat` WHERE `user_id` = {$user_id} ORDER BY `chat_time` LIMIT {$page},{$display};";
                }
            } else {
                if ($row['COUNT(*)']>$display){
                    $page = $row['COUNT(*)']-$display;
                    $sql = "SELECT * FROM `{$prefix}chat` WHERE `user_id` = {$user_id} ORDER BY `chat_time` LIMIT ".intval($page).",{$display};";
                } else {
                    $sql = "SELECT * FROM `{$prefix}chat` WHERE `user_id` = {$user_id} ORDER BY `chat_time` LIMIT {$display};";
                }
            }
        }
        
        $data = array();
        $status = "ok";
        
        $res = mysql_query($sql);
        $count = 0;
        while ($row = mysql_fetch_assoc($res)){
            $data[$count]['chat_time'] = $row['chat_time'];
            $data[$count]['chat_message'] = $row['chat_message'];
            $data[$count]['from'] = $row['from'];
            $count++;
        }
        if ($count == 0){
            $status = 'error';
        }
        if ($offset){
            $data = array_reverse($data);
        }
        $result = array('status'=>$status,'data'=>$data);
        echo json_encode($result);
        mysql_free_result($res);
    }
}