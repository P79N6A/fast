<?php

require_once 'AbsAPIModel.php';
require_lib('Exceptions/ExtException');

/**
 * 京东API类
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-23
 */
class JingdongAPIModel extends AbsAPIModel {

    /**
     * 接口网关地址
     * @var string 
     */
    public $gate = 'http://gw.api.360buy.com/routerjson';
    private $app_key = '74D6CF3EFB9C03909428537169B267B5';
    private $secret = '52e9a6277910474386330e3bf66ed220';
    private $access_token = '42fdc5f2-f23b-441e-910f-3a80c5979d40';
    //热敏单商家编码
    private $customerCode = '';
    

    /**
     * 订单处理类型：
     * FBP：仓储和配送都用京东
     * SOP：仓储和配送都不用京东
     * LBP：仓储不用，配送用，京东代开发票，需要送货到分拣中心
     * SOPL：仓储不用，配送用，自己开发票，需要送货到分拣中心
     * @var string
     */
    private $type = 'sop';

    /**
     * 接口实例化
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $token 应用及授权信息数组
     */
    public function __construct($token) {
        $this->app_key = $token['app_key'];
        $this->secret = $token['secret'];
        $this->access_token = $token['access_token'];
        $this->type = strtoupper($token['type']);
        $this->customerCode = $token['customerCode'];

        $this->order_pk = 'order_id';
        $this->goods_pk = 'ware_id';
        $this->refund_pk = 'afsServiceId';
        $this->refund_ext = true;
    }

    #########################################################################

    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param string $api 接口地址
     * @param array $param 请求参数
     */
    public function request_send($api, $param = array()) {
        //增加系统级参数
        $data['method'] = $api;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['app_key'] = $this->app_key;
        $data['v'] = '2.0';
        $data['access_token'] = $this->access_token;
        //封装签名
        $data['360buy_param_json'] = empty($param) ? '{}' : json_encode($param);
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        //发送请求
        $url = $this->gate;
        $result = $this->exec($url, $data);
        return $result;
    }

    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param string $api 接口地址
     * @param array $params 请求参数
     */
    public function request_send_multi($api, $params = array()) {
        $datas = array();
        foreach ($params as $param) {
            //增加系统级参数
            $data['method'] = $api;
            $data['timestamp'] = date('Y-m-d H:i:s');
            $data['app_key'] = $this->app_key;
            $data['v'] = '2.0';
            $data['access_token'] = $this->access_token;
            //封装签名
            $data['360buy_param_json'] = json_encode($param);
            $sign = $this->sign($data);
            $data['sign'] = $sign;
            $datas[] = $data;
        }
        //发送请求
        $url = $this->gate;
        $result = $this->multiExec($url, $datas);
        return $result;
    }

    /**
     * 生成淘宝API请求签名
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $param 待签名参数
     * @return string 返回签名
     */
    public function sign($param = array()) {
        $sign = $this->secret;
        ksort($param);
        foreach ($param as $k => $v) {
            $sign .= "$k$v";
        }
        unset($k, $v);
        $sign .= $this->secret;
        return strtoupper(md5($sign));
    }

    ###### 商品部分 ###########################################################
    /**
     * 商品信息列表下载，仅返回商品主键，明细信息需要调用明细接口获取
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-25
     * @link http://help.jd.com/jos/question-568.html#A9 获取商品上架的商品信息
     * @link http://help.jd.com/jos/question-568.html#A10 获取商品下架的商品信息
     * 
     * @param array $data 订单查询条件数组, 基本查询条件同淘宝 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @throws ExtException 下载失败抛出异常
     * @return array 返回查询结果 包含 items=>商品列表，total_results=>总条数
     */

    public function goods_list_download(array $data) {
        $params = array();
        $params['fields'] = 'ware_id';
        $params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if (isset($data['start_modified'])) {
            $params['start_modified'] = $data['start_modified'];
            $params['end_modified'] = date('Y-m-d H:i:s');
        }

        $get_types = array('listing', 'delisting'); //在售、在库
        $data['items']['item'] = array();
        $data['total_results'] = 0;

        foreach ($get_types as $get_type) {
            $result = $this->request_send('360buy.ware.' . $get_type . '.get', $params);
            if (PHP_VERSION >= 5.4) {
                $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
            } else {
                $result = json_decode($result, true);
            }

            if (isset($result['error_response'])) {
                $msg = $result['error_response']['zh_desc'];
                throw new ExtException($msg, $result['error_response']['code']);
            }

            if ($result['ware_' . $get_type . '_get_response']['total'] > 0) {
                $data['items']['item'] = array_merge($data['items']['item'], $result['ware_' . $get_type . '_get_response']['ware_infos']);
                $data['total_results'] += $result['ware_' . $get_type . '_get_response']['total'];
            }
            
        }

        return $data;
    }

    public function goods_info_download($data) {
        return $data;
    }

