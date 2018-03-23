<?php
require "BaseController.php";
class ShowController extends BaseController {

    /**
     * test
     */
    public function index(){
        $text = "sqw";
        $this->vars('test',$text);
        $this->display("index.html");

    }

    public function uploadShow(){
        $this->display("imgShow.html");
    }

}