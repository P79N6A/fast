<?php

class wmsapi {

    function ydwms_api(array &$request, array &$response, array &$app) {
        header('Content-Type: text/xml;charset=UTF-8');
        $kh_conf = require_conf("wms/yd_kh");
        if (empty($request['customerid']) || !isset($kh_conf[$request['customerid']])) {
            echo '<Response><return><returnCode>0001</returnCode><returnDesc>customerid错误</returnDesc><returnFlag>1</returnFlag></return></Response>';
            die;
        }

        $kh_id = $kh_conf[$request['customerid']];
        $status = load_model('api/ApiKehuModel')->change_db_conn($kh_id);
        if ($status === false) {
            echo '<Response><return><returnCode>FAILURE</returnCode><returnDesc>customerid匹配异常</returnDesc><returnFlag>1</returnFlag></return></Response>';
            die;
        }

        $ret = load_model('wms/ydwms/YdwmsOpenAPIModel')->exec_api($_REQUEST);
        echo $ret;
        die;
    }

    function bswms_api(array &$request, array &$response, array &$app) {
        header('Content-Type: text/html;charset=UTF-8');
        $kh_conf = require_conf("wms/bs_kh");
        if (empty($request['partnerId']) || !isset($kh_conf[$request['partnerId']])) {
            echo '<Response><return><returnCode>FAILURE</returnCode><returnDesc>customerid错误</returnDesc><returnFlag>1</returnFlag></return></Response>';
            die;
        }

        $kh_id = $kh_conf[$request['partnerId']];
        $status = load_model('api/ApiKehuModel')->change_db_conn($kh_id);
        if ($status === false) {

            echo '<Response><return><returnCode>FAILURE</returnCode><returnDesc>customerid匹配异常</returnDesc><returnFlag>1</returnFlag></return></Response>';
            die;
        }



        $ret = load_model('wms/bswms/BswmsOpenAPIModel')->exec_api($_REQUEST);

        echo $ret;
        die;
    }

    function qimei_api(array &$request, array &$response, array &$app) {
        header('Content-Type: text/xml;charset=UTF-8');
        $kh_conf = require_conf("wms/qm_kh");

        //$filepath = ROOT_PATH.'logs/open_api_wms_'.date('Ymd').'.log';
        //     file_put_contents($filepath, "time:".date('Y-m-d H:i:s')."\n{$kh_conf[$request['customerId']]}\n".  var_export($_REQUEST,true), FILE_APPEND);
        //     file_put_contents($filepath,  var_export($GLOBALS['HTTP_RAW_POST_DATA'],true), FILE_APPEND);


        if (empty($request['customerId']) || !isset($kh_conf[$request['customerId']])) {

            echo '<?xml version="1.0" encoding="utf-8"?><response><flag>failure</flag><code>-1000</code><message>缺少系统参数customerId</message></response>';
            die;
        }




        $kh_id = $kh_conf[$request['customerId']];
        $status = load_model('api/ApiKehuModel')->change_db_conn($kh_id);
        if ($request['customerId'] == 'lf2016110402') {

            $_REQUEST['customerId'] = '20110105-05728008';
        }
        if ($status === false) {

            echo '<?xml version="1.0" encoding="utf-8"?><response><flag>failure</flag><code>-1000</code><message>系统参数customerId匹配异常</message></response>';
            die;
        }

        $ret = load_model('wms/qimen/QimenOpenAPIModel')->exec_api($_REQUEST);

        echo $ret;
        die;
    }

    function iwms_api(array &$request, array &$response, array &$app) {
        header('Content-Type: text/html;charset=UTF-8');
        $kh_conf = require_conf("wms/iwms_kh");
        if (empty($request['accesskey']) || !isset($kh_conf[$request['accesskey']])) {

            echo '{"flag":"FALSE","data":{"msg":"accesskey不能为空!"}}';
            die; //$result['data']['msg']
        }
        $kh_id = $kh_conf[$request['accesskey']];
        $status = load_model('api/ApiKehuModel')->get_rds_info_by_kehu($kh_id);
        if ($status === false) {

            echo '{"flag":"FALSE","data":{"msg":"accesskey匹配不正确"}}';
            die; //$result['data']['msg']      
        }


        $ret = load_model('wms/iwms/IwmsOpenAPIModel')->exec_api($_REQUEST);

        echo $ret;
        die;
    }

    function api_test(array &$request, array &$response, array &$app) {

        if (isset($request['id'])) {
            $row = CTX()->db->get_row("select * from api_logs where id ='{$request['id']}'");

            //  require_lib('util/xml_util');
            $return = $row['post_data'];
            //  xml2array($row['post_data'], $return);

            require_model('wms/ydwms/YdwmsAPIModel');

            $token ['appkey'] = 'test';
            $token ['appSecret'] = '12345678';
            $token ['apptoken'] = '810AC1A3F-F949-492C-A024-7044B28C8025';
            $token ['URL'] = 'http://121.41.163.187/efast_devtest/webefast/web/ydwmsapi.php';
            $token ['customerid'] = 'EFAST001';
            $token ['warehouseid'] = 'WH01';
            $m = new YdwmsAPIModel($token);
            $api = $row['method'];
            $result = $m->request_send2($api, $return);
            var_dump($result);
        } else {
            echo 'fail';
        }
        die;
    }

