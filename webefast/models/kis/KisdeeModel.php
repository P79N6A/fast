<?php

require_model('tb/TbModel');

/**
 * 金蝶日报业务
 */
class KisdeeModel extends TbModel {

    function __construct() {
        parent::__construct('kisdee_trade');
    }

    private $record_type = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
    );
    private $upload_status = array(
        'upload_not' => 0,
        'upload_success' => 1,
        'upload_fail' => 2
    );
    //日报生成状态
    private $_status = array();
    //日报生成信息
    private $_message = array();

    /**
     * 销售日报查询
     * @param array $filter 过滤条件
     * @return array 查询结果集
     */
    function get_sell_daily_by_page($filter) {
        $sql_main = "FROM {$this->table} WHERE 1 ";
        $sql_values = array();
        $config_store = $this->get_sys_api_store();
        if (empty($config_store)) {
            $sql_main .= " AND 1 = 2 ";
        }
        $config_store = array_column($config_store, 'store_code');
        $config_store_str = deal_array_with_quote($config_store);

        $sql_main .= " AND store_code IN($config_store_str) ";
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] <> '') {
            $sql_main .= " AND record_code = :record_code ";
            $sql_values[':record_code'] = $filter['record_code'];
        }
        //单据类型
        if (isset($filter['record_type']) && $filter['record_type'] <> 'all') {
            $sql_main .= " AND record_type = :record_type ";
            $sql_values[':record_type'] = $filter['record_type'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $shop_str = deal_strs_with_quote($filter['shop_code']);
            $sql_main .= " AND shop_code IN({$shop_str}) ";
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $store_str = deal_strs_with_quote($filter['store_code']);
            $sql_main .= " AND store_code IN({$store_str}) ";
        }
        //上传时间-开始
        if (isset($filter['upload_time_start']) && $filter['upload_time_start'] <> '') {
            $sql_main .= " AND upload_time >=:upload_time_start ";
            $sql_values[':upload_time'] = $filter['upload_time_start'];
        }
        //上传时间-结束
        if (isset($filter['upload_time_end']) && $filter['upload_time_end'] <> '') {
            $sql_main .= " AND upload_time >=:upload_time_end ";
            $sql_values[':upload_time'] = $filter['upload_time_end'];
        }

        //是否上传
        if (isset($filter['upload_tab']) && $filter['upload_tab'] <> '') {
            $sql_main .= " AND upload_status =:upload_status ";
            $sql_values[':upload_status'] = $this->upload_status[$filter['upload_tab']];
        }

        $select = '*';
        $sql_main .= " ORDER BY upload_time DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$row) {
            $row['record_type_name'] = $this->record_type[$row['record_type']];
            $row['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
            $row['upload_time'] = $row['upload_time'] != 0 ? date('Y-m-d H:i:s', $row['upload_time']) : '';
        }
        filter_fk_name($data['data'], array('shop_code|shop', 'store_code|store'));

        return $this->format_ret(1, $data);
    }

    /**
     * 根据条件获取记录
     */
    function get_info($params) {
        $sql = "SELECT kt.record_code,kt.record_type,kt.amount,bs.shop_name,st.store_name,kt.remark,kt.record_date FROM kisdee_trade AS kt
            INNER JOIN sys_api_shop_store AS ss ON kt.store_code=ss.shop_store_code
            INNER JOIN base_shop AS bs ON kt.shop_code=bs.shop_code
            INNER JOIN base_store AS st ON kt.store_code=st.store_code
            WHERE kt.record_code=:record_code AND kt.record_type=:record_type AND ss.p_id=:p_id AND ss.p_type=2";
        return $this->db->get_row($sql, $params);
    }

    /**
     * 根据id获取零售日报主单据信息
     * @param int $id 主单据ID
     * @return array 数据集
     */
    function get_sell_daily_by_id($id) {
        $daily = $this->get_row(array('id' => $id));
        $daily = $daily['data'];
        $daily['record_type_name'] = $this->record_type[$daily['record_type']];
        $daily['create_time'] = date('Y-m-d H:i:s', $daily['create_time']);
        filter_fk_name($daily, array('shop_code|shop', 'store_code|store'));
        return $daily;
    }

    /**
     * 获取配置店铺
     */
    function get_sys_api_store() {
        $sql = "SELECT bs.store_code, bs.store_name FROM sys_api_shop_store AS ss
INNER JOIN kisdee_config AS kc ON ss.p_id = kc.config_id
INNER JOIN base_store AS bs ON ss.shop_store_code = bs.store_code
WHERE ss.p_type = 2 AND ss.shop_store_type = 1 AND outside_type = 1 AND kc.config_status = 1";
        $store = $this->db->get_all($sql);

        return $store;
    }

    /**
     * 获取配置仓库-选择仓库使用--暂作废
     */
    function get_store_by_page($filter) {
        $sql_main = " FROM sys_api_shop_store AS ss
                    INNER JOIN kisdee_config AS kc ON ss.p_id = kc.config_id
                    INNER JOIN base_store AS bs ON ss.shop_store_code = bs.store_code
                    WHERE ss.p_type = 2 AND ss.shop_store_type = 1 AND outside_type = 1 AND kc.config_status = 1";
        $sql_values = array();
        //仓库类型
        if (isset($filter['store_property']) && $filter['store_property'] != '') {
            $sql_main .= " AND bs.store_property = :store_property ";
            $sql_values[':store_property'] = $filter['store_property'];
        }
        //仓库名称
        if (isset($filter['store_name']) && $filter['store_name'] != '') {
            $sql_main .=' AND bs.store_name LIKE :store_name ';
            $sql_values[':store_name'] = "%{$filter['store_name']}%";
        }
        $select = 'bs.store_code, bs.store_name,ss.outside_code';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        return $this->format_ret(1, $data);
    }

    /**
     * 获取单据类型供选择
     */
    function get_select_record_type() {
        $record_type = $this->record_type;
        $new_record_type = array();
        foreach ($record_type as $key => $val) {
            $arr['record_type_code'] = $key;
            $arr['record_type_name'] = $val;
            $new_record_type[] = $arr;
        }
        return $new_record_type;
    }

    /**
     * 根据条件判断记录是否存在
     * @param array $params 查询条件数组
     * @return array 记录集合
     */
    function is_exists($params = array()) {
        $wh = get_array_vars($params, array('record_date', 'shop_code', 'store_code', 'record_type'));
        return $this->get_row($wh);
    }

    /**
     * 生成销售日报
     * @param array $data 条件数组
     * @return array 生成结果
     */
    function create_sell_daily($data) {
        try {
            $field_arr = array('record_date' => '业务日期', 'shop_code' => '店铺', 'record_type' => '日报类型');
            $ret_check = $this->check_params($data, $field_arr);
            if ($ret_check['status'] != 1) {
                return $ret_check;
            }
            $sql_values = array(
                ':record_date_start' => $data['record_date'] . ' 00:00:00',
                ':record_date_end' => $data['record_date'] . ' 23:59:59',
            );
            $ss_arr = $this->comb_shop_store($data['shop_code']);
            if (isset($ss_arr['status']) && $ss_arr['status'] != 1) {
                throw new Exception($ss_arr['message']);
            }

            array_walk($ss_arr, function($val) use(&$data, &$sql_values) {
                $sql_values[':shop_code'] = $val['shop_code'];
                $sql_values[':store_code'] = $val['store_code'];
                $data = array_merge($data, $val);
                if ($data['record_type'] == 'all') {
                    $data['record_type'] = 'sell_record';
                    $this->sell_daily($data, $sql_values);
                    $data['record_type'] = 'sell_return';
                    $this->sell_daily($data, $sql_values);
                } else {
                    $this->sell_daily($data, $sql_values);
                }
            });
            $msg = $this->create_fail_file($this->_message);
            if (in_array(-1, $this->_status)) {
                throw new Exception('部分日报生成失败' . $msg);
            }
            if (in_array(-2, $this->_status) && !in_array(-1, $this->_status) && !in_array(1, $this->_status)) {
                throw new Exception('无单据记录');
            }
            return $this->format_ret(1, '', '日报生成成功');
        } catch (Exception $e) {
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    /**
     * 生成日报主单据信息
     * @param array $data 条件数组
     * @param array $sql_values 查询条件占位符值
     * @return boolean 成功返回ture，失败返回false
     */
    private function sell_daily($data, $sql_values) {
        $record_type = $data['record_type'];
        $daily_info = get_array_vars($data, array('record_date', 'shop_code', 'store_code'));
        $record_type_name = $this->record_type[$record_type];
        $check_exists = $this->is_exists($data);
        if ($check_exists['status'] == 1) {
            $this->_status[] = -1;
            $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}已存在日报"));
            return FALSE;
        }

        $reocrd = $this->get_daily_record($record_type, $sql_values);
        if (empty($reocrd)) {
            $this->_status[] = -2;
            return FALSE;
        }
        $record_code = $this->create_record_code();
        $data['record_code'] = $record_code;
        $data['create_time'] = time();
        $reocrd['quantity'] = (int) $reocrd['quantity'];
        $reocrd['amount'] = (float) $reocrd['amount'];
        $reocrd['express_amount'] = (float) $reocrd['express_amount'];
        $daily = array_merge($data, $reocrd);

        $this->begin_trans();
        $ret = $this->insert($daily);
        if ($ret['status'] != 1 || $this->affected_rows() != 1) {
            $this->rollback();
            $this->_status[] = -1;
            $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}日报生成失败"));
            return FALSE;
        }
        unset($daily);

        $ret = $this->sell_daily_detail($data, $sql_values);
        if ($ret === FALSE) {
            $this->rollback();
        } else {
            $this->commit();
        }
        return $ret;
    }

    /**
     * 获取日报主单据信息
     * @param string $record_type 单据类型
     * @param array $sql_values 查询条件
     * @return array 数据集
     */
    private function get_daily_record($record_type, $sql_values) {
        $data = array();
        switch ($record_type) {
            case 'sell_record':
                $sql = "SELECT sum(goods_num) AS quantity,sum(payable_money) AS amount,sum(express_money) AS express_amount FROM oms_sell_record WHERE shipping_status=4 AND is_fenxiao=0 AND delivery_time>=:record_date_start AND delivery_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                $data1 = $this->db->get_row($sql, $sql_values);
                $sql = "SELECT sum(goods_num) AS quantity,sum(fx_payable_money) AS amount,sum(fx_express_money) AS express_amount FROM oms_sell_record WHERE shipping_status=4 AND is_fenxiao in (1,2) AND delivery_time>=:record_date_start AND delivery_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                $data2 = $this->db->get_row($sql, $sql_values);
                $data['quantity'] = $data1['quantity'] + $data2['quantity'];
                $data['amount'] = $data1['amount'] + $data2['amount'];
                $data['express_amount'] = $data1['express_amount'] + $data2['express_amount'];
                break;
            case 'sell_return':
                $sql = "SELECT sum(recv_num) AS quantity,sum(return_avg_money+seller_express_money+compensate_money+adjust_money) AS amount,0 AS express_amount FROM oms_sell_return WHERE return_shipping_status=1 AND receive_time>=:record_date_start AND receive_time<=:record_date_end AND shop_code=:shop_code AND store_code=:store_code";
                $data = $this->db->get_row($sql, $sql_values);
                break;

            default:
                $data = array();
                break;
        }

        return $data;
    }

    /**
     * 生成销售日报明细
     * @param array $data 条件数组
     * @param array $sql_values 查询条件占位符值
     * @return boolean 成功返回true，失败返回false
     */
    private function sell_daily_detail(&$data, $sql_values) {
        $record_type = $data['record_type'];
        $daily_info = get_array_vars($data, array('record_date', 'shop_code', 'store_code'));
        $record_type_name = $this->record_type[$record_type];
        if ($record_type == 'sell_record') {
            $sql = "SELECT rd.goods_code,rd.sku,sum(rd.num) AS num,sum(rd.avg_money) AS money
            FROM oms_sell_record AS sr
            INNER JOIN oms_sell_record_detail AS rd ON sr.sell_record_code=rd.sell_record_code
            WHERE sr.shipping_status=4 AND sr.delivery_time>=:record_date_start AND sr.delivery_time<=:record_date_end
            AND sr.shop_code=:shop_code AND sr.store_code=:store_code
            GROUP BY rd.sku ORDER BY sku";
        } else {
            $sql = "SELECT rd.goods_code,rd.sku,sum(rd.recv_num) AS num,sum(rd.avg_money) AS money
            FROM oms_sell_return AS sr
            INNER JOIN oms_sell_return_detail AS rd ON sr.sell_return_code=rd.sell_return_code
            WHERE sr.return_shipping_status=1 AND sr.receive_time>=:record_date_start AND sr.receive_time<=:record_date_end
            AND sr.shop_code=:shop_code AND sr.store_code=:store_code
            GROUP BY rd.sku ORDER BY sku";
        }
        $detail = $this->db->get_all($sql, $sql_values);
        if (empty($detail)) {
            $this->_status[] = -2;
            return FALSE;
        }
        $record_code = $data['record_code'];
        $i = 1;
        array_walk($detail, function(&$val) use(&$i, $record_code) {
            $val['record_code'] = $record_code;
            $val['detail_no'] = $i;
            $i++;
        });

        $detail_table = $record_type == 'sell_record' ? 'kisdee_trade_record_detail' : 'kisdee_trade_return_detail';

        $detail_arr = array_chunk($detail, 2000);
        foreach ($detail_arr as $val) {
            $ret = $this->insert_multi_exp($detail_table, $val);
            if ($ret['status'] != 1) {
                $this->_status[] = -1;
                $this->_message[] = array_merge($daily_info, array('daily_msg' => "{$record_type_name}日报明细生成失败"));
                return FALSE;
            }
        }
        $this->_status[] = 1;
//        $this->_message[] = array_merge($daily_info, array('daily_msg' => $record_type_name . "日报生成成功"));
        return TRUE;
    }

    /**
     * 店铺和仓库交叉生成数组
     * @param array $shop_code 店铺
     * @return array 处理结果数组
     */
    private function comb_shop_store($shop_code) {
        $store_arr = $this->get_sys_api_store();
        if (empty($store_arr)) {
            return $this->format_ret(-1, '', '未配置系统和金蝶仓库对应关系');
        }
        $store_arr = array_column($store_arr, 'store_code');
        $shop_arr = explode(',', $shop_code);

        $ss_arr = array();
        foreach ($store_arr as $store) {
            foreach ($shop_arr as $shop) {
                $arr['shop_code'] = $shop;
                $arr['store_code'] = $store;
                $ss_arr[] = $arr;
            }
        }

        return $ss_arr;
    }

    /**
     * 检查参数是否存在
     * @param array $params 参数
     * @param array $field_arr 要检查的字段 array('字段'=>'字段名')
     * @return array 检查结果
     */
    public function check_params($params, $field_arr = array()) {
        if (empty($params) || empty($field_arr)) {
            return $this->format_ret(-1, '', '内部参数错误');
        }
        $status = '1';
        $msg = '';
        foreach ($field_arr as $k => $v) {
            if (!isset($params[$k]) || empty($params[$k])) {
                $status = '-1';
                $msg = $v . ' 不能为空';
                break;
            }
        }
        return $this->format_ret($status, array(), $msg);
    }

    /**
     * 生成kis单据号
     */
    private function create_record_code() {
        $sql = "SELECT id FROM {$this->table} ORDER BY id DESC";
        $id = $this->db->get_value($sql);
        if (!empty($id)) {
            $id = intval($id) + 1;
        } else {
            $id = 1;
        }
        require_lib('comm_util', true);
        $code = 'LSRB' . date("Ymd") . add_zero($id, 3);
        return $code;
    }

    /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg) {
        $fail_top = array('业务日期', '店铺', '仓库', '日报生成信息');
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, 'create_kis_daily');
//        $message = "，日报生成信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message = "，日报生成信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }

}
