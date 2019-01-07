<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 淘宝API类
 *
 * @author wade
 * @date 2015-03-09
 */
class Zhe800APIModel extends AbsAPIModel {

    /**
     * 接口网关地址
     * @var string 
     */
    public $gate = 'http://seller.zhe800.com/api/erp/v1/';
    //public $gate = 'http://223.4.54.191/taobao_trans_req.php?';
    public $https_gate = '';

    /**
     * 淘宝应用的APP_KEY
     * @var string
     */
    private $authorize_token;

    /**
     * 淘宝应用的secret
     * @var string
     */
    private $api_token;

    /**
     * 淘宝卖家nick
     * @var string
     */
    private $seller_nick;
    
    /**
     * 下载模式
     * @var type 
     */
    private $mode = 'api';
    
    /**
     * 沙盒测试开关，开启后，以沙盒测试API地址替换正式接口地址，以pts_info替换sys_info数据库
     * @var boolean
     */
    private $sandbox = false;
    
    /**
     * RDS模式下，数据推送的库名
     * @var string
     */
    private $rds_db_name = 'sys_info';

    /**
     * 接口实例化
     * @author wade
     * @date 2015-03-09
     * @param array $token 应用及授权信息数组
     */
    public function __construct($token) {
        $this->authorize_token = $token['authorize_token'];
        $this->api_token = $token['api_token'];

        $this->order_pk = 'id';
        $this->goods_pk = 'id';
        $this->refund_pk = 'refund_id';
        $this->order_page_size = 30;
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    }

    /**
     * API请求发送
     * @author wade
     * @date 2015-03-09
     * @param string $api 接口地址
     * @param array $param 请求参数
     * @param boolean $https 是否https网关，默认否，某些接口需要使用https网关
     */
    public function request_send($api, $param = array(), $https = false,$method='get') {
//     	$url  =  "http://seller.zhe800.com/api/erp/v1/orders.json?page=1&per_page=20&start_time=1399887640&end_time=1431423640&authorize_token=24d662c0-f197-4635-9a90-f17a011e45df&api_token=333e637becff6b1ca934248c44ae7e9d";     
//         $ch = curl_init();
//     	curl_setopt($ch, CURLOPT_URL, $url);
//     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     	$result = curl_exec($ch);
//         return $result;
        //发送请求
        $param['authorize_token'] = $this->authorize_token;
        $param['api_token']       = $this->api_token;
        $url = $https ? $this->https_gate : $this->gate;
        $url .= $api;
        $result = $this->exec($url, $param,$method);
        return $result;
    }

    /**
     * API请求发送
     * @author wade
     * @date 2015-03-12
     * @param string $api 接口地址
     * @param array $params 请求参数
     */
    public function request_send_multi($api, $params = array(),$https = false) {
        $datas = array();
        foreach ($params as $param) {
            //增加系统级参数
            $data['method'] = $api;
            $data['timestamp'] = date('Y-m-d H:i:s');
            $data['format'] = 'json';
            $data['app_key'] = $this->app_key;
            $data['v'] = '2.0';
            $data['sign_method'] = 'md5';
            $data['session'] = $this->session;
            //封装签名
            $data = array_merge($data, $param);
            $sign = $this->sign($data);
            $data['sign'] = $sign;
            $datas[] = $data;
        }
        //发送请求
        $url = $https ? $this->https_gate : $this->gate;
        $result = $this->multiExec($url, $datas);
        return $result;
    }

    /**
     * 生成淘宝API请求签名
     * @author 
     * @date 
     * @todo 签名方法
     * @param array $param 待签名参数
     * @return string 返回签名
     */
    public function sign($param = array()) {
        $sign = $this->secret;
        ksort($param);
        foreach ($param as $k => $v) {
            if ("@" != substr($v, 0, 1)) {
                $sign .= "$k$v";
            }
        }
        unset($k, $v);
        $sign .= $this->secret;
        
        return strtoupper(md5($sign));
    }

