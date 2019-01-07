<?php
/**
 * 聚美优品API类
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-27
 */
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');
class JumeiAPIModel extends AbsAPIModel {
    public $gate = 'http://openapi.ext.jumei.com';

    private $client_id;
    private $client_key;
    private $secret_key;

    public function __construct($token) {
        $this->client_id = $token['app_key'];//商家key
        $this->client_key  = $token['secret'];//商家的id
        $this->secret_key = $token['session'];


        $this->order_pk = 'order_id';
        $this->goods_pk = 'product_id';
    }
    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array())
    {
        //http://openapi.ext.jumei.com/Order/GetLogistics
        $data = $param;
        $data['client_id']  = $this->client_id;
        $data['client_key'] = $this->client_key;
        $data['sign'] = $this->createSign($data, $this->secret_key);
        $url = $this->gate.'/'.$api;
        $result = $this->exec($url, $data);
        return $result;
    }

    /**
     * 批量发送第三方平台请求
     */
    public function request_send_multi($api, $params = array())
    {
        // TODO: Implement request_send_multi() method.
    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array())
    {
        // TODO: Implement sign() method.
    }

    /**
     * @param $params
     * @param $secret
     * @return string
     */
    private function createSign($params, $secret) {
        $sign = $secret;
        ksort($params);
        foreach ($params as $key => $val) {
            $sign .= "$key$val";
        }
        $sign .= $secret;
        $sign = strtoupper(md5($sign));
        return $sign;
    }

    /**
     * 批量下载商品信息
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     */
    public function goods_list_download(array $data)
    {
        $params = array();
        $params['status'] = 2;//销售状态 1/2(1.在售的,2.即将上线的, 未来 48 小时内上线)
        $params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['count'] = isset($data['page_size']) ? $data['page_size'] : 50;

        $result = $this->request_send('Product/GetProductsByStatus', $params);
        $result = $this->get_result($result);
        if($result['error'] == 1){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            $return = array(
                'items' => array('item' => $result['products']),
                'total_results' => $result['totalCount'],
            );
            return $return;
        }

    }
    public function get_result($result){
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        return $result;
    }
    /**
     * 单个商品信息下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回共享表信息和平台原始数据
     */
    public function goods_info_download($data)
    {
        // TODO: Implement goods_info_download() method.
    }

    /**
     * 批量商品信息下载
     * @param $ids
     * @param array $data
     * @return array 返回共享表信息和平台原始数据
     */
    public function goods_info_download_multi($ids, $data = array())
    {
        $return = array();
        foreach ($ids as $id) {
            $return[] = $data[$id];
        }
        return $return;
    }

    /**
     * 订单列表下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     */
    public function order_list_download($data)
    {
        $params = array();
        if (isset($data['start_time'])) {
            $params['start_date'] = date('Y-m-d H:i:s',strtotime($data['start_modified']));
        } else {
            $params['start_date'] = date('Y-m-d H:i:s',strtotime('-1 day'));
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['end_date'] = date('Y-m-d H:i:s',strtotime($data['end_modified']));
        }else{
            $params['end_date'] = date('Y-m-d H:i:s',time());
        }
        $params['status'] = '2,7';//2：已付款订单7：备货中订单这两种状态的订单都是未完成发货的状态，都需要获取。参数传入方式：status=2,7或status=2或status=7

        //$params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
       // $params['count'] = isset($data['page_size']) ? $data['page_size'] : 50;

        $result = $this->request_send('Order/GetOrderIds', $params);
        $result = $this->get_result($result);
        if($result['error'] == 1){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            $arr = $res = array();
            foreach($result['result'] as $item){
                $arr['order_id'] = $item;
                $res[] = $arr;
            }
            $return = array(
                'trades' => array('trade' => $res),
                'total_results' => count($result['result']),
            );
            return $return;
        }
    }

    /**
     * 单个订单下载
     * @param $id
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     */
    public function order_info_download($id, $data = array())
    {
        $params = array();
        $params['order_id'] = $id;
        $result = $this->request_send('Order/GetOrderById', $params);

        $result = $this->get_result($result);
        if($result['error'] == 1){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            return $result['result'];
        }
    }

    /**
     * 退单列表下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @return array 返回退单列表信息
     */
    public function refund_list_download(array $data)
    {
        // TODO: Implement refund_list_download() method.
    }

    /**
     * 单个退单明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @return array 返回退单明细信息
     */
    public function refund_info_download($refund_id, $refund_info)
    {
        // TODO: Implement refund_info_download() method.
    }

    /**
     * 库存信息回传
     * @param array $data
     * @throws ExtException
     * @return array 返回结果和第三方平台原始信息
     */
    public function inv_upload(array $data)
    {
        $params = array();
        $params['upc_code'] = $data['sku_id'];
        $params['enable_num'] = $data['inv_num'];
        $result = $this->request_send('Stock/StockSync', $params);

        $result = $this->get_result($result);
        if($result['error'] == 1){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            return $result['data'];
        }
    }

    /**
     * 批量库存信息回传
     * @param array $data
     * @return array 返回结果和第三方平台原始信息
     */
    public function inv_upload_multi(array $data)
    {
        $return = array();
        foreach ($data as $item) {
            $return[] = $this->inv_upload($item);
        }
        return $return;
    }

    /**
     * 发货信息回传
     * @param array $data
     * @throws ExtException
     * @return array 返回结果和第三方平台原始信息
     */
    public function logistics_upload(array $data)
    {
        $params = array();
        $params['order_id'] = $data['tid'];
        $params['logistic_id'] = $data['logistics_id']; //快递公司
        $params['logistic_track_no'] = $data['express_no']; //物流单号

        $result = $this->request_send('Order/SetShipping', $params);

        $result = $this->get_result($result);
        if($result['error'] == 0){
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result;
        }else{
            $msg = $result['message'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        }
    }

    /**
     * 下载物流公司原始数据
     * @throws ExtException
     * @return array
     */
    public function logistics_company_download()
    {
        $params = array();
        $result = $this->request_send('Order/GetLogistics', $params);

        $result = $this->get_result($result);
        if($result['error'] == 1){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            return $result['result'];
        }
    }

    /**
     * 转换平台商品为标准信息
     * @param array $data 第三方平台商品信息
     * @return array
     */
    public function _trans_goods(array $data)
    {
        $return = array();
        $return['goods_name']    = $data['name'];
        $return['goods_from_id'] = $data['product_id'];
        $return['source'] = 'jumei';
        $return['price'] = $data['discounted_price'];
        $return['has_sku'] = 1;
        $return['onsale_time'] = $data['start_time'];
        return $return;
    }

    /**
     * 转换SKU信息为标准信息
     * @param array $data 第三方平台商品SKU信息
     * @return array
     */
    public function _trans_sku(array $data)
    {
        $return = array();
        foreach ($data['sku_data'] as $item) {
            //存在商品货号
            $arr = $item;
            $arr['goods_from_id'] = $data['product_id'];
            $arr['source'] = 'jumei';
            $arr['sku_id'] = $item['upc_code'];
            $arr['goods_barcode'] = $item['sku_no'];
            $arr['num'] = $item['stock'];
            $arr['price'] = $data['discounted_price'];
            $arr['sku_properties'] = $item['product_sn'];
            $arr['lastchanged'] = time();
            $return[] = $arr;
        }

        return $return;
    }

    /**
     * 转换订单信息为标准订单
     * @param array $data 第三方平台订单信息
     * @return array
     */
    public function _trans_order($shop_code, array $data)
    {
        $return = array();
        $return['tid'] = $data['order_id'];
        $return['source'] = 'jumei'; //'平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $status = 0;//状态(1:未付款,2:已付款,7:正在配货,3：已发货,4:交易完成, 6:已退货)
        if ($data['status'] == 7) {
            $status = 1;
        }
        $return['status'] = $status; //不可转单 1：可转单  允许转单规则：已付款未发货',
        $return['seller_nick']  = $this->nick;
        $return['buyer_nick']  = $data['receiver_infos']['receiver_name'];
        $pay_type = 0;
        // 暂定货到付款判定
        if (strtolower($data['payment_method'])=='cod') {
            $pay_type = 1;
        }
        
        $return['pay_type'] = $pay_type;
        $address  = explode('-' ,$data['receiver_infos']['address']);//四川省-成都市-武侯区 益州大道1800号G35楼
        $province = $address[0];
        $city     = $address[1];
        $info = $address[2];
        $position = strpos($info,' ');
        $country  = substr($info,0,$position);
        $return['receiver_country'] = '中国';
        $return['receiver_province'] = $province;
        $return['receiver_city'] = $city;
        $return['receiver_district'] = $country;
        $address = substr($info, $position);
        $return['receiver_address'] = $data['receiver_infos']['address'];//$data['province'].$data['city'].$data['country'].$data['address']; //有省市区
        $return['receiver_addr'] = $address; //无省市区
                                            
        $return['receiver_name']  = $data['receiver_infos']['receiver_name'];
        $return['receiver_mobile'] = $data['receiver_infos']['hp'];
        $return['receiver_phone'] = $data['receiver_infos']['phone'];
        $return['receiver_email'] = $data['receiver_infos']['email'];
        $return['receiver_zip_code'] = $data['receiver_infos']['postalcode'];                         
        $return['num'] = $this->get_num($data['product_infos']);
        $return['sku_num'] = count($data['product_infos']);
        $return['order_money'] = $data['total_products_price']-$data['price_discount_amount']+$data['delivery_fee'];
        $return['express_money'] = $data['delivery_fee'];
        $return['coupon_change_money'] = $data['price_discount_amount'];//优惠总金额
        $return['balance_change_money'] = $data['balance_paid_amount'];//帐号余额支付金额
        $return['buyer_money'] = $data['payment_amount'];//在线支付金额
        $return['invoice_type'] = $data['invoice_medium'];
        $return['invoice_title'] = $data['invoice_header'];
        $return['invoice_content'] = $data['invoice_contents'];
        $return['pay_time'] = isset($data['timestamp']) ? date('Y-m-d H:i:s', $data['timestamp']) : '';

        $datetime = date('Y-m-d H:i:s');
        $return['order_last_update_time'] = $return['last_update_time'] = $return['order_first_insert_time'] = isset($data['creation_time']) ? date('Y-m-d H:i:s', $data['creation_time']) : '';
        $return['first_insert_time'] = $datetime;
        return $return;
    }

    /**
     * 获取订单商品总数
     * @param array $product_info
     * @return int
     */
    public function get_num($product_info = array()){
        $num = 0;
        foreach($product_info as $item){
            $num += $item['quantity'];
        }
        return $num;
    }

    /**
     * 转换订单明细信息为标准订单明细
     * @param array $data 第三方平台订单明细信息
     * @return array
     */
    public function _trans_order_detail(array $data)
    {
        $return = array();
        if (isset($data['product_infos'])) {
            
            $avg_total = 0;
            $total = 0;
            $base = $data['total_products_price'] - $data['price_discount_amount'];
            
            foreach($data['product_infos'] as $value){
                $total +=  $value['deal_price']*$value['quantity'];
            }
            
            foreach ($data['product_infos'] as $key=>$item) {
                //存在商品货号
                $arr['tid'] = $data['order_id'];
                $arr['source'] = 'jumei';
                $arr['oid'] = $data['order_id'] . '_' . $item['upc_code']; //平台子订单编号
                $arr['title'] = $item['deal_short_name'];
                $arr['price'] = $item['deal_price']; //实际价格
                $arr['num'] = $item['quantity'];
                $arr['goods_code'] = $item['supplier_code'];
                $arr['sku_id'] = $item['sku_no'];
                $arr['goods_barcode'] = $item['upc_code'];
                $arr['payment'] = $item['settlement_price']; 
                $arr['total_fee'] = $item['deal_price']*$item['quantity'];//小计
                $arr['avg_money'] = $item['settlement_price']; //商品折后价（将优惠总金额按比例分摊到各个商品，然后结算的到的折后价）
                if($key < count($data['product_infos'])-1){
                    if($total == 0){
                        $arr['avg_money'] = 0;
                    }else{
                        $arr['avg_money'] = round($base * ($arr['total_fee'] / $total));
                    }
                }else{
                    $arr['avg_money'] = $base - $avg_total;
                }
                $avg_total += $arr['avg_money'];
                
                $arr['lastchanged'] = date('Y-m-d H:i:s', time());
                $return[] = $arr;
            }
        }
        return $return;
    }

    /**
     * 转换退单信息为标准退单信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $data 第三方平台订单明细信息
     */
    public function _trans_refund($shop_code, array $data)
    {
        // TODO: Implement _trans_refund() method.
    }

    /**
     * 转换退单明细信息为标准退单明细信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $data 第三方平台订单明细信息
     */
    public function _trans_refund_detail(array $data)
    {
        // TODO: Implement _trans_refund_detail() method.
    }

    /**
     * 保存原始订单和订单明细数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_order_and_detail($shop_code, $data)
    {
        return $ret = load_model('source/ApiJumeiTradeModel')->save_trade_and_order($shop_code, $data);
    }

    /**
     * 保存原始退单和退单数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_refund($shop_code, $data)
    {
        // TODO: Implement save_source_refund() method.
    }

    /**
     * 保存原始商品和sku数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_goods_and_sku($shop_code, $data)
    {
        return $ret = load_model('source/ApiJumeiGoodsModel')->save_goods_info($shop_code, $data);
    }

    /**
     * 保存物流公司原始数据
     * @param string $shop_code 店铺代码
     * @param array $data 物流公司原始数据
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data)
    {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'jumei');
    }
}