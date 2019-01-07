<?php
/*
 * 营销中心-增值订购
 */
class valueorder_base {
    /** 日志记录模型 */
    public $log = null;
    
    /** 初始化 */
    public function __construct()
    {
        $this->log = load_model('market/ValueorderLogModel');
    }
    
    /**
     * 增值服务订购主页面
     */
    public function do_list(array & $request, array & $response, array & $app)
    {
    }
    
    /**
     * 新增增值服务订购页面
     */
    public function detail_add(array & $request, array & $response, array & $app)
    {
    }
    
     /**
     * 增值服务订购页面详情，可编辑
     */
    public function detail_edit(array & $request, array & $response, array & $app)
    {
        /** 产品明细查询 start */
        // 明细表有该订单数据则用明细表数据
        $data = load_model('market/ValueorderModel')->get_by_id($request['_id']);
        $order = $data['data'];
        $order += load_model('market/ValueorderBaseModel')->getOrderById($request['_id']);
        $status = array('已下单', '已付款', '已审核', '已作废');
        $order['val_status'] = $status[$order['val_status']];
        is_null($order['val_paydate']) and $order['val_paydate'] = '未付款';
        is_null($order['val_checkdate']) and $order['val_checkdate'] = '未审核';
        foreach($order as $k => &$v) {
            if (in_array($k, array(
                'val_standard_price', 'val_cheap_price', 'val_actual_price'
            ))) {
                $v = number_format($v, '2', '.' , '');
            }
        }
        unset($v);
        $response['order'] = $order;
        /** 产品明细查询 end */
        /** 产品列表 start */
        $pro = ds_get_select('chanpin',2);
        array_shift($pro);
        $response['pro'] = json_encode(array_map(function($row) {
            return array(
                'text'  => $row['cp_name'],
                'value' => $row['cp_id']
            );
        }, $pro));
        /** 产品列表 end */
        /** 产品版本列表 start */
        $ver = ds_get_select_by_field('product_version', 2);
        array_shift($ver);
        $response['ver'] = json_encode(array_map(function($row) {
            return array(
                'text'  => $row['1'],
                'value' => $row['0']
            );
        }, $ver));
        /** 产品版本列表 end */
        /** 产品增值服务明细 start */
        $response['valueorder'] = load_model('market/ValueorderValueserverModel')
                                  ->valueorderList($request['_id']);
        if (count($response['valueorder']) > 0) {
            foreach($response['valueorder'] as &$row) {
                $row['value_price'] = number_format($row['value_price'], '2', '.' , '');
            }
        }
        unset($row);
        /** 产品增值服务明细 end */
        /** 开放api增值服务附加按钮 start */
        foreach($response['valueorder'] as $row) {
            if ($row['vs_value_id'] == '73') {
                $get = load_model('market/OspValueauthKeyModel')
                        ->isApiExists($response['order']['val_kh_id']);
                $response['order']['api'] = $get['status'] ? $get['data'] : '';
            }
        }
        /** 开放api增值服务附加按钮 end */
    }
    
    /**
     * 增值服务添加
     */
    public function detail_valueorder_add(array & $request, array & $response, array & $app)
    {
        $m = load_model('market/ValueorderValueserverModel');
        if ($m->isAdded($request)) {
            exit_json_response(array('status' => 1, 'data' => true, 'message' =>'该增值服务已添加'));
        } else {
            $ret = $m->insert($request);
            $this->syncValueorder($request);
            $this->log->log($request['vs_val_num'], '添加增值服务订购明细', '0');
            exit_json_response($ret);
        }
    }
    
    /**
     * 增值服务编辑
     */
    public function detail_valueorder_edit(array & $request, array & $response, array & $app)
    {
        $request['vs_actual_price'] = $request['value_price'] - $request['vs_cheap_price'];
        unset($request['value_name']);
        unset($request['value_desc']);
        $ret = load_model('market/ValueorderValueserverModel')->update(
            $request,
            array('vs_value_id' => $request['vs_value_id'], 'vs_val_num' => $request['vs_val_num'])
        );
        $this->syncValueorder($request);
        $this->log->log($request['vs_val_num'], '修改增值服务订购明细', '0');
        exit_json_response($ret);
    }
    
    /**
     * 增值服务删除
     */
    public function detail_valueorder_delete(array & $request, array & $response, array & $app)
    {
        $ret = load_model('market/ValueorderValueserverModel')->delete($request);
        $this->syncValueorder($request);
        $this->log->log($request['vs_val_num'], '删除增值服务订购明细', '0');
        exit_json_response($ret);
    }
    
    /**
     * 新增增值服务订购页面表单处理
     */
    public function valueorder_add(array & $request, array & $response, array & $app)
    {
        $order = array_filter(get_array_vars($request, array(
            'val_channel_id',
            'val_kh_id',
            'val_cp_id',
            'val_pt_version',
            'val_seller',
            'val_desc'
        )));
        $get = load_model('market/ValueorderBaseModel')->valueorderAdd($order);
        $ret = load_model('market/ValueorderModel')->insertWithoutNum($get['order']);
        if($ret['status']){
            load_model('basedata/RdsDataModel')->update_kh_data($request['val_kh_id'],'0','osp_valueorder');
        }
        exit_json_response($ret);
    }
    
    
    /**
     * 编辑增值服务订购页面表单处理
     */
    public function valueorder_edit(array & $request, array & $response, array & $app)
    {
        /** 过滤不必要的信息 start */
        $edit = array();
        foreach ($request['parameter'] as $key => $val) {
            if (in_array($key, array(
                'val_desc',
                'val_cp_id',
                'val_channel_id',
                'val_kh_id',
                'val_seller',
                'val_pt_version'
            ))) {
                $edit[$key] = $val;
            }
        }
        $edit['val_num'] = $request['parameterUrl']['_id'];
        if (empty($edit['val_seller'])) {
            unset($edit['val_seller']);
        }
        /** 过滤不必要的信息 end */
        load_model('market/ValueorderBaseModel')->valueorderEdit($edit);
        $ret = load_model('market/ValueorderModel')->update($edit, $edit['val_num']);
        if($ret['status']){
            load_model('basedata/RdsDataModel')->update_kh_data($edit['val_kh_id'],'0','osp_valueorder');
        }
        $this->log->log($edit['val_num'], '修改增值服务订购', '0');
        exit_json_response($ret);
    }
    
