<?php

require_lib('util/web_util', true);
require_lib('util/oms_util', true);
require_model('oms/SellReturnModel');
require_model('oms/SellReturnOptModel');
class sell_repair {
    /**
	 *
	 * 方法名       do_sell_list
	 *
	 * 功能描述     获取已经发货订单未将数据插入零售结算单的控制器
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-28
	 * @param       mixed &$request	 
     * @param       mixed &$response
	 * @param       mixed $app
	 */
    function do_sell_list(array &$request, array &$response, array &$app) {
    }
    
    /**
	 *
	 * 方法名       do_sell_repair
	 *
	 * 功能描述     数据处理控制器
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-28
	 * @param       mixed &$request	 
     * @param       mixed &$response
	 * @param       mixed $app
	 */
    function do_sell_repair(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        if (!isset($request['sell_record_code']) || empty($request['sell_record_code'])) {
            $response = array('status' => 1, 'data' => '', 'message' => lang('op_error_params'));
        } else {
            $r = load_model('oms/SellSettlementModel')->generate_settlement_data(trim($request['sell_record_code']), 1);
            if ($r['status'] != '1') {
                $response = array('status' => -1, 'data' => '', 'message' => lang('op_error'));
            } else {
                $response = array('status' => 1, 'data' => '', 'message' => lang('op_success'));
            }
        }
    }
    
    /**
	 *
	 * 方法名       do_return_list
	 *
	 * 功能描述     获取退款退货（已经收货）|仅退款订单未将数据插入零售结算单的控制器
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-28
	 * @param       mixed &$request	 
     * @param       mixed &$response
	 * @param       mixed $app
	 */
    function do_return_list(array &$request, array &$response, array &$app) {
    }
    
    /**
	 *
	 * 方法名       do_return_repair
	 *
	 * 功能描述     数据处理控制器
	 *
	 * @author      BaiSon PHP R&D
	 * @date        2015-07-28
	 * @param       mixed &$request	 
     * @param       mixed &$response
	 * @param       mixed $app
	 */
    function do_return_repair(array &$request, array &$response, array &$app) {
        $app['fmt'] = 'json';
        if (!isset($request['sell_return_code']) || empty($request['sell_return_code'])) {
            $response = array('status' => 1, 'data' => '', 'message' => lang('op_error_params'));
        } else {
            $r = load_model('oms/SellSettlementModel')->generate_settlement_data(trim($request['sell_return_code']), 2);
            if ($r['status'] != '1') {
                $response = array('status' => -1, 'data' => '', 'message' => lang('op_error'));
            } else {
                $response = array('status' => 1, 'data' => '', 'message' => lang('op_success'));
            }
        }
    }
}