    function lifeng(array &$request, array &$response, array &$app) {
        header('Content-Type: text/html;charset=UTF-8');
        $app['step'] = 'return';
       //$kh_id = $kh_conf[$request['customerId']];
        $kh_id ='2463'; //东莞布鲁克斯跑奇贸易有限公司
        $status = load_model('api/ApiKehuModel')->change_db_conn($kh_id);

        $response = load_model('wms/lifeng/LifengOpenAPIModel')->exec_api($request);
        return $response;
    }

    function tt(array &$request, array &$response, array &$app) {
        $tr = '<xmldata><data><orderinfo><OMSOrderNo>1509020026322</OMSOrderNo><WMSOrderNo>B201509020001</WMSOrderNo><OrderType>RS</OrderType><CustomerID>EFAST001</CustomerID><WarehouseID>WH01</WarehouseID><Status>40</Status><Desc>完全收货</Desc><ASNCreationTime></ASNCreationTime><ExpectedArriveTime1></ExpectedArriveTime1><ASNReference2></ASNReference2><ASNReference3></ASNReference3><ASNReference4></ASNReference4><ASNReference5></ASNReference5><PONO></PONO><I_Contact></I_Contact><IssuePartyName></IssuePartyName><CountryOfOrigin></CountryOfOrigin><CountryOfDestination></CountryOfDestination><PlaceOfLoading></PlaceOfLoading><PlaceOfDischarge></PlaceOfDischarge><PlaceofDelivery></PlaceofDelivery><UserDefine1>YUNDA</UserDefine1><UserDefine2></UserDefine2><UserDefine3></UserDefine3><UserDefine4>ERP</UserDefine4><UserDefine5></UserDefine5><Notes></Notes><SupplierID></SupplierID><Supplier_Name></Supplier_Name><CarrierID></CarrierID><CarrierName></CarrierName><H_EDI_02></H_EDI_02><H_EDI_03></H_EDI_03><H_EDI_04></H_EDI_04><H_EDI_05></H_EDI_05><H_EDI_06></H_EDI_06><H_EDI_07></H_EDI_07><H_EDI_08></H_EDI_08><H_EDI_09></H_EDI_09><H_EDI_10></H_EDI_10><UserDefine6></UserDefine6><UserDefine7></UserDefine7><UserDefine8></UserDefine8><Priority></Priority><FollowUp></FollowUp><item><LineNo></LineNo><CustomerID>EFAST001</CustomerID><SKU>BQFBK02001B</SKU><LineStatus>40</LineStatus><LineDesc>完全收货</LineDesc><ExpectedQty>1</ExpectedQty><ReceivedQty>1</ReceivedQty><ReceivedTime>2015-09-02 12:32:15</ReceivedTime><Lotatt01></Lotatt01><Lotatt02></Lotatt02><Lotatt03></Lotatt03><Lotatt04></Lotatt04><Lotatt05></Lotatt05><Lotatt06></Lotatt06><Lotatt07></Lotatt07><Lotatt08>N</Lotatt08><Lotatt09></Lotatt09><Lotatt10></Lotatt10><Lotatt11></Lotatt11><Lotatt12></Lotatt12><TotalPrice></TotalPrice><UserDefine1></UserDefine1><UserDefine2></UserDefine2><UserDefine3></UserDefine3><UserDefine4></UserDefine4><UserDefine5></UserDefine5><UserDefine6></UserDefine6><Notes></Notes><D_EDI_03></D_EDI_03><D_EDI_04></D_EDI_04><D_EDI_05></D_EDI_05><D_EDI_06></D_EDI_06><D_EDI_07></D_EDI_07><D_EDI_08></D_EDI_08><D_EDI_09></D_EDI_09><D_EDI_10></D_EDI_10><D_EDI_11></D_EDI_11><D_EDI_12></D_EDI_12><D_EDI_13></D_EDI_13><D_EDI_14></D_EDI_14><D_EDI_15></D_EDI_15></item><item><LineNo></LineNo><CustomerID>EFAST001</CustomerID><SKU>WWTT09003A</SKU><LineStatus>40</LineStatus><LineDesc>完全收货</LineDesc><ExpectedQty>1</ExpectedQty><ReceivedQty>1</ReceivedQty><ReceivedTime>2015-09-02 12:32:15</ReceivedTime><Lotatt01></Lotatt01><Lotatt02></Lotatt02><Lotatt03></Lotatt03><Lotatt04></Lotatt04><Lotatt05></Lotatt05><Lotatt06></Lotatt06><Lotatt07></Lotatt07><Lotatt08>N</Lotatt08><Lotatt09></Lotatt09><Lotatt10></Lotatt10><Lotatt11></Lotatt11><Lotatt12></Lotatt12><TotalPrice></TotalPrice><UserDefine1></UserDefine1><UserDefine2></UserDefine2><UserDefine3></UserDefine3><UserDefine4></UserDefine4><UserDefine5></UserDefine5><UserDefine6></UserDefine6><Notes></Notes><D_EDI_03></D_EDI_03><D_EDI_04></D_EDI_04><D_EDI_05></D_EDI_05><D_EDI_06></D_EDI_06><D_EDI_07></D_EDI_07><D_EDI_08></D_EDI_08><D_EDI_09></D_EDI_09><D_EDI_10></D_EDI_10><D_EDI_11></D_EDI_11><D_EDI_12></D_EDI_12><D_EDI_13></D_EDI_13><D_EDI_14></D_EDI_14><D_EDI_15></D_EDI_15></item></orderinfo></data></xmldata>';
        require_lib('util/xml_util');
        $return = array();
        xml2array($tr, $return);

        var_dump($return);
        die;
    }

}
