<?php

/**
 * Description of sell_record_ajust
 *
 * 单据库存调剂
 * @author wq
 */
require_lib('util/web_util', true);

class sell_record_ajust {

    function detail(array &$request, array &$response, array &$app) {
        $response['data'] = load_model('oms/SellRecordModel')->get_record_by_code($request['record_code']);
        $ret = load_model("oms/SellRecordInvAdjustModel")->get_record_short_detail($request['record_code']);
        $short_list = array();
        foreach ($ret['data'] as $val) {
            $short_num = $val['num'] - $val['lock_num'];
            $key = $val['sku'] . "," . $short_num;
            $short_list[$key]['sku'] = $val['sku'];
            $short_list[$key]['name'] = "商品编码：{$val['goods_code']} ；规格：{$val['spec1_name']}，{$val['spec2_name']}；缺货数：{$short_num}";
        }
        $response['short_list'] = $short_list;
    }

    function inv_adjust(array &$request, array &$response, array &$app) {
        $response = load_model("oms/SellRecordInvAdjustModel")->inv_adjust($request['record_code'], $request['sku'], $request['by_record_code'] );
    }

}
