<?php
/**
 * 此文件包括Output类，Download类
 */
require_lib('Output/output_util',false);
/**
 * 将数据输出到文件，包括csv,excel
 * @author zengjf
 *
 */
class Output{
	private $data;
	private $caption;
	private $title;
	/**
	 * 对象构建
	 * @param array $data 数据数组
	 * @param array $caption    行标题  false：不带标题，true：使用第一行key作为标题，array：使用caption数组数据作为标题。
	 * @param string $title     页面标题 。
	 */
	function __construct(array $data,$caption=true,$title=NULL){
		$this->data=$data;
		$this->caption=$caption;
		$this->title=$title;
	}
	/**
	 * 下载数据数组到csv文件
	 * @param array $data 数据数组
	 * @param array $caption    标题  false：不带标题，true：使用第一行key作为标题，array：使用caption数组数据作为标题。
	 * @param string $download_file 文件名称
	 * @param string $delimiter csv分隔符	默认值：,
	 * @param string $enclosure csv包含符	默认值："
	 */
	function csv($download_file='csvfile',$delimiter = ',',$enclosure = '"') {
		global $context;
		$charset='gbk';
		header('Content-type: text/csv;charset='.$charset);
		header('Content-Disposition: attachment; filename="'.$download_file.'.csv"');
		header("Pragma: no-cache");
		header("Expires: 0");
		data_to_csv($this->data,$this->caption,$charset,'php://Output',$delimiter,$enclosure);
		exit;
	
	}
	/**
	 * 下载数据数组到xls文件
	 * @param array $data 数据数组
	 * @param array $caption    标题  false：不带标题，true：使用第一行key作为标题，array：使用caption数组数据作为标题。
	 * @param string $title     sheet页面标题 。
	 * @param string $download_file 文件名称  如果为空，使用$title。
	 */
	function xlsx($download_file=NULL) {
		global $context;
		$charset='gbk';
		if($this->title && ! $download_file) $download_file=$this->title;
		if(! $download_file) $download_file='ExcelFile';
		$app_charset=$context->get_app_conf('charset');	
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.iconv($app_charset,$charset,$download_file).'.xlsx"');
		header("Pragma: no-cache");
		header("Expires: 0");
		data_to_xls($this->data,$this->caption,$this->title,'php://Output');
		exit;
	
	}
	/**
	 * 下载html下载文件
	 * @param string $tpl：模板文件路径  相对路径即views ，theme目录下文件路径和名称，名称不包括.tpl.php,
	 * @param string page：模板页面文件路径  如果为NULL，不使用模板页面文件，相对路径即views ，theme目录下文件路径和名称，名称不包括.tpl.php。
	 * @param string $download_file 文件名称  如果为空，使用$title
	 */
	function html($tpl,$page,$download_file=NULL) {
		global $context;
		if($this->title && ! $download_file) $download_file=$this->title;
		if(! $download_file) $download_file='HtmlFile';
				
		$charset=$context->get_app_conf('charset');
		header('Content-type: text/html;charset='.$charset);
		header('Content-Disposition: attachment; filename="'.$download_file.'.html"');
		header("Pragma: no-cache");
		header("Expires: 0");
		data_to_html($this->data,$tpl,$page,$this->title,$charset,'php://Output');
		$context->quit();	
	}
}