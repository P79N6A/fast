<?php

require_lib('apiclient/QmCloudClient');
/**
 * QmErpClient类
 * @author zwj
 * Date: 1/4/18
 * Time: 11:34 AM
 */
class QmErpClient extends QmCloudClient {

    /**
     * 接口配置信息
     * QmErpClient constructor.
     * @param $api_config
     */
    public function __construct($api_config) {
      parent::__construct($api_config);
    }


    function get_item($params) {
        $params['page'] = isset($params['page']) ? $params['page'] : '1';
        $params['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : '100';
        $params['endDate'] = isset($params['endDate']) ? $params['endDate'] : date('Y-m-d H:i:s');
        $params['startDate'] = isset($params['startDate']) ? $params['startDate'] : date("Y-m-d H:i:s",strtotime($params['endDate']) - 1 * 24 * 60 * 60);

        $data = $this->execute('taobao.erp.item.get', $params);

        return $this->get_response($data);
    }

    function get_supplier($params) {
        $params['page'] = isset($params['page']) ? $params['page'] : '1';
        $params['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : '100';
        $params['endDate'] = isset($params['endDate']) ? $params['endDate'] : date('Y-m-d H:i:s');
        $params['startDate'] = isset($params['startDate']) ? $params['startDate'] : date("Y-m-d H:i:s",strtotime($params['endDate']) - 1 * 24 * 60 * 60);

        $params['session'] = isset($params['session']) ? $params['session'] : '1';

        $data = $this->execute('taobao.erp.supplier.get', $params);

        return $this->get_response($data);
    }

    function get_customer($params) {
        $params['page'] = isset($params['page']) ? $params['page'] : '1';
        $params['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : '100';
        //$params['endDate'] = isset($params['endDate']) ? $params['endDate'] : date('Y-m-d H:i:s');
        //$params['startDate'] = isset($params['startDate']) ? $params['startDate'] : date("Y-m-d H:i:s",strtotime($params['endDate']) - 1 * 24 * 60 * 60);

        $data = $this->execute('taobao.erp.customerlist.get', $params);

        return $this->get_response($data);
    }

    function get_retail_order($params) {
        //$params['Order'] = '[{"amount":"2000","brandID":"001","orderCreateTime":"2017-01-01 00:00:00","warehouseCode":"HZ","actualQty":"20","channelCode":"001","remark":"备注信息","orderType":"LSCK","orderCode":"FA2017","customerCode":"HZ01","extendProps":"扩展信息","orderId":"SHOP01160617000002"}]';
        //$params['orderLine'] = '[{"actualQty":1000,"itemName":"衣服","colorName":"红色","styleCode":"IW02","sizeName":"XX","itemId":"IW02","sizeCode":"1001","stdprice":"100","discount":"1","amount":"20","styleName":"XX","retailPrice":"20","skuProperty":"abc","extendProps":"扩展信息","colorCode":"1002","orderId":"100000224121232111","itemCode":"IW02","purchasePrice":"20"}]';
        $data = $this->execute('taobao.erp.retailorder.get', $params);

        return $this->get_response($data);
    }

    function lock_inventory($params) {
        //$params['orderId'] = 'ABC123456789';
        //$params['orderCreateTime'] = '2017-01-01 10:10:10';
        //$params['warehouseCode'] = 'ABC123456789';
        //$params['whareaTypeCode'] = 'ABC123456789';
        //$params['Amount'] = '100';
        //$params['orderType'] = 'LSCK';
        //$params['Number'] = '1000';
        //$params['customerCode'] = 'LQSD02';
        //$params['items'] = '[{"amount":"100","items_number":"100","itemName":"手机","price":"200","styleCode":"IW02","remark":"备注信息","itemId":"IW0210021001","extendProps":"扩展信息","sizeCode":"32","colorCode":"1001","discount":"9.5"}]';

        $data = $this->execute('taobao.erp.inventory.lock', $params);

        return $this->get_response($data);
    }

    function get_item_inventory($params) {
        $params['page'] = isset($params['page']) ? $params['page'] : '1';
        $params['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : '100';
        $params['warehouseCode'] = isset($params['warehouseCode']) ? $params['warehouseCode'] : '';
        $params['itemsID'] = isset($params['itemsID']) ? $params['itemsID'] : '';
        $params['extendProps'] = isset($params['extendProps']) ? $params['extendProps'] : '';

        $data = $this->execute('taobao.erp.item.inventory.get', $params);

        return $this->get_response($data);
    }

    function sync_entryorder($params) {
        //$params['order'] = '{"whareaTypeCode":"ABC123","channelCode":"110","remark":"备注信息","orderCode":"100017001","orderType":"LSCK","supplierCode":"ABC0011","number":"1000","createEmp":"张三","warehouseCode":"ZCZBCK","orderCreateTime":"2017-01-01 10:10:10","brandID":"000","amount":"10000","warehouseCodeIn":"ABCD","customerCode":"LQSD02","extendProps":"扩展信息","orderId":"BOIWMS0116061"}';
        //$params['items'] = '[{"itemName":"手机","colorName":"红色","styleCode":"01","remark":"备注信息","sizeName":"43","number":"1000","itemId":"IW0210021001","sizeCode":"1001","stdprice":"280","discount":"9.5","amount":"500","styleName":"alipay","retailPrice":"300","skuProperty":"ABC110","extendProps":"扩展信息","colorCode":"IW02","itemCode":"IW0210021001","purchasePrice":"200"}]';

        $data = $this->execute('taobao.erp.entryorder.sync', $params);

        return $this->get_response($data);
    }

    function sync_base_data($params) {
        $params['page_no'] = isset($params['page']) ? $params['page'] : '1';
        $params['pageSize'] = isset($params['pageSize']) ? $params['pageSize'] : '100';
        $params['basedataType'] = isset($params['basedataType']) ? $params['basedataType'] : '';
        $params['basedataID'] = isset($params['basedataID']) ? $params['basedataID'] : '';
//        $params['endDate'] = isset($params['endDate']) ? $params['endDate'] : date('Y-m-d H:i:s');
  //      $params['startDate'] = isset($params['startDate']) ? $params['startDate'] : date("Y-m-d H:i:s",strtotime($params['endDate']) - 1 * 24 * 60 * 60);

        $data = $this->execute('taobao.erp.basedata.sync', $params);

        return $this->get_response($data);
    }

    function report_inventory($params) {
        //$params['bill'] = '{"billType":"类型","operateType":"ABC123","sourceCode":"123456789","billCode":"201710101111"}';
        $data = $this->execute('taobao.erp.inventory.report', $params);

        return $this->get_response($data);
    }
}
