<?php
require_once 'AbsAPIModel.php';

require_lib('Exceptions/ExtException');

/**
 * 淘宝API类
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-09
 */
class TaobaoAPIModel extends AbsAPIModel {

    /**
     * 接口网关地址
     * @var string 
     */
    public $gate = 'http://gw.api.taobao.com/router/rest';
    //public $gate = 'http://223.4.54.191/taobao_trans_req.php?';
    public $https_gate = 'https://eco.taobao.com/router/rest';

    /**
     * 淘宝应用的APP_KEY
     * @var string
     */
    private $app_key;

    /**
     * 淘宝应用的secret
     * @var string
     */
    private $secret;

    /**
     * 淘宝授权session
     * @var string 
     */
    private $session;

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
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param array $token 应用及授权信息数组
     */
    public function __construct($token) {
        $this->app_key = $token['app_key'];
        $this->session = $token['session'];
        $this->secret = $token['secret'];
        $this->seller_nick = $token['nick'];

        $this->order_pk = 'tid';
        $this->goods_pk = 'num_iid';
        $this->refund_pk = 'refund_id';
        $this->mode = strtolower($token['mode']);

        //沙盒测试模式处理 ++++++++++++++++++++++++++++++++++++++++++++
        if(isset($token['sandbox']) && $token['sandbox']){
            $this->sandbox = true;
            $this->rds_db_name = 'pts_info';
        }else{
            $this->sandbox = false;
            $this->rds_db_name = 'sys_info';
        }

        if ('rds' == $this->mode) {
            $this->order_page_size = 500;
        } else {
            $this->order_page_size = 100;
        }

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    }

    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param string $api 接口地址
     * @param array $param 请求参数
     * @param boolean $https 是否https网关，默认否，某些接口需要使用https网关
     */
    public function request_send($api, $param = array(), $https = false) {
        //增加系统级参数
        $data['method'] = $api;
        $data['timestamp'] = date('Y-m-d H:i:s');
        $data['format'] = 'json';
        $data['app_key'] = $this->app_key;
        $data['v'] = '2.0';
        $data['sign_method'] = 'md5';
        $data['session'] = $this->session;
        //$data['nick'] = $this->seller_nick;
        //封装签名
        $data = array_merge($data, $param);
        $sign = $this->sign($data);
        $data['sign'] = $sign;
        //发送请求
        $url = $https ? $this->https_gate : $this->gate;
        $result = $this->exec($url, $data);
        //dump($data);
        return $result;
    }

    /**
     * API请求发送
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
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
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download(array $data) {
        if ($this->mode == 'api') {
            return $this->goods_list_download_by_api($data);
        } else {
            return $this->goods_list_download_by_rds($data);
        }
    }

    /**
     * 商品列表下载(rds)
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download_by_rds(array $data) {
        $params = array();
        if (isset($data['fields'])) {
            $params['fields'] = $data['fields'];
        } else {
            $params['fields'] = 'num_iid';
        }
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['order_by'] = 'modified:desc';
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if (isset($data['start_modified'])) {
            $params['start_modified'] = $data['start_modified'];
        }

        $start = $params['page_size'] * ($params['page_no'] - 1);
        if (empty($data['start_modified'])) {
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_tb_item where nick = :nick order by modified";
            $sql_value = array(':nick' => $data['seller_nick']);
        } else {
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_tb_item where nick = :nick and modified > :start_modified order by modified";
            $sql_value = array(
                ':nick' => $data['seller_nick'],
                ':start_modified' => $data['start_modified']
            );
        }

        $result = CTX()->db->get_limit($sql, $sql_value, $data['page_size'], $start);
        $total_results = CTX()->db->get_value('SELECT FOUND_ROWS()');

        $data['items']['item'] = array();
        foreach ($result as $item) {
            if (PHP_VERSION >= 5.4) {
                $refund = json_decode($item['jdp_response'], true, 512, JSON_BIGINT_AS_STRING);
            } else {
                $refund = json_decode($item['jdp_response'], true);
            }
            array_push($data['items']['item'], $refund['item_get_response']['item']);
        }

        $data['total_results'] = $total_results;

        return $data;
    }

    /**
     * 商品列表下载(api)
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 items=>商品列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.7KnzAk&path=cid:4-apiId:18
     */

    public function goods_list_download_by_api(array $data) {
        $params = array();
        if (isset($data['fields'])) {
            $params['fields'] = $data['fields'];
        } else {
            $params['fields'] = 'num_iid';
        }
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['order_by'] = 'modified:desc';
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if (isset($data['start_modified'])) {
            $params['start_modified'] = $data['start_modified'];
        }

        $get_types = array('onsale', 'inventory'); //在售、在库
        $data['items']['item'] = array();
        $data['total_results'] = 0;
        foreach ($get_types as $get_type) {
            $params['get_type'] = $get_type;
            $result = $this->request_send('taobao.items.' . $get_type . '.get', $params);

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

            if ($return['items_' . $get_type . '_get_response']['total_results'] > 0) {
                $data['items']['item'] = array_merge($data['items']['item'], $return['items_' . $get_type . '_get_response']['items']['item']);
                $data['total_results'] += $return['items_' . $get_type . '_get_response']['total_results'];
            }
        }
        
        return $data;
    }
    
