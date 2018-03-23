<?php
//集中函数、就是不用class、就是这么任性

$tips = [
    '500' => '系统错误',
    '200' => '成功',
    '404' => '不存在'
];


//引入文件 如果是引入当前目录下的用原先的函数就ok
function model($name){
    static $files;
    if(isset($files[$name])){
        return null;
    }else{
        $file = ROOT . '/' .  $name . '.php';
        if(!file_exists($file)){
            $file = ROOT . '/library/' . $name . '.php';
        }
        if(!file_exists($file)){
            throw new Exception("no model");
        }
        require($file);
        $files[$name] = 1;
    }
}

//创建对象、可以当单例模式来用（也就是只有一个实例）、如果动态创建、则用原生的就好
function make($name){
    static $instance;
    if(isset($instance[$name])) return $instance[$name];
    $instance[$name] = new $name();
    return $instance[$name];
}



function echoError($code = 500,$message = ''){
    global $tips;
    $data = [
        'code' => $code,
        'message' => $message ? $message : $tips[$code]
    ];
    exit(json_encode($data));
}

function arrEncode($data){
    if(is_string($data)){
        return urlencode($data);
    }
    foreach($data as $key => $v){
        if(is_array($v)){
            $data[$key] = arrEncode($v);
        }else{
            $data[$key] = urlencode($v);
        }
    }
    return $data;
}

function strip_tags_content($text, $tags = '', $invert = FALSE) {
    preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
    $tags = array_unique($tags[1]);

    if(is_array($tags) AND count($tags) > 0) {
        if($invert == FALSE) {
            return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
        }
        else {
            return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
        }
    }
    elseif($invert == FALSE) {
        return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
    }
    return $text;
}

function echoJson($code = 200,$data = [],$message = ''){
    global $tips;
    $data = [
        'code' => $code,
        'message' => $message ? $message : $tips[$code],
        'data' => $data
    ];
    exit (json_encode($data,JSON_UNESCAPED_UNICODE));
}

function echoJsonEncode($code = 200,$data = [],$message = ''){
    global $tips;
    $data = [
        'code' => $code,
        'message' => $message ? $message : $tips[$code],
        'data' => arrEncode($data)
    ];
    exit(urldecode(json_encode($data)));
}

//获取配置
function config($name,$delimiter = '.'){
    static $configArr;
    global $config;
    if(isset($configArr[$name])){
        return $configArr[$name];
    }else{
        if(strpos($name,$delimiter)){
            $ret = $config;
            $nameArr = explode($delimiter,$name);
            foreach ($nameArr as $key){
                $ret = $ret[$key];
            }
            $configArr[$name] = $ret;
            return $ret;

        }else{
            $configArr[$name] = $config[$name];
            return $config[$name];
        }
    }
}

function autoload($classname){
    if(class_exists($classname)){
        return;
    }
    if(is_file(ROOT . '/library/'.$classname . '.php')){
        require ROOT . '/library/'.$classname . '.php';
        return;
    }
    if(is_file(ROOT . '/models/' .$classname . '.php')){
        require ROOT . '/models/' .$classname . '.php';
        return;
    }
}

function imgUpload($file,$url,$timeout=10) {
    $postData = [];
    if (file_exists($file)) {
        $ch = curl_init($url);
        $postData['file'] = curl_file_create($file);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Disposition' => 'form-data',
            'name' => 'file',
            'filename' => $file
        ));

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if ($info['http_code'] == 200) {
            return json_decode($result, true);
        } else {
            throw new Exception('图片上传失败');
        }
    } else {
        throw new Exception('文件不存在');
    }
}
//notice warning等\会自己直接输出

function err_handler($errno, $errstr ,$errfile, $errline){
    $err = [
        'code' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'time' => date('Y-m-d H:i:s')
    ];
    $errFile = '/opt/logs/gyms_errors.log';
    file_put_contents($errFile,var_export($err,true));
}

//对php7 来说 异常 + 致命错误
function exception_handler(Throwable $t){
    $err = [
        'code' => $t->getCode(),
        'message' => $t->getMessage(),
        'file' => $t->getFile(),
        'line' => $t->getLine(),
        'time' => date('Y-m-d H:i:s')
    ];
    $errFile = '/opt/logs/gyms_errors.log';
    file_put_contents($errFile,var_export($err,true));
    echoError(500);
}

