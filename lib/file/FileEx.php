<?php
/**
 * 目录、文件函数的扩展
 */
class FileEx{
	/**
	 * 根据文件magic字节得到mime类型，如php<5.3 或者创建失败，请加载fileinfo PHP扩展模块
	 * @param string $filename 文件名称
	 * @return string|boolean 文件的mime类型，如果失败返回false。 
	 */
	function getMime($filename){
		$finfo = finfo_open(FILEINFO_MIME); 
		if(! $finfo) return false;
		$t= finfo_file($finfo, $filename) ;
		if(! $t) return $t;
		finfo_close($finfo);
		list($t,$c)=explode (';',$t,2);	
		return trim($t);
	}
		
	/**
	 * 传输文件，调用此函数会退出应用，主要用于保密文件显示或下载
	 * @param string $filename 文件名称
	 * @param boolean $attachFile 是否作为附件下载，默认false，直接输出
	 */
	function passthru($filename,$attachFile=false){
		$t=$this->getMime($filename);
		if($t===false) $t="application/octet-stream";
		
		$fp = fopen($filename, 'rb');
		header('Content-type: ' . $t);
		if($attachFile)	header('Content-Disposition: attachment; filename="'.basename($filename));
		else header('Content-Length: ' . filesize($filename));
		fpassthru($fp);
		fclose($fp);
		exit;
	}
	
	/**
	 * 清空目录，删除目录，循环删除包含子目录。由于使用php嵌套函数，故目录深度不能超过php嵌套限制
	 * @param string $path 目录路径
	 */
	function rmdir($path) {
		if(! file_exists($path)) return ;
		if(! is_dir($path)) {
			@unlink($path);
			return ;
		} 
		$handle = @opendir($path);
		while(($file = @readdir($handle)) !== false){
			if($file != '.' && $file != '..'){
				$dir = $path . '/' . $file;
				if(is_dir($dir)) $this->rmdir($dir);
				else @unlink($dir);
			}
		}
		closedir($handle);
		rmdir($path) ;
	}

	/**
	 * 拷贝整个目录，拷贝目录，循环拷贝子目录。由于使用php嵌套函数，故目录深度不能超过php嵌套限制
	 * @param string $src  源目录
	 * @param string $dest 目标目录
	 */
	function cp($src, $dest) {
		if (is_dir ( $src ) == false)	return;
		if (is_dir ( $dest ) == false)	mkdir ( $dest, 0700 );
		$handle = @opendir ( $src );
		while ( false !== ($file = @readdir ( $handle )) ) {
			if ($file != '.' && $file != '..') {
				if (is_dir ( $src . DIRECTORY_SEPARATOR . $file ))
					$this->cp ( $src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file );
				else	copy ( $src . DIRECTORY_SEPARATOR . $file, $dest . DIRECTORY_SEPARATOR . $file );
			}
		}
		@closedir ( $handle );
	}	
	/**
	 * 清空file cahce目录
	 */
	function clearFileCache(){
		global $context;
		$path=ROOT_PATH . $context->app_name . DIRECTORY_SEPARATOR .'cache';
		$this->rmdir($path);
	}		
}
