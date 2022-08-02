<?php
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
include_once('lib/base.php');
$array1 = array('../application/','../runtime/','../install/','../skin/upload/');
$array2 = array('mysqli_connect','fsockopen','gethostbyname','file_get_contents','xml_parser_create','mb_strlen','curl_exec');
if(file_exists('install.lock')){
    die('对不起，该程序已经安装过了。如您要重新安装，请手动删除install/install.lock文件。');
}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title点可云进销存管理系统安装</title>
		<link rel="stylesheet" type="text/css" href="css/metinfo.css" />
		<link rel="stylesheet" type="text/css" href="css/reset.css" />
		<script language="javascript" src="js/jQuery1.7.2.js"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				var l = $("#jsheit").height() + 10;
				$(window.parent.document).find("#index").height(l);
			});
		</script>
	</head>
	<body>
		<div id="jsheit">
			<form method="post" action="set3.php">
				<div class="contenttext round">
					<p style="font-size:14px;">请检查您的服务器是否支持安装点可云进销存管理系统，请在继续安装前消除错误或警告信息。</p>
					<fieldset>
						<legend><strong>环境检测结果</strong></legend>
						<div class="section">
							<ul class="inspect-list aownbwef">
								<li class='OK'>
									<span>PHP版本</span>
									<?php echo $_SERVER['SERVER_SOFTWARE'];?> 
								</li>
							</ul>
						</div>
					</fieldset>
					<fieldset>
						<legend><strong>函数与目录权限</strong></legend>
						<div class='section'>
							&nbsp;&nbsp;&nbsp;&nbsp;要能正常使用本进销存系统， 需要将几个文件/目录设置为 "可写"。下面是需要设置为"可写" 的目录清单， 以及必须的 CHMOD 设置。
							<br/>
							&nbsp;&nbsp;&nbsp;&nbsp;某些主机不允许您设置 CHMOD 777，要用666。先试最高的值，不行的话，再逐步降低该值。
							<ul class="inspect-list auwgph">
								<?php foreach($array1 as $w){?>
								<li class='<?php echo is_writable($w) ? ' OK ' : 'WARN '?>'>
									<span><?php echo $w;?></span>
								</li>
								<?php }?>
								<?php foreach($array2 as $w){?>
								<li class='<?php echo function_exists($w) ? ' OK ' : 'WARN '?>'>
									<span><?php echo $w;?></span>
								</li>
								<?php }?>
								<li class='<?php  echo extension_loaded("pdo_mysql") ? ' OK ' : 'WARN '?>'>
									<span>pdo_mysql</span>
								</li>
							</ul>
							<div class="clear"></div>
						</div>
					</fieldset>
				</div>
				<div style=" text-align:center; ">
					<input type="button" name="refresh" class="submit" tabindex="11" value="重新检查" onClick="history.go(0)" />
					<input type="submit" name="submit" class="submit" style="margin-right:5px;" value="下一步" />
				</div>
			</form>
		</div>
	</body>

</html>