<?php
require_once '../Lib/db.inc.php';
require_once 'functions.php';

header("Content-type: text/html;charset=utf8");
date_default_timezone_set('PRC');

$original_message = trim($_REQUEST['message']);
$message = strtolower(addslashes(htmlspecialchars(trim($_REQUEST['message']))));
//$message = preg_replace('# #', '', $message);
$user_id = trim($_REQUEST['user_id']);

$showapi_appid = '9657';
$showapi_sign = '5dc5cecaa4f34df8826e67523a3666cf';
$showapi_timestamp = date('YmdHis');

$hasCustomReply = false;

$from_wechat = $_REQUEST['from_wechat']?true:false;

if ($_REQUEST){
    $conn = mysql_connect(DB_HOST,DB_ADMIN,DB_PWD);
    if ($conn){
        mysql_select_db(DB_NAME);
        $prefix = DB_TB_PRE;
        mysql_query("SET NAMES utf8mb4;");
        
        $sql = "SELECT * FROM `{$prefix}myai` WHERE `user_id` = '{$user_id}';";
        $res = mysql_query($sql);
        $row = mysql_fetch_array($res);
        $myai = $row;
        $ai_config = require('ai.config.php');

        if (stripos($message,"小a学话")!==false && $user_id){
            // AI learn how to reply based on user's demand
            if (isset($myai)){
                $temp = substr($original_message,stripos($original_message,"|")+strlen("|"));
                $question = substr($temp,0,stripos($temp,"|"));
                $reply = substr($original_message,strlen("小a学话|")+strlen($question)+strlen("|"));
                $exp = $myai['exp'];
                $gotLevel = false;
                
                if ($exp >= $ai_config['levels']['10']){
                    $level = '10';
                } else {
                    foreach ($ai_config['levels'] as $key => $value){
                        if ($exp < $value && !$gotLevel){
                            $level = intval($key-1);
                            $gotLevel = true;
                        }
                    }
                }
                $learn_times = $ai_config['learn_times'][$level];
                
                $sql = "SELECT COUNT(*) FROM `{$prefix}custom_reply` WHERE `user_id` = '{$user_id}';";
                $res = mysql_query($sql);
                $row = mysql_fetch_array($res);
                
                if (isset($row)){
                    $learned_times = $row['COUNT(*)'];
                    $times_left = intval($learn_times - $learned_times);
                    if ($times_left > 0){
                        $sql = "INSERT INTO `{$prefix}custom_reply` (`reply_message`,`trigger`,`user_id`) VALUES ('{$reply}','{$question}','{$user_id}');";
                        if (mysql_query($sql,$conn)){
                            if ($from_wechat){
                                echo '已学习~ 感谢主人教我';
                            } else {
                                echo 'LEARNED';
                            }
                            exit();
                        }
                    } else {
                        if ($from_wechat){
                            echo '已达学习次数上限。。';
                        } else {
                            echo 'LEARN_TIMES_LIMIT';
                        }
                        exit();
                    }
                }
            }
        }
        $sql = "SELECT * FROM `{$prefix}custom_reply` WHERE `trigger` LIKE '%{$message}%' AND `user_id` = '{$user_id}';";
        $res = mysql_query($sql);
        $customIndex = 0;
        
        while ($row = mysql_fetch_assoc($res)){
            $reply = $row['reply_message'];
            $customIndex++;
        }
        if ($customIndex>0) $hasCustomReply = true;
        if ($hasCustomReply) {
            echo $reply;
            exit();
        }
        
        $sql = "SELECT `trigger` FROM `{$prefix}reply`;";
        $res = mysql_query($sql);
        
        $triggers = array();
        $keywords = array();
        $i = 0;
        
        while ($row = mysql_fetch_assoc($res)){
            // Store keywords
            $triggers[$i] = $row['trigger'];
            $i++;
        }
        foreach ($triggers as $k => $v){
            if (strpos($v,',')){
                $temp = array();
                $temp = explode(',',$v);
                foreach ($temp as $value){
                    if (strpos($message,$value) !== false){
                        $trigger = $v;
                    }
                }
            } else {
                if (strpos($message,$v) !== false){
                    $trigger = $v;
                    break;
                }
            }
        }
        // Get reply
        if (isset($trigger)){
            $sql = "SELECT * FROM `{$prefix}reply` WHERE `trigger` = '{$trigger}';";
        } else {
            $sql = "SELECT * FROM `{$prefix}reply` WHERE `trigger` LIKE '%{$message}%';";
        }
        $res = mysql_query($sql);

        $result = array();
        $i = 0;
        while($row = mysql_fetch_assoc($res)){
            $result[$i]['keywords'] = $row['trigger'];
            $result[$i]['answer'] = $row['reply_message'];
            $i++;
        }
        $index = array_rand($result);
        if (strpos($result[$index]['answer'],'`') !== false && $user_id){
            // Special answer
            $specialReplies = require('special_replies.php');
            $answerCode = substr($result[$index]['answer'],strlen('`'));
            
            // Get the current available skills
            $skill_list = array();
            $current_skill_triggers = "MY_XIAOA,SKILL_LIST,AGE,DATE,TIME,";
            $exp = $myai['exp'];

            foreach ($ai_config['levels'] as $key => $value){
                if ($exp >= $value){
                    $level = intval($key-1);
                }
            }
            foreach ($ai_config['skills'] as $key => $value){
                if ($level < $key - 1){
                    break;
                }
                $skill_list[] = $value;
            }
            for ($i = 0; $i <= $level; $i++){
                foreach($skill_list[$i] as $value){
                    $current_skill_triggers .= $value['skill_trigger'].",";
                }
            }
            if (strpos($current_skill_triggers,$answerCode) === false){
                echo '抱歉哦，小A暂时没有习得该技能，输入『技能列表』即可查看我目前掌握的全部技能了哦~';
                exit();
            }
            
            switch ($answerCode){
                case 'MY_XIAOA':
                    $sql = "SELECT COUNT(*) FROM `{$prefix}custom_reply` WHERE `user_id` = '{$user_id}';";
                    $res = mysql_query($sql);
                    $row = mysql_fetch_array($res);

                    if (isset($row)){
                        $learned_times = $row['COUNT(*)'];
                    }
                    if (isset($myai)){
                        $echostring = "";
                        $exp = $myai['exp'];
                        if ($exp >= $ai_config['levels']['10']){
                            $echostring = '好厉害呀~ 你已经让小A 满级了呢~ 真开心 (づ￣ ³￣)づ<hr /> 输入『技能列表』即可查看我目前掌握的全部技能了哦~<hr />输入『小A学话|问题|答案』(固定格式)即可让你教我如何回答噢，你一共有'.$ai_config['learn_times']['10'].'次 小A学话的机会呢, 已经使用了'.$learned_times.'次';
                            if ($from_wechat) $echostring = strip_tags($echostring);
                            exit($echostring);
                        } else {
                            foreach ($ai_config['levels'] as $key => $value){
                                if ($exp < $value){
                                    $level = intval($key-1);
                                    $left_exp = intval($value-$exp);
                                    $echostring = "报告(｡・`ω´･)/',我现在{$level}级了，经验 {$exp} exp, 距离升级还需要 {$left_exp} exp噢~(多陪我聊天即可增长经验~ (づ￣ ³￣)づ)<hr />输入『技能列表』即可查看我目前掌握的全部技能了哦~<hr />输入『小A学话|问题|答案』(固定格式)即可让你教我如何回答噢，你一共有".$ai_config['learn_times'][$level]."次机会呢, 已经使用了{$learned_times}次";
                                    if ($from_wechat) $echostring = strip_tags($echostring);
                                    exit($echostring);
                                }
                            }
                        }
                    }
                    break;
                case 'SKILL_LIST':
                    
                    echo "这是我的目前技能列表~<br />";
                    if ($from_wechat){
                        for ($i = 0; $i <= $level; $i++){
                            foreach ($skill_list[$i] as $value){
                                echo "   技能名称：《{$value['skill_name']}》 {$value['skill_description']}       ";
                            }
                        }
                    } else {
                        for ($i = 0; $i <= $level; $i++){
                            foreach ($skill_list[$i] as $value){
                                echo "<hr />技能名称：《{$value['skill_name']}》<br />{$value['skill_description']}";
                            }
                        }
                    }
                    exit();
                case 'NEWS':
                    if ($from_wechat) die('Sorry，微信中无法浏览新闻，请前往<a href="http://chat.abletive.com/">网页版小 A</a> 查看');
                     $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'page' => 1,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/109-35?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content_list = $result->showapi_res_body->pagebean->contentlist;
                        foreach ($content_list as $v){
                            if (count($v->imageurls) > 0){
                                echo '<img src="'.$v->imageurls[0]->url.'" />';
                            }
                            echo "《".$v->title."》<br />".$v->desc.", 来自<strong>".$v->source."</strong>, 新闻地址:<a href='".$v->link."' target='_blank'>链接</a><hr />";
                        }
                    }
                    break;
                case 'HISTORY_TODAY':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/119-42?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $list = $result->showapi_res_body->list;
                        if ($from_wechat){
                            foreach($list as $v){
                                echo $v->year."年的今天是".$v->title."的日子      ";
                            }
                        } else {
                            foreach($list as $v){
                                echo $v->year."年的今天是".$v->title."的日子<br /><br />";
                            }
                        }
                    }
                    break;
                case 'VIDEO':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'type' => '41',
                         'page' => rand(1,10),
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/255-1?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content_list = $result->showapi_res_body->pagebean->contentlist;
                        if ($from_wechat){
                            // Wechat request
                            $randIndex = array_rand($content_list);
                            echo $content_list[$randIndex]->video_uri;
                            exit();
                        } else {
                            $randIndex = array_rand($content_list);
                            echo '<video src="'.$content_list[$randIndex]->video_uri.'" controls></video><br />'.$content_list[$randIndex]->text;
                            exit();
                        }
                    }
                case 'AUDIO':
                    if ($from_wechat) exit('Sorry，微信中无法查看，请前往<a href="http://chat.abletive.com/">网页版小 A</a> 查看');
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'type' => '31',
                         'page' => rand(1,10),
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/255-1?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content_list = $result->showapi_res_body->pagebean->contentlist;
                        $randIndex = array_rand($content_list);
                        echo '<img src="'.$content_list[$randIndex]->image0.'" /><br />'.$content_list[$randIndex]->text.'<br /><audio src="'.$content_list[$randIndex]->voiceuri.'" controls></audio>';
                        exit();
                    }
                case 'NIGHT_PICS':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'type' => 33+rand(1,7),
                         'page' => rand(1,100),
                         'num' => 3,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/819-1?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content = $result->showapi_res_body;
                        if ($from_wechat){
                            // Wechat request
                            $i = array_rand($content);
                            echo $content->$i->thumb;
                        } else {
                            for ($i = 0; $i < 3; $i++){
                                echo '<img src="'.$content->$i->thumb.'" /><br />'.$content->$i->title.'<br /><hr />';
                            }
                        }
                    }
                    exit();
                case 'CHICK_PICS':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'page' => rand(1,150),
                         'num' => 2,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/197-1?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content = $result->showapi_res_body;
                        if ($from_wechat){
                            // From wechat request
                            $i = array_rand($content);
                            echo $content->$i->picUrl;
                        } else {
                            for ($i = 0; $i < 2; $i++){
                                echo '<hr /><img src="'.$content->$i->picUrl.'" /><br />'.$content->$i->description.'<br />';
                            }
                        }
                    }
                    exit();
                case 'GUESS':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'type' => 33+rand(1,7),
                         'page' => rand(1,100),
                         'num' => 3,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/151-2?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content = $result->showapi_res_body;
                        if ($from_wechat){
                            echo '谜语：'.$content->Title.', <a href="http://chat.abletive.com/mi.php?message='.$content->Answer.'">点击查看谜底</a>';
                        } else {
                            echo '谜语是：<strong>'.$content->Title.'</strong><br /><a href="javascript:void(0)" onclick="showAnswer($(this))"><span class="answer-hidden">谜底&gt;&gt;'.$content->Answer.'&lt;&lt;<br /></span>点击查看谜底</a>';
                        }
                    }
                    exit();
                case 'WECHAT':
