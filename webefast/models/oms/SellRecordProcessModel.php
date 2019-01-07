<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class SellRecordProcessModel extends TbModel {

    private $process_status = array(
        'ACCEPT' => '仓库接单',
        'PRINT' => '打印',
        'PICK' => '捡货',
        'CHECK' => '复核',
        'PACKAGE' => '打包',
        'WEIGH' => '称重',
        'DELIVERED' => '已发货',
        'EXCEPTION' => '异常',
        'OTHER' => '其他'
    );

    function __construct() {
        parent::__construct('oms_sell_record_process');
    }

    /**
     * @todo 根据订单号获取订单仓库流水
     */
    public function get_order_process($code) {
        $select = 'process_status,operate_time,remark';
        $process = $this->get_info_by_wh($select, array('sell_record_code' => $code));
        if (empty($process)) {
            return $this->format_ret(-1, '', '暂未查询到该订单仓库流水');
        }
        foreach ($process as &$val) {
            $val['operate_time'] = date('Y-m-d H:i:s', $val['operate_time']);
            $val['process_status'] = $this->process_status[$val['process_status']];
        }
        return $this->format_ret(1, $process);
    }

    public function get_info_by_wh($select, $wh = array()) {
        $where = array();
        $sql_values = array();
        foreach ($wh as $k => $v) {
            $where[]= " {$k} = :{$k} ";
            $sql_values[":{$k}"] = $v;
        }
        $where = ' AND ' . implode(' AND ', $where);
        $sql = "SELECT {$select} FROM {$this->table} WHERE 1=1 {$where}";
        return $this->db->get_all($sql, $sql_values);
    }

    /**
     * @todo       API-订单流水通知
     * @desc       仓库推送订单流水状态到系统
     * @date       2016-08-17
     * @param      array $param
     *              array(
     *                  必选: 'sell_record_code','processstatus','operatetime'
     *                  可选: 'remark'
     *              )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":""}
     */
    public function api_order_process_report($param) {
        $key_required = array(
            's' => array(
                'sell_record_code', 'processstatus', 'operatetime'
            )
        );
        $arr_required = array();
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        if ($ret_required != TRUE) {
            return $this->format_ret('-10001', $ret_required['req_empty'], '必填参数为空');
        }
        $arr_deal = $arr_required;
        if (isset($param['remark'])) {
            $arr_deal['remark'] = $param['remark'];
        } else {
            $arr_deal['remark'] = '';
        }
        unset($arr_required, $param);
        $status = '-1';
        $data_msg = array();
        try {
            $time = strtotime($arr_deal['operatetime']);
            if ($time == FALSE || $time == -1) {
                $status = '0';
                $data_msg['operatetime'] = $arr_deal['operatetime'];
                throw new Exception('时间格式不正确');
            } else {
                $arr_deal['operatetime'] = $time;
            }

            $record = M('oms_sell_record')->get_row(array('sell_record_code' => $arr_deal['sell_record_code']));
            if (empty($record['data'])) {
                $status = '-10002';
                $data_msg['sell_record_code'] = $arr_deal['sell_record_code'];
                throw new Exception('订单号不存在');
            }

            $data['sell_record_code'] = $arr_deal['sell_record_code'];
            $data['process_status'] = $arr_deal['processstatus'];
            $data['operate_time'] = $arr_deal['operatetime'];
            $data['remark'] = $arr_deal['remark'];
            $ret = $this->insert($data);
            $aff_row = $this->affected_rows();
            if ($ret['status'] != 1 && $aff_row < 1) {
                throw new Exception('操作失败');
            }
            return $this->format_ret(1);
        } catch (Exception $e) {
            return $this->format_ret($status, $data_msg, $e->getMessage());
        }
    }

}
