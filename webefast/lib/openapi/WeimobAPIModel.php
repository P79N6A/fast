<?php
/**
 * 微盟接口
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-16
 */
require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

class WeimobAPIModel extends AbsAPIModel
{

    //public $gate = 'https://open.weimob.com/api/mname/WE_MALL/cname';
    public $gate = 'https://opendev.weimob.com/api/mname/WE_MALL/cname'; //沙盒环境

    private $appId;
    private $appSecret;
    private $access_token;
    public $nick;

    public function __construct($token)
    {
        $this->appId = $token['app_key'];
        $this->appSecret = $token['secret'];
        $this->access_token = $token['access_token'];

        $this->order_pk = 'OrderNo'; //订单主键
        $this->goods_pk = 'spu_code'; //产品主键
        $this->nick = $token['seller_nick']; //买家昵称
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array())
    {
        $data = count($param) > 0 ? json_encode($param) : '';

        $url = $this->get_api_url($api);
        $result = $this->exec($url . '?accesstoken=' . $this->access_token, $data, 'post', array('header' => 'Content-Type: application/json'));
        return $result;
    }

    /**
     * 获取各个api的地址
     * @param $api
     * @return string
     */
    public function get_api_url($api)
    {
        return $this->gate . '/' . $api;
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
     * 批量下载商品信息
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=SpuController&act=Get
     */
    public function goods_list_download(array $data)
    {
        $params = array();
        $params['is_onsale'] = 2;//上下架状态	 下架=0，上架=1，所有=2
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 20;

        $result = $this->request_send('spuGet', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
            $return = array(
                'items' => array('item' => $result['data']['page_data']),
                'total_results' => count($result['data']['row_count']),
            );
            return $return;
        }
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
     */
    public function goods_info_download_multi($ids, $data = array())
    {
        $return = array();
        foreach ($ids as $id) {
            if ($data[$id]['is_spec']) {
                $data[$id]['skus'] = $this->sku_download($data[$id]['spu_code']);
            }

            $return[] = $data[$id];
        }
        return $return;
    }

    /**
     * 获取单个商品的sku信息
     * @param $spu_code
     * @return mixed
     * @throws ExtException
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=SkuController&act=Get
     */
    function sku_download($spu_code)
    {
        $params = array();
        $params['spu_code'] = $spu_code;//商品编码
        $result = $this->request_send('skuGet', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
            return $result['data'];
        }
    }

    /**
     * 订单列表下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=OrderController&act=Get
     */
    public function order_list_download($data)
    {
        $params = array();
        $params['order_type'] = 0; //所要类型=0,普通订单=1,众筹订单=2
        $params['search_type'] = 1; //商品名或编码=1,粉丝昵称=2,订单编号=3
        $params['search'] = ''; //搜索条件，如果为空则该条件自动忽略
        $params['order_state'] = 0; //所有=0，已完成=1，已关闭=2，未付款待发货=5，未付款已发货=6，已付款待发货=7，已付款已发货=8，已删除=9
        $params['order_fields'] = 'OrderNo'; //	Id = 订单标识,OrderNo = 订单编号,
        $params['order_detail_fields'] = 'Id'; //
        if (isset($data['start_modified'])) {
            $params['update_begin_time'] = $data['start_modified'];
        } else {
            $params['update_begin_time'] = date('Y-m-d H:i:s', strtotime('-5 day'));
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['update_end_time'] = $data['end_modified'];
        }

        //页码
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 40;
        $result = $this->request_send('orderGet', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
            $arr = $res = array();
            foreach ($result['data']['page_data'] as $item) {
                $arr['OrderNo'] = $item['order']['OrderNo'];
                $res[] = $arr;
            }
            $return = array(
                'trades' => array('trade' => $res),
                'total_results' => count($result['data']['row_count']),
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
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=OrderController&act=FullInfoGet
     */
    public function order_info_download($id, $data = array())
    {
        $params = array();
        $params['order_no'] = $id; //	Id = 订单标识,OrderNo = 订单编号,
        $params['order_fields'] = '*'; //
        $params['order_detail_fields'] = '*'; //

        $result = $this->request_send('orderFullInfoGet', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
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
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=InventoryController&act=Update
     */
    public function inv_upload(array $data)
    {
        $params = array();
        $params['spu_code'] = $data['goods_from_id'];
        $params['type'] = 'TOTAL';
        $params['sku_list'][] = array(
            'sku_code' => $data['sku_id'],
            'inventory' => (int)$data['inv_num'],
        );

        $result = $this->request_send('inventoryUpdate', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
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
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=LogisticsController&act=Delivery
     */
    public function logistics_upload(array $data)
    {
        $params = array();
        $need = ($data['express_no']) ? true : false;
        $phone = isset($data['contact_tel']) ? $data['contact_tel'] : '';
        if(empty($phone)){
            $phone = isset($data['contact_phone']) ? $data['contact_phone'] : '';
        }
        $params['deliveries'][] = array(
            'order_no' => $data['tid'],
            'need_delivery' => $need,//是否需要物流
            'carrier_code' => $data['logistics_id'], //承运商编码
            'carrier_name' => $data['logistics_name'], //承运商名称
            'express_no' => $data['express_no'], //物流单号
            'sender_address' => $data['address'], //发货人地址
            'sender_name' => $data['contact_person'], //发货人姓名
            'sender_tel' => $phone, //发货人电话
            //}
        );

        $result = $this->request_send('logisticsDelivery', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        } else {
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
     * @link http://112.124.16.233:8083/home/apiActionDoc?ctrl=CarrierController&act=Get
     */
    public function logistics_company_download()
    {
        $params = array();
        $result = $this->request_send('carrierGet', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        if ($result['code']['errcode'] != 0) {
            $msg = $result['code']['errmsg'];
            throw new ExtException($msg);
        } else {
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
        //$return['shop_code'] = '';
        $return['goods_name'] = $data['spu_name'];
        $return['goods_code'] = $data['spu_code'];
        $return['goods_from_id'] = $data['spu_code'];//没有返回id
        $return['num'] = $data['inventory'];
        $return['sell_nick'] = $this->nick;
        $return['source'] = 'weimob';
        $return['status'] = $data['is_onsale']; //onsale ：销售中instock：已下架delete：已删除
        $return['price'] = $data['low_sellprice']; //low_sellprice销售最低价 high_sellprice

        $return['goods_img'] = $data['default_img'];
        $return['has_sku'] = $data['is_spec'];
        $return['goods_desc'] = $data['description'];

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
        if (isset($data['skus'])) {
            foreach ($data['skus'] as $item) {
                //存在商品货号
                $arr['goods_from_id'] = $item['spu_code'];
                $arr['source'] = 'weimob';
                $arr['sku_id'] = $item['sku_code'];
                $arr['sku_properties'] = json_decode($item['sku_attrs']);
                $arr['num'] = $item['inventory'];
                $arr['price'] = $item['sale_price'];
                $arr['status'] = $item['is_onsale'];
                $return[] = $arr;
            }
        }
        return $return;
    }

    /**
     * 转换订单信息为标准订单
     * @param $shop_code
     * @param array $datas
     * @return array
     * @internal param array $data 第三方平台订单信息
     */
    public function _trans_order($shop_code, array $datas)
    {
        $data = $datas['order'];
        $return = array();
        $return['source'] = 'weimob'; //
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $return['tid'] = $data['OrderNo']; //'业务系统店铺代码',
        $status = 0;
        //data['PaymentId'] == '31471' 货到付款
        if (($data['PayStatus'] == 1 && $data['DeliveryStatus'] == 0) || $data['PaymentId'] == '31471') {
            $status = 1;
        }
        $return['status'] = $status; //0不可转单 1：可转单  允许转单规则：已付款未发货',
        $pay_type = 0;
        if ($data['IsOnlinePay'] == 1) {
            $pay_type = 1;
        }
        $return['pay_type'] = $pay_type;
        $return['pay_time'] = $data['PayTime'];
        $return['seller_nick'] = $this->nick;

        $return['buyer_nick'] = $data['ConsigneeName'];
        $return['receiver_name'] = $data['ConsigneeName'];
        $return['receiver_country'] = '中国';
        $addr = explode(' ', $data['ConsigneeAddress']);
        $data['province'] = isset($addr[0]) ? $addr[0] : '';
        $data['city'] = isset($addr[1]) ? $addr[1] : '';
        $data['region'] = isset($addr[2]) ? $addr[2] : '';
        $return['receiver_province'] = $data['province'];
        $return['receiver_city'] = $data['city'];
        $return['receiver_district'] = $data['region'];

        $return['receiver_address'] = $data['ConsigneeAddress']; //有省市区
        $return['receiver_addr'] = str_replace(array($return['receiver_province'], $return['receiver_city'], $return['receiver_district']), '', $return['receiver_address']); //去掉省市区
        //四个直辖市 会匹配不对
        if (trim(mb_substr($return['receiver_addr'], 0, 2, 'utf-8')) == '市') {
            $return['receiver_addr'] = mb_substr($return['receiver_addr'], 2, null, 'utf-8');
        }
        // $return['receiver_street'] = $data['self_address'];//街道
        //$return['receiver_zip_code'] = $data['post'];
        $return['receiver_mobile'] = $data['ConsigneeTel'];
        //$return['receiver_phone'] = $data['phone'];
        $return['express_code'] = $data['DeliveryType'];
        $return['express_no'] = $data['DeliveryNo'];
        $return['num'] = $data['TotalQty'];
        //$return['sku_num'] = count($data['items']);
        $return['buyer_remark'] = $data['Remark']; //买家备注
        $return['seller_remark'] = $data['Remark']; //买家备注
        $return['order_money'] = $data['RealAmount'];
        $return['express_money'] = $data['DeliveryFee'];
        $return['coupon_change_money'] = $data['CouponsAmount'];
        $return['is_change'] = 0;

        $return['order_first_insert_time'] = $data['CreateTime']; //'平台订单第一次插入订单时间',
        $return['order_last_update_time'] = $return['last_update_time'] = $data['LastUpdateTime']; //'最后一次更新订单时间,数据在本平台的更新时间',
        $return['first_insert_time'] = date('Y-m-d H:i:s', time()); //'第一次插入订单时间,数据在本平台的更新时间',
        $return['lastchanged'] = date('Y-m-d H:i:s', time()); //,

        return $return;
    }

    /**
     * 转换订单明细信息为标准订单明细
     * @param array $data 第三方平台订单明细信息
     * @return array
     * @return array
     */
    public function _trans_order_detail(array $data)
    {
        $return = array();
        if (isset($data['goods'])) {
            $count = count($data['goods']);
            $i = 1;
            $avg_sum_money = 0; //均摊金额 累加
            foreach ($data['goods'] as $item) {
                //存在商品货号
                $arr['tid'] = $data['order']['OrderNo'];
                $arr['source'] = 'weimob';
                $arr['oid'] = $item['OrderId'] . '_' . $item['ProductsCode']; //平台子订单编号
                $arr['title'] = $item['ItemName'];
                $arr['price'] = $item['RealPrice']; //实际价格
                $arr['num'] = $item['Qty'];
                $arr['goods_code'] = $item['GoodsCode'];
                $arr['sku_id'] = $item['ProductsCode'];
                $arr['goods_barcode'] = $item['ProductsCode'];
                $arr['sku_properties'] = $item['ItemDescription'];
                $arr['total_fee'] = $item['RealPrice'] * $item['Qty'];//平台应付金额,应付金额=（商品价格 * 商品数量 + 手工调整金额 - 子订单级订单优惠金额）
                $arr['adjust_fee'] = 0;

                //$sum_payment = $this->get_order_payment($data['tid']); //获得所有详细的实付金额
                if ($i < $count) {
                    $avg_money = ($data['RealAmount'] - $data['DeliveryFee']) * ($item['RealPrice'] * $item['Qty'] / $data['RealAmount']);
                    $avg_sum_money = $avg_sum_money + $avg_money;
                } else {
                    $avg_money = $data['real_income_price'] - $avg_sum_money;
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
        return $ret = load_model('source/ApiWeimobTradeModel')->save_trade_and_order($shop_code, $data);
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
        return $ret = load_model('source/ApiWeimobGoodsModel')->save_goods_info($shop_code, $data);
    }

    /**
     * 保存物流公司原始数据
     * @param string $shop_code 店铺代码
     * @param array $data 物流公司原始数据
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data)
    {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'weimob');
    }
}