//                    if ($from_wechat) die('Sorry, 微信中无法查看精选微信文章，请前往<a href="http://chat.abletive.com/">网页版小A</a>');
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'type' => '31',
                         'page' => rand(1,10),
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/582-2?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $content_list = $result->showapi_res_body->pagebean->contentlist;
                        if ($from_wechat){
                            foreach ($content_list as $v){
                                echo '『'.$v->title.'』，来自公众号 '.$v->userName.' <a href="'.$v->url.'">点击查看</a>     ';
                            }
                        } else {
                            foreach ($content_list as $v){
                                echo '<img src="'.$v->contentImg.'" /><br />『'.$v->title.'』，来自公众号&lt;'.$v->userName.'&gt;<br /><a href="'.$v->url.'" target="_blank">点击查看</a><hr />';
                            }
                        }
                    }
                    exit();
                case 'WEATHER_NOW':
                    if ($from_wechat) exit('Sorry, 请前往<a href="http://chat.abletive.com/">网页版小A</a>');
                    $cityid = require('get_cityid.php');
                    $url = "http://www.weather.com.cn/adat/sk/{$cityid}.html";
                    $weather = file_get_contents($url);
                    $weatherInfo = json_decode($weather,true);
                    $answer = "<strong>".$weatherInfo['weatherinfo']['city']."</strong> 今天".$weatherInfo['weatherinfo']['temp']."℃, 湿度为".$weatherInfo['weatherinfo']['SD'].", 风向：".$weatherInfo['weatherinfo']['WS']." ".$weatherInfo['weatherinfo']['WD'].".<br />";
                    $eID = rand(0,10);
                    $expression = require('expression.config.php');
                    $answer .= $expression[$eID];
                    break;
                case 'WEATHER_FORECAST':
                    if ($from_wechat){
                        $area = str_replace("天气预报","",$message);
                        if ($area == ""){
                            exit('请输入地名+天气预报，示例：深圳天气预报');
                        }
                        $paramArr = array(
                             'showapi_appid'=> $showapi_appid,
                             'needMoreDay' => 1,
                             'area' => $area,
                             'showapi_timestamp' => $showapi_timestamp
                        );
                        $sign = createSign($paramArr);
                        $strParam = createStrParam($paramArr);
                        $strParam .= 'showapi_sign='.$sign;
                        $url = 'http://route.showapi.com/9-2?'.$strParam; 
                    } else {
                        if($_SERVER['HTTP_CLIENT_IP']){
                             $onlineip=$_SERVER['HTTP_CLIENT_IP'];
                        }elseif($_SERVER['HTTP_X_FORWARDED_FOR']){
                             $onlineip=$_SERVER['HTTP_X_FORWARDED_FOR'];
                        }else{
                             $onlineip=$_SERVER['REMOTE_ADDR'];
                        }

                        $paramArr = array(
                             'showapi_appid'=> $showapi_appid,
                             'needMoreDay' => 1,
                             'ip' => $onlineip,
                             'showapi_timestamp' => $showapi_timestamp
                        );
                        $sign = createSign($paramArr);
                        $strParam = createStrParam($paramArr);
                        $strParam .= 'showapi_sign='.$sign;
                        $url = 'http://route.showapi.com/9-4?'.$strParam; 
                    }
                    
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    $weathers = array();
                    if ($result->showapi_res_code == 0){
                        $weathers[0] = $result->showapi_res_body->f1;
                        $weathers[1] = $result->showapi_res_body->f2;
                        $weathers[2] = $result->showapi_res_body->f3;
                        $weathers[3] = $result->showapi_res_body->f4;
                        $weathers[4] = $result->showapi_res_body->f5;
                        $weathers[5] = $result->showapi_res_body->f6;
                        $weathers[6] = $result->showapi_res_body->f7;
                        $city = $result->showapi_res_body->now->aqiDetail->area;
                        echo "{$city}的天气预报：<br /><br />";
                        // Wechat request
                        if ($from_wechat){
                            foreach ($weathers as $k => $v){
                                if ($k == 0){
                                    echo '今天的天气: ';
                                } else {
                                    echo $k."天后的天气:";
                                }
                                echo '白天 '.$v->day_air_temperature.'℃ '.$v->day_weather.','.$v->day_wind_power.'       夜间 '.$v->night_air_temperature.'℃ '.$v->night_weather.','.$v->night_wind_power.'.        ';
                            }
                        } else {
                            foreach ($weathers as $k => $v){
                                if ($k == 0){
                                    echo '今天的天气: <br />';
                                } else {
                                    echo $k."天后的天气:<br />";
                                }
                                echo '白天 <img src="'.$v->day_weather_pic.'" style="width: 30px" />'.$v->day_air_temperature.'℃ '.$v->day_weather.','.$v->day_wind_power.'<br />夜间 <img src="'.$v->night_weather_pic.'" style="width: 30px" /> '.$v->night_air_temperature.'℃ '.$v->night_weather.','.$v->night_wind_power.'<br /><br />';
                            }
                        }
                    }
                    break;
                case 'JOKE':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'time' => date('Y-m-d',strtotime("-5 day")) ,
                         'page' => rand(1,500) ,
                         'maxResult' => '1' ,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/341-1?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $answer = $result->showapi_res_body->contentlist[0]->text;
                    }
                    break;
                case 'HILARIOUS_PICS':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/107-33?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $list_rand = rand(0,19);
                        $imgSrc = $result->showapi_res_body->list[$list_rand]->sourceurl;
                        $title = $result->showapi_res_body->list[$list_rand]->title;
                        // From Wechat
                        if ($from_wechat){
                            echo $imgSrc.' '.$title;
                        } else {
                            echo '<img src="'.$imgSrc.'" /><br />'.$title;
                        }
                        exit();
                    }
                case 'TRANSLATE':
                    $message = substr($message,strlen("翻译"),strlen($message)-strlen("翻译"));
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'q' => $message,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/32-9?'.$strParam; 
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        if ($explains = $result->showapi_res_body->basic->explains[0]){
                            echo $explains;
                            exit();
                        }
                        if ($webtran = $result->showapi_res_body->web[0]){
                            echo $webtran->value[0];
                            exit();
                        }
                        if ($translation = $result->showapi_res_body->translation[0]){
                            echo $translation;
                            exit();
                        }
                    }
                    break;
                case 'MONTH_MOVIE':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/578-4?'.$strParam;
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $datalist = $result->showapi_res_body->datalist;
                        if ($from_wechat){
                            foreach ($datalist as $key => $value){
                                echo '#'.intval($key+1).'：《'.$value->MovieName.'》, 本月票房：￥'.$value->boxoffice.'万, 平均票价：￥'.$value->avgboxoffice.'，月度占比:'.$value->box_pro.'上映日期：'.$value->releaseTime.'          ';
                            }
                        } else {
                            foreach ($datalist as $key => $value){
                                echo '#'.intval($key+1).'：《'.$value->MovieName.'》, 本月票房：￥'.$value->boxoffice.'万, 平均票价：￥'.$value->avgboxoffice.'，月度占比:'.$value->box_pro.'上映日期：'.$value->releaseTime.'<hr />';
                            }
                        }
                    }
                    break;
                case 'GLOBAL_MOVIE':
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/578-5?'.$strParam;
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $datalist = $result->showapi_res_body->datalist;
                        if ($from_wechat){
                            foreach ($datalist as $key => $value){
                                echo '#'.intval($key+1).' 《'.$value->MovieName.'》, 周末票房：'.$value->BoxOffice.'万, 累计票房：'.$value->SumBoxOffice.'万, 在'.$value->CountryNum.'个不同国家上映了'.$value->WeekNum.'周.       ';
                            }
                        } else {
                            foreach ($datalist as $key => $value){
                                echo '#'.intval($key+1).' 《'.$value->MovieName.'》, 周末票房：'.$value->BoxOffice.'万, 累计票房：'.$value->SumBoxOffice.'万, 在'.$value->CountryNum.'个不同国家上映了'.$value->WeekNum.'周. <hr />';
                            }
                        }
                    }
                    break;
                case 'MUSIC':
                    if ($from_wechat) die('抱歉，微信无法进行搜乐，请前往<a href="http://chat.abletive.com/">网页版小A</a>');
                    $message = trim(substr($message,strlen("搜乐")));
                    $paramArr = array(
                         'showapi_appid'=> $showapi_appid,
                         'keyword' => $message,
                         'showapi_timestamp' => $showapi_timestamp
                    );
                    $sign = createSign($paramArr);
                    $strParam = createStrParam($paramArr);
                    $strParam .= 'showapi_sign='.$sign;
                    $url = 'http://route.showapi.com/213-1?'.$strParam;
                    
                    $result = file_get_contents($url);
                    $result = json_decode($result);
                    
                    if ($result->showapi_res_code == 0){
                        $musiclist = $result->showapi_res_body->pagebean->contentlist;
                        echo '关于'.trim($message).'的搜索结果：<br />';
                        foreach ($musiclist as $value){
                            echo "<img src='{$value->albumpic_big}' /><br />".$value->singername."的《{$value->songname}》, 来自专辑《{$value->albumname}》.<br /><a href='{$value->downUrl}' download='{$value->singername} - {$value->songname}'>点击下载</a><br /><audio src='{$value->m4a}' controls></audio><hr />";
                        }
                    }
                    break;
                default:
                    $answer = $specialReplies[$answerCode];
                    break;
            }
            echo $answer;
        } else {
            if (isset($result[$index]['answer'])){
                // If we got answer directly from the database
                $answer = $result[$index]['answer'];
                $newAnswer = preg_replace('/<img[^>]+>/i','',$answer);
                $newAnswer = str_replace("图灵机器人","小 A",$newAnswer);
                echo $newAnswer;
            } else {
                $paramArr = array(
                    'showapi_appid'=> $showapi_appid,
                    'info' => $message,
                    'userid' => $user_id,
                    'showapi_timestamp' => $showapi_timestamp
                );
                $sign = createSign($paramArr);
                $strParam = createStrParam($paramArr);
                $strParam .= 'showapi_sign='.$sign;
                $url = "http://route.showapi.com/60-27?".$strParam;
                
                $result = file_get_contents($url);
                $result = json_decode($result);
                
                if ($result->showapi_res_code == 0){
                    $eID = rand(0,20);
                    $expression = require('expression.config.php');
                    echo $result->showapi_res_body->text." ".$expression[$eID];
                } else {
                    echo '我短路了一下。。再试试。？';
                }
            }
        }
    } else {
        die('数据库连接出错啦');
    }
}