    /**
     * 根据淘宝商品num_iid, 获取单个商品信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param string $data 淘宝商品num_iid, 可通过列表获取到
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.8.TZCa1M&path=cid:4-apiId:20
     */
    public function goods_info_download($data) {
        $params = array();
        $params['fields'] = 'detail_url,num_iid,title,nick,type,desc,props_name,created,promoted_service,is_lightning_consignment,is_fenxiao,auction_point,property_alias,template_id,after_sale_id,is_xinpin,sub_stock,inner_shop_auction_template_id,outer_shop_auction_template_id,food_security,features,locality_life,desc_module_info,with_hold_quantity,change_prop,delivery_time,paimai_info,sell_point,valid_thru,outer_id,auto_fill,desc_modules,custom_made_type_id,wireless_desc,is_offline,barcode,is_cspu,newprepay,sub_title,video_id,mpic_video,sold_quantity,second_result,qualification,shop_type,open_iid,global_stock_type,global_stock_country,cid,seller_cids,props,input_pids,input_str,pic_url,num,list_time,delist_time,stuff_status,location,price,post_fee,express_fee,ems_fee,has_discount,freight_payer,has_invoice,has_warranty,has_showcase,modified,increment,approve_status,postage_id,product_id,item_imgs,prop_imgs,is_virtual,is_taobao,is_ex,is_timing,videos,is_3D,score,one_station,second_kill,violation,is_prepay,ww_status,wap_desc,wap_detail_url,cod_postage_id,sell_promise,sku.status,sku.with_hold_quantity,sku.sku_id,sku.iid,sku.num_iid,sku.quantity,sku.price,sku.created,sku.modified,sku.properties,sku.properties_name,sku.outer_id,sku.barcode';
        $params['num_iid'] = $data;

        $return = $this->request_send('taobao.item.get', $params);
        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }
        

        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        
        return $return['item_get_response'];
    }

    /**
     * 批量返回淘宝商品数据，一次最多返回20个
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-13
     * @param array $data 淘宝商品num_iid组成的数组, 可通过列表获取到
     * @return array 返回淘宝标准信息
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.32.IRfvK1&path=cid:4-apiId:315
     * @return array
     * @throws ExtException
     */
    public function goods_info_download_multi($ids, $data=array()) {
        $params = array();
        $params['fields'] = 'detail_url,num_iid,title,nick,type,desc,props_name,created,promoted_service,is_lightning_consignment,is_fenxiao,auction_point,property_alias,template_id,after_sale_id,is_xinpin,sub_stock,inner_shop_auction_template_id,outer_shop_auction_template_id,food_security,features,locality_life,desc_module_info,with_hold_quantity,change_prop,delivery_time,paimai_info,sell_point,valid_thru,outer_id,auto_fill,desc_modules,custom_made_type_id,wireless_desc,is_offline,barcode,is_cspu,newprepay,sub_title,video_id,mpic_video,sold_quantity,second_result,qualification,shop_type,open_iid,global_stock_type,global_stock_country,cid,seller_cids,props,input_pids,input_str,pic_url,num,list_time,delist_time,stuff_status,location,price,post_fee,express_fee,ems_fee,has_discount,freight_payer,has_invoice,has_warranty,has_showcase,modified,increment,approve_status,postage_id,product_id,item_imgs,prop_imgs,is_virtual,is_taobao,is_ex,is_timing,videos,is_3D,score,one_station,second_kill,violation,is_prepay,ww_status,wap_desc,wap_detail_url,cod_postage_id,sell_promise,sku.status,sku.with_hold_quantity,sku.sku_id,sku.iid,sku.num_iid,sku.quantity,sku.price,sku.created,sku.modified,sku.properties,sku.properties_name,sku.outer_id,sku.barcode';
        $params['num_iids'] = implode(',', $ids);

        $return = $this->request_send('taobao.items.list.get', $params);
        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }
        
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['items_list_get_response']['items']['item'];
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
        if ($this->mode == 'api') {
            return $this->refund_list_download_by_api($data);
        } else {
            return $this->refund_list_download_by_rds($data);
        }
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
        $params = array();
        $params['fields'] = 'refund_id, alipay_no, tid, oid, buyer_nick, seller_nick, total_fee, status, created, refund_fee, good_status, has_good_return, payment, reason, desc, num_iid, title, price, num, good_return_time, company_name, sid, address, shipping_type, refund_remind_timeout, attribute, outer_id, sku';
        $params['refund_id'] = $refund_id;

        $return = $this->request_send('taobao.refund.get', $params);

        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }
        
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['refund_get_response']['refund'];
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
        $params = array();
        $params['fields'] = 'refund_id';
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['order_by'] = 'modified:desc';
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if (isset($data['start_modified'])) {
            $params['start_modified'] = $data['start_modified'];
        }

        $return = $this->request_send('taobao.refunds.receive.get', $params);

        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }
        

        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['refunds_receive_get_response'];
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
        $start = $data['page_size'] * ($data['page_no'] - 1);
        if (empty($data['start_modified'])) {
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_tb_refund where seller_nick = :seller_nick order by modified";
            $sql_value = array(':seller_nick' => $data['seller_nick']);
        } else {
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_tb_refund where seller_nick = :seller_nick and modified > :start_modified order by modified";
            $sql_value = array(
                ':seller_nick' => $data['seller_nick'],
                ':start_modified' => $data['start_modified']
            );
        }
        $result = CTX()->db->get_limit($sql, $sql_value, $data['page_size'], $start);
        $total_results = CTX()->db->get_value('SELECT FOUND_ROWS()');

        $refunds = array();
        foreach ($result as $item) {
            if(PHP_VERSION>=5.4){
                $refund = json_decode($item['jdp_response'], true, 512, JSON_BIGINT_AS_STRING);
            }else{
                $refund = json_decode($item['jdp_response'], true);
            }
            array_push($refunds, $refund['refund_get_response']['refund']);
        }
        $return = array(
            'refunds' => array(
                'refund' => $refunds
            ),
            'total_results' => $total_results
        );

        return $return;
    }
    
    ##########################################################################
    /**
     * 订单列表下载(分发)
     * @author jianbin.zheng
     * @date 2015-03-25
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.9YAnMu&path=cid:5-apiId:46
     * @see 取sys_info.jdp_tb_trade数据
     */
    public function order_list_download($data) {
        if ($this->mode == 'api') {
            return $this->order_list_download_by_api($data);
        } else {
            return $this->order_list_download_by_rds($data);
        }
    }

    /**
     * 订单列表下载(api) 增量下载
     * @author jianbin.zheng
     * @date 2015-03-25
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_modified=>起始时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:5-apiId:128
     */
    public function order_list_download_by_api(array $data) {
        $params = array();
        if (isset($data['fields'])) {
            $params['fields'] = $data['fields'];
        } else {
            $params['fields'] = 'tid';
        }
        $params['page_no']   = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['order_by']  = 'modified:desc';
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if ( isset($data['start_modified']) && isset($data['end_modified']) && !empty($data['start_modified']) && !empty($data['end_modified']) ) {
            //开始时间和结束时间都传了， 以结束时间为准
            $params['end_modified'] = $data['end_modified'];
            $params['start_modified'] = date('Y-m-d H:i:s', strtotime('-1 day',strtotime($data['end_modified'])));
        }elseif(isset($data['start_modified']) && !empty($data['start_modified'])){
            //只传了开始时间， 以当前时间为准， 开始时间向前推一天
            //$params['start_modified'] = $data['start_modified'];
            //$params['end_modified'] = date('Y-m-d H:i:s', strtotime('+1 day',strtotime($data['start_modified'])));
            $params['end_modified'] = date('Y-m-d H:i:s');
            $params['start_modified'] = date('Y-m-d H:i:s', time() - 86400);
        }elseif(isset($data['end_modified']) && !empty($data['end_modified'])){
            //只传了结束时间， 以结束时间为准， 开始时间向前推一天
            $params['end_modified'] = $data['end_modified'];
            $params['start_modified'] = date('Y-m-d H:i:s', strtotime('-1 day',strtotime($data['end_modified'])));
        }else{
            //开始结束都没有
            $params['end_modified'] = date('Y-m-d H:i:s');
            $params['start_modified'] = date('Y-m-d H:i:s', time() - 86400);
        }
        $return = $this->request_send('taobao.trades.sold.increment.get', $params);

        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }

        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        
        return $return['trades_sold_increment_get_response'];
    }
    
    /**
     * 订单列表下载，全量接口
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @param array $data 查询条件 page_no=>页码，page_size=>每页条数，start_created=>起始时间, end_created=>结束时间
     * @return array 返回淘宝标准信息 包含 trades=>订单列表，total_results=>总条数
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.SaICi0&path=cid:5-apiId:46
     */
    public function order_list_download_by_all($data){
        $params = array();
        if (isset($data['fields'])) {
            $params['fields'] = $data['fields'];
        } else {
            $params['fields'] = 'tid';
        }
        $params['page_no'] = isset($data['page_no']) ? $data['page_no'] : 1;
        $params['page_size'] = isset($data['page_size']) ? $data['page_size'] : 100;
        if ( isset($data['start_created']) && isset($data['end_created']) && !empty($data['start_created']) && !empty($data['end_created']) ) {
            //开始时间和结束时间都传了
            $params['end_created']   = $data['end_created'];
            $params['start_created'] =  $data['start_created'];
        }elseif(isset($data['start_created']) && !empty($data['start_created'])){
            //只传了开始时间， 以开始时间为准， 结束时间为当前
            $params['end_created'] = date('Y-m-d H:i:s');
            $params['start_created'] = $data['start_created'];
        }elseif(isset($data['end_created']) && !empty($data['end_created'])){
            //只传了结束时间， 以结束时间为准， 开始时间向前推15天
            $params['end_created'] = $data['end_created'];
            $params['start_created'] = date('Y-m-d H:i:s', strtotime('-15 day',strtotime($data['end_created'])));
        }

        $return = $this->request_send('taobao.trades.sold.get', $params);

        if(PHP_VERSION>=5.4){
            $return = json_decode($return, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($return, true);
        }

        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        
        return $return['trades_sold_get_response'];
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
        $start = $data['page_size'] * ($data['page_no'] - 1);
        if (empty($data['start_modified'])) {
            $sql = "select jdp_response from {$this->rds_db_name}.jdp_tb_trade where seller_nick = :seller_nick order by modified";
            $sql2 = "select count(*) as num from {$this->rds_db_name}.jdp_tb_trade where seller_nick = :seller_nick";
            $sql_value = array(':seller_nick' => $data['seller_nick']);
        } else {
            //补单(上次更新之前一个月)
            $start_modified = date('Y-m-d H:i:s', strtotime('-10 minute',strtotime($data['start_modified'])));

            if (isset($data['end_modified']) && !empty($data['end_modified'])) {
                $end_modified = $data['end_modified'];
            } else {
                $end_modified = date('Y-m-d H:i:s');
            }

            $sql = "select jdp_response,modified from {$this->rds_db_name}.jdp_tb_trade
                    WHERE seller_nick = :seller_nick
                    AND modified > :start_modified  
                    AND modified <= :end_modified order by modified";
            $sql2 = "select count(*) as num from {$this->rds_db_name}.jdp_tb_trade
                    WHERE seller_nick = :seller_nick
                    AND modified > :start_modified  
                    AND modified <= :end_modified";
            /***
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from 
                        (SELECT tid,modified,jdp_response FROM {$this->rds_db_name}.jdp_tb_trade 
                            WHERE seller_nick = :seller_nick
                            AND modified > :start_modified  
                        UNION 
                        SELECT a.tid,a.modified,a.jdp_response FROM {$this->rds_db_name}.jdp_tb_trade AS a LEFT JOIN api_order b USING (tid)
                            WHERE a.seller_nick = :seller_nick 
                            AND a.modified > :start_modified 
                            AND a.modified <= :end_modified 
                            AND ISNULL(b.tid)
                        ) as t order by modified ";
             * 
             */
            $sql_value = array(
                ':seller_nick' => isset($data['seller_nick'])?$data['seller_nick']:$this->seller_nick,
                ':start_modified' => $start_modified,
                ':end_modified' => $end_modified,
            );
        }
        $tradeList = CTX()->db->get_limit($sql, $sql_value, $data['page_size'], $start);
        $total_results = CTX()->db->get_value($sql2, $sql_value);
        $data['trades']['trade'] = array();
        foreach ($tradeList as $item) {
            if(PHP_VERSION>=5.4){
                $jdp_response = json_decode($item['jdp_response'], true, 512, JSON_BIGINT_AS_STRING);
            }else{
                $jdp_response = json_decode($item['jdp_response'], true);
            }
            $jdp_response['trade_fullinfo_get_response']['trade']['modified'] = $item['modified'];
            array_push($data['trades']['trade'], $jdp_response['trade_fullinfo_get_response']['trade']);
        }
        
        $data['total_results'] = $total_results;
        return $data;
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
        if ($this->mode == 'api') {
            return $this->order_info_download_by_api($id);
        } else {
            return $this->order_info_download_by_rds($id);
        }
    }
    
    /**
     * 从RDS获取单个订单详细信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param string $data 订单tid
     * @return array
     */
    private function order_info_download_by_rds($data){
        $sql = "select jdp_response from {$this->rds_db_name}.jdp_tb_trade where tid = :tid";
        $jdp_response = CTX()->db->get_value($sql, array(':tid' => (string) $data));
        
        if(PHP_VERSION>=5.4){
            $return = json_decode($jdp_response, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($jdp_response, true);
        }
        return $return['trade_fullinfo_get_response']['trade'];
    }

    /**
     * 从API获取单个订单详细信息,免费, taobao.trade.fullinfo.get
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param string $data 订单tid
     * @return array
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.5.fkXXA5&path=cid:5-apiId:54 获取单笔交易的详细信息
     */
    private function order_info_download_by_api($data){
        $param = array();
        $param['tid'] = $data;
        $param['fields'] = 'seller_nick, orders,orders.out_iid,buyer_nick,title,type,created,tid,seller_rate,buyer_rate,status,payment,adjust_fee,post_fee,total_fee,pay_time,end_time,modified,consign_time,buyer_obtain_point_fee,point_fee,real_point_fee,received_payment,commission_fee,seller_memo,alipay_no,alipay_id,buyer_message,pic_path,num_iid,num,price,buyer_alipay_no,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,receiver_mobile,receiver_phone,seller_flag,seller_mobile,seller_phone,seller_name,seller_email,available_confirm_fee,has_post_fee,timeout_action_time,snapshot_url,cod_fee,cod_status,shipping_type,trade_memo,is_3D,buyer_email,buyer_area,trade_from,is_lgtype,is_force_wlb,is_brand_sale,buyer_cod_fee,discount_fee,seller_cod_fee,express_agency_fee,invoice_name,service_orders,credit_cardfee,step_trade_status,step_paid_fee,mark_desc,trade_source,eticket_ext,send_time,is_daixiao,is_part_consign,arrive_interval,arrive_cut_time,consign_interval,zero_purchase,alipay_point';
        $result = $this->request_send('taobao.trade.fullinfo.get', $param);
        
        if(PHP_VERSION>=5.4){
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($result, true);
        }
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['trade_fullinfo_get_response']['trade'];
    }


    /**
     * 库存上传,taobao.item.quantity.update,免费调用
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-10
     * @param array $data 待更新的数组，必须包括第三方平台的SKU外部编码和库存数量
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.34.eakxYJ&path=cid:4-apiId:10591
     */
    public function inv_upload(array $data) {
        $param = array();
        $param['num_iid'] = $data['goods_from_id'];
        //taobao库存同步要注意：with_hold_quantity 商品在付款减库存的状态下，该sku上未付款的订单数量，同步的数量要大于等于这个数量
        //如果同步的数量 <  with_hold_quantity,取 with_hold_quantity 作为同步数量
        $param['quantity'] = $data['inv_num'] < $data['with_hold_quantity'] ? $data['with_hold_quantity'] : $data['inv_num'];
        $param['sku_id'] = $data['sku_id'];
        //$param['outer_id'] = $data['goods_barcode'];
        $param['type'] = 1;
        if(PHP_VERSION>=5.4){
            $return = json_decode($this->request_send('taobao.item.quantity.update', $param), true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($this->request_send('taobao.item.quantity.update', $param), true);
        }
        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        
        return $return;
    }

    /**
     * 库存上传,taobao.item.quantity.update,免费调用
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-10
     * @param array $data 待更新的数组，必须包括第三方平台的SKU外部编码和库存数量，数组结构同inv_upload单个上传
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.39.AcJUhj&path=cid:4-apiId:21169
     */
    public function inv_upload_multi(array $data) {        //根据goods_from_id分片，一个最多支持20个sku
        $list_pieces = array();
        $new_data = array();
        $list_pieces_new = array();
        foreach ($data as $item) {
            //taobao库存同步要注意：with_hold_quantity 商品在付款减库存的状态下，该sku上未付款的订单数量，同步的数量要大于等于这个数量
            //如果同步的数量 <  with_hold_quantity,取 with_hold_quantity 作为同步数量
            if(isset($item['with_hold_quantity']) && $item['inv_num'] < $item['with_hold_quantity']) {
                $item['inv_num'] = $item['with_hold_quantity'];
            }
            $list_pieces_new[$item['goods_from_id']][] = $item;
            if(empty($item['sku_id'])){
                $list_pieces[$item['goods_from_id']]['goods_from_id']  = $item['goods_from_id'];
                $list_pieces[$item['goods_from_id']]['num'] = $item['inv_num'];
                $list_pieces[$item['goods_from_id']]['is_goods'] = 1;
                continue;
            }
            
            if (!array_key_exists($item['goods_from_id'], $list_pieces)) {
                $list_pieces[$item['goods_from_id']]['goods_from_id']  = $item['goods_from_id'];
                $list_pieces[$item['goods_from_id']]['sku_id_inv_num'] = $item['sku_id'] . ':' . $item['inv_num'];
                $list_pieces[$item['goods_from_id']]['count']  = 1;
            } else if ($list_pieces[$item['goods_from_id']]['count'] < 20) {
                $list_pieces[$item['goods_from_id']]['sku_id_inv_num'] .= ';' . $item['sku_id'] . ':' . $item['inv_num'];
                $list_pieces[$item['goods_from_id']]['count']++;
            } else if (!array_key_exists($item['goods_from_id'] . '_2', $list_pieces)) {
                $list_pieces[$item['goods_from_id'] . '_2']['goods_from_id']  = $item['goods_from_id'];
                $list_pieces[$item['goods_from_id'] . '_2']['sku_id_inv_num'] = $item['sku_id'] . ':' . $item['inv_num'];
                $list_pieces[$item['goods_from_id'] . '_2']['count'] = 1;
            } else {
                $list_pieces[$item['goods_from_id'] . '_2']['sku_id_inv_num'] .= ';' . $item['sku_id'] . ':' . $item['inv_num'];
                $list_pieces[$item['goods_from_id'] . '_2']['count']++;
            }
        }
        
        $returns = array();
        $returns_error = array();
        $messgae_all = '';
        foreach ($list_pieces as $key=>$value) {
            //仅商品，没有SKU
            if($value['is_goods'] == 1){
                $param = array();
                $param['num_iid'] = $value['goods_from_id'];
                $param['quantity'] = $value['num'];
                $param['type'] = 1;
                $return = json_decode($this->request_send('taobao.skus.quantity.update', $param), true, 512, JSON_BIGINT_AS_STRING);
                continue;
            }
            
            
            $param = array();
            $param['num_iid'] = $value['goods_from_id'];
            $param['skuid_quantities'] = $value['sku_id_inv_num'];
            $param['type'] = 1;
            
            /*** 
            // 临时处理 商品上下架
            $num = CTX()->db->get_value("select sum(inv_num) from api_goods_sku where num>0  and inv_num >0 and goods_from_id = :id ",array(':id'=>$value['goods_from_id']));
            
            $temp_result = $this->request_send('taobao.item.update.listing', array(
                'num_iid'=>$value['goods_from_id'],
                'num'=>$num,
            ));
            dump(array(
                'num_iid'=>$value['goods_from_id'],
                'num'=>$num,
            ));
            dump($temp_result);
            ***/
            
            if(PHP_VERSION>=5.4){
                $return = json_decode($this->request_send('taobao.skus.quantity.update', $param), true, 512, JSON_BIGINT_AS_STRING);
            }else{
                $return = json_decode($this->request_send('taobao.skus.quantity.update', $param), true);
            }
            
            //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
            if (isset($return['error_response'])) {
                $msg = $return['error_response']['msg'];
                if (isset($return['error_response']['sub_msg'])) {
                    $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
                }
                $messgae_all.=$msg;
               // throw new ExtException($msg, $return['error_response']['code']);
                $returns_error[] = array('msg'=>$msg,'code'=>$return['error_response']['code']);
            }else{

                foreach($list_pieces_new[$key] as $old_val){
                     array_push($new_data, $old_val);
                }
                array_push($returns, $return);
            }
        }
        if(!empty($returns_error)){
         //     throw new ExtException($messgae_all, -1000);
        }
       // $data = $new_data;
        return $new_data;
    }

    /**
     * 物流回写 taobao.logistics.offline.send 免费
     * @author jianbin.zheng
     * @date 2015-03-10
     * @param array $data
     * @return array 返回更新的结果
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.14.NvzVW0&path=cid:7-apiId:10690
     */
    public function logistics_upload(array $data) {
        //淘宝发货物流单号必填
        if(!isset($data['express_code']) || empty($data['express_code'])){
            $send_log['status'] = -1;
            $oms_log['is_back'] = 2;
            $msg = '必须存在物流单号';
            $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
            throw new ExtException($msg, -1);
        }
        
        //dump($data,1);
        //先判断订单状态
        $sql = "select status from {$this->rds_db_name}.jdp_tb_trade where tid = :tid";
        $order_status = CTX()->db->get_value($sql, array(':tid' => (string) $data['tid']));

        $no_upload_statis = array(
            'WAIT_BUYER_CONFIRM_GOODS',
            'TRADE_FINISHED',
            'TRADE_CLOSED'
        );
        if (in_array($order_status, $no_upload_statis)) {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            $return['send_log'] = $send_log;
            $return['oms_log'] = $oms_log;
            return $return;
        }

        $param = array(
            'tid' => $data['tid'], //淘宝交易ID
            'out_sid' => $data['express_no'], //运单号
            'company_code' => $data['express_code'], //物流公司代码
        );
        
        if(isset($data['type']) && $data['type']=='cod'){
            //货到付款发货接口
            $return = $this->json_decode($this->request_send('taobao.logistics.online.send', $param));
        }else{
            //普通发货接口
            $return = $this->json_decode($this->request_send('taobao.logistics.offline.send', $param));
        }

        //日志记录到业务系统中，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
        if (isset($return['error_response'])) {
            
            $check_status = $this->check_is_logistics_upload($data['tid'],$data['express_no']);
            if($check_status===false){
                $msg = $return['error_response']['msg'];
                if (isset($return['error_response']['sub_msg'])) {
                    $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
                }
                if (isset($return['error_response']['sub_code'])) {
                    $msg = $msg . '---' . $return['error_response']['sub_code'];
                }

                $send_log['status'] = -1;
                $oms_log['is_back'] = 2;
                $oms_log['is_back_reason'] = $send_log['error_remark'] = $msg;
                throw new ExtException($msg, $return['error_response']['code']);
            }else{
                $oms_log['is_back'] = $send_log['status'] = 1;
                $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
            }
        } else {
            $oms_log['is_back'] = $send_log['status'] = 1;
            $oms_log['is_back_time'] = $send_log['upload_time'] = date('Y-m-d H:i:s');
        }

//        //异常处理，例如返回{"error_response":{"code":25,"msg":"Invalid signature","request_id":"109wcmcgllsj1"}}
//        if (isset($return['error_response'])) {
//            $msg = $return['error_response']['msg'];
//            if (isset($return['error_response']['sub_msg'])) {
//                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
//            }
//            if (isset($return['error_response']['sub_code'])) {
//                $msg = $msg . '---' . $return['error_response']['sub_code'];
//            }
//            throw new ExtException($msg, $return['error_response']['code']);
//        }

        $return['send_log'] = $send_log;
        $return['oms_log'] = $oms_log;
        return $return;
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
     * 转换淘宝商品数据为共享表商品数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 淘宝商品单个数据信息
     * @return array 共享表商品数据
     * @link http://open.taobao.com/apidoc/dataStruct.htm?spm=a219a.7395905.1998342880.8.4r6Shz&path=cid:4-dataStructId:113721-apiId:20-invokePath:item 淘宝Item数据结构
     */

    public function _trans_goods(array $data) {
        $return = array();

        $return['goods_name'] = isset($data['title']) ? $data['title'] : '';
        $return['goods_code'] = isset($data['outer_id']) ? $data['outer_id'] : '';
        $return['goods_from_id'] = isset($data['num_iid']) ? $data['num_iid'] : '';
        $return['num'] = isset($data['num']) ? $data['num'] : '';
        $return['seller_nick'] = isset($data['nick']) ? $data['nick'] : '';
        $return['source'] = 'taobao';
        $return['status'] = $data['approve_status'] === 'onsale' ? 1 : 0;
        //@todo 扣减库存规则判断
        $return['stock_type'] = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
        $return['onsale_time'] = isset($data['list_time']) ? $data['list_time'] : '';
        //@todo has_sku需要调试
        $return['has_sku'] = empty($data['skus']['sku']) ? 0 : 1;
        $return['price'] = isset($data['price']) ? $data['price'] : '';
        $return['goods_img'] = isset($data['pic_url']) ? $data['pic_url'] : '';
        $return['goods_desc'] = isset($data['desc']) ? $data['desc'] : '';

        return $return;
    }

    /**
     * 平台SKU信息->共享SKU
     * @param array $data
     * @return string
     */
    public function _trans_sku(array $data) {
        $return = array();
        if (isset($data['skus']['sku'])) {
            foreach ($data['skus']['sku'] as $value) {
                $sku = array();
                $sku['goods_from_id'] = $data['num_iid'];
                $sku['source'] = 'taobao';
                $sku['sku_id'] = $value['sku_id'];
                $sku['goods_barcode'] = isset($value['outer_id']) ? $value['outer_id'] : '';
                $sku['status'] = (isset($value['status']) && $value['status'] == 'delete') ? 0 : 1;
                $sku['num'] = $value['quantity'];
                $sku['price'] = $value['price'];
                $sku['stock_type'] = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
                $sku['with_hold_quantity'] = isset($value['with_hold_quantity']) ? $value['with_hold_quantity'] : 0;
                $sku['sku_properties'] = $value['properties'];
                $sku['sku_properties_name'] = $value['properties_name'];
                $return[] = $sku;
            }
        } else {
            //如果不存在SKU，则创建一条通色通码数据
            $sku = array();
            $sku['goods_from_id'] = $data['num_iid'];
            $sku['source'] = 'taobao';
            $sku['sku_id'] = '';
            $sku['goods_barcode'] = $data['outer_id'];
            $sku['status'] = 1;
            $sku['num'] = $data['num'];
            $sku['price'] = $data['price'];
            $sku['stock_type'] = isset($data['sub_stock']) ? $data['sub_stock'] : 1;
            $sku['with_hold_quantity'] = isset($data['with_hold_quantity']) ? $data['with_hold_quantity'] : 0;
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
        foreach($data['orders']['order'] as $order){
            $num += $order['num'];
        }
        
        $return['source'] = 'taobao';
        $return['shop_code'] = $shop_code;
        $return['pay_type'] = $data['type'] === 'cod' ? 1 : 0;
        $return['receiver_country'] = '中国';
        $return['receiver_province'] = $data['receiver_state'];
        if( isset($data['receiver_district']) && !empty($data['receiver_district']) ){
            $return['receiver_address'] = $data['receiver_state'] . $data['receiver_city'] . $data['receiver_district'] . $data['receiver_address'];
        }else{
            $return['receiver_address'] = $data['receiver_state'] . $data['receiver_city'] . $data['receiver_address'];
        }
        $return['receiver_addr'] = isset($data['receiver_address']) ? $data['receiver_address'] : '';
        $return['receiver_zip_code'] = $data['receiver_zip'];
        
        $return['num'] = $num; //商品购买数量。取值范围：大于零的整数,对于一个trade对应多个order的时候（一笔主订单，对应多笔子订单），num=0，num是一个跟商品关联的属性，一笔订单对应多比子订单的时候，主订单上的num无意义。
        $return['sku_num'] = count($data['orders']['order']); //平台sku种类数量
        $return['express_money'] = $data['post_fee'];
        $return['order_money'] = $data['payment'];
        $return['buyer_remark'] = isset($data['buyer_message']) ? $data['buyer_message'] : '';
        $return['seller_remark'] = isset($data['seller_memo']) ? $data['seller_memo'] : '';
        $return['buyer_money'] = $data['payment'];
        $return['integral_change_money'] = $data['point_fee'] / 100;
        $return['status'] = $data['status'] == 'WAIT_SELLER_SEND_GOODS' ? 1 : 0;
        $return['order_last_update_time'] = $data['modified'];
        $return['order_first_insert_time'] = $data['created'];
        $return['first_insert_time'] = $datetime;
        if($data['type']=='nopaid'&&!isset($data['pay_time'])){
            $return['pay_time']=$data['created'];
        }
        
        $return['last_update_time'] = $data['modified'];

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
        if (isset($data['orders']['order'])) {
            $avg_total = 0;
            $total = 0;
            $base = $data['payment'] - $data['post_fee'] - $data['cod_fee'];
            foreach($data['orders']['order'] as $value){
                $total += $value['payment'];
            }
            
            foreach ($data['orders']['order'] as $key=>$value) {
                $detail = $value;
                $detail['tid'] = $data['tid'];
                $detail['source'] = 'taobao';
                $detail['return_status'] = $value['refund_status'];
                $detail['goods_code'] = isset($value['outer_iid'])?$value['outer_iid']:'';
                $detail['goods_barcode'] = isset($value['outer_sku_id']) ? $value['outer_sku_id'] : $value['outer_iid'];
                $detail['express_company_name'] = isset($value['logistics_company']) ? $value['logistics_company'] : '';
                $detail['express_no'] = isset($value['invoice_no']) ? $value['invoice_no'] : '';
                $detail['sku_properties'] = isset($value['sku_properties_name'])?$value['sku_properties_name']:'';
                if($key < count($data['orders']['order'])-1){
                    if($total == 0){
                        $detail['avg_money'] = 0;
                    }else{
                        $detail['avg_money'] = round($base * ($value['payment'] / $total));
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
        $datetime = date('Y-m-d H:i:s');
        //允许转单的类型
        $refund_allow = array('WAIT_SELLER_AGREE', 'WAIT_BUYER_RETURN_GOODS', 'WAIT_SELLER_CONFIRM_GOODS', 'SUCCESS');
        $return = array(
            'refund_id' => $data['refund_id'],
            'tid' => $data['tid'],
            'oid' => isset($data['oid']) ? $data['oid'] : '',
            'source' => 'taobao',
            'shop_code' => $shop_code,
            'status' => in_array($data['status'], $refund_allow) ? 1 : 0,
            'seller_nick' => $data['seller_nick'],
            'buyer_nick' => $data['buyer_nick'],
            'has_good_return' => $data['has_good_return'],
            'refund_fee' => $data['refund_fee'],
            'payment' => $data['payment'],
            'refund_reason' => isset($data['refund_reason']) ? $data['refund_reason'] : '',
            'refund_desc' => isset($data['desc']) ? $data['desc'] : '',
            'refund_express_code' => isset($data['company_name']) ? $data['company_name'] : '',
            'refund_express_no' => isset($data['sid']) ? $data['sid'] : '',
        );

        $return['order_last_update_time'] = isset($data['modified']) ? $data['modified'] : '';
        $return['order_first_insert_time'] = $data['created'];
        $return['first_insert_time'] = $return['last_update_time'] = $datetime;

        return $return;
    }

    /**
     * 平台退单信息->共享退单明细
     * @param array $data
     * @return array
     */
    public function _trans_refund_detail(array $data) {
        $sql = "select goods_code,goods_barcode,sku_properties from api_order_detail where oid = :oid";
        $goods_sku = CTX()->db->get_row($sql, array('oid' => $data['oid']));

        $return = array(
            'refund_id' => $data['refund_id'],
            'tid' => $data['tid'],
            'oid' => $data['oid'],
            'title' => $data['title'],
            'price' => $data['price'],
            'num' => $data['num'],
            'refund_price' => $data['refund_fee'],
            'goods_code' => isset($goods_sku['goods_code']) ? $goods_sku['goods_code'] : '',
            'goods_barcode' => isset($goods_sku['goods_barcode']) ? $goods_sku['goods_barcode'] : '',
            'sku_properties' => isset($goods_sku['sku_properties']) ? $goods_sku['sku_properties'] : '',
        );

        return $return;
    }

    /**
     * 调到模型类,插入记录
     * @param type $shop_code
     * @param type $goods_info
     */
    public function save_source_goods_and_sku($shop_code, $data) {
        return $ret = load_model('source/ApiTaobaoGoodsModel')->save_taobao_goods_and_sku($shop_code, $data);
    }

    /**
     * 调到模型类,插入记录
     * @param type $shop_code
     * @param type $order_info
     */
    public function save_source_order_and_detail($shop_code, $data) {
        return $ret = load_model('source/ApiTaobaoTradeModel')->save_taobao_trade_and_order($shop_code, $data);
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
     * 获取淘宝物流公司信息, 并记录到api_taobao_logistics_companies表中
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @todo
     */

    public function logistics_company_download() {
        return array();
    }

    /**
     * 保存淘宝物流公司信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @param string $shop_code 店铺代码
     * @param array $data 淘宝接口返回的信息
     * @return array
     */
    public function save_source_logistics_company($shop_code, $data){
        return $ret = load_model('source/ApiLogisticsCompanyModel')->save_list($shop_code, $data, 'taobao');
    }
    
    #### 订单全链路相关 #######################################################
    /**
     * 订单全链路打标或标签更新
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-02
     * @param array $data
     * @return array 淘宝平台返回信息
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386797.1998343897.3.otWwu6&id=102423 订单全链路状态回传
     */
    public function taobao_jds_TradeTrace(array $data) {
        $tmc = array();
        foreach($data as $msg){
            $tmc[] = array(
                'topic' => 'taobao_jds_TradeTrace',
                'content' => array(
                    'tid' => $msg['tid'],
                    'order_ids' => $msg['order_ids'],
                    'status' => $msg['status'],
                    'action_time' => $msg['action_time'],
                    'operator' => 'efast5',
                    'remark' => $msg['remark'],
                    'seller_nick' => $msg['seller_nick'],
                ),
            );
        }
        $str_tmc = json_encode($tmc);
        //过滤掉tid外的双引号，32位PHP直接转换为数字会导致整形溢出
        $str_tmc_fliter = preg_replace('|"tid":"(.*?)"|', '"tid":$1', $str_tmc);
        
        $param['messages'] = $str_tmc_fliter;
        $result = $this->request_send('taobao.tmc.messages.produce', $param);
        
        if(PHP_VERSION>=5.4){
            $return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
        }else{
            $return = json_decode($result, true);
        }
        
        return $return['tmc_messages_produce_response'];
    }
    
    #### 天猫退款相关 #########################################################
    /**
     * 卖家同意退款 
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param String $refund_infos  退款信息，格式：refund_id|amount|version|phase，其中refund_id为退款编号，amount为退款金额（以分为单位），version为退款最后更新时间（时间戳格式），phase为退款阶段（可选值为：onsale, aftersale，天猫退款必值，淘宝退款不需要传），多个退款以半角逗号分隔。
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.mfqyxM&path=cid:10125-apiId:22465
     */
    public function taobao_rp_refunds_agree($refund_infos){
        $param = array();
        $param['refund_infos'] = $refund_infos;
        $result = $this->request_send('taobao.rp.refunds.agree', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['rp_refunds_agree_response'];
    }
    
    /**
     * 卖家同意退货 
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data  退货信息数组，参数参考淘宝API，必填字段：refund_id
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.vOqmEk&path=cid:10125-apiId:22466 卖家同意退货API 
     */
    public function taobao_rp_returngoods_agree($data){
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }
        ### 可选参数 ###
        $param = get_exist_vars($data, array('name','address','post','tel','mobile','remark','refund_phase','refund_version','seller_address_id'));
        $param['refund_id'] = $data['refund_id'];
        
        $result = $this->request_send('taobao.rp.returngoods.agree', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['rp_returngoods_agree_response'];
    }
    
    /**
     * 卖家回填物流信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data  回填物流信息数组，参数参考淘宝API，必填字段：'refund_id','refund_phase','logistics_waybill_no','logistics_company_code'
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.kxcQ1d&path=cid:10125-apiId:23876 卖家回填物流信息API
     */
    public function taobao_rp_returngoods_refill($data){
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }
        if (!isset($data['refund_phase']) || empty($data['refund_phase'])) {
            throw new ExtException('-1', 'refund_phase不能为空');
        }
        if (!isset($data['logistics_waybill_no']) || empty($data['logistics_waybill_no'])) {
            throw new ExtException('-1', 'logistics_waybill_no不能为空');
        }
        if (!isset($data['logistics_company_code']) || empty($data['logistics_company_code'])) {
            throw new ExtException('-1', 'logistics_company_code不能为空');
        }
        $param = get_array_vars($data,array('refund_id','refund_phase','logistics_waybill_no','logistics_company_code'));
        $result = $this->request_send('taobao.rp.returngoods.refill', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['rp_returngoods_refill_response'];
    }
    
    /**
     * 审核退款单，标志是否可用于批量退款，目前仅支持天猫订单
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，operator，refund_phase，refund_version，result
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.nF25e5&path=cid:10125-apiId:23875 审核退款单API
     */
    public function taobao_rp_refund_review($data){
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }
        if (!isset($data['operator']) || empty($data['operator'])) {
            throw new ExtException('-1', 'operator不能为空');
        }
        if (!isset($data['refund_phase']) || empty($data['refund_phase'])) {
            throw new ExtException('-1', 'refund_phase不能为空');
        }
        if (!isset($data['refund_version']) || empty($data['refund_version'])) {
            throw new ExtException('-1', 'refund_version不能为空');
        }
        if (!isset($data['result']) || empty($data['result'])) {
            throw new ExtException('-1', 'result不能为空');
        }
        if (!isset($data['message']) || empty($data['message'])) {
            throw new ExtException('-1', 'message不能为空');
        }
        
        $param = get_array_vars($data,array('refund_id','operator','refund_phase','refund_version','result','message'));
        $result = $this->request_send('taobao.rp.refund.review', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['rp_refund_review_response'];
    }
    
    /**
     * 卖家拒绝退款
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，refuse_message
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.rXh1Sn&path=cid:10125-apiId:10480 卖家拒绝退款API
     */
    public function taobao_refund_refuse($data){
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }
        if (!isset($data['refuse_message']) || empty($data['refuse_message'])) {
            throw new ExtException('-1', 'refuse_message不能为空');
        }
        
        ### 可选参数 ###
        $param = get_exist_vars($data, array('refund_id','refuse_message','refuse_proof','refund_phase','refund_version'));
        
        $result = $this->request_send('taobao.refund.refuse', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['refund_refuse_response'];
    }
    
    /**
     * 卖家拒绝退货
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data 退款单数组，参数参考淘宝API， 必填字段：refund_id，refund_phase，refund_version，refuse_proof
     * @return array 返回淘宝信息;
     * @link http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.ZRRLbo&path=cid:10125-apiId:23877 卖家拒绝退货API
     */
    public function taobao_rp_returngoods_refuse($data){
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }
        if (!isset($data['refund_phase']) || empty($data['refund_phase'])) {
            throw new ExtException('-1', 'refund_phase不能为空');
        }
        if (!isset($data['refund_version']) || empty($data['refund_version'])) {
            throw new ExtException('-1', 'refund_version不能为空');
        }
        if (!isset($data['refuse_proof']) || empty($data['refuse_proof'])) {
            throw new ExtException('-1', 'refuse_proof不能为空');
        }
        
        $param = get_array_vars($data,array('refund_id', 'refund_phase', 'refund_version', 'refuse_proof'));
        $result = $this->request_send('taobao.rp.returngoods.refuse', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['rp_returngoods_refuse_response'];
    }
    
    /**
     * 单笔退款详情
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-22
     * @param array $data refund_id必填 fields必填
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.3.LCK0rs&path=cid:10125-apiId:53 单笔退款详情
     */
    public function taobao_refund_get($data){
        $param = array();
        ### 必填参数 ###
        if (!isset($data['refund_id']) || empty($data['refund_id'])) {
            throw new ExtException('-1', 'refund_id不能为空');
        }else{
            $param['refund_id'] = $data['refund_id'];
        }
        ### 默认参数 ###
        if (!isset($data['fields']) || empty($data['fields'])) {
            $param['fields'] = 'refund_id, alipay_no, tid, oid, buyer_nick, seller_nick, total_fee, status, created, refund_fee, good_status, has_good_return, payment, reason, desc, num_iid, title, price, num, good_return_time, company_name, sid, address, shipping_type, refund_remind_timeout, refund_phase, refund_version, operation_contraint, attribute, outer_id, sku';
        }else{
            $param['fields'] = $data['fields'];
        }
        
        $result = $this->request_send('taobao.refund.get', $param);
        $return = $this->json_decode($result);
        
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }

        return $return['refund_get_response'];
    }
    
    #### 淘宝分销相关 #########################################################
    /**
     * 分销商品下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-15
     * @param array $data
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.5.R6NjYM&path=cid:15-apiId:328 查询产品列表
     */
    public function fx_goods_list_download($data) {
        $param = array();
        if(isset($data['start_modified'])&&!empty($data['start_modified'])){
            $param['start_modified'] = $data['start_modified'];
        }
        
        if(isset($data['end_modified'])&&!empty($data['end_modified'])){
            $param['end_modified'] = $data['end_modified'];
        }else{
            $param['end_modified'] = date('Y-m-d H:i:s');
        }
        $param['page_size'] = $data['page_size'];
        $param['page_no'] = $data['page_no'];
        $param['fields'] = 'skus,images';
        $result = $this->request_send('taobao.fenxiao.products.get',$param);
        $return = $this->json_decode($result);

        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        
        return $result['fenxiao_products_get_response'];
    }

    /**
     * 分销订单下载（分发）
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data 查询条件
     */
    public function fx_order_download($data){
        $page = 1;
        $page_size = 50;
        $results = $result = array();
        
        do {
            $param = array(
                'seller_nick' => $data['nick'],
                'page_no' => $page,
                'page_size' => $page_size,
                'start_modified' => $data['start_modified']
            );

            if ($this->mode == 'api') {
                $result = $this->fx_order_download_by_rds($param);
            } else {
                $result = $this->fx_order_download_by_rds($param);
            }

            if (!isset($result['total_results']) || 0 == $result['total_results']) {
                $results = array();
                break;
            }
            $pages = ceil($result['total_results'] / $page_size);
            $results = array_merge($results, $result['purchase_orders']['purchase_order']);
            $page++;
        } while ($page < $pages);
        //dump($results,1);
        return $results;
    }
    
    /**
     * 从RDS获取分销订单， 含明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param array $data 查询条件
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386781.1998343697.1.JtNjT8&id=101587#s2 聚石塔jdp_fx_trade部分
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.pFXp87&path=cid:15-apiId:180 回调值purchase_orders数据结构
     * @return array
     */
    private function fx_order_download_by_rds($data){
        $start = $data['page_size'] * ($data['page_no'] - 1);
        
        if (empty($data['start_modified'])) {
            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_fx_trade where supplier_username = :seller_nick order by modified";
            $sql_value = array(
                ':seller_nick' => isset($data['seller_nick']) ? $data['seller_nick'] : $this->seller_nick,
            );
        } else {
            //补单(上次更新之前一3天)
            $data['start_modified'] = date('Y-m-d H:i:s', strtotime('-3 day',strtotime($data['start_modified'])));
            if (isset($data['end_modified']) && !empty($data['end_modified'])) {
                $end_modified = $data['end_modified'];
            } else {
                $end_modified = date('Y-m-d H:i:s');
            }

            $sql = "select SQL_CALC_FOUND_ROWS jdp_response from {$this->rds_db_name}.jdp_fx_trade 
                    where supplier_username = :seller_nick 
                    and modified >= :start_modified 
                    and modified <= :end_modified  
                    order by modified";
            $sql_value = array(
                ':seller_nick' => isset($data['seller_nick'])?$data['seller_nick']:$this->seller_nick,
                ':start_modified' => $data['start_modified'],
                ':end_modified' => $end_modified,
            );
        }
        $tradeList = CTX()->db->get_limit($sql, $sql_value, $data['page_size'], $start);
        $total_results = CTX()->db->get_value('SELECT FOUND_ROWS()');

        $data['purchase_orders']['purchase_order'] = array();
        foreach ($tradeList as $item) {
            $jdp_response = $this->json_decode($item['jdp_response']);
            array_push($data['purchase_orders']['purchase_order'], $jdp_response['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0]);
        }
        $data['total_results'] = $total_results;

        return $data;
    }
    
    /**
     * @TODO 从API接口获取分销订单
     */
    private function fx_order_download_by_api($data){
        
    }
    
    /**
     * 保存分销订单原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-14
     * @param string $shop_code 店铺代码
     * @param array $data 待保存数组
     */
    public function save_source_fx_order($shop_code, $data){
        return load_model('source/ApiTaobaoFxTradeModel')->save_taobao_trade_and_order($shop_code, $data);
    }
    
    /**
     * 保存分销商品原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
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
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-20
     * @param array $data 查询条件
     * @link http://api.taobao.com/apidoc/api.htm?path=cid:10238-apiId:23584 获取支付宝对账单记录接口
     */
    public function alipay_user_accountreport_get($data){
        $param = array();        
        if (isset($data['end_time']) && !empty($data['end_time'])) {
            $param['end_time'] = $data['end_time'];
            $param['start_time'] = date('Y-m-d H:i:s',  strtotime('-1 day',strtotime($param['end_time'])));
        }else{
            $param['end_time'] = date('Y-m-d H:i:s');
            $param['start_time'] = date('Y-m-d H:i:s',  strtotime('-1 day',strtotime($param['end_time'])));
        }
        $param['page_no'] = $data['page_no'];
        $param['page_size'] = 100;
        $param['fields'] = 'create_time,type,business_type,balance,in_amount,out_amount,alipay_order_no,merchant_order_no,self_user_id,opt_user_id,memo';
        $result = $this->request_send('alipay.user.accountreport.get', $param, true);
        $return = $this->json_decode($result);
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['alipay_user_accountreport_get_response'];
        
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
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.2.yjycWy&path=cid:3-apiId:121 
     */
    public function taobao_itemprops_get($data){
        $param = array();
        if(!isset($data['cid'])){
            throw new Exception('cid必填', -1);
        }else{
            $param['cid'] = $data['cid'];
        }
        if(isset($data['fields'])){
            $param['fields'] = $data['fields'];
        }
        $return = $this->json_decode($this->request_send('taobao.itemprops.get',$param));
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['itemprops_get_response'];
    }
    
    /**
     * 获取后台供卖家发布商品的标准商品类目 
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?path=cid:3-apiId:122
     */
    public function taobao_itemcats_get($data){
        $param = array();
        if(!isset($data['cids']) && !isset($data['parent_cid'])){
            throw new Exception('cids、parent_cid至少传一个', -1);
        }
        if(isset($data['cids'])){
            $param['cids'] = $data['cids'];
        }
        if(isset($data['parent_cid'])){
            $param['parent_cid'] = $data['parent_cid'];
        }
        if(isset($data['datetime'])){
            $param['datetime'] = $data['datetime'];
        }
        if(isset($data['fields'])){
            $param['fields'] = $data['fields'];
        }
        $return = $this->json_decode($this->request_send('taobao.itemcats.get',$param));
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['itemcats_get_response'];
    }
    
    /**
     * 获取标准类目属性值 
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.1.qhKv2j&path=cid:3-apiId:13
     */
    public function taobao_itempropvalues_get($data){
        $param = array();
        if(!isset($data['fields'])){
            $param['fields'] = 'cid,pid,prop_name,vid,name,name_alias,status,sort_order';
        }else{
            $param['fields'] = $data['fields'];
        }
        
        if(!isset($data['cid'])){
            throw new Exception('cid必填', -1);
        }else{
            $param['cid'] = $data['cid'];
        }
        
        $return = $this->json_decode($this->request_send('taobao.itempropvalues.get',$param));
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['itempropvalues_get_response'];
    }
    
    /**
     * 查询商家被授权品牌列表和类目列表  
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-27
     * @param array $data 查询条件
     * @return array 返回json_decode以后的淘宝原始数据
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.4.5qve1D&path=cid:3-apiId:161
     */
    public function taobao_itemcats_authorize_get($data){
        $param = array();
        if(!isset($data['fields'])){
            $param['fields'] = 'brand.vid, brand.name, item_cat.cid, item_cat.name, item_cat.status,item_cat.sort_order,item_cat.parent_cid,item_cat.is_parent, xinpin_item_cat.cid, xinpin_item_cat.name, xinpin_item_cat.status, xinpin_item_cat.sort_order, xinpin_item_cat.parent_cid, xinpin_item_cat.is_parent';
        }else{
            $param['fields'] = $data['fields'];
        }
        
        $return = $this->json_decode($this->request_send('taobao.itemcats.authorize.get',$param));
        //返回失败抛出异常 ===============================================
        if (isset($return['error_response'])) {
            $msg = $return['error_response']['msg'];
            if (isset($return['error_response']['sub_msg'])) {
                $msg = $msg . '！详细信息：' . $return['error_response']['sub_msg'];
            }
            throw new ExtException($msg, $return['error_response']['code']);
        }
        return $return['itemcats_authorize_get_response'];
    }
    
}