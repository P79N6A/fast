<?php

/**
 * Requirement Checker script
 *
 * This script will check if your system meets the requirements for running
 */
/**
 *
 * @var array List of requirements (name, required or not, result, used by, memo)
 */
$requirements = array ();
/*
 * $requirements[] = array( 'PHP版本', ---项目名称 true, --- 是否系统必须 version_compare(PHP_VERSION,"5.1.0",">="), -- 检查结果 '所有模块', ---使用者 'PHP 5.1.0或更高版本是必须的。', --- 说明 );
 */

// PHP版本
$requirements [] = array (
		'PHP版本',
		true,
		version_compare ( PHP_VERSION, "5.3.7", ">=" ),
		'FastApp基础框架服务',
		'PHP 5.3.7或更高版本是必须的，推荐版本为5.3.7' 
);

// $_SERVER variable

$ret = checkServerVar ();
$requirements [] = array (
		'$_SERVER变量',
		true,
		$ret ['status'],
		'FastApp基础框架服务',
		$ret ['message'] 
);

$requirements [] = array (
		'PDO扩展模块',
		true,
		extension_loaded ( 'pdo' ),
		'FastApp基础框架服务',
		'所有和数据库相关的类' 
);

$requirements [] = array (
		'PDO MySQL扩展模块',
		true,
		extension_loaded ( 'pdo_mysql' ),
		'FastApp基础框架服务',
		'如果使用MySQL数据库，这是必须的。' 
);

$requirements [] = array (
		'PDO ORACLE扩展模块',
		false,
		extension_loaded ( 'pdo_oci8' ),
		'FastApp基础框架服务',
		'如果使用ORACLE数据库，这是必须的。' 
);

$requirements [] = array (
		'Memcache扩展模块',
		false,
		extension_loaded ( "memcache" ) || extension_loaded ( "memcached" ),
		'FastApp基础框架服务',
		'如果使用Memcache缓存服务，这是必须的。' 
);

$requirements [] = array (
		'CURL扩展模块',
		true,
		extension_loaded ( "curl" ),
		'网店API接口',
		'如果使用CURL相关函数，这是必须的。' 
);

$requirements [] = array (
		'FTP扩展模块',
		false,
		extension_loaded ( "ftp" ),
		'部分接口模块',
		'如果使用FTP相关功能，这是必须的。' 
);

$requirements [] = array (
		'SOAP扩展模块',
		false,
		extension_loaded ( "soap" ),
		'部分接口模块',
		'如果使用SOAP相关功能，这是必须的。' 
);

$requirements [] = array (
		'APC扩展模块',
		false,
		extension_loaded ( "apc" ),
		'FastApp基础框架服务',
		'如果使用APC服务，这是必须的。' 
);

$requirements [] = array (
		'Mcrypt扩展模块',
		false,
		extension_loaded ( "mcrypt" ),
		'FastApp基础框架服务',
		'如果使用Mcrypt函数，这是必须的。' 
);

// cup 硬盘大小 目录权限， apache运行帐号 计划任务配置检查 魔法引用
function checkServerVar() {
	$vars = array (
			'HTTP_HOST',
			'SERVER_NAME',
			'SERVER_PORT',
			'SCRIPT_NAME',
			'SCRIPT_FILENAME',
			'PHP_SELF',
			'HTTP_ACCEPT',
			'HTTP_USER_AGENT' 
	);
	$missing = array ();
	foreach ( $vars as $var ) {
		if (! isset ( $_SERVER [$var] ))
			$missing [] = $var;
	}
	$message = '';
	if (! empty ( $missing ))
		$message = '$_SERVER缺少' . implode ( ', ', $missing ) . '。';
	
	if (realpath ( $_SERVER ["SCRIPT_FILENAME"] ) !== realpath ( __FILE__ ))
		$message = '$_SERVER["SCRIPT_FILENAME"]必须与入口文件路径一致。';
	
	if (! isset ( $_SERVER ["REQUEST_URI"] ) && isset ( $_SERVER ["QUERY_STRING"] ))
		$message = '$_SERVER["REQUEST_URI"]或$_SERVER["QUERY_STRING"]必须存在。';
	
	if (! isset ( $_SERVER ["PATH_INFO"] ) && strpos ( $_SERVER ["PHP_SELF"], $_SERVER ["SCRIPT_NAME"] ) !== 0)
		$message = '无法确定URL path info。请检查$_SERVER["PATH_INFO"]（或$_SERVER["PHP_SELF"]和$_SERVER["SCRIPT_NAME"]）的值是否正确。';
	
	return array (
			'status' => $message == '' ? true : false,
			'message' => $message 
	);
}
function getServerInfo() {
	$info [] = isset ( $_SERVER ['SERVER_SOFTWARE'] ) ? $_SERVER ['SERVER_SOFTWARE'] : '';
	$info [] = '<a href="http://www.baison.com.cn/">系统运行电子商务平台软件</a>/';
	$info [] = @strftime ( '%Y-%m-%d %H:%M', time () );
	
	return implode ( ' ', $info );
}

$result = 1; // 1: all pass, 0: fail, -1: pass with warnings

foreach ( $requirements as $i => $requirement ) {
	if ($requirement [1] && ! $requirement [2])
		$result = 0;
	else if ($result > 0 && ! $requirement [1] && ! $requirement [2])
		$result = - 1;
	if ($requirement [4] === '')
		$requirements [$i] [4] = '&nbsp;';
}

$serverInfo = getServerInfo ();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="en" />
<title>软件配置需求检查</title>
<style type="text/css">
body
{
	background: white;
	font-family:'Lucida Grande',Verdana,Geneva,Lucida,Helvetica,Arial,sans-serif;
	font-size:10pt;
	font-weight:normal;
}
b{
	font-size:11pt;

}
#page
{
	width: 800px;
	
}

#header
{
}

#content
{
}

#footer
{
	color: gray;
	font-size:8pt;
	border-top:1px solid #aaa;
	margin-top:10px;
}

h1
{
	color:black;
	font-size:1.6em;
	font-weight:bold;
	margin:0.5em 0pt;
}

h2
{
	color:black;
	font-size:1.25em;
	font-weight:bold;
	margin:0.3em 0pt;
}

h3
{
	color:black;
	font-size:1.1em;
	font-weight:bold;
	margin:0.2em 0pt;
}

table.result
{
	background:#E6ECFF none repeat scroll 0% 0%;
	border-collapse:collapse;
	width:100%;
}

table.result th
{
	background:#CCD9FF none repeat scroll 0% 0%;
	text-align:left;
}

table.result th, table.result td
{
	border:1px solid #BFCFFF;
	padding:0.2em;
}

td.passed
{
	background-color: #60BF60;
	border: 1px solid silver;
	padding: 2px;
}

td.warning
{
	background-color: #FFFFBF;
	border: 1px solid silver;
	padding: 2px;
}

td.failed
{
	background-color: #FF8080;
	border: 1px solid silver;
	padding: 2px;
}
</style>
</head>

<body>
	<div id="page">

		<div id="header">
			<h1>软件配置需求检查</h1>
		</div>
		<!-- header-->

		<div id="content">
			<h2>检查内容</h2>
			<p>
				本网页用于确认您的服务器配置是否能满足运行 <a href="http://www.baison.com.cn/">系统运行电子商务平台软件</a>
				系统的要求。它将检查服务器所运行的PHP版本，查看是否安装了合适的PHP扩展模块，确认php.ini文件是否正确设置，以及其他运行软件必要的检查。
			</p>

			<h2>检查结果</h2>
			<p>
			<?php if($result>0): ?>
				恭喜！您的服务器配置完全符合系统运行的要求。
				<?php elseif($result<0): ?>
				您的服务器配置符合系统运行的最低要求。如果您需要使用特定的功能，请关注如下警告。
				<?php else: ?>
				您的服务器配置未能满足系统运行的要求。
				<?php endif; ?>
			</p>

			<h2>具体结果</h2>

			<table class="result">
				<tr>
					<th>项目名称</th>
					<th>结果</th>
					<th>使用者</th>
					<th>备注</th>
				</tr>
				<?php foreach($requirements as $requirement): ?>
				<tr>
					<td><?php echo $requirement[0]; ?>
					</td>
					<td
						class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
						<?php echo $requirement[2] ? '通过' : '未通过'; ?>
					</td>
					<td><?php echo $requirement[3]; ?>
					</td>
					<td><?php echo $requirement[4]; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>

			<table>
				<tr>
					<td class="passed">&nbsp;</td>
					<td>通过</td>
					<td class="failed">&nbsp;</td>
					<td>未通过</td>
					<td class="warning">&nbsp;</td>
					<td>警告</td>
				</tr>
			</table>

		</div>
		<!-- content -->

		<div id="footer">
		<?php echo $serverInfo; ?>
		</div>
		<!-- footer -->

	</div>
	<!-- page -->
</body>
</html>

