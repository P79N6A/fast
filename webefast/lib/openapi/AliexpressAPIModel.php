<?php

require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

/**
 * 速卖通接口API类
 * @author jhua.zuo
 */
class AliexpressAPIModel extends AbsAPIModel {

    /**
     * 接口网关地址
     * @var string 
     */
    public $gate = 'http://gw.api.alibaba.com:80/openapi/';

    /**
     * 协议格式
     * @var string
     */
    private $param = 'param2';

    /**
     * app_key
     * @var string
     */
    private $app_key;

    /**
     * 淘宝应用的secret
     * @var string
     */
    private $secret;

    /**
     * 授权session
     * @var string 
     */
    private $access_token;

    /**
     * 接口实例化
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param array $token 应用及授权信息数组
     */
    public function __construct($token) {
        $this->app_key = isset($token['app_key']) ? $token['app_key'] : '9872703';
        $this->access_token = $token['access_token'];
        $this->secret = isset($token['secret']) ? $token['secret'] : 'nMbwPEo0Vx';

        $this->order_pk = 'orderId';
        $this->goods_pk = '';
        $this->refund_pk = '';
        $this->order_page_size = 50;

	    $this->goods_pk = 'productId';
	    $this->goods_page_size = 100;

	    $this->goods_ext = true;
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    }

    public function _trans_goods(array $data) {
	    $return = array();
	    $return['goods_name'] = $data['subject'];
	    $return['goods_code'] = '';
	    $return['goods_from_id'] = $data['productId'];
	    //TODO num暂无
	    $return['num'] = load_model('source/aliexpress/ApiAliexpressGoodsModel')->get_sku_sum_num($data['productId']);
	    //TODO seller_nick暂无
	    $return['seller_nick'] = '';
	    $return['source'] = 'aliexpress';
	    $return['status'] = $data['productStatusType'] == 'onSelling' ? 1 : 0;
	    //TODO stock_type暂无
	    $return['stock_type'] = 2;
	    //TODO onsale_time暂无
	    $return['onsale_time'] = '';
	    $return['has_sku'] = empty($data['aeopAeProductSKUs']) ? 0 : 1;
	    //TODO price暂无
	    $return['price'] = '';
	    //TODO goods_img暂无
	    $return['goods_img'] = '';
	    $return['goods_desc'] = $data['detail'];

	    return $return;
    }

