<?php

/**
 * 将数据转换为HTML格式文件
 * @param array $data 数据数组
 * @param string $tpl 	        模板文件路径  相对路径即views ，theme目录下文件路径和名称，名称不包括.tpl.php。  
 * @param string $web_page 模板页面文件路径  如果为NULL，不使用模板页面文件，相对路径即views ，theme目录下文件路径和名称，名称不包括.tpl.php。  
 * @param string $caption 标题   如果为NULL，没有标题
 * @param string $charset 字体   如果为NULL，使用app_conf设置的字体
 * @param string $filename  HTML格式文件名称，默认为NULL，如果为NULL，函数将返回HTML结果字符串，如果为php://out，输出到浏览器。
 * @return string|true 当$filename==NULL时，返回HTML结果字符串，当$filename==php://out时，输出到浏览器，否则，写入文件。
 */
function & data_to_html(array & $data,$tpl,$web_page=NULL,$caption=NULL,$charset=NULL,$filename=NULL){
	global $context;
	if(! $web_page) $web_page='NULL';
	$app_charset=$context->get_app_conf('charset');
	$app=array('fmt'=>'html','tpl'=>$tpl,'title'=>$caption,'page'=>$web_page,'charset'=>$charset);	
	$request=array();
			
	$render = $context->get_renderer_obj("HtmlRenderer");
	if(! $render){
		$context->register_renderer('HtmlRenderer','lib/filter/HtmlRenderer.class.php');
		$render = $context->get_renderer_obj("HtmlRenderer");
	}
	if($filename=='php://out' && $charset==$app_charset)	$render->render($request, $data, $app);
	else{
		ob_start();
		$render->render($request, $data, $app);
		$s=ob_get_clean();
		if($charset!=$app_charset && $charset!=NULL) $s=iconv($app_charset,$charset,$s);
		if($filename==NULL)	return $s;
		else	file_put_contents($filename,$s);
	}
	$s=NULL;
	return $s;
}
/**
 * 将数据数组转换为CVS格式文件
 * @param array $data 数据数组
 * @param array|boolean $caption 标题  默认 false：不带标题，true：使用第一行key作为标题，array：使用caption数组数据作为标题。
 * @param string $charset 字体   如果为NULL，使用app_conf设置的字体
 * @param string $filename  CVS格式文件名称，默认为NULL，如果为NULL，函数将返回CSV结果字符串，如果为php://out，输出到浏览器。
 * @param string $delimiter csv分隔符     默认值：,
 * @param string $enclosure csv包含符     默认值："
 * @return string|boolean false 失败，否则成功，当$filename==NULL时，返回CSV结果字符串。
 */
function & data_to_csv(array $data,$caption=false,$charset=NULL,$filename=NULL,$delimiter=',',$enclosure='"'){
	if(! $data){ $r=false;return $r;};
	if(! is_array($data[0])) $data=array($data);
	global $context;
	$app_charset=$context->get_app_conf('charset');	
			
	if($filename && $charset==NULL) $fp = fopen($filename, 'w');
	else $fp = fopen('php://temp', 'w');
	
	if($caption ){
		if(is_array($caption)) fputcsv($fp,$caption,$delimiter, $enclosure);
		else fputcsv($fp,array_keys( $data[0] ),$delimiter, $enclosure);	
	}

	reset($data);
	foreach ( $data as $row ) {
		fputcsv($fp,$row,$delimiter, $enclosure);
	}
	if(! $filename || $charset!=NULL){
		rewind($fp);
		$s=stream_get_contents($fp);
		fclose($fp);

		if($app_charset!=$charset)	$s= iconv($app_charset,$charset,$s);
		if(! $filename)
			return $s;
		else{
			file_put_contents($filename,$s);
			$r=true;
			return $r;
		} 
	}else{
		fclose($fp);
		$r=true;
		return $r;	
	} 
}
/**
 * xls表列序号转换为字母，范围A~ZZ
 * @param $col
 */
function xls_col_to_ch($col){
		if($col>26){
			$ch=chr( floor( $col / 26) +64) . chr( $col % 26 +65 );
		}elseif($col==26) $ch='AA';
		else	$ch=chr($col+65);
		return $ch;	
}
function & data_to_xls(array & $data,$caption=false,$title=NULL,$filename=NULL){
	require_lib('output/extra/PHPExcel',false);
	if(! $title) $title='fastapp2_0';
	$xls = new PHPExcel();
    $xls->getProperties()->setCreator("fastapp_2_0")->setTitle($title);	
	$xls->setActiveSheetIndex(0);
	$sheet=$xls->getActiveSheet();
	$sheet->setTitle($title);	
	$row = 1;
	$col=0;
	if($caption ){
		if(! is_array($caption)) $caption=array_keys( $data[0] );
		foreach($caption as $t){
			$ch=xls_col_to_ch($col);
			$sheet->getColumnDimension($ch)->setAutoSize(true);
			$sheet->setCellValue($ch.$row,$t);
			$sheet->getStyle($ch.$row)->getFont()->setBold(true);
			$col++;
		} 
		$row ++;
	}

	reset($data);
	foreach ( $data as $rowData ) {
		$col=0;
		foreach($rowData as $key=>$val){
			$ch=xls_col_to_ch($col);
			$sheet->setCellValue($ch.$row,$val);
			$sheet->getColumnDimension($ch)->setAutoSize(true);
			$col++;	
		}
		$row++;
	}
	$objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel2007');	
	$objWriter->save($filename);
}