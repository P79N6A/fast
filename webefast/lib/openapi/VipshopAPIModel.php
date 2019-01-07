<?php

require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

/**
 * 唯品会API处理类
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-30
 */
class VipshopAPIModel extends AbsAPIModel {

    /**
     * 接口网关地址
     * @var string 
     */
    public $gate = 'http://visopen.vipshop.com/api/scm/';
    private $vis_sid = '852';
    private $vis_source = '0d87e2a2154ebd3ca72474d3c24b5f8e';
    private $vendor_id = '8165';

    /**
     * 接口模式 JIT: jit模式 POP: 普通直发模式
     * @var string
     */
    private $type = 'pop';

    /**
     * 接口实例化
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param array $token 应用及授权信息数组
     */
    public function __construct($token) {
        $this->vis_sid = $token['vis_sid'];
        $this->vis_source = $token['vis_source'];
        $this->vendor_id = $token['vendor_id'];
        $this->type = strtoupper($token['type']);
        
        $this->order_pk = 'order_sn';
        $this->goods_pk = '';
    }

    #####################################################################
    
    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param string $api API名称
     * @param array $param 请求的业务参数
     * @return string 返回json字符串
     * @link http://visopen.vipshop.com/doc/intro.php 通用请求参数说明
     */

    public function request_send($api, $param = array()) {
        //增加系统级参数
        $data = $param;
        $data['call_time'] = time();
        $data['sid'] = $this->vis_sid;
        $data['o'] = 'json';

        $data['token'] = $this->sign(array(
            'api_name' => $api,
            'call_time' => $data['call_time']
        ));

        //发送请求
        $url = $this->gate . $api . '.php';
        $result = $this->exec($url, $data);

        return $result;
    }
    
    /**
     * API请求批量发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param string $api API名称
     * @param array $params 请求参数
     * @return array 返回多个请求的json字符串
     * @link http://visopen.vipshop.com/doc/intro.php 通用请求参数说明
     */
    public function request_send_multi($api, $params = array()) {
        $datas = array();
        foreach ($params as $param) {
            $data = $param;
            //增加系统级参数
            $data['call_time'] = time();
            $data['sid'] = $this->vis_sid;
            $data['o'] = 'json';

            $data['token'] = $this->sign(array(
                'api_name' => $api,
                'call_time' => $data['call_time']
            ));
            $datas[] = $data;
        }

        //发送请求
        $url = $this->gate . $api . '.php';
        $result = $this->multiExec($url, $datas);

        return $result;
    }

    /**
     * 生成唯品会的签名
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param array $param 包含call_time和api_name
     */
    public function sign($param = array()) {
        $api_name = isset($param['api_name']) ? $param['api_name'] : '';
        $call_time = isset($param['call_time']) ? $param['call_time'] : time();
        $token = md5($this->vis_source . $api_name . $call_time);
        return $token;
    }

    #####################################################################
    /**
     * 订单列表下载，需要路由jit模式和直发模式
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param array $data 下载条件
     * @return array
     */
    public function order_list_download($data) {
        if($this->type=='POP'){
            return $this->order_list_download_by_pop($data);
        }elseif($this->type=='JIT'){
            return $this->order_list_download_by_jit($data);
        }
    }
    
