<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class SellRecordRankModel extends TbModel {
    function  get_table(){
        return 'oms_sell_record_rank';
    }
    
    function get_rank_list($data){
        if(empty($data['op_gift_strategy_detail_id']) || empty($data['rank_hour'])){
            return $this->format_ret(-1,'','赠品规则或活动时间不存在！');
        }
        //查找排名送相关信息
        $sql = "select r1.time_type,r1.start_time,r1.end_time,r2.money_min,r2.is_goods_money,r2.ranking_time_type,r1.strategy_code,
                    r2.ranking_hour,r3.range_start,r3.range_end,r2.op_gift_strategy_detail_id
                from op_gift_strategy r1 
                inner join op_gift_strategy_detail r2 on r1.strategy_code = r2.strategy_code 
                inner join op_gift_strategy_range r3 on r3.op_gift_strategy_detail_id = r2.op_gift_strategy_detail_id 
                where r2.ranking_hour = :ranking_hour and r3.op_gift_strategy_detail_id = :op_gift_strategy_detail_id and r2.status = 1 and r2.type = 2 ";
        $rank_info = $this->db->get_all($sql,array(":ranking_hour" => $data['rank_hour'],":op_gift_strategy_detail_id" => $data['op_gift_strategy_detail_id']));
        if(empty($rank_info)){
            return $this->format_ret(-1,'','请设置赠品规则！');
        }
        $rank_record_info = $this->get_rank_record_list($rank_info,$data['shop_code']);
        return $rank_record_info;
    }
    
    function get_rank_record_list($rank_info,$shop_code){
       
        $this->begin_trans();
        try {
            $rank_list = array();
            foreach ($rank_info as $rank){
                //排名类型，下单时间，付款时间
                $time_field = $rank['time_type'] == 0 ? 'pay_time' : 'record_time';
                $order_field = $time_field == 'pay_time' ? 'record_time' : 'pay_time';
                $sql = "select count(*) from oms_sell_record_rank where shop_code = :shop_code and ranking_hour = :ranking_hour and op_gift_strategy_detail_id = :op_gift_strategy_detail_id and rank_start = :rank_start and rank_end = :rank_end";
               //删除历史数据
                $record_rank = $this->db->get_value($sql,array(':shop_code' => $shop_code,":ranking_hour" => $rank['ranking_hour'],":op_gift_strategy_detail_id" => $rank['op_gift_strategy_detail_id'],":rank_start" => $rank['range_start'], ":rank_end" => $rank['range_end']));
                if($record_rank > 0){
                    $delete_ret = $this->delete(array('shop_code' => $shop_code,"ranking_hour" => $rank['ranking_hour'],"op_gift_strategy_detail_id" => $rank['op_gift_strategy_detail_id'],"rank_start" => $rank['range_start'], "rank_end" => $rank['range_end']));
                    if($delete_ret['status'] < 0){
                        $this->rollback();
                        return $delete_ret;
                    }
                }
                //查找排名送赠品
                $gift_sql = "select r2.sku,r2.num from op_gift_strategy_range r1 INNER JOIN op_gift_strategy_goods r2 ON r1.op_gift_strategy_detail_id = r2.op_gift_strategy_detail_id
                         where r1.op_gift_strategy_detail_id = :op_gift_strategy_detail_id and range_start = :range_start and range_end = :range_end and r2.is_gift = 0";
                $gift_info = $this->db->get_all($gift_sql,array(':op_gift_strategy_detail_id' => $rank['op_gift_strategy_detail_id'],':range_start' => $rank['range_start'],':range_end' => $rank['range_end']));
                
                $record_sql_join = '';
                if(!empty($gift_info)){
                    $gift_arr = array();
                    foreach ($gift_info as $gift){
                        $gift_arr[] = $gift['sku'];
                    }
                    $gift_str = implode("','", $gift_arr);
                    $record_sql_join = " INNER JOIN oms_sell_record_detail r2 on r1.sell_record_code = r2.sell_record_code and (r2.sku in ('{$gift_str}') OR r2.combo_sku in ('{$gift_str}')) and r2.is_gift != 1 ";
                    
                    //print_r($record_sql_join);die;
                    
                }
                //查找订单
                $record_sql = "select r1.sell_record_code,r1.order_status,deal_code_list,sale_channel_code,buyer_name,store_code,shop_code,payable_money,record_time,pay_time,customer_code"
                        . " from oms_sell_record r1 {$record_sql_join} "
                        . " where shop_code = :shop_code and {$time_field} >= :ranking_hour "
                        . " and {$time_field} >=:start_time and {$time_field} <=:end_time "
                        . " and (order_status = 0 or order_status = 1) and (pay_type = 'cod' or pay_status=2)";

                //是否满额
                if($rank['is_goods_money'] == 1){
                    $record_sql .= " and payable_money >= '{$rank['money_min']}'";
                }
                $rank_start = $rank['range_start'] - 1;
                $range_end = $rank['range_end'] - $rank_start;
                
                if(!empty($record_sql_join)) {
                	 $record_sql .= " group by r1.sell_record_code ";
                }

                $record_sql .= " order by {$time_field} asc,$order_field asc limit {$rank_start},{$range_end}";
                
                $record = $this->db->get_all($record_sql,array(':ranking_hour' => $rank['ranking_hour'],':start_time' => date("Y-m-d H:i:s",$rank['start_time']),':end_time' => date("Y-m-d H:i:s",$rank['end_time']),':shop_code' => $shop_code));
                if(!empty($record)){
                	
                	if(count($record) > 0) {
	                	$sell_record_code_list = array();
	                	foreach ($record as $sub_record) {
	                		$sell_record_code_list[] = $sub_record['sell_record_code'];
	                	}
	                	
	                	$sell_record_code_list_str = "'".implode("','", $sell_record_code_list)."'";
	                	//从策略日志表查找排名送成功订单
	                	$sql = "select * from op_strategy_log where sell_record_code in (".$sell_record_code_list_str.") and is_success = 1 and strategy_detail_id = '{$rank['op_gift_strategy_detail_id']}'";
	                	$strategy_logs = $this->db->get_all($sql);

                		foreach ($record as $key=>$sub_record) {
                		    //从策略日志表确定订单赠送是否成功
	                		foreach ($strategy_logs as $strategy_log) {
	                			if($sub_record['sell_record_code'] == $strategy_log['sell_record_code']) {
	                				$record[$key]['is_has_given'] = $strategy_log['is_success'];
	                				break;
	                			}
	                		}
	                	}
                	}

                	//插入排名送订单中间表
                    $ret = $this->insert_rank_record($record,$rank);
                    if($ret['status'] < 0){
                        $this->rollback();
                        return $ret;
                    }
                    $rank_list[] = $ret['data'];
                }
            }
            $this->commit();
            return $this->format_ret(1,$rank_list);
        }catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '保存失败:' . $e->getMessage());
        }
       
    }
    
    function insert_rank_record($record,$rank){
        
        $record_list = array();
        foreach ($record as $data){
            $parmas = array();
            $parmas['sell_record_code'] = $data['sell_record_code'];
            $parmas['order_status'] = $data['order_status'];
            $parmas['deal_code_list'] = $data['deal_code_list'];
            $parmas['sale_channel_code'] = $data['sale_channel_code'];
            $parmas['buyer_name'] = $data['buyer_name'];
            $parmas['store_code'] = $data['store_code'];
            $parmas['shop_code'] = $data['shop_code'];
            $parmas['order_money'] = $data['payable_money'];
            $parmas['record_time'] = $data['record_time'];
            $parmas['customer_code'] = $data['customer_code'];
            $parmas['op_gift_strategy_detail_id'] = $rank['op_gift_strategy_detail_id'];
            $parmas['pay_time'] = $data['pay_time'];
            $parmas['ranking_hour'] = $rank['ranking_hour'];
            $parmas['rank_start'] = $rank['range_start'];
            $parmas['rank_end'] = $rank['range_end'];
            $parmas['strategy_code'] = $rank['strategy_code'];
            
            if(isset($data['is_has_given'])) {
            	$parmas['is_has_given'] = $data['is_has_given'];
            } else {
            	$parmas['is_has_given'] = 0;
            }
            
            $record_list[] = $parmas;
        }
        $ret = $this->insert_multi($record_list);
        if($ret['status'] < 0){
            return $ret;
        }
        return $this->format_ret(1,$record_list);
    }

    function get_send_record($data){
        $sql = "select * from {$this->table} where ranking_hour = :ranking_hour and op_gift_strategy_detail_id = :op_gift_strategy_detail_id and shop_code = :shop_code";
        $sell_record = $this->db->get_all($sql, array(':ranking_hour' => $data['rank_hour'],':op_gift_strategy_detail_id' => $data['op_gift_strategy_detail_id'],':shop_code' => $data['shop_code']));
        return $sell_record;
    }
    
    public function send_gift($data) {
        //从中间表查找需要赠送的订单
        $sell_record = $this->get_send_record($data);
        if(empty($sell_record)){
            return $this->format_ret(-1,'','订单信息不存在！');
        }
        
        $error_info = array();
        $count = count($sell_record);
        $inv_arr = array();
        $this->begin_trans();
        try {
        	
        $sell_record_code_list = array();
        foreach ($sell_record as $sub_record) {
            $sell_record_code_list[] = $sub_record['sell_record_code'];
        }

        $sell_record_code_list_str = "'".implode("','", $sell_record_code_list)."'";

        //获取没有赠送成功的订单记录
        $sql = "select * from op_strategy_log where sell_record_code in (".$sell_record_code_list_str.") and is_success = 1 and strategy_detail_id = '{$data['op_gift_strategy_detail_id']}'";

        $strategy_logs = $this->db->get_all($sql);
  
        $success_num = 0;//定义成功赠送的订单笔数

        foreach ($sell_record as $record){
            //订单状态必须是为未确认才能送
            if($record['order_status'] != 0) {
                    continue;
            }

            //判断订单是是否已送过，送过的就不送了
            $is_has_given = false;
            foreach ($strategy_logs as $strategy_log) {
                if ($record['sell_record_code'] == $strategy_log['sell_record_code']) {
                    $is_has_given = true;
                }
            }
            if ($is_has_given) {
                continue;
            }

            //查询需要送的赠品信息
            $gift_sql = "select r2.sku,r2.num from op_gift_strategy_range r1 inner join op_gift_strategy_goods r2 on r1.id = r2.op_gift_strategy_range_id
                         where r1.op_gift_strategy_detail_id = :op_gift_strategy_detail_id and range_start = :range_start and range_end = :range_end and r2.is_gift = 1";
            $gift_goods_info = $this->db->get_all($gift_sql,array(':op_gift_strategy_detail_id' => $record['op_gift_strategy_detail_id'],':range_start' => $record['rank_start'],':range_end' => $record['rank_end']));
            if(empty($gift_goods_info)){
                $error_info[$record['sell_record_code']] = '查询不到赠品信息！';
                continue;
            }

            //向订单表插入赠品，$inv_arr记录库存信息
            $ret = $this->get_record_gift_detail($gift_goods_info,$record,$inv_arr,$error_info);
            $gift_log = array();
            $gift_log['type'] = 'gift';
            $gift_log['strategy_code'] = $record['strategy_code'];
            $gift_log['strategy_detail_id'] = $record['op_gift_strategy_detail_id'];
            $gift_log['deal_code'] = $record['deal_code_list'];
            $gift_log['sell_record_code'] = $record['sell_record_code'];
            $gift_log['customer_code'] = $record['customer_code'];
            $gift_log['is_success'] = 1;
            if($ret['status'] < 0 ){
                $gift_log['is_success'] = 0;
                $error_info[$record['sell_record_code']] = $ret['message'];
                $gift_arr[] = $gift_log;
                continue;
            } else {
                $gift_arr[] = $gift_log;
                $success_num++;
            }
        }

        if(empty($gift_arr)){
            return $this->format_ret(-1,'','没有找到需要赠送赠品的订单！');
        }

        //插入策略日志表
        $ret = load_model('op/StrategyLogModel')->insert_multi($gift_arr);
        if($ret['status'] < 0){
            $this->rollback();
            return $ret;
        }
            $this->commit();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        
        $message = '';
        $message .= "操作结束，操作成功订单数:".$success_num."<br>操作失败的订单数:".count($error_info);
        if( count($error_info) > 0){
            $fail_top = array('订单号', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_info);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        return $this->format_ret(1,'',$message);
    }
    
    function get_record_gift_detail($gift_goods_info,$record,&$inv_info,&$error_info){

        $store_code = $this->db->get_value("select store_code from oms_sell_record where sell_record_code = :sell_record_code",array(":sell_record_code" => $record['sell_record_code']));
        $data = array();
        //查询策略相关信息
        $op_gift_strategy_detail_id = $record['op_gift_strategy_detail_id'];
        $sql = "select is_continue_no_inv,r2.strategy_code,r2.name,is_once_only from op_gift_strategy r1 
                    inner join op_gift_strategy_detail r2 on r1.strategy_code = r2.strategy_code
                where r2.op_gift_strategy_detail_id = :op_gift_strategy_detail_id";
        $row = $this->db->get_row($sql, array(":op_gift_strategy_detail_id" => $op_gift_strategy_detail_id));
        //库存不足是否继续送
        $is_continue_no_inv = $row['is_continue_no_inv'];
        //一个会员赠送1次
        if($row['is_once_only'] == 1){
            $params = array();
            $params['type'] = 'gift';
            $params['strategy_code'] = $record['strategy_code'];
            $params['strategy_detail_id'] = $record['op_gift_strategy_detail_id'];
            $params['sell_record_code'] = $record['sell_record_code'];
            $params['customer_code'] = $record['customer_code'];
            $check_is_sended = load_model('op/StrategyLogModel')->check_is_sended($params);
            if($check_is_sended['data'] > 0){
                return $this->format_ret(-1,'','赠品策略开启"一个会员赠送1次",该订单已经添加过赠品');
            }
        }
        foreach ($gift_goods_info as $gift){
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($gift['sku'], $key_arr);
            $key = $record['sell_record_code'].'_'.$gift['sku'].'_'.$record['rank_start'].'_'.$record['rank_end'];
            //没有开启库存不足是否继续送，校验库存
            if ($is_continue_no_inv == 0) {
                if(!array_key_exists($key, $inv_info)){
                    $able_inv = $this->get_able_inv($store_code,$gift['sku']);
                    $inv_info[$key] = $able_inv;
                }else{
                    $able_inv = $inv_info[$key];
                }
                $able_inv -= $gift['num'];
                $inv_info[$key] = $able_inv;
                if($able_inv < 0){
                    $error_info[$record['sell_record_code']] .= "{$sku_info['barcode']}库存不足不赠送;";
                    continue;
                }
            }
            $gift_goods = array();
            $gift_goods['sku'] = $gift['sku'];
            $gift_goods['num'] = $gift['num'];
            $gift_goods['barcode'] = $sku_info['barcode'];
            $gift_goods['is_gift'] = 1;
            $data['data'][] = $gift_goods;
        }
        //无满足条件赠品
        if (empty($data['data'])) {
            return $this->format_ret('-1', '', '全部赠品库存不足！');
        }
        $data['sell_record_code'] = $record['sell_record_code'];
        $data['deal_code'] = $record['deal_code_list'];
        $data['action_rank_log'] = '策略代码'.$row['strategy_code'].'中规则:'.$row['name'].'增加的赠品';
        //订单添加赠品
        $ret = load_model('oms/SellRecordOptModel')->opt_new_multi_detail($data);
        return $ret;
    }
    
    function get_able_inv($store_code,$sku){
        $sql = "select stock_num,lock_num from goods_inv where store_code = :store_code and sku = :sku";
        $inv_row = $this->db->get_row($sql,array(":store_code" => $store_code,":sku" => $sku));
        return $inv_row['stock_num'] - $inv_row['lock_num'];
    }

    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("sell_record_gift_add" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
}
