<?php
require_lib ( 'util/web_util', true );
class Brand {
	function do_list(array & $request, array & $response, array & $app) {
		
	}
	function detail(array & $request, array & $response, array & $app) {
		if(isset($request['_id']) && $request['_id'] != ''){
			$ret = load_model('prm/BrandModel')->get_by_id($request['_id']);
			$response['data'] = $ret['data'];
		}
		$response['app_scene'] = $_GET['app_scene'];
	}

	function do_edit(array & $request, array & $response, array & $app) {
		
		$brand = get_array_vars($request, array('brand_name','brand_logo','remark'));
		$ret = load_model('prm/BrandModel')->update($brand, $request['brand_id']);
		exit_json_response($ret);
	}

	function do_add(array & $request, array & $response, array & $app) {
		$brand = get_array_vars($request, array('brand_code', 'brand_name','brand_logo','remark'));
		$ret = load_model('prm/BrandModel')->insert($brand);
		exit_json_response($ret);
	}

	
	function do_delete(array & $request, array & $response, array & $app) {
		$ret = load_model('prm/BrandModel')->delete($request['brand_id']);               
		exit_json_response($ret);
	}
        
        function opt_delete(array & $request, array & $response, array & $app) {
            $i = 0;
            $fail = '';
            $fail_code='';
            foreach ($request['brand_id'] as $value) {
                $ret = load_model('prm/BrandModel')->delete($value);
                if ($ret['status'] == -1) {
                    $fail_code .= $ret['data'].',';
                    if ($i==5) {
                        $fail .= $fail_code .'<br/>';
                        $fail_code = '';
                        $i = 0;
                    }
                    $i++;
                }
            }
            if (empty($fail_code) && empty($fail)) {
                $ret2 = array(
                    "status"=> 1,
                    "data" =>  '',
                    "message"=>  ''
                );
            } else {
                $ret2 = array(
                "status"=> -1,
                "data" =>  '',
                "message"=>  "品牌代码为:{$fail}{$fail_code}的已经在业务系统中使用，不能删除！"
                );
            }           
            exit_json_response($ret2);
	}
}

