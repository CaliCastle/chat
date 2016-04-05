<?php 

function createSign ($paramArr) {
     global $showapi_sign;
     $sign = "";
     ksort($paramArr);
     foreach ($paramArr as $key => $val) {
         if ($key != '' && $val != '') {
             $sign .= $key.$val;
         }
     }
     $sign.=$showapi_sign;
     $sign = strtoupper(md5($sign));
     return $sign;
}
function createStrParam ($paramArr) {
     $strParam = '';
     foreach ($paramArr as $key => $val) {
     if ($key != '' && $val != '') {
             $strParam .= $key.'='.urlencode($val).'&';
         }
     }
     return $strParam;
}
function newstripos($str, $find, $count, $offset=0){
	$pos = stripos($str, $find, $offset);
	$count--;
	if ($count > 0 && $pos !== FALSE)
	{
		$pos = newstripos($str, $find ,$count, $pos+1);
	}
	return $pos;
}