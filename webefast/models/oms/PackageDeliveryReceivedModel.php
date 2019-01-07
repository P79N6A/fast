<?php

/**
 * 包裹快递交接模型类
 * 2017/04/12
 * @author zwj
 * @modify 2017/10/12 WMH
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class PackageDeliveryReceivedModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record';

    //获取扫描提示音
    public function get_sound() {
        $res = array('success' => '', 'error' => '');
        $sql = "select value from sys_params where param_code = :param_code";
        $sql_val = array(':param_code' => 'scan_voice_ok');
        $res['success'] = $this->db->get_value($sql, $sql_val);
        $sql_val = array(':param_code' => 'scan_voice_fail');
        $res['error'] = $this->db->get_value($sql, $sql_val);
        return $res;
    }

    /**
     * 快递交接
     * @param string $express_no 快递单号
     * @param string $call_source 调用来源
     * @return array
     */
    public function express_scan_receive($express_no, $call_source = '') {
        $check_receive = $this->check_express_no($express_no);
        if ($check_receive['status'] < 1) {
            return $check_receive;
        }
        $record = $check_receive['data'];
        $ret = $this->check_refund($record['deal_code_list']);
        //更新快递交接状态
        $receive_status = $ret['status'] < 1 ? -1 : 1;
        $this->update(['is_receive' => $receive_status], [ 'express_no' => $express_no]);
        if ($ret['status'] == 1) {
            $ret['message'] = '快递交接成功';
        }

        if ($call_source != 'api') {
            load_model('oms/SellRecordActionModel')->add_action($record['sell_record_code'], '快递交接', $ret['message']);
        }

        $ret['data'] = get_array_vars($record, ['sell_record_code', 'express_code', 'express_name', 'express_no', 'delivery_time','goods_num']);
        return $ret;
    }

    /**
     * 检查快递单是否可交接
     * @param string $express_no
     * @return array
     */
    public function check_express_no($express_no) {
        $sql = "SELECT sell_record_code,deal_code_list,express_code,express_no,delivery_time,goods_num,is_receive FROM {$this->table} WHERE express_no=:express_no AND shipping_status=4";
        $res = $this->db->get_row($sql, array(':express_no' => $express_no));
        if (empty($res)) {
            return $this->format_ret(-1, '', "快递单不存在");
        }
        if (!empty($res['express_code'])) {
            $res['express_name'] = get_express_name_by_code($res['express_code']);
        }
        if (empty($res['express_code']) || empty($res['express_name'])) {
            return $this->format_ret(-1, '', "配送方式不存在，请检查快递配置");
        }
        if ($res['is_receive'] == 1) {
            return $this->format_ret(-1, '', "该快递单已交接");
        }

        return $this->format_ret(1, $res, "快递单未交接");
    }

    /**
     * 校验是否存在退单
     * @param string $deal_code
     * @return array
     */
    public function check_refund($deal_code) {
        $status = 1;
        $msg = '';
        $sql_values = [];
        $deal_code_arr = explode(',', $deal_code);
        $deal_code_str = $this->arr_to_in_sql_value($deal_code_arr, 'tid', $sql_values);

        $sql = "SELECT tid,is_change,change_remark FROM api_refund WHERE tid IN ({$deal_code_str})  AND is_change<>1";
        $tid_data = $this->db->get_row($sql, $sql_values);
        if (!empty($tid_data)) {
            if ($tid_data['is_change'] == 0) {
                $status = -2;
                $msg = "交易号{$tid_data['tid']}存在未处理的退单";
            } else if ($tid_data['is_change'] == -1) {
                $status = -2;
                $msg = "交易号{$tid_data['tid']}存在已处理失败退单，失败原因：{$tid_data['change_remark']}";
            }
            return $this->format_ret($status, '', $msg);
        }

        $sql = "SELECT tid FROM api_refund WHERE tid IN ({$deal_code_str}) AND is_change=1";
        $tid_arr = $this->db->get_all_col($sql, $sql_values);
        if (!empty($tid_arr)) {
            $sql_values = [];
            $tid_str = $this->arr_to_like_sql_value($tid_arr, 'deal_code', $sql_values);
            $sql = "SELECT sell_return_code FROM oms_sell_return WHERE {$tid_str} AND return_order_status=0";
            $deal_data = $this->db->get_row($sql, $sql_values);
            if (!empty($deal_data)) {
                $status = -2;
                $msg = "交易号{$deal_data['tid']}存在已处理未确认的退单{$deal_data['sell_return_code']}";
            }
        }


        return $this->format_ret($status, '', $msg);
    }

    //获取统计数据
    public function get_count_data($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1 ";
        if ($filter['ctl_type'] == 'export') {
            $sql_main = " FROM {$this->table} rl, {$this->table}_detail r2 WHERE rl.sell_record_code = r2.sell_record_code";
        }

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);

        //发货日期
        if (isset($filter['deliver_date_start']) && !empty($filter['deliver_date_start'])) {
            $sql_main .= " AND rl.delivery_date >= :deliver_date_start ";
            $sql_values[':deliver_date_start'] = $filter['deliver_date_start'];
        }
        if (isset($filter['deliver_date_end']) && !empty($filter['deliver_date_end'])) {
            $sql_main .= " AND rl.delivery_date <= :deliver_date_end ";
            $sql_values[':deliver_date_end'] = $filter['deliver_date_end'];
        }
        $sql_main .= " AND rl.shipping_status = :shipping_status ";
        $sql_values[':shipping_status'] = 4;

        $select = 'rl.*';
        if ($filter['ctl_type'] == 'export') {
            $select .= ", r2.num, r2.sku, r2.platform_spec, r2.goods_price, r2.avg_money";
            if(isset($filter['ctl_export_name'])&&$filter['ctl_export_name']=='未交接包裹明细'){
                $receive_arr=array(0,-1);
                $str = $this->arr_to_in_sql_value($receive_arr, 'is_receive', $sql_values);
                $sql_main .= " AND rl.is_receive in ({$str})";
            }else{
                $sql_main .= " AND rl.is_receive = :is_receive ";
                $sql_values[':is_receive'] = 1;
            }

            $data = $this->db->get_all('SELECT ' . $select . $sql_main, $sql_values);
            foreach ($data as $key => &$value) {
                $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
                $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));
                $value['sale_channel_name'] = oms_tb_val('base_sale_channel','sale_channel_name',array('sale_channel_code'=>$value['sale_channel_code']));
                $key_arr = array('barcode', 'goods_name', 'goods_code', 'spec1_name', 'spec2_name');
                $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
                $data['data'][$key] = array_merge($value, $sku_info);
            }
        } else {
            $sql_main .= " GROUP BY rl.store_code,rl.express_code,rl.delivery_date ORDER BY delivery_date DESC,sell_record_id DESC";
            $select.= ",sum(rl.goods_num) as goods_num";
            $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
            foreach ($data['data'] as $key => &$value) {
                $value['shop_name'] = oms_tb_val('base_shop', 'shop_name', array('shop_code' => $value['shop_code']));
                $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
                $value['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code' => $value['express_code']));

                if ($filter['ctl_type'] == 'export') {
                    $key_arr = array('barcode', 'goods_name', 'goods_code', 'spec1_name', 'spec2_name');
                    $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
                    $value = array_merge($value, $sku_info);
                }

                $sql = "select count(*) from {$this->table} where store_code =:store_code and express_code =:express_code and delivery_date =:delivery_date ";
                $sql_val = array(':store_code' => $value['store_code'], ':express_code' => $value['express_code'], ':delivery_date' => $value['delivery_date']);
                $value['deliver_num'] = $this->db->get_value($sql, $sql_val);
                $sql .= " and is_receive =:is_receive ";
                $sql_val[':is_receive'] = 1;
                $value['success_num'] = $this->db->get_value($sql, $sql_val);
                $sql_val[':is_receive'] = -1;
                $value['fail_num'] = $this->db->get_value($sql, $sql_val);
                $sql_val[':is_receive'] = 0;
                $value['blank_num'] = $this->db->get_value($sql, $sql_val);
            }
        }
        if ($filter['ctl_type'] == 'export' && isset($filter['ctl_export_conf'])  && !empty($filter['__t_user_code'])) {
            $is_security_role = load_model('sys/UserModel')->is_security_role($filter['__t_user_code']);
            if ($is_security_role === true) {
                $data['data'] = load_model('sys/security/OmsSecurityOptModel')->get_sell_record_decrypt_list($data['data']);
                $log = array('user_id' => 0, 'user_code' => $filter['__t_user_code'], 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '配发货', 'yw_code' => '', 'operate_type' => '导出', 'operate_xq' => '包裹快递交接统计导出解密数据');
                load_model('sys/OperateLogModel')->insert($log);
            }
        }
        //处理分页
        //总条数
        $sql = "SELECT count(t.count_sl) FROM (" . "SELECT count(*) AS count_sl $sql_main " . ") AS t ";
        $data_count = $this->db->get_value($sql, $sql_values);
        $record_count = $data_count;
        $data['filter']['record_count'] = $record_count;
        //页码
        $page = (int) $filter['page'];
        //每页的显示条数
        $pageSize = (int) $filter['page_size'];
        //总共要显示几页
        $data['filter']['page_count'] = ceil($record_count / $pageSize);
        //分页显示
        $new_data = array_chunk($data['data'], $pageSize);
        $data['data'] = $new_data[0];
        if (empty($data['data'])) {
            $data['data'] = array();
        }
        return $this->format_ret(1, $data);
    }

    //获取统计数据
    public function get_picture_count_data($request) {
        $sql_values = array();
        $sql_main = "SELECT count(*) FROM {$this->table} rl WHERE 1 ";

        //商店仓库权限
        $request_store_code = isset($request['store_code']) ? $request['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $request_store_code);

        //发货日期
        if (isset($request['deliver_date_start']) && !empty($request['deliver_date_start'])) {
            $sql_main .= " AND rl.delivery_date >= :deliver_date_start ";
            $sql_values[':deliver_date_start'] = $request['deliver_date_start'];
        }
        if (isset($request['deliver_date_end']) && !empty($request['deliver_date_end'])) {
            $sql_main .= " AND rl.delivery_date <= :deliver_date_end ";
            $sql_values[':deliver_date_end'] = $request['deliver_date_end'];
        }
        $sql_main .= " AND rl.shipping_status = :shipping_status ";
        $sql_values[':shipping_status'] = 4;

        //总数
        $deliver_num = $this->db->get_value($sql_main, $sql_values);

        //交接成功数
        $sql_main .= " and is_receive =:is_receive ";
        $sql_values[':is_receive'] = 1;
        $success_num = $this->db->get_value($sql_main, $sql_values);
        //交接失败数
        $sql_values[':is_receive'] = -1;
        $fail_num = $this->db->get_value($sql_main, $sql_values);
        //未交接数
        $sql_values[':is_receive'] = 0;
        $blank_num = $this->db->get_value($sql_main, $sql_values);

        $success_num = bcdiv($success_num, $deliver_num, 4) * 100;
        $fail_num = bcdiv($fail_num, $deliver_num, 4) * 100;
        $blank_num = bcdiv($blank_num, $deliver_num, 4) * 100;

        $data = array();
        $data[] = array('name' => '交接成功包裹数', 'num' => $success_num);
        $data[] = array('name' => '交接失败包裹数', 'num' => $fail_num);
        $data[] = array('name' => '未交接包裹数', 'num' => $blank_num);

        return $this->format_ret(1, $data);
    }

}
