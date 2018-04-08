<?php

/**
 * 请求处理
 * Class Request
 */

class Request{
    private $post;
    private $get;
    const ADDSLASHES = 1;
    const STRIPSLASHES = 0;

    private static $data;

    public function __construct()
    {
        $this->merge();
    }

    public function input($name,$model=self::ADDSLASHES){
        $params = self::$data;
        if($model === self::ADDSLASHES){
            $params = $this->addslashesDeep(self::$data);
        }

        if (is_string($name)){
            if(isset($params[$name])){
                return $params[$name];
            }else{
                return NULL;
            }
        }
        $data = [];
        if(is_array($name)){
            foreach ($name as $v){
                if(isset($params[$v])){
                    $data[$v] = $params[$v];
                }else{
                    $data[$v] = NULL;
                }
            }
        }
        return $data;
    }


    public function merge(){
        self::$data = array_merge($_POST,$_GET,$_COOKIE,$_REQUEST);
    }

    public function requestData(){
        return self::$data;
    }

    public function addslashesDeep($data){
        if(is_string($data)) return addslashes($data);
        if(is_array($data)) {
            $data = array_map(array($this,'addslashesDeep'),$data);
        }
        return $data;
    }

}