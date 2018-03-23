<?php
model("Request");
model("Response");
model("Validate");
model('View');



class BaseController{
    protected $request;
    protected $response;
    protected $validate;
    protected $openid;


    public function __construct()
    {
        $this->request = new Request();
        $this->validate = new Validate();
        $this->response = new Response();
        $this->view = new View();
    }

    public function input($name){
        return $this->request->input($name);
    }

    public function model($className){
        static $class = [];
        if(isset($class[$className])){
            return $class[$className];
        }
        if(!class_exists($className)){
            require ROOT . '/models/' . $className . '.php';
        }
        $classx = new $className();
        $class[$className] = $classx;
        return $classx;
    }

    protected function vars($name,$data){
        $this->view->vars($name,$data);
    }
    protected function display($file){
        $this->view->display($file);
    }

    //校验机制
    protected function Authentication($js_code){
        if(!$js_code){
            $this->response->echoError(Response::NOACCESS);
        }
        $data = $this->getOpenid($js_code);
        if(isset($data['openid'])){
            $this->openid = $data['openid'];
        }else{
            $this->response->echoError(Response::ERROR,$this->validate->errMsg());
        }
    }








}