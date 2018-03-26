已经应用到项目中 
适用于单一小型的项目、自我感觉用的很舒服

1、如果是library目录下的文件、直接写文件名就好。
 如引入library/DB.php,可以这么引用:model("DB")。引入之后可以实例化:make("DB")
 如果引入相同路径下的文件、直接用php原生的require就好。model的相对路径是在项目根目录下
 
注意的是make可以缓存实例化的对象，如果整个项目只需要一个实例化、比如log，db，request等可以用make.需要动态的创建对象直接用php原生的new就好
 
 2、路由功能 路由的路径最终会转化为正则。如果路由中带参数、用{}包裹就好 如 /index/{name} 
 
 3、模版视图使用的简单的注射变量然后extract解析。
 
 
 4、检验规则类似语法：
 
 ```
 $this->validate->verify(array(
     "name" => 'required|string'
 )
 )
 if($this->validate->fail()){
     $this->response->echoError(Response::ERROR,$this->validate->errMsg());
 }
 ```
 
 5、文件目录
 
 |-conf 配置<br/>
 |-contoller 控制器<br/>
 |-library 功能性文件<br/>
 |-log 默认日志所在<br/>
 |-models 数据逻辑<br/>
 |-view 视图部分<br/>
 |-index.php 入口文件<br/>
 |-route.php 路由逻辑<br/>
 
 6、library现在包括 **数据库、日志、二维码、redis操作、校验类、视图类、微信接口、请求返回** 框架功能并不完善、但是开发小型项目足够、后期会酌情增加功能
 
 
 