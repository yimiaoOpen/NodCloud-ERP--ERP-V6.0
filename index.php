<?php
//点可云安装向导
if(is_dir('install')){
    if (!file_exists('install/install.lock')) {
        header("Location:/install"); 
        exit;
    }
}
// 定义应用目录  
define('APP_PATH', __DIR__ . '/application/');
// 加载框架引导文件  
require __DIR__ . '/thinkphp/start.php';  
