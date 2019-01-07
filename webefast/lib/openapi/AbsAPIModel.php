<?php

require_lib('net/HttpClient');

/**
 * 第三方平台API抽象类，定义若干业务接口
 *
 * @author jhua.zuo<jhua.zuo@baisonmail.com>
 * @date 2015-03-09
 */
abstract class AbsAPIModel {

    /**
     * 第三方平台接口地址
     * @var string
     */
    public $gate;

    /**
     * 第三方平台订单主键名
     * @var string 
     */
    public $order_pk;
    
    /**
     *  第三方平台商品主键名
     * @var string 
     */
    public $goods_pk;
    
    /**
     * 退单特殊处理标识符, 标识有额外方法通过不同接口获取退单额外数据
     * @var boolean 
     */
    public $refund_ext = false;

    /**
     * 商品下载有额外的入口
     * @var boolean
     */
    public $goods_ext = false;

    /**
     * 订单特殊处理标识符, 标识有额外方法通过不同接口获取订单额外数据
     * @var boolean 
     */
    public $order_ext = false;
    
    /**
     * 订单每页获取数量
     * @var int
     */
    public $order_page_size = 100;

    /**
     * 商品每页获取数量
     * @var int
     */
    public $goods_page_size = 100;

    /**
     * 退单每页获取数量
     * @var int
     */
    public $refund_page_size = 30;

    /**
     * 库存同步下架预警数
     * @var int
     */
    protected  $delisting_alert_num = 30;
    /**
     * 第三方平台请求发送
     */
    abstract public function request_send($api, $param = array());

    /**
     * 批量发送第三方平台请求
     */
    abstract public function request_send_multi($api, $params = array());

    /**
     * 生成签名
     * @param array $param 待签名参数
     * @return string 返回签名值
     */
    abstract public function sign($param = array());
    
    ####################################################################
    /**
     * 批量下载商品信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回共享表信息和平台原始数据
     */

    abstract public function goods_list_download(array $data);

    /**
     * 单个商品信息下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回共享表信息和平台原始数据
     */
    abstract public function goods_info_download($data);

    /**
     * 批量商品信息下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-14
     * @return array 返回共享表信息和平台原始数据
     */
    abstract public function goods_info_download_multi($ids, $data=array());

    /**
     * 商品下载的额外方法，如果派生类中 $this->goods_ext 为TRUE，则覆写此方法
     * @param $data
     */
    public function goods_download_ext($data){return array();}

    /**
     * 商品下载的额外方法，如果派生类中 $this->goods_ext 为TRUE，则覆写此方法
     * @param $data
     */
    public function save_source_goods_and_sku_ext($shop_code, $data){return array();}
    /**
     * 商品下载的额外方法，如果派生类中 $this->goods_ext 为TRUE，则覆写此方法
     * @param $data
     */
    public function _trans_goods_ext($data){return array();}

    /**
     * 商品下载的额外方法，如果派生类中 $this->goods_ext 为TRUE，则覆写此方法
     * @param $data
     */
    public function _trans_sku_ext($data){return array();}

    /**
     * 订单列表下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回共享表信息和平台原始数据
     */
    abstract public function order_list_download($data);

    /**
     * 单个订单下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回共享表信息和平台原始数据
     */
    abstract public function order_info_download($id, $data = array());
    
    /**
     * 订单处理的额外方法，如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function order_download_ext($data){}
    /**
     * 转换订单的额外方法，如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function _trans_order_ext($shop_code, $data){return array();}
    /**
     * 转换订单明细的额外方法，如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function _trans_order_detail_ext($data){return array();}
    
    /**
     * 保存订单额外方法， 如果派生类中 $this->order_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-28
     */
    public function save_source_order_ext($shop_code, $data){return false;}
    
    #####################################################################
    
    /**
     * 退单列表下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @return array 返回退单列表信息
     */
    abstract public function refund_list_download(array $data);
    
    /**
     * 单个退单明细下载
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     * @return array 返回退单明细信息
     */
    abstract public function refund_info_download($refund_id, $refund_info);
    
    
    /**
     * 退单处理的额外方法，如果派生类中 $this->refund_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     */
    public function refund_download_ext($data){}
    /**
     * 转换退单的额外方法，如果派生类中 $this->refund_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     */
    public function _trans_refund_ext($shop_code,$data){return array();}
    /**
     * 转换退单明细的额外方法，如果派生类中 $this->refund_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     */
    public function _trans_refund_detail_ext($data){return array();}
    
