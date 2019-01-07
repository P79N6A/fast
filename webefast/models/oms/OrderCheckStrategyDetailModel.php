<?php

require_model('tb/TbModel');
require_lang('sys');

class OrderCheckStrategyDetailModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }

    function get_table() {
        return 'order_check_strategy_detail';
    }

    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = '';
        $sql_main = "FROM {$this->table} $sql_join WHERE 1 and check_strategy_code='not_auto_confirm_with_goods'";
        //按商品编码
        if (isset($filter['content']) && $filter['content'] !== '') {
            $sql_main .= " AND content LIKE :content ";
            $sql_values[':content'] = "%" . $filter['content'] . "%";
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        foreach ($ret_data['data'] as $key => $value) {
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'goods_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['content'], $key_arr);
            $value = array_merge($value, $sku_info);
            $ret_data['data'][$key] = $value;
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    function add_goods($params) {
        $goods_arr = array();
        $check_strategy_code = $params['check_strategy_code'];
        $this->begin_trans();
//		$ret = $this->delete_all_goods($check_strategy_code);//当取消勾选时 涉及到删除，所以全部删除，重新插入
//		if ($ret['status'] != 1){
//			$this->rollback();
//			return $this -> format_ret("-1", '',"店铺设置失败");
//		}
        foreach ($params['data'] as $val) {
            $goods = array(
                'check_strategy_code' => $check_strategy_code,
                'content' => $val['sku'],
            );
            $goods_arr[] = $goods;
        }
        $ret = $this->insert_multi_exp($this->table, $goods_arr, true);
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $this->commit();
        return $ret;
    }

    /**
     * 删除纪录
     */
    function delete_goods($id) {
        $ret = parent::delete(array('id' => $id));
        return $ret;
    }

    function delete_all_goods($check_strategy_code) {
        $ret = parent::delete(array('check_strategy_code' => $check_strategy_code));
        return $ret;
    }

    function get_shoped_info() {
        $shop_info = $this->db->get_all("select * from {$this->table} where check_strategy_code = 'not_auto_confirm_with_shop'");
        return $shop_info;
    }

    function shop_do_add($params) {
        $shop_arr = array();
        $check_strategy_code = 'not_auto_confirm_with_shop';
        $this->begin_trans();
        $ret = $this->delete_all_goods($check_strategy_code); //当取消勾选时 涉及到删除，所以全部删除，重新插入

        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret("-1", '', "设置订单审核店铺失败");
        }
        foreach ($params['shop_code'] as $val) {
            $goods = array(
                'check_strategy_code' => $check_strategy_code,
                'content' => $val,
            );
            $shop_arr[] = $goods;
        }
        if (!empty($shop_arr)) {
            $ret = $this->insert_multi_exp($this->table, $shop_arr, true);
            if ($ret['status'] != 1) {
                $this->rollback();
            }
        }
        $this->commit();
        return $ret;
    }

    function detail_time() {
        $execut_time = $this->db->get_all("select * from {$this->table} where check_strategy_code='auto_confirm_time' group by content asc");
        return $execut_time;
    }

    function time_do_add($params) {
        $time_arr = array();
        $check_strategy_code = 'auto_confirm_time';
        $this->begin_trans();
        $ret = $this->delete_all_goods($check_strategy_code); //当取消勾选时 涉及到删除，所以全部删除，重新插入
        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret("-1", '', "设置自动执行时间失败");
        }
        foreach ($params['execut_time'] as $val) {
            if (empty($params['execut_time'][0]) || $params['execut_time'][0] == '00:00') {
                return $this->format_ret(-1, '', '时间设置初始不能为00:00');
            }
            if (empty($val) || $val == '00:00') {
                continue;
            }
// 			$check_ret = $this->check_time($val);//需要正则匹配时间 边界值
            $times = array(
                'check_strategy_code' => $check_strategy_code,
                'content' => $val,
            );
            $time_arr[] = $times;
        }
        $ret = $this->insert_multi_exp($this->table, $time_arr, true);
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $sql = "select is_active from order_check_strategy where check_strategy_code='auto_confirm_time'";
        $active = $this->db->get_value($sql);
        if ($active == 1) {
            $this->set_sys_schedule();
        }
        $this->commit();
        return $ret;
    }

    function set_sys_schedule() {
        $plan_exec_data = array();
        $sql = "select content  from order_check_strategy_detail where check_strategy_code=:check_strategy_code ";
        $sql_values = array(':check_strategy_code' => 'auto_confirm_time');
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as $val) {
            $plan_exec_data['time'][] = $val['content'];
        }

        if (!empty($plan_exec_data)) {
            load_model('sys/SysScheduleModel')->set_plan_exec_data('auto_confirm', $plan_exec_data);
        }
    }

    function protect_time() {
        $protect_time = $this->db->get_row("select content from {$this->table} where check_strategy_code='protect_time'");
        return $protect_time;
    }

    function protect_time_edit($params) {
        $params['protect_time'] = isset($params['protect_time']) ? trim($params['protect_time']) : 0;

        if ($params['protect_time'] == '') {
            return $this->format_ret(-1, '', '保护期时间不能为空');
        }
        $row = $this->db->get_row("select * from {$this->table } where check_strategy_code = 'protect_time'");
        if (!empty($row)) {
            $ret = parent::update(array('content' => $params['protect_time']), array('check_strategy_code' => 'protect_time'));
        } else {
            $data['content'] = $params['protect_time'];
            $data['check_strategy_code'] = 'protect_time';
            $ret = $this->insert($data);
        }
        return $ret;
    }

    function get_stored_info() {
        $store_info = $this->db->get_all("select * from {$this->table} where check_strategy_code = 'not_auto_confirm_with_store'");
        return $store_info;
    }

    function store_do_add($params) {
        $shop_arr = array();
        $check_strategy_code = 'not_auto_confirm_with_store';
        $this->begin_trans();
        $ret = $this->delete_all_goods($check_strategy_code); //当取消勾选时 涉及到删除，所以全部删除，重新插入

        if ($ret['status'] != 1) {
            $this->rollback();
            return $this->format_ret("-1", '', "设置订单审核仓库失败");
        }
        foreach ($params['store_code'] as $val) {
            $goods = array(
                'check_strategy_code' => $check_strategy_code,
                'content' => $val,
            );
            $shop_arr[] = $goods;
        }
        if (!empty($shop_arr)) {
            $ret = $this->insert_multi_exp($this->table, $shop_arr, true);
            if ($ret['status'] != 1) {
                $this->rollback();
            }
        }
        $this->commit();
        return $ret;
    }

    /**
     * 订单限制金额范围配置
     * @param array $params
     * @return array
     */
    public function order_money_edit($params) {
        $params['protect_time'] = isset($params['protect_time']) ? trim($params['protect_time']) : 0;

        if ($params['protect_time'] == '') {
            return $this->format_ret(-1, '', '保护期时间不能为空');
        }
        $row = $this->db->get_row("select * from {$this->table } where check_strategy_code = 'protect_time'");
        if (!empty($row)) {
            $ret = parent::update(array('content' => $params['protect_time']), array('check_strategy_code' => 'protect_time'));
        } else {
            $data['content'] = $params['protect_time'];
            $data['check_strategy_code'] = 'protect_time';
            $ret = $this->insert($data);
        }
        return $ret;
    }

    /**
     * 统一获取规则明细方法
     * @param string $strategy_code 规则代码
     * @param array $_type 1-一对一;2-一对多
     * @return array
     */
    public function get_strategy_detail($strategy_code) {
        $sql = "SELECT content FROM {$this->table} WHERE check_strategy_code=:_code";
        $data = $this->db->get_all_col($sql, [':_code' => $strategy_code]);
        $revert_data = [];
        if ($strategy_code === 'not_auto_confirm_with_money') {
            if (empty($data)) {
                $revert_data = [];
            } else {
                $data = explode(',', $data[0]);
                $revert_data = [
                    'min_money' => isset($data[0]) ? $data[0] : '',
                    'max_money' => isset($data[1]) ? $data[1] : '',
                ];
            }
        }

        return $revert_data;
    }

    /**
     * 规则明细统一设置方法
     * @param array $strategy_code 规则代码
     * @param array $param 明细数据
     * @return array
     */
    public function set_strategy_detail($strategy_code, $param) {
        if (empty($strategy_code) || empty($param)) {
            return $this->format_ret(-1, '', '参数有误,请刷新重试');
        }
        $data = [];
        if ($strategy_code == 'not_auto_confirm_with_money') {
            if (($param['min_money'] !== '' && !is_numeric($param['min_money'])) || ($param['max_money'] !== '' && !is_numeric($param['max_money']))) {
                return $this->format_ret(-1, '', '金额必须为正数');
            }
            if ($param['min_money'] === '' && $param['max_money'] === '') {
                $content = '';
            } else {
                $param['min_money'] = $param['min_money'] == '' ? '' : number_format($param['min_money'], 2, '.', '');
                $param['max_money'] = $param['max_money'] == '' ? '' : number_format($param['max_money'], 2, '.', '');
                $content = [$param['min_money'], $param['max_money']];
                $content = implode(',', $content);
            }
            $data['content'] = $content;
        }

        $sql = "SELECT COUNT(1) FROM {$this->table} WHERE check_strategy_code=:_code";
        $count = $this->db->get_value($sql, [':_code' => $strategy_code]);
        if ($count > 0) {
            $ret = parent::update($data, ['check_strategy_code' => $strategy_code]);
        } else {
            $data['check_strategy_code'] = $strategy_code;
            $ret = parent::insert($data);
        }

        return $ret;
    }

}
