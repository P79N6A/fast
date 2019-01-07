<?php
/**
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-20
 */
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');
class BeibeiAPIModel extends AbsAPIModel{
    //public $gate = 'http://www.beibei.com/outer_api/out_gateway/route.html';
    public $gate = 'http://d.beibei.com/outer_api/out_gateway/route.html';	//	2.0 URL
    private $appId;
    private $appSecret;
    private $session;
    private $nick;

    public function __construct($token)
    {
        $this->appId = $token['app_key'];
        $this->appSecret = $token['secret'];
        $this->session = $token['session'];
        $this->order_pk = 'oid'; //订单主键
        $this->goods_pk = 'iid'; //产品主键

        $this->nick = $token['seller_nick'];
    }
    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array())
    {
        ksort($param);
        $data['app_id'] = $this->appId;
        $data['session'] = $this->session;
        $data['timestamp'] = time();
        $data['method'] = $api;

        $data = array_merge ($data, $param);
        $sign = $this->sign($data);
        $data['sign']   = $sign;
        $url = $this->buildUrl($data);
        $result = $this->exec($url, array());
        return $result;
    }
    public function buildUrl($params) {
        $requestUrl = $this->gate . "?";
        foreach ( $params as $sysParamKey => $sysParamValue ) {
            $requestUrl .= "$sysParamKey=" . urlencode ( $sysParamValue ) . "&";
        }
        $requestUrl = substr ( $requestUrl, 0, - 1 );
//		echo $requestUrl;exit;
        return $requestUrl;
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
     * @param array $params
     * @internal param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($params = array())
    {
        //所有请求参数按照字母先后顺序排序
        ksort ( $params );
        //定义字符串开始 结尾所包括的字符串
        $stringToBeSigned = $this->appSecret;
        //把所有参数名和参数值串在一起
        foreach ( $params as $k => $v ) {
            if("@"!=  substr($v, 0,1)){
                $stringToBeSigned .= "$k$v";
            }
        }
        unset ( $k, $v );
        //把venderKey夹在字符串的两端
        $stringToBeSigned .= $this->appSecret;
        //使用MD5进行加密，再转化成大写
        return strtoupper ( md5 ( $stringToBeSigned ) );
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
        //页码
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 40;

        //在售商品列表
        $result = $this->request_send('beibei.outer.item.onsale.get', $params);
        $result = $this->get_result($result);
        if($result['success'] == false){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            $return = array(
                'items' => array('item' => $result['data']),
                'total_results' => $result['count'],
            );
            return $return;
        }

    }

    /**
     * @param $result
     * @return bool|mix|mixed|string
     */
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
        //页码
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 40;

        if (isset($data['start_time'])) {
            $params['start_time'] = strtotime($data['start_modified']);
        } else {
            $params['start_time'] = strtotime('-7 day');
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['end_time'] = strtotime($data['end_modified']);
        }else{
            $params['end_time'] = time();
        }
        $params['status'] = -1;//(状态码 ­1:返回所有,1:待发货,2:已发货,3:已完成)

        $result = $this->request_send('beibei.outer.trade.order.get', $params);
        $result = $this->get_result($result);
        if($result['success'] == false){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            $return = array(
                'trades' => array('trade' => $result['data']),
                'total_results' => $result['count'],
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
        $params['oid'] = $id;
        $result = $this->request_send('beibei.outer.trade.order.detail.get', $params);

        $result = $this->get_result($result);
        if($result['success'] == false){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            return $result['data'];
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
        $params['iid'] = $data['goods_from_id'];
        $params['outer_id'] = $data['goods_barcode'];
        $params['qty'] = $data['inv_num'];
        $result = $this->request_send('beibei.outer.item.qty.update', $params);

        $result = $this->get_result($result);
        if($result['success'] == false){
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
        $params['deliveries']['oid'] = $data['tid'];
        $params['deliveries']['company'] = $data['logistics_id']; //快递公司
        $params['deliveries']['out_sid'] = $data['express_no']; //物流单号

        $result = $this->request_send('beibei.outer.trade.logistics.ship', $params);

        $result = $this->get_result($result);
        if($result['success'] == false){
            $msg = $result['message'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        }else{
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result['data'];
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
        $result = $this->request_send('beibei.outer.trade.logistics.get', $params);

        $result = $this->get_result($result);
        if($result['success'] == false){
            $msg = $result['message'];
            throw new ExtException($msg);
        }else{
            return $result['data'];
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
        $return['goods_name'] = $data['title'];
        $return['goods_from_id'] = $data['iid'];
        $return['source'] = 'beibei';
        $return['price'] = $data['price'];
        $return['has_sku'] = 1;
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
        foreach ($data['sku'] as $item) {
            //存在商品货号
            $arr = $item;
            $arr['goods_from_id'] = $item['iid'];
            $arr['source'] = 'beibei';
            $arr['sku_id'] = $item['sku_id'];
            $arr['goods_barcode'] = $item['outer_id'];
            $arr['num'] = $item['num'];
            $arr['price'] = $item['price'];
            $arr['sku_properties'] = $item['sku_properties'];
            $arr['lastchanged'] = time();
            $return[] = $arr;
        }

        return $return;
    }

    /**
     * 转换订单信息为标准订单
     * @param $shop_code
     * @param array $data 第三方平台订单信息
     * @return array
     */
    public function _trans_order($shop_code, array $data)
    {
        $return = array();
        $return['tid'] = $data['oid'];
        $return['source'] = 'beibei'; //'平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $status = 0;//状态(-1:返回所有,1:待发货,2:已发货,3:已完成)
        if ($data['status'] == 1) {
            $status = 1;
        }
        $return['status'] = $status; //不可转单 1：可转单  允许转单规则：已付款未发货',
        $return['seller_nick']  = $this->nick;
        $return['buyer_nick']  = $data['nick'];
        $pay_type = 0;
        //如果存在支付时间 则为已支付订单
        if (!empty($data['pay_time'])) {
            $pay_type = 1;
        }
        $return['pay_type'] = $pay_type;

        $return['receiver_country'] = '中国';
        $return['receiver_province'] = $data['province'];
        $return['receiver_city'] = $data['city'];
        $return['receiver_district'] = $data['country'];
        $return['receiver_address'] = $data['receiver_address'];//$data['province'].$data['city'].$data['country'].$data['address']; //有省市区
        $return['receiver_addr'] = $data['address']; //无省市区
        $return['receiver_name']  = $data['receiver_name'];
        $return['receiver_phone'] = $data['receiver_phone'];
        $return['num'] = $data['item_num'];
        $return['sku_num'] = count($data['item']);
        $return['order_money'] = $data['total_fee'];
        $return['express_money'] = $data['shipping_fee'];
        $return['buyer_money'] = $data['payment'];
        $return['invoice_type'] = $data['invoice_type'];
        $return['invoice_title'] = $data['invoice_name'];
        $return['seller_remark'] = $data['seller_remark'];
        $return['buyer_remark'] = $data['remark'];
        $return['pay_time'] = $data['pay_time'];

        $datetime = date('Y-m-d H:i:s');
        $return['order_first_insert_time'] = isset($data['create_time']) ? $data['create_time'] : '';
        $return['order_last_update_time'] = $return['last_update_time']=$data['create_time'];
        $return['first_insert_time'] = $datetime;
        return $return;
    }

    /**
     * 转换订单明细信息为标准订单明细
     * @param array $data 第三方平台订单明细信息
     * @return array
     */
    public function _trans_order_detail(array $data)
    {
        $return = array();
        if (isset($data['item'])) {
            $count = count($data['item']);
            $i = 1;
            $avg_sum_money = 0; //均摊金额 累加
            foreach ($data['item'] as $item) {
                //存在商品货号
                $arr['tid'] = $data['oid'];
                $arr['source'] = 'beibei';
                $arr['oid'] = $data['oid'] . '_' . $item['iid']; //平台子订单编号
                $arr['title'] = $item['title'];
                $arr['price'] = $item['price']; //实际价格
                $arr['num'] = $item['num'];
                $arr['goods_code'] = $item['goods_num'];
                $arr['sku_id'] = $item['sku_id'];
                $arr['goods_barcode'] = $item['outer_id'];
                $arr['total_fee'] = $item['subtotal'];//小计
                //$sum_payment = $this->get_order_payment($data['tid']); //获得所有详细的实付金额
                if ($i < $count) {
                    $avg_money = ($data['total_fee'] - $data['shipping_fee']) * ($item['price'] * $item['num'] / $data['total_fee']);
                    $avg_sum_money = $avg_sum_money + $avg_money;
                } else {
                    $avg_money = $data['total_fee'] - $avg_sum_money;
                }

                $arr['avg_money'] = $avg_money; //
                $arr['lastchanged'] = date('Y-m-d H:i:s', time());
                $i++;
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
        return $ret = load_model('source/ApiBeibeiTradeModel')->save_trade_and_order($shop_code, $data);
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
        return $ret = load_model('source/ApiBeibeiGoodsModel')->save_goods_info($shop_code, $data);
    }

    /**
     * 保存物流公司原始数据
     * @param string $shop_code 店铺代码
     * @param array $data 物流公司原始数据
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data)
    {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'beibei');
    }
}