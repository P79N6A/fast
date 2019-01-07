<?php
/**
 * 有赞API类
 * Author: yb.ding<yb.ding@baisonmail.com>
 * Date: 15-4-7
 */
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

class YouzanAPIModel extends AbsAPIModel
{
    const VERSION = '1.0';

    public $gate = 'http://open.koudaitong.com/api/entry';

    private $appId;
    private $appSecret;
    private $format = 'json';
    //private $signMethod = 'md5';
    public $nick;

    public function __construct($token)
    {
        $this->appId = $token['app_key'];
        $this->appSecret = $token['secret'];

        $this->order_pk = 'tid'; //订单主键
        $this->goods_pk = 'num_iid'; //产品主键
        $this->nick = $token['seller_nick']; //买家昵称
    }

    /**
     * 第三方平台请求发送
     */
    public function request_send($api, $param = array())
    {
        $data = $param;
        $data['app_id'] = $this->appId;
        $data['method'] = $api;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['format'] = $this->format;
        $data['sign_method'] = 'md5';
        $data['v'] = self::VERSION;

        $sign = $this->sign($data);
        $data['sign'] = $sign;

        $url = $this->gate;
        $result = $this->exec($url, $data);
        return $result;

    }

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    public function sign($param = array())
    {
        if (!is_array($param)) $param = array();

        ksort($param);
        $text = '';
        foreach ($param as $k => $v) {
            $text .= $k . $v;
        }

        return md5($this->appSecret . $text . $this->appSecret);
    }

