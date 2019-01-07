<?php

require_lib('util/web_util', true);
require_lib('business_util', true);

/**
 * 成本月结单业务
 */
class cost_month {

    /**
     * @todo 月结单列表 
     */
    function do_list(array & $request, array & $response, array & $app) {
        
    }

    /**
     * @todo 创建月结单 
     */
    function detail(array & $request, array & $response, array & $app) {
        $response['store'] = load_model('base/StoreModel')->get_select(0); //月结统计仓库
        $response['data']['record_code'] = load_model('acc/CostMonthModel')->create_fast_bill_sn(); //月结单号
    }

    /**
     * @todo 月结单详情 
     */
    function view(array & $request, array & $response, array & $app) {
        //主单据信息
        $ret = load_model('acc/CostMonthModel')->get_by_field('record_code', $request['record_code']);

        //根据仓库代码匹配仓库
        $store_name = load_model('base/StoreModel')->get_store_by_code_arr($ret['data']['store_code']);
        $ret['data']['store_name'] = implode('，', $store_name);

        $ok = get_theme_url('images/ok.png');
        $no = get_theme_url('images/no.gif');
        if ($ret['data']['is_check'] == '1') {
            $is_check_src = $ok;
        } else {
            $is_check_src = $no;
        }
        $ret['data']['is_check_src'] = "<img src='{$is_check_src}'>";

        $response['data'] = $ret['data'];
    }

    /**
     * @todo 新增月结单
     */
    function do_add(array & $request, array & $response, array & $app) {
        $cost_month = get_array_vars($request, array('record_code', 'ymonth', 'store_code', 'remark'));
        $ret = load_model('acc/CostMonthModel')->insert_cost_month($cost_month);
        exit_json_response($ret);
    }

    /**
     * @todo 删除月结单
     */
    function do_delete(array & $request, array & $response, array & $app) {
        $ret = load_model('acc/CostMonthModel')->delete($request['cost_month_id']);
        exit_json_response($ret);
    }

    /**
     * @todo 获取子明细
     */
    function get_detail_list_by_id(array & $request, array & $response, array & $app) {
        $data = load_model("acc/CostMonthDetailModel")->get_by_mx_id($request['cost_yj_mx_id'], $request['ymonth']);
        $response = array('rows' => $data);
    }

    /**
     * @todo 更新确认状态
     */
    function update_sure(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('acc/CostMonthModel')->update_sure($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * @todo 更新审核状态
     */
    function update_check(array &$request, array &$response, array &$app) {
        $arr = array('enable' => 1, 'disable' => 0);
        $ret = load_model('acc/CostMonthModel')->update_check($arr[$request['type']], $request['id']);
        exit_json_response($ret);
    }

    /**
     * @todo 修改月结单主单据
     */
    function do_edit(array &$request, array &$response, array &$app) {
        $ret = load_model('acc/CostMonthModel')->edit_action($request['parameter'], array('record_code' => $request['parameterUrl']['record_code']));
        exit_json_response($ret);
    }

    /**
     * @todo 修改月结单明细
     */
    function do_edit_detail(array &$request, array &$response, array &$app) {
        $cost_month_detail = get_array_vars($request, array('record_code', 'goods_code', 'adjust_cost', 'cost_yj_mx_id', 'ymonth'));
        $ret = load_model('acc/CostMonthDetailModel')->edit_detail_action($cost_month_detail);
        exit_json_response($ret);
    }

    /**
     * @todo 月结单销售成本维护
     */
    function cost_oam(array &$request, array &$response, array &$app) {
        $cost_month = get_array_vars($request, array('record_code', 'ymonth', 'id', 'store_code'));
        $ret = load_model('acc/CostMonthDetailModel')->cost_oam($cost_month);
        exit_json_response($ret);
    }

    /**
     * @todo 月结单明细数据刷新
     */
    function data_refresh(array &$request, array &$response, array &$app) {
        $cost_month = get_array_vars($request, array('record_code', 'ymonth', 'id', 'store_code'));
        $ret = load_model('acc/CostMonthDetailModel')->data_refresh($cost_month);
        exit_json_response($ret);
    }

    /**
     * @todo 导出月结单明细
     */
    function export_csv_list(array &$request, array &$response, array &$app) {
        $filter['record_code'] = $request['record_code'];
        $filter['ymonth'] = $request['ymonth'];
        $filter['page'] = 1;
        $filter['page_size'] = 10000;
        $filter['ctl_type'] = 'export';
        $res = load_model('acc/CostMonthDetailModel')->get_by_page($filter);

        $detail_result = $res['data']['data'];

        $str = "月结月份,月结单号,月结仓库,商品编码,商品名称,期初数,期初成本单价,期初成本金额,采购进货数,采购金额,采购退货数,网络销售数,网络退货数,批发销货单数,批发退货数,库存调整数,移仓入库数,移仓出库数,期末数,期末成本单价,调整成本,期末调整后成本单价,期末成本金额\n";
        $str = iconv('utf-8', 'gbk', $str);
        foreach ($detail_result as $value) {
            $value['goods_name'] = iconv('utf-8', 'gbk', $value['goods_name']);
            $value['store_name'] = iconv('utf-8', 'gbk', $value['store_name']);

            $str .= $request['ymonth'] . "\t," . $value['record_code'] . "\t," . $value['store_name'] . "\t," . $value['goods_code'] . "\t," . $value['goods_name'] . "\t," . $value['begin_num'] . "\t," . $value['begin_cost'] . "\t," . $value['begin_amount'] . "\t," . $value['purchase'] . "\t," . $value['pur_money'] . "\t," . $value['pur_return'] . "\t," . $value['oms_sell_record'] . "\t," . $value['oms_sell_return'] . "\t," . $value['wbm_store_out'] . "\t," . $value['wbm_return'] . "\t," . $value['adjust'] . "\t," . $value['shift_in'] . "\t," . $value['shift_out'] . "\t," . $value['end_num'] . "\t," . $value['end_cost'] . "\t," . $value['adjust_cost'] . "\t," . $value['end_adjust_cost'] . "\t," . $value['end_amount'] . "\n"; //用引文逗号分开  
        }
        $filename = 'cost_month_detail_' . $request['ymonth'] . '.csv'; //设置文件名  
        $this->export_csv($filename, $str); //导出  
    }

    /**
     * @todo 导出excel文件
     */
    function export_csv($filename, $data) {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;
        die;
    }

    /**
     * @todo 导入
     */
    function import_cost(array & $request, array & $response, array & $app) {
        $response['record_code'] = $request['record_code'];
        $response['ymonth'] = $request['ymonth'];
    }

    /**
     * @todo 导入商品成本
     * @describe type-adjust_cost:以调整成本导入
     * @describe type-end_adjust_cost:以调整后成本单价导入
     */
    function import_upload(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $file = $request['url'];
        if (empty($file)) {
            $response = array(
                'status' => 0,
                'type' => '',
                'msg' => "请先上传文件"
            );
        }
        $param = get_array_vars($request, array('record_code', 'ymonth', 'type'));
        $ret = load_model('acc/CostMonthDetailModel')->import_detail($param, $file);
        $response = $ret;
        $response['url'] = $_FILES['fileData']['name'];

    }

}
