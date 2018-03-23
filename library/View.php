<?php

/**
 * 页面渲染中转器
 * controller 把变量注射到此控制器。此控制器引入文件。利用extract拆解变量。文件里就可以直接用控制器的变量了
 * Class View
 */
class View{
    private $vars = [];


    public function vars($name,$data){
        $this->vars[$name] = $data;
    }

    public function __set($name,$data){
        $this->$name = $data;
    }

    public function display($file){
        $file = ROOT . '/view/' . $file;
        if(!file_exists($file)){
            throw new Exception("no file");
        }
        extract($this->vars);
        require $file;
    }
}