    ##########################################################################
    /**
     * 商品列表下载(分发)
     * @author wade
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download(array $data) {
        return $this->goods_list_download_by_api($data);
    }

    /**
     * 商品列表下载(rds)
     * @author wade
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download_by_rds(array $data) {

    }

    /**
     * 商品列表下载(api)
     * @author wade
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download_by_api(array $data) {
        $params = array();
        $params['page'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['perPage'] = isset($data['page_size']) ? $data['page_size'] : 50;
        if (isset($data['start_modified'])) {
            $params['start_modified'] = $data['start_modified'];
        }


        $data['items']['item'] = array();
        $data['total_results'] = 0;

        $params['authorize_token'] = $this->authorize_token;
        $params['api_token']       = $this->api_token;
        $result = $this->request_send('products.json?'.http_build_query($params), $params);
        if (PHP_VERSION >= 5.4) {
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        } else {
            $return = json_decode($result, true);
        }
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        if ($return['pagination']['total_count'] > 0) {
            $data['items']['item'] = array_merge($data['items']['item'], $return['products']);
            $data['total_results'] = $return['pagination']['total_count'];
        }
        
        return $data;
    }
    
    /**
     * 根据淘宝商品num_iid, 获取单个商品信息
     * @author wade
     * @date 2015-03-12
     * @param string $data 淘宝商品num_iid, 可通过列表获取到
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.8.TZCa1M&path=cid:4-apiId:20
     */
    public function goods_info_download($data) {

    }

    /**
     * 批量返回淘宝商品数据，一次最多返回20个
     * @author wade
     * @date 2015-03-13
     * @param array $data 淘宝商品num_iid组成的数组, 可通过列表获取到
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.32.IRfvK1&path=cid:4-apiId:315
     * @return array
     * @throws ExtException
     */
    public function goods_info_download_multi($ids, $data=array()) {
        $result = array();
        foreach ($ids as $k=>$v){
            $result[$v] = $data[$v];
        }
        return $result;
    }
    
    ##########################################################################
    /**
     * 下载退单列表(分发)
     * @author jianbin.zheng
     * @date 2015-03-23
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 refunds=>退单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:10125-apiId:52
     */

    public function refund_list_download(array $data) {
            return $this->refund_list_download_by_api($data);
    }

    /**
     * 返回单条淘宝退单数据
     * @author jianbin.zheng
     * @date 2015-03-23
     * @param string
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:10125-apiId:53
     * @return array
     * @throws ExtException
     */
    public function refund_info_download($refund_id, $refund_info) {

    }

    /**
     * 通过API调用下载退单列表
     * @author jianbin.zheng
     * @date 2015-03-23
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 refunds=>退单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:10125-apiId:52
     */

    private function refund_list_download_by_api(array $data) {

    }

    /**
     * 读取RDS退单列表
     * @author jianbin.zheng
     * @date 2015-03-23
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:10125-apiId:52
     * @see 取sys_info.jdp_tb_refund数据
     */

    public function refund_list_download_by_rds($data) {

    }
    
    ##########################################################################
    /**
     * 订单列表下载(分发)
     * @author wade
     * @date 2015-03-25
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link GET http://seller.zhe800.com/api/erp/v1/orders.json
     * @see 取sys_info.jdp_tb_trade数据
     */
    public function order_list_download($data) {
        return $this->order_list_download_by_api($data);
    }

