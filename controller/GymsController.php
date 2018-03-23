<?php

require "BaseController.php";
model('WxApi');

class GymsController extends  BaseController{


    protected $noAuth = [];
    public function __construct()
    {
        parent::__construct();
    }

    public function check($name,$value,$token){
        $this->Authentication($token);
        $GymsInfoModel = $this->model('GymsInfoModel');
        $res = $GymsInfoModel->checkField($name,urldecode($value));
        if ($res){
            $this->response->echoError(Response::FIELDERR);
        }
        $this->response->echoJson(Response::SUCCESS);

    }







}