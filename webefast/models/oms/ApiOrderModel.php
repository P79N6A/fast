<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class ApiOrderModel extends TbModel {
    
    private $err_msg = '';
    private $log_id;
            
     function get_table() {
		return 'api_order';
	}
	//测试赠品策略简单列表搜索
    public function get_by_test_page($filter){
        $sql_main = " from {$this->table} ar inner join api_order_detail ard on ar.tid = ard.tid where 1 = 1 ";
        $sql_values = array();
        $select = 'ar.shop_code,ar.tid,ar.buyer_nick,ar.num,ar.sku_num,ar.order_money,sum(ard.avg_money) ard_money';
        if(isset($filter['tid']) && $filter['tid'] !== ''){
            $sql_values[':tid'] = $filter['tid'];
            $sql_main .= ' and ar.tid = :tid ';
        }
        if(isset($filter['include_express']) && $filter['include_express'] == 1){
            if(isset($filter['price_start'])&& $filter['price_start'] !== ''){
                $sql_values[':order_money_start'] = $filter['price_start'];
                $sql_main .= ' and ar.order_money >= :order_money_start ';
            }
            if(isset($filter['price_end'])&& $filter['price_end'] !== ''){
                $sql_values[':order_money_end'] = $filter['price_end'];
                $sql_main .= ' and ar.order_money <= :order_money_end ';
            }
        }
        if(isset($filter['buyer_nick'])&& $filter['buyer_nick'] !== ''){
            $sql_values[':buyer_nick'] = $filter['buyer_nick'];
            $sql_main .= ' and ar.buyer_nick = :buyer_nick ';
        }
        if(isset($filter['keyword_type']) && $filter['keyword_type'] !== '' && isset($filter['keyword']) && $filter['keyword'] !== '' ){
            $sql_values[':keyword'] = $filter['keyword'];
            $sql_main .= ' and ard.'.$filter['keyword_type'].' = :keyword';
        }
        if(isset($filter['num_start']) && $filter['num_start'] !== ''){
            $sql_values[':num_start'] = $filter['num_start'];
            $sql_main .= ' and ar.num >= :num_start ';
        }
        if(isset($filter['num_end']) && $filter['num_end'] !== ''){
            $sql_values[':num_end'] = $filter['num_end'];
            $sql_main .= ' and ar.num <= :num_end ';
        }
        $sql_main .= ' group by ar.tid having 1=1 ';
        if(!isset($filter['include_express']) || $filter['include_express'] == 0){
            if(isset($filter['price_start'])&& $filter['price_start'] !== ''){
                $sql_values[':order_money_start'] = $filter['price_start'];
                $sql_main .= ' and ard_money >= :order_money_start ';
            }
            if(isset($filter['price_end'])&& $filter['price_end'] !== ''){
                $sql_values[':order_money_end'] = $filter['price_end'];
                $sql_main .= ' and ard_money <= :order_money_end ';
            }
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select,true);
        filter_fk_name($data['data'], array('shop_code|shop'));
        return $this->format_ret(1,$data);
    }
    function get_by_page($filter){
        if(isset($filter['keyword_type'])&&$filter['keyword_type']!==''){
            $filter[$filter['keyword_type']]=trim($filter['keyword']);
        }
        $sql_main = "";
    	$sql = "FROM {$this->table} rl WHERE 1";
    	$sql_values = array();
    	//商店权限
    	//load_model('base/ShopModel')->get_purview_shop()
    	$filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
    	$sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code',$filter_shop_code);
    	//名称或代码
    	if (isset($filter['code_name']) && $filter['code_name'] != '') {
    		//$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
    		$sql_main .= " AND (rl.brand_code LIKE :code_name or rl.brand_name LIKE :code_name)";
    		$sql_values[':code_name'] = $filter['code_name'].'%';
    	}
    	//转单状态

    	if (isset($filter['is_change']) && $filter['is_change'] != '' ) {
    		$sql_main .= " AND rl.is_change = :is_change ";
    		$sql_values[':is_change'] = $filter['is_change'];
// 	    	if ($filter['is_change']>0){
//     			$sql_main .= " AND rl.is_change = 1 ";
// 	    	}else{
//     			$sql_main .= " AND rl.is_change <=0 ";
// 	    	}
    	}
    	//是否允许转单
    	if (isset($filter['status']) && $filter['status'] != '' && $filter['status'] != 'all') {
    		$sql_main .= " AND rl.status = :status ";
    		$sql_values[':status'] = $filter['status'];
    	}
    	//交易类型
    	if (isset($filter['type']) && $filter['type'] != '' ) {
    		$sql_main .= " AND rl.type = :type ";
    		$sql_values[':type'] = $filter['type'];
    	}
    	//下单时间
    	if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
    		$sql_main .= " AND (rl.order_first_insert_time >= :record_time_start )";
    		$sql_values[':record_time_start'] = $filter['record_time_start'];
    	}
    	if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
    		$sql_main .= " AND (rl.order_first_insert_time <= :record_time_end )";
    		$sql_values[':record_time_end'] = $filter['record_time_end'];
    	}

    	//支付时间
    	if (isset($filter['pay_time_start']) && $filter['pay_time_start'] != '') {
    		$sql_main .= " AND (rl.pay_time >= :pay_time_start )";
    		$sql_values[':pay_time_start'] = $filter['pay_time_start'];
    	}
    	if (isset($filter['pay_time_end']) && $filter['pay_time_end'] != '') {
    		$sql_main .= " AND (rl.pay_time <= :pay_time_end )";
    		$sql_values[':pay_time_end'] = $filter['pay_time_end'];
    	}
        //包含代销商品
        if(isset($filter['is_daixiao']) && $filter['is_daixiao'] != ''){
            $sql_main .= " AND (rl.is_daixiao = :is_daixiao )";
            $sql_values[':is_daixiao'] = $filter['is_daixiao'];
        }
        $sql_mx = "";
        $sql_mx_values = array();
    	if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
    		$sql_mx .= " AND goods_code LIKE :goods_code ";
    		$sql_mx_values[':goods_code'] = $filter['goods_code'].'%';
    	}
    	if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
    		$sql_mx .= " AND goods_barcode LIKE :goods_barcode ";
    		$sql_mx_values[':goods_barcode'] = $filter['goods_barcode'].'%';
    	}
        if(!empty($sql_mx)){
            $sql_mx = "select tid from api_order_detail where 1 ".$sql_mx;
            $tid_arr = ctx()->db->get_all_col($sql_mx,$sql_mx_values);
            if(empty($tid_arr)){
    		    $sql_main .= " AND 1 != 1";
            }else{
                $tid_list = $this->arr_to_in_sql_value($tid_arr, 'tid', $sql_values);
             //   $tid_list = join(',',$tid_arr);
    		    $sql_main .= " AND rl.tid in ({$tid_list}) ";
            }
        }


    	//买家昵称
    	if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {
            $buyer_nick_data = load_model('sys/security/CustomersSecurityModel')->get_encrypt_value_all($filter['buyer_nick']);
            if (!empty($buyer_nick_data)) {
                $buyer_nick_str = "'" . implode("','", $buyer_nick_data) . "'";
                $sql_main .= " AND ( rl.buyer_nick LIKE :buyer_nick OR rl.buyer_nick in ({$buyer_nick_str}) ) ";
                $sql_values[':buyer_nick'] = $filter['buyer_nick'] . '%';
            } else {
                $sql_main .= " AND rl.buyer_nick LIKE :buyer_nick ";
                $sql_values[':buyer_nick'] = $filter['buyer_nick'] . '%';
            }
        }

        if (isset($filter['seller_flag']) && $filter['seller_flag'] !== '') {
            $_seller_flag = array_map("intval", explode(',', $filter['seller_flag']));
            $sell_flag_list = $this->arr_to_in_sql_value($_seller_flag,'seller_flag',$sql_values);
            $sql_main .= " AND rl.seller_flag in({$sell_flag_list})";
        }

    	//销售平台
    	if (isset($filter['source']) && !empty($filter['source']) && $filter['source'] != 'select_all') {
    		$sql_main .= " AND rl.source = :sale_channel_code ";
    		$sql_values[':sale_channel_code'] = $filter['source'];
    	}
		/*
    	//店铺
    	if (isset($filter['shop_code']) && $filter['shop_code'] <> '' ) {
    		$arr = explode(',',$filter['shop_code']);
    		$str = "'".join("','",$arr)."'";
    		$sql_main .= " AND rl.shop_code in ({$str}) ";
    	}
    	*/
       //买家留言
    	if (isset($filter['buyer_remark']) && $filter['buyer_remark'] != '') {
    		$sql_main .= " AND rl.buyer_remark LIKE :buyer_remark ";
    		$sql_values[':buyer_remark'] = '%'.trim($filter['buyer_remark']).'%';
    	}
       //卖家留言
    	if (isset($filter['seller_remark']) && $filter['seller_remark'] != '') {

    		$sql_main .= " AND rl.seller_remark LIKE :seller_remark ";
    		$sql_values[':seller_remark'] = '%'.trim($filter['seller_remark']).'%';
    	}
    	//订单标签
    	$sql_tag = "";
    	$sql_tag_values = array();
    	if (isset($filter['tag']) && $filter['tag'] != '') {
    		$tag_filter = explode('/', $filter['tag']);
    		$sql_tag .= " AND source='{$tag_filter[0]}'";
    		$sql_tag .= " AND tag_name = :tag_name ";
    		$sql_tag_values[':tag_name'] = $tag_filter[1];
    	}
    	if(!empty($sql_tag)){
    		$sql_tag = "select tid from api_order_tag where 1 ".$sql_tag;
    		$tid_arr = ctx()->db->get_all_col($sql_tag,$sql_tag_values);
    		if(empty($tid_arr)){
    			$sql_main .= " AND 1=2 ";
    		}else{
    			$tid_list = "'".implode("','", $tid_arr)."'";
    			$sql_main .= " AND rl.tid in ({$tid_list}) ";
    		}
    	}
    	//交易号(唯一条件放最后 交易号不为空 其他条件均无效)
    	if (isset($filter['tid']) && trim($filter['tid']) != '') {
    		$sql_values = array();
    		$sql_main = " AND rl.tid LIKE :tid ";
    		$sql_values[':tid'] = trim($filter['tid']).'%';
    	}
       //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        //导出
        if ($filter['ctl_type'] == 'export') {
        	return $this->api_order_export_csv($sql_main, $sql_values, $filter);
        }
        $sql_main = $sql.$sql_main;
		$sql_main .= " order by rl.order_first_insert_time DESC";
    	$select = 'rl.*';
    	//echo $sql_main;
    	$data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);

    	//$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	filter_fk_name($data['data'], array('shop_code|shop', ));
    	//print_r($data);
    	$ret_status = OP_SUCCESS;
    	$ret_data = $data;
		$key_tid_relation = array();
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        foreach ($ret_data['data'] as $k => &$sub_data) {
            //print_r($sub_data);
            //系统抓单、变更时间
            if (empty($sub_data['last_update_time'])) {
                $sub_data['last_update_time'] = $sub_data['first_insert_time'];
            }
            if ($sub_data['pay_type'] == 1) {
                $ret_data['data'][$k]['pay_time'] = '';
            }
            $sub_data['tag_name'] = '';
            $key_tid_relation[$sub_data['tid']] = $k;
           //主单商品数量为0 或者小于0时，通过明细获取商品总数量
            if ($sub_data['num'] < 0 || $sub_data['num'] == 0) {
                $sub_data['num'] = $this->get_order_num($sub_data);
            }
            if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
                $sub_data['receiver_name'] = $this->name_hidden($sub_data['receiver_name']);
                $sub_data['buyer_nick'] = $this->name_hidden($sub_data['buyer_nick']);
            }
            $sql_sub = "select sale_channel_name from base_sale_channel where sale_channel_code = :sale_channel_code";
            $sub_data['source_name'] = $this->db->get_value($sql_sub,array(':sale_channel_code'=>$sub_data['source']));
            $sub_data['youfei'] = $sub_data['express_money'] + $sub_data['delivery_money'];
            }
        //查询订单标签
    	$tid_arr = array_keys($key_tid_relation);
    	$tid_str = "'".implode("','", $tid_arr)."'";
		$sql = "select tag_name,tid from api_order_tag where tid in($tid_str)";
		$tag_ret = $this->db->get_all($sql);
		$trade_tag = array();
		$tag_namss = load_model('api/OrderTagModel')->tag_names;
		foreach ($tag_ret as $tag_row) {
			$tag_name = $tag_row['tag_name'];
			if (isset($tag_namss[$tag_row['tag_name']])){
				$tag_name = $tag_namss[$tag_row['tag_name']];
			}
			if (empty($ret_data['data'][$key_tid_relation[$tag_row['tid']]]['tag_name'])){
				$ret_data['data'][$key_tid_relation[$tag_row['tid']]]['tag_name'].=$tag_name;
			} else {
				$ret_data['data'][$key_tid_relation[$tag_row['tid']]]['tag_name'].=','.$tag_name;
			}

		}

        //平台交易订单列表，商家备注列显示淘宝旗帜标识
        foreach ($ret_data['data'] as $k => &$sub_data){
            if($sub_data['seller_flag']>0&&$sub_data['source']=='taobao'){
                $sub_data['seller_remark'].=' <img src="assets/img/taobao/op_memo_'.$sub_data['seller_flag'].'.png">';
            }
        }
         //御城河日志
        load_model('common/TBlLogModel')->set_log_multi($ret_data['data'],'search');
    	return $this->format_ret($ret_status, $ret_data);
    	/*
        $sql_values = array();
    	$select = 'tl.*';
    	$sql_main = "FROM api_order tl WHERE 1 ";
    	$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
    	return $this->format_ret(1, $data);
    	*/
    }
    //导出
    function api_order_export_csv($sql_main,$sql_values,$filter){
    	$sql = "select rl.source,rl.shop_code,rl.tid,rl.seller_nick,rl.buyer_nick,rl.receiver_name,rl.receiver_address,rl.receiver_mobile,rl.buyer_remark,rl.seller_remark,rl.order_money,rl.order_first_insert_time,rl.pay_time,rl.status,rl.is_change,rl.change_remark,rl.sell_record_code,rl.num as order_num,
    	r2.title,r2.price,r2.num as detail_num,r2.price,r2.goods_code,r2.goods_barcode,r2.total_fee,r2.avg_money,rl.express_money,rl.delivery_money

    	FROM {$this->table} rl LEFT JOIN api_order_detail r2 on rl.tid = r2.tid WHERE 1";
    	$sql .= $sql_main." order by rl.order_first_insert_time desc";
    	$data = $this->db->get_all($sql,$sql_values);
        filter_fk_name($data, array('shop_code|shop', ));
        foreach ($data as &$row) {
            $row['is_change'] = $row['is_change'] == 1 ? '是' : '否';
            $row['status'] = $row['status'] == 1 ? '是' : '否';
            if ($row['order_num'] < 0 || $row['order_num'] == 0) {
                $row['num'] = $row['order_num'];
                $row['order_num'] = $this->get_order_num($row);
            }
            $sql_sub = "select sale_channel_name from base_sale_channel where sale_channel_code = :sale_channel_code";
            $row['source_name'] = $this->db->get_value($sql_sub,array(':sale_channel_code'=>$row['source']));
            $row['youfei'] = $row['express_money'] + $row['delivery_money'];
        }
        $ret['data'] = $data;
        return $this->format_ret(1, $ret);
    }

    /**
     * @todo 当主单商品数量为0时，获取订单详情商品数量
     */
    function get_order_num($sub_order){
        if($sub_order['source'] =='taobao'){
            return $this->get_detail_sum_num($sub_order['tid'], $sub_order['num']);
        }else{
            return 0;
        }
    }

    /**
     * @todo 获取订单详情商品数量，若主单数量和明细数量之和不相等，更新主单数量
     */
    function get_detail_sum_num($tid, $pre_num){
        $sql = "SELECT SUM(num) FROM api_order_detail WHERE tid = :tid AND status = 1 AND source = 'taobao'";
        $sql_value = array(":tid" => $tid);
        $ret = $this->db->get_value($sql, $sql_value);
        if($pre_num != $ret){
            $this->db->update($this->table, array('num' => $ret), array('tid' => $tid));
        }
        return $ret;
    }


    //根据id查询
    function get_by_id($id) {
    	return  $this->get_row(array('id'=>$id));
    }
    /*
     * 修改纪录
    */
    function update($data, $id) {
    	$ret = parent::update($data, array('id'=>$id));
    	return $ret;
    }
	    /*
     * 修改纪录
    */
    function td_traned($ids, $is_change = 1) {
        //array('is_change'=>1), $request['id']
        $change_remark = $is_change == 1 ? "批量置为已转单" : "批量置为未转单";
        $data = array('is_change' => $is_change, 'change_remark' => $change_remark);
        $where = " id in({$ids})";
        $ret = parent::update($data, $where);
        if ($ret == true) {
            $sql = "SELECT * FROM api_order where id in({$ids})";
            $result = $this->db->get_all($sql);
            $tid_arr = array();
            foreach ($result as $value) {
                $tid_arr[] = $value['tid'];
            }
            $tid_group = array_chunk($tid_arr, 10, true);
            foreach ($tid_group as $tid) {
                $tid_log = implode(',', $tid);
                $log = array(
                    'user_id' => CTX()->get_session('user_id'),
                    'user_code' => CTX()->get_session('user_code'),
                    'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
                    'module' => '网络订单',
                    'yw_code' => $tid_arr[0],
                    'operate_type' => '修改',
                    'operate_xq' => $tid_log . $change_remark
                );
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        return $ret;
    }

    //判断是否更新
    public function td_save_goods_check($request){

        $check=FALSE;
        foreach($request['barcode'] as $k=>$v){
             $sql1="select sku_id from api_order_detail where detail_id='{$k}'";
             $sku=$this->db->get_row($sql1);
             if(empty($sku['sku_id'])){
                 continue;
             }
             $detail=array();
             $detail[':sku_id']=$sku['sku_id'];
             $detail[':goods_barcode']=$v;
             $detail[':detail_id']=$k;
             $detail[':shop_code']=$request['shop_code'];
             $sql2="select r2.tid from api_order as r1 left join api_order_detail as r2 on r1.tid=r2.tid where r1.is_change in (0,-1) AND r1.shop_code = :shop_code AND r1.status =1 AND r2.sku_id = :sku_id AND r2.goods_barcode <> :goods_barcode AND r2.detail_id <> :detail_id;";
             $tid=$this->db->get_all($sql2,$detail);
             if(!empty($tid)){
                 $check=TRUE;
                 break;
             }
        }

        if($check==TRUE){
            return $this->format_ret(1);
        }else{
            return $this->format_ret(-1);
        }

    }


  /***
   * 获取需要更新的条码数组
   */
    public function get_update_barcode_log($request){
       $update_barcode = array();
       $barcode_info=array();
        foreach ($request['barcode'] as $k => $v) {
            $sql1 = "select sku_id from api_order_detail where detail_id='{$k}'";
            $sku = $this->db->get_row($sql1);
            $detail = array();
            $detail[':sku_id'] = $sku['sku_id'];
            $detail[':goods_barcode'] = $v;
            $detail[':detail_id'] = $k;
            $detail[':shop_code'] = $request['shop_code'];
            $sql2 = "select r2.goods_barcode,r2.tid from api_order as r1 left join api_order_detail as r2 on r1.tid=r2.tid where r1.is_change in (0,-1) AND r1.shop_code = :shop_code AND r1.status =1 AND r2.sku_id = :sku_id AND r2.goods_barcode <> :goods_barcode AND r2.detail_id <> :detail_id;";
            $goods_barcode = $this->db->get_all($sql2, $detail);
            if (!empty($goods_barcode)) {
                $update_barcode[$k]['old_barcode'] = $goods_barcode[0]['goods_barcode'];
                $update_barcode[$k]['new_barcode'] = $v;
                $tid=array();
                foreach($goods_barcode as $value){
                    $tid[]=$value['tid'];
                }
                $tid=array_unique($tid);
                $update_barcode[$k]['tid']=  implode(',', $tid);
            }
        }
        return $update_barcode;
    }

    /**
     * @param type $request
     * @return type     /更新未转单的商品的商家规格编码
     */
    public function update_barcode($request){

            $barcode=$request['barcode'];
            foreach ($barcode as $detail_id=>$goods_barcode)
            {
              $sql1="select sku_id from api_order_detail where detail_id='{$detail_id}'";
              $sku_id=$this->db->get_value($sql1);
              if(empty($sku_id)){
                  continue;
              }
                $sql = "UPDATE api_order , api_order_detail
                    SET api_order_detail.goods_barcode = :barcode
                    WHERE api_order.tid = api_order_detail.tid
                    AND api_order.is_change in (0,-1) AND api_order.shop_code = :shop_code
                    AND api_order.`status` = 1 AND api_order_detail.sku_id = :sku_id";

                $this->db->query($sql,array(':barcode'=>$goods_barcode,':shop_code'=>$request['shop_code'],':sku_id'=>$sku_id));

            }
            return $this->format_ret(1);

     }




     //更新未转单的商品的商家规格编码
    public function barcode_update($request){

        $sql = "select api_order_detail.tid,api_order_detail.sku_id,api_goods_sku.goods_barcode from api_order_detail,api_goods_sku
                WHERE api_order_detail.tid = :tid AND api_order_detail.sku_id = api_goods_sku.sku_id
                AND api_order_detail.goods_barcode <> api_goods_sku.goods_barcode";
        $sku_data = $this->db->get_all($sql,array(':tid'=>$request['tid']));

        if(!empty($sku_data)){
           // $success_count = 0;

            foreach ($sku_data as $sku)
            {
                if(empty($sku['goods_barcode'])||empty($sku['sku_id']))
                {
                    continue;
                }

                $sql = "UPDATE api_order , api_order_detail
                    SET api_order_detail.goods_barcode = :barcode
                    WHERE api_order.tid = api_order_detail.tid
                    AND api_order.is_change in (0,-1) AND api_order.shop_code = :shop_code
                    AND api_order.`status` = 1 AND api_order_detail.sku_id = :sku_id";

                $this->db->query($sql,array(':barcode'=>$sku['goods_barcode'],':shop_code'=>$request['shop_code'],':sku_id'=>$sku['sku_id']));

         //      $success_count++;

            }
            return $this->format_ret(1);
            //return array('status' => -1,'data' => $success_count,'message' =>'更新完成，成功更新'.$success_count.'单');
        }
            return $this->format_ret(1);
        //return array('status' => -1,'data' => $success_count,'message' => '系统未发现异常，必须未转单，且规格编码不一致才允许更新！');
    }

    /**
     * 查询数据是否存在
     * @param string $field 字段名
     * @param string $value 字段值
     * @return int 数据条数
     */
    function is_exists($field, $value) {
        $sql = "SELECT COUNT(1) FROM {$this->table} WHERE {$field}=:{$field}";
        return $this->db->get_value($sql, array(":{$field}" => $value));
    }

    /**
     * 外部接口新增订单到api_order->系统转成订单
     * @param type $record 主单据
     * @param type $detail 商品
     *         主单据:     array(
     *                  必填: 'shop_code', 'receiver_province', 'receiver_city', 'receiver_district', 'receiver_addr', 'receiver_name','pay_code','deal_code','buyer_name','record_time','goods_num','order_money',''
     *                  选填: 'receiver_mobile', 'receiver_phone', 'pay_code', 'buyer_remark', 'seller_remark', 'pay_time', 'express_money', 'invoice_title', 'invoice_content'
     *         )
     *         商品:  array(
     *          		必填: 'sku', 'num', 'avg_money',
     *           		选填: 'is_gift'
     *         )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    function api_add_order($data) {
        $record = $data;
        $detail = $data['detail'];
        $ret = $this->check_params_null($record, $detail);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $info = $ret['data'];
        $ret_exists = $this->is_exists('tid', $info['record']['deal_code']);
        if ($ret_exists > 0) {
            return $this->format_ret(2, $info['record']['deal_code'], '单据已存在');
        }
         $sql = "select sale_channel_code,shop_code from base_shop where shop_code=:shop_code ";
        $shop_info = $this->db->get_row($sql,array(':shop_code'=>$data['shop_code']));
        if (empty($shop_info)) {
            return $this->format_ret(-1, '', '找不到对应店铺');
        }
        $source = $shop_info['sale_channel_code'];

        
        $this->db->begin_trans();
        try {
            $record = $info['record'];
            //单据主信息
            $first_insert_time = strtotime($record['record_time']);
            $record_info = array(
                'shop_code' => $record['shop_code'],
                'status' => $record['status'],
                'receiver_country' => $record['receiver_country'],
                'receiver_province' => $record['receiver_province'],
                'receiver_city' => $record['receiver_city'],
                'receiver_district' => isset($record['receiver_district']) ? $record['receiver_district'] : '',
                'receiver_street' => isset($record['receiver_street']) ? $record['receiver_street'] : '',
                'receiver_addr' => $record['receiver_addr'],
                'receiver_address' => $record['receiver_country'] . $record['receiver_province'] . $record['receiver_city'] . $record['receiver_district'] . $record['receiver_street'] . $record['receiver_addr'],
                'receiver_name' => $record['receiver_name'],
                'pay_type' => $record['pay_type'] == 0 ? 0 : 1,
                'tid' => $record['deal_code'],
                'buyer_nick' => $record['buyer_name'],
                'order_first_insert_time' => $record['record_time'],
                'order_first_insert_time_int' => $first_insert_time ? $first_insert_time : 0,
                'num' => isset($record['goods_num']) ? $record['goods_num'] : 0,
                'receiver_mobile' => $record['receiver_mobile'],
                'receiver_phone' => $record['receiver_phone'],
                'buyer_remark' => $record['buyer_remark'],
                'seller_nick'=>$record['seller_nick'],
                'seller_remark' => $record['seller_remark'],
                'seller_flag' => isset($record['seller_flag']) ? $record['seller_flag'] : 0,
                'receiver_mobile' => $record['receiver_mobile'],
                'pay_time' => isset($record['pay_time']) ? $record['pay_time'] : '0000-00-00 00:00:00',
                'express_money' => isset($record['express_money']) ? $record['express_money'] : 0,
                'invoice_title' => isset($record['invoice_title']) ? $record['invoice_title'] : "",
                'invoice_content' => isset($record['invoice_content']) ? $record['invoice_content'] : "",
                'order_money' => isset($record['order_money']) ? $record['order_money'] : 0,
                'buyer_money' => isset($record['buyer_money']) ? $record['buyer_money'] : 0,
                'pay_code' => isset($record['pay_code']) ? $record['pay_code'] : '',
                'alipay_no' => isset($record['alipay_no']) ? $record['alipay_no'] : '',
                'source' => $source,
            );
            
            $ret_detail = $this->payment_ft(($record_info['order_money'] - $record_info['express_money']), $info['detail']);
            if ($ret_detail['status'] < 1) {
                $this->rollback();
                return $ret_detail;
            }
            $detail = &$ret_detail['data'];
            
            $detail_info = array();
            foreach ($detail as $d) {
                $is_gift = empty($d['is_gift']) ? 0 : $d['is_gift'];
                $detail_info[] = array(
                    'source' => $source,
                    'goods_barcode' => $d['sku'],
                    'num' => $d['num'],
                    'avg_money' => $d['avg_money'],
                    'tid' => $record_info['tid'],
                    'payment' => $d['payment'],
                    'total_fee' => $d['payment'],
                    'price' => isset($d['price']) ? $d['price'] : 0,
                    'goods_barcode' => $d['sku'],
                    'oid' => $record_info['tid'] . $d['sku'] . $is_gift,
                    'is_gift' => $is_gift
                );
            }
             $ret_encrypt = load_model('sys/security/SysEncrypModel')->get_shop_encrypt($record['shop_code']);
            if (!empty($ret_encrypt)) {
                $encrypt_key = array('buyer_name', 'receiver_mobile', 'receiver_phone', 'receiver_addr', 'receiver_name');
                foreach ($encrypt_key as $_key) {
                    if (!empty($record[$_key])) {
                        $type = $_key;
                        if($type == 'buyer_name') $key = 'buyer_nick';
                        else $key = $_key;
                        $record_info[$key] = load_model('sys/security/CustomersSecurityModel')->encrypt_shop_value($record[$_key], $type, $record['shop_code']);
                    }
                }
            }

            $ret = $this->insert($record_info);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
            }
            $ret_detail_info = load_model('oms/ApiOrderDetailModel')->insert_multi($detail_info, true);

            if ($ret_detail_info['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1, '', '新增订单成功');
    }

    function check_params_null($record, $detail) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('shop_code', 'receiver_province', 'receiver_city', 'receiver_addr', 'receiver_name', 'deal_code', 'buyer_name', 'record_time', 'order_money', 'receiver_mobile'),
            'i' => array('goods_num', 'status', 'pay_type')
        );
        //可选字段
        $key_option = array(
            's' => array('receiver_country','receiver_district','receiver_street', 'receiver_phone', 'buyer_remark', 'seller_remark', 'pay_time', 'express_money', 'invoice_title', 'invoice_content', 'buyer_money', 'seller_flag', 'pay_code', 'alipay_no','seller_nick')
        );
        $detail_required = array(
            's' => array('sku', 'payment'),
            'i' => array('num')
        );
        $detail_option = array(
            'i' => array('price','is_gift'),
        );
        $brand_required = array();
        $brand_option = array();
        $d_required = array();
        $d_option = array();
        $new_detail = array();
        //验证必选字段是否为空并提取必选字段数据

        $ret_required = valid_assign_array($record, $key_required, $brand_required, TRUE);
        if (TRUE == $ret_required['status']) {
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($record, $key_option, $brand_option);
            $brand = array_merge($brand_required, $brand_option);
            unset($brand_required, $brand_option, $record);

            if ($brand['pay_type'] == 0 && empty($brand['pay_time'])) {
                return $this->format_ret(-10001, array('pay_time'), 'API_RETURN_MESSAGE_10001');
            }
            $detail = json_decode($detail, true);
            if(empty($detail)){
                return $this->format_ret(-10005,'','参数解析错误');
            }
            $exists_sku = array();
            foreach ($detail as $d) {
                $ret_d_required = valid_assign_array($d, $detail_required, $d_required, TRUE);
                if ($ret_d_required['status'] != TRUE) {
                    return $this->format_ret(-10001, $ret_d_required['req_empty'], 'API_RETURN_MESSAGE_10001');
                }
                $ret_d_option = valid_assign_array($d, $detail_option, $d_option);
                $temp = array_merge($d_required, $d_option);

                $is_gift = empty($temp['is_gift']) ? 0 : $temp['is_gift'];
                $k = $temp['sku'] . '_' . $is_gift;
                if (in_array($k, $exists_sku)) {
                    return $this->format_ret(-1, array('sku' => $temp['sku']), '存在重复条码');
                }
                $exists_sku[] = $k;
                $new_detail[] = $temp;
            }

            $data = array();
            $data['record'] = $brand;
            $data['detail'] = $new_detail;
            return $this->format_ret(1, $data);
        } else {
            return $this->format_ret(-10001, $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
    }

    //计算均摊金额
    function payment_ft($trade_ft_money,$goods) {
    	$items_count = count($goods);
    	if ($items_count < 1) {
    		return array('status' => -1,'data' => $goods,'message' => '均摊缺少明细数据');
    	}
    	$total_ft_ed = 0;//已经分摊掉的金额
    	$total_goods_payment = 0;//所有商品明细的总额
    	foreach($goods as $k => $sub_goods) {
    		$total_goods_payment += $sub_goods['payment'];
    	}
    	foreach($goods as $k => $data){
    		if ($items_count != 1) {
    			$cur_ft = $trade_ft_money * ($data['payment'] / $total_goods_payment);
    			$cur_ft = floor($cur_ft * 100) / 100;
    			$total_ft_ed = bcadd($total_ft_ed,$cur_ft,2);
    			$items_count--;
    		} else {
    			$cur_ft = bcsub($trade_ft_money , $total_ft_ed,2);
    			$total_ft_ed = bcadd($total_ft_ed,$cur_ft,2);
    		}
    		$goods[$k]['avg_money'] = $cur_ft;
    	}
    	if (bccomp($total_ft_ed,$trade_ft_money,2) != 0){
    		return array('status' => -1,'data' => '','message' => '均摊的数据总和验证失败');
    	}
    	return  array('status' => 1,'data' => $goods);
    }

    function get_order_send_status($data){
    	$shop_code = $data['shop_code'];
    	$sell_record_code = $data['sell_record_code'];
    	$tid = $data['tid'];
    	if (empty($tid) || empty($shop_code)){
    		return $this->format_ret('-10001','','店铺，交易号不能为空');
    	}
    	$sql = "select sell_record_code,express_code,express_no,send_time from api_order where shop_code='{$shop_code}' and tid='{$tid}'";
    	$order_info = CTX()->db->get_row($sql);
    	if (empty($order_info)){
    		$status = 0;
    		$message = '订单信息不存在';
    		$data = '';
    	} else {
    		$sql = "select sell_record_code,express_code,express_no,send_time from api_order_send where shop_code='{$shop_code}' and tid='{$tid}'";
    		$send_info = CTX()->db->get_row($sql);
    		$message= '';
    		if (empty($send_info)){
    			$message = '订单未发货';
    			$data = '';
    		} else {
    			$express_name = oms_tb_val("base_express", 'express_name', array('express_code' => $send_info['express_code']));
    			$message = '订单已发货';
    			$data = array('express_name'=>$express_name,'express_no'=>$send_info['express_no'],'send_time'=>$send_info['send_time']);
    		}
    		$status = 1;
    	}

    	return $this->format_ret($status,$data,$message);
    }
    /*
     * 修改平台订单详情成功后,添加系统操作日志
     * */
    function add_operate_log($detail_list,$barcode_arr){
        $tid=$detail_list[0]['tid'];
        $new_barcode_arr = array_values($barcode_arr);
        $log_xq = '';
        foreach ($detail_list as $key =>$value){
            if($value['goods_barcode'] != $new_barcode_arr[$key]){
                $log_xq .= "商品条形码：{$value['goods_barcode']}变更为{$new_barcode_arr[$key]} ";
            }
        }
        //当商品条形码被修改时,添加操作日志
       // $barcode_str = implode(',', $barcode_arr);
        $log = array('user_id'=>CTX()->get_session('user_id'),
                  'user_code'=>CTX()->get_session('user_code'),
                  'ip'=>'','add_time'=>date('Y-m-d H:i:s'),
                  'module'=>'网络订单',
                  'yw_code'=>$tid,
                  'operate_type'=>'修改',
                  'operate_xq'=>$log_xq);
        $ret = load_model('sys/OperateLogModel')->insert($log);
        return $ret;
    }

     /*
     * 修改平台订单详情成功后,更新其他订单,添加系统操作日志
     * */
    function add_update_operate_log($detail_list,$barcode_arr){
        $tid=$detail_list[0]['tid'];
        $ret='';
        foreach ($barcode_arr as $key =>$value){
            $log_xq = '交易号：'.$value['tid'].'；';
            if($value['old_barcode'] != $value['new_barcode']){
                $log_xq .= "商品条形码：{$value['old_barcode']}变更为{$value['new_barcode']} ";
            }
         //当商品条形码被更新时,添加操作日志
        $log = array('user_id'=>CTX()->get_session('user_id'),
                  'user_code'=>CTX()->get_session('user_code'),
                  'ip'=>'','add_time'=>date('Y-m-d H:i:s'),
                  'module'=>'网络订单',
                  'yw_code'=>$tid,
                  'operate_type'=>'修改',
                  'operate_xq'=>$log_xq);
           $ret = load_model('sys/OperateLogModel')->insert($log);
        }
        return $ret;
    }


     /**
     * @交易下载
     */
    function down_trade($request) {
        $params=array();
        $params['sale_channel_code']=$request['sale_channel_code'];
        $params['shop_code']=$request['shop_code'];
        $params['start_time']=$request['start_time'];
        $params['end_time']=$request['end_time'];
        $params['method']='trade_sync';
        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);

        return $result;

    }

    //下载进度
    function down_trade_check($request){
        $params=array();
        $params['task_sn']=$request['task_sn'];
        $result = load_model('sys/EfastApiTaskModel')->request_api('check', $params);

        return $result;

    }

    /**
     * 双十一图中获取交易总笔数
     * @param string $where_time
     * @param string $shop_code
     * @return array ['total_order' => '交易总笔数', 'total_money' => '交易总金额']
     */
    public function getTotalOrderNum($where_time = '', $shop_code = '')
    {
        // 状态：可转单，是否转单：已转单，时间：time，商定代码：shop_code
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";

        $sql1 = "SELECT COUNT(*) AS `total_order`, SUM(`order_money`) AS `total_money` "
              . "FROM `{$this->table}` WHERE `pay_time` {$where_time} "
              . "AND `pay_type` = '0' AND `status` = '1' {$shop_sql}";
        $row[] = $this->db->getRow($sql1);

        $sql2 = "SELECT COUNT(*) AS `total_order`, SUM(`order_money`) AS `total_money` "
              . "FROM `{$this->table}` WHERE `order_first_insert_time` {$where_time} "
              . "AND `pay_type` = '1' and `status` = '1' {$shop_sql}";
        $row[] = $this->db->getRow($sql2);

        $sql3 = "SELECT COUNT(1)  AS `total_order`, SUM(`distributor_payment`) AS `total_money` "
              . "FROM `api_taobao_fx_trade` WHERE `pay_time` {$where_time} "
              . " AND `is_invo` = '1' {$shop_sql}";
        $row[] = $this->db->getRow($sql3);

        return array(
            'total_order' => array_sum(array_column($row, 'total_order')),
            'total_money' => array_sum(array_column($row, 'total_money'))
        );
    }

    /**
     * 双十一图中转入的订单数
     * @param string $where_time
     * @param string $shop_code
     * @return type ['change_done' => '已转入的交易笔数', 'change_todo' => '未转入的交易笔数']
     */
    public function getChangeOrderNum($where_time = '', $shop_code = '')
    {
        $shop_sql = $shop_code == '0' ? '' : "AND `shop_code` = '{$shop_code}'";
        /** 已转入订单 */
        $sql1 = "SELECT COUNT(*) FROM `{$this->table}` "
              . "WHERE `status` = '1' AND `pay_type` = '0' AND `is_change` = '1' "
              . "AND `pay_time` {$where_time} {$shop_sql}";
        $transform_num1 = ctx()->db->getOne($sql1);

        $sql2 = "SELECT COUNT(*) FROM `{$this->table}` "
              . "WHERE `status` = '1' AND `pay_type` = '1' AND `status` = '1' "
              . "AND `is_change` = '1' AND `order_first_insert_time` {$where_time} {$shop_sql}";
        $transform_num2 = ctx()->db->getOne($sql2);

        $sql3 = "SELECT COUNT(1) FROM `api_taobao_fx_trade` "
              . "WHERE `is_invo` = '1' AND `is_change` = '1' "
              . "AND `pay_time` {$where_time} {$shop_sql}";
        $transform_num3 = ctx()->db->getOne($sql3);

        /** 未转入订单 */
        $sql4 = "SELECT COUNT(*) AS `change_todo` "
              . "FROM `{$this->table}` "
              . "WHERE `status` = '1' AND `is_change` IN ('-1', '0') {$shop_sql}";
        return array(
            'change_done' => $transform_num1 + $transform_num2 + $transform_num3,
            'change_todo' => ctx()->db->getOne($sql4)
        );
    }


     /*
     * 修改平台成功后,添加系统操作日志
     * */
    function add_order_operate_log($request, $order_info) {
        $log_xq = '';
        if ($request['receiver_name'] != $order_info['receiver_name']) {
            $log_xq .= "收货人：{$order_info['receiver_name']}变更为{$request['receiver_name']}; ";
        }
        if ($request['receiver_mobile'] != $order_info['receiver_mobile']) {
            $log_xq .= "手机号码：{$order_info['receiver_mobile']}变更为{$request['receiver_mobile']};";
        }
        if ($request['receiver_phone'] != $order_info['receiver_phone']) {
            $log_xq .= "固定电话：{$order_info['receiver_phone']}变更为{$request['receiver_phone']};";
        }
        if ($request['receiver_address'] != $order_info['receiver_address']) {
            $log_xq .= "收货地址：{$order_info['receiver_address']}变更为{$request['receiver_address']};";
        }
        if ($log_xq != '') {
            $log = array('user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
                'module' => '网络订单',
                'yw_code' => $request['tid'],
                'operate_type' => '修改',
                'operate_xq' => $log_xq);
            $ret = load_model('sys/OperateLogModel')->insert($log);
        }
    }
    
    /**
     * 米家推送订单接收数据方法
     */
    function api_add_mijia_order(){
        //获取POST的数据
        $request_data = $GLOBALS['HTTP_RAW_POST_DATA'];
        //加号可能会被转换成空格，进行urlencode
        if(strpos($request_data, '+') !== FALSE) {
            $request_data = str_replace('+', '%2B', $request_data);
        }
        //可能传的json
        $api_data = json_decode($request_data, true, 512, JSON_BIGINT_AS_STRING);
        if(empty($api_data)) {
            parse_str($request_data, $api_data);
        }
        $data = array('encodeData' => json_encode($api_data, true));
        $this->save_log($data);
        //校验传值
        $check = $this->check_params($api_data);
        if($check['status'] != 1) {
            return $check;
        }
        $shop_data = $this->db->get_all("SELECT base_shop_api.*,base_shop.shop_name FROM base_shop_api,base_shop WHERE base_shop_api.shop_code = base_shop.shop_code AND base_shop.authorize_state = 1 AND source='xiaomizhijia' ORDER BY base_shop_api.shop_api_id ASC");
        //判断店铺参数
        if (empty($shop_data)) {
            return $this->format_ret(-1, '', 'NO SHOP SELECTED');
        }
        $decData = array();
        $record = array();
        foreach ($shop_data as $params) {
            $api = json_decode($params['api'], true);
            //判断aes_key是否为空
            if(empty($api['aes_key']) || empty($api['key'])) {
                $this->err_msg = 'EMPTY aes_KEY OR EMPTY key';
                continue;
            }
            //验证签名
            if(!empty($api_data['sign'])) {
                $orgin_sign = $api_data['sign'];
                unset($api_data['sign']);
                $sign_data = urldecode(http_build_query($api_data) . $api['key']);
                if($orgin_sign != md5($sign_data)){
                    $this->err_msg = 'SIGN ERROR';
                    continue;
                }
            }
            //解密传输的数据
            require_lib('Util_Aes');
            $aes = new Util_Aes();
            $aes->set_key($api['aes_key']);
            $decData = $aes->decrypt($api_data['data']);
            //过滤空字符串 \u0000场景
            $sub = str_replace("\0", "", $decData);
            $record = json_decode($sub, true, 512, JSON_BIGINT_AS_STRING);
            if(empty($decData) || empty($record)) {
                $this->err_msg = 'API_DATA_IS_EMPTY';
                continue;
            } else {
                $shop_code = $params['shop_code'];
                $shop_name = $params['shop_name'];
                break;
            }
        }
        //判断数据是否为空
        if(empty($record) && $this->err_msg != '') {
            return $this->format_ret(-1, '', $this->err_msg);
        }
        $ret = $this->handle_mijia_api_order($record, $shop_code, $shop_name);
        if($ret['status'] == 1) {
            $data = array('encodeData' => json_encode($api_data, true), 'decodeData'=> json_encode($record, true));
            $this->update_log($data);
        }
        return $ret;
    }
    
    function handle_mijia_api_order($record, $shop_code, $shop_name) {
        //处理数据，写入api_order表和api_order_detail表
        try {
            $address_data = json_decode($record['address'], TRUE);
            $record_info = array(
                'shop_code' => $shop_code,
                'seller_nick' => $shop_name,
                'receiver_country' => $address_data['country']['name'],
                'receiver_province' => $address_data['province']['name'],
                'receiver_city' => $address_data['city']['name'],
                'receiver_district' => $address_data['district']['name'],
                'receiver_addr' => isset($address_data['area']['name']) ? $address_data['area']['name'] . $address_data['address'] : $address_data['address'],
                'receiver_name' => $address_data['consignee'],
                'pay_type' => 0,
                'tid' => $record['order_id'],
                'buyer_nick' => $address_data['consignee'],
                'order_first_insert_time' => date('Y-m-d H:i:s', $record['ctime']),
                'order_first_insert_time_int' => $record['ctime'],
                'num' => 0,
                'receiver_mobile' => $address_data['tel'],
                'receiver_phone' => $address_data['tel'],
                'buyer_remark' => !empty($record['description']) ? $record['description'] : $record['description'],
                'seller_remark' => '',
                'seller_flag' => 0,
                'pay_time' => date('Y-m-d H:i:s', $record['ftime']) ,
                'express_money' => !empty($record['ship_fee']) ? $record['ship_fee'] / 100 : 0,
                'invoice_title' => isset($record['invoice_title']) ? $record['invoice_title'] : "",
                'order_money' => isset($record['total_price']) ? $record['total_price'] / 100 : 0,
                'buyer_money' => isset($record['total_price']) ? $record['total_price'] / 100 : 0,
                'pay_code' => isset($record['pay_code']) ? $record['pay_code'] : '',
                'alipay_no' => isset($record['alipay_no']) ? $record['alipay_no'] : '',
                'source' => 'xiaomizhijia'
            );
			 $record_info['receiver_address'] = $record_info['receiver_province'] . $record_info['receiver_city'] . $record_info['receiver_district'] . $record_info['receiver_addr'];
            
            foreach ($record['products'] as $detail) {
                if($detail['refundtype'] != -1) {
                    return $this->handle_mijia_api_refund($record, $shop_code, $shop_name);
                }
                $goods_barcode = $this->get_goods_barcode($detail['pid'], $shop_code);
                $is_gift = !empty($detail['is_gift']) ? $detail['is_gift'] : 0;
                $detail_info[] = array(
                    'source' => 'xiaomizhijia',
                    'tid' => $record['order_id'],
                    'oid' => $record['order_id'] . '_' . $detail['pid'],
                    'sku_id' => $detail['pid'],
                    'goods_name' => $detail['name'],
                    'goods_code' => $detail['gid'],
                    'goods_barcode' => !empty($goods_barcode) ? $goods_barcode : $detail['pid'],
                    'num' => $detail['count'],
                    'payment' => $detail['price'],
                    'total_fee' => $detail['product_price'],
                    'price' => isset($detail['product_price']) ? $detail['product_price'] : 0,
                    'is_gift' => $is_gift
                );
                $record_info['num'] += $detail['count'];
                $record_info['status'] = $detail['status'] == 4 ? 1 : 0;
            }
            $this->begin_trans();
            $ret_detail = $this->payment_ft(($record_info['order_money'] - $record_info['express_money']), $detail_info);
            if ($ret_detail['status'] < 1) {
                $this->rollback();
                return $ret_detail;
            }
            $update_str = 'buyer_remark = VALUES(buyer_remark),seller_remark = VALUES(seller_remark),order_first_insert_time = VALUES(order_first_insert_time)';
            $ret = $this->insert_multi_duplicate('api_order', array($record_info), $update_str);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
            }
            $ret_detail_info = load_model('oms/ApiOrderDetailModel')->insert_multi($ret_detail['data'], true);
            if ($ret_detail_info['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(1, '', 'ADD ORDER SUCCESS');
    }
    
    /**
     * 处理退单业务
     */
    function handle_mijia_api_refund($refund, $shop_code, $shop_name) {
        $this->begin_trans();
        try {
            $address_data = json_decode($refund['address'], TRUE);
            $refunds = array();
            //$refunds['refund_express_code'] = '';//快递公司
            $refund_express_info = array();
            foreach ($refund['products'] as $api_sub_order) {
                if($api_sub_order['refundtype'] != -1){//售后类型【0-退款,1-退货退款,2-换货】 
                    $refunds['shop_code'] = $shop_code;
                    $refunds['seller_nick'] = $shop_name;
                    $refunds['source'] = 'xiaomizhijia';
                    $refunds['refund_id'] = $api_sub_order['refund_id'];
                    $refunds['tid'] = $api_sub_order['order_id'];
                    $refunds['oid'] = $api_sub_order['order_id'] . '_' . $api_sub_order['gid'];
                    $refunds['buyer_nick'] = $address_data['consignee'];
                    $refunds['status'] = in_array($api_sub_order['status'], array(16,17,19,39,6,50)) ? 1 : 0; //16 申请换货 17 申请退款
                    $refunds['order_last_update_time'] = date('Y-m-d H:i:s', $api_sub_order['rctime']);
                    $refunds['order_first_insert_time'] = date('Y-m-d H:i:s', $api_sub_order['rctime']);
                    $refunds['first_insert_time'] = date('Y-m-d H:i:s');
                    $refunds['has_good_return'] = $api_sub_order['refundtype'] == 0 ? 0 : 1; //0-退款,1-退货退款,2-换货
                    $refunds['refund_desc'] = $api_sub_order['refunddescription']; //退货原因
                    $detail['refund_id'] = $api_sub_order['refund_id'];
                    $detail['tid'] = $api_sub_order['order_id'];
                    $detail['oid'] = $api_sub_order['order_id'] . '_' . $api_sub_order['gid'];
                    $detail['sku_id'] = $api_sub_order['pid'];
                    $detail['num'] = $api_sub_order['count'];
                    $detail['goods_code'] = $api_sub_order['gid'];
                    $detail['title'] = $api_sub_order['name'];
                    $detail['goods_barcode'] = $api_sub_order['gid'];
                    $goods_barcode = $this->get_goods_barcode($detail['sku_id'], $shop_code);
                    if(!empty($goods_barcode)){
                        $detail['goods_barcode'] = $goods_barcode;
                    }
                    $detail['refund_price'] = $api_sub_order['refundprice']/100;
                    $ret_detail[] = $detail;
                    $refunds['refund_fee'] += $detail['refund_price'];

                    //快递单号
                    $refund_express_info[$api_sub_order['refund_id']]['refund_express_no'] = isset($api_sub_order['express_sn']) ? $api_sub_order['express_sn'] : '';
                    //快递公司
                    $refund_express_info[$api_sub_order['refund_id']]['refund_express_code'] = '';
                    if(!empty($api_sub_order['bizcode'])){
                        $express_name = Logistics_mapping_refund::get($api_sub_order['bizcode']);
                        if (!empty($express_name)) {
                            $sql = "select express_code from base_express where express_name=:express_name";
                            $sql_value[':express_name'] = $express_name;
                            $express_code = $this->db->get_value($sql, $sql_value);
                            $refund_express_info[$api_sub_order['refund_id']]['refund_express_code'] = !empty($express_code) ? $express_code : $api_sub_order['bizcode'];
                        } else {
                            $refund_express_info[$api_sub_order['refund_id']]['refund_express_code'] = $api_sub_order['bizcode'];
                        }
                    }

                }
            }
            $update_str = 'status = VALUES(status),last_update_time = VALUES(last_update_time),refund_express_code = VALUES(refund_express_code),refund_express_no = VALUES(refund_express_no),refund_desc= VALUES(refund_desc)';
            error_log('['.date('Y-m-d H:i:s')."]***:".var_export($refunds, true)."\r\n", 3, ROOT_PATH.'logs/mijia_push_log.txt');
            error_log('['.date('Y-m-d H:i:s')."]express:".var_export($refund_express_info, true)."\r\n", 3, ROOT_PATH.'logs/mijia_push_log.txt');
            $record_result = $this->insert_multi_duplicate('api_refund', array($refunds), $update_str);
            if ($record_result['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_REFUND_SAVE_ERROR');
            }

            if (!empty($refund_express_info)) {
                foreach ($refund_express_info as $refund_id => $express_data) {
                    $this->update_exp('api_refund', $express_data, array('refund_id' => $refund_id));
                }
            }

            //已转退单 更新售后服务单快递信息
            $sql = "SELECT is_change,refund_record_code,refund_express_no,refund_express_code FROM api_refund WHERE tid=:tid";
            $sql_values = array();
            $sql_values[':tid'] = $refunds['tid'];
            $refunds_order = $this->db->get_all($sql, $sql_values);
            foreach ($refunds_order as $api_refund){
                if ($api_refund['is_change'] == 1 && !empty($api_refund['refund_record_code']) && !empty($api_refund['refund_express_no'])) {
                    $update_params = array(
                        'return_express_code' => $api_refund['refund_express_code'],
                        'return_express_no' => $api_refund['refund_express_no'],
                    );
                    //售后服务单
                    $this->update_exp('oms_sell_return', $update_params, array('sell_return_code' => $api_refund['refund_record_code'], 'return_express_no' => ''));
                    //退货包裹单
                    $this->update_exp('oms_return_package', $update_params, array('sell_return_code' => $api_refund['refund_record_code'], 'return_express_no' => ''));
                }
            }

            //保存明细
            $detail_update_str = 'goods_barcode = VALUES(goods_barcode)';
            $detailResult = $this->insert_multi_duplicate('api_refund_detail', $ret_detail, $detail_update_str);
            if ($detailResult['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_REFUND_SAVE_DETAIL_ERROR');
            }
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        
        return $this->format_ret(1, '', 'ADD ORDER SUCCESS');
    }
    
    /**
     * 获取米家barcode,接口数据没有返回
     */
    function get_goods_barcode($sku_id,$shop_code){ 
        $sql = "SELECT goods_barcode FROM api_goods_sku WHERE sku_id=:sku_id AND source='xiaomizhijia' and shop_code=:shop_code";
        $goods_barcode = $this->db->get_value($sql, array(':sku_id' => $sku_id, ':shop_code' => $shop_code));
        return $goods_barcode;
    }
    
    /**
     * 保存日志
     */
    function save_log($request) {
        $key_id = md5($request['data']);
        $sql = "SELECT id FROM api_open_logs WHERE key_id=:key_id AND type='mijia_push'";
        $id = $this->db->get_value($sql, array(":key_id" => $key_id));
        if(!empty($id)) {
            $data['return_data'] = json_encode($request, true);
            $this->db->update('api_open_logs', $data, "key_id = '{$key_id }'");
            $this->log_id = $id;
            return;
        }
        $data = array();
        $data['key_id'] = md5($request['data']);
        $data['method'] = 'create_mijia_order';
        $data['type'] = 'mijia_push';
        $data['add_time'] = date('Y-m-d H:i:s');
        $data['post_data'] = array();
        $data['url'] = '';
        $data['return_data'] = json_encode($request, true);
        $this->db->insert('api_open_logs', $data);
        $this->log_id = $this->db->insert_id();
    }
    
   /**
     * 更新日志
     */
    function update_log($api_logs) {
        if ($this->log_id != 0) {
            $up_data = array('return_data' => json_encode($api_logs, true));
            $this->db->update('api_open_logs', $up_data, "id = '{$this->log_id }'");
        }
    }
    
    /**
     * 校验传值
     */
    function check_params($api_data) {
        $arr_key = array_keys($api_data);
        $err = '';
        foreach ($arr_key as $key){
            if(empty($api_data[$key])) {
                $err .= 'PARAM ' . $key . ' EMPTY';
            }
        }
        if(!empty($err)) {
            return $this->format_ret(-1, '', $err);
        }
        return $this->format_ret(1);
    }
}




/**
 * 物流公司映射
 */
class Logistics_mapping_refund {

    public static function get($property_name, $default_value = '') {
        $property_value = NULL;

        $rfc = new ReflectionClass('Logistics_mapping_refund');
        $rfc_ins = $rfc->newInstance();
        $properties = $rfc->getProperties();
        foreach ($properties as $p) {
            if ($property_name == $p->getName()) {
                $property_value = $p->getValue($rfc_ins);
                break;
            }
        }
        if ($property_value == NULL) {
            $property_value = $default_value;
        }
        return $property_value;
    }

    public static $zhongtong = '中通快递';
    public static $yunda = '韵达快递';
    public static $yuantong = '圆通速递';
    public static $yibangwuliu = '一邦快递';
    public static $yafengsudi = '亚风快递';
    public static $tiantian = '天天快递';
    public static $shentong = '申通快递';
    public static $rufengda = '如风达';
    public static $debangwuliu = '德邦物流';
    public static $shunfeng = '顺丰速运';
    public static $zhongtiekuaiyun = '中铁快运';
    public static $ems = '中国邮政EMS';
    public static $zhaijisong = '宅急送';
    public static $yuanchengwuliu = '远成物流';
    public static $xinfengwuliu = '信丰物流';
    public static $xinbangwuliu = '新邦物流';
    public static $wxwl = '万象物流';
    public static $tiandihuayu = '天地华宇';
    public static $shenghuiwuliu = '盛辉物流';
    public static $quanyikuaidi = '全一快递';
    public static $minghangkuaidi = '民航快递';
    public static $lianbangkuaidi = '联邦快递';
    public static $kuaijiesudi = '快捷速递';
    public static $quanfengkuaidi = '全峰快递';
    public static $jjwl = '佳吉快递';
    public static $jixianda = '急先达';
    public static $hengluwuliu = '恒路物流';
    public static $anxindakuaixi = '安信达快递';
    public static $ups = 'UPS快运';
    public static $tnt = 'TNT快递';
    public static $dhl = 'DHL快递';
    public static $aae = 'AAE全球专递';
    public static $huitongkuaidi = '百世汇通';
    public static $bht = 'bht快递';
    public static $baifudongfang = '百福东方国际物流';
    public static $changyuwuliu = '长宇物流';
    public static $datianwuliu = '大田物流';
    public static $dpex = 'DPEX物流';
    public static $guotongkuaidi = '国通快递';
    public static $gangzhongnengda = '港中能达物流';
    public static $huiqiangkuaidi = '汇强快递';
    public static $youzhengguonei = '邮政包裹挂号信';
    public static $youzhengguoji = '邮政国际包裹挂号信';
    public static $longbanwuliu = '龙邦速递';
    public static $bangsongwuliu = '邦送物流';
    public static $feikangda = '飞康达物流';
    public static $feikuaida = '飞快达';
    public static $fengxingtianxia = '风行天下';
    public static $feibaokuaidi = '飞豹快递';
    public static $ganzhongnengda = '港中能达';
    public static $jiajiwuliu = '佳吉物流';
    public static $cces = '希伊艾斯（CCES）';
    public static $coe = '中国东方（COE）';
    public static $chuanxiwuliu = '传喜物流';
    public static $dsukuaidi = 'D速快递';
    public static $disifang = '递四方';
    public static $guangdongyouzhengwuliu = '广东邮政';
    public static $gls = 'GLS';
    public static $gongsuda = '共速达';
    public static $huaxialongwuliu = '华夏龙';
    public static $haiwaihuanqiu = '海外环球';
    public static $haimengsudi = '海盟速递';
    public static $huaqikuaiyun = '华企快运';
    public static $haihongwangsong = '山东海红';
    public static $jiayiwuliu = '佳怡物流';
    public static $jiayunmeiwuliu = '加运美';
    public static $jinguangsudikuaijian = '京广速递';
    public static $jinyuekuaidi = '晋越快递';
    public static $jietekuaidi = '捷特快递';
    public static $jindawuliu = '金大物流';
    public static $jialidatong = '嘉里大通';
    public static $kangliwuliu = '康力物流';
    public static $kuayue = '跨越物流';
    public static $lianhaowuliu = '联昊通';
    public static $lanbiaokuaidi = '蓝镖快递';
    public static $longlangkuaidi = '隆浪快递';
    public static $menduimen = '门对门';
    public static $meiguokuaidi = '美国快递';
    public static $mingliangwuliu = '明亮物流';
    public static $ocs = 'OCS';
    public static $ontrac = 'onTrac';
    public static $quanchenkuaidi = '全晨快递';
    public static $quanjitong = '全际通';
    public static $quanritongkuaidi = '全日通';
    public static $santaisudi = '三态速递';
    public static $suer = '速尔物流';
    public static $shengfengwuliu = '盛丰物流';
    public static $shangda = '上大物流';
    public static $saiaodi = '赛澳递';
    public static $shenganwuliu = '圣安物流';
    public static $suijiawuliu = '穗佳物流';
    public static $youshuwuliu = '优速物流';
    public static $wanjiawuliu = '万家物流';
    public static $wanxiangwuliu = '万象物流';
    public static $weitepai = '微特派';
    public static $neweggozzo = '新蛋奥硕物流';
    public static $hkpost = '香港邮政';
    public static $yuntongkuaidi = '运通快递';
    public static $yuanweifeng = '源伟丰快递';
    public static $yuanzhijiecheng = '元智捷诚';
    public static $yuefengwuliu = '越丰物流';
    public static $yuananda = '源安达';
    public static $yuanfeihangwuliu = '原飞航';
    public static $zhongxinda = '忠信达快递';
    public static $zhimakaimen = '芝麻开门';
    public static $yinjiesudi = '银捷速递';
    public static $zhongyouwuliu = '中邮物流';
    public static $zhongsukuaidi = '中速快件';
    public static $zhongtianwanyun = '中天万运';
    public static $fedexcn = '联邦快递';
    public static $annengwuliu = '安能物流';
    public static $anxl = '安迅物流';
    public static $balunzhi = '巴伦支快递';
    public static $xiaohongmao = '北青小红帽';
    public static $lbbk = '宝凯物流';
    public static $bqcwl = '百千诚物流';
    public static $byht = '博源恒通';
    public static $idada = '百成大达物流';
    public static $city100 = '城市100';
    public static $chengjisudi = '城际速递';
    public static $lijisong = '成都立即送';
    public static $chukou1 = '出口易';
    public static $dhlen = 'DHL(国际件)';
    public static $dhlde = 'DHL(德国件)';
    public static $dayangwuliu = '大洋物流';
    public static $diantongkuaidi = '店通快递';
    public static $dechuangwuliu = '德创物流';
    public static $donghong = '东红物流';
    public static $donghanwl = '东瀚物流';
    public static $dfpost = '达方物流';
    public static $emsguoji = 'EMS国际快递';
    public static $eshunda = '俄顺达';
    public static $fedex = 'FedEx';
    public static $fedexus = 'FedEx(美国)';
    public static $feihukuaidi = '飞狐快递';
    public static $fanyukuaidi = '凡宇速递';
    public static $fandaguoji = '颿达国际';
    public static $feiyuanvipshop = '飞远配送';
    public static $hnfy = '飞鹰物流';
    public static $gaticn = 'GATI快递';
    public static $gtongsudi = '广通速递';
    public static $suteng = '广东速腾物流';
    public static $gdkd = '港快速递';
    public static $hre = '高铁速递';
    public static $gda = '冠达快递';
    public static $hlyex = '好来运快递';
    public static $hebeijianhua = '昊盛物流';
    public static $hutongwuliu = '户通物流';
    public static $hzpl = '华航快递';
    public static $huangmajia = '黄马甲快递';
    public static $ucs = '合众速递(UCS)';
    public static $hanrun = '韩润物流';
    public static $pfcexpress = '皇家物流';
    public static $huoban = '伙伴物流';
    public static $nedahm = '红马速递';
    public static $iparcel = 'i-parcel';
    public static $jd = '京东快递';
    public static $jiuyicn = '久易快递';
    public static $kuaiyouda = '快优达速递';
    public static $kuaitao = '快淘快递';
    public static $lejiedi = '乐捷递';
    public static $lanhukuaidi = '蓝弧快递';
    public static $minbangsudi = '民邦速递';
    public static $minshengkuaidi = '闽盛快递';
    public static $mailikuaidi = '麦力快递';
    public static $nuoyaao = '偌亚奥国际';
    public static $nanjingshengbang = '南京晟邦物流';
    public static $pingandatengfei = '平安达腾飞';
    public static $peixingwuliu = '陪行物流';
    public static $sevendays = '7天连锁物流';
    public static $qbexpress = '秦邦快运';
    public static $riyuwuliu = '日昱物流';
    public static $rfsd = '瑞丰速递';
    public static $shiyunkuaidi = '世运快递';
    public static $sxhongmajia = '山西红马甲';
    public static $syjiahuier = '沈阳佳惠尔';
    public static $shlindao = '上海林道货运';
    public static $sfift = '十方通物流';
    public static $tonghetianxia = '通和天下';
    public static $tianzong = '天纵物流';
    public static $chinatzx = '同舟行物流';
    public static $nntengda = '腾达速递';
    public static $usps = 'USPS美国邮政';
    public static $wanboex = '万博快递';
    public static $xiyoutekuaidi = '希优特快递';
    public static $xianglongyuntong = '祥龙运通物流';
    public static $xianchengliansudi = '西安城联速递';
    public static $xilaikd = '西安喜来快递';
    public static $xsrd = '鑫世锐达';
    public static $yishunhang = '亿顺航';
    public static $yitongfeihong = '一统飞鸿';
    public static $yuxinwuliu = '宇鑫物流';
    public static $yitongda = '易通达';
    public static $youbijia = '邮必佳';
    public static $yiqiguojiwuliu = '一柒物流';
    public static $yinsu = '音素快运';
    public static $yilingsuyun = '亿领速运';
    public static $yujiawuliu = '煜嘉物流';
    public static $gml = '英脉物流';
    public static $leopard = '云豹国际货运';
    public static $czwlyn = '云南中诚';
    public static $ztky = '中铁物流';
    public static $zhengzhoujianhua = '郑州建华';
    public static $zhongwaiyun = '中外运速递';
    public static $zengyisudi = '增益速递';
    public static $sujievip = '郑州速捷';

}

