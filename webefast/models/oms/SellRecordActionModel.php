<?php
/**
 * 订单日志相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');

class SellRecordActionModel extends TbModel {
    /**
     * @var string 订单日志表
     */
    protected $table = 'oms_sell_record_action';

    private $link_state_arr;
    public $record_log_check = 1;
    //订单状态
    public $order_status = array(
        0 => '未确认',
        1 => '已确认',
        3 => '已作废',
        5 => '已完成',
    );
    //付款状态
    public $pay_status = array(
        0 => '未付款',
        2 => '已付款',
    );
    //发货状态
    public $shipping_status = array(
        0 => '未发货',
        1 => '已通知配货',
        2 => '拣货中',
        3 => '已完成拣货',
        4 => '已发货',
    );
    function get_by_page($filter) {
        //"select * from oms_sell_record_action where sell_record_code = :sell_record_code order by sell_record_action_id desc", array('sell_record_code' => $filter(sell_record_code)));
        $sql_main = " FROM {$this->table} r1 WHERE 1 ";
        if(isset($filter['sell_record_code']) && !empty($filter['sell_record_code'])){
            $sql_main .= " AND sell_record_code = :sell_record_code ";
            $sql_values[':sell_record_code'] = $filter['sell_record_code'];
        }
        $sql_main .= " ORDER BY r1.sell_record_action_id DESC";
        $select =" * ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$action){
            $action['order_status'] = $this->order_status[$action['order_status']];
            $action['shipping_status'] = $this->shipping_status[$action['shipping_status']];
            $action['pay_status'] = $this->pay_status[$action['pay_status']];
        }
        return $this->format_ret(1, $data);
    }
    /**
     * 写入订单日志
     * @param $sellRecordCode
     * @param $actionName
     * @param string $actionNote
     * @param bool $isDeamon
     * @return array
     * @throws Exception
     */
    function add_action($sellRecordCode, $actionName, $actionNote = '', $isDeamon = false){
        $sql = "SELECT order_status, shipping_status, pay_status
        FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('sell_record_code' => (string)$sellRecordCode));
        if(empty($record)){
            return $this->format_ret(-1,'','订单不存在:'.$sellRecordCode);
        }

        $log = array();
        $log['sell_record_code'] = $sellRecordCode;
        $log['order_status'] = $record['order_status'];
        $log['shipping_status'] = $record['shipping_status'];
        $log['pay_status'] = $record['pay_status'];
        $log['action_name'] = $actionName;
        $log['action_note'] = $actionNote;

        if($isDeamon == false && CTX()->app['mode'] == 'cli') {
            $isDeamon = true;
        }

        if($isDeamon){
            $log['user_code'] = load_model('sys/UserTaskModel')->get_user_code();
            $log['user_name'] =  load_model('sys/UserTaskModel')->get_user_name();
            $log['user_code'] = !empty($log['user_code'])?$log['user_code']:'计划任务';
            $log['user_name'] = !empty($log['user_name'])?$log['user_name']:'计划任务';
        } else {
            $log['user_code'] = CTX()->get_session('user_code');
            $log['user_name'] = CTX()->get_session('user_name');
        }


        if($this->record_log_check !=0){
            if(empty($log['user_code']) || empty($log['user_name'])){
                return $this->format_ret(-1,'','用户登录数据错误');
            }
        }else{
            $log['user_code'] = 'api';
            $log['user_name'] = 'OPENAPI';
        }

        $ret = $this->insert($log);
        if($ret['status'] != 1){
            return $this->format_ret(-1,'','保存日志出错');
        }
    }
    
    /**
     * 写入订单日志-接口使用
     */
    function api_add_action($sellRecordCode, $log_data) {
        $sql = "SELECT order_status, shipping_status, pay_status
        FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('sell_record_code' => $sellRecordCode));
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在:' . $sellRecordCode);
        }

        $log = array();
        $log['sell_record_code'] = $sellRecordCode;
        $log['order_status'] = $record['order_status'];
        $log['shipping_status'] = $record['shipping_status'];
        $log['pay_status'] = $record['pay_status'];
        $log['action_name'] = $log_data['action_name'];
        $log['action_note'] = $log_data['action_note'];

        if (empty($log_data['user_code'])) {
            $log['user_code'] = 'api';
            $log['user_name'] = 'OPENAPI';
        } else {
            $log['user_code'] = $log_data['user_code'];
            $log['user_name'] = $log_data['user_name'];
        }

        $ret = $this->insert($log);
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '保存日志出错');
        }
    }

    /**
     * 通过field_name查询
     * @param $field_name
     * @param $value
     * @param string $select ：查询返回字段
     * @internal param $ $ :查询field_name
     * @return array (status, data, message)
     */
	public function get_by_field($field_name,$value, $select = "*") {

		$sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";
		$data = $this -> db -> get_row($sql, array(":{$field_name}" => $value));
		if ($data) {
			return $this -> format_ret('1', $data);
		} else {
			return $this -> format_ret('-1', '', 'get_data_fail');
		}
	}

    function add_action_info($record, $actionName, $actionNote = '', $isDeamon = false) {
        $sql = "SELECT sell_record_code,order_status, shipping_status, pay_status FROM oms_sell_record WHERE sell_record_code = :sell_record_code";
        $record = $this->db->get_row($sql, array('sell_record_code' => $record['sell_record_code']));
        if (empty($record)) {
            return $this->format_ret(-1, '', '订单不存在:' . $record['sell_record_code']);
        }

        $log = array();
        $log['sell_record_code'] = $record['sell_record_code'];
        $log['order_status'] = $record['order_status'];
        $log['shipping_status'] = $record['shipping_status'];
        $log['pay_status'] = $record['pay_status'];
        $log['action_name'] = $actionName;
        $log['action_note'] = $actionNote;
        if($isDeamon == false && ctx()->app['mode'] == 'cli') {
            $isDeamon = true;
        }
        if($isDeamon){
            $log['user_code'] = load_model('sys/UserTaskModel')->get_user_code();
            $log['user_name'] =  load_model('sys/UserTaskModel')->get_user_name();
            $log['user_code'] = !empty($log['user_code'])?$log['user_code']:'计划任务';
            $log['user_name'] = !empty($log['user_name'])?$log['user_name']:'计划任务';
        } else {
            $log['user_code'] = ctx()->get_session('user_code');
            $log['user_name'] = ctx()->get_session('user_name');
        }
        if(empty($log['user_code']) || empty($log['user_name'])){
            return $this->format_ret(-1,'','用户登录数据错误');
        }
        $ret = $this->insert($log);
        if($ret['status'] != 1){
            return $this->format_ret(-1,'','保存日志出错');
        }
        return $this->format_ret(1);
    }

	function get_state_map($sys_state){
		if (!isset($this->link_state_arr)){
			$sql = "select link_state from state_map where sys_state = :sys_state";
			$this->link_state_arr = ctx()->db->get_all_col($sql,array(':sys_state'=>$sys_state));
		}
		return $this->link_state_arr;
	}

    public function add_action_to_api($channel, $shopCode, $dealCodeList,$status){
        if($channel != 'taobao'){
            return $this->format_ret(1);
        }
        $deal_code_arr = explode(',',$dealCodeList);
        if (empty($deal_code_arr)){
            return $this->format_ret(-1,'','交易号为空');
        }
        $link_state_arr = $this->get_state_map($status);
        if (empty($link_state_arr)){
            return $this->format_ret(-1,'',$status.'状态不存在');
        }
        $d = array();
		foreach($link_state_arr as $_link_state){
			foreach($deal_code_arr as $_deal_code){
		        $d[] = array(
		            'shop_code' => $shopCode,
		            'tid' => $_deal_code,
		            'status' => $_link_state,
		            'action_time' => date('Y-m-d H:i:s'),
		            'remark' => '',
		        );
			}
		}
        $r = M('api_taobao_trade_trace')->insert_multi($d,true);
		return $r;
    }

    /**
     * 订单操作日志查询
     * @author wmh
     * @date 2016-01-03
     * @param array $param
     * <pre> 可选: 'record_code','start_lastchanged','end_lastchanged','page','page_size'
     * @return array 操作结果
     */
    public function api_order_log_get($param) {
        //可选字段
        $key_option = array(
            's' => array(
                'sell_record_code', 'start_lastchanged', 'end_lastchanged'
            ),
            'i' => array('page', 'page_size')
        );
        $arr_option = array();
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $arr_option);

        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }
        unset($param);

        $select = 'sa.`sell_record_code`, sa.`user_name`, sa.`order_status`, sa.`shipping_status`, sa.`pay_status`, sa.`action_name`, sa.`action_note`, sa.`lastchanged`';
        $sql_main = " FROM {$this->table} sa WHERE 1=1";
        //绑定数据
        $sql_values = array();
        if (isset($arr_option['sell_record_code']) && !empty($arr_option['sell_record_code'])) {
            $sql_main .= " AND sa.sell_record_code=:sell_record_code";
            $sql_values[":sell_record_code"] = $arr_option['sell_record_code'];
        } else {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days -1 seconds"));
            $sql_main .= " AND sa.lastchanged>=:start_lastchanged ";
            $sql_main .= " AND sa.lastchanged<=:end_lastchanged ";
            if (isset($arr_option['start_lastchanged']) && strtotime($arr_option['start_lastchanged']) === FALSE) {
                return $this->format_ret(-10005, array('start_lastchanged' => $arr_option['start_lastchanged']), '日期格式不正确');
            }
            if (isset($arr_option['end_lastchanged']) && strtotime($arr_option['end_lastchanged']) === FALSE) {
                return $this->format_ret(-10005, array('end_lastchanged' => $arr_option['end_lastchanged']), '日期格式不正确');
            }
            $sql_values[':start_lastchanged'] = isset($arr_option['start_lastchanged']) ? $arr_option['start_lastchanged'] : $start_time;
            $sql_values[':end_lastchanged'] = isset($arr_option['end_lastchanged']) ? $arr_option['end_lastchanged'] : $end_time;
        }

        $sql_main .= " ORDER BY sa.sell_record_code,sa.lastchanged";
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);
        foreach ($ret['data'] as &$row) {
            $row['order_status'] = $this->order_status[$row['order_status']];
            $row['shipping_status'] = $this->shipping_status[$row['shipping_status']];
            $row['pay_status'] = $this->pay_status[$row['pay_status']];
        }
        $data = array();
        $data['record_count'] = $ret['filter']['record_count'];
        $data['data'] = $ret['data'];

        return $this->format_ret(1,$data);
    }

}
