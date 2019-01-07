<?php
/**
 * 支付宝收支相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
class OmsSellSettlementModel extends TbModel
{
	function get_table() {
		return 'oms_sell_settlement';
	}
	public $order_attr = array(
			1 => '销售',
			2 => '退货',
			3 => '调整'
	);
	public $settle_type = array(
		'1' => '商品',
		'2' => '邮费',
		'3' => '补差',
		'4' => '调整',
	);
	public $check_accounts_status = array(
			'0' =>'未核销',
			'10' =>'已核销',
			'20' =>'部分核销',
			'30' =>'虚拟核销',
			'40' =>'人工核销',
			'50' =>'核销失败',
			);
	function do_update_check_status($deal_code,$account_month = ""){
		$user_code = CTX()->get_session('user_code');
		$check_accounts_time = date('Y-m-d H:i:s');
		$up_arr = array(
				'check_accounts_status' => 40,
				'check_accounts_user_code' => CTX()->get_session('user_code'),
				'check_accounts_time' => date('Y-m-d H:i:s'),
				);
		
		//更新支付宝流水核销状态
		$apipay_ret = load_model('acc/ApiTaobaoAlipayModel')->get_all(array('deal_code' => $deal_code));
		if (!empty($apipay_ret['data'])){
			$this->db->update('api_taobao_alipay',$up_arr,array('deal_code' => $deal_code));
		}
		if (!empty($account_month)){
			$up_arr['account_month'] = $account_month;
			$up_arr['account_month_ym'] = date('Y-m',strtotime($account_month));
		}
		$this->db->update('oms_sell_settlement_record',$up_arr,array('deal_code'=>$deal_code));
		
		$this->db->update('oms_sell_settlement',$up_arr,array('deal_code'=>$deal_code));
		
		return $this->format_ret(1,'');
	}
    function do_update_cancel_status($deal_code){
        $up_arr = array(
            'check_accounts_status' => 0,
            'check_accounts_user_code' => '',
            'check_accounts_time' => '',
        );

        $pay_ret = load_model('acc/ApiTaobaoAlipayModel')->get_all(array('deal_code' => $deal_code));
        if (!empty($pay_ret['data'])){
            $this->db->update('api_taobao_alipay',$up_arr,array('deal_code' => $deal_code));
        }

        $this->db->update('oms_sell_settlement_record',$up_arr,array('deal_code'=>$deal_code));
        $this->db->update('oms_sell_settlement',$up_arr,array('deal_code'=>$deal_code));

        return $this->format_ret(1,'');
    }
	//合计
	function sellsettlement_total_amount($filter){
		$sql = 'select SUM(total_fee) as total_fee ';
		$wh = $this->get_search_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$sql .= $sql_main;
		$data = $this->db->get_row($sql,$sql_values);
		$data['total_fee'] = $data['total_fee']?$data['total_fee']:'0.00';
		return $data;
	}
	function get_search_where($filter){
		$sql_main = "FROM {$this->table} rl WHERE 1";
		$sql_values = array();
		$filter['check_tab'] = empty($filter['check_tab'])?'no_check':$filter['check_tab'];
                
                $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter['shop_code']);
		//选项卡
		if ($filter['check_tab'] == 'no_check') {
			$sql_main .= " AND check_accounts_status IN (0,50)";
		} elseif ($filter['check_tab'] == 'part_check'){
			$sql_main .= " AND check_accounts_status=20";
		} elseif ($filter['check_tab'] == 'have_check'){
			$sql_main .= " AND check_accounts_status IN (10,40)";
		} elseif ($filter['check_tab'] == 'dummy_check'){
			$sql_main .= " AND check_accounts_status=30";
		}
		// 单据编号
		if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
			$sql_main .= " AND (rl.deal_code = :deal_code )";
			$sql_values[':deal_code'] = $filter['deal_code'];
		}
		
		//店铺
//		if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
//			$arr = explode(',',$filter['shop_code']);
//			$str = "'".join("','",$arr)."'";
//			$sql_main .= " AND rl.shop_code in ({$str}) ";
//		} else {
//			$shop_arr = load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao');
//			if (!empty($shop_arr)){
//				$str = "";
//				foreach ($shop_arr as $shop_row) {
//					$str .= "'{$shop_row['shop_code']}',";
//				}
//				$str = rtrim($str,',');
//				$sql_main .= " AND rl.shop_code in ({$str}) ";
//			} else {
//				$sql_main .= " AND 1=2 ";
//			}
//			
//		}
		//发货时间
		if (!empty($filter['sell_month_start'])) {
			$sql_main .= " AND rl.sell_month >= :sell_month_start ";
			$sql_values[':sell_month_start'] = $filter['sell_month_start'];
		}
		if (!empty($filter['sell_month_end'])) {
			$sql_main .= " AND rl.sell_month <= :sell_month_end ";
			$sql_values[':sell_month_end'] = $filter['sell_month_end'];
		}
		//收款日期
		if (!empty($filter['account_month_start'])) {
			$sql_main .= " AND rl.account_month >= :account_month_start ";
			$sql_values[':account_month_start'] = $filter['account_month_start'];
		}
		if (!empty($filter['account_month_end'])) {
			$sql_main .= " AND rl.account_month <= :account_month_end ";
			$sql_values[':account_month_end'] = $filter['account_month_end'];
		}
		//核销日期
		if (!empty($filter['check_accounts_time_start'])) {
			$sql_main .= " AND rl.check_accounts_time >= :check_accounts_time_start ";
			$sql_values[':check_accounts_time_start'] = $filter['check_accounts_time_start']. ' 00:00:00';
		}
		if (!empty($filter['check_accounts_time_end'])) {
			$sql_main .= " AND rl.check_accounts_time <= :check_accounts_time_end ";
			$sql_values[':check_accounts_time_end'] = $filter['check_accounts_time_end']. ' 23:59:59';
		}
		return array('sql_main' => $sql_main,'sql_values' => $sql_values);
	}
	/*
	 *支付宝核销查询
	*/
	function get_by_page($filter){
		$wh = $this->get_search_where($filter);
		$sql_main = $wh['sql_main'];
		$sql_values = $wh['sql_values'];
		$select = 'rl.*';
		$sql_main .= " order by create_time DESC,id DESC ";
		$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

		filter_fk_name($data['data'], array('shop_code|shop'));
		foreach ($data['data'] as $key=>&$value) {
			if ($value['sell_month'] =='0000-00-00'){
				$value['sell_month'] = '';
			}
			if ($value['account_month'] =='0000-00-00'){
				$value['account_month'] = '';
			}
			if ($value['check_accounts_time'] =='0000-00-00 00:00:00'){
				$value['check_accounts_time'] = '';
			}
                        //实收款（支付宝交易收款-售后维权退款）
                        $value['ali_total_fee'] = $value['ali_trade_je']-$value['sale_right_fee'];
			//核销状态
			$value['check_accounts_status_txt'] = $this->check_accounts_status[$value['check_accounts_status']];
		}
		$ret_status = OP_SUCCESS;
		$ret_data = $data;
		return $this->format_ret($ret_status, $ret_data);
	}
	
    function get_record_by_deal_code($filter){
        $sql_main = "FROM oms_sell_settlement_record rl  WHERE deal_code=:deal_code";
        //交易号
        if (isset($filter['deal_code']) && $filter['deal_code'] != '') {
        	$sql_main .= " AND deal_code = :deal_code ";
        	$sql_values[':deal_code'] = $filter['deal_code'];
        }
        
        $select = 'rl.*';
        $sql_main .= " ORDER BY id DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop'));
        foreach($data['data'] as $key=>&$value){
        	$value['order_attr_txt'] = $value['order_attr']==1?'销售':'退货';
        	$value['settle_type_txt'] = $this->settle_type[$value['settle_type']];
        	//核销状态
        	$value['check_accounts_status_txt'] = $this->check_accounts_status[$value['check_accounts_status']];
        	if ($value['check_accounts_time'] =='0000-00-00 00:00:00'){
        		$value['check_accounts_time'] = '';
        	}
        }
        return $this->format_ret(1, $data);
    }
    function get_record_detail($sell_record_code,$deal_code,$order_attr){
    	$data = load_model("oms/SellSettlementModel")->get_detail_by_deal_code($deal_code,$order_attr,$sell_record_code);
    	return $data;
    }
    function import_dz($file){
    	$data = $this->read_dz_import($file);
    	$error_msg = "";
        ////////////////////
        // 优化方法 start //
        ////////////////////
        /** 1000个一组分批处理 */
        $user_code  = CTX()->get_session('user_code');
        $check_time = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        while($group = array_splice($data, 0, 1000)){
            /** 上传的交易号 */
            $list = array_column($group, 'deal_code');
            $sql_values = array();
            $in =$this->arr_to_in_sql_value($list, 'deal_code', $sql_values);
            $sql = "SELECT `deal_code` FROM `oms_sell_settlement` "
                 . "WHERE `deal_code` IN ({$in})";
            $sql_r = "SELECT `id`, `deal_code` FROM `oms_sell_settlement_record` "
                   . "WHERE `deal_code` IN ({$in})";
            // 释放内存
            unset($in);
            /** 存在的交易号 */
            $find_r = $this->db->getAll($sql_r,$sql_values);
            unset($sql_r);
            $find   = array_column($this->db->getAll($sql,$sql_values), 'deal_code');
            unset($sql);
            $insert = $insert_r = '';
            foreach ($group as $row) {
                // 交易号是否存在以oms_sell_settlement为准
                if(!in_array($row['deal_code'], $find)){
                    $error_msg .= "交易号{$row['deal_code']}系统不存在,";
                    continue;
                } else {
                    $acc_time = str_replace('/', '-', $row['account_month']);
                    $insert .= ",('{$user_code}', '{$check_time}', '{$row['deal_code']}', '40', '{$acc_time}', '"        
                             . substr($acc_time, 0, strrpos($acc_time, '-'))."')";
                    foreach(array_keys(array_column($find_r, 'deal_code'), $row['deal_code']) as $k) {
                        $insert_r .= ",('{$user_code}', '{$check_time}','{$find_r[$k]['id']}','{$find_r[$k]['deal_code']}', '40', '{$acc_time}', '"        
                                   . substr($acc_time, 0, strrpos($acc_time, '-'))."')";
                    }
                }
            }
            /** 如果不为空执行sql */
            if (count($find) > 0) {
                unset($group);
                $sql1 = "UPDATE `api_taobao_alipay` "
                      . "SET `check_accounts_status` = '40', "
                      . "`check_accounts_user_code` = '{$user_code}', "
                      . "`check_accounts_time` = '{$check_time}' "
                      . "WHERE `deal_code` IN ('".implode("','", $find)."')";
                $insert_sql = 'VALUES '.ltrim($insert, ',');
                $insert_r_sql = 'VALUES '.ltrim($insert_r, ',');
                $update = "`check_accounts_user_code` = VALUES(check_accounts_user_code), "
                        . "`check_accounts_time` = VALUES(check_accounts_time), "
                        . "`check_accounts_status` = VALUES(check_accounts_status), "
                        . "`account_month` = VALUES(account_month), "
                        . "`account_month_ym` = VALUES(account_month_ym)";
                $sql2 = "INSERT `oms_sell_settlement_record`"
                      . "(`check_accounts_user_code`, `check_accounts_time`, `id`, `deal_code`,`check_accounts_status`,`account_month`,`account_month_ym`) "
                      . "{$insert_r_sql} ON DUPLICATE KEY UPDATE ".$update;
                $sql3 = "INSERT `oms_sell_settlement`"
                      . "(`check_accounts_user_code`, `check_accounts_time`, `deal_code`,`check_accounts_status`,`account_month`,`account_month_ym`) "
                      . "{$insert_sql} ON DUPLICATE KEY UPDATE ".$update;
                $this->db->query($sql1);
                $this->db->query($sql2);
                $this->db->query($sql3);
            }
        }
        ////////////////////
        // 优化方法 end   //
        ////////////////////
        
        //////////////////
        // 原方法 start //
        //////////////////
    	/* foreach ($data as $row) {
    		$sell_settlement_ret = load_model("oms/SellSettlementModel")->get_row(array('deal_code'=>$row['deal_code']));
    		if(empty($sell_settlement_ret['data'])){
    			$error_msg .= "交易号{$row['deal_code']}系统不存在,";
    			continue;
    		}
    		$account_month = date('Y-m-d',strtotime($row['account_month']));
    		$this->do_update_check_status($row['deal_code'],$account_month)	;
    		
    	} */
        ////////////////
        // 原方法 end //
        ////////////////
    	if (!empty($error_msg)) {
    		$file_name = create_import_fail_files($error_msg,'retail_settlement_detail_dz_import_fail');
//    		$msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
    			
    		return $this->format_ret(-1,'',$msg);
    	} else {
    		return $this->format_ret(1,'');
    	}
    }
    function read_dz_import($file){
    	//读文件***********************
    	$start_line = 1;
    	$file = fopen($file, "r");
    	$i = 0;
    	$header = array();
    	$file_str = '';
    	$data_arr = array();
    	$trans = array('deal_code'=>0,'account_month'=>1);
    	while (!feof($file)) {
    			
    		$row = fgetcsv($file);
    		if (!empty($row)) {
    			if ($i >= $start_line) {
    				foreach ($trans as $k=>$v) {
    					$trans_row[$k] = trim($row[$v]);
    				}
    				$data_arr[] = $trans_row;
    			}
    		}
    		$i++;
    			
    	}
    	fclose($file);
    	return $data_arr;
    }
}
