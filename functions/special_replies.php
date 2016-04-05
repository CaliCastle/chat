<?php

date_default_timezone_set("Asia/Shanghai");

$month = strpos(date('m'),'0')===0?substr(date('m'),strlen('0')):date('m');
$date = strpos(date('d'),'0')===0?substr(date('d'),strlen('0')):date('d');
$currentDate = $month."月".$date."号";

$hour = strpos(date('h'),'0')===0?substr(date('h'),strlen('0')):date('h');
$minute = strpos(date('i'),'0')===0?substr(date('i'),strlen('0')):date('i');
$when = date('a')=='am'?($hour>=6?'上午':'凌晨'):($hour>=7?'晚上':'下午');

$currentTime = $when." ".$hour."点".$minute."分";

$birthdateString = "2015-09-18";
$birth_date = strtotime($birthdateString);

$Date_1=date('Y-m-d');
$Date_2="2015-09-18";

$Date_List_a1=explode("-",$Date_1);

$Date_List_a2=explode("-",$Date_2);

$d1=mktime(0,0,0,$Date_List_a1[1],$Date_List_a1[2],$Date_List_a1[0]);

$d2=mktime(0,0,0,$Date_List_a2[1],$Date_List_a2[2],$Date_List_a2[0]);

$past_days=round(($d1-$d2)/3600/24);

if ($past_days >= 30){
    if ($past_days >= 365){
        $past_time = "今年".round($past_days/30/12)."岁了";
    } else {
        $past_time = "已经过去".round($past_days/30)."个月了噢~";
    }
} else {
    $past_time = "距离今天已经".$past_days."天呢";
}

return array(
    'DATE' => "报告~ 今天".$currentDate,
    'TIME' => "现在是 ".$currentTime."（´∀｀*) ",
    'AGE' => "哈哈，谢谢你的关心哦，我的生日是2015年9月18号，{$past_time}~",
);