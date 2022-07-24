<?php
header('Content-Type: text/html; charset=utf-8');
@set_time_limit(0);
function str_enhtml($t0) {
    return addslashes(htmlspecialchars(trim($t0)));
}
function str_nohtml($t0) {
    return stripslashes(htmlspecialchars_decode(trim($t0)));
}
function write_file($path,$str,$type = 'w+') {
    $of = fopen($path,$type);
    if ($of) {
        fwrite($of,iconv('utf-8','utf-8',$str));
        fclose($of);
        return true;
    } else {
        return false;
    }
}
function read_file($file) {
    if (file_exists($file) and is_readable($file)) {
        if (function_exists('file_get_contents')) {
            $content = file_get_contents($file);
        } else {
            $fp = fopen($file, 'r');
            while (!feof($fp)) {
                $content = fgets($fp, 1024);
            }
            fclose($fp);
        }
        return $content;
    }
}

function alert($str,$url = '') {
    $str = $str ? 'alert("'.$str.'");' : '';
    $url = $url ? 'location.href="'.$url.'";' : 'history.go(-1);';
    die('<script>'.$str.$url.'</script>');
}

function install() {
    $dbhost = isset($_POST['dbhost']) ? str_enhtml($_POST['dbhost']) :'';
    $dbname = isset($_POST['dbname']) ? str_enhtml($_POST['dbname']) :'';
    $dbuser = isset($_POST['dbuser']) ? str_enhtml($_POST['dbuser']) :'';
    $dbpwd = isset($_POST['dbpwd'])  ? str_enhtml($_POST['dbpwd'])  :'';
    (!$dbhost || !$dbname || !$dbuser) && alert('资料填写不完全！');
    $conn = mysqli_connect($dbhost,$dbuser,$dbpwd);
    !$conn && alert('连接数据库失败,请核实信息！');
    mysqli_query($conn,"SET NAMES 'utf8', character_set_client=binary, sql_mode='', interactive_timeout=3600;");
    $list = mysqli_query($conn,'show Databases');
    while ($row = mysqli_fetch_array($list)) {
        $dbname_arr[] = $row['Database'];
    }
    if (!in_array($dbname,$dbname_arr)) {
        !mysqli_query($conn,'CREATE DATABASE '.$dbname.'') && alert('创建数据库失败！');
    }
    mysqli_select_db($conn,$dbname);
    $sql = read_file('nod.sql');
    $sqlarr = explode(";\n",$sql);
    foreach ($sqlarr as $sql) {
        mysqli_query($conn,$sql);
    }
    //创建数据库连接文件
    $database = read_file('database.ini');
    $database = str_replace('{db_host}',$dbhost,$database);
    $database = str_replace('{db_user}',$dbuser,$database);
    $database = str_replace('{db_pwd}',$dbpwd,$database);
    $database = str_replace('{db_name}',$dbname,$database);
    if (!write_file('../application/database.php',$database)) {
        alert('数据库连接文件创建失败!');
    }
    //创建API秘钥配置文件
    $config = read_file('config.ini');
    $config = str_replace('{api_key}','nod_'.substr(md5(time()),5,13),$config);
    if (!write_file('../application/index/config.php',$config)) {
        alert('数据库连接文件创建失败!');
    }
    write_file('install.lock','点可云进销存程序已正确安装，重新安装请删除本文件');
    alert('','set4.php');
}
?>