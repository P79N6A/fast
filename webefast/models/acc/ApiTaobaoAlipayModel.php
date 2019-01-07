<?php
/**
 * 支付宝收支相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
class ApiTaobaoAlipayModel extends TbModel
{
	function get_table() {
		return 'api_taobao_alipay';
	}
	
	public $api_type = array(
		'payment' => '在线支付',
		'transfer' => '转账',
		'deposit' => '充值',
		'withdraw' => '提现',
		'charge' => '收费',
		'preauth' => '预授权',
		'other' => '其它',
	);
	public $check_accounts_status = array(
			'0' =>'未核销',
			'10' =>'已核销',
			'20' =>'部分核销',
			'30' =>'虚拟核销',
			'40' =>'人工核销',
			'50' =>'核销失败',
			);
	/*
	 * 根据条件查询数据
	*/
	function get_by_page($filter){
		$wh = $this->get_list_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$select = 'rl.*';
		$sql_main .= " order by create_time desc ";
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
		filter_fk_name($data['data'], array('shop_code|shop'));
                $api_type = $this->api_type;
                foreach($data['data'] as &$v){
                    $v['type'] = isset($api_type[$v['type']]) ? $api_type[$v['type']] : '';
                }
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
	function get_list_where($filter){
		$sql_main = "FROM {$this->table} rl
		WHERE 1";
		$sql_values = array();
		// 单据编号
		if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
                    $sql_main .= " AND (rl.deal_code = :deal_code )";
                    $sql_values[':deal_code'] = $filter['deal_code'];
		}
		if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
			$arr = explode(',',$filter['shop_code']);
			$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
			$sql_main .= " AND rl.shop_code in ({$str}) ";
		}
		// 单据状态
		if (isset($filter['type']) && $filter['type'] != '') {
			$arr = explode(',',$filter['type']);
			$str = $this->arr_to_in_sql_value($arr, 'type', $sql_values);
			$sql_main .= " AND rl.type in ({$str}) ";
		}
		
		//下单日期
		if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
			$sql_main .= " AND (rl.create_time >= :create_time_start )";
			$sql_values[':create_time_start'] = $filter['create_time_start']." 00:00:00";
		}
		if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
			$sql_main .= " AND (rl.create_time <= :create_time_end )";
			$sql_values[':create_time_end'] = $filter['create_time_end']." 23:59:59";
		}
		return array('sql_main' => $sql_main,'sql_values' => $sql_values);
	}
	function do_update_check_status($aid){
		$user_code = CTX()->get_session('user_code');
		$check_accounts_time = date('Y-m-d H:i:s');
		$up_arr = array(
				'check_accounts_status' => 40,
				'check_accounts_user_code' => $user_code,
				'check_accounts_time' =>$check_accounts_time,
				);
		//更新支付宝流水核销状态
        if(is_array($aid)){
            foreach($aid as $key=>$val){
                $this->db->update('api_taobao_alipay',$up_arr,array('aid' => $val));
            }
            $aid_str = $this->arr_to_in_sql_value($aid, 'aid', $sql_values);
            $sql = "select deal_code,account_month,account_month_ym from api_taobao_alipay where aid in ({$aid_str})";
            $alipay_row = $this->db->get_all($sql,$sql_values);
            if (!empty($alipay_row)){
                foreach($alipay_row as $k=>$v){
                    $up_arr['account_month'] = $v['account_month'];
                    $up_arr['account_month_ym'] = $v['account_month_ym'];
                    $this->db->update('oms_sell_settlement',$up_arr,array('deal_code'=>$v['deal_code']));
                    $this->db->update('oms_sell_settlement_record',$up_arr,array('deal_code'=>$v['deal_code']));
                }
            }
            return $this->format_ret(1,'');
        }else {
            $this->db->update('api_taobao_alipay', $up_arr, array('aid' => $aid));
            $sql = "select deal_code,account_month,account_month_ym from api_taobao_alipay where aid=$aid";
            $alipay_row = $this->db->getRow($sql);
            if (!empty($alipay_row)) {
                $up_arr['account_month'] = $alipay_row['account_month'];
                $up_arr['account_month_ym'] = $alipay_row['account_month_ym'];
                $this->db->update('oms_sell_settlement', $up_arr, array('deal_code' => $alipay_row['deal_code']));
                $this->db->update('oms_sell_settlement_record', $up_arr, array('deal_code' => $alipay_row['deal_code']));
            }
            return $this->format_ret(1, '');
        }
	}
	function get_search_where($filter){
		$sql_main = "FROM {$this->table} rl WHERE 1";
		//$sql_main .= " AND account_item_code!='' ";
		$sql_values = array();
		//选项卡
		if ($filter['check_tab'] == 'no_check') {
			$sql_main .= " AND check_accounts_status IN (0,50)";
		} elseif ($filter['check_tab'] == 'part_check'){
			$sql_main .= " AND check_accounts_status=20";
		} elseif ($filter['check_tab'] == 'have_check'){
			$sql_main .= " AND check_accounts_status IN (10,30,40)";
		}
		// 单据编号
		if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
			$sql_main .= " AND (rl.deal_code = :deal_code )";
			$sql_values[':deal_code'] = $filter['deal_code'];
		}
		
		//店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
			$arr = explode(',',$filter['shop_code']);
			$str = $this->arr_to_in_sql_value($arr, 'shop_code', $sql_values);
			$sql_main .= " AND rl.shop_code in ({$str}) ";
		}
		if (isset($filter['account_month_ym']) && $filter['account_month_ym'] != '') {
			$sql_main .= " AND rl.account_month_ym = :account_month_ym ";
			$sql_values[':account_month_ym'] = $filter['account_month_ym'];
		}
        //科目
        if (isset($filter['account_item']) && $filter['account_item'] != '') {
		    $ret=$this->get_all_account_item();
            $item_arr=explode(',',$filter['account_item']);
            $arr=array_diff($item_arr,$ret);
            if(!empty($arr)){
                    $item_arr=array_diff($ret,$item_arr);
                    if(!empty($item_arr)){
                        $item_str = $this->arr_to_in_sql_value($item_arr, 'account_item', $sql_values);
                        $sql_main .= "AND  account_item_code not in ({$item_str})";
                    }
            }else{
                $item_str = $this->arr_to_in_sql_value($item_arr, 'account_item', $sql_values);
                $sql_main .= "AND  account_item_code  in ({$item_str})";
            }
        }
		return array('sql_main' => $sql_main,'sql_values' => $sql_values);
	}
	//获取所有的科目
    function get_all_account_item($type){
	    $sql='select code,account_item,in_out_flag from alipay_account_item order by in_out_flag asc';
	    $ret=$this->db->get_all($sql);
	    if($type=='temp'){
            foreach($ret as $k=>$val){
                if(!empty($val['account_item'])){

                    $item_type = '支出';
                    if ($val['in_out_flag'] == 1){
                        $item_type = '收入';
                    }
                    $res[$k]['id']=$val['code'];
                    $res[$k]['account_item']="[{$item_type}]".$val['account_item'];
                }
            }
            $res[$k+1]['id']='others';
            $res[$k+1]['account_item']='其他';
            return $res;
        }else{
            foreach($ret as $k=>$val){
                if(!empty($val['account_item'])){
                    $res[$k]=$val['code'];
                }
            }
             return $res;
        }


    }
	//支付宝流水合计
	function alipay_total_amount_search($filter){
		$sql = 'select SUM(in_amount) as in_je,SUM(out_amount) as out_je ';
		$wh = $this->get_list_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$sql .= $sql_main;
		$data = $this->db->get_row($sql,$sql_values);
		$data['in_je'] = $data['in_je']?$data['in_je']:'0.00';
		$data['out_je'] = $data['out_je']?$data['out_je']:'0.00';
		return $data;
	}
	function total_amount_search($filter){
		$sql = 'select SUM(in_amount) as in_je,SUM(out_amount) as out_je ';
		$wh = $this->get_search_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$sql .= $sql_main;
		$data = $this->db->get_row($sql,$sql_values);
		$data['in_je'] = $data['in_je']?$data['in_je']:'0.00';
		$data['out_je'] = $data['out_je']?$data['out_je']:'0.00';
		return $data;
	}
	/*
	 *支付宝核销查询
	*/
	function get_search_by_page($filter){
		/*
		$sql_main = "FROM {$this->table} rl WHERE 1";
		$sql_main .= " AND account_item_code='001' ";
		$sql_values = array();
		//选项卡
		if ($filter['check_tab'] == 'no_check') {
			$sql_main .= " AND check_accounts_status IN (0,50)";
		} elseif ($filter['check_tab'] == 'part_check'){
			$sql_main .= " AND check_accounts_status=20";
		} elseif ($filter['check_tab'] == 'have_check'){
			$sql_main .= " AND check_accounts_status IN (10,30,40)";
		}
		// 单据编号
		if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
			$sql_main .= " AND (rl.deal_code = :deal_code )";
			$sql_values[':deal_code'] = $filter['deal_code'];
		}
	
		//店铺
		if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
			$arr = explode(',',$filter['shop_code']);
			$str = "'".join("','",$arr)."'";
			$sql_main .= " AND rl.shop_code in ({$str}) ";
		}
		if (isset($filter['create_time']) && $filter['create_time'] != '') {
			$start_time = $filter['create_time'].'-01 00:00:00';
			$end_time = date('Y-m-01 00:00:00',strtotime($filter['create_time'].' +1 month'));
			$sql_main .= " AND rl.create_time >= :start_time ";
			$sql_values[':start_time'] = $start_time;
			$sql_main .= " AND rl.create_time < :end_time ";
			$sql_values[':end_time'] = $end_time;
		} */
		$wh = $this->get_search_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$select = 'rl.*';
		$sql_main .= " order by create_time desc ";
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
                
		filter_fk_name($data['data'], array('shop_code|shop'));
		foreach ($data['data'] as $key=>&$value) {
			//核销状态
			$value['check_accounts_status_txt'] = $this->check_accounts_status[$value['check_accounts_status']];
			//科目
            if(!empty($value['account_item_code'])){
                $first_str = substr($value['account_item_code'], 0,1);
                $item_type = '收入';
                if ($first_str == '1'){
                    $item_type = '支出';
                }
                $value['account_item_txt'] = "[{$item_type}]".$value['account_item'];
            }

                        //导出数据的账期                        
                        $value['account_month_ym'] = $filter['account_month_ym'];
		}
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
    function get_list_by_deal_code($filter){
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE deal_code=:deal_code";
        $sql_values[':deal_code'] = $filter['deal_code'];
        $select = 'rl.*';
        $sql_main .= " ORDER BY aid DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach($data['data'] as $key=>&$value){
            $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code'=>$value['shop_code']));
        	$value['api_type'] = $this->api_type[$value['type']];
        }
        return $this->format_ret(1, $data);
    }
	
	
	function imoprt_detail($shop_code,$file){
		
		$sku_arr = $sku_num = array();
		$error_msg = '';
	
		
		$sql = $this->read_csv_sku($file, $sku_arr, $sku_num,$shop_code);
		// print_r($sku_arr);
		 //print_r($sql);
		 $is_filter_repeat = true;
		 if($sql <> ''){
		 	$sql = substr($sql, 1);
		 	$sql = 'INSERT ' . ($is_filter_repeat ? 'ignore' : '') . ' INTO  api_taobao_alipay ' . '(alipay_order_no,opt_user_id,balance,out_amount,in_amount,shop_code) VALUES' . $sql . ";";
		 	//echo $sql;
		 	$ret = $this->db->query($sql);
		 	if (!$ret) {
		 		return $this->format_ret("-1", '', 'insert_error');
		 	}else{
		 		//$ret['data'] = '导入成功';
		 		return $this->format_ret("1", '');
		 	}
		 }
		
	}
	function read_csv_sku($file,&$sku_arr,&$sku_num,$shop_code){
		//    $key_arr = array('0'=>'sku','1'=>'num');
		$file = fopen($file, "r");
		$i =0 ;
		$sql= '';
		while (!feof($file)) {
		$row = fgetcsv($file);
		if ($i >= 1) {
		$this->tran_csv($row);
			if(!empty($row[0])){
				$row[5] = $shop_code;
				$sku_arr[] = $row;
				$sku_num[$row[0]] = $row[1];
				$sql .= ",('" . implode("','", $row) . "')";
		    }
		}
		$i++;
		}
		fclose($file);
		return $sql;
			// var_dump($sku_arr,$sku_num);die;
	}
	private function tran_csv(&$row){
		if(!empty($row)){
			foreach($row as &$val){
				$val = iconv('gbk','utf-8',$val);
				$val = str_replace('"', '', $val);
				//   $row[$key] = $val;
			}
		}
	}

    /**
     * 更新
     * @param $aid
     * @param $params
     * @return array
     */
    function update_alipay_info($aid, $params) {
        $ali = $this->get_row(array('aid' => $aid));
        if ($ali['status'] != 1) {
            return $this->format_ret(-1, '', '单据不存在！');
        }
        $ali_data = $ali['data'];
        if ($ali_data['check_accounts_status'] != 40) {
            return $this->format_ret(-1, '', '单据非人工核销状态！');
        }
        if (strtotime($params['check_accounts_time']) == strtotime($ali_data['check_accounts_time'])) {
            return $this->format_ret(2, '', '数据未变更!');
        }

        $update_check_accounts_time = date('Y-m-d H:i:s', strtotime($params['check_accounts_time']));
        $ret = $this->update(array('check_accounts_time' => $update_check_accounts_time), array('aid' => $aid));
        if ($ret['status'] != 1) {
            return $ret;
        }
        //操作日志
        $log_xq = "核销时间由:{$ali_data['check_accounts_time']} 修改为 {$update_check_accounts_time}";
        $log = array(
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
            'module' => '账务',
            'yw_code' => $ali_data['deal_code'],
            'operate_type' => '修改核销时间',
            'operate_xq' => $log_xq
        );
        $ret = load_model('sys/OperateLogModel')->insert($log);
        return $this->format_ret(1,'','变更成功！');
    }



}
