<?php

chdir(__DIR__);
define('ROOT',__DIR__);
//自动加载
//时间配置
date_default_timezone_set("Asia/Shanghai");
//本地配置
require "conf/local.php";
//加载公共函数
require "library/Func.php";

set_error_handler('err_handler',E_ALL);
set_exception_handler('exception_handler');
spl_autoload_register("autoload");
//权限 + 过滤
require "access.php";

//加载路由解析
require "route.php";

$path = $_SERVER['REQUEST_URI'];

(new Route())->run($path);





























