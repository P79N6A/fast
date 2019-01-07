<?php

class test {

    function parse_express(array & $request, array & $response, array & $app) {
        $record = '
            {
                "receiver_country":1,
                "receiver_province":410000,
                "receiver_city":"410800000000",
                "receiver_district":"410883000000",
                "receiver_street":"",
                "receiver_addr":"会昌街道韩愈花园2号楼3单元",
                "receiver_address":"河南省焦作市孟州市会昌街道韩愈花园2号楼3单元",
                "sell_record_code":"1704210001121",
                "deal_code":"2058128807826128",
                "deal_code_list":"2058128807826128",
                "sale_channel_code":"taobao",
                "shop_code":"tb016",
                "pay_type":"secured",
                "pay_code":"alipay",
                "pay_time":"2016-07-07 22:06:07",
                "record_time":"2016-07-07 22:05:58",
                "buyer_name":"马刘家富",
                "receiver_name":"刘睿",
                "receiver_zip_code":"454750",
                "receiver_mobile":"15346556633",
                "receiver_phone":"",
                "receiver_email":"",
                "express_no":"",
                "buyer_remark":"",
                "seller_remark":"",
                "is_buyer_remark":0,
                "is_seller_remark":0,
                "seller_flag":0,
                "payable_money":49,
                "express_money":0,
                "delivery_money":0,
                "paid_money":"49.00",
                "alipay_no":"2016070721001001240217130696",
                "invoice_status":0,
                "invoice_type":"",
                "invoice_title":"",
                "invoice_content":"",
                "invoice_money":0,
                "create_time":"2017-04-21 14:41:58",
                "pay_status":2,
                "must_occupy_inv":"1",
                "fenxiao_name":"",
                "order_status":0,
                "buyer_alipay_no":"15346556633",
                "sale_mode":"stock",
                "fx_express_money":0,
                "is_lock":0,
                "is_lock_person":"",
                "store_code":"002"
            }';
        $detail = '
            [
                {
                    "deal_code":"2058128807826128",
                    "sub_deal_code":"2058128807826128",
                    "goods_price":"52.000",
                    "cost_price":"0.000",
                    "num":"1",
                    "sku_id":"3186665289416",
                    "avg_money":"49.00",
                    "platform_spec":"颜色分类:蓝色;参考身高:约7-8岁，建议身高130左右(1件)",
                    "pic_path":"http://img03.taobaocdn.com/bao/uploaded/i3/1710394567/TB2ZMeOqVXXXXaLXpXXXXXXXXXX_!!1710394567.jpg",
                    "barcode":"6922494976491",
                    "sku":"py203001001",
                    "goods_code":"py203",
                    "combo_sku":"",
                    "lock_num":0,
                    "is_gift":"0",
                    "sku_properties":"颜色分类:蓝色;参考身高:约7-8岁，建议身高130左右",
                    "combo_num":0,
                    "sale_mode":"stock",
                    "sell_record_code":"1704210001121"
                }
            ]';
        $record = json_decode($record, TRUE);
        $detail = json_decode($detail, TRUE);
        $ret = load_model('op/ploy/ExpressPloyMatchModel')->parse($record, $detail);
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function convert_barcode(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $barcode_arr = array('0000002034', '692249497649123', '6902075316007', '6902075316038', '0000002034', '0000002034');
        $ret = load_model('prm/SkuModel')->convert_barcode($barcode_arr);
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function sys_inv_test(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $barcode_arr = array('0000002034', '692249497649123','6922494976491');
        $ret = load_model('prm/InvStrategyModel')->get_shop_sku_inv('cicishop旗舰店', $barcode_arr, TRUE);
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function inv_sync_test(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        echo '<pre>';
        $barcode_arr = array('0000002034', '692249497649123','6922494976491');
        $ret = load_model('op/InvSyncHandleModel')->get_shop_sku_inv('cicishop旗舰店', $barcode_arr, TRUE);
        var_dump($ret);
        exit;
    }

    function sync_wms_quehuo(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('wms/WmsSellRecordModel')->sync_wms_quehuo('QMNR001', '', '');
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function opt_record_by_seller_remark(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('oms/SellRecordOptModel')->opt_record_by_seller_remark();
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function switch_lof(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $ret = load_model('wms/WmsSwitchLofModel')->switch_lof_lock('10221121212', 'sell_record');
        echo '<pre>';
        var_dump($ret);
        exit;
    }

    function qimenwms(array & $request, array & $response, array & $app) {
        $app['fmt'] = 'json';
        $act = 'stockout_confirm'; //'entryorder_confirm';
        //入库确认
        $json_data = '{
    "entryOrder":{
        "confirmType":"0",
        "entryOrderCode":"YC20170227006",
        "entryOrderId":"SI20170106000003360008",
        "entryOrderType":"DBRK",
        "operateTime":"2017-01-06 11:06:01",
        "outBizCode":"SI20170106000003360008",
        "ownerCode":"c1483415185882",
        "remark":"",
        "status":"FULFILLED",
        "warehouseCode":"QM002"
    },
    "orderLines":{
        "orderLine":[
            {
                "actualQty":"3",
                "batchCode":"",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"2",
                            "batchCode":"20170225",
                            "expireDate":"2019-10-03",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-02-25"
                        },
                        {
                            "actualQty":"1",
                            "batchCode":"20170223",
                            "expireDate":"2019-10-03",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-02-23"
                        }
                    ]
                },
                "expireDate":"",
                "itemCode":"CS0010000001",
                "itemId":"300027",
                "itemName":"衬衫",
                "orderLineNo":"1",
                "planQty":"2",
                "produceCode":"",
                "remark":""
            }
        ]
    }
}';
        //出库确认
        $json_data = '{
    "deliveryOrder":{
        "confirmType":"0",
        "deliveryOrderCode":"PFTZ20170522111",
        "deliveryOrderId":"OS20170106000003370004",
        "invoices":"",
        "operateTime":"2017-02-23 15:20:00",
        "operatorCode":"",
        "orderConfirmTime":"2017-02-23 14:47:14",
        "orderType":"B2BCK",
        "outBizCode":"OS20170106000003370004",
        "status":"DELIVERED",
        "warehouseCode":"HZTT"
    },
    "orderLines":{
        "orderLine":[
            {
                "actualQty":"7",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"7",
                            "batchCode":"default_lof",
                            "expireDate":"2017-07-05",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2030-01-01"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"6940477410497",
                "itemId":"260290",
                "itemName":"1100011803",
                "orderLineNo":"1",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"7"
            },
            {
                "actualQty":"6",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"6",
                            "batchCode":"20170222",
                            "expireDate":"2017-07-05",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-02-22"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"6940477410497",
                "itemId":"260290",
                "itemName":"1100011803",
                "orderLineNo":"1",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"6"
            },
            {
                "actualQty":"5",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"5",
                            "batchCode":"20170522",
                            "expireDate":"2017-05-22",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-05-22"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"6940477410497",
                "itemId":"260290",
                "itemName":"1100011803",
                "orderLineNo":"1",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"5"
            }
        ]
    }
}';
        //出库确认
        $json_data = '{
    "deliveryOrder":{
        "confirmType":"0",
        "deliveryOrderCode":"PFTZ20170531116",
        "deliveryOrderId":"OS20170106000003370004",
        "invoices":"",
        "operateTime":"2017-02-23 15:20:00",
        "operatorCode":"",
        "orderConfirmTime":"2017-02-23 14:47:14",
        "orderType":"B2BCK",
        "outBizCode":"OS20170106000003370004",
        "status":"DELIVERED",
        "warehouseCode":"HZTT"
    },
    "orderLines":{
        "orderLine":[
            {
                "actualQty":"2",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"2",
                            "batchCode":"20170531",
                            "expireDate":"2017-05-31",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-05-31"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"6940477410497",
                "itemId":"260290",
                "itemName":"1100011803",
                "orderLineNo":"5",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"2"
            },
            {
                "actualQty":"3",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"3",
                            "batchCode":"20170601",
                            "expireDate":"2017-06-01",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2017-06-01"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"6940477410497",
                "itemId":"260290",
                "itemName":"1100011803",
                "orderLineNo":"5",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"3"
            },
            {
                "actualQty":"5",
                "batchs":{
                    "batch":[
                        {
                            "actualQty":"5",
                            "batchCode":"default_lof",
                            "expireDate":"2030-01-01",
                            "inventoryType":"ZP",
                            "produceCode":"Z020202001",
                            "productDate":"2030-01-01"
                        }
                    ]
                },
                "extCode":"",
                "itemCode":"WTA641719438",
                "itemId":"233312",
                "itemName":"1100011803",
                "orderLineNo":"5",
                "orderSourceCode":"",
                "ownerCode":"c1483415185881",
                "planQty":"5"
            }
        ]
    }
}';

//        $json_data = '{
//    "deliveryOrder":{
//        "confirmType":"0",
//        "deliveryOrderCode":"1702270000016",
//        "deliveryOrderId":"OS20170106000003370004",
//        "invoices":"",
//        "operateTime":"2017-02-23 15:20:00",
//        "operatorCode":"",
//        "orderConfirmTime":"2017-02-23 14:47:14",
//        "orderType":"1",
//        "outBizCode":"OS20170106000003370004",
//        "status":"DELIVERED",
//        "warehouseCode":"QM001",
//    },
//    "orderLines":{
//        "orderLine":[
//            {
//                "actualQty":"2",
//                "batchs":{
//                    "batch":[
//                        {
//                            "actualQty":"1",
//                            "batchCode":"20170226",
//                            "expireDate":"2017-07-05",
//                            "inventoryType":"ZP",
//                            "produceCode":"Z020202001",
//                            "productDate":"2017-02-26"
//                        },
//                        {
//                            "actualQty":"1",
//                            "batchCode":"20170223",
//                            "expireDate":"2017-07-05",
//                            "inventoryType":"ZP",
//                            "produceCode":"Z020202001",
//                            "productDate":"2017-02-23"
//                        }
//                    ]
//                },
//                "extCode":"",
//                "itemCode":"CS0010000001",
//                "itemId":"300027",
//                "itemName":"1100011803",
//                "orderLineNo":"1",
//                "orderSourceCode":"",
//                "ownerCode":"c1483415185881",
//                "planQty":"4"
//            }
//        ]
//    }
//}';
//        $json_data = '{
//    "orderLines": {
//      "orderLine": [
//              {
//          "actualQty": "1",
//          "batchs": {
//            "batch": {
//              "actualQty": "1",
//              "batchCode": "20170307A",
//              "expireDate": "2018-01-23",
//              "inventoryType": "ZP",
//              "produceCode": "Z020202001",
//              "productDate": "2017-02-01"
//            }
//          },
//          "itemCode": "CS0010000001",
//          "itemId": "300027",
//          "orderLineNo": "1",
//          "planQty": "1"
//        },
//        {
//          "actualQty": "2",
//          "batchs": {
//            "batch": {
//              "actualQty": "2",
//              "batchCode": "20170307B",
//              "expireDate": "2018-01-23",
//              "inventoryType": "ZP",
//              "produceCode": "Z020202001",
//              "productDate": "2017-02-01"
//            }
//          },
//          "itemCode": "CS0010000001",
//          "itemId": "300027",
//          "orderLineNo": "2",
//          "planQty": "2"
//        }
//      ]
//    },
//    "returnOrder": {
//      "orderConfirmTime": "2017-03-10 14:18:01",
//      "orderType": "THRK",
//      "outBizCode": "RO20170310000077",
//      "returnOrderCode": "1703100000022",
//      "returnOrderId": "RO20170310000077",
//      "returnReason": "退运费",
//      "senderInfo": {
//        "area": "浦东新区",
//        "city": "上海市",
//        "detailAddress": "中国 上海 上海市 浦东新区 塘桥街道 峨山路91弄100号",
//        "mobile": "赵丁丁",
//        "name": "赵丁丁",
//        "province": "上海"
//      },
//      "warehouseCode": "QM001"
//    }
//  }';
//
//        $json_data = '{
//    "entryOrder": {
//      "entryOrderCode": "JR2017050318654",
//      "ownerCode": "BL0133",
//      "warehouseCode": "HZTT",
//      "entryOrderType": "CGRK",
//      "outBizCode": "JR2017050318654-FULFILLED-BL0133",
//      "confirmType": "0",
//      "status": "FULFILLED",
//      "operateTime": "2017-05-05 09:27:22"
//    },
//    "orderLines": {
//      "orderLine": [
//        {
//          "orderLineNo": "1",
//          "itemCode": "ZY172299066110",
//          "actualQty": "23",
//          "batchs": {
//            "batch": {
//              "inventoryType": "ZP",
//              "actualQty": "23"
//            }
//          }
//        },
//        {
//          "orderLineNo": "2",
//          "itemCode": "ZY172299066120",
//          "actualQty": "99",
//          "batchs": {
//            "batch": {
//              "inventoryType": "ZP",
//              "actualQty": "99"
//            }
//          }
//        },
//        {
//          "orderLineNo": "3",
//          "itemCode": "ZY172299066130",
//          "actualQty": "55",
//          "batchs": {
//            "batch": [
//              {
//                "inventoryType": "ZP",
//                "actualQty": "54"
//              },
//              {
//                "inventoryType": "CC",
//                "actualQty": "1"
//              }
//            ]
//          }
//        },
//        {
//          "orderLineNo": "4",
//          "itemCode": "ZY172299066150",
//          "actualQty": "51",
//          "batchs": {
//            "batch": {
//              "inventoryType": "ZP",
//              "actualQty": "51"
//            }
//          }
//        },
//        {
//          "orderLineNo": "5",
//          "itemCode": "ZY172299066160",
//          "actualQty": "99",
//          "batchs": {
//            "batch": {
//              "inventoryType": "ZP",
//              "actualQty": "99"
//            }
//          }
//        }
//      ]
//    }
//  }';
        $data['request'] = json_decode($json_data, true);
        echo '<pre>';
//        var_dump($data);exit;
        $ret = load_model('wms/qimen/QimenOpenAPIModel')->$act($data);
        echo '<pre>';
        var_dump($ret);
        exit;
    }

}
