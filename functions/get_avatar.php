<?php

$user_id = intval($_GET["user_id"]);

$ch = curl_init("http://abletive.com/api/user/get_avatar/?user_id={$user_id}");  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回  
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
echo $output = curl_exec($ch);