<?php
require_model('tb/TbModel');
/**
 * 日志清除类
 */
class LogCleanUpModel extends TbModel
{
    /** 日志配置 */
    public $log_cfg   = null;
    /** 清除日志记录模型 */
    public $log_model = null;
    /** 清除记录日志 */
    public $log_log   = null;
    
    
    public function __construct($table = '', $pk = '', $db = '') {
        parent::__construct($table, $pk, $db);
        /** 加载配置 */
        // 定时清除
        // 特殊情况不能清除日志的时间设置
        $this->log_cfg   = require_conf('sys/log_clean_up');
        /** 加载最近清除日志操作记录 */
        $this->log_model = load_model('sys/LogCleanUpLogModel');
        $this->log_log   = $this->log_model->lastchanged();
    }
    
    /**
     * 是否允许清除日志
     * 1. 是否过了配置的时间
     * 2. 是否处于活动时间内
     * @param  string  $type
     * @return boolean
     */
    protected function allowCleanUp($type)
    {
        /** 对配置数组进行基础判断，如键名存不存在， */
        // 如果没有配置日志类型，不执行操作
        // 如果INTERVAL不设置，不执行操作
        // 如果ENABLE不设置，不执行操作
        if (!isset($this->log_cfg[$type])
            || !isset($this->log_cfg[$type]['INTERVAL'])
            || !isset($this->log_cfg[$type]['ENABLE'])) {
            return false;
        }
        /** 判断是否开启,如果没有开启，不执行操作 */
        if (!array_search($this->log_cfg[$type]['ENABLE'], array('0', '1'))) {
            return false;
        }
        /** 优先判断，是否处于拒绝清除操作的时间（应用场景比如说活动时间） */
        if ($this->isDeny($type)) {
            return false;
        }
        /** 查看是否已经过了保留日志时间 */
//        if (isset($this->log_log[$type])) {
//            $one_day  = 24 * 3600;
//            $interval = $this->log_cfg[$type]['INTERVAL'] * $one_day;
//            $deadline = $this->log_log[$type] + $interval;
//            if ( $deadline > $_SERVER['REQUEST_TIME']) {
//                return false;
//            }
//        }
        /** INTERVAL设置为0不予执行 */
        if ($this->log_cfg[$type]['INTERVAL'] == '0') {
            return false;
        }
        /** 1000个为一批处理 */
        $this->log_cfg[$type] += array('GROUP' => '1000');
        return true;
    }
    
    
    /**
     * 拒绝清除操作的时段
     * @param  string  $type
     * @return boolean
     */
    protected function isDeny($type)
    {
        if (isset($this->log_cfg[$type]['DENY'])) {
            foreach((array)$this->log_cfg[$type]['DENY'] as $row) {
                $period = array_filter(array_slice(array_map('strtotime',
                        (array)explode('|', $row)), 0, 2));
                if(count($period) == 0) {
                    return false;
                }
                count($period) < 2 and $period[] = $period[0] + 24*3600;
                if ($_SERVER['REQUEST_TIME'] >= min($period)
                    && $_SERVER['REQUEST_TIME'] < max($period)){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 清除日志，并且记录该操作
     * @param string $type
     */
    protected function cleanUpAndLog($type, $closure)
    {
        /** 查看是否允许清除 */
        if (!$this->allowCleanUp($type)) {
            return ;
        }
        $top      = strtotime('today') - $this->log_cfg[$type]['INTERVAL'] * 24 * 3600;
        $group    = $this->log_cfg[$type]['GROUP'];
        while ($msg = $closure($group, date('Y-m-d H:i:s', $top))){}
        $this->log_model->insertLog($type, 1, '清除成功'); // 清除成功
    }
    
    /**
     * 清除所有日志
     */
    public function allCleanUp()
    {
        $this->orderLogCleanUp();
        $this->standardLogCleanUp();
        $this->sysLogCleanUp();
        $this->loginLogCleanUp();
        $this->apiOrderCleanUp();
        $this->apiTaobaoCleanUp();
        $this->apiTaobaoTraceCleanUp();
        $this->wmsOmsCleanUp();
        $this->goodsInvApiCleanUp();
        $this->apiOrderSendCleanUp();
        $this->omsDeliverCleanUp();
    }
    
    /**
     * 订单操作日志清除
     */
    public function orderLogCleanUp()
    {
        $this->cleanUpAndLog('ORDER_LOG', function ($group, $top) {
            static $i = 0;
            $start = $i * $group;
            $sql = "SELECT `sell_record_code` "
                 . "FROM `oms_sell_record` "
                 . "WHERE (`shipping_status` = '4' AND `delivery_time` < '{$top}') "
                 . "OR (`order_status` = '3' AND `lastchanged` < '{$top}')"
                 . "LIMIT {$start}, {$group}";
            $i++;
            $get = array_map(function ($row) {
                return $row['sell_record_code'];
            }, $this->db->getAll($sql));
            $in = count($get) > 0 
                ? ' WHERE `sell_record_code` IN ("'.implode('","', $get).'")'
                : '';
            if (!empty($in)) {
                $sql2 = "DELETE FROM `oms_sell_record_action`{$in}";
                $this->db->query($sql2);
                return true;
            } else {
                return false;
            }
        });
    }
    
    /**
     * 标准日志接口日志清除
     */
    public function standardLogCleanUp()
    {
        $this->cleanUpAndLog('STANDARD_LOG', function ($group, $top) {
            $sql1 = "DELETE FROM `api_logs` "
                  . "WHERE `add_time` < '{$top}' "
                  . "LIMIT {$group}";
            $sql2 = "DELETE FROM `api_open_logs` "
                  . "WHERE `add_time` < '{$top}' "
                  . "LIMIT {$group}";
            $this->db->query($sql1);
            $cnt = $this->affected_rows();
            $this->db->query($sql2);
            $cnt += $this->affected_rows();
            return $cnt != 0;
        });
    }
    
    /**
     * 系统订单日志清除
     */
    public function sysLogCleanUp()
    {
        $this->cleanUpAndLog('SYS_LOG', function ($group, $top) {
            $sql = "DELETE FROM `sys_operate_log` "
                 . "WHERE `add_time` < '{$top}' LIMIT {$group}";
            $this->db->query($sql);
            return 0 != $this->affected_rows();
        });
    }
    
    /**
     * 登录日志清除
     */
    public function loginLogCleanUp()
    {
        $this->cleanUpAndLog('LOGIN_LOG', function ($group, $top) {
            $sql = "DELETE FROM `sys_login_log` "
                 . "WHERE `add_time` < '{$top}' LIMIT {$group}";
            $this->db->query($sql);
            return 0 != $this->affected_rows();
        });
    }
    
    
     /**
     *   api_order/
      *  api_order_detail 清除
     */
    public function apiOrderCleanUp() {
        $this->cleanUpAndLog('API_ORDER', function ($group, $top) {
            $sql_1 = "SELECT tid FROM api_order r1 "
                    . "WHERE r1.order_first_insert_time < '{$top}' AND (r1.is_change=1 OR r1.status=0) "
                    . "LIMIT {$group}";
            $tid = array();
            //依次查询交易号
            $result = $this->db->get_all($sql_1);
            foreach ($result as $value) {
                $tid[] = $value['tid'];
            }
            if (!empty($tid)) {
                $tid_str = deal_array_with_quote($tid);
                //删除主表
                $sql_2 = "DELETE FROM `api_order` "
                        . "WHERE `tid` IN ({$tid_str})";
                $this->db->query($sql_2);
                $cnt = $this->affected_rows();
                //删除明细表        
                $sql_3 = "DELETE FROM `api_order_detail` WHERE tid IN ({$tid_str})";
                $this->db->query($sql_3);
                return $cnt != 0;
            } else {
                return false;
            }
        });
    }
    
     /**
     *    api_taobao_trade/
      *   api_taobao_order 清除
     */
    public function apiTaobaoCleanUp(){
        $this->cleanUpAndLog('API_TAOBAO', function ($group, $top) {
            $sql_1 = "SELECT tid FROM api_taobao_trade r1 "
                    . "WHERE r1.end_time < '{$top}' AND r1.end_time<>'0000-00-00 00:00:00' "
                    . "LIMIT {$group}";
            $tid = array();
            //查询交易号
            $result = $this->db->get_all($sql_1);
            foreach ($result as $value) {
                $tid[] = $value['tid'];
            }
            if (!empty($tid)) {
                $tid_str = deal_array_with_quote($tid);
                //删除主表
                $sql_2 = "DELETE FROM `api_taobao_trade` "
                        . "WHERE `tid` IN ({$tid_str})";
                $this->db->query($sql_2);
                $cnt = $this->affected_rows();
                //删除明细表        
                $sql_3 = "DELETE FROM `api_taobao_order` WHERE tid IN ({$tid_str})";
                $this->db->query($sql_3);
                return $cnt != 0;
            } else {
                return false;
            }
        });
    }
    
    /**
     * api_taobao_trade_trace清除
     */
    public function apiTaobaoTraceCleanUp(){
        $this->cleanUpAndLog('API_TAOBAO_TRACE', function ($group, $top) {
            $sql = "DELETE FROM `api_taobao_trade_trace` "
                    . "LIMIT {$group}";
            $this->db->query($sql);
            $cnt = $this->affected_rows();
            return $cnt != 0;
        });
    }

    /**
     *    wms_oms_trade/
     *    wms_oms_order/
     *    wms_oms_log清除
     */
    public function wmsOmsCleanUp() {
        $this->cleanUpAndLog('WMS_OMS', function ($group, $top) {
            $sql_1 = "SELECT record_code FROM wms_oms_trade r1 "
                    . "WHERE  (r1.process_time< '{$top}' AND r1.process_flag=30) OR (r1.cancel_flag=1 AND  r1.lastchanged < '{$top}') "             
                    . "LIMIT {$group}";
            $record_code = array();
            //查询订单号
            $result = $this->db->get_all($sql_1);
            foreach ($result as $value) {
                $record_code[] = $value['record_code'];
            }
            if (!empty($record_code)) {
                $record_code_str = deal_array_with_quote($record_code);
                //删除wms_oms_trade表
                $sql_2 = "DELETE FROM `wms_oms_trade` "
                        . "WHERE record_code IN ({$record_code_str})";
                $this->db->query($sql_2);
                $cnt = $this->affected_rows();
                //删除wms_oms_order,wms_oms_log表        
                $sql_3 = "DELETE FROM `wms_oms_order` WHERE record_code IN ({$record_code_str}) ";
                $this->db->query($sql_3);
                $sql_4 = "DELETE FROM `wms_oms_log` WHERE record_code IN ({$record_code_str})";
                $this->db->query($sql_4);
                return $cnt != 0;
            } else {
                return false;
            }
        });
    }
    
    
     /**
     * goods_inv_api_sync_log清除
     */
    public function goodsInvApiCleanUp(){
        $this->cleanUpAndLog('GOODS_INV_API', function ($group, $top) {
            $sql = "DELETE FROM `goods_inv_api_sync_log` "
                    ." WHERE inv_update_time < '{$top}' "
                    . " LIMIT {$group}";
            $this->db->query($sql);
            $cnt = $this->affected_rows();
            return $cnt != 0;
        });
    }
    
     /**
     * api_order_send清除
     */
    public function apiOrderSendCleanUp(){
        $this->cleanUpAndLog('API_ORDER_SEND', function ($group, $top) {
            $sql = "DELETE FROM `api_order_send` "
                    ." WHERE send_time<'{$top}' AND (status=1 OR status=2) "
                    ." LIMIT {$group}";
            $this->db->query($sql);
            $cnt = $this->affected_rows();
            return $cnt != 0;
        });
    }

    /**
     *oms_deliver_record清除
     */
    public function omsDeliverCleanUp() {
        $this->cleanUpAndLog('OMS_DELIVER', function ($group, $top) {
            //查询波次主单
            $sql = "SELECT waves_record_id FROM oms_waves_record WHERE (is_cancel=1 OR is_deliver=1) AND record_time<'{$top}' LIMIT {$group}";
            $waves_record_id_arr = $this->db->get_all_col($sql);
            if (empty($waves_record_id_arr)) {
                return false;
            }
            $sql_value_wave = array();
            $waves_record_id_str = $this->arr_to_in_sql_value($waves_record_id_arr, 'waves_record_id', $sql_value_wave);
            //删除波次主单
            $wave_sql = "DELETE FROM oms_waves_record WHERE waves_record_id IN ({$waves_record_id_str})";
            $ret = $this->query($wave_sql, $sql_value_wave);
            $cnt = $this->affected_rows();
            //删除关联的订单
            $deliver_sql = "DELETE FROM oms_deliver_record WHERE waves_record_id IN ({$waves_record_id_str})";
            $ret = $this->query($deliver_sql, $sql_value_wave);
            //删除订单明细
            $deliver_detail_sql = "DELETE FROM oms_deliver_record_detail WHERE waves_record_id IN ({$waves_record_id_str})";
            $ret = $this->query($deliver_detail_sql, $sql_value_wave);
            return $cnt != 0;
        });
    }

}

