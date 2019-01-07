<?php

require_lib('util/web_util', true);

class Create {
    function gg(array & $request, array & $response, array & $app) {
     
       $data =  load_model('api/sap/SapApiClientModel')->zr_send_order_to_sap();
        
//       $data =  load_model('sys/KisApiModel')->request_api('',array());
//var_dump($data);die;

//   $data =  load_model('sys/KisApiModel')->DealAcctPlatForm(array());
//var_dump($data);die;

    }


    function tt2(array & $request, array & $response, array & $app) {
        $efast_store_code ='001';
        $barcode_arr = array('ZZK33003100');//,'ZZK33003110'
       $response = load_model('wms/WmsInvModel')->down_wms_stock_by_barcode($efast_store_code, $barcode_arr);
       var_dump($response);die;

    }
    function tt3(array & $request, array & $response, array & $app) {
        
 $d=       array (
  'type' => 'post',
  'url' => 'http://qimen.api.taobao.com/router/qimen/service?app_key=23300032&format=xml&v=2.0&sign_method=md5&timestamp=2016-07-04+11%3A50%3A11&method=taobao.qimen.inventory.query&customerId=BL0133&sign=95EBECE7A0D180A37BDD062393FA51BC',
  'headers' => 
  array (
    0 => 'Content-Type:application/xml;charset=UTF-8',
  ),
  'body' => '<?xml version="1.0" encoding="UTF-8"?><request><criteriaList><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154135007140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154135007150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154135007160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152004160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152012160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154152014160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153002160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154153010160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001140</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001150</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154001160</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154014100</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154014110</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154014120</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154014130</itemCode><inventoryType>ZP</inventoryType></criteria><criteria><warehouseCode>CLOUD_WX_XIYU</warehouseCode><ownerCode>BL0133</ownerCode><itemCode>GY154154014140</itemCode><inventoryType>ZP</inventoryType></criteria></criteriaList></request>',
);
   $s = '{"app_key":"23300032","app_secret":"fc0c155345cf996ba9257bc7bd877770","customerid":"BL0133","owner_code":"BL0133","URL":"http:\/\/qimen.api.taobao.com\/router\/qimen\/service","effect_inv_type":"1","wms_cut_time":"17:00"}';
   
    $token = json_decode($s,true);
     require_model('wms/qimen/QimenAPIModel');
       $qm = new QimenAPIModel($token);
       $qm->url_data = array(
          'app_key' =>'23300032',
            'format' => 'xml',
            'v' => '2.0',
            'sign_method' => 'md5',
            'timestamp' => date('Y-m-d H:i:s'),
            'method'=>'taobao.qimen.inventory.query',
            'customerId'=> 'BL0133',
       );
            $sign = $qm->sign($d['body']);
         $qm->url_data['sign'] = $sign;
          //http://qimen.api.taobao.com/router/qimen/service?app_key=23300032&format=xml&v=2.0&sign_method=md5&timestamp=2016-07-04+11%3A50%3A11&method=taobao.qimen.inventory.query&customerId=BL0133&sign=95EBECE7A0D180A37BDD062393FA51BC  
            $header = array("Content-Type:application/xml;charset=UTF-8");
         $url = $qm->get_url();
        $resp = $qm->exec($url, $d['body'], 'post', $header);
       var_dump($token,$url,$resp);die;
            
            
    }
    
    
    
    
       function t2(array & $request, array & $response, array & $app) {
//http://order.yundasys.com:10235/cus_order/order_interface/interface_order_info_mailno.php
      $sql = "select * from oms_sell_record where sell_record_code='1601240005337'" ;
        $record = CTX()->db->get_row($sql);
        $sql = "select * from oms_sell_record_detail where sell_record_code='1601240005337'" ;
     
      $record_detail = CTX()->db->get_all($sql);
var_dump(111,$record_detail);

     $ret = load_model('op/PolicyExpressModel')->parse($record,$record_detail);

        var_dump($ret,$record);

    }

    function do_index(array & $request, array & $response, array & $app) {
        require_model('tb/CreateTbModel');
        $obj = new CreateTbModel();
        if (!isset($request['sys'])) {
            $request['sys'] = 1;
        }
        $obj->create_table_conf();
        echo 'ok';
        die;
    }

    function do_task(array & $request, array & $response, array & $app) {
        $request['sleep'] = isset($request['sleep']) ? $request['sleep'] : 1000000000;
        sleep($request['sleep']);
        echo "is test run";
        $response['status'] = 1;
    }

    function c_task(array & $request, array & $response, array & $app) {
        require_model('common/TaskModel');
        $task = new TaskModel();

        $data['code'] = 'test_task';

        //for($i = 0;$i<10;$i++){

        $data['request'] = array(
            'app_fmt' => 'json',
            'app_act' => 'create/do_task',
            'sleep' => 1000000000,
        );
        $ret = $task->save_task($data);
        $task_id = $ret['data'];
        //}
        // $task->create_task_over($task_id);

        echo 'ok' . $task_id;
        die;
    }

