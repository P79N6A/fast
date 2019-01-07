<?php
/**
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-11
 * @link http://wiki.open.weidian.com/index.php?title=%E6%8E%A5%E5%85%A5%E8%AF%B4%E6%98%8E
 * @微店两种模式 采用的服务型
 */
require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

class WeidianAPIModel extends AbsAPIModel
{
    public $gate = 'https://api.vdian.com/api';
    public $get_token_url = 'https://api.vdian.com/token';
    //public $version = '1.0'; //默认1.0 订单需要1.1
    private $grant_type = 'client_credential';
    private $appId;
    private $appSecret;
    private $access_token;
    private $format = 'json';
    private $nick;

    public function __construct($token)
    {
        $this->appId = $token['app_key'];
        $this->appSecret = $token['secret'];
        $this->access_token = $token['access_token'];
        $this->order_pk = 'order_id'; //订单主键
        $this->goods_pk = 'itemid'; //产品主键

        $this->nick = $token['seller_nick'];
    }

    /**
     * 第三方平台请求发送
     * @param $api
     * @param array $param
     * @return mixed
     * @link http://wiki.open.weidian.com/index.php?title=%E6%8E%A5%E5%85%A5%E8%AF%B4%E6%98%8E
     */
    public function request_send($api, $param = array())
    {
        //所有入参需要urlencode
        $data['param'] = $this->get_param($param); //业务参数
        $data['public'] = $this->get_public_param($api); //公共参数
        $url = $this->gate;
        $result = $this->exec($url, $data);
        return $result;
    }

    /**
     * 微店获取公共参数
     * @param $api
     * @return bool|mixed|string
     */
    public function get_public_param($api)
    {
        if (empty($this->access_token)) {
            //自用型应用获取Token
            $token_info = $this->get_access_token();
            if (PHP_VERSION >= 5.4) {
                $token_info = json_decode($token_info, true, 512, JSON_BIGINT_AS_STRING);
            } else {
                $token_info = json_decode($token_info, true);
            }
            if ($token_info['status']['status_code'] == 0) {
                $token = $token_info['result']['access_token'];
                //$expires_in = $token_info['result']['expires_in'];//有效时间 秒
            } else {
                return false;
            }
            $data['access_token'] = $token;
        } else {
            //服务型
            $data['access_token'] = $this->access_token; //服务型
        }
        $data['method'] = $api;
        $data['format'] = $this->format;
        $data['version'] = $this->get_version($api);

        return $this->get_param($data);
    }

    /**
     * 获得每个method的使用版本 默认 1.0
     * @param $api
     * @return string
     */
    public function get_version($api)
    {

        switch ($api) {
            case 'vdian.order.list.get':
                $version = '1.1';
                break;
            default:
                $version = '1.0';
                break;
        }
        return $version;
    }

    /**
     * 获取业务参数
     * @param $param
     * @return mixed|string
     */
    public function get_param($param)
    {
        $param = $this->str_urlencode($param, true);
        $json = json_encode($param);
        return $json;
    }