    /**
     * 转换订单主单据信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     * @param string $shop_code 店铺代码
     * @param array $data 速卖通订单原始信息
     * @return array
     */
    public function _trans_order($shop_code, array $data) {
        $datetime = date('Y-m-d H:i:s');
        $num = 0;
        foreach($data['childOrderList'] as $v){
            $num += $v['productCount'];
        }
        
        $return = array();
        $return['tid'] = $data['id'];
        $return['source'] = 'aliexpress';
        $return['shop_code'] = $shop_code;
        $return['status'] = ($data['logisticsStatus']=='WAIT_SELLER_SEND_GOODS')?1:0;
        $return['trade_from'] = '';
        $return['pay_type'] = 0;
        $return['pay_time'] = date('Y-m-d H:i:s', strtotime($data['gmtPaySuccess']));
        $return['seller_nick'] = $data['sellerOperatorLoginId'];
        $return['buyer_nick'] = $data['buyerloginid'];
        $return['receiver_name'] = $data['buyerInfo']['firstName'].' '.$data['buyerInfo']['lastName'];
        $return['receiver_country'] = $data['receiptAddress']['country'];
        $return['receiver_province'] = $data['receiptAddress']['province'];
        $return['receiver_city'] = $data['receiptAddress']['city'];
        $return['receiver_district'] = $data['receiptAddress']['address2'];
        //$return['receiver_street'] = $data[''];
        //含省市区
        $return['receiver_address'] = $return['receiver_province'] . $return['receiver_city'] . $return['receiver_district']. $data['receiptAddress']['detailAddress'];
        //不含省市区
        $return['receiver_addr'] = $data['receiptAddress']['detailAddress'];
        $return['receiver_zip_code'] = $data['receiptAddress']['zip'];
        if(isset($data['receiptAddress']['phoneNumber'])&&!empty($data['receiptAddress']['phoneNumber'])){
            $return['receiver_phone'] = '+(' . $data['receiptAddress']['phoneCountry'] .')' . $data['receiptAddress']['phoneArea'] .'-'. $data['receiptAddress']['phoneNumber'];
        }
        
        if(isset($data['receiptAddress']['mobileNo'])&&!empty($data['receiptAddress']['mobileNo'])){
            $return['receiver_mobile'] = '+(' . $data['receiptAddress']['phoneCountry'] .')' . $data['receiptAddress']['phoneArea'] .'-'. $data['receiptAddress']['mobileNo'];
        }

        $return['receiver_email'] = $data['buyerInfo']['email'];
        
        $return['express_code'] = $data['logisticInfoList']['0']['logisticsTypeCode'];
        $return['express_no'] = $data['logisticInfoList']['0']['logisticsNo'];
        //$return['hope_send_time'] = ;
        $return['num'] = $num;
        $return['sku_num'] = count($data['childOrderList']);
        //$return['goods_weight'] = ;
        //$return['buyer_remark'] = ;
        //$return['seller_remark'] = ;
        //$return['seller_flag'] = ;
        $return['order_money'] = $data['orderAmount']['amount'];
        $return['express_money'] = $data['logisticsAmount']['amount'];
        $return['delivery_money'] = 0; //TODO
        $return['gift_coupon_money'] = 0; //TODO
        $return['gift_money'] = 0; //TODO
        $return['buyer_money'] = 0; //TODO 
        $return['alipay_no'] = '';
        $return['integral_change_money'] = 0; //TODO
        $return['coupon_change_money'] = 0; //TODO
        $return['balance_change_money'] = 0; //TODO
        //$return['is_lgtype'] = 0; //没有
        //$return['seller_rate'] = 0; //没有
        //$return['buyer_rate'] = 0; //没有
        //$return['invoice_type'] = '';
        //$return['invoice_title'] = '';
        //$return['invoice_content'] = '';
        //$return['invoice_money'] = '';
        //$return['invoice_pay_type'] = '';
        $return['order_last_update_time'] =   date('Y-m-d H:i:s', strtotime($data['gmtModified']));
        $return['order_first_insert_time'] =   date('Y-m-d H:i:s', strtotime($data['gmtCreate']));
        $return['last_update_time'] =   date('Y-m-d H:i:s', strtotime($data['gmtModified']));
        $return['first_insert_time'] = $datetime;
        
        return $return;
    }

