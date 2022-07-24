<?php
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
include_once('lib/base.php');
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>点可云进销存管理系统安装</title>
		<link rel="stylesheet" type="text/css" href="css/metinfo.css" />
		<link rel="stylesheet" type="text/css" href="css/reset.css" />
	</head>
	<body>
		<div class="top">
			<div class="topcont">
				<div class="logo">
					<a href="https://www.nodcloud.com/" target="_blank"><img src="images/logo.png" alt="点可云进销存管理系统" /></a>
				</div>
				<div class="headright">点可云进销存管理系统 <font>V6.0.1</font></div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="body">
			<div class="bodycont">
				<div class="bodyleft">
					<ul>
						<li id="license" class="stepnow"><span>1</span> 阅读授权许可协议</li>
						<li id="inspect" ><span>2</span> 系统环境检测</li>
						<li id="databasesetup"><span>3</span> 数据库用户设置</li>
						<li id="finished"><span>4</span> 安装完成</li>
					</ul>
					<div class="clear"></div>
				</div>
				<div class="bodyright">
					<iframe id="index" src="set1.php" scrolling="no" frameborder="0"></iframe>
				</div>
				<div class="foot">点可云进销存软件受《中华人民共和国著作权法》保护，著作权号（2019SR0135099）,版权所有，盗版必究！</div>
			</div>
		</div>
	</body>
</html>