    function test(array & $request, array & $response, array & $app) {
        $str = '<xmldata>
<data>
         <orderinfo>
<OMSOrderNo >A001</OMSOrderNo>
<WMSOrderNo>X100000001</WMSOrderNo>
<OrderType>PO</OrderType>
<CustomerID>TEST</CustomerID>
<Status></Status>
<Desc></Desc>
<ASNCreationTime></ASNCreationTime>
<ExpectedArriveTime1></ExpectedArriveTime1>
<ASNReference2></ASNReference2>
<ASNReference3></ASNReference3>
<ASNReference4></ASNReference4>
<ASNReference5></ASNReference5>
<PONO></PONO>
<I_Contact></I_Contact>
<IssuePartyName></IssuePartyName>
<CountryOfOrigin></CountryOfOrigin>
<CountryOfDestination></CountryOfDestination>
<PlaceOfLoading></PlaceOfLoading>
<PlaceOfDischarge></PlaceOfDischarge>
<PlaceofDelivery></PlaceofDelivery>
<UserDefine1></UserDefine1>
<UserDefine2></UserDefine2>
<UserDefine3></UserDefine3>
<UserDefine4></UserDefine4>
<UserDefine5></UserDefine5>
<Notes></Notes>
<SupplierID></SupplierID>
<Supplier_Name></Supplier_Name>
<H_EDI_02></H_EDI_02>
<H_EDI_03></H_EDI_03>
<H_EDI_04></H_EDI_04>
<H_EDI_05></H_EDI_05>
<H_EDI_06></H_EDI_06>
<H_EDI_07></H_EDI_07>
<H_EDI_08></H_EDI_08>
<H_EDI_09></H_EDI_09>
<H_EDI_10></H_EDI_10>
<UserDefine6></UserDefine6>
<UserDefine7></UserDefine7>
<UserDefine8></UserDefine8>
<WarehouseID></WarehouseID>
<Priority></Priority>
<FollowUp></FollowUp>
<item>
<LineNo></LineNo>
<CustomerID></CustomerID>
<SKU></SKU>
<LineStatus></LineStatus>
<LineDesc></LineDesc>
<ExpectedQty></ExpectedQty>
<ReceivedQty></ReceivedQty>
<ReceivedTime></ReceivedTime>
<LotAtt01></LotAtt01>
<LotAtt02></LotAtt02>
<LotAtt03></LotAtt03>
<LotAtt04></LotAtt04>
<LotAtt05></LotAtt05>
<LotAtt06></LotAtt06>
<LotAtt07></LotAtt07>
<LotAtt08></LotAtt08>
<LotAtt09></LotAtt09>
<LotAtt10></LotAtt10>
<LotAtt11></LotAtt11>
<LotAtt12></LotAtt12>
<TotalPrice></TotalPrice>
<UserDefine1></UserDefine1>
<UserDefine2></UserDefine2>
<UserDefine3></UserDefine3>
<UserDefine4></UserDefine4>
<UserDefine5></UserDefine5>
<UserDefine6></UserDefine6>
<Notes></Notes>
<D_EDI_03></D_EDI_03>
<D_EDI_04></D_EDI_04>
<D_EDI_05></D_EDI_05>
<D_EDI_06></D_EDI_06>
<D_EDI_07></D_EDI_07>
<D_EDI_08></D_EDI_08>
<D_EDI_09></D_EDI_09>
<D_EDI_10></D_EDI_10>
<D_EDI_11></D_EDI_11>
<D_EDI_12></D_EDI_12>
<D_EDI_13></D_EDI_13>
<D_EDI_14></D_EDI_14>
<D_EDI_15></D_EDI_15>
<D_EDI_16></D_EDI_16>
</item>
</orderinfo>
<orderinfo>
<OrderNo>A002</OrderNo>
<OrderType>CM</OrderType>
<CustomerID>TEST</CustomerID>
<ASNCreationTime></ASNCreationTime>
<ExpectedArriveTime1></ExpectedArriveTime1>
<ASNReference2></ASNReference2>
<ASNReference3></ASNReference3>
<ASNReference4></ASNReference4>
<ASNReference5></ASNReference5>
<PONO></PONO>
<I_Contact></I_Contact>
<IssuePartyName></IssuePartyName>
<CountryOfOrigin></CountryOfOrigin>
<CountryOfDestination></CountryOfDestination>
<PlaceOfLoading></PlaceOfLoading>
<PlaceOfDischarge></PlaceOfDischarge>
<PlaceofDelivery></PlaceofDelivery>
<UserDefine1></UserDefine1>
<UserDefine2></UserDefine2>
<UserDefine3></UserDefine3>
<UserDefine4></UserDefine4>
<UserDefine5></UserDefine5>
<Notes></Notes>
<SupplierID></SupplierID>
<Supplier_Name></Supplier_Name>
<H_EDI_02></H_EDI_02>
<H_EDI_03></H_EDI_03>
<H_EDI_04></H_EDI_04>
<H_EDI_05></H_EDI_05>
<H_EDI_06></H_EDI_06>
<H_EDI_07></H_EDI_07>
<H_EDI_08></H_EDI_08>
<H_EDI_09></H_EDI_09>
<H_EDI_10></H_EDI_10>
<UserDefine6></UserDefine6>
<UserDefine7></UserDefine7>
<UserDefine8></UserDefine8>
<WarehouseID></WarehouseID>
<Priority></Priority>
<FollowUp></FollowUp>
<item>
<LineNo></LineNo>
<CustomerID></CustomerID>
<SKU></SKU>
<LineStatus></LineStatus>
<LineDesc></LineDesc>
<ExpectedQty></ExpectedQty>
<ReceivedQty></ReceivedQty>
<ReceivedTime></ReceivedTime>
<LotAtt01></LotAtt01>
<LotAtt02></LotAtt02>
<LotAtt03></LotAtt03>
<LotAtt04></LotAtt04>
<LotAtt05></LotAtt05>
<LotAtt06></LotAtt06>
<LotAtt07></LotAtt07>
<LotAtt08></LotAtt08>
<LotAtt09></LotAtt09>
<LotAtt10></LotAtt10>
<LotAtt11></LotAtt11>
<LotAtt12></LotAtt12>
<TotalPrice></TotalPrice>
<UserDefine1></UserDefine1>
<UserDefine2></UserDefine2>
<UserDefine3></UserDefine3>
<UserDefine4></UserDefine4>
<UserDefine5></UserDefine5>
<UserDefine6></UserDefine6>
<Notes></Notes>
<D_EDI_03></D_EDI_03>
<D_EDI_04></D_EDI_04>
<D_EDI_05></D_EDI_05>
<D_EDI_06></D_EDI_06>
<D_EDI_07></D_EDI_07>
<D_EDI_08></D_EDI_08>
<D_EDI_09></D_EDI_09>
<D_EDI_10></D_EDI_10>
<D_EDI_11></D_EDI_11>
<D_EDI_12></D_EDI_12>
<D_EDI_13></D_EDI_13>
<D_EDI_14></D_EDI_14>
<D_EDI_15></D_EDI_15>
<D_EDI_16></D_EDI_16>
</item>
<item>
<LineNo></LineNo>
<CustomerID></CustomerID>
<SKU></SKU>
<LineStatus></LineStatus>
<LineDesc></LineDesc>
<ExpectedQty></ExpectedQty>
<ReceivedQty></ReceivedQty>
<ReceivedTime></ReceivedTime>
<LotAtt01></LotAtt01>
<LotAtt02></LotAtt02>
<LotAtt03></LotAtt03>
<LotAtt04></LotAtt04>
<LotAtt05></LotAtt05>
<LotAtt06></LotAtt06>
<LotAtt07></LotAtt07>
<LotAtt08></LotAtt08>
<LotAtt09></LotAtt09>
<LotAtt10></LotAtt10>
<LotAtt11></LotAtt11>
<LotAtt12></LotAtt12>
<TotalPrice></TotalPrice>
<UserDefine1></UserDefine1>
<UserDefine2></UserDefine2>
<UserDefine3></UserDefine3>
<UserDefine4></UserDefine4>
<UserDefine5></UserDefine5>
<UserDefine6></UserDefine6>
<Notes></Notes>
<D_EDI_03></D_EDI_03>
<D_EDI_04></D_EDI_04>
<D_EDI_05></D_EDI_05>
<D_EDI_06></D_EDI_06>
<D_EDI_07></D_EDI_07>
<D_EDI_08></D_EDI_08>
<D_EDI_09></D_EDI_09>
<D_EDI_10></D_EDI_10>
<D_EDI_11></D_EDI_11>
<D_EDI_12></D_EDI_12>
<D_EDI_13></D_EDI_13>
<D_EDI_14></D_EDI_14>
<D_EDI_15></D_EDI_15>
<D_EDI_16></D_EDI_16>
</item>
</orderinfo>
</data>
</xmldata>';
        require_lib('util/xml_util');
        $return = array();
        xml2array($str, $return);
        require_model('wms/ydwms/YdwmsAPIModel');
        $mod = new YdwmsAPIModel();
        $api = '';
        $param = array();
        $mod->set_token($token);
        $response['status'] = 1;
        $data = $mod->request_send('confirmASNData', $return['xmldata']);
        var_dump($data);
        die;
    }

}