    /**
     * 转换子订单到标准结构
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     * @param array $data  原始订单信息
     * @return array 返回子订单标准格式
     */
    public function _trans_order_detail(array $data) {
        $return = array();
        
        $avg_total = 0;
        $total = 0;
        $base = $data['orderAmount']['amount'] - $data['logisticsAmount']['amount'] - $data['escrowFee'];
        
        foreach($data['childOrderList'] as $value){
            $total = $total + ($value['productPrice']['amount'] * $value['productCount']);
        }
        
        if (isset($data['childOrderList'])) {
            foreach ($data['childOrderList'] as $key=>$value) {
                $detail = array();
                $detail['tid'] = $data['id'];
                $detail['source'] = 'aliexpress';
                $detail['oid'] = $data['id'] . '-' . $value['skuCode'];
                $detail['status'] = $value['orderStatus']; //TODO ???????????????
                $detail['return_status'] = '';
                $detail['title'] = $value['productName'];
                $detail['price'] = $value['productPrice']['amount'];
                $detail['num'] = $value['productCount'];
                $detail['goods_code'] = $value['productId'];
                $detail['sku_id'] = $value['skuCode'];
                $detail['goods_barcode'] = $value['skuCode'];
                $detail['total_fee'] = $value['initOrderAmt']['amount'];
                $detail['payment'] = 0; //TODO
                $detail['discount_fee'] = 0; //TODO
                $detail['adjust_fee'] = 0; //TODO
                
                //$detail['avg_money'] = $base * ($value['productPrice']['amount'] * $value['productCount'] / $total);

                if($key < count($data['childOrderList'])-1){
                    if($total == 0){
                        $detail['avg_money'] = 0;
                    }else{
                        $detail['avg_money'] = round($base * ($value['productPrice']['amount'] * $value['productCount'] / $total));
                    }
                }else{
                    $detail['avg_money'] = $base - $avg_total;
                }
                
                $avg_total += $detail['avg_money'];
                
                $detail['end_time'] = date('Y-m-d H:i:s', strtotime($data['gmtSendGoodsTime']));
                $detail['consign_time'] = date('Y-m-d H:i:s', strtotime($data['logisticInfoList'][0]['gmtSend']));
                $detail['express_code'] = $data['logisticInfoList'][0]['logisticsTypeCode'];
                $detail['express_company_name'] = $data['logisticInfoList'][0]['logisticsServiceName'];
                $detail['express_no'] = $data['logisticInfoList'][0]['logisticsNo'];
                $detail['pic_path'] = $value[''];
                $productAttributes = $this->json_decode($value['productAttributes']);
                $productAttributes_str = '';
                foreach ($productAttributes['sku'] as $sku) {
                    $productAttributes_str = $productAttributes_str . $sku['pName'] . ':' . $sku['pValue'] . ';';
                }
                $detail['sku_properties'] = $productAttributes_str;
                $return[] = $detail;
            }
        }
        return $return;
    }

    public function _trans_refund($shop_code, array $data) {
        
    }

    public function _trans_refund_detail(array $data) {
        
    }

    public function _trans_sku(array $data) {
	    $return = array();
	    if (isset($data['aeopAeProductSKUs'])) {
		    foreach ($data['aeopAeProductSKUs'] as $value) {
			    $sku = array();
			    $sku['goods_from_id'] = $data['productId'];
			    $sku['source'] = 'aliexpress';
			    $sku['sku_id'] = $value['id'];
			    $sku['goods_barcode'] = isset($value['skuCode']) ? $value['skuCode'] : '';

			    $sku['status'] = 1;
			    $sku['num'] = $value['ipmSkuStock'];
			    $sku['price'] = $value['skuPrice'];
			    $sku['stock_type'] = 1;
			    $sku['with_hold_quantity'] = '';

			    $return[] = $sku;
		    }
	    } else {
	    }
	    return $return;
    }

    ########################################################################

    public function goods_info_download($data) {
	    $params = array();
	    $params['productId'] = $data;

	    $result = $this->json_decode($this->request_send('1/aliexpress.open/api.findAeProductById', $params));
	    if (isset($return['error_code'])) {
		    $msg = $return['error_message'];
		    throw new ExtException($msg, $return['error_code']);
	    }  else {
		    $return = $result;
		    return $return;
	    }
    }

    public function goods_info_download_multi($ids, $data = array()) {
	    $return = array();
	    foreach ($ids as $goods_id) {
		    $return[] = $this->goods_info_download($goods_id);
	    }
	    return $return;
    }

	/**
	 * @param array $data
	 * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.findProductInfoListQuery&v=1
	 */
    public function goods_list_download(array $data) {
	    $params = array();
	    $params['currentPage'] = isset($data['page_no']) ? $data['page_no'] : 1;
	    $params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 20;

	    $params['productStatusType'] = isset($data['productStatusType']) ? $data['productStatusType']:'onSelling';

	    $result = $this->json_decode($this->request_send('1/aliexpress.open/api.findProductInfoListQuery', $params));
	    if (isset($return['error_code'])) {
		    $msg = $return['error_message'];
		    throw new ExtException($msg, $return['error_code']);
	    } else {
		    $return = array(
			    //转成与淘宝类似的返回格式
			    'total_results' => $result['productCount'],
			    'items' => array('item' => $result['aeopAEProductDisplayDTOList']
			    )
		    );
		    return $return;
	    }
    }

