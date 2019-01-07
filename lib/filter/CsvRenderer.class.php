<?php
require_once ROOT_PATH.'boot/req_inc.php';
/**
 * 用于csv文本文件下载。
 *将response数据渲染为表格数据，通过$app['caption']=='y'设置导出标题行。
 */
class CsvRenderer implements IReponseRenderer {
	private $delimit=',';
	private $enclosure='"';
	private $enclosure_2='""';
	function render(array & $request,array & $response, array & $app) {
		if ($app ['fmt'] === 'csv') {
			if($app['err_no']!==0){
				header('Content-type: text/html;charset=UTF-8');
				echo '<b>'.lang('req_err_title').'</b><br>'.lang('req_err_no').':'.$app['err_no']."<br>\n"
						.lang('req_err_msg').$app['err_msg'];
				return true;
			}else{
				if (count ( $response ) > 0) {
					header('Content-type: text/csv;charset=gbk');
					header('Content-Disposition: attachment; filename="'.$app['grp'].'_'.$app['act'].'.csv"');
					reset($response);
					if(count($response)==1) $response=current($response);
					reset($response);
					if(isset($app['caption']) && $app['caption']=='y'){
						$data = current ( $response );
						$title = array_keys ( $data );
						echo iconv('utf-8','gbk',implode($this->delimit,  $title )) . "\n";
					}
					foreach ( $response as $row ) {
						$v=array();
						foreach ( array_values ( $row ) as $val){
							$d=strpos($val,$this->delimit)!==false;
							$c=strpos($val,$this->enclosure)!==false;
							if($d || $c){
								if($c) $val=str_replace($this->enclosure,$this->enclosure_2,$val);
								$val = $this->enclosure . $val . $this->enclosure;
							}
							$v[]=$val;
						}
						echo iconv('utf-8','gbk',implode($this->delimit, $v )) . "\n";
					}
				}
			}
			return true;
		}
		return false;
	}
}