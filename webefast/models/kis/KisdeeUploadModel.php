<?php

require_model('tb/TbModel');

/**
 * 金蝶日报业务
 */
class KisdeeUploadModel extends TbModel {

    protected $method = 'kis.APP004088.acctplatform.AcctController.DealAcctPlatForm';
    private $record_type_name = array(
        'all' => '全部',
        'sell_record' => '销售订单',
        'sell_return' => '销售退单',
    );
    private $record_type;
    //日报生成状态
    private $_status = 1;
    //日报生成信息
    private $_message = array();

    /**
     * 单条上传
     * @param array $params 参数
     * @return array 上传结果
     */
    function upload_sell_daily($params) {
        $ret_check = load_model('kis/KisdeeModel')->check_params($params, array('record_code' => '单据编号', 'record_type' => '单据类型'));
        if ($ret_check['status'] != 1) {
            return $ret_check;
        }
        $this->record_type = $params['record_type'];

        static $api_params = NULL;
        if ($api_params == NULl) {
            $api_params = load_model('sys/KisdeeConfigModel')->get_api_params();
            $api_params['method'] = $this->method;
        }
        $params['p_id'] = $api_params['config_id'];
        try {
            if (empty($api_params)) {
                throw new Exception('金蝶配置未启用或配置有误', -1);
            }
            if (empty($api_params['server_url']) || empty($api_params['netid'])) {
                throw new Exception('金蝶接口地址有误，请返回配置重新测试连通', -1);
            }
            $record = load_model('kis/KisdeeModel')->get_info($params);
            if (empty($record)) {
                throw new Exception($params['record_code'] . '单据不存在', -1);
            }
            if (strtotime($record['record_date']) < $api_params['online_time']) {
                throw new Exception('业务日期不能小于应用上线日期', -1);
            }
            if (empty($record['store_name'])) {
                throw new Exception('仓库配置有误', -1);
            }
            $detail = load_model('kis/KisdeeDetailModel')->get_detail($params);
            if (empty($detail)) {
                throw new Exception('单据明细为空，不能上传', -1);
            }

            $recordset = array();
            array_walk($detail, function($val) use(&$recordset, $record) {
                $val = array_merge($val, $record);
                $val['amount_for'] = $val['amount'];
                $val['money_for'] = $val['money'];
                $val['currency_code'] = 'RMB';
                $val['exchange_rate'] = 1;
                $recordset[] = $this->auto_match($val);
            });
            $api_params['custdata']['Data']['Action'] = 'SyncBill';
            $api_params['custdata']['Data']['Recordset'] = $recordset;
            $api_params['custdata'] = json_encode($api_params['custdata']);
            $api_name = $api_params['method'];
            unset($api_params['method'], $api_params['online_time'], $api_params['config_id']);
            $result = load_model('api/kis/KisApiModel')->request_api($api_name, $api_params);
            $this->update_upload_status($result, $params['record_code']);
            if ($result['status'] != 1) {
                throw new Exception('上传失败，请至<上传失败>页签查看具体原因', $result['status']);
            }
            return $this->format_ret(1, '', '上传成功');
        } catch (Exception $e) {
            $msg = $e->getCode() == -1 ? $e->getMessage() : '上传失败，处理异常';
            return $this->format_ret($e->getCode(), '', $msg);
        }
    }

    /**
     * 匹配金蝶字段和值
     * @param array $data 匹配前数据
     * @return array 匹配后数据
     */
    function auto_match($data) {
        $record_type = $this->record_type;
        $field_data = load_model('kis/KisdeeFieldContrastModel')->get_bill_field_contrast($record_type);
        $field_val = $field_data['bill_field_val_match'];
        $field_contrast = $field_data[$record_type];
        $data_new = array();
        foreach ($data as $key => $val) {
            if (!isset($field_contrast[$key])) {
                continue;
            }
            $v = array();
            $field_arr = $field_contrast[$key];
            $field = $field_arr['field'];
            $match_type = $field_arr['match_type'];
            switch ($match_type) {
                case 1:
                    $v = $field_val[$field][$val];
                    break;
                case 2:
                    $v = isset($data_new[$field]) ? $data_new[$field] : $field_val[$field];
                    $v[$field_arr['index']] = $val;
                    break;
                default:
                    $v = $val;
                    break;
            }
            $data_new[$field] = $v;
        }
        return $data_new;
    }

    /**
     * 批量上传
     * @param array $params 参数
     * @return array 上传结果
     */
    function batch_upload_sell_daily($params) {
        $is_enable = load_model('sys/KisdeeConfigModel')->is_enable_config();
        if ($is_enable != 1) {
            return $this->format_ret(-1, '', '配置未启用，不能上传');
        }
        foreach ($params as $val) {
            $this->upload_sell_daily($val);
        }
        return $this->format_ret(1);
    }

    /**
     * 更新上传状态
     * @param array $data 更新信息
     * @param string $record_code 单据编号
     */
    function update_upload_status($data, $record_code) {
        $d = array();
        if ($data['status'] == 1) {
            $d['upload_status'] = 1;
            $d['fail_cause'] = '';
            $d['upload_time'] = time();
        } else {
            $d['upload_status'] = 2;
            $d['fail_cause'] = $data['message'];
        }
        $this->update_exp('kisdee_trade', $d, "record_code='{$record_code}'");
    }

}