    public function inv_upload(array $data) {
	    $params = array();
	    $params['productId'] = $data['goods_from_id'];
	    $params['skuId'] = $data['sku_id'];
	    $params['ipmSkuStock'] = $data['inv_num'];

	    $return = $this->json_decode($this->request_send('1/aliexpress.open/api.editSingleSkuStock', $params));
	    if (isset($return['error_code'])) {
		    $msg = $return['error_message'];
		    throw new ExtException($msg, $return['error_code']);
	    }

	    return $return;
    }

    public function inv_upload_multi(array $data) {
	    $return = array();
	    foreach($data as $item) {
		    $return[] = $this->inv_upload($item);
	    }
	    return $return;
    }

    /**
     * 下载速卖通平台支持的物流公司信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function logistics_company_download() {
        $param = array();
        $result = $this->request_send('1/aliexpress.open/api.listLogisticsService', $param);
        $return = $this->json_decode($result);
        return $return['result'];
    }

    /**
     * 速卖通声明发货接口
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param array $data
     * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.sellerShipment&v=1 声明发货
     */
    public function logistics_upload(array $data) {
        $sendType = 'all';
        $param = array(
            'outRef' => $data['tid'], //用户需要发货的订单id
            'logisticsNo' => $data['express_no'], //物流追踪号
            'serviceName' => $data['express_code'], //用户选择的实际发货物流服务（物流服务key：该接口根据api.listLogisticsService列出平台所支持的物流服务 进行获取目前所支持的物流。）
            'sendType' => $sendType, //状态包括：全部发货(all)、部分发货(part)
        );
        
        $result = $this->request_send('1/aliexpress.open/api.sellerShipment', $param);
        $return = $this->json_decode($result);
        //日志记录到业务系统中，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_code'])) {
            $msg = $return['error_message'];
            $send_log['status'] = -1;
            $oms_log['is_back'] = 2;
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg, $return['error_code']);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
        }
        $return['send_log'] = $send_log;
        $return['oms_log'] = $oms_log;
        return $return;
    }

    /**
     * 速卖通订单明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param $id
     * @param array $data
     * @link http://gw.api.alibaba.com/dev/doc/intl/api.htm?ns=aliexpress.open&n=api.findOrderById&v=1 交易订单详情查询 
     * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.findOrderBaseInfo&v=1 订单基础信息查询（试用）
     * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.findOrderReceiptInfo&v=1 订单收货信息查询（试用）
     * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.findOrderTradeInfo&v=1 订单交易信息查询（试用）
     * @return array 返回速卖通原始数据
     */
    public function order_info_download($id, $data = array()) {
        $param = array();
        $param['orderId'] = $id;

        $return = $this->json_decode($this->request_send('1/aliexpress.open/api.findOrderById', $param));
        if (isset($return['error_code'])) {
            $msg = $return['error_message'];
            throw new ExtException($msg, $return['error_code']);
        }
        
        //试用接口，暂不使用
        //$base_info = $this->json_decode($this->request_send('1/aliexpress.open/api.findOrderBaseInfo', $param));
        //$receipt_info = $this->json_decode($this->request_send('1/aliexpress.open/api.findOrderReceiptInfo', $param));
        //$trade_info = $this->json_decode($this->request_send('1/aliexpress.open/api.findOrderTradeInfo', $param));
        /**
        $result = array(
            'base_info' => $base_info,
            'receipt_info' => $receipt_info,
            'trade_info' => $trade_info,
        );**/
        
        return $return;
    }

    /**
     * 速卖通订单下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param array $data 查询条件
     * @link http://gw.api.alibaba.com/dev/doc/api.htm?ns=aliexpress.open&n=api.findOrderListSimpleQuery&v=1 订单列表简化查询（试用）
     * @return array 返回速卖通原始数据
     */
    public function order_list_download($data) {
        $param = array();
        $param['page'] = $data['page_no'];
        $param['pageSize'] = $data['page_size'];
        if(isset($data['start_modified'])&&!empty($data['start_modified'])){
            $param['createDateStart'] = $data['start_modified'];
        }
        $param['createDateEnd'] = date('Y-m-d H:i:s');

        $result = $this->request_send('1/aliexpress.open/api.findOrderListQuery', $param);
        $return = $this->json_decode($result);
        
        if (isset($return['error_code'])) {
            $msg = $return['error_message'];
            throw new ExtException($msg, $return['error_code']);
        }else{
            $return = array(
                //转成与淘宝类似的返回格式
                'trades' => array('trade' => $return['orderList']),
                'total_results' => $return['totalItem'],
            );
        }
        return $return;
    }
    
    
    /**
     * 下载订单额外方法，说明：速卖通订单分为订单和交易订单两类
     * @link http://gw.api.alibaba.com/dev/doc/intl/api.htm?ns=aliexpress.open&n=api.findOrderListSimpleQuery&v=1 订单简化列表查询
     * @link http://gw.api.alibaba.com/dev/doc/intl/api.htm?ns=aliexpress.open&n=api.findOrderListQuery&v=1 交易订单列表查询
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     * @todo 暂时只处理交易订单
     */
    public function order_download_ext($data){
        //TODO 暂时不用 代码未完成
        $results = array();
        $page = 1;
        $page_size = 50;
        do{
            $result = $this->order_list_download_ext($data);
            $results = array_merge($results,$result['trades']['trade']);
            $pages = ceil($result['total_results'] / $page_size);
        }while($page <= $pages);
        return $results;
    }
    
    /**
     * 交易订单简化查询
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     * @param array $data
     * @return array 返回速卖通原始数据
     * @throws ExtException
     * @link http://gw.api.alibaba.com/dev/doc/intl/api.htm?ns=aliexpress.open&n=api.findOrderListSimpleQuery&v=1  订单列表简化查询
     */
    private function order_list_download_ext($data){
        //TODO 暂时不用 代码未完成
        $param = array();
        $param['page'] = $data['page_no'];
        $param['pageSize'] = $data['page_size'];
        if(isset($data['start_modified'])&&!empty($data['start_modified'])){
            $param['createDateStart'] = $data['start_modified'];
        }
        $param['createDateEnd'] = date('Y-m-d H:i:s');

        $result = $this->request_send('1/aliexpress.open/api.findOrderListSimpleQuery', $param);
        $return = $this->json_decode($result);
        if (isset($return['error_code'])) {
            $msg = $return['error_message'];
            throw new ExtException($msg, $return['error_code']);
        }else{
            $return = array(
                //转成与淘宝类似的返回格式
                'trades' => array('trade' => $return['orderList']),
                'total_results' => $return['totalItem'],
            );
        }
        return $return;
    }


    /**
     * 转换订单的额外方法，如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function _trans_order_ext($shop_code, $data){return array();}
    /**
     * 转换订单明细的额外方法，如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function _trans_order_detail_ext($data){return array();}
    
    /**
     * 保存订单额外方法， 如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function save_source_order_ext($shop_code, $data){return false;}
    

    public function refund_info_download($refund_id, $refund_info) {
        
    }

    public function refund_list_download(array $data) {
        
    }

    ########################################################################
    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param string $api 接口地址
     * @param array $param 请求参数
     */

    public function request_send($api, $param = array()) {
        //增加系统级参数
        //$data['_aop_timestamp'] = time();
        $data['access_token'] = $this->access_token;
        $data = array_merge($data, $param);
        //封装签名
        $sign = $this->sign(array(
            'url' => $this->param . '/' . $api . '/' . $this->app_key,
            'data' => $data,
        ));
        $data['_aop_signature'] = $sign;
        //发送请求
        $url = $this->gate . $this->param . '/' . $api . '/' . $this->app_key;
        $result = $this->exec($url, $data);
        return $result;
    }

    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param string $api 接口地址
     * @param array $params 请求参数
     */
    public function request_send_multi($api, $params = array()) {
        $datas = array();
        foreach ($params as $param) {
            //增加系统级参数
            $data['_aop_timestamp'] = time();
            $data['access_token'] = $this->session;
            //封装签名
            $sign = $this->sign(array(
                'url' => $this->param . '/' . $api . '/' . $this->app_key,
                'data' => $data,
            ));
            $data['_aop_signature'] = $sign;
            $data = array_merge($data, $param);
            $datas[] = $data;
        }
        //发送请求
        $url = $this->gate . $this->param . '/' . $api . '/' . $this->app_key;
        $result = $this->multiExec($url, $datas);
        return $result;
    }

    /**
     * 生成签名
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-21
     * @param array $data 包含url=>请求地址 和data=>请求参数
     * @return string 返回签名
     * @link http://gw.api.alibaba.com/dev/doc/sys_signature.htm?ns=aliexpress.open 签名规则
     */
    public function sign($data = array()) {
        $param = $data['data'];
        ksort($param);
        $sign = $data['url'];
        foreach ($param as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign_sha1 = strtoupper(bin2hex(hash_hmac('sha1', $sign, $this->secret,true)));
        return $sign_sha1;
    }

    ########################################################################

    public function save_source_goods_and_sku($shop_code, $data) {
	    return $ret = load_model('source/aliexpress/ApiAliexpressGoodsModel')->save_goods_and_sku($shop_code, $data);
    }

    /**
     * 保存物流公司原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     * @param string $shop_code 店铺代码
     * @param array $data 原始数据
     */
    public function save_source_logistics_company($shop_code, $data) {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'aliexpress');
    }

    public function save_source_order_and_detail($shop_code, $data) {
        return load_model('source/aliexpress/ApiAliexpressTradeModel')->save_trade_and_order($shop_code, $data);
    }

    public function save_source_refund($shop_code, $data) {
        
    }


	/**
	 * 商品下载(额外)
	 * @param $data
	 * @return array
	 */
	public function goods_download_ext($data) {

		$page = 1;
		$page_size = $this->goods_page_size;
		$results = $result = array();
		do {
			$params = array(
				'page_no' => $page,
				'page_size' => $page_size,
				'start_modified' => $data['start_modified'],
				'productStatusType' => 'offline'
			);

			$result = $this->goods_list_download($params);

			$pages = ceil($result['total_results'] / $page_size);
			$results = array_merge($results, $result['items']['item']);
			$page++;
		} while ($page <= $pages);

		$results_arr = array();
		foreach ($results as $item) {
			$_result = $this->goods_info_download($item['productId']);
			$results_arr[] = $_result;
		}

		return $results_arr;
	}

	/**
	 * 保存原始商品和sku数据(额外)
	 * @param $shop_code
	 * @param $data
	 * @return array
	 */
	public function save_source_goods_and_sku_ext($shop_code, $data) {
		return $ret = load_model('source/aliexpress/ApiAliexpressGoodsModel')->save_goods_and_sku($shop_code, $data);
	}

	/**
	 * 转换平台商品为标准信息
	 * @param $data
	 * @return array
	 */
	public function _trans_goods_ext($data) {
		return $this->_trans_goods($data);
	}

	/**
	 * @param $data
	 * @return array
	 */
	public function _trans_sku_ext($data) {
		return $this->_trans_sku($data);
	}
}
