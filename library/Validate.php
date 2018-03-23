<?php
/**
 * 用户输入规则验证类
 */

model("Request");
class Validate{
    // 验证规则
    private $rules = array(
        // 验证是否为空
        'required' => "不能为空",

        // 匹配邮箱
        'email'=>"邮箱格式错误",

        // 匹配身份证
        'idcode' => "身份证信息有误",

        // 匹配数字
        'number'=> "必须是数字",

        // 匹配http地址
        'http'=> "网址格式有误",

        // 匹配qq号
        'qq'=>'不是qq号',

        //匹配中国邮政编码
        'postcode'=>'邮编格式有误',

        //匹配ip地址
        'ip'=>'ip不对',

        //匹配电话格式
        'telephone'=>'电话格式有误',

        // 匹配手机格式
        'mobile'=>'手机号格式有误',

        //匹配26个英文字母
        'en_word'=>'不是英文字母',

        // 匹配只有中文
        'cn_word'=>'必须是中文',

        // 验证账户(字母开头，由字母数字下划线组成，4-20字节)
        'user_account'=>'格式不对',
        "string"=>'不是字符串',
        "between" =>'不在这个区间',
        "min"=>"数大小不对",
        "max"=>'数大小不对',
        "array" => '格式不对'
    );

    private $fail = 0;
    private $errMsg;

    /**
     * $data = [
     *     "email" => "required|number"
     *      "num" => "required|"
     * ]
     * @param $data
     * @param $rule
     * @param string $errMsg
     */
    public function verify($data){
        $request = make('Request');
        if(is_array($data)){

        }
        foreach ($data as $name => $rule){
            if(empty($rule)){}
            $ruleArr = explode("|",$rule);
            $value = $request->input($name,Request::STRIPSLASHES);
            if(!strstr($rule,'required') && !$value){
                continue;
            };
            foreach ($ruleArr as $v){
                $func = $v;
                if(strstr($v,":")){
                    list($func,$params) = explode(":",$v);
                }
                if(isset($this->rules[$func])){
                    if(isset($params)){
                        $params = explode(",",$params);
                        $res = $this->$func($value, ...$params);
                        unset($params);
                    }else{
                        $res = $this->$func($value);
                    }
                    if(!$res){
                        $this->fail = 1;
                        $this->errMsg = $name .'字段'. $this->rules[$func];
                        goto RUN;
                    }
                }
            }
        }
        RUN:
    }


    // 获取规则数组
    public function get_rules(){
        return $this->rules;
    }

    public function fail(){
        return $this->fail;
    }
    public function errMsg(){
        return $this->errMsg;
    }

    // 设置属性规则
    public function set_rules($arr){
        $this->rules = array_merge($this->rules, $arr);
    }

    // 验证是否为空
    public function required($str){
        if(trim($str) != "") return true;
        return false;
    }

    // 验证邮件格式
    public function email($str){
        if(preg_match("/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/", $str)) return true;
        else return false;
    }

    // 验证身份证
    public function idcode($str){
        if(preg_match("/^\d{14}(\d{1}|\d{4}|(\d{3}[xX]))$/", $str)) return true;
        else return false;
    }

    // 验证http地址
    public function http($str){
        if(preg_match("/[a-zA-Z]+:\/\/[^\s]*/", $str)) return true;
        else return false;
    }

    //匹配QQ号(QQ号从10000开始)
    public function qq($str){
        if(preg_match("/^[1-9][0-9]{4,}$/", $str)) return true;
        else return false;
    }

    //匹配中国邮政编码
    public function postcode($str){
        if(preg_match("/^[1-9]\d{5}$/", $str)) return true;
        else return false;
    }

    //匹配ip地址
    public function ip($str){
        if(preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $str)) return true;
        else return false;
    }

    // 匹配电话格式
    public function telephone($str){
        if(preg_match("/^\d{3}-\d{8}$|^\d{4}-\d{7}$/", $str)) return true;
        else return false;
    }

    // 匹配手机格式
    public function mobile($str){
        if(preg_match("/^(13[0-9]|15[0-9]|18[0-9])\d{8}$/", $str)) return true;
        else return false;
    }

    // 匹配26个英文字母
    public function en_word($str){
        if(preg_match("/^[A-Za-z]+$/", $str)) return true;
        else return false;
    }

    // 匹配只有中文
    public function cn_word($str){
        if(preg_match("/^[\x80-\xff]+$/", $str)) return true;
        else return false;
    }

    // 验证账户(字母开头，由字母数字下划线组成，4-20字节)
    public function user_account($str){
        if(preg_match("/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/", $str)) return true;
        else return false;
    }

    // 验证数字
    public function number($str){
        if(preg_match("/^[0-9]+$/", $str)) return true;
        else return false;
    }
    public function string($str){
        if(preg_match("/^.+$/", $str)) return true;
        else return false;
    }

    public function between($str,$min,$max){
        if ($min>$max){
            throw  new Exception('min > max');
        }
        if(preg_match('/^\d+\.?(\d+)?$/',$str)){
            $len = (float)$str;
        }else{
            $len = mb_strlen($str);
        }
        return $len <= $max && $len >=$min;
    }

    public function min($str,$min){
        if(preg_match('/^\d+\.?(\d+)?$/',$str)){
            $len = (float)$str;
        }else{
            $len = mb_strlen($str);
        }
        return $len >= $min;
    }

    public function max($str,$max){
        if(preg_match('/^\d+\.?(\d+)?$/',$str)){
            $len = (float)$str;
        }else{
            $len = mb_strlen($str);
        }
        return $len <= $max;
    }

    //约定以json
    //格式\里面的每个元素需要满足哪些格式、例如array:http
    public function array($data,$params){
        //内容需要是json

        $data = json_decode($data,true);

        if($data == false){
            return false;
        }
        $params = explode(',',$params);
        foreach ($data as $v){
            foreach ($params as $func){
                if($this->rules[$func]){
                    $res = $this->$func($v);
                    if(!$res){
                        $this->fail = 1;
                        $this->errMsg = $this->rules[$func];
                        goto END;
                    }
                }
            }
        }
        END:
        return true;
    }
}