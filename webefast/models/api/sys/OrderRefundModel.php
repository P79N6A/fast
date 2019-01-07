<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class OrderRefundModel extends TbModel {

    function get_table() {
        return 'api_refund';
    }

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = "";
        if($filter['ctl_type'] == 'export'){
            $sql_join = " LEFT JOIN api_refund_detail r2 on rl.refund_id = r2.refund_id ";
        }
        $sql_main1 = $sql_main = "FROM {$this->table} rl {$sql_join} WHERE 1";
        $sql_values = array();
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //退单编号
        if (isset($filter['refund_id']) && $filter['refund_id'] != '') {

            $sql_main .= " AND rl.refund_id LIKE :refund_id ";
            $sql_values[':refund_id'] = $filter['refund_id'] . '%';
        }
        //买家昵称
        if (isset($filter['buyer_nick']) && $filter['buyer_nick'] != '') {

            $sql_main .= " AND rl.buyer_nick LIKE :buyer_nick ";
            $sql_values[':buyer_nick'] = $filter['buyer_nick'] . '%';
        }
        //echo '<hr/>$filter<xmp>'.var_export($filter,true).'</xmp>';
        // 转单状态
        if (isset($filter['is_change']) && $filter['is_change'] <> 'all') {
            if ($filter['is_change'] == 1) {
                $sql_main .= " AND rl.is_change = 1 ";
            } else if ($filter['is_change'] == -1) {
                $sql_main .= " AND rl.is_change = -1 ";
            } else {
                $sql_main .= " AND rl.is_change =0 ";
            }
        }
        
        //是否允许转单
        if (isset($filter['status']) && $filter['status'] != '' && $filter['status'] != 'all') {
            $sql_main .= " AND rl.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }

        //销售平台
        if (isset($filter['source']) && $filter['source'] <> '') {
                $arr = explode(',', $filter['source']);
                $str = $this->arr_to_in_sql_value($arr, 'source', $sql_values);
                $sql_main .= " AND rl.source  in ({$str}) ";
        }
        //申请时间
        if (isset($filter['order_first_start']) && $filter['order_first_start'] != '') {
            $sql_main .= " AND (rl.order_first_insert_time >= :order_first_start )";
            $sql_values[':order_first_start'] = $filter['order_first_start'];
        }
        if (isset($filter['order_first_end']) && $filter['order_first_end'] != '') {
            $sql_main .= " AND (rl.order_first_insert_time <= :order_first_end )";
            $sql_values[':order_first_end'] = $filter['order_first_end'];
        }
        //退货快递信息
        if (isset($filter['refund_express_no']) && $filter['refund_express_no'] <> 'all' && $filter['refund_express_no'] != '') {
            if ($filter['refund_express_no'] == 1) {
                $sql_main .= "AND rl.refund_express_no is not null AND  rl.refund_express_no != ''";
            } else if ($filter['refund_express_no'] == 0) {
                $sql_main .="AND (rl.refund_express_no is null or rl.refund_express_no = '') ";
            }
        }
        $select = 'rl.*';
        //交易号
        if (isset($filter['tid']) && $filter['tid'] != '') {
            $sql_values = array();
            $sql_main = $sql_main1;
            $sql_main .= " AND rl.tid LIKE :tid ";
            $sql_values[':tid'] = $filter['tid'] . '%';
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $sql_main .= 'ORDER BY order_first_insert_time DESC';
        if ($filter['ctl_type'] == 'export') {
            $select .= ', r2.goods_code,r2.title,r2.goods_barcode,r2.num,r2.sku_properties_name,r2.refund_price ';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('safety_control'));
        if ($cfg['safety_control'] == 1 && $filter['ctl_type'] == 'view') {
            foreach ($data['data'] as &$value) {
                $value['buyer_nick'] = $this->name_hidden($value['buyer_nick']);
            }
        }
        //$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop',));
        foreach ($data['data'] as &$value) {
            $value['is_change_status'] = $value['is_change'] == 0 ? '否' : '是';
            $value['refund_type'] = $value['has_good_return'] == 1 ? '退款退货' : '仅退款';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('refund_id' => $id));
    }

    /**
     * 获取退单详情信息
     * @param string $id refund_id 退单编号
     */
    function get_refund($id) {
        $refund = $this->get_by_id($id);
        $refund = $refund['data'];
        $refund['refund_type'] = $refund['has_good_return'] == 1 ? '退款退货' : '仅退款';
        $refund['refund_express_name'] = get_express_name_by_code($refund['refund_express_code']);
        $sql_detail = 'SELECT rd.goods_code,rd.title,rd.price,rd.num,rd.refund_price,rd.goods_barcode,gs.spec1_name,gs.spec2_name,rd.detail_id FROM api_refund_detail AS rd LEFT JOIN goods_sku AS gs ON rd.goods_barcode=gs.barcode WHERE rd.refund_id=:refund_id';
        $refund['detail'] = $this->db->get_all($sql_detail, array(':refund_id' => $refund['refund_id']));
        foreach ($refund['detail'] as &$value) {
            $value['sku_properties'] = empty($value['spec1_name']) && empty($value['spec2_name']) ? '' : '颜色:'.$value['spec1_name'].'; 尺码:'.$value['spec2_name'];
        }
        return $refund;
    }

    /**
     * 更新退单商品
     */
    function update_detail($data) {
        if (empty($data)) {
            return $this->format_ret(1, '', '未更改商品信息');
        }
        $ids = array_column($data, 'detail_id');
        $ids_str = implode(',', $ids);
        $detail = $this->db->get_all("SELECT detail_id,goods_barcode FROM api_refund_detail WHERE detail_id in({$ids_str})");
        $old_info = array_column($detail, 'goods_barcode', 'detail_id');

        $ret = $this->insert_multi_duplicate('api_refund_detail', $data, 'goods_barcode=VALUES(goods_barcode)');
        if ($ret['status'] == 1) {
            $log = array();
            foreach ($data as $val) {
                $log[] = array(
                    'user_id' => CTX()->get_session('user_id'),
                    'user_code' => CTX()->get_session('user_code'),
                    'ip' => '',
                    'add_time' => date('Y-m-d H:i:s'),
                    'module' => '网络订单',
                    'yw_code' => $val['refund_id'],
                    'operate_type' => '修改',
                    'operate_xq' => "商品条形码：{$old_info[$val['detail_id']]}变更为{$val['goods_barcode']}"
                );
            }
            load_model('sys/OperateLogModel')->insert_multi($log);
        }
        return $ret;
    }

    function update_active($active, $id) {

        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $data = $this->db->create_mapper('api_goods_sku')->update(array('is_allow_sync_inv' => $active), array('goods_from_id' => $id));
        $ret = parent :: update(array('is_inv_sync' => $active), array('goods_from_id' => $id));
        return $ret;
    }
    //设置处理状态
    function set_change_status($refund_id,$change_status) {
        if ($change_status == 1) {
            $order_status = $this->db->get_row(" SELECT is_change FROM api_refund WHERE refund_id = :refund_id ", array(':refund_id'=>$refund_id));
            if ($order_status['is_change']==1) {
                return $this->format_ret(-1, '', ' 状态为处理成功，不能执行此操作');
            }
            $change_remark = '设置为已处理';
        } else {
            $order_status = $this->db->get_row(" SELECT is_hand_change FROM api_refund WHERE refund_id = :refund_id ", array(':refund_id'=>$refund_id));
            if ($order_status['is_hand_change']==0) {
                return $this->format_ret(-1, '', ' 非手动设为已处理，不能执行此操作');
            }
            $change_remark = '设置为未处理';
        }
        $sql = "update api_refund set is_change = :is_change,is_hand_change = :is_hand_change,change_remark=:change_remark where refund_id = :refund_id";
        $ret = $this->db->query($sql,array(':is_change'=>$change_status,':is_hand_change'=>$change_status , ':change_remark'=>$change_remark,':refund_id'=>$refund_id));
        if ($ret) {
            $log = array(
                'user_id' => CTX()->get_session('user_id'),
                'user_code' => CTX()->get_session('user_code'),
                'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
                'module' => '网络订单',
                'yw_code' => $refund_id,
                'operate_type' => '修改',
                'operate_xq' => $change_remark
            );
            load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret(1,'',' 设置成功');
        }else{
            return $this->format_ret(1,'',' 设置失败');
        }       
    }

    /**
     * @todo 新增退单
     * @desc 外部接口新增退单到api_refund，存在即更新
     * @param
     *         主单据:     array(
     *                  必填: 'refund_id', 'tid', 'shop_code', 'status','has_good_return','create_time','refund_fee','refund_reason'
     *                  选填:  'seller_nick','buyer_nick', 'refund_express_code', 'refund_express_no', 'refund_desc'
     *         )
     *         商品:  array(
     *          		必填: 'sku','price','num'
     *         )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    function api_add_refund_order($data) {
        $return_detail = $data['return_detail'];
        unset($data['return_detail']);
        $return_record = $data;
        $ret = $this->api_check_params($return_record, $return_detail);
        if ($ret['status'] != 1) {
            return $ret;
        }

        $this->begin_trans();
        try {
            $record_info = $ret['data']['record'];
            //添加api_order信息
            $record_info['tid'] = $record_info['deal_code'];
            $record_info['oid'] = $record_info['deal_code'];
            $record_info['order_first_insert_time'] = $record_info['create_time'];
            $record_info['order_last_update_time'] = $record_info['create_time'];
            $record_info['last_update_time'] = date('Y-m-d H:i:s');
            $record_info['first_insert_time'] = date('Y-m-d H:i:s');
            $record_info['refund_express_code'] = isset($record_info['refund_express_code']) ? $record_info['refund_express_code'] : '';
            $record_info['refund_express_no'] = isset($record_info['refund_express_no']) ? $record_info['refund_express_no'] : '';
            unset($record_info['deal_code'], $record_info['create_time']);

            $sql = "SELECT refund_id FROM api_refund WHERE refund_id=:refund_id";
            $ret_refund = $this->db->get_row($sql, array(':refund_id' => $record_info['refund_id']));

            $update_str = 'status = VALUES(status),last_update_time = VALUES(last_update_time),refund_express_code = VALUES(refund_express_code),refund_express_no = VALUES(refund_express_no)';
            $result = $this->insert_multi_duplicate($this->table, array($record_info), $update_str);

            if ($result['status'] != 1) {
                $this->rollback();
                return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_ERROR');
            }

            //存在退单则不插入明细
            if (empty($ret_refund)) {
                $detail_info = $ret['data']['detail'];
                foreach ($detail_info as &$detail) {
                    $detail['refund_id'] = $record_info['refund_id'];
                    $detail['tid'] = $record_info['tid'];
                    $detail['oid'] = $record_info['oid'];
                    $detail['goods_barcode'] = $detail['sku'];
                    unset($detail['sku']);
                }
                $result = $this->insert_multi_exp('api_refund_detail', $detail_info);

                if ($result['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', 'SELL_RECORD_SAVE_DETAIL_ERROR');
                }
            }
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        $this->commit();
        return $this->format_ret(1, '', '新增退单成功');
    }

    /**
     * @todo 校验接口传入参数
     */
    function api_check_params($record, $detail) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
            's' => array('refund_id', 'deal_code', 'shop_code', 'refund_reason', 'create_time'),
            'i' => array('status', 'has_good_return', 'refund_fee'),
        );
        //可选字段
        $key_option = array(
            's' => array('seller_nick', 'buyer_nick', 'refund_desc', 'refund_express_code', 'refund_express_no')
        );
        $detail_required = array(
            's' => array('sku'),
            'i' => array('num', 'refund_price')
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

            //判断平台交易号是否存在
            $sql = "SELECT tid FROM api_order WHERE tid=:tid";
            $ret_tid = $this->db->get_row($sql, array(':tid' => $brand['deal_code']));
            if (empty($ret_tid)) {
                return $this->format_ret(-1, array('deal_code' => $brand['deal_code']), '平台交易号不存在');
            }

            //判断店铺是否存在，同时查询来源平台
            $sql = "SELECT sale_channel_code AS source FROM base_shop WHERE shop_code=:shop_code";
            $ret_shop = $this->db->get_row($sql, array(':shop_code' => $brand['shop_code']));
            if (empty($ret_shop)) {
                return $this->format_ret(-1, array('shop_code' => $brand['shop_code']), '店铺不存在');
            }
            $brand['source'] = $ret_shop['source']; //来源平台

            $detail = json_decode($detail, true);
            if (empty($detail) || !is_array($detail)) {
                return $this->format_ret(-10005, '', '明细处理失败');
            }
            foreach ($detail as $d) {
                $ret_d_required = valid_assign_array($d, $detail_required, $d_required, TRUE);
                if ($ret_d_required['status'] != TRUE) {
                    return $this->format_ret("-10001", $ret_d_required['req_empty'], 'API_RETURN_MESSAGE_10001');
                }
                $sql = "SELECT bg.goods_code,bg.goods_name AS title,bg.price FROM base_goods bg
                    INNER JOIN goods_sku gs ON bg.goods_code=gs.goods_code WHERE gs.barcode=:barcode";
                $ret_goods = $this->db->get_row($sql, array(':barcode' => $d_required['sku']));
                if (empty($ret_goods)) {
                    //查看套餐存不存在
                    $combo_sql = 'select 
                                          gc.goods_code,gc.price,gc.goods_name as title 
                                  from goods_combo gc 
                                  inner join goods_combo_barcode gb on gc.goods_code = gb.goods_code
                                  where gb.barcode = :barcode and status = 1';
                    $ret_goods = $this->db->get_row($combo_sql,array(':barcode' => $d_required['sku']));
                    if(empty($ret_goods)){
                        return $this->format_ret(-1, array('sku' => $d_required['sku']), 'SKU记录不存在');
                    }
                }
                $new_detail[] = array_merge($d_required, $ret_goods);
            }
            $data = array();
            $data['record'] = $brand;
            $data['detail'] = $new_detail;
            return $this->format_ret(1, $data);
        } else {
            return $this->format_ret("-10001", $ret_required['req_empty'], 'API_RETURN_MESSAGE_10001');
        }
    }


    /**
     * 退单下载
     * @param $request
     * @return mixed
     */
    function down_refund($request) {
        $params=array();
        $params['sale_channel_code']=$request['sale_channel_code'];
        $params['shop_code']=$request['shop_code'];
        $params['start_time']=$request['start_time'];
        $params['end_time']=$request['end_time'];
        $params['method']='refund_sync';
        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);
        return $result;
    }

    /**
     * 下载进度
     * @param $request
     * @return mixed
     */
    function down_refund_check($request) {
        $params = array();
        $params['task_sn'] = $request['task_sn'];
        $result = load_model('sys/EfastApiTaskModel')->request_api('check', $params);
        return $result;
    }

}
