<?php
class record_scan_common {
	
    function view_scan(array & $request, array & $response, array & $app){    
        $record_code = $request['record_code'];
        require_model('sys/RecordScanCommonModel');
        $obj = new RecordScanCommonModel($request['dj_type']);
		$ret = $obj->view_scan($record_code);
		if ($ret['status']<0){
			$tpl = "web_page_message";
			$app['title'] = "普通扫描出错";
			$app['message'] = $ret['message'];
			$app['url'] = array();
		}else{
			$tpl = "record_scan_common";
			$response = $ret['data'];
		}
		
        ob_start();
        include get_tpl_path($tpl);
        $html = ob_get_contents();
        ob_end_clean();

        echo $html;
        die;
    }
    
    function save_scan(array & $request, array & $response, array & $app){
	    $record_code = $request['record_code']; 
        require_model('sys/RecordScanCommonModel');
        $obj = new RecordScanCommonModel($request['dj_type']);
		$response = $obj->save_scan($request);
		if ($response['status']<0){
			$response['message'] = $request['scan_barcode'].$response['message'];
		}
		//echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';die;
    }

}


