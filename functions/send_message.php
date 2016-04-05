<?php
require_once '../Lib/db.inc.php';

$user_id = trim($_POST['user_id']);
$chat_message = addslashes(trim($_POST['chat_message']));
$from = intval($_POST['from']);

if (!$_POST || !isset($user_id) || !isset($chat_message) || !isset($from)){
    echo '出错啦, Error code: 100';
    exit();
}
$conn = mysql_connect(DB_HOST,DB_ADMIN,DB_PWD);
$prefix = DB_TB_PRE;

$exp = ceil(strlen($chat_message)/10);

if ($conn){
    mysql_select_db(DB_NAME);
    mysql_query("set names utf8mb4;");
    $sql = "INSERT INTO `{$prefix}chat` (`user_id`,`chat_message`,`from`) VALUES ('{$user_id}','{$chat_message}',{$from});";
    
    if (mysql_query($sql,$conn)){
        // Succeeded
        $sql = "SELECT * FROM `{$prefix}myai` WHERE `user_id` = '{$user_id}';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        
        if ($row['user_id']){
            // Add experience
            if ($from){
                $ai_config = require('ai.config.php');
                $curr_exp = $row['exp'];
                $unLevel = true;
                $oldLevel = 1;
                $newLevel = 1;
                foreach ($ai_config['levels'] as $key => $value){
                    if ($curr_exp < $value && $unLevel){
                        $oldLevel = intval($key-1);
                        $unLevel = false;
                    }
                }
                $sql = "UPDATE `{$prefix}myai` SET `exp` = `exp`+{$exp};";
                if (mysql_query($sql,$conn)){
                    $new_exp = $curr_exp+$exp;
                    $unLevel = true;
                    foreach ($ai_config['levels'] as $key => $value){
                        if ($new_exp < $value && $unLevel){
                            $newLevel = intval($key-1);
                            $unLevel = false;
                        }
                    }
                    if ($newLevel > $oldLevel){
                        echo '2';
                    } else {
                        echo '1';
                    }
                } else {
                    echo '-1';
                }
            } else {
                echo '1';
            }
        } else {
            // Create record if not exists
            $sql = "INSERT INTO `{$prefix}myai` (`user_id`) VALUES ('{$user_id}');";
            if (mysql_query($sql,$conn)){
                echo '1';
            }
        }
    } else {
        // Failed
        echo '-1';
    }
} else {
    die('数据库连接出错啦');
}