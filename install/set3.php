<?php
	error_reporting(0);
	if(file_exists('install.lock')){
		header('Content-Type: text/html; charset=utf-8');
	    die('对不起，该程序已经安装过了。如您要重新安装，请手动删除install/install.lock文件。');
	}else{
		include_once('lib/base.php');
		if(isset($_GET['act'])=='install'){
			install();
		}
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>点可云进销存管理系统安装</title>
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
			<div class="contenttext databasesetup round">
				<p style="font-size:14px;">请检查您的数据库设置情况，请在相应栏目仔细输入配置内容。</p>
				<form method="post" action="set3.php?act=install">
					<fieldset style="padding-bottom:0px;">
						<legend><strong>数据库信息</strong></legend>
						<div class="section1">
							<p>数据库主机</p>
							<input type="text" class="text" name="dbhost" value="127.0.0.1" size="18" />
							<span>数据库主机地址，一般不需要更改</span>
						</div>
						<div class="section1">
							<p>数据库名 </p>
							<input type="text" name="dbname" class="text" size="18" />
							<span>例如'nod'或'is_nod',请确保用字母开头</span>
						</div>
						<div class="section1">
							<p>数据库用户名</p>
							<input type="text" class="text" name="dbuser" value="root" size="18" />
						</div>
						<div class="section1" style="margin-bottom:0px;">
							<p>数据库密码</p>
							<input type="password" class="text" name="dbpwd" />
						</div>
						<div class="section1" style="margin-bottom:0px;">

						</div>
						<div style="padding:8px 0px 8px 80px;">
							<span style="color:#FF0000">系统默认登陆账号:admin 密码:admin888 登陆系统后您可以自行改密码 </span>
						</div>
						<div class="section1">
							<p>登陆用户</p>
							<input type="text" class="text" readonly="" value="admin" size="18" />
						</div>
						<div class="section1" style="margin-bottom:0px;">
							<p>登陆密码</p>
							<input type="text" class="text" readonly="" value="admin888" />
						</div>
					</fieldset>
					<div style="text-align:center; margin-top:5px;">
						<input type="submit" name="submit" class="submit" tabindex="15" value="保存数据库设置并继续" />
					</div>
				</form>
			</div>
		</div>
	</body>
</html>