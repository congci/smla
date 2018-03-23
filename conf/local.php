<?php

//数据库"dblib:host=$hostname:$port;charset=$charset;dbname=$dbname",$username,$passwd
$config['db']['master']     = array('dsn' => 'mysql:host=10.2.61.100;dbname=community_gyms', 'user' => 'root', 'pwd' => '');
$config['db']['slaves']     = array('dsn' => 'mysql:host=10.2.61.100;dbname=community_gyms', 'user' => 'root', 'pwd' => '');
$config['db']['persistent'] = false; // 是否启用 PDO 长连接
$config['db']['timeout']    = 1; //数据库操作超时时间，单位（秒）
$config['db']['character']  = 'utf8mb4'; // 连接字符集



//路由
$config['route'] = array(
    '/api/check/{name}/{value}/{js_code}' => array(
        'request' => 'GET',
        'class' => 'GymsController',
        'method' => 'check'
    )
);