    /**
     * 已卖出订单列表下载
     * @param array $data
     * @return array|void
     * @throws ExtException
     * @link http://open.koudaitong.com/doc/api?method=kdt.trades.sold.get
     *
     */
    public function order_list_download($data)
    {
        $params = array();
        $params['fields'] = 'tid'; //返回订单字段，为空则返回所有值 非必须
        /**
         *
         * TRADE_NO_CREATE_PAY（没有创建支付交易）
         * WAIT_BUYER_PAY（等待买家付款）
         * WAIT_SELLER_SEND_GOODS（等待卖家发货，即：买家已付款）
         * WAIT_BUYER_CONFIRM_GOODS（等待买家确认收货，即：卖家已发货）
         * TRADE_BUYER_SIGNED（买家已签收）
         * TRADE_CLOSED（付款以后用户退款成功，交易自动关闭）
         * TRADE_CLOSED_BY_USER（付款以前，卖家或买家主动关闭交易）
         * ALL_WAIT_PAY（包含：WAIT_BUYER_PAY、TRADE_NO_CREATE_PAY）
         * ALL_CLOSED（包含：TRADE_CLOSED、TRADE_CLOSED_BY_USER）
         * */
        //$params['status'] = '';//返回订单交易状态 非必须
        //start_update end_update 交易状态更新的开始时间
        //start_created end_created 交易创建开始时间
        if (isset($data['start_modified'])) {
            $params['start_created'] = $data['start_modified'];
        } else {
            $params['start_created'] = date('Y-m-d H:i:s', strtotime('-5 day'));
        }
        //结束时间
        if (isset($data['end_modified']) && !empty($data['end_modified'])) {
            $params['end_created'] = $data['end_modified'];
        }

        //$params['end_created'] = date('Y-m-d H:i:s',time());
        //微信粉丝ID
        //$params['weixin_user_id'] = '';
        //买家昵称
        //$params['buyer_nick'] = '';
        //页码
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 40;
        //是否启用has_next的分页方式，是的话返回的结果中不包含总记录数，但是会新增一个是否存在下一页的的字段 默认false
        //$params['use_has_next'] = true;

        $result = $this->request_send('kdt.trades.sold.get', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            /**
             * -1    系统错误    系统内部错误，请直接联系技术支持，或邮件给 openapi@qima-inc.com
             * 40001    未指定 AppId    请求时传入 AppId
             * 40002    无效的App    申请有效的 AppId
             * 40003    无效的时间参数    以当前时间重新发起请求；如果系统时间和服务器时间误差超过10分钟，请调整系统时间
             * 40004    请求没有签名    请使用协议规范对请求中的参数进行签名
             * 40005    签名校验失败    检查 AppId 和 AppSecret 是否正确；如果是自行开发的协议分装，请检查代码
             * 40006    未指定请求的 Api 方法    指定 Api 方法
             * 40007    请求非法的方法    检查请求的方法的值
             * 40008    校验团队信息失败    检查团队是否有效、是否绑定微信
             * 40009    未指定 AccessToken    请求时传入 AccessToken
             * 40010    AccessToken不存在或已过期    传入有效的 AccessToken
             * 40011    无效的 AccessToken    传入有效的 AccessToken
             * 40012    请求的 Api 方法不在访问范围内    检查请求的方法
             * 41000    请求方法的应用级输入参数错误    阅读接口文档，检查调用接口时是否缺少必传参数
             * 50000    请求方法时业务逻辑发生错误    阅读返回的 error_response 里的 msg 字段，做相应的逻辑调整
             * $code = $result['error_response']['code'];//错误码
             **/

            $msg = $result['error_response']['msg'];
            throw new ExtException($msg);
        } else {
            $return = array(
                //转成与淘宝类似的返回格式
                'trades' => array('trade' => $result['response']['trades']),
                'total_results' => $result['response']['total_results'],
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
     * @link http://open.koudaitong.com/doc/api?method=kdt.trade.get
     */
    public function order_info_download($id, $data = array())
    {
        $params = array();
        //返回订单字段，为空则返回所有值 非必须
        //$params['fields'] = '';
        $params['tid'] = $id;

        $result = $this->request_send('kdt.trade.get', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        //dump($result,1);
        if (isset($result['error_response'])) {
            $msg = $result['error_response']['msg'];
            throw new ExtException($msg);
        } else {
            //转成与淘宝类似的返回格式
            $return = $result['response']['trade'];
            return $return;
        }
    }

    /**
     * 批量发送第三方平台请求
     */
    public function request_send_multi($api, $params = array())
    {
        // TODO: Implement request_send_multi() method.

    }

    /**
     * 批量下载商品信息
     * @param array $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://open.koudaitong.com/doc/api?method=kdt.items.onsale.get(获取出售中的商品列表)
     * @link1 http://open.koudaitong.com/doc/api?method=kdt.items.inventory.get(获取仓库中的商品列表)
     */
    public function goods_list_download(array $data)
    {
        $params = array();
        $params['fields'] = 'num_iid'; //返回订单字段，为空则返回所有值 非必须


        //页码
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        //每页条数
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        //是否启用has_next的分页方式，是的话返回的结果中不包含总记录数，但是会新增一个是否存在下一页的的字段 默认false
        //$params['use_has_next'] = true;
        //获取仓库中的商品  kdt.items.onsale.get（出售商品列表）
        if (isset($data['is_onsale']) && $data['is_onsale'] == 1) {
            //出售商品列表
            $result = $this->request_send('kdt.items.onsale.get', $params);
        } else {
            //仓库中的商品列表（默认）
            $result = $this->request_send('kdt.items.inventory.get', $params);
        }


        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['msg'];
            throw new ExtException($msg);
        } else {
            $return = array(
                //转成与淘宝类似的返回格式
                'items' => array('item' => $result['response']['items']),
                'total_results' => $result['response']['total_results'],
            );
            return $return;
        }
    }

    /**
     * 单个商品信息下载
     * @param $data
     * @throws ExtException
     * @return array 返回共享表信息和平台原始数据
     * @link http://open.koudaitong.com/doc/api?method=kdt.item.get
     */
    public function goods_info_download($data)
    {
        $params = array();
        $params['num_iid'] = $data; //返回订单字段，为空则返回所有值 非必须
        $result = $this->request_send('kdt.item.get', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['msg'];
            throw new ExtException($msg);
        } else {
            return $result['response']['item'];
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
        $info = array();
        foreach ($ids as $productId) {
            $info[] = $this->goods_info_download($productId);
        }

        return $info;
    }

    /**
     * 库存信息回传
     * @param array $data
     * @throws ExtException
     * @return array 返回结果和第三方平台原始信息
     * @link http://open.koudaitong.com/doc/api?method=kdt.item.update(更新单个商品信息)
     * @link http://open.koudaitong.com/doc/api?method=kdt.item.sku.update(更新sku信息)
     */
    public function inv_upload(array $data)
    {
        $params = array();
        $params['num_iid'] = $data['goods_from_id'];
        //$params['outer_id'] = $data['outer_id'];
        //如果存在sku 则更新sku信息 否则更新商品信息
        if (isset($data['sku_id']) && !empty($data['sku_id'])) {
            $params['sku_id'] = $data['sku_id'];
            $params['quantity'] = $data['inv_num'];
            $method = 'kdt.item.sku.update';
        } else {
            $params['quantity'] = $data['inv_num'];
            // $params['sku_quantities']   = $data['sku_quantities'];//Sku的数量串
            //$params['sku_outer_ids']   = $data['sku_outer_ids'];//Sku的商家编码
            $method = 'kdt.item.update';
        }
        $result = $this->request_send($method, $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['msg'];
            throw new ExtException($msg);
        } else {
            return $result['response']['sku'];
        }

    }

    /**
     * 批量库存信息回传
     * @param $data
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
        //$params['tid'] = $data['tid'];//交易编号
        // $params['outer_id'] = $data['outer_id'];//外部交易编号
        $params['is_no_express'] = $data['is_no_express']; //发货是否无需物流 如果为 0 则必须传递物流参数，如果为 1 则无需传递物流参数（out_stype和out_sid），默认为 0
        $params['out_stype'] = $data['logistics_id']; //快递公司
        $params['out_sid'] = $data['express_no']; //物流单号

        $result = $this->request_send('kdt.logistics.online.confirm', $params);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['msg'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result;
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
        $return['goods_name'] = $data['title'];
        $return['goods_code'] = $data['outer_id'];
        $return['goods_from_id'] = $data['num_iid'];
        $return['num'] = $data['num'];
        $return['seller_nick'] = $this->nick;
        $return['source'] = 'youzan';
        // $return['status'] = '';
        // $return['stock_type'] = '';
        // $return['onsale_time']= '';

        if (isset($data['skus']) && count($data['skus']) > 0) {
            $return['has_sku'] = 1;
        }
        $return['price'] = $data['price'];
        $return['goods_img'] = $data['pic_url'];
        $return['goods_desc'] = $data['desc'];
        $return['is_lock_inv'] = $data['is_lock'];
        $return['lastchanged'] = time();

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
                $arr['goods_from_id'] = $item['num_iid'];
                $arr['source'] = 'youzan';
                $arr['sku_id'] = $item['sku_id'];
                $arr['goods_barcode'] = $item['outer_id'];
                $arr['num'] = $item['quantity'];
                $arr['price'] = $item['price'];
                $arr['sku_properties_name'] = $item['properties_name'];
                $arr['lastchanged'] = time();
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
        $return = $data;
        //必须字段
        $return['source'] = 'youzan'; //'平台来源标识符,淘宝：taobao，后台：houtai，京东：jingdong，唯品会：weipinhui，当当：dangdang，拍拍：paipai，1号店：yihaodian，亚马逊：yamaxun，凡客：vjia，优购：yougou，聚美优品：jumei，麦网：m18，库巴：coo8，苏宁：suning，名鞋库：scn，阿里巴巴：alibaba，微购物：weigou，口袋通:koudaitong,工行:gonghang,银泰:yintai,走秀网:zouxiu,贝贝网:beibei,蘑菇街:mogujie,拍鞋网:paixie,好乐买:okbuy,乐蜂:lefeng',
        $return['shop_code'] = $shop_code; //'业务系统店铺代码',
        $status = 0;
        if ($data['status'] == 'WAIT_SELLER_SEND_GOODS') {
            $status = 1;
        }
        $return['status'] = $status; //不可转单 1：可转单  允许转单规则：已付款未发货',
        $pay_type = 0;
        //货到付款 支付类型为1
        if ($data['pay_type'] == 'CODPAY') {
            $pay_type = 1;
        }
        $return['pay_type'] = $pay_type; //'平台支付类型（转化后),0：款到发货 1：货到付款
        $return['seller_nick'] = isset($data['orders'][0]['seller_nick']) ? $data['orders'][0]['seller_nick'] : $this->nick; // '平台卖家昵称',
        // $return['buyer_nick'] = $data['buyer_nick'];//'平台买家昵称',
        //  $return['receiver_name'] = $data['receiver_name'];//'平台收货人名称',
        $return['receiver_province'] = $data['receiver_state']; // '省（转化后）',
        // $return['receiver_city']=$data['receiver_city'];//'市（转化后）',
        // $return['receiver_district']=$data['receiver_district'];//'区（转化后）',
        $return['receiver_address'] = $data['receiver_state'] . $data['receiver_city'] . $data['receiver_district'] . $data['receiver_address']; //'地址（含省市区）',
        $return['receiver_addr'] = $data['receiver_address']; //'地址（不含省市区）',
        //  $return['receiver_mobile']=$data['receiver_mobile'];//'平台电话',
        $return['buyer_remark'] = $data['buyer_message']; //'平台买家留言',
        $return['seller_remark'] = $data['trade_memo']; //'平台商家备注',
        $return['order_money'] = $data['payment']; //'平台实付金额',
        $return['express_money'] = $data['post_fee']; //'平台运费',
        //$return['invoice_type']='';//  '平台发票类型',
        //$return['invoice_title']='';// '平台发票抬头',
        //$return['invoice_content']='';//  '平台发票内容',
        //$return['invoice_money']='';// '平台发票金额',
        //$return['invoice_pay_type']='';//'平台发票支付方式',
        $return['order_last_update_time'] = $data['update_time']; //'平台订单最后一次更新订单时间',
        $return['order_first_insert_time'] = $data['created']; //'平台订单第一次插入订单时间',
        $return['last_update_time'] = $data['update_time']; //'最后一次更新订单时间,数据在本平台的更新时间',
        $return['first_insert_time'] = date('Y-m-d H:i:s', time()); //'第一次插入订单时间,数据在本平台的更新时间',
        $return['lastchanged'] = date('Y-m-d H:i:s', time()); //,

        //可选字段
        $return['receiver_country'] = '中国'; //'国家（转化后）',
        $return['receiver_zip_code'] = $data['receiver_zip']; //'平台邮政编码',
        $return['express_code'] = $data['shipping_type']; //'配送方式CODE',
        // $return['receiver_phone']='';//'平台固定电话',
        //$return['receiver_email']='';//'平台email',
        $return['express_no'] = ''; //'平台快递单号',
        //  $return['num']=$data['num'];//'平台数量',
        $return['seller_flag'] = $data['seller_flag']; //'平台订单的旗帜',
        //$return['receiver_street']='';//'街道（转化后）',
        //$return['hope_send_time']='';//'期望配送时间',
        //$return['sell_record_code'] = '';//'业务系统单据编号(订单号)',
        $return['sku_num'] = count($data['orders']); //'平台sku种类数量（转化后）',
        //$return['goods_weight']='';//'平台商品总重量(g)',
        //$return['delivery_money']='';//'配送手续费',
        //$return['gift_coupon_money']='';//'买家支付礼券金额',
        //$return['gift_money']='';//'买家支付礼品卡金额',
        //$return['buyer_money']='';//''买家已付款，买家真实付款',
        //$return['alipay_no']='';//'支付流水号，如：2009112081173831',
        //$return['integral_change_money']='';//'买家实际使用积分兑换金额',
        //$return['coupon_change_money']='';// '平台抵扣金额。如：优惠劵',
        //$return['balance_change_money']='';//'余额抵扣金额',
        //$return['is_lgtype']='';//'平台是否需要物流宝发货的标识,如果为true，则需要可以用物流宝来发货，如果未false，则该订单不能用物流宝发货',
        // $return['seller_rate']='';//'卖家是否已评价',
        //$return['buyer_rate']='';// '买家是否已评价',
        // $return['trade_from'] = '';//'WAP(手机);HITAO(嗨淘);TOP(TOP平台);TAOBAO(普通淘宝);JHS(聚划算)',
        //$return['change_remark']='';//  '转单日志',
        //$return['is_detail']='';//  '0：不处理 1：需要处理',
        //  $return['is_change']='';// '0：未转单 1：已转单',
        // $return['is_allow_change']='';//'0：不允许转单 1：允许转单',
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
        if (isset($data['orders'])) {
            $count = count($data['orders']);
            $i = 1;
            $avg_sum_money = 0; //均摊金额 累加
            foreach ($data['orders'] as $item) {
                //存在商品货号
                $arr = $item;
                $arr['tid'] = $data['tid'];
                $arr['source'] = 'youzan';
                $item['sku_id'] = isset($item['sku_id']) ? $item['sku_id'] : '';
                $arr['oid'] = $data['tid'] . '_' . $item['sku_id']; //平台子订单编号
                $arr['goods_code'] = isset($item['outer_item_id']) ? $item['outer_item_id'] : '';
                $arr['goods_barcode'] = isset($item['outer_sku_id']) ? $item['outer_sku_id'] : '';
                //$arr['adjust_fee'] = '';
                $sum_payment = $this->get_order_payment($data['tid']); //获得所有详细的实付金额
                if ($i < $count) {
                    $avg_money = ($data['payment'] - $data['post_fee']) * ($item['payment'] / $sum_payment);
                    $avg_sum_money = $avg_sum_money + $avg_money;
                } else {
                    $avg_money = $data['payment'] - $avg_sum_money;
                }

                $arr['avg_money'] = $avg_money; //平台子订单均摊金额avg_money=(trade.payment-trade.post_fee)
                //                    *(order.(payment))/(order.SUM(payment))

                // $arr['end_time'] = '';
                // $arr['consign_time'] = '';
                // $arr['express_code'] = '';
                //  $arr['express_company_name'] = '';
                // $arr['express_no'] = '';
                $arr['sku_properties'] = $item['sku_properties_name'];
                //$arr['first_insert_time'] = date('Y-m-d H:i:s', time());
                //$arr['last_update_time'] = date('Y-m-d H:i:s', time());
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
        $sum_payment = $db->get_value('select sum(payment) as sum_payment from api_youzan_order where tid=:tid', array(':tid' => $tid));
        return $sum_payment;
    }

    /**
     * 转换退单信息为标准退单信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param $shop_code
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
     * 单个退单明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param int
     * @return array 返回退单明细信息
     */
    public function refund_info_download($refund_id, $refund_info)
    {

    }

    /**
     * 退单列表下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param array $data
     * @return array 返回退单列表信息
     */
    public function refund_list_download(array $data)
    {

    }

    /**
     * 保存原始订单和订单明细数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    public function save_source_order_and_detail($shop_code, $data)
    {
        return $ret = load_model('source/ApiYouzanTradeModel')->save_trade_and_order($shop_code, $data);
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
        return $ret = load_model('source/ApiYouzanGoodsModel')->save_goods_info($shop_code, $data);
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