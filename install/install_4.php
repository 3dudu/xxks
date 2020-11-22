<?php
defined('ROOT_PATH') ? "" : die("can't run alone");
//生成lock文件
$is_success = file_put_contents(ROOT_PATH.'./install/install.lock','ok');
if(!$is_success)
{
	die('create install.lock file fail');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>安装向导(四)</title>
<link rel="icon" href="./../favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="./../favicon.ico" type="image/x-icon" />
<link rel="stylesheet" href="css/install.css" />
</head>
<body>
<div class="container">
	<div class="ins_box clearfix">
		<div class="cont clearfix">
			<ul class="step">
				<li id="step_1"></li>
				<li id="step_2"></li>
				<li id="step_3"></li>
				<li id="step_4" class="current"></li>
			</ul>
			<div class="log_box">
				<h2><img src="images/guide_4.gif" width="83" height="17" /></h2>

				<div class="gray_box">
					<div class="box clearfix">
						<p class="red"><img src="images/error.gif" width="16" height="15" />警告：</p>
						<p class="red intent">为了增强安全性，您必须删除'install'文件夹和自述文件。</p>
						<a class="go_index f_l" href="../index.html"></a>
						<a class="go_admin f_r" href="../index.php?controller=admin&action=index"></a>
					</div>
				</div>
			</div>
		</div>
		<span class="l"></span><span class="r"></span><span class="b_l"></span><span class="b_r"></span>
	</div>
</div>
</body>
</html>