    /**
     * 订单列表下载(api) 增量下载
     * @author wade
     * @date 2015-03-25
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link GET http://seller.zhe800.com/api/erp/v1/orders.json
     */
    public function order_list_download_by_api(array $data) {
        $params = array();
        if (isset($data['fields'])) {
            $params['fields'] = $data['fields'];
        } else {
            $params['fields'] = 'tid';
        }
        $params['page']       = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['sort_rule']  = '0';
        $params['order_state']= '2';
        $params['per_page']   = isset($data['page_size']) ? $data['page_size'] : 100;
        if ( isset($data['start_modified']) && isset($data['end_modified']) && !empty($data['start_modified']) && !empty($data['end_modified']) ) {
            //开始时间和结束时间都传了， 以结束时间为准
            $params['end_time']   = strtotime($data['end_modified']);
            $params['start_time'] = strtotime('-1 day',strtotime($data['end_modified']));
        }elseif(isset($data['start_modified']) && !empty($data['start_modified'])){
            //只传了开始时间， 以当前时间为准， 开始时间向前推一天
            //$params['start_modified'] = $data['start_modified'];
            //$params['end_modified'] = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($data['start_modified'])));
            $params['end_time']   =  time();
            $params['start_time'] =  time() - 86400;
        }elseif(isset($data['end_modified']) && !empty($data['end_modified'])){
            //只传了结束时间， 以结束时间为准， 开始时间向前推一天
            $params['end_time']   = strtotime($data['end_modified']);
            $params['start_time'] = strtotime('-1 day',strtotime($data['end_modified']));
        }else{
            //开始结束都没有
            $params['end_time']   = time();
            $params['start_time'] = time() - 86400;
        }
       //var_dump($params);die;
        $params['authorize_token'] = $this->authorize_token;
        $params['api_token']       = $this->api_token;
        $return = $this->request_send('orders.json?'.http_build_query($params), $params);
        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['message'])) {
            $msg = $return['message'];
            if (isset($return['message'])) {
                $msg = $msg . '！详细信息：' . $return['message'];
            }
            throw new ExtException($msg, $return['error_response']['id']);
        }
        $return = array(
            'trades' => array('trade' => $return['orders']),
            'total_results' => $return['pagination']['total_count'],
        );
        return $return;
    }
    
    /**
     * 订单列表下载，全量接口
     * @author wade
     * @date 2015-04-27
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_created=>起始时间, end_created=>结束时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.SaICi0&path=cid:5-apiId:46
     */
    public function order_list_download_by_all($data){

    }
    
    
    /**
     * 订单列表下载(rds)
     * @author jianbin.zheng
     * @date 2015-03-25
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表,total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.9YAnMu&path=cid:5-apiId:46
     * @see 取sys_info.jdp_tb_trade数据
     */
    public function order_list_download_by_rds($data) {

    }

    

    /**
     * 根据淘宝订单tid, 获取单个订单信息
     * @author jianbin.zheng
     * @date 2015-03-16
     * @param $id 淘宝tid, 可通过列表获取到
     * @param string $data 淘宝列表完整数据
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:5-apiId:47
     * @see 取sys_info.jdp_tb_trade
     */
    public function order_info_download($id, $data = array()) {
        return $data;
    }
    
    /**
     * 从RDS获取单个订单详细信息
     * @author wade
     * @date 2015-03-28
     * @param string $data 订单tid
     * @return array
     */
    private function order_info_download_by_rds($data){

    }

    /**
     * 从API获取单个订单详细信息,免费, taobao.trade.fullinfo.get
     * @author wade
     * @date 2015-03-28
     * @param string $data 订单tid
     * @return array
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.5.fkXXA5&path=cid:5-apiId:54 获取单笔交易的详细信息
     */
    private function order_info_download_by_api($data){

    }


    /**
     * 库存上传,taobao.item.quantity.update,免费调用
     * @author wade
     * @date 2015-03-10
     * @param array $data 待更新的数组，必须包括第三方平台的SKU外部编码和库存数量
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.34.eakxYJ&path=cid:4-apiId:10591
     */
    public function inv_upload(array $data) {

    }

    /**
     * 库存上传,taobao.item.quantity.update,免费调用
     * @author wade
     * @date 2015-03-10
     * @param array $data 待更新的数组，必须包括第三方平台的SKU外部编码和库存数量，数组结构同inv_upload单个上传
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.39.AcJUhj&path=cid:4-apiId:21169
     */
    public function inv_upload_multi(array $data) {        //根据goods_from_id分片，一个最多支持20个sku

    }

    /**
     * 物流回写 deliver.json 免费
     * @author wade
     * @date 2015-05-26
     * @param array $data
     * @return array 返回更新的结果
     * @link POST http://seller.zhe800.com/api/erp/v1/orders/:id/deliver.json
     */
    public function logistics_upload(array $data) {
        $params = array();
        $params['id']                = $data['tid'];
        $params['logistics_id']      = $data['logistics_id']; //快递公司id
        $params['express_no']        = $data['express_no']; //物流单号
        $params['express_company']   = $data['logistics_name']; //物流公司名称
        
        $result = $this->request_send("/orders/{$data['tid']}/deliver.json", $params,false,'post');
        $result = $this->get_result($result);
        if($result['error'] == 0){
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $result['send_log'] = $send_log;
            $result['oms_log'] = $oms_log;
            return $result['data'];
        }else{
            $msg = $result['message'];
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg);
        }
    }

    /**
     * 库存同步 taobao.logistics.offline.send 免费
     * @author jianbin.zheng
     * @date 2015-03-10
     * @param array $data
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.14.NvzVW0&path=cid:7-apiId:10690
     */
    public function logistics_upload_multi(array $data) {
        ;
    }

    ##############################################################
    /**
     * 转换折800商品数据为共享表商品数据
     * @author wade
     * @date 2015-03-12
     * @param array $data 折800商品单个数据信息
     * @return array 共享表商品数据
     * @link http://open.taobao.com/apidoc/dataStruct.htm?spm=a219a.7395905.1998342880.8.4r6Shz&path=cid:4-dataStructId:113721-apiId:20-invokePath:item 折800Item数据结构
     */

    public function _trans_goods(array $data) {
        $return = array();

        $return['goods_name']       = isset($data['name']) ? $data['name'] : '';
        $return['goods_code']       = isset($data['num']) ? $data['num'] : '';
        $return['goods_from_id']    = isset($data['id']) ? $data['id'] : '';
        $return['num']              = isset($data['stock']) ? $data['stock'] : '';
        $return['seller_nick']      = isset($data['nick']) ? $data['nick'] : '';
        $return['source']           = 'zhe800';
        $return['status']           = $data['approve_status'] === 'onsale' ? 1 : 0;
        //@todo 扣减库存规则判断
        $return['stock_type']       = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
        $return['onsale_time']      = isset($data['list_time']) ? $data['list_time'] : '';
        //@todo has_sku需要调试
        $return['has_sku']          = empty($data['sku_descs']) ? 0 : 1;
        $return['price']            = isset($data['cur_price']) ? $data['cur_price'] : '';
        $return['goods_img']        = isset($data['image']) ? $data['image'] : '';
        $return['goods_desc']       = isset($data['desc']) ? $data['desc'] : '';

        return $return;
    }

    /**
     * 平台SKU信息->共享SKU
     * @param array $data
     * @return string
     */
    public function _trans_sku(array $data) {
        $return = array();
        if (isset($data['sku_descs'])) {
            foreach ($data['sku_descs'] as $value) {
                $sku = array();
                $sku['goods_from_id']       = $data['id'];
                $sku['source']              = 'zhe800';
                $sku['sku_id']              = $value['seller_no'];
                $sku['goods_barcode']       = isset($value['seller_no']) ? $value['seller_no'] : '';
                $sku['status']              = (isset($value['status']) && $value['status'] == 'delete') ? 0 : 1;
                $sku['num']                 = $value['stock'];
                $sku['price']               = $value['cur_price'];
                $sku['stock_type']          = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
                $sku['with_hold_quantity']  = isset($value['with_hold_quantity']) ? $value['with_hold_quantity'] : 0;
                $sku['sku_properties']      = $value['sku_num'];
                $sku['sku_properties_name'] = $value['sku_desc'];
                $return[] = $sku;
            }
        } else {
            //如果不存在SKU，则创建一条通色通码数据
            $sku = array();
            $sku['goods_from_id']       = $data['id'];
            $sku['source']              = 'zhe800';
            $sku['sku_id']              = '';
            $sku['goods_barcode']       = $data['outer_id'];
            $sku['status']              = 1;
            $sku['num']                 = $data['num'];
            $sku['price']               = $data['price'];
            $sku['stock_type']          = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
            $sku['with_hold_quantity']  = isset($data['with_hold_quantity']) ? $data['with_hold_quantity'] : 0;
            $sku['sku_properties_name'] = $sku['sku_properties'] = '';
            $return[] = $sku;
        }
        return $return;
    }

    /**
     * 平台订单信息->共享订单主表
     * @param type $shop_code
     * @param array $data
     * @return type
     */
    public function _trans_order($shop_code, array $data) {
        $datetime = date('Y-m-d H:i:s');
        $return = $data;
        
        $num = 0;
        foreach($data['products'] as $order){
            $num += $order['count'];
        }
        
        $return['tid']       = $data['id'];
        $return['source']    = 'zhe800';
        $return['shop_code'] = $shop_code;
        $return['pay_type']  = 0;
        $return['receiver_country']  = '中国';
        $return['receiver_province'] = $data['receiver']['province'];
        $return['receiver_city']     = $data['receiver']['city'];
        $return['receiver_district'] = $data['receiver']['county'];
        $return['receiver_name']     = $data['receiver']['name'];
        $return['receiver_zip_code'] = $data['receiver']['postCode'];
        $return['receiver_mobile']   = $data['receiver']['phone'];
        $return['receiver_phone']    = $data['receiver']['tel'];
        if( isset($data['receiver']['receiver_district']) && !empty($data['receiver']['receiver_district']) ){
            $return['receiver_address'] = $data['receiver']['province'] . $data['receiver']['city'] . $data['receiver']['county'] . $data['receiver']['address'];
        }else{
            $return['receiver_address'] = $data['receiver']['province'] . $data['receiver']['city'] . $data['receiver']['address'];
        }
        $return['receiver_addr'] = str_replace(array($return['receiver_province'], $return['receiver_city'], $return['receiver_district']), '', $return['receiver_address']);
        $return['num'] = $num; //商品购买数量。取值范围：大于零的整数,对于一个trade对应多个order的时候（一笔主订单，对应多笔子订单），num=0，num是一个跟商品关联的属性，一笔订单对应多比子订单的时候，主订单上的num无意义。
        $return['sku_num']       = count($data['orders']['products']); //平台sku种类数量
        $return['express_money'] = $data['postage'];
        $return['order_money']   = $data['order_price'];
        $return['buyer_remark']  = isset($data['buyer_comment']) ? $data['buyer_comment'] : '';
        $return['seller_remark'] = isset($data['seller_comment']) ? $data['seller_comment'] : '';
        $return['buyer_money']   = $data['order_price'];
        $return['integral_change_money'] = 0;
        $return['status'] = $data['status'] == '2' ? 1 : 0;

        $return['order_last_update_time']  = $data['updated_at'];
        $return['order_first_insert_time'] = $data['created_at'];
        $return['first_insert_time'] = $datetime;

        $return['last_update_time'] = $data['updated_at'];
        $return['buyer_nick']       = $data['nickname'];
        $return['seller_nick']      = $data['seller_nickname'];
        $return['invoice_type']     = $data['invoice']['type'];
        $return['invoice_title']    = $data['invoice']['title'];
        $return['invoice_content']  = $data['invoice']['content'];
        unset($return['id']);
        return $return;
    }

    /**
     * 平台订单信息->共享订单明细
     * <s>1、若trade.(payment - post_fee - cod_fee)=SUM(order.divide_order_fee)，则avg_money=order.divide_order_fee</s>
     * 2、若不相等，则avg_money=trade.(payment - post_fee - cod_fee)*order.(payment/SUM(payment))，采用舍去法保留两位小数，最后一个商品用减法
     * 计算完后，校验SUM(avg_money)=trade.(payment - post_fee - cod_fee),若相等则插入数据，否则不插入数据，记录日志错误
     * @param array $data
     * @return array
     */
    public function _trans_order_detail(array $data) {
        $return = array();
        if (isset($data['products'])) {
            $avg_total = 0;
            $total = 0;
            $base = $data['order_price'] - $data['postage'] ;
            foreach($data['products'] as $value){
                $total += $value['goods_earning'];
            }
            
            foreach ($data['products'] as $key=>$value) {
                $detail = $value;
                $sku    ="";
                foreach ($value['sku'] as $k=>$v){
                    $sku .= implode(":", $v).";";
                }
                $sku =trim($sku,";");
                $detail['tid']                  = $data['id'];
                $detail['oid']                  = $data['id'].$key;
                $detail['source']               = 'zhe800';
                $detail['return_status']        = $value['refund_status'];
                $detail['total_fee']            = $value['goods_earning'];
                $detail['payment']              = $value['goods_earning'];
                $detail['num']                  = $value['count'];
                $detail['discount_fee']         = 0;
                $detail['adjust_fee']           = 0;
                $detail['goods_code']           = isset($value['sku_num'])?$value['sku_num']:'';
                $detail['goods_barcode']        = isset($value['seller_no']) ? $value['seller_no'] : $value['seller_no'];
                $detail['express_company_name'] =  '';
                $detail['express_no']           =  '';
                $detail['sku_properties']       = '';
                $detail['pic_path']             = $value['image'];
                $detail['title']                = $value['name'];
                $detail['sku_properties']       = $sku;
                if($key < count($data['products'])-1){
                    if($total == 0){
                        $detail['avg_money'] = 0;
                    }else{
                        $detail['avg_money'] = number_format($base * ($value['goods_earning'] / $total),2,'.','');
                    }
                }else{
                    $detail['avg_money'] = $base - $avg_total;
                }
                $avg_total += $detail['avg_money'];
                $return[] = $detail;
            }
            
            //校验均摊金额, 其实没必要
            if( $base != $avg_total){
                throw new ExtException('订单均摊金额计算异常', -1);
            }
        }

        return $return;
    }

    /**
     * 平台退单信息->共享退单主表
     * @param type $shop_code
     * @param array $data
     * @return type
     */
    public function _trans_refund($shop_code, array $data) {

    }

    /**
     * 平台退单信息->共享退单明细
     * @param array $data
     * @return array
     */
    public function _trans_refund_detail(array $data) {

    }

    /**
     * 调到模型类,插入记录
     * @param type $shop_code
     * @param type $goods_info
     */
    public function save_source_goods_and_sku($shop_code, $data) {
        return $ret = load_model('source/ApiZhe800GoodsModel')->save_zhe800_goods_and_sku($shop_code, $data);
    }

    /**
     * 调到模型类,插入记录
     * @param type $shop_code
     * @param type $order_info
     */
    public function save_source_order_and_detail($shop_code, $data) {
        return $ret = load_model('source/ApiZhe800TradeModel')->save_zhe800_trade_and_order($shop_code, $data);
    }

    /**
     * 调到模型类,插入记录
     * @param string $shop_code
     * @param array $data
     */
    public function save_source_refund($shop_code, $data) {
        return $ret = load_model('source/ApiTaobaoRefundModel')->save_taobao_refund($shop_code, $data);
    }

    
    ##### 其他公有方法 ########################################################
    /**
     * 获取折800物流公司信息, 并记录到api_taobao_logistics_companies表中
     * @author wade
     * @date 2015-03-28
     * @todo
     */

    public function logistics_company_download() {
        return array();
    }

    /**
     * 保存折800物流公司信息
     * @author wade
     * @date 2015-03-28
     * @param string $shop_code 店铺代码
     * @param array $data 折800接口返回的信息
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data){
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'taobao');
    }
    
    #### 订单全链路相关 #######################################################
    /**
     * 订单全链路打标或标签更新
     * @author wade
     * @date 2015-04-02
     * @param array $data
     * @return array 折800平台返回信息
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386797.1998343897.3.otWwu6&id=102423 订单全链路状态回传
     */
    public function taobao_jds_TradeTrace(array $data) {

    }
    
    #### 天猫退款相关 #########################################################
    /**
     * 卖家同意退款 
     * @author wade
     * @date 2015-04-13
     * @param String $refund_infos  退款信息，格式：refund_id|amount|version|phase，其中refund_id为退款编号，amount为退款金额（以分为单位），version为退款最后更新时间（时间戳格式），phase为退款阶段（可选值为：onsale, aftersale，天猫退款必值，淘宝退款不需要传），多个退款以半角逗号分隔。
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.mfqyxM&path=cid:10125-apiId:22465
     */
    public function taobao_rp_refunds_agree($refund_infos){

    }
    
    /**
     * 卖家同意退货 
     * @author wade
     * @date 2015-04-13
     * @param array $data  退货信息数组，参数参考淘宝API，必填字段：refund_id
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.vOqmEk&path=cid:10125-apiId:22466 卖家同意退货API 
     */
    public function taobao_rp_returngoods_agree($data){

    }
    
    /**
     * 卖家回填物流信息
     * @author wade
     * @date 2015-04-13
     * @param array $data  回填物流信息数组，参数参考淘宝API，必填字段：'refund_id','refund_phase','logistics_waybill_no','logistics_company_code'
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.kxcQ1d&path=cid:10125-apiId:23876 卖家回填物流信息API
     */
    public function taobao_rp_returngoods_refill($data){

    }
    
    /**
     * 审核退款单，标志是否可用于批量退款，目前仅支持天猫订单
     * @author wade
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，operator，refund_phase，refund_version，result
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.nF25e5&path=cid:10125-apiId:23875 审核退款单API
     */
    public function taobao_rp_refund_review($data){

    }
    
    /**
     * 卖家拒绝退款
     * @author wade
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，refuse_message
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.rXh1Sn&path=cid:10125-apiId:10480 卖家拒绝退款API
     */
    public function taobao_refund_refuse($data){

    }
    
    /**
     * 卖家拒绝退货
     * @author wade
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，refund_phase，refund_version，refuse_proof
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.ZRRLbo&path=cid:10125-apiId:23877 卖家拒绝退货API
     */
    public function taobao_rp_returngoods_refuse($data){

    }
    
    /**
     * 单笔退款详情
     * @author wade
     * @date 2015-04-22
     * @param array $data refund_id必填 fields必填
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.3.LCK0rs&path=cid:10125-apiId:53 单笔退款详情
     */
    public function taobao_refund_get($data){

    }
    
    #### 淘宝分销相关 #########################################################
    /**
     * 分销商品下载
     * @author wade
     * @date 2015-04-15
     * @param array $data
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.5.R6NjYM&path=cid:15-apiId:328 查询产品列表
     */
    public function fx_goods_list_download($data) {

    }

    /**
     * 分销订单下载（分发）
     * @author wade
     * @date 2015-04-13
     * @param array $data 查询条件
     */
    public function fx_order_download($data){

    }
    
    /**
     * 从RDS获取分销订单， 含明细
     * @author wade
     * @date 2015-04-13
     * @param array $data 查询条件
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386781.1998343697.1.JtNjT8&id=101587#s2 聚石塔jdp_fx_trade部分
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.pFXp87&path=cid:15-apiId:180 回调值purchase_orders数据结构
     * @return array
     */
    private function fx_order_download_by_rds($data){

    }
    
    /**
     * @TODO 从API接口获取分销订单
     */
    private function fx_order_download_by_api($data){
        
    }
    
    /**
     * 保存分销订单原始数据
     * @author wade
     * @date 2015-04-14
     * @param string $shop_code 店铺代码
     * @param array $data 待保存数组
     */
    public function save_source_fx_order($shop_code, $data){
        return load_model('source/ApiTaobaoFxTradeModel')->save_taobao_trade_and_order($shop_code, $data);
    }
    
    /**
     * 保存分销商品原始数据
     * @author wade
     * @date 2015-04-15
     * @param string $shop_code 店铺代码
     * @param array $data 待保存数组
     */
    public function save_source_fx_goods($shop_code, $data){
        return load_model('source/ApiTaobaoFxGoodsModel')->save_taobao_goods_and_sku($shop_code, $data);
    }
    
    #### 支付宝对账单记录 #####################################################
    /**
     * 获取支付宝对账单记录
     * @author wade
     * @date 2015-04-20
     * @param array $data 查询条件
     * @link http://api.taobao.com/apidoc/api.htm?path=cid:10238-apiId:23584 获取支付宝对账单记录接口
     */
    public function alipay_user_accountreport_get($data){
    
    }
    
    /*
     * ########################智选物流相关接口################################
     * 2.1.1 物流服务商(cpCode) : 
     * 	顺丰(SF)、EMS(标准快递： EMS ；经济快件：EYB)、宅急送(ZJS)、圆通(YTO)、中通(ZTO)、百世汇通(HTKY)、优速(UC)  、申通(STO)、天天快递 (TTKDEX)、全峰 (QFKD)、快捷(FAST)、韵达（YUNDA ）、中国邮政（POST ）、国通（GTO ）  
	 * 2.1.2 订单来源(orderSource)：订单产生的电商平台  
	 *	淘宝(TB)、天猫(TM)、京东(JD)、当当(DD)、拍拍(PP)、易讯(YX)、ebay(EBAY)、QQ 网购(QQ)、亚马逊(AMAZON)、苏宁(SN)、国美(GM)、唯品会(WPH)、聚美(JM)、乐蜂(LF)、蘑菇街(MGJ)、聚尚(JS)、拍鞋(PX)、银泰(YT)、1 号店(YHD)、凡客(VANCL)、邮乐(YL)、优购(YG)、其他(OTHERS)  
     * 
     * 
     */
    /**
     * 获取智选物流推荐的物流服务商及服务产品
     * @example
     * <pre>
     * $opt = array('send_address'=>"浙江省杭州市余杭区",
				'receive_address'=>"山东省济南市历下区千佛山街道千佛山小区99号",
				'cpid_list'=>"2,101,502",
				'service_type'=>88,
				'trade_order'=>"1200000011",
				'order_source'=>"TB"
		);
		$ret = $model->smartwl_assistant_get($opt);
		</pre>
     * @param unknown $params
     * @return mixed
     */
    function smartwl_assistant_get($params) {
    	$m = 'taobao.smartwl.assistant.get';
    	
    	$opts = array();    	
    	$this->_merge_params($opts, $params,
    			array('send_address', 'receive_address',
    					'cpid_list', 'service_type', 'trade_order', 'order_source'));
    	$this->_merge_opts($opts, $params, array('feature'));
    	
    	$ret = $this->get_data($m, $opts);
    	
    	return $ret['smartwl_assistant_get_response'];
    }
    
    /**
     * 将卖家最终真是的订单旅行包裹信息继续进行回传
     * @example
     * <pre>
     * $opt = array(
				'cp_id'=>"101",
				'trade_order'=>"1200000011",
				'order_src'=>"TB",
				'mail_no'=>'34567890',
				'weight'=>2112
		);
		$ret = $model->smartwl_package_create($opt);
     * </pre>
     * @param unknown $params
     * @return mixed
     */
    function smartwl_package_create($params) {
    	$m = 'taobao.smartwl.package.create';
    	 
    	$opts = array();
    	$this->_merge_params($opts, $params,
    			array('cp_id', 'trade_order', 'order_src', 'mail_no', 'weight'));
    	$this->_merge_opts($opts, $params, array('length', 'width', 'height', 'volumn', 'feature'));
    	 
    	$ret = $this->get_data($m, $opts);
    	 
    	return $ret['smartwl_package_create_response'];
    }
    
    /**
     * 获取商家在智选物流中的信息
     * @example
     * <pre>
     * $ret = $model->taobao_smartwl_userinfo_get(array());
     * </pre>
     * @param unknown $params
     * @return unknown
     */
    function taobao_smartwl_userinfo_get($params) {
    	$m = 'taobao.smartwl.userinfo.get';
    	$opts = array();
    
    	$this->_merge_opts($opts, $params, array('feature'));
    
    	$ret = $this->get_data($m, $opts);
    
    	return $ret['smartwl_userinfo_get_response'];
    }
    
    private function get_data($m, $opts) {
    	$return = $this->request_send($m, $opts);
    	var_dump($return);
    	return $this->json_decode($return);
    }
    
    /**
     * 合并必填参数
     * @param unknown $dest
     * @param unknown $src
     * @param unknown $fields
     */
    function _merge_params(&$dest, $src, $fields) {
    	foreach ($fields as $f) {
    		$dest[$f] = $src[$f];
    	}
    }
    /**
     * 合并可选参数
     * @param unknown $dest
     * @param unknown $src
     * @param unknown $fields
     */
    function _merge_opts(&$dest, $src, $fields) {
    	foreach ($fields as $f) {
    		if (isset($src[$f])) {
    			$dest[$f] = $src[$f];
    		}
    	}
    }
    
    ##### 类目接口 ############################################################
    /**
     * 获取标准商品类目属性 
     * @author wade
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.2.yjycWy&path=cid:3-apiId:121 
     */
    public function taobao_itemprops_get($data){

    }
    
    /**
     * 获取后台供卖家发布商品的标准商品类目 
     * @author wade
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:3-apiId:122
     */
    public function taobao_itemcats_get($data){

    }
    
    /**
     * 获取标准类目属性值 
     * @author wade
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.qhKv2j&path=cid:3-apiId:13
     */
    public function taobao_itempropvalues_get($data){

    }
    
    /**
     * 查询商家被授权品牌列表和类目列表  
     * @author wade
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.4.5qve1D&path=cid:3-apiId:161
     */
    public function taobao_itemcats_authorize_get($data){

    }
    
}