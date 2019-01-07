<?php

require_model('api/mes/MesApiModel');

class MesClientModel extends MesApiModel {

    protected $conf = array();

    //todo:缺少查询配置文件
    function __construct($api_conf = array()) {
        $this->conf = require_conf('api/mes_param');

        parent::__construct($api_conf);
    }

    function login() {
        if (!empty($this->ticket)) {
            return $this->ticket;
        }
        $param = $this->conf['login'];
        $param['Parameters'][] = array(
            'Value' => $this->username,
            'Type' => "System.String,mscorlib",
        );
        $param['Parameters'][] = array(
            'Value' => $this->password,
            'Type' => "System.String,mscorlib",
        );

        return $this->request_send('Login', $param);
    }

    function record_return($param = array()) {
        $this->login();
        $api_param = $this->conf['record_return'];
        $api_param['Parameters'] = array(
            array('Value' => $param['order_code'], 'Type' => "System.String,mscorlib"),
            array('Value' => $param['kh_code'], 'Type' => "System.String,mscorlib"),
            array('Value' => $param['kh_name'], 'Type' => "System.String,mscorlib"),
            array('Value' => $param['goods_list'],
                'Type' => "RiFengWMS.eFastErps.RmaDetailDto[], eFastInterface",
            ),
        );
        return $this->request_send('PushRma', $api_param);
    }

    function record($param = array()) {
        $this->login();
        $api_param = $this->conf['record'];
//        $api_param['Parameters'] = array(
//            array('Value' => '20161020002', 'Type' => "System.String,mscorlib"),
//            array('Value' => 'sh001', 'Type' => "System.String,mscorlib"),
//            array('Value' => '上海', 'Type' => "System.String,mscorlib"),
//            array('Value' => array(
//                    array('ItemCode' => '001', 'Unit' => '10', 'Qty' => '10', 'WarehouseCode' => '001'),
////                    array('ItemCode' => '明细2产品编码','Unit' => '明细2单位,最小单位','Qty' => '明细2装箱数量','WarehouseCode' => '明细2发货仓库代码'),
//                ),
//                'Type' => "RiFengWMS.eFastErps.SalesIssueDetailDto[], eFastInterface",
//            ),
//        );


        $api_param['Parameters'] = array(
            array('Value' =>$param['order_code'], 'Type' => "System.String,mscorlib"),
            array('Value' => $param['kh_code'], 'Type' => "System.String,mscorlib"),
            array('Value' => $param['kh_name'], 'Type' => "System.String,mscorlib"),
            array('Value' =>$param['goods_list'],
                'Type' => "RiFengWMS.eFastErps.SalesIssueDetailDto[], eFastInterface",
            ),
        );

        return $this->request_send('PushSalesIssue', $api_param);
    }

    function cancel_return($param = array()) {
        $this->login();
        $api_param = $this->conf['cancel_return'];
        $api_param['Parameters'][] = array("Value" => $param['order_code'], "Type" => "System.String,mscorlib");
        return $this->request_send('CancelSalesIssue', $api_param);
    }

}
