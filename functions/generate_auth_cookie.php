<?php
$username = $_POST["username"];
$password = $_POST["password"];
if(!$_POST){
    echo '出错了';
    exit();
}
$ch = curl_init("http://abletive.com/api/auth/generate_auth_cookie/?username=".$username."&password=".$password);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回  
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
echo $output = curl_exec($ch);