    /**
     * 保存退单额外方法， 如果派生类中 $this->refund_ext 为TRUE，则覆写此方法
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-10
     */
    public function save_source_refund_ext($shop_code, $data){return false;}
    
    #####################################################################

    /**
     * 库存信息回传
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回结果和第三方平台原始信息
     */
    abstract public function inv_upload(array $data);

    /**
     * 批量库存信息回传
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-10
     * @return array 返回结果和第三方平台原始信息
     */
    abstract public function inv_upload_multi(array $data);

    /**
     * 发货信息回传
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @return array 返回结果和第三方平台原始信息
     */
    abstract public function logistics_upload(array $data);

    /**
     * 批量发货信息回传
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @return array 返回结果和第三方平台原始信息
     */
    //abstract public function logistics_upload_multi(array $data);
    
    /**
     * 下载物流公司原始数据
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-28
     * @return array
     */
    abstract public function logistics_company_download();
    
    /**
     * 下载区域地址信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-08
     * @param mix $data
     * @return array
     */
    //abstract public function api_area_download($data);
    
    ######################################################################

    /**
     * 转换平台商品为标准信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 第三方平台商品信息
     */
    abstract public function _trans_goods(array $data);

    /**
     * 转换SKU信息为标准信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 第三方平台商品SKU信息
     */
    abstract public function _trans_sku(array $data);

    /**
     * 转换订单信息为标准订单
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 第三方平台订单信息
     */
    abstract public function _trans_order($shop_code, array $data);

    /**
     * 转换订单明细信息为标准订单明细
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param array $data 第三方平台订单明细信息
     */
    abstract public function _trans_order_detail(array $data);

    /**
     * 转换退单信息为标准退单信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $data 第三方平台订单明细信息
     */
    abstract public function _trans_refund($shop_code, array $data);

    /**
     * 转换退单明细信息为标准退单明细信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-23
     * @param array $data 第三方平台订单明细信息
     */
    abstract public function _trans_refund_detail(array $data);
    
    #####################################################################

    /**
     * 保存原始订单和订单明细数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    abstract public function save_source_order_and_detail($shop_code, $data);

    /**
     * 保存原始退单和退单数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    abstract public function save_source_refund($shop_code, $data);

    /**
     * 保存原始商品和sku数据
     * @param $shop_code
     * @param $data
     * @return mixed
     */
    abstract public function save_source_goods_and_sku($shop_code, $data);
    
    /**
     * 保存物流公司原始数据
     * @param string $shop_code 店铺代码 
     * @param array $data 物流公司原始数据
     * @return array
     */
    abstract public function save_source_logistics_company($shop_code, $data);
    
    #####################################################################

