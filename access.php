<?php
//权限限制
//1、referer
$referer = $_SERVER["HTTP_REFERER"] ?? '';
if(strpos($referer,'x')){
   // echoError(403);
}


