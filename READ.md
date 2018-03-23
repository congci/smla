适用于单一小型的项目
1、如果是library目录下的文件、直接写文件名就好。
 如引入library/DB.php,可以这么引用:model("DB")。引入之后可以实例化:make("DB")
 如果引入相同路径下的文件、直接用php原生的require就好。model的相对路径是在项目根目录下
 
注意的是make可以缓存实例化的对象，如果整个项目只需要一个实例化、比如log，db，request等可以用make.需要动态的创建对象直接用php原生的new就好
 
 2、路由功能
 
 3、