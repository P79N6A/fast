<?php
/**
 * 目录、文件函数的补充 
 */
/**
 * 传输文件，调用此函数会退出应用，主要用于保密文件显示或下载
 * @param string $filename 文件名称
 * @param boolean $download 是否用于下载，默认true
 */
function pass_file($filename,$download=true){
	$t=get_file_mimetype($filename);
	if($t===false) $t="text/plain";
	
	$fp = fopen($filename, 'rb');
	header('Content-type: ' . $t);
	if($download)
		header('Content-Disposition: attachment; filename="'.basename($filename));
	else header('Content-Length: ' . filesize($filename));
	fpassthru($fp);
	fclose($fp);
	exit;
}
/**
 * 根据文件magic字节得到mime类型，如php<5.3 请加载fileinfo PHP扩展模块
 * @param string $filename 文件名称
 * @return string|boolean 文件的mime类型，如果失败返回false。 
 */
function get_file_mimetype($filename){
	if(strncasecmp(PHP_OS,  'WIN', 3) === 0)
		$finfo = finfo_open(FILEINFO_MIME,ROOT_PATH . DIRECTORY_SEPARATOR . 'res' . DIRECTORY_SEPARATOR .'magic'); 
	else $finfo = finfo_open(FILEINFO_MIME); 
	
	if(! $finfo) return false;
	$t= finfo_file($finfo, $filename) ;
	finfo_close($finfo);	
	return $t;
}



/**
 * 删除目录，如果包含其它目录，将循环删除。由于使用php嵌套函数，故目录深度不能超过php嵌套限制
 * @param string $path 目录路径
 */
function rmdir_r($path) {
	if(! file_exists($path)) return ;
	if(! is_dir($path)) {
		@unlink($path);
		return ;
	} 
	$handle = @opendir($path);
	while(($file = @readdir($handle)) !== false){
		if($file != '.' && $file != '..'){
			$dir = $path . '/' . $file;
			if(is_dir($dir)) rmdir_r($dir);
			else @unlink($dir);
		}
	}
	closedir($handle);
	rmdir($path) ;
}
/**
 * 清除webapp中cahce目录
 */
function clear_cache_folder(){
	$context=$GLOBALS['context'];
	$path=ROOT_PATH . $context->app_name . DIRECTORY_SEPARATOR .'cache';
	rmdir_r($path);
}
/**
 * 拷贝目录，如果包含其它目录，将循环拷贝。由于使用php嵌套函数，故目录深度不能超过php嵌套限制
 * @param string $src  源目录
 * @param string $dest 目标目录
 */
function cp_r($src, $dest) {
	if (is_dir ( $src ) == false)	return;
	if (is_dir ( $dest ) == false)	mkdir ( $dest, 0700 );
	$handle = @opendir ( $src );
	while ( false !== ($file = @readdir ( $handle )) ) {
		if ($file != '.' && $file != '..') {
			if (is_dir ( $src . DIRECTORY_SEPARATOR . $file ))	cp_r ( $src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file );
			else	copy ( $src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file );
		}
	}
	@closedir ( $handle );
}