    /**
     * 直发模式订单列表下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-30
     * @param array $data 下载条件
     * @return array
     * @param type $data
     * @link http://visopen.vipshop.com/doc/api.php?n=pop/order_list
     */
    private function order_list_download_by_pop($data){
        $params['p'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['l'] = isset($data['page_size']) ? $data['page_size'] : 10;

        if (isset($data['start_modified'])) {
            $params['st_add_time'] = $data['start_modified'];
            $params['et_add_time'] = date('Y-m-d H:i:s');
        }else{
            $params['st_add_time'] = date('Y-m-d H:i:s', strtotime('-1 day',time()));
            $params['et_add_time'] = date('Y-m-d H:i:s');
        }
        
        $result = $this->request_send('pop/order_list', $params);
        $return = $this->json_decode($result);
        
        if($return['status']==-1){
            throw new ExtException($return['data']['error_msg'], $return['data']['error_code']);
        }

        return array(
            'trades' => array('trade' => $return['data']['list']),
            'total_results' => $return['data']['total'],
        );
        
    }
    
    private function order_list_download_by_jit($data){
        //todo
    }

    public function _trans_goods(array $data) {
        
    }

    /**
     * 转换唯品会订单到标准订单
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-04
     * @param string $shop_code 店铺代码
     * @param array $data 原始数据
     */
    public function _trans_order($shop_code, array $data) {
        $datetime = date('Y-m-d H:i:s');
        $num = 0;
        foreach($data['list'] as $v){
            $num += $v['amount'];
        }
        
        $return = array();
        $return['tid'] = $data['base']['order_sn'];
        $return['source'] = 'vipshop';
        $return['shop_code'] = $shop_code;
        $return['status'] = ($data['base']['stat'] == 10) ? 1 : 0;
        $return['trade_from'] = '';
        $return['pay_type'] = 0;
        $return['pay_time'] = $data['base']['add_time'];   //???????????????????????????
        $return['seller_nick'] = $data['base']['vendor_name'];
        $return['buyer_nick'] = $data['base']['buyer'];
        $return['receiver_name'] = $data['base']['buyer'];
        $return['receiver_country'] = ($data['base']['country_id'] == 'CN') ? '中国' : $data['base']['country_id'];
        $return['receiver_province'] = $data['base']['state'];
        $return['receiver_city'] = $data['base']['city'];
        $return['receiver_district'] = $data['base']['county'];
        //$return['receiver_street'] = $data[''];
        //含省市区
        $return['receiver_address'] = $data['base']['address'];
        //不含省市区
        $return['receiver_addr'] = $this->remove_address($data['base']['address'], array(
            $return['receiver_province'], $return['receiver_city'], $return['receiver_district']
        )); 
        
        $return['receiver_zip_code'] = $data['base']['postcode'];
        $return['receiver_phone'] = $data['base']['tel'];
        $return['receiver_mobile'] = $data['base']['mobile'];
        $return['receiver_email'] = '';
        
        $return['express_code'] = '';
        $return['express_no'] = '';
        $return['hope_send_time'] = $data['base']['transport_day'];
        $return['num'] = $num;
        $return['sku_num'] = count($data['list']);
        //$return['goods_weight'] = ;
        $return['buyer_remark'] = $data['base']['remark'];
        //$return['seller_remark'] = ;
        //$return['seller_flag'] = ;
        $return['order_money'] = $data['base']['goods_money'];
        $return['express_money'] = $data['base']['carriage'];
        $return['delivery_money'] = 0; //TODO
        $return['gift_coupon_money'] = 0; //TODO
        $return['gift_money'] = 0; //TODO
        //整张出库单商品金额总和(计算发票金额 == 整张出库单商品金额总和 + 快递费用 - 优惠金额 - 促销优惠金额)
        $return['buyer_money'] = $data['base']['goods_money'] + $data['base']['carriage'] - $data['base']['favourable_money'] - $data['base']['ex_fav_money'];
        $return['alipay_no'] = '';
        $return['integral_change_money'] = 0; //TODO
        $return['coupon_change_money'] = 0; //TODO
        $return['balance_change_money'] = 0; //TODO
        //$return['is_lgtype'] = 0; //没有
        //$return['seller_rate'] = 0; //没有
        //$return['buyer_rate'] = 0; //没有
        //$return['invoice_type'] = '';
        $return['invoice_title'] = $data['base']['invoice'];
        //$return['invoice_content'] = '';
        //(计算发票金额 == 整张出库单商品金额总和 + 快递费用 - 优惠金额 - 促销优惠金额)
        $return['invoice_money'] = $data['base']['goods_money'] + $data['base']['carriage'] - $data['base']['favourable_money'] - $data['base']['ex_fav_money'];
        //$return['invoice_pay_type'] = '';
        $return['order_last_update_time'] = $data['base']['add_time'];
        $return['order_first_insert_time'] = $data['base']['add_time'];
        $return['last_update_time'] = $data['base']['add_time'];
        $return['first_insert_time'] = $datetime;
        return $return;
    }

    /**
     * 转换原始数据到标准子订单
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-04
     * @param array $data 原始订单信息
     * @return array
     */
    public function _trans_order_detail(array $data) {
        $return = array();
        if (isset($data['list'])) {
            foreach ($data['list'] as $value) {
                $detail = array();
                $detail['tid'] = $data['base']['order_sn'];
                $detail['source'] = 'vipshop';
                $detail['oid'] = $data['base']['order_sn'] . '-' . $value['good_sn'];
                $detail['status'] = 1; //TODO ???????????????
                $detail['return_status'] = 0;
                $detail['title'] = $value['good_name'];
                $detail['price'] = $value['price'];
                $detail['num'] = $value['amount'];
                $detail['goods_code'] = $value['good_no'];
                $detail['sku_id'] = $value['good_sn'];
                $detail['goods_barcode'] = $value['good_sn'];
                $detail['total_fee'] = $value['price'] * $value['amount'];
                $detail['payment'] = 0; //TODO
                $detail['discount_fee'] = 0; //TODO
                $detail['adjust_fee'] = 0; //TODO
                $detail['avg_money'] = 0; //TODO
                $detail['end_time'] = $data['base']['add_time'];
                //$detail['consign_time'] = '';
                //$detail['express_code'] = '';
                //$detail['express_company_name'] = '';
                //$detail['express_no'] = '';
                //$detail['pic_path'] = $value[''];
                $detail['sku_properties'] = 'size:' . $value['size'];
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
        
    }

    public function goods_info_download($data) {
        
    }

    public function goods_info_download_multi($ids, $data = array()) {
        
    }

    public function goods_list_download(array $data) {
        
    }

    public function inv_upload(array $data) {
        
    }

    public function inv_upload_multi(array $data) {
        
    }
    
    /**
     * 下载唯品会物流公司列表
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-04
     */
    public function logistics_company_download() {
        $param = array();
        $param['vendor_id'] = $this->vendor_id;
        $result = $this->request_send('carriers/get_carriers_list', $param);
        $return = $this->json_decode($result);
        return $return;
    }

    /**
     * 唯品会发货
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-03
     * @param array $data
     * @link http://visopen.vipshop.com/doc/api.php?n=pop/ship 批量发货
     */
    public function logistics_upload(array $data) {
        $param['order_list'] = array(
            //承运商编码
            'carriers_code' => $data['express_code'],
            //承运商名称(请传‘承运商列表接口’的carrier_shortname字段)
            'carrier' => $data['carrier_shortname'],
            'package_type' => 1,
            'transport_no' => $data['express_no'],
            'order_sn' => $data['tid'],
        );
        $result = $this->request_send('pop/ship',$param);
        $return = $this->json_decode($result);
        
        if (isset($return['data']['fail_num']) && $return['data']['fail_num']>0) {
            $msg = $return['data']['fail_data'][0]['fail']['error_msg']. $return['data']['fail_data'][0]['fail']['error_type'];
            $code = $return['data']['fail_data'][0]['fail']['error_code'];
            
            $send_log['status'] = -1;
            $oms_log['is_back'] = 2;
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg, $code);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
        }
        $return['send_log'] = $send_log;
        $return['oms_log'] = $oms_log;
        return $return;
    }

    /**
     * 单个订单明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-30
     * @param string $id 订单主键
     * @link http://visopen.vipshop.com/doc/api.php?n=pop/get_pop_order_goods_list
     */
    public function order_info_download($id, $data = array()) {
        $params['p'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['l'] = isset($data['page_size']) ? $data['page_size'] : 100;

        $params['order_sn'] = $id;

        $result = $this->request_send('pop/get_pop_order_goods_list', $params);
        $return = $this->json_decode($result);
        
        if($return['status']==-1){
            throw new ExtException($return['data']['error_msg'], $return['data']['error_code']);
        }
        return array(
            'base' => $data,
            'list' => $return['data']['list'],
        );
    }

    public function refund_info_download($refund_id, $refund_info) {
        
    }

    public function refund_list_download(array $data) {
        
    }

    public function save_source_goods_and_sku($shop_code, $data) {
        
    }
    
    /**
     * 保存物流公司原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-04
     * @param string $shop_code 店铺代码
     * @param array $data 物流公司原始数据
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data) {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'vipshop');
    }
    
    /**
     * 保存唯品会订单原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-30
     * @param string $shop_code 店铺代码
     * @param array $data
     */
    public function save_source_order_and_detail($shop_code, $data) {
        return load_model('source/vipshop/ApiVipshopTradeModel')->save_trade_and_order($shop_code, $data);
    }

    public function save_source_refund($shop_code, $data) {
        
    }

}