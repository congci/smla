<?php

/**
 * 输出格式化
**/
model("Log");

class Response{
    const SUCCESS = 200;
    const ERROR = 500;
    const NOTFOUND = 404;
    const NOACCESS = 403;
    const POSTERROR = 505;
    const AREAERROR = 504;
    const FIELDERR=100;

    static $tips = [
        self::SUCCESS => '成功',
        self::ERROR   => '系统错误',
        self::NOTFOUND => '文件不存在或者路由错误',
        self::NOACCESS => '访问禁止或权限不够',
        self::POSTERROR => '上传格式有误',
        self::AREAERROR=>'地区匹配错误,请联系开发者',
        self::FIELDERR => '字段已存在'
    ];

    public function echoError($code,$message=''){
        $data = [
            'code' => $code,
            'message' => $message ? $message : self::$tips[$code]
        ];
        $errInfo = debug_backtrace()[0];
        make("log")->log(json_encode($errInfo));
        exit(json_encode($data));
    }

    public function echoJson($code = 200,$data = [],$message = ''){
        $data = [
            'code' => $code,
            'message' => $message ? $message : self::$tips[$code],
            'data' => $data
        ];
        exit(json_encode($data));
    }

    public function arrEncode($data){
        if(is_string($data)){
            return urlencode($data);
        }
        foreach($data as $key => $v){
            if(is_array($v)){
                $data[$key] =$this->arrEncode($v);
            }else{
                $data[$key] = urlencode($v);
            }
        }
        return $data;
    }
}
