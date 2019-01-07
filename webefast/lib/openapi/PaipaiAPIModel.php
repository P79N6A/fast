<?php
/**
 * 拍拍网接口
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-29
 */
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

class PaipaiAPIModel extends AbsAPIModel
{

    public $gate = 'http://api.paipai.com/';
    private $uin; //合法的QQ号
    private $appOAuthID; //appOAuthID对应于接入方平台，可能是一个商家系统或一个第三方应用
    private $secretOAuthKey;
    private $accessToken; //accessToken与uin一一对应，用于校验单个用户的真实身份
    private $nick;

    public function __construct($token)
    {
        $this->uin = $token['uin'];
        $this->appOAuthID = $token['app_key'];
        $this->secretOAuthKey = $token['secret'];
        $this->accessToken = $token['session'];
        $this->order_pk = 'dealCode'; //订单主键
        $this->goods_pk = 'itemCode'; //产品主键

        $this->nick = $token['seller_nick'];
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array())
    {
        $paipaiParamArr = array(
            'sellerUin' => $this->uin,
            'uin' => $this->uin,
            'accessToken' => $this->accessToken,
            'appOAuthID' => $this->appOAuthID,
           // 'charset' => 'utf-8',
            'pureData' => '1',
            'timeStamp' => time(),
            'randomValue' => rand(10000, 9999999)
        );

        $_paramArr = array_merge($param, $paipaiParamArr);
        $sign = $this->createSign($_paramArr, '/' . $api);
        $params_arr = $_paramArr;
        $params_arr['sign'] = $sign;
        $url = $this->gate . $api;
        $result = $this->exec($url, $params_arr);
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
     * 生成签名
     * @param $paramArr
     * @param string $cmdid
     * @return string
     */
    private function createSign($paramArr, $cmdid = '')
    {
        ksort($paramArr);
        $sign_cmdid = rawurlencode($cmdid);
        $sign_param = array();
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val !== '' && $val !== null) {
                $sign_param[] = $key . '=' . $val;
            }
        }
        $sign = 'POST&' . $sign_cmdid . '&' . rawurlencode(join('&', $sign_param));
        $sign = base64_encode(hash_hmac('sha1', $sign, $this->secretOAuthKey . '&', true));
        return $sign;
    }

    /**
     * 批量下载商品信息
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://pop.paipai.com/api/paipai/item/sellerSearchItemList
     */
    public function goods_list_download(array $data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['sellerUin'] = $this->uin; //卖家QQ
        $params['extendInfo'] = 0;
        //页码
        $params['pageIndex'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 40;
        if (isset($data['start_modified'])) {
            //$params['modifyTimeBegin'] = date('Y-m-d H:i:s', strtotime($data['start_modified']));
        } else {
            //$params['modifyTimeBegin'] = date('Y-m-d H:i:s',strtotime('-7 day'));
        }
        $result = $this->request_send('item/sellerSearchItemList.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            $return = array(
                'items' => array('item' => $result['itemList']),
                'total_results' => $result['countTotal'],
            );
            return $return;
        }
    }

    /**
     * 单个商品信息下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://pop.paipai.com/api/paipai/item/getItem
     */
    public function goods_info_download($data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['itemCode'] = $data;

        $result = $this->request_send('item/getItem.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            return $result;
        }
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
            $return[] = $this->goods_info_download($id);
        }
        return $return;
    }

    /**
     * 订单列表下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://pop.paipai.com/api/paipai/deal/sellerSearchDealList
     */
    public function order_list_download($data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['sellerUin'] = $this->uin; //卖家QQ

        //页码
        $params['pageIndex'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 40;

        $params['timeType'] = 'UPDATE';
        if (isset($data['start_modified'])) {
            $params['timeBegin'] = $data['start_modified'];
        } else {
            $params['timeBegin'] = date('Y-m-d H:i:s', strtotime('-7 day'));
        }
        $params['timeEnd'] = date('Y-m-d H:i:s', time());

        $result = $this->request_send('deal/sellerSearchDealList.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            $return = array(
                'trades' => array('trade' => $result['dealList']),
                'total_results' => $result['countTotal'],
            );
            return $return;
        }
    }

    /**
     * 单个订单下载
     * @param $id
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://pop.paipai.com/api/paipai/deal/getDealDetail
     */
    public function order_info_download($id, $data = array())
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['sellerUin'] = $this->uin; //卖家QQ
        $params['dealCode'] = $data['dealCode'];
        $params['listItem'] = 1;

        $result = $this->request_send('deal/getDealDetail.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            return $result;
        }
    }

    /**
     * 退单列表下载(待完善)
     * @param array $data
     * @throws ExtException
     * @return array 返回退单列表信息
     * @link http://pop.paipai.com/api/paipai/deal/getDealRefundInfoList
     */
    public function refund_list_download(array $data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['userUin'] = $this->uin; //卖家或者买家QQ号
        //页码
        $params['pageIndex'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['pageSize'] = isset($data['page_size']) ? $data['page_size'] : 40;
        if (isset($data['start_modified'])) {
            $params['timeBegin'] = strtotime($data['start_modified']);
        } else {
            $params['timeBegin'] = strtotime('-7 day');
        }
        $result = $this->request_send('deal/getDealRefundInfoList.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            $return = array(
                'refunds' => array('refund' => $result['dealList']),
                'total_results' => $result['countTotal'],
            );
            return $return;
        }
    }

    /**
     * 单个退单明细下载
     * @param $refund_id
     * @param $refund_info
     * @throws ExtException
     * @return array 返回退单明细信息
     * @link http://pop.paipai.com/api/paipai/deal/getDealRefundDetailInfo
     */
    public function refund_info_download($refund_id, $refund_info)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['userUin'] = $this->uin; //卖家或者买家QQ号
        $params['dealCode'] = $refund_id;

        $result = $this->request_send('deal/getDealDetail.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            return $result;
        }
    }

    /**
     * 库存信息回传
     * @param array $data
     * @throws ExtException
     * @return array 返回结果和第三方平台原始信息
     * @link http://pop.paipai.com/api/paipai/item/modifyItemStock
     */
    public function inv_upload(array $data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['userUin'] = $this->uin; //卖家或者买家QQ号
        $params['skuId'] = $data['sku_id'];
        $params['stockCount'] = $data['inv_num'];

        $result = $this->request_send('item/modifyItemStock.xhtml', $params);
        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            throw new ExtException($msg);
        } else {
            return $result;
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
     * @link http://pop.paipai.com/api/paipai/deal/sellerConsignDealItem
     */
    public function logistics_upload(array $data)
    {
        $params = array();
        $params['format'] = 'json'; //用于指定返回格式,默认值为xml
        $params['userUin'] = $this->uin; //卖家或者买家QQ号
        $params['dealCode'] = $data['tid'];
        $params['logisticsName'] = $data['logistics_id']; //快递公司 名称 非id NO, 表示无需快递，同时不必再指定发货单号
        $params['logisticsCode'] = $data['express_no']; //物流单号
        $params['arriveDays'] = 7; //预计几天后到货[1、2、3、4、5、7、10、15、20、30]

        $result = $this->request_send('deal/sellerConsignDealItem.xhtml', $params);

        $result = $this->get_result($result);
        if ($result['errorCode'] != 0) {
            $msg = $result['errorMessage'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result['dealCode'];
        }
    }

    /**
     * 下载物流公司原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @return array
     */
    public function logistics_company_download()
    {
        // TODO: Implement logistics_company_download() method.
    }

    /**
     * 转换平台商品为标准信息
     * @param array $data 第三方平台商品信息
     * @return array
     */
    public function _trans_goods(array $data)
    {
        $return = array();
        $return['goods_name'] = $data['itemName'];
        $return['goods_from_id'] = $data['itemCode'];
        $return['source'] = 'paipai';
        $return['num'] = $data['stockCount'];
        $return['seller_nick'] = $data['sellerUin']; //卖家qq
        $return['has_sku'] = 1;
        $return['status'] = $data['itemState'];
        $return['onsale_time'] = $data['lastToSaleTime'];
        $return['price'] = $this->fen_change_yuan($data['itemPrice']);
        $return['goods_img'] = $data['picLink'];
        $return['goods_desc'] = $data['detailInfo'];
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
        foreach ($data['stockList'] as $item) {
            //存在商品货号
            $arr = $item;
            $arr['goods_from_id'] = $data['itemCode'];
            $arr['source'] = 'paipai';
            $arr['sku_id'] = $item['skuId'];
            $arr['goods_barcode'] = $item['stockLocalCode'];
            $arr['num'] = $item['stockCount'];
            $arr['price'] = $this->fen_change_yuan($item['stockPrice']);
            $arr['status'] = $item['status'];
            $arr['sku_properties'] = $item['saleAttr'];
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
        $return['tid'] = $data['dealCode'];
        $return['source'] = 'paipai'; //'平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $status = 0; //DS_UNKNOWN(0, “系统处理中,未知状态”),
        /*DS_WAIT_BUYER_PAY(1, “等待买家付款”),
        DS_WAIT_SELLER_DELIVERY(2, “买家已付款,等待卖家发货”),
        DS_WAIT_BUYER_RECEIVE(3, “商家已发货，等待买家收货”),
        DS_DEAL_END_NORMAL(4, “交易成功”),
        DS_DEAL_CANCELLED(5, “订单取消”),
        DS_SYSTEM_HALT(6, “系统暂停订单”),
        DS_SYSTEM_PAYING(7, “系统打款中”),
        DS_DEAL_REFUNDING(8, “退款处理中”),

        STATE_COD_WAIT_SHIP(41, “货到付款等待发货”) ,
        STATE_COD_SHIP_OK(42,“货到付款已发货”) ,
        STATE_COD_SIGN(43,“货到付款已签收”) ,
        STATE_COD_REFUSE(44,“货到付款拒签”) ,
        STATE_COD_SUCESS(45,“货到付款付款成功”) ,
        STATE_COD_CANCEL(46,“货到付款取消(关闭OR 拒签后关闭)”) ,*/
        if ($data['dealState'] == 'DS_WAIT_SELLER_DELIVERY') {
            $status = 1;
        }
        $return['status'] = $status; //不可转单 1：可转单  允许转单规则：已付款未发货',
        $return['seller_nick'] = $data['sellerNick'];
        $return['buyer_nick'] = $data['buyerName'];
        $pay_type = 0;

        /**
         * 订单的支付方式 UNKNOW:未知类型
         * TENPAY:财付通
         * OFF_LINE:线下交易
         * DEAL_BIZ_FLAG_HDFK:货到付款流程
         * MOBILE_SCORE:移动积分
         * WEIXIN_PAY:微信支付
         */
        if ($data['dealPayType'] == 'TENPAY' || $data['dealPayType'] == 'WEIXIN_PAY') {
            $pay_type = 1;
        }
        $return['pay_type'] = $pay_type;

        $return['receiver_country'] = '中国';
        $info = explode(' ',$data['receiverAddress']);

        $return['receiver_province'] = $info[0];
        $return['receiver_city'] = $info[1];
        //$return['receiver_district'] = $info[2];

        $return['receiver_address'] = $data['receiverAddress']; //$data['province'].$data['city'].$data['country'].$data['address']; //有省市区
        $return['receiver_addr'] = end($info); //无省市区
        $return['receiver_mobile'] = $data['receiverMobile'];
        $return['receiver_name'] = $data['receiverName'];
        $return['receiver_phone'] = $data['receiverPhone'];
        $return['receiver_zip_code'] = $data['receiverPostcode'];
        $return['num'] = $this->get_num($data['itemList']);
        $return['sku_num'] = count($data['itemList']);
        $return['order_money'] = $data['dealPayFeeTotal']/100;
        $return['express_money'] = $data['freight']/100;
        //$return['buyer_money'] = $data['totalCash'];
        $return['invoice_type'] = $data['invoiceContent'];
        $return['invoice_title'] = $data['invoiceTitle'];
        $return['seller_remark'] = $data['dealNote'];
        $return['buyer_remark'] = $data['buyerRemark'];
        $return['pay_time'] = $data['payTime'];
        /**
         * 运送类型
         * TRANSPORT_NONE：卖家包邮，无需买家关心运送
         * TRANSPORT_MAIL：邮政寄送
         * TRANSPORT_EXPRESS：快递
         * TRANSPORT_EMS：EMS
         * TRANSPORT_UNKNOWN：未知的运输方式
         */
        $return['transportType'] = $data['transportType'];
        $return['express_code'] = $data['wuliuId'];//物流id 或者保存wuliuCompany 物流公司名称
        $return['express_no'] = $data['wuliuCode'];
        $return['coupon_change_money'] = $data['couponFee'];
        $return['alipay_no'] = $data['tenpayCode'];
        $datetime = date('Y-m-d H:i:s');
        $return['order_first_insert_time'] = isset($data['createTime']) ? $data['createTime'] : '';
        $return['order_last_update_time']  = $return['last_update_time'] = $data['lastUpdateTime'];
        $return['first_insert_time'] = $datetime;
        return $return;
    }

    /**
     * 从子订单获得订单商品总数量
     * @param $itemList
     * @internal param $data
     * @return int
     */
    public function get_num($itemList)
    {
        $return = 0;
        if (count($itemList) > 0) {
            foreach ($itemList as $item) {
                $return += $item['itemDealCount'];
            }
        }
        return $return;
    }

    /**
     * 拍拍返回的金额都是以分为单位
     * @param $data
     * @return string
     * Author: yb.ding<ybd312@163.com>
     */
    function fen_change_yuan($data){
        return $data / 100;
    }
    /**
     * 转换订单明细信息为标准订单明细
     * @param array $data 第三方平台订单明细信息
     * @return array
     */
    public function _trans_order_detail(array $data)
    {
        $return = array();
        if (isset($data['itemList'])) {
            $count = count($data['itemList']);
            $i = 1;
            $avg_sum_money = 0; //均摊金额 累加
            foreach ($data['itemList'] as $item) {
                //存在商品货号
                $data['freight'] = $data['freight'] / 100;
                $data['totalCash'] = $data['totalCash']/100;

                $arr['tid'] = $data['dealCode'];
                $arr['source'] = 'paipai';
                $arr['oid'] = $item['dealSubCode']; //平台子订单编号
                $arr['title'] = $item['itemName'];
                $arr['price'] = $item['itemDealPrice']/100; //实际价格
                $arr['num'] = $item['itemDealCount'];
                $arr['goods_code'] = $item['itemCode'];
                $arr['sku_id'] = $item['skuId'];
                $arr['goods_barcode'] = $item['stockLocalCode'];
                $arr['total_fee'] = $item['itemDealCount'] * $arr['price']; //小计
                if ($i < $count) {
                    $avg_money = ($data['totalCash'] - $data['freight']) * ($arr['price'] * $item['itemDealCount'] / $data['totalCash']);
                    $avg_sum_money = $avg_sum_money + $avg_money;
                } else {
                    $avg_money = $data['totalCash'] - $avg_sum_money;
                }
                $arr['avg_money'] = $avg_money; //
                $arr['sku_properties'] = $item['stockAttr'];
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
        return $ret = load_model('source/ApiPaipaiTradeModel')->save_trade_and_order($shop_code, $data);
    }

    /**
     * 保存原始退单和退单数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_refund($shop_code, $data)
    {
        return $ret = load_model('source/ApiPaipaiRefundModel')->save_paipai_refund($shop_code, $data);
    }

    /**
     * 保存原始商品和sku数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_goods_and_sku($shop_code, $data)
    {
        return $ret = load_model('source/ApiPaipaiGoodsModel')->save_goods_info($shop_code, $data);
    }

    /**
     * 保存物流公司原始数据
     * @param string $shop_code 店铺代码
     * @param array $data 物流公司原始数据
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data)
    {
        // TODO: Implement save_source_logistics_company() method.
    }
    /**
     * @param $result
     * @return array
     */
    public function get_result($resp)
    {
        $ret = iconv('GBK', 'UTF-8//IGNORE', $resp);
        $ret = preg_replace("/(\"buyerName\":\"[^,\"]+),/i", "\\1\",", $ret);

        $temp_arr = json_decode($ret, true);
        if (empty($temp_arr)) {
            $ret = mb_convert_encoding($resp, 'UTF-8', 'GBK');
            $temp_arr = json_decode($ret, true);
            if (empty($temp_arr)) {
                $ret = str_replace('\\r', '\\\r', $ret);
                $ret = str_replace('\\n', '\\\n', $ret);
                $ret = str_replace('\\t', '\\\t', $ret);
                $ret = str_replace('\\\'', '\\\\\'', $ret);
                $ret = str_replace('\\\"', '\\\\\"', $ret);

                $patterns = array('/,+\s*\}/', '/,+\s*\]/', '/\\\{1,3}/', '/"\s+|\s+"/');
                $replacements = array('}', ']', '\\\\\\\\', '"');
                $ret = preg_replace($patterns, $replacements, $ret);

                $ret = json_decode($ret);
                if ($ret === NULL) {
                    $ret = str_replace(array('\\"', '\\'), array('**', '\\\\'), $resp);
                    $ret = mb_convert_encoding($ret, 'UTF-8', 'GBK');
                    $ret = json_decode($ret);
                    if ($ret === NULL) {
                        $_err = '拍拍接口(sellerUin:' . $this->uin . '):返回json字符串解析失败';
                        throw new ExtException($_err);
                        return false;
                    }
                    $temp_arr = $this->object_to_array($ret);
                }
                $temp_arr = $this->object_to_array($ret);
            }

        }
        return $temp_arr;
    }

    function object_to_array($e)
    {
        $e = (array )$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource')
                return;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array )$this->object_to_array($v);
        }
        return $e;
    }
}