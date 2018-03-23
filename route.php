<?php
model("Response");
//先正则编译、然后匹配

class Route
{
    private $response;
    private $path;
    private $params = [];
    private $verb;

    public function match($path)
    {
        global $config;
        $urls = $config['route'];
        $matches = [];
        $this->path = $path;
        $this->verb = $requestMethod = $_SERVER['REQUEST_METHOD'];
        //路由正则匹配
        foreach ($urls as $pattern => $val) {
            //解析匹配规则、组成正则
            $pattern = $this->parseRoute($pattern);
            if (preg_match($pattern, $path .'/', $matches)) {
                $request = $val['request'];
                if(is_array($request) && in_array($requestMethod,$request)){
                    $this->response->echoError(Response::NOTFOUND);
                }
                if(is_string($request) &&  $requestMethod != strtoupper($val['request'])){
                    $this->response->echoError(Response::NOTFOUND);
                }

                //两种写法、可以只写一个path 或者写class method
                if(isset($val['path'])){
                    list($className,$method) = explode('@',$val['path']);
                }
                $className = isset($val['class']) ? $val['class'] : $className;
                $method = isset($val['method']) ? $val['method'] : $method;
                break;
            };
        }

        //绑定参数
        unset($matches[0]);
        $this->bindParams($matches);

        //判断
        if (!isset($className) || !isset($method)) {
            $this->response->echoError(Response::NOTFOUND);
        }

        //引入文件
        $file = "controller/$className.php";
        if (!file_exists($file)) {
            $this->response->echoError(Response::NOTFOUND);
        }
        require $file;
        if (!class_exists($className)) {
            $this->response->echoError(Response::NOTFOUND);
        }
        return [$className, $method, $matches];
    }

    public function run($path)
    {
        $this->response = new Response();
        list($className, $method, $params) = $this->match($path);
        $class = new $className;
        //检测方法是否存在
        if (!method_exists($className, $method)) {
            $this->response->echoError(Response::NOTFOUND);
        }
        //路由正则值必须跟参数顺序匹配、不然会发生意想不到的错误、哈哈
        $class->$method(...$params);
    }


    //把数据转化为正则
    public function parseRoute($pattern){
        $pattern = str_replace('/','\\/',trim($pattern,'/'));

        if(preg_match_all('/\{(\w+)\??\}/',$pattern,$match)){
            $this->params = $match[1];
        };

        $pattern = preg_replace(['/\{(\w+)\}/','/\{(\w+)\?\}/'],['([^\/]+)','([^\/]+)?'],$pattern);
        return '/' . $pattern . '/';
    }

    //绑定参数
    public function bindParams($matches){
        if($matches){
            $params = [];
            //匹配的和设定的个数不一定一致、去除正则?的影响
            foreach ($matches as $key => $v){
                $params[$this->params[$key]] = $v;
            }
            $_REQUEST += $params;
            if($this->verb == 'GET'){
                $_GET += $params;
            }elseif($this->verb == 'POST'){
                $_POST += $params;
            }
        }
    }


    public function get($pattern,$subject){
    }

}