    /**
     * 批量返回京东商品数据，一次最多返回20个
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * 
     * @param array $data 京东商品ware_id组成的数组, 可通过列表获取到
     * @return array 返回商品信息
     * @link http://help.jd.com/jos/question-568.html#A7 京东批量获取商品信息接口
     * @return array
     * @throws ExtException
     */
    public function goods_info_download_multi($ids, $data=array()) {
        $params = array();
        $params['fields'] = 'outer_id,ware_id,skus,spu_id,cid,vender_id,shop_id,ware_status,title,item_num,upc_code,transport_id,online_time,offline_time,attributes,desc,producter,wrap,cubage,pack_listing,service,cost_price,market_price,jd_price,stock_num,logo,creator,status,weight,created,modified,shop_categorys,is_pay_first,is_can_vat,is_imported,is_health_product,is_shelf_life,shelf_life_days,is_serial_no,is_appliances_card,is_special_wet,ware_big_small_model,ware_pack_type';
        $params['ware_ids'] = implode(',', $ids);

        $result = $this->request_send('360buy.wares.list.get', $params);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        }
        return $result['ware_list_response']['wares'];
    }

    ###### 订单部分 ###########################################################

    /**
     * 订单下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-24
     * 
     * @param array $data 订单下载条件数组
     * @return array 订单详情数组和订单总条数
     * @throws ExtException 下载失败抛出异常
     * @link http://help.jd.com/jos/question-569.html#A2 京东订单：订单检索、FBP订单检索
     */
    public function order_list_download($data) {
        $params = array();
        if (isset($data['fields'])) {
            $params['optional_fields'] = $data['fields'];
        } else {
            $params['optional_fields'] = 'order_id,vender_id,pay_type,order_total_price,order_payment,order_seller_price,freight_price,seller_discount,order_state,order_state_remark,delivery_type,invoice_info,order_remark,order_start_time,order_end_time,consignee_info,item_info_list,coupon_detail_list,return_order,vender_remark,pin,balance_used,modified,payment_confirm_time,logistics_id,waybill,vat_invoice_info,parent_order_id,order_type,order_source';
        }
        $params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['sortType'] = '1';
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;

        if ($this->type == 'LBP' || $this->type == 'SOPL') {
            $params['order_state'] = 'WAIT_SELLER_STOCK_OUT,SEND_TO_DISTRIBUTION_CENER,DISTRIBUTION_CENTER_RECEIVED,WAIT_GOODS_RECEIVE_CONFIRM,RECEIPTS_CONFIRM,FINISHED_L,TRADE_CANCELED,LOCKED';
        } else {
            $params['order_state'] = 'WAIT_SELLER_STOCK_OUT,WAIT_GOODS_RECEIVE_CONFIRM,FINISHED_L,TRADE_CANCELED,LOCKED';
        }

        if (isset($data['start_modified'])) {
            $params['start_date'] = date('Y-m-d H:i:s', strtotime('-5 minute', strtotime($data['start_modified'])));
            //按订单编辑时间
            $params['dateType'] = 0;
            $params['end_date'] = date('Y-m-d H:i:s');
        }
        if ($this->type == 'FBP') {
            $result = $this->request_send('360buy.order.fbp.search', $params);
        } else {
            $result = $this->request_send('360buy.order.search', $params);
        }
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            $return = array(
                //转成与淘宝类似的返回格式
                'trades' => array('trade' => $result['order_search_response']['order_search']['order_info_list']),
                'total_results' => $result['order_search_response']['order_search']['order_total'],
            );
            return $return;
        }
    }

    /**
     * 订单明细信息下载,根据订单order_id, 获取单个订单信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-24
     * @param $id 订单的order_id
     * @param string $data
     * @return array
     * @throws ExtException 单据明细下载失败抛出通用异常
     */
    public function order_info_download($id, $data = array()) {
        $params = array();
        $params['order_id'] = $id;
        $params['optional_fields'] = 'order_id,vender_id,pay_type,order_total_price,order_payment,order_seller_price,freight_price,seller_discount,order_state,order_state_remark,delivery_type,invoice_info,order_remark,order_start_time,order_end_time,consignee_info,item_info_list,coupon_detail_list,return_order,vender_remark,pin,balance_used,modified,payment_confirm_time,logistics_id,waybill,vat_invoice_info,parent_order_id,order_type';

        if ($this->type == 'FBP') {
            $result = $this->request_send('360buy.order.fbp.get', $params);
        } else {
            $result = $this->request_send('360buy.order.get', $params);
        }

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        //dump($result,1);
        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            //转成与淘宝类似的返回格式
            $result['order_get_response']['order']['orderInfo']['printData'] = $this->order_print_download($id);
            $return = $result['order_get_response']['order']['orderInfo'];
            return $return;
        }
    }

    ##########################################################################

    /**
     * 京东退单下载（服务单列表）
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param array $data 查询条件
     * @link http://help.jd.com/jos/question-570.html#A7 获取服务单列表信息
     */
    public function refund_list_download(array $data) {
        $param = array();
        $param['pageNumber'] = $data['page_no'];
        $param['pageSize'] = $data['page_size'];
        if (!empty($data['start_modified'])) {
            $param['afsApplyTimeBegin'] = $data['start_modified'];
            $param['afsApplyTimeEnd'] = date('Y-m-d H:i:s');
        }
        $result = $this->request_send('jingdong.afsservice.alltask.get', $param);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            //转成与淘宝类似的返回格式
            $return['refunds']['refund'] = $result['jingdong_afsservice_alltask_get_responce']['publicResultObject']['allAfsService']['result'];
            $return['total_results'] = $result['jingdong_afsservice_alltask_get_responce']['publicResultObject']['allAfsService']['totalCount'];
            return $return;
        }
    }

    /**
     * 京东退单单条明细下载（服务单）
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param string $refund_id 服务单单号
     * @return array 返回服务单明细信息
     * @link http://help.jd.com/jos/question-570.html#A8  获取服务单信息
     */
    public function refund_info_download($refund_id, $refund_info) {
        $return = array();
        $return['info'] = $this->refund_info_download_info($refund_id);
        $return['detail'] = $this->refund_info_download_detail($refund_id);
        $return['money'] = $this->refund_info_download_money($refund_id);
        return $return;
    }

    /**
     * 京东退单单条明细下载（服务单明细）
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param string $refund_id 服务单单号
     * @return array 返回服务单明细信息
     * @link http://help.jd.com/jos/question-570.html#A8  获取服务单信息
     */
    private function refund_info_download_info($refund_id) {
        $param = array();
        $param['afsServiceId'] = $refund_id;
        $result = $this->request_send('jingdong.afsservice.serviceinfo.get', $param);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            return $result['jingdong_afsservice_serviceinfo_get_responce']['publicResultObject']['afsServiceOut'];
        }
    }

    /**
     * 京东退单单条商品明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param string $refund_id 服务单单号
     * @return array 返回商品明细信息
     * @link http://help.jd.com/jos/question-570.html#A10  获取服务单详情
     */
    private function refund_info_download_detail($refund_id) {
        $param = array();
        $param['afsServiceId'] = $refund_id;
        $result = $this->request_send('jingdong.afsservice.servicedetail.list', $param);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            return isset($result['jingdong_afsservice_servicedetail_list_responce']['publicResultList']['modelList']) ? $result['jingdong_afsservice_servicedetail_list_responce']['publicResultList']['modelList'] : array();
        }
    }

    /**
     * 京东退单退款明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param string $refund_id 服务单单号
     * @return array 返回商品明细信息
     * @link http://help.jd.com/jos/question-570.html#A16  获取退款信息
     */
    private function refund_info_download_money($refund_id) {
        $param = array();
        $param['afsServiceId'] = $refund_id;
        $result = $this->request_send('jingdong.afsservice.refundinfo.get', $param);

        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }

        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            return isset($result['jingdong_afsservice_refundinfo_get_responce']['publicResultObject']['afsRefundInfoOut']) ? $result['jingdong_afsservice_refundinfo_get_responce']['publicResultObject']['afsRefundInfoOut'] : array();
        }
    }

    /**
     * 下载售前退款列表
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param array $data
     * @return array $results 返回数组中包含主单据列表和明细
     */
    public function refund_download_ext($data){
        $page_index = 1;
        $page_size = 30;
        $pages = 0;
        $results = $result = array();
        do{
            $param = array(
                'page_index' => $page_index,
                'page_size' => $page_size,
            );
            if(isset($data['start_modified'])&&!empty($data['start_modified'])){
                $param['check_time_start'] = $data['start_modified'];
                $param['check_time_end'] = date('Y-m-d H:i:s');
            }
            
            $result = $this->refund_list_download_ext($param);
            
            $pages = ceil($result['count'] / $page_size);
            $results = array_merge($results, $result['results']);
            $page_index++;
        }while ($page_index <= $pages);
        
        return $results;
    }
    
    /**
     * 下载售前退款列表, 分页, 京东售前退款的列表返回值和明细接口返回值是一样的， 所以只取列表即可
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param array $param 查询条件 
     * @throws ExtException
     * @link http://help.jd.com/jos/question-570.html#A19 商家查询退款审核单列表 
     */
    private function refund_list_download_ext($param){
        $result = $this->request_send('jingdong.pop.afs.refundapply.querylist', $param);
        if (PHP_VERSION >= 5.4) {
            $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = json_decode($result, true);
        }
        
        if (isset($result['error_response'])) {
            $msg = $result['error_response']['zh_desc'];
            throw new ExtException($msg, $result['error_response']['code']);
        } else {
            return isset($result['jingdong_pop_afs_refundapply_querylist_responce']['refundApplyResponse'])?$result['jingdong_pop_afs_refundapply_querylist_responce']['refundApplyResponse']:array();
        }
    }
    
    /**
     * 转化售前退款到标准数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param string $shop_code 店铺代码 
     * @param array $data 待转换数据
     */
    public function _trans_refund_ext($shop_code, $data){
        $return = array();
        $return['refund_id'] = $data['id'];
        $return['refund_type'] = 1;
        $return['tid'] = $data['order_id'];
        $return['oid'] = '';
        $return['source'] = 'jingdong';
        $return['shop_code'] = $shop_code;
        $return['status'] = 1;
        $return['is_change'] = 0;
        $return['seller_nick'] = $data['check_username'];
        $return['buyer_nick'] = $data['buyer_id']; //TODO or $data['buyer_name']
        $return['has_good_return'] = 'FALSE';
        $return['refund_fee'] = $data['apply_refund_sum'] * 0.01; //单位分 转换为元
        $return['payment'] = '';
        $return['refund_reason'] = '';
        return $return;
    }
    
    /**
     * 转化售前退款明细到标准数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param array $data 待转换数据
     */
    public function _trans_refund_detail_ext($data){
        //售前退款没有明细，整单退， 返回空
        return array();
    }
    
    /**
     * 保存售前退款的原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @param string $shop_code
     * @param array $data
     */
    public function save_source_refund_ext($shop_code, $data){
        return load_model('source/ApiJingdongRefundModel')->save_jingdong_refund_ext($shop_code, $data);
    }

    #######################################################################

    public function inv_upload(array $data) {
        
    }

    /**
     * 批量库存同步 360buy.sku.stock.update
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param array $data 待更新的数组，必须包括第三方平台的SKU外部编码和库存数量，数组结构同inv_upload单个上传
     * @return array 返回更新的结果
     * @link http://help.jd.com/jos/question-573.html#A3 修改SKU库存信息
     */
    public function inv_upload_multi(array $data) {
        $params = array();
        $result = array();
        foreach ($data as $item) {
            $param = array();
            $param['outer_id'] = $item['goods_barcode'];
            $param['quantity'] = $item['inv_num'];
           // $params[] = $param;
            $v = $this->request_send('360buy.sku.stock.update', $param);
            if (PHP_VERSION >= 5.4) {
                $return = json_decode($v, true, 512, JSON_BIGINT_AS_STRING);
            } else {
                $return = json_decode($v, true);
            }
            $returns[] = $return;
        }

        return $returns;
    }

    /**
     * 京东单个订单发货回写
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param array $data 待回写的订单信息
     * @return array 返回第三方平台调用结果
     * @link http://help.jd.com/jos/question-569.html#A10 订单SOP出库
     * @link http://help.jd.com/jos/question-569.html#A11 订单LBP出库
     * @link http://help.jd.com/jos/question-569.html#A12 订单SOPL出库
     */
    public function logistics_upload(array $data) {
        $param = array();
        $param['order_id'] = $data['tid'];
        $param['logistics_id'] = $data['logistics_id'];
        $param['waybill'] = $data['express_no'];

        switch ($this->type) {
            case 'SOP':
                $result = $this->request_send('360buy.order.sop.outstorage', $param);
                break;
            case 'LBP':
                $result = $this->request_send('360buy.order.lbp.outstorage', $param);
                break;
            case 'SOPL':
                $result = $this->request_send('360buy.order.sopl.outstorage', $param);
                break;
            default :
                throw new ExtException('接口订单出库处理模式只支持SOP/LBP/SOPL,请检查配置信息', '-1');
        }

        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }

        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