    /**
     * 支付
     */
    function doPay(array & $request, array & $response, array & $app){
        if (isset($request['val_num'])) {
            if (!load_model('market/ValueorderValueserverModel')
                ->isAdded(array('vs_val_num' => $request['val_num']))) {
                exit_json_response(array('status'=>'0', 'data' => true, 'message' => '请先添加增值服务'));
            }
            $pay_update['val_pay_status'] = 1;
            $pay_update['val_paydate'] = date('Y-m-d H:i:s');
            $pay_stat = load_model('market/ValueorderModel')->update($pay_update,$request['val_num']);
            $pay_update['val_status'] = 1;
            unset($pay_update['val_pay_status']);
            load_model('market/ValueorderBaseModel')->update($pay_update,array('val_num' => $request['val_num']));
            load_model('market/ValueorderLogModel')->log($request['val_num'], '付款', '1');
            exit_json_response($pay_stat);
        }
    }
    
    /**
     * 审核
     */
    function doCheck(array & $request, array & $response, array & $app) {
        if (!empty($request['val_num'])) {
            $ret = load_model('market/ValueorderModel')->get_by_id($request['val_num']);
            $check_update['val_check_status'] = 1;
            $check_update['val_checkdate'] = date('Y-m-d H:i:s');
            $check_update['val_status'] = 2;
            load_model('market/ValueorderBaseModel')->update($check_update, array('val_num' => $request['val_num']));
            unset($check_update['val_status']);
            $check_stat = load_model('market/ValueorderModel')->update($check_update, $request['val_num']);
            load_model('market/ValueorderLogModel')->log($request['val_num'], '审核', '2');
            $valueauth = array();
            if (isset($ret['data'])) {
                //是否存在授权记录
                 $valauthinfo=array('vra_kh_id' => $ret['data']['val_kh_id'],
                                'vra_cp_id' => $ret['data']['val_cp_id'],
                                'vra_pt_version' => $ret['data']['val_pt_version'],
                                'vra_server_id' => $ret['data']['val_serverid']);
                $ret_auth = load_model('products/ValueorderauthModel')->get_values_info($valauthinfo);
                if (!empty($ret_auth['data'])) {
                    if (!empty($ret['data']['val_hire_limit'])) {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month ,strtotime($ret_auth['data']['vra_enddate'])));
                    } else {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    }
                    //$valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month));
                    $value_data = load_model('products/ValueorderauthModel')->update_value_auth($valueauth,$ret_auth['data']['vra_id']);
                } else {
                    if (!empty($ret['data']['val_hire_limit'])) {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+" . $ret['data']['val_hire_limit'] . month));
                    } else {
                        $valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    }
                    //$valueauth['vra_enddate'] = date('Y-m-d H:i:s', strtotime("+20 year"));
                    $valueauth['vra_kh_id'] = $ret['data']['val_kh_id'];
                    $valueauth['vra_cp_id'] = $ret['data']['val_cp_id'];
                    $valueauth['vra_pt_version'] = $ret['data']['val_pt_version'];
                    $valueauth['vra_startdate'] = date('Y-m-d H:i:s');
                    $valueauth['vra_server_id'] = $ret['data']['val_serverid'];
                    $valueauth['vra_state'] = '1';
                    $valueauth['vra_bz'] = $ret['data']['val_desc'];
                    $value_data = load_model('products/ValueorderauthModel')->insert_value_auth($valueauth);
                }
                if($value_data['status']){
                    load_model('basedata/RdsDataModel')->update_kh_data($valueauth['vra_kh_id'],'0','osp_valueorder_auth');
                }
                exit_json_response($value_data);
            }
        } else {
            exit_json_response(load_model('market/ValueorderModel')->format_ret("-1", '', '订购编号错误'));
        }
    }
    
    /**
     * 将订单作废
     */
    public function doAbate(array & $request, array & $response, array & $app)
    {
        $ret = load_model('market/ValueorderBaseModel')
            ->update(array('val_status' => 3),  $request);
        $ret['message'] = $ret['status'] == '1' ?  '作废成功' : '作废失败';
        $this->log->log($request['val_num'], '作废', '3');
        exit_json_response($ret);
    }
    
    public function addLog(array & $request, array & $response, array & $app)
    {
        $status = load_model('market/ValueorderBaseModel')
                ->getOrderField($request['val_num'], 'val_status');
        load_model('market/ValueorderLogModel')
                ->log($request['val_num'], $request['val_action'], $status, $request['val_remark']);
        exit_json_response(array('status' => 1, 'data' => true, 'message' => '填写成功'));
    }
    
    /**
     * 同步订购总金额，优惠和成交金额
     * @param array $request
     */
    private function syncValueorder($request)
    {
        load_model('market/ValueorderBaseModel')->valueorderEditDetail($request);
        load_model('market/ValueorderModel')->valueorderEditDetail($request);
    }
}