    /**
     * PHP发送CURL请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-09
     * @param string $url 请求地址
     * @param array $params 请求参数
     * @since efast3
     * @deprecated since version efast3
     * @return 返回请求结果
     */
    protected function makeRequest($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        /**
         * 如果参数为数组则
         */
        if (is_array($params) && 0 < count($params)) {
            $postBodyString = "";
            foreach ($params as $k => $v) {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset($k, $v);
        } else {
            $postBodyString = "";
        }

        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($postBodyString)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
        }

        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $curl_error = curl_error($ch);
            throw new runtimeException($curl_error, -10);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new runtimeException("httpStatusCode={$httpStatusCode} " . $reponse, -11);
            }
        }
        curl_close($ch);
        return $reponse;
    }

    /**
     * 执行单个API请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param string $url 请求的URL地址
     * @param array $parameters 请求参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     * @see lib/apiclient/ApiClient::exec
     * @depends lib/net/HttpClient
     */
    public function exec($url, $parameters, $type = 'post', $headers = array()) {
        $h = new HttpClient();
        $h->newHandle('0', $type, $url, $headers, $parameters);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];
    }

    /**
     * 执行批量API请求
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-12
     * @param string $url 请求的URL地址
     * @param array $parameters_array 请求参数数组, 相同地址的不同参数
     * @param string $type post or get
     * @param array $headers header信息
     * @return mixed
     * @throws Exception
     * @see lib/apiclient/ApiClient::multiExec
     * @depends lib/net/HttpClient
     */
    public function multiExec($url, $parameters_array, $type = 'post', $headers = array()) {
        $h = new HttpClient();
        foreach ($parameters_array as $key => $parameters) {
            $h->newHandle($key, $type, $url, $headers, $parameters);
        }
        $h->exec();
        return $h->responses();
    }

    /**
     * 将数组生成请求字符串
     * @auhtor jhua.zuo<jhua.zuo@baisonmail>
     * @date 2015-03-10
     * @param array $paramArr
     * @return string
     * @link http://open.taobao.com/doc/detail.htm?spm=a219a.7386781.1998343697.6.JblVVr&id=131
     */
    protected function createStrParam($paramArr) {
        $strParam = '';
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $strParam .= $key . '=' . urlencode($val) . '&';
            }
        }
        return $strParam;
    }

    /**
     * 对象转化为数组
     * @auhtor jhua.zuo<jhua.zuo@baisonmail>
     * @date 2015-03-10
     * @since efast3
     * @param object $e 待处理对象
     * @return array
     */
    protected function object_to_array($e) {
        $e = (array) $e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }

            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $e[$k] = (array) $this->object_to_array($v);
            }
        }
        return $e;
    }

    /**
     * xml转数组
     * @param $data
     * @return array
     * @throws Exception
     */
    protected function xml2array($data) {
        require_lib('util/xml_util');
        $return = array();
        xml2array($data, $return);
        return $return;
    }
    
    /**
     * 优化的json_decode方法， 保证5.3版本以下PHP能本地调试通过
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-13
     * @param string $result
     * @return array 返回json_decode以后的数组
     */
    protected function json_decode($result) {
    	if(PHP_VERSION>=5.4){
    		$return = json_decode($result, true, 512, JSON_BIGINT_AS_STRING);
    	}else{
    		$return = json_decode($result, true);
    	}
    	return $return;
    }

    /**
     * 物流回写向业务系统记录的日志(api_order_send、oms_sell_record)
     * @author jianbin.zheng
     * @date 2015-3-23
     * @param type $tid  平台交易号
     * @param type $send_log array('status'=>-1,'error_remark'=> '', 'upload_time'=>'2015-3-23 14:24:00')
     * @param type $oms_log array('is_back'=>2,'is_back_reason'=> '', 'is_back_time'=>'2015-3-23 14:24:00')
     */
    public function logistics_upload_log($tid, $send_log, $oms_log) {
       $ret = CTX()->db->update('api_order_send',$send_log, array('tid' => $tid));
       $ret2 = CTX()->db->update('oms_sell_record',$oms_log, array('deal_code' => $tid));
       
       return $ret && $ret2; 
    }
    
    /**
     * 物流回写检测拆单是否已经回写
     * @author wqian
     * @date 2015-3-23
     * @param type $tid  平台交易号
     * @param type $express_no 物流单号
     */
    public function check_is_logistics_upload($tid,$express_no) {
         $row = CTX()->db->get_row("select status from api_order_send where tid=:tid AND  express_no<>:express_no",array(':tid'=>$tid,':express_no'=>$express_no));
         $status = false;
         if(isset($row['status'])&&$row['status']==1){
             $status = TRUE;
         }
         return $status;
    }  
    
    
    
    /**
     * 商品下架预警：如果本次库存同步，在架商品一次下架30条或以上，则进行预警，本次库存不进行同步
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-04-23
     * @param string $shop_code 店铺代码
     */
    public function logistics_upload_alert($shop_code){
        $sql = "select count(*) from api_goods where 
                status = 1 
                and shop_code = :shop_code
                and goods_from_id in ( select goods_from_id from api_goods_sku where inv_num < 1 and is_allow_sync_inv = 1 ) 
                and goods_from_id not in ( select goods_from_id from api_goods_sku where inv_num > 0 and is_allow_sync_inv = 1 )";
        
        $num = CTX()->db->get_value($sql, array(':shop_code'=>$shop_code));
        if($num >= $this->delisting_alert_num){
            require_lib('net/MailEx');
			$mail = new MailEx();
			$mail->setHtml(true)->notify_mail(array(
                'wqian@baisonmail.com',
                'lin.wang@baisonmail.com',
                'yuanfei@baisonmail.com',
                'wzd@baisonmail.com',
            ), '下架预警提醒', '客户店铺代码为：'.$shop_code);
            return false;
        }else{
            return true;
        }
    }

    /**
     * 从地址内 去除掉省市区信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-05-04
     * @param string $str 待过滤的地址
     * @param array $addr  需要去掉的省市区组成的数组
     */
    protected function remove_address($str, $addr) {
        return str_replace($addr, '', $str);
    }

}