    /**
     * 遍历数组 将字符urlencode
     * @param $array
     * @param bool $apply_to_keys_also
     * @return mixed
     */
    public function str_urlencode($array, $apply_to_keys_also = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->str_urlencode($array[$key], $apply_to_keys_also);
            } else {
                $array[$key] = urlencode($value);
            }
        }
        return $array;
    }

    /**
     * 获取access token
     * @link http://wiki.open.weidian.com/index.php?title=%E8%8E%B7%E5%8F%96Token
     */
    public function get_access_token()
    {
        $url = $this->get_token_url;
        $data['grant_type'] = $this->grant_type;
        $data['appkey'] = $this->appId;
        $data['secret'] = $this->appSecret;
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
     * 批量下载商品信息
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://wiki.open.weidian.com/index.php?title=%E8%8E%B7%E5%8F%96%E5%85%A8%E5%BA%97%E5%95%86%E5%93%81
     */
    public function goods_list_download(array $data)
    {
        $params = array();
        //页码
        $params['page_num'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        //
        /*if (isset($data['start_modified'])) {
            $params['update_start'] = $data['start_modified'];
        } else {
            $params['update_start'] = date('Y-m-d H:i:s', strtotime('-15 day'));
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['update_end'] = $data['end_modified'];
        }*/

        //$params['orderby'] = 1;
        //        默认值1。
//        排序方式如下：
//
//        1---优先推荐
//        2---优先已售完
//        3---销量倒序
//        4---销量正序
//        5---库存倒序
//        6---库存正序

        //商家系统获取微店的商品列表
        $result = $this->request_send('vdian.item.list.get', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            throw new ExtException($msg);
        } else {
            $return = array(
                //转成与淘宝类似的返回格式
                'items' => array('item' => $result['result']['items']),
                'total_results' => $result['result']['item_num'],
            );
            return $return;
        }
    }

    /**
     * 单个商品信息下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://wiki.open.weidian.com/index.php?title=%E8%8E%B7%E5%8F%96%E5%8D%95%E4%B8%AA%E5%95%86%E5%93%81
     */
    public function goods_info_download($data)
    {
        $params = array();
        $params['itemid'] = $data;

        $result = $this->request_send('vdian.item.get', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            throw new ExtException($msg);
        } else {
            return $result['result'];
        }
    }

    /**
     * 批量商品信息下载
     * @param $data
     * @return array 返回共享表信息和平台原始数据
     */
    public function goods_info_download_multi($ids, $data = array())
    {
        $info = array();
        foreach ($ids as $productId) {
            $info[] = $this->goods_info_download($productId);
        }

        return $info;
    }

    /**
     * 订单列表下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://wiki.open.weidian.com/index.php?title=%E8%8E%B7%E5%8F%96%E8%AE%A2%E5%8D%95%E5%88%97%E8%A1%A8
     */
    public function order_list_download($data)
    {
        $params = array();
        //order_type 当version为 1.0时：此参数必选
        //当version为1.1时： 此参数为可选，不传返回全部状态的订单
        //finish（完成的订单）unpay（未付款订单）pend（待处理订单）close（关闭的订单）V1.1新增refund（退款中订单）
        //$params['order_type'] = isset($data['order_type']) ? $data['order_type'] : '';
        //页码
        $params['page_num'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        //
        if (isset($data['start_modified'])) {
            $params['update_start'] = $data['start_modified'];
        } else {
            $params['update_start'] = date('Y-m-d H:i:s', strtotime('-15 day'));
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['update_end'] = $data['end_modified'];
        }

        $result = $this->request_send('vdian.order.list.get', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            throw new ExtException($msg);
        } else {
            $return = array(
                //转成与淘宝类似的返回格式
                'trades' => array('trade' => $result['result']['orders']),
                'total_results' => $result['result']['order_num'],
                //total_num 查询到的订单总条数
                //order_num 列表中订单条数
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
     * @link http://wiki.open.weidian.com/index.php?title=%E8%8E%B7%E5%8F%96%E8%AE%A2%E5%8D%95%E8%AF%A6%E6%83%85
     */
    public function order_info_download($id, $data = array())
    {
        $params = array();
        $params['order_id'] = $id;
        $result = $this->request_send('vdian.order.get', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            throw new ExtException($msg);
        } else {
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
     * @link http://wiki.open.weidian.com/index.php?title=%E6%9B%B4%E6%96%B0%E5%95%86%E5%93%81%E5%9E%8B%E5%8F%B7
     * @link http://wiki.open.weidian.com/index.php?title=%E6%9B%B4%E6%96%B0%E5%95%86%E5%93%81%E4%BF%A1%E6%81%AF
     */
    public function inv_upload(array $data)
    {
        $params = array();
        $params['itemid'] = $data['goods_from_id'];
        //如果存在sku 则更新sku信息 否则更新商品信息
        if (isset($data['sku_id']) && !empty($data['sku_id'])) {
            $params['skus'] = array(
                'id' => $data['sku_id'],
                'stock' => $data['inv_num'],
            );
            $method = 'vdian.item.sku.update';
        } else {
            $params['stock'] = $data['inv_num'];
            $method = 'vdian.item.update';
        }
        $result = $this->request_send($method, $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            throw new ExtException($msg);
        } else {
            return $result['result'];
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
        $params['express_type'] = $data['logistics_id']; //快递公司
        $params['express_no'] = $data['express_no']; //物流单号

        if ($data['logistics_id'] == 0) {
            $params['express_custom'] = $data['express_custom']; //自定义快递
            if (empty($data['express_custom'])) {
                $oms_log['is_back_reason'] = $send_log['error_remark'] = '自定义快递字段不能为空';
                throw new ExtException('自定义快递字段不能为空');
            }
        }
        //当物流不是无需物流发货时 订单号必须
        if ($data['logistics_id'] != '999') {
            if (empty($data['express_no'])) {
                $oms_log['is_back_reason'] = $send_log['error_remark'] = '快递单号必须';
                throw new ExtException('快递单号必须');
            }
        }
        $result = $this->request_send('vdian.order.deliver', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if ($result['status']['status_code'] != 0) {
            $msg = $result['status']['status_reason'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result['result'];
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
        //$return['shop_code'] = '';
        $return['goods_name'] = $data['item_name'];
        $return['goods_code'] = $data['merchant_code'];
        $return['goods_from_id'] = $data['itemid'];
        $return['num'] = $data['stock'];
        $return['sell_nick'] = $this->nick;
        $return['source'] = 'weidian';
        $return['status'] = $data['status'] == 'onsale' ? 1 : 0; //onsale ：销售中instock：已下架delete：已删除
        $return['price'] = $data['price'];
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
                $arr = $item;
                $arr['goods_from_id'] = $item['itemid'];
                $arr['source'] = 'weidian';
                $arr['sku_id'] = $item['id'];
                $arr['goods_barcode'] = $item['sku_merchant_codes'];
                $arr['num'] = $item['stock'];
                $arr['price'] = $item['price'];
                $return[] = $arr;
            }
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
        $return['source'] = 'weidian'; //'平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $return['tid'] = $data['order_id']; //'业务系统店铺代码',
        $status = 0;
        if ($data['status'] == 'pay') {
            $status = 1;
        }
        $return['status'] = $status; //不可转单 1：可转单  允许转单规则：已付款未发货',
        $pay_type = 0;
        //货到付款 支付类型为1
        if ($data['pay_type'] == 1) {
            $pay_type = 1;
        }
        $data['name'] = $data['buyer_info']['name'];
        $data['province'] = $data['buyer_info']['province'];
        $data['city'] = $data['buyer_info']['city'];
        $data['region'] = $data['buyer_info']['region'];
        $data['post'] = $data['buyer_info']['post'];
        $data['phone'] = $data['buyer_info']['phone'];
        $data['address'] = $data['buyer_info']['address'];
        $data['self_address'] = $data['buyer_info']['self_address'];

        $return['pay_type'] = $pay_type; //'平台支付类型（转化后),0：款到发货 1：货到付款
        $return['seller_nick'] = $data['seller_name']; // '平台卖家昵称',
        $return['buyer_nick'] = $data['name'];
        $return['receiver_name'] = $data['name'];
        $return['receiver_country'] = '中国';
        $return['receiver_province'] = $data['province'];
        $return['receiver_city'] = $data['city'];
        $return['receiver_district'] = $data['region'];
        $return['receiver_address'] = $data['address']; //有省市区
        $return['receiver_addr'] = str_replace(array($return['receiver_province'], $return['receiver_city'], $return['receiver_district']), '', $return['receiver_address']);; //去掉省市区
        $return['receiver_street'] = $data['self_address']; //街道
        $return['receiver_zip_code'] = $data['post'];
        $return['receiver_mobile'] = $data['user_phone'];
        $return['receiver_phone'] = $data['phone'];
        $return['express_code'] = $data['express_type'];
        $return['express_no'] = $data['express_no'];
        $return['num'] = $data['quantity'];
        $return['sku_num'] = count($data['items']);
        $return['buyer_remark'] = $data['note']; //买家备注
        $return['seller_remark'] = $data['express_note']; //买家备注
        $return['order_money'] = $data['real_income_price'];
        $return['express_money'] = $data['express_fee'];
        $return['coupon_change_money'] = $data['discount_amount'];
        $return['is_change'] = 0;

        $return['order_first_insert_time'] = $data['add_time']; //'平台订单第一次插入订单时间',
        $return['order_last_update_time'] = $return['last_update_time'] = $data['update_time']; //'最后一次更新订单时间,数据在本平台的更新时间',
        $return['first_insert_time'] = date('Y-m-d H:i:s', time()); //'第一次插入订单时间,数据在本平台的更新时间',
        $return['lastchanged'] = date('Y-m-d H:i:s', time()); //,

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
        if (isset($data['items'])) {
            $count = count($data['items']);
            $i = 1;
            $avg_sum_money = 0; //均摊金额 累加
            foreach ($data['items'] as $item) {
                //存在商品货号
                $arr['tid'] = $data['order_id'];
                $arr['source'] = 'weidian';
                $arr['oid'] = $data['order_id'] . '_' . $item['sku_id']; //平台子订单编号
                $arr['title'] = $item['item_name'];
                $arr['price'] = $item['price'];
                $arr['num'] = $item['quantity'];
                $arr['goods_code'] = $item['merchant_code'];
                $arr['sku_id'] = $item['sku_id'];
                $arr['goods_barcode'] = $item['sku_merchant_code'];

                $sum_payment = $this->get_order_payment($data['tid']); //获得所有详细的实付金额
                if ($i < $count) {
                    $avg_money = ($data['real_income_price'] - $data['express_fee']) * ($item['price'] * $item['quantity'] / $sum_payment);
                    $avg_sum_money = $avg_sum_money + $avg_money;
                } else {
                    $avg_money = $data['real_income_price'] - $avg_sum_money;
                }

                $arr['avg_money'] = $avg_money; //avg_money=(real_income_price-express_fee) *
                //(items.(price*quantity))/(items.SUM(price*quantity))
                //去法保留两位小数，最后一个商品用减法

                $arr['lastchanged'] = date('Y-m-d H:i:s', time());
                $i++;
                $return[] = $arr;
            }
        }
        return $return;
    }

    /**
     * 获得每个交易下面总的商品实付金额
     * @param int $tid 交易编号
     */
    public function get_order_payment($tid)
    {
        $db = & $GLOBALS['context']->db;
        $sum_payment = $db->get_value('select sum(price*quantity) as sum_payment from api_weidian_order where order_id=:order_id', array(':order_id' => $tid));
        return $sum_payment;
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
        return $ret = load_model('source/ApiWeidianTradeModel')->save_trade_and_order($shop_code, $data);
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
        return $ret = load_model('source/ApiWeidianGoodsModel')->save_goods_info($shop_code, $data);
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
}