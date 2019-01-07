<?php

require_model('api/sap/SapApiModel');

class SapApiClientModel extends SapApiModel {

    protected $sapapi;

    function __construct() {
        $sap_data = load_model('sys/SapConfigModel')->get_by_sap_config();
        $api_param = array(
//            'ASHOST' => 'http://www.rifeng.cn:8081/api/api.php',
//            'SYSNR' => 'cb463721cc629be937b87fffbf78c771',
            'ASHOST' => $sap_data['sap_address'],
            'SYSNR' => $sap_data['instance_number'],
        );


        parent::__construct($api_param);
    }

    function zr_mes_kctb($params) {
        $parameters = array();  
        $parameters['ZMARK'] = isset($params['ZMARK'])?$params['ZMARK']:1;
        $ret = $this->request_api('ZR_MES_KCTB', $parameters);
        //将数组中的键改为小写
        $this->lowercase_letters($ret['data']['GZTMES_KCXX']);
        //组合数据
        foreach ($ret['data']['GZTMES_KCXX'] as &$val) {
            $download_date = date('Y-m-d H:i:s');
            $record_code = $out_trade_no = date('YmdHis') . mt_rand(000000, 999999);
            $val['record_code'] = $record_code;
            $val['num'] = (int) $val['menge'];
            $val['matnr'] = preg_replace('/^0+/','',$val['matnr']);
            $val['status'] = 0;
            $val['download_date'] = $download_date;
        }
        if (empty($ret['data']['GZTMES_KCXX'])) {
            return $this->format_ret(-1, '', '没有数据');
        }
        $ret = load_model('sys/SapAdjustRecordModel')->insert($ret['data']['GZTMES_KCXX'], $download_date);
//        var_dump($ret);
//        die;
        return $ret;
    }

    function lowercase_letters(&$data) {
        foreach ($data as $key => $val) {
            $data[$key] = array_change_key_case($val, CASE_LOWER);
        }
    }
   //
    function zr_send_order_to_sap($params = array()) {
//        $params['IT_DATA'][] = array(
//            'BSTKD' => '1600323235',//客户采购订单编号
//            'POSEX' => '1',//电商平台的销售订单行项目号
//            'AUART' => 'ZG01',//订单类型 ZG01销售，退货ZG02,ZGE1积分（正值都用ZGE1，包括积分），ZGE2调差（负值）
//            'KUNNR' => 'taobao001',//客户  店铺代码
//            'FKDAT' => '20161107',//开票日期 //发货时间  20161020 单据付款时间  到日期，不到时间
//            'MATNR' => '2323423',//产品代码 条码
//            'ARKTX' => '',//产品名称
//            'KWMENG' => '1.000',//数量
//            'VRKME' => '',//单位
//            'ACTPR' => '11.00',//对应行项目的实际销售（收款）单价
//            'WRBTR' => '11.00',//对应行项目的实际销售（收款）金额
//            'WAERK' => 'CNY',//交易货币，如人民币：CNY
//            'VFLNR' => '44324234324',   // 对应行项目开具给收票方的金税发票号
//        );
//           $params['IT_DATA'][] = array(
//            'BSTKD' => '1600323231',//客户采购订单编号
//            'POSEX' => '1',//电商平台的销售订单行项目号
//            'AUART' => 'ZG01',//订单类型 ZG01销售，退货ZG02,ZGE1积分（正值都用ZGE1，包括积分），ZGE2调差（负值）
//            'KUNNR' => 'taobao001',//客户  店铺代码
//            'FKDAT' => '20161107',//开票日期 //发货时间  20161020 单据付款时间  到日期，不到时间
//            'MATNR' => '2323423',//产品代码 条码
//            'ARKTX' => '',//产品名称
//            'KWMENG' => '1.000',//数量
//            'VRKME' => '件',//单位
//            'ACTPR' => '11.00',//对应行项目的实际销售（收款）单价
//            'WRBTR' => '11.00',//对应行项目的实际销售（收款）金额
//            'WAERK' => 'CNY',//交易货币，如人民币：CNY
//            'VFLNR' => '44',   // 对应行项目开具给收票方的金税发票号
//        );
          
        $ret = $this->request_api('ZR_SEND_ORDER_TO_SAP', $params);
//        var_dump($ret);die;
        return $ret;
    }
   
}