//            $send_log['status'] = -1;
//            $oms_log['is_back'] = 2;
//            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg, $return['error_response']['code']);
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            //如果回写成功并且该订单为货到付款订单，则需要额外回写热敏物流单信息，使用京东物流接单接口
            if (load_model('ApiOrderSendModel')->has_jingdong_print_data($data['tid'])) {
                //需要从业务表中查询出相关物流信息
                $print_data = load_model('ApiOrderSendModel')->get_jingdong_print_data($data['tid']);
                $print_data_upload_result = $this->order_print_info_upload($print_data);
                if (!$print_data_upload_result['status']) {
                    //TODO异常处理目前只记录到日志
                    $send_log['error_remark'] = $send_log['error_remark'] . ' | 京东物流接单接口回传错误：' . $print_data_upload_result['message'];
                    $oms_log['is_back_reason'] = $oms_log['is_back_reason'] . ' | 京东物流接单接口回传错误：' . $print_data_upload_result['message'];
                }
            }
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
        }

        $return['send_log'] = $send_log;
        $return['oms_log'] = $oms_log;

        return $return;
    }

    public function logistics_upload_multi(array $data) {
        return $data;
    }

    /**
     * 京东物流接单接口信息回传, 条件为 货到付款的订单 
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-07
     * 
     * @param array $data 物流信息，其中运单号为通过jingdong.etms.waybillcode.get获取到的运单号
     * @return array status=>true/false, message=>'结果信息'
     * @link http://help.jd.com/jos/question-814.html#A21 京东物流接单接口
     */
    private function order_print_info_upload($data) {
        $param = $data;
        $param['customerCode'] = $this->customerCode;
        $result = $this->request_send('jingdong.etms.waybill.send', $param);

        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }

        //返回结果，状态值true/false和结果信息
        if (isset($return['jingdong_etms_waybill_send_responce']['resultInfo']['code']) && $return['jingdong_etms_waybill_send_responce']['resultInfo']['code'] == 100) {
            return array('status' => true, 'message' => $return['jingdong_etms_waybill_send_responce']['resultInfo']['message']);
        } else {
            return array('status' => false, 'message' => $return['jingdong_etms_waybill_send_responce']['resultInfo']['message']);
        }
    }

    ##########################################################################
    /**
     * 转换京东商品格式为标准商品格式
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param array $data 京东接口返回的商品原始格式
     * @link http://help.jd.com/jos/question-568.html#A6 京东ware数据结构
     */

    public function _trans_goods(array $data) {
        $return = array();

        $return['goods_name'] = $data['title'];
        $return['goods_code'] = $data['item_num'];
        $return['goods_from_id'] = $data['ware_id'];
        $return['num'] = $data['stock_num'];
        //TODO 京东的seller_nick暂无
        $return['seller_nick'] = $data['vender_id'];
        $return['source'] = 'jingdong';
        $return['status'] = $data['ware_status'] === 'ON_SALE' ? 1 : 0;
        //TODO 京东暂无
        $return['stock_type'] = 2;
        $return['onsale_time'] = $data['online_time'];
        $return['offsale_time'] = isset($data['offline_time'])?$data['offline_time']:'';
        $return['sale_start_time'] = $data['created'];
        $return['has_sku'] = empty($data['skus']) ? 0 : 1;
        $return['price'] = $data['jd_price'];
        $return['goods_img'] = $data['logo'];
        $return['goods_desc'] = isset($data['desc']) ? $data['desc'] : '';

        return $return;
    }

    /**
     * 转换京东SKU为标准SKU
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param array $data 京东接口返回的商品信息中的SKU部分
     * @link http://help.jd.com/jos/question-568.html#A6 京东SKU数据结构
     */
    public function _trans_sku(array $data) {
        $return = array();
        if (isset($data['skus'])) {
            foreach ($data['skus'] as $value) {
                $sku = array();
                $sku['goods_from_id'] = $data['ware_id'];
                $sku['source'] = 'jingdong';
                $sku['sku_id'] = $value['sku_id'];
                $sku['goods_barcode'] = isset($value['outer_id']) ? $value['outer_id'] : '';
                //sku状态: 有效-Valid 无效-Invalid 删除-Delete
                $sku['status'] = (isset($value['status']) && $value['status'] == 'Valid') ? 1 : 0;
                $sku['num'] = $value['stock_num'];
                $sku['price'] = $value['jd_price'];
                //TODO京东暂无库存扣减规则及预占库存
                $sku['stock_type'] = 2;
                $sku['with_hold_quantity'] = 0;
                $sku['sku_properties'] = $value['attributes'];
                $sku['sku_properties_name'] = $value['color_value'] . ',' . $value['size_value'];
                $return[] = $sku;
            }
        } else {
            //如果不存在SKU，则创建一条通色通码数据
            $sku = array();
            $sku['goods_from_id'] = $data['ware_id'];
            $sku['source'] = 'jingdong';
            $sku['sku_id'] = $data['ware_id'];
            $sku['goods_barcode'] = $data['item_num'];
            $sku['status'] = 1;
            $sku['num'] = $data['stock_num'];
            $sku['price'] = $data['jd_price'];
            $sku['stock_type'] = 2;
            $sku['with_hold_quantity'] = 0;
            $sku['sku_properties_name'] = $sku['sku_properties'] = '';
            $return[] = $sku;
        }
        return $return;
    }

    /**
     * 转换京东订单信息为标准订单信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param string $shop_code 店铺代码
     * @param array $data 京东订单信息
     * @return array
     */
    public function _trans_order($shop_code, array $data) {
        $datetime = date('Y-m-d H:i:s');
        //$return = $data;
        $return['tid'] = $data['order_id'];
        $return['source'] = 'jingdong';
        $return['shop_code'] = $shop_code;
        $return['status'] = $data['order_state'] == 'WAIT_SELLER_STOCK_OUT' ? 1 : 0;
        $return['trade_from'] = isset($data['order_source']) ? $data['order_source'] : '';
        $return['pay_type'] = $data['pay_type'] === '1-货到付款' ? 1 : 0;
        $return['pay_time'] = $data['payment_confirm_time'];
        $return['seller_nick'] = $data['vender_id'];
        $return['buyer_nick'] = $data['pin'];
        $return['receiver_name'] = $data['consignee_info']['fullname'];
        $return['receiver_country'] = '中国';
        $return['receiver_province'] = $data['consignee_info']['province'];
        $return['receiver_city'] = $data['consignee_info']['city'];
        $return['receiver_district'] = $data['consignee_info']['county'];
        $return['receiver_street'] = '';
        $return['receiver_address'] = $data['consignee_info']['full_address'];
        //receiver_addr是需要去掉省市区的地址
        $return['receiver_addr'] = str_replace(array($return['receiver_province'], $return['receiver_city'], $return['receiver_district']), '', $return['receiver_address']);
        $return['receiver_zip_code'] = '';
        $return['receiver_mobile'] = $data['consignee_info']['mobile'];
        $return['receiver_phone'] = $data['consignee_info']['telephone'];
        $return['receiver_email'] = '';
        $return['express_code'] = $data['logistics_id'];
        $return['express_no'] = $data['waybill'];
        $return['hope_send_time'] = $data['delivery_type'];
        //订单总数量
        $num = 0;
        foreach ($data['item_info_list'] as $i) {
            $num = $num + $i['item_total'];
        }
        $return['num'] = $num;
        $return['sku_num'] = count($data['item_info_list']); //平台sku种类数量
        $return['goods_weight'] = 0;
        $return['buyer_remark'] = isset($data['order_remark']) ? $data['order_remark'] : '';
        $return['seller_remark'] = isset($data['vender_remark']) ? $data['vender_remark'] : '';
        $return['seller_flag'] = '';
        $return['order_money'] = $data['order_seller_price'] + $data['freight_price'];
        $return['express_money'] = $data['freight_price'];
        $return['delivery_money'] = 0;
        //转换优惠金额
        $gift_coupon_money = $gift_money = $integral_change_money = 0;
        foreach ($data['coupon_detail_list'] as $c) {
            if ($c['coupon_type'] == 41) {
                $gift_coupon_money = $gift_coupon_money + $c['coupon_price'];
            } elseif ($c['coupon_type'] == 52) {
                $gift_money = $gift_money + $c['coupon_price'];
            } elseif ($c['coupon_type'] == 39) {
                $integral_change_money = $integral_change_money + $c['coupon_price'];
            }
        }
        $return['gift_coupon_money'] = $gift_coupon_money;
        $return['gift_money'] = $gift_money;
        $return['integral_change_money'] = $integral_change_money;
        if ($return['pay_type'] == 1) {
            $return['buyer_money'] = $data['order_seller_price'] + $data['freight_price'] - $data['order_payment'] + $data['balance_used'];
        } else {
            $return['buyer_money'] = $data['order_payment'] + $data['balance_used'];
        }
        $return['alipay_no'] = '';
        $return['coupon_change_money'] = 0;
        $return['balance_change_money'] = $data['balance_used'];
        $return['is_lgtype'] = '';
        $return['seller_rate'] = '';
        $return['buyer_rate'] = '';

        $return['invoice_type'] = $return['invoice_title'] = $return['invoice_content'] = '';
        $return['invoice_money'] = $data['order_seller_price'];
        if ('不需要开具发票' != $data['invoice_info']) {
            $invoice_info = explode(';', $data['invoice_info']);
            foreach ($invoice_info as $i) {
                if (false !== strpos($i, '发票类型')) {
                    $return['invoice_type'] = str_replace('发票类型:', '', $i);
                }
                if (false !== strpos($i, '发票抬头')) {
                    $return['invoice_title'] = str_replace('发票抬头:', '', $i);
                }
                if (false !== strpos($i, '发票内容')) {
                    $return['invoice_content'] = str_replace('发票内容:', '', $i);
                }
            }
        }
        $return['invoice_pay_type'] = '';
        $return['order_last_update_time'] = $data['modified'];
        $return['order_first_insert_time'] = $data['order_start_time'];
        $return['first_insert_time'] = $datetime;

        $return['last_update_time'] = $data['modified'];

        //额外信息
        $return['coupon_detail_list'] = $data['coupon_detail_list'];
        return $return;
    }

    /**
     * 转换京东订单明细信息为标准订单明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param array $data 京东订单信息
     * @return array
     */
    public function _trans_order_detail(array $data) {
        $return = array();
        if (isset($data['item_info_list'])) {
            $avg_total = 0;
            $total = 0;
            $base = $data['order_seller_price'];
            foreach ($data['item_info_list'] as $value) {
                $total = $total + ($value['jd_price'] * $value['item_total']);
            }
            foreach ($data['coupon_detail_list'] as $c) {
                $total = $total - $c['coupon_price'];
            }

            foreach ($data['item_info_list'] as $key => $value) {
                $detail = array();
                $detail['tid'] = $data['order_id'];
                $detail['oid'] = $data['order_id'] . '_' . $value['sku_id'];
                $detail['source'] = 'jingdong';
                $detail['return_status'] = $data['return_order'];

                $discount_fee = 0;
                foreach ($data['coupon_detail_list'] as $c) {
                    if ($c['sku_id'] == $value['sku_id'] && in_array($c['coupon_type'], array(20, 28, 29, 30, 35, 100))) {
                        $discount_fee = $discount_fee + $c['coupon_price'];
                    }
                }

                $detail['title'] = $value['sku_name'];
                $detail['price'] = $value['jd_price'];
                $detail['num'] = $value['item_total'];
                $detail['goods_code'] = $value['product_no'];
                $detail['sku_id'] = $value['sku_id'];
                $detail['goods_barcode'] = $value['outer_sku_id'];

                $detail['total_fee'] = ($value['jd_price'] * $value['item_total']) - $discount_fee;
                $detail['payment'] = ($value['jd_price'] * $value['item_total']) - $discount_fee;
                $detail['discount_fee'] = $discount_fee;
                $detail['adjust_fee'] = 0;

                /**
                 * 均摊金额计算公式
                  avg_money = trade.order_seller_price * ((items.(jd_price*item_total)-(coupon.coupon_price where coupon_type=20,28,29,30,35,100 and sku_id=items.sku_id ))/(items.SUM(jd_price*item_total)-coupon.SUM(coupon_price)))
                 * */
                if ($key < count($data['item_info_list']) - 1) {
                    $detail['avg_money'] = round($base * ($detail['total_fee'] / $total));
                } else {
                    $detail['avg_money'] = $base - $avg_total;
                }

                $detail['end_time'] = $data['order_end_time'];
                //$detail['express_code'] = isset($data['logistics_id']) ? $data['logistics_id'] : '';
                //$detail['express_no'] = isset($data['waybill']) ? $data['waybill'] : '';
                //$detail['express_company_name'] = isset($data['logistics_id']) ? $data['logistics_id'] : '';
                //$detail['pic_path'] = '';
                $detail['sku_properties'] = $value['sku_name'];

                $avg_total += $detail['avg_money'];
                $return[] = $detail;
            }

            //校验均摊金额, 其实没必要
            if ($base != $avg_total) {
                throw new ExtException('订单均摊金额计算异常', -1);
            }
        }

        return $return;
    }

    /**
     * 转换京东退单原始数据到标准数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-09
     * @param string $shop_code 店铺代码
     * @param array $data 京东退单原始数据
     * @return array
     */
    public function _trans_refund($shop_code, array $data) {
        $refund = array();

        $refund['refund_id'] = $data['info']['afsServiceId'];
        switch ($data['info']['customerExpect']) {
            case 10:
                $refund['refund_type'] = 0;
                break;
            case 20:
                $refund['refund_type'] = 3;
                break;
            case 30:
                $refund['refund_type'] = 2;
                break;
            default :
                $refund['refund_type'] = -1;
                break;
        }
        $refund['tid'] = isset($data['money']['afsFinanceOut']['orderId']) ? $data['money']['afsFinanceOut']['orderId'] : '';
        $refund['oid'] = '';
        $refund['source'] = 'jingdong';
        $refund['shop_code'] = $shop_code;
        $refund['status'] = 1;
        $refund['is_change'] = 0;
        $refund['seller_nick'] = $data['info']['approveName'];
        $refund['buyer_nick'] = $data['info']['createName'];
        $refund['has_good_return'] = 'TRUE';
        $refund['refund_fee'] = isset($data['money']['afsFinanceOut']['price']) ? $data['money']['afsFinanceOut']['price'] : 0;
        $refund['payment'] = 0;
        $refund['refund_reason'] = isset($data['money']['afsFinanceOut']['reason']) ? $data['money']['afsFinanceOut']['reason'] : '';
        $refund['refund_desc'] = $data['info']['questionDesc'];
        $refund['refund_express_code'] = '';
        $refund['refund_express_no'] = '';
        $refund['attribute'] = '';
        $refund['change_remark'] = '';

        return $refund;
    }

    /**
     * 转换京东退单明细原始数据到标准数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-09
     * @param array $data 京东退单原始数据
     * @return array
     */
    public function _trans_refund_detail(array $data) {
        $return = array();
        if (isset($data['money']) && !empty($data['money'])) {
            //dump($data['money'],1);
            //服务单存在退款信息
            foreach ($data['money']['afsFinanceDetailOuts'] as $v) {
                $sql = 'select * FROM api_jingdong_order where sku_id = :ware_id and order_id = :order_id';
                $api_order_detail = CTX()->db->get_row($sql, array(
                    ':ware_id'  => $v['wareId'], 
                    ':order_id' => isset($v['orderId'])?$v['orderId']:$data['money']['afsFinanceOut']['orderId']
                ));
                //dump(array(':ware_id' => $v['wareId'], ':order_id' => $data['money']['afsFinanceOut']['orderId']),1);
                $detail['refund_id'] = $data['info']['afsServiceId'];
                $detail['tid'] = isset($v['orderId'])?$v['orderId']:$data['money']['afsFinanceOut']['orderId'];
                $detail['oid'] = '';
                $detail['goods_code'] = $api_order_detail['outer_sku_id'];
               // $detail['goods_from_id'] = $v['wareId'];
                $detail['title'] = $api_order_detail['sku_name'];
                $detail['price'] = $api_order_detail['jd_price'];
                $detail['num'] = $api_order_detail['item_total'];
                $detail['refund_price'] = '0'; //TODO ????????????????????????
                $return[] = $detail;
            }
        }else{
            //TODO 服务单不存在退款信息 是否需要写入明细?
        }

        return $return;
    }

    ##########################################################################
    /**
     * 保存京东商品以及商品明细原始信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param string $shop_code 店铺代码
     * @param array $data 商品数据，含明细
     * @return array
     */

    public function save_source_goods_and_sku($shop_code, $data) {
        return $ret = load_model('source/ApiJingdongGoodsModel')->save_goods_and_sku($shop_code, $data);
    }

    /**
     * 保存京东订单及订单明细原始信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-26
     * @param string $shop_code 店铺代码
     * @param array $data 订单数据，含明细
     */
    public function save_source_order_and_detail($shop_code, $data) {
        return $ret = load_model('source/ApiJingdongTradeModel')->save_trade_and_order($shop_code, $data);
    }

    /**
     * 保存京东退单及明细的原始信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param string $shop_code 店铺代码
     * @param array $data 退单各详情
     */
    public function save_source_refund($shop_code, $data) {
        return $ret = load_model('source/ApiJingdongRefundModel')->save_jingdong_refund($shop_code, $data);
    }

    ##### 其他公有方法 ########################################################
    /**
     * 使用360buy.delivery.logistics.get获取京东物流公司信息, 并记录到api_jingdong_logistics_companies表中
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @link http://help.jd.com/jos/question-571.html 京东配送服务信息获取
     */

    public function logistics_company_download() {
        $param = array();
        $result = $this->request_send('360buy.delivery.logistics.get', $param);
        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }

        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['delivery_logistics_get_response']['logistics_companies'];
    }

    /**
     * 保存京东物流公司信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param string $shop_code 店铺代码
     * @param array $data 京东接口返回的信息
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data) {
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'jingdong');
    }

    /**
     * 订单打印数据获取
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-31
     * @param string $data 京东订单order_id
     * @return array
     */
    public function order_print_download($data) {
        $param['order_id'] = $data;
        switch ($this->type) {
            case 'SOP':
                $result = $this->request_send('360buy.order.sop.print.data.get', $param);
                break;
            case 'LBP':
                $result = $this->request_send('360buy.order.lbp.print.data.get', $param);
                break;
            case 'SOPL':
                $result = $this->request_send('360buy.order.sopl.print.data.get', $param);
                break;
            default :
                $result = $this->request_send('360buy.order.print.data.get', $param);
        }
        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }
        //TODO出错暂未处理
        switch ($this->type) {
            case 'SOP':
                return isset($return['order_sop_printdata_response']['order_printdata']) ? $return['order_sop_printdata_response']['order_printdata'] : array();
            case 'LBP':
                return isset($return['order_lbp_printdata_response']['order_printdata']) ? $return['order_lbp_printdata_response']['order_printdata'] : array();
            case 'SOPL':
                return isset($return['order_sopl_printdata_response']['order_printdata']) ? $return['order_sopl_printdata_response']['order_printdata'] : array();
            default :
                return isset($return['order_printdata_response']['order_printdata']) ? $return['order_printdata_response']['order_printdata'] : array();
        }
    }

    /**
     * 下载京东的区域地址
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param int $data data['pid'] 父ID，为０则下载省级地址
     * @return array
     */
    public function api_area_download($data) {
        $result = $this->api_area_download_detail($data);
        return $result;
    }

    /**
     * 保存京东的区域地址
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param array $data 通过接口获取到的京东地址库
     * @return null
     */
    public function save_api_area($data) {
        $result = load_model('source/ApiAreaModel')->save_jingdong_area($data);
        return $result;
    }

    /**
     * 下载京东的区域地址
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-09
     * @param int $data data['pid'] 父ID，为０则下载省级地址
     * @return array $return
     * @link http://help.jd.com/jos/question-827.html 京东地址库接口
     */
    private function api_area_download_detail($data, &$return = array()) {
        $param = array();
        if (!empty($data['pid'])) {
            $param['parent_id'] = $data['pid'];
        }

        switch ($data['type']) {
            case '0':
                $result = $this->request_send('jingdong.area.province.get', $param);
                if (PHP_VERSION >= 5.4) {
                    $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
                } else {
                    $result = json_decode($result, true);
                }
                foreach ($result['jingdong_area_province_get_responce']['province_areas'] as &$area) {
                    $area['pid'] = 0;
                    $area['type'] = 0;
                }
                $return = array_merge($return, $result['jingdong_area_province_get_responce']['province_areas']);
                foreach ($result['jingdong_area_province_get_responce']['province_areas'] as &$area) {
                    $this->api_area_download_detail(array('pid' => $area['id'], 'type' => 1), $return);
                }
                break;
            case '1':
                $result = $this->request_send('jingdong.area.city.get', $param);
                if (PHP_VERSION >= 5.4) {
                    $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
                } else {
                    $result = json_decode($result, true);
                }
                foreach ($result['jingdong_area_city_get_responce']['city_areas'] as &$area) {
                    $area['pid'] = $param['parent_id'];
                    $area['type'] = 1;
                }
                $return = array_merge($return, $result['jingdong_area_city_get_responce']['city_areas']);
                foreach ($result['jingdong_area_city_get_responce']['city_areas'] as &$area) {
                    $this->api_area_download_detail(array('pid' => $area['id'], 'type' => 2), $return);
                }
                break;
            case '2':
                $result = $this->request_send('jingdong.area.county.get', $param);
                if (PHP_VERSION >= 5.4) {
                    $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
                } else {
                    $result = json_decode($result, true);
                }
                foreach ($result['jingdong_area_county_get_responce']['county_areas'] as &$area) {
                    $area['pid'] = $param['parent_id'];
                    $area['type'] = 2;
                }
                $return = array_merge($return, $result['jingdong_area_county_get_responce']['county_areas']);
                foreach ($result['jingdong_area_county_get_responce']['county_areas'] as &$area) {
                    $this->api_area_download_detail(array('pid' => $area['id'], 'type' => 3), $return);
                }
                break;
            case '3':
                $result = $this->request_send('jingdong.area.town.get', $param);
                if (PHP_VERSION >= 5.4) {
                    $result = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
                } else {
                    $result = json_decode($result, true);
                }
                foreach ($result['jingdong_area_town_get_responce']['town_areas'] as &$area) {
                    $area['pid'] = $param['parent_id'];
                    $area['type'] = 3;
                }
                $return = array_merge($return, $result['jingdong_area_town_get_responce']['town_areas']);
                break;
            default:
                break;
        }

        return $return;
    }

    /**
     * 订单打印单号获取
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-01
     * @param array $data 包含预分配的数量 
     * @return array
     * @link http://help.jd.com/jos/question-814.html#A20 获取京东物流运单号接口
     */
    public function order_print_num_download($data) {
        //热敏快递单商家编码，京东解释：此商家编码需由商家向京东快递运营人员（与商家签订京东快递合同的人）索取。
        if (!isset($data['customerCode']) || empty($data['customerCode'])) {
            $data['customerCode'] = $this->customerCode;
        }
        $result = $this->request_send('jingdong.etms.waybillcode.get', $data);
        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }

        if ($return['jingdong_etms_waybillcode_get_responce']['resultInfo']['message'] != '成功') {
            //异常抛出
            $msg = $return['jingdong_etms_waybillcode_get_responce']['resultInfo']['message'];
            throw new ExtException($msg, $return['jingdong_etms_waybillcode_get_responce']['resultInfo']['code']);
        } else {
            return $return['jingdong_etms_waybillcode_get_responce']['resultInfo']['deliveryIdList'];
        }
    }

}