<?php
/**
 * 商品条码管理相关业务
 *
 * @author dfr
 *
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('prm');


class GoodsUniqueCodeModel extends TbModel
{

    function get_table() {
		return 'goods_unique_code';
	}

	/*
	 * 根据条件查询数据
	 */
	function get_by_page($filter) {
		if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
			$filter[$filter['keyword_type']] = $filter['keyword'];
		}
		$sql_values = array();
		$sql_join = "";
		$sql_main = "FROM {$this->table} rl  
		  LEFT JOIN goods_sku r2 on rl.sku = r2.sku
		 INNER JOIN  base_goods r3 on r2.goods_code = r3.goods_code   
		   WHERE 1";
		//商品编号
		if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
			$sql_main .= " AND (r3.goods_code LIKE :goods_code )";
			$sql_values[':goods_code'] = $filter['goods_code'].'%';
		}
		//商品名称
		if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
			$sql_main .= " AND (r3.goods_name LIKE :goods_name )";
			$sql_values[':goods_name'] = '%'.$filter['goods_name'].'%';
		}
		//商品条码
		if (isset($filter['barcode']) && $filter['barcode'] != '') {
	 		$sql_main .= " AND (r2.barcode LIKE :barcode )";
			$sql_values[':barcode'] = $filter['barcode'].'%';
		}
		//唯一码
		if (isset($filter['unique_code']) && $filter['unique_code'] != '') {
			$sql_main .= " AND  (rl.unique_code LIKE :unique_code) ";
			$sql_values[':unique_code'] = $filter['unique_code'].'%';
		}
		//tab 标签
		if(isset($filter['do_list_tab']) && $filter['do_list_tab'] != ''){
			if ($filter['do_list_tab'] == 'tabs_allow'){
				$sql_main .= " AND  (rl.status = :status) ";
				$sql_values[':status'] = 0;
			}
			if ($filter['do_list_tab'] == 'tabs_not_allow'){
				$sql_main .= " AND  (rl.status = :status) ";
				$sql_values[':status'] = 1;
			}
		}
		$sql_main .= " ORDER BY rl.lastchanged DESC ";
		$select = 'rl.*, r3.goods_name,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.barcode';
		$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
		foreach($data['data'] as $key => &$value){
			$value['spec1_name'] = oms_tb_val('base_spec1', 'spec1_name', array('spec1_code' => $value['spec1_code']));
			$value['spec2_name'] = oms_tb_val('base_spec2', 'spec2_name', array('spec2_code' => $value['spec2_code']));
			$value['is_allow_name'] = $value['status'] == 1?'不可用':'可用';
		}
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}

	function get_by_id($id) {

		return  $this->get_row(array('barcode_id'=>$id));
	}
	
	/*
	 * 删除记录
	 * */
	function delete($sku_id) {
		$sql = "select goods_code,spec1_code,spec2_code FROM goods_sku where sku_id = '{$sku_id}' limit 1 ";
		$rs = $this->db->get_all($sql);
		$sql = "delete from goods_barcode where goods_code ='{$rs[0][goods_code]}' and spec1_code = '{$rs[0][spec1_code]}' and spec2_code = '{$rs[0][spec2_code]}'";
		$ret = $this -> db -> query($sql);
		$ret = parent::delete(array('sku_id'=>$sku_id));
		return $ret;
	}

	/*
	 * 服务器端验证
	*/
	private function valid($goods_code,$spec1_code,$spec2_code, $is_edit = false) {
		if (!$is_edit && (!isset($goods_code) || !valid_input($goods_code, 'required'))) return GOODS_ERROR_CODE1;
		if (!isset($spec1_code) || !valid_input($spec1_code, 'required')) return GOODS_ERROR_NAME2;
		if (!isset($spec2_code) || !valid_input($spec2_code, 'required')) return GOODS_ERROR_NAME3;
		return 1;
	}

	/**
	 * 判断是否存在
	 * @param $value
	 * @param string $field_name
	 * @return array
	 */
	function is_exists($value, $field_name='barcode_code') {
		$ret = parent::get_row(array($field_name=>$value));

		return $ret;
	}
	
	/**
     * 新增
     * @param $skuCode
     * @param $storeCode
     * @param $shelfCode
     * @param string $batchNumber
     * @return array
     */
        function add($file) {

        //读文件***********************
        $start_line = 0;
        $file = fopen($file, "r");
        $i = 0;
        $header = array();
        $file_str = '';
        $data_arr = array();
        $result_data = array();
        while (!feof($file)) {
            if ($i >= $start_line) {
                $row = fgetcsv($file);
                if (!empty($row)) {
                    $data_arr[] = $row;
                    $i++;
                }
            } else {
                $header[] = fgetcsv($file);
            }
            $i++;
        }
        array_shift($data_arr);
//        array_pop($data_arr);
        fclose($file);

        $tips = array();
        if(is_array($data_arr) && count($data_arr) > 0){
        foreach ($data_arr as $key => $v) {
            $rus = $this->is_valid_excel_data($v, $key);
            if($rus['status'] == 1){
            $sql = "select * from goods_unique_code where unique_code = :code";
            $unique_code = $this->db->get_row($sql, array('code' => $v[0]));
            
            if (!empty($unique_code)) {
                $tips[]= "唯一码已存在:" . $v[0] . ",";
                continue;
            } 
            
                $sql = "select sku from goods_sku where barcode = :code";
                $sku = $this->db->get_row($sql, array('code' => $v[1]));
                if (empty($sku)) {
                    $tips[]= "条形码不存在:" . $v[1] . ",";
                    continue;
                } 
                    $d = array(
                        'unique_code' => $v[0],
                        'sku' => $sku['sku'],
                        'lastchanged' => date("Y-m-d H:i:s", time()),
                    );
                    array_push($result_data, $d);
             } else {
                $tips[] = $rus;
            }  
          }
        }else{
            $error_msg =  array('status' => '-1', 'data' => '','msg' => '没有需要导入的唯一码！'); 
            return $error_msg;
        }    
        $success_num = count($result_data);
        $all_count = count($data_arr);
        $ret = $this->insert($result_data);
        if ($ret['status'] == 1) {
            if (!empty($tips)) {
                $msg = $this->get_error_msg($tips, $success_num, $all_count, 1);
            } else {
                $msg = '导入成功';
            }
            $ret = array('status' => '1',
                'data' => '',
                'msg' => $msg
            );
        } else {
            $msg = $this->get_error_msg($tips, $success_num, $all_count, 0);
            $ret = array(
                'status' => '-1',
                'tip' => '',
                'msg' => $msg
            );
        }
        return $ret;
    }

    function get_error_msg($err_msg,$success_num,$all_count,$type){
    	$msg = '';
    	if ($type == 1 ){
    		$msg .= '导入成功'.$success_num.'条信息，导入失败'.($all_count-$success_num).'条信息，失败信息';
    	} else {
    		$msg .= '导入成功 0 条信息，导入失败'.$all_count.'条信息，失败信息';
    	}
    	$file_name = load_model('prm/GoodsImportModel')->create_import_fail_files($err_msg,'goods_unique_code_import');
//    	$msg .= "<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $msg .= "<a target=\"_blank\" href=\"{$url}\" >下载</a>";
    	return $msg;
    }
    
    /**
     * 判定导入数据是否有效
     * @param type $row_data 行数据
     * @return true 有效 false 无效
     */
    function is_valid_excel_data($row_data, $key) {
        $key += 2;
        if ($row_data[0] == '') {
            $err = '第' . $key . '行唯一码不能为空;';
            return $err;
        }
        if ($row_data[1] == '') {
            $err = '第' . $key . '行商品条形码不能为空;';
            return $err;
        }
        return $this->format_ret(1);
    }
    
    /**
     * 批量添加纪录
     */
    function insert($data) {
        $ret = $this->insert_multi($data,true);;
        return $ret;
    }
}



