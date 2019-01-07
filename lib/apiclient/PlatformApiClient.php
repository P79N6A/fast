<?php
require_lib('apiclient/TaobaoClient');
class PlatformApiClient
{
    private $shop_code;
    private $source;
    private $type = 1;
    function __construct($source,$shop_code) {
        $this->shop_code = $shop_code;
        $this->source = $source;
        if(strtolower($source) == "taobao"){
            $this->mdl = new TaobaoClient($shop_code);
        }
    }
    
    function analyzeFunction($fun,$data){
        if(isset($data['status']) && $data['status'] == -1){
            return $data;
        }
        $fun = $this->source.$fun;
        return $this->$fun($data);
    }
    
    function getNcmOrder($param,$type=1){
        if($type == 1){
            $data = $this->mdl->getNcmOrderTop($param);
        }else{
            $data = $this->mdl->getNcmOrderRds($param);
        }
        return $this->analyzeFunction("NcmOrder",$data);
    }
    
    function taobaoNcmOrder($data){
        $ret = array();
        foreach ($data as $v){
            $order = array();
            $detail = $v['jdp_response']['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0]['sub_purchase_orders']['sub_purchase_order'];
            
            unset($v['jdp_response']['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0]['sub_purchase_orders']);
            unset($v['jdp_response']['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0]['order_messages']);
            
            $order = $v['jdp_response']['fenxiao_orders_get_response']['purchase_orders']['purchase_order'][0];
            
            $order['receiver'] = $order['receiver']['state'].$order['receiver']['city'].$order['receiver']['district'].$order['receiver']['address'];
            $order['detail'] = $detail;
            $ret['order'][] = $order;
        }
        return $ret;
    }
    
    function updateSkuStock($param){
        return $data = $this->mdl->updateSkuStock($param);
    }
    
    function updateGoodsStock($param){
        $data = $this->mdl->updateGoodsStock($param);
        return $this->analyzeFunction("UpdateGoodsStock",$data);
    }
    
    function taobaoUpdateGoodsStock($param){
        $ret = array();
        $ret['goods_from_id'] = $param['item_quantity_update_response']['item']['iid'];
        $ret['num'] = $param['item_quantity_update_response']['item']['num'];
        return $ret;
    }
    
    function getGoods($param){
        $ret = array();
        $inv_goods = array();
        $onsale_goods = array();

        if($param['inv_goods']){
            $inv_goods = $this->mdl->getInvGoods($param);
            $inv_goods = $this->analyzeFunction("InvGoods",$inv_goods);
        }
        if($param['onsale_goods']){
            $onsale_goods = $this->mdl->getOnsaleGoods($param);
            $onsale_goods = $this->analyzeFunction("OnsaleGoods",$onsale_goods);
        }

        if(isset($onsale_goods['status']) && $onsale_goods['status'] == -1){
            return $this->analyzeFunction("Goods",$onsale_goods);
        }
        if(isset($inv_goods['status']) && $inv_goods['status'] == -1){
            return $this->analyzeFunction("Goods",$inv_goods);
        }

        if(isset($inv_goods['data']) && isset($onsale_goods['data'])
        && is_array($inv_goods['data']) && is_array($onsale_goods['data'])){
            $ret['data'] = array_merge($inv_goods['data'],$onsale_goods['data']);
        }else if(isset($onsale_goods['data']) && is_array($onsale_goods['data'])){
            $ret['data'] = $onsale_goods['data'];
        }else if(isset($inv_goods['data']) && is_array($inv_goods['data'])){
            $ret['data'] = $inv_goods['data'];
        }else{
            $ret['data'] = array();
        }
        
        $ret['inv_goods_page'] = empty($inv_goods['page'])?1:$inv_goods['page'];
        $ret['onsale_goods_page'] = empty($onsale_goods['page'])?1:$onsale_goods['page'];
        return $this->analyzeFunction("Goods",$ret);
    }
    
    function taobaoGoods($param){
        foreach ($param['data'] as &$goods){
            $taobao_goods = $this->mdl->getGoodsDetail(array("goods_from_id"=>$goods['num_iid']));
            $item = $taobao_goods['item_get_response']['item'];
            $goods['goods_from_id'] = $item['num_iid'];
            $goods['num'] = $item['num'];
            $goods['goods_name'] = $item['title'];
            $goods['goods_code'] = $item['outer_id'];
            $goods['seller_nick'] = $item['nick'];
            $goods['source'] = "taobao";
            $goods['status'] = $item['approve_status'] == "onsale" ? "1" : "0";//需要转化
            $goods['stock_type'] = isset($item['sub_stock'])?$item['sub_stock']:1;
            $goods['is_lock_inv'] = $goods['stock_type'] == "1"?0:1;
            $goods['goods_img'] = $item['pic_url'];
            $goods['price'] = $item['price'];
        }
        return $param;
    }
    
    function getTaobaoInvSoldOutGoods($param){
        $param['banner'] = "sold_out";
        $goods = $this->mdl->getInvGoods($param);
        $goods = $this->analyzeFunction("InvGoods",$goods);
        $goods['page'] = empty($goods['page'])?1:$goods['page'];
        return $this->analyzeFunction("Goods",$goods);
    }
    
    function getGoodsSkus($param){
        $data = $this->mdl->getGoodsSkus($param);
        return $this->analyzeFunction("GoodsSkus",$data);
    }
    
    function taobaoGoodsSkus($param){
        $ret = array();
        if(!isset($param['item_skus_get_response']['skus']) || !isset($param['item_skus_get_response']['skus']['sku'])){
            return $ret;
        }
        foreach ($param['item_skus_get_response']['skus']['sku'] as $sku){
            $detail = array();
            $detail['properties'] = $sku['properties'];
            $detail['with_hold_quantity'] = isset($sku['with_hold_quantity'])?$sku['with_hold_quantity']:0;
            $detail['sku_id'] = $sku['sku_id'];
            $detail['goods_barcode'] = isset($sku['outer_id'])?$sku['outer_id']:"";
            $detail['status'] = $sku['status'] == "normal" ? "0" : "1";//需要转化
            $detail['num'] = $sku['quantity'];
            $detail['price'] = $sku['price'];
            $detail['last_update_time'] = $sku['modified'];
            $detail['goods_from_id'] = $sku['num_iid'];
            $detail['source'] = "taobao";
            $ret[$sku['num_iid']][] = $detail;
        }
        return $ret;
    }
    
    function taobaoInvGoods($param){
        $ret = array();
        $ret['data'] = array();
        if(isset($param['items_inventory_get_response']['items']['item'])){
            $ret['data'] = $param['items_inventory_get_response']['items']['item'];
        }
        
        foreach ($ret['data'] as &$data){
            $data['goods_from_id'] = $data['num_iid'];
        }
 
        $ret['page'] = ceil($param['items_inventory_get_response']['total_results']/20);
        return $ret;
    }
    
    function taobaoOnsaleGoods($param){
        $ret = array();
        $ret['data'] = array();
        if(isset($param['items_onsale_get_response']['items']['item'])){
            $ret['data'] = $param['items_onsale_get_response']['items']['item'];
        }
        
        foreach ($ret['data'] as &$data){
            $data['goods_from_id'] = $data['num_iid'];
        }

        $ret['page'] = ceil($param['items_onsale_get_response']['total_results']/20);
        return $ret;
    }
    
    function taobaoGoodsDetail($param){
        $ret = array();
        $goods_detail = array();

        $item = $param['item_get_response']['item'];
        $sku_arr = isset($param['item_get_response']['item']['skus'])?$param['item_get_response']['item']['skus']:array();
        $goods['goods_from_id'] = $item['num_iid'];
        $goods['num'] = $item['num'];
        $goods['with_hold_quantity '] = $item['with_hold_quantity'];
        $goods['goods_name'] = $item['title'];
        $goods['goods_code'] = $item['outer_id'];
        $goods['seller_nick'] = $item['nick'];
        $goods['source'] = "taobao";
        $goods['status'] = $item['approve_status'] == "onsale" ? "1" : "0";//需要转化
        $goods['stock_type'] = $item['sub_stock'];
        $goods['price'] = $item['price'];
        $goods['goods_img'] = $item['pic_url'];
        
        foreach ($sku_arr as $sku){
            $detail = array();
            $detail['properties'] = $sku['properties'];
            $detail['with_hold_quantity'] = $sku['with_hold_quantity'];
            $detail['sku_id'] = $sku['sku_id'];
            $detail['goods_barcode'] = $sku['outer_id'];
            $detail['status'] = $sku['status'] == "normal" ? "0" : "1";//需要转化
            $detail['num'] = $sku['quantity'];
            $detail['price'] = $sku['price'];
            $detail['last_update_time'] = $sku['modified'];
            $goods_detail[] = $detail;
        }
        $ret["goods"] = $goods;
        $ret["detail"] = $goods_detail;
        return $ret;
    }
    
    function sendOrder($param){
        $data = $this->mdl->sendOrder($param);
        return $this->analyzeFunction("SendOrder",$data);
    }
    
    function taobaoSendOrder($param){
        $ret = array();
        if(isset($param['error_response'])){
            if($param['error_response']['sub_code'] == "isp.logistics-online-service-error:S01" 
            ||$param['error_response']['sub_code'] == "isv.invalid-parameter:seller_nick:P17" 
            ||$param['error_response']['sub_code'] == "isv.logistics-offline-service-error:B150"){
                $ret['status'] = -2;
            }else{
                $ret['status'] = -1;
            }
            $ret['message'] = $param['error_response']['msg'];
        }else{
            $ret['status'] = 1;
            $ret['message'] = "";
        }
        return $ret;
    }
    
    function getExpress(){
        $data = $this->mdl->getExpress();
        return $this->analyzeFunction("Express",$data);
    }
    
    function taobaoExpress($param){
        $param = $param['logistics_companies_get_response']['logistics_companies']['logistics_company'];
        foreach ($param as &$p){
            $p['reg'] = $p['reg_mail_no'];
            $p['source'] = "taobao";
        }
        return $param;
    }
    /**
     * 
     * Enter description here ...
     * @param unknown_type $param
     * @param unknown_type $model 0：rds模式 1：api模式
     */
    function getOrder($param,$model=0){
        if($model == 1){
           $data = $this->mdl->getOrderApi($param);
        }
        return $this->analyzeFunction("Order",$data);
    }

    function taobaoOrder($order_list){
        $ret = array();
        $ret['has_next'] = $order_list['trades_sold_increment_get_response']['has_next'];
        if(isset($order_list['trades_sold_increment_get_response']['trades']['trade'])){
            $ret['data'] = $order_list['trades_sold_increment_get_response']['trades']['trade'];
        }else{
            $ret['data'] = array();
        }
        return $ret;
    }
    
    function getOrderDetail($param){
        $data = $this->mdl->getOrderDetail($param);
        
        $data = $this->analyzeFunction("OrderDetail",$data);
        
        //计算分摊金额
        if(isset($data['order'])){
            $data['order']['detail'] = $this->get_avg_money($data['order']['agv_order_money'],$data['order']['detail']);    
        }
        
        return $data;
    }
    
    function taobaoOrderDetail($order){
        $db = $GLOBALS['context']->db;
        
        $ret = array();
        $trade = $order['trade_fullinfo_get_response']['trade'];
        
       // $ret['order']['status'] = $trade['status'];
        $ret['order']['trade_from'] = isset($trade['trade_from'])?$trade['trade_from']:"";
        $ret['order']['pay_time'] = isset($trade['pay_time'])?$trade['pay_time']:"";
        $ret['order']['pay_type'] = ($trade['type']=='cod')?1:0;
  
        $ret['order']['seller_nick'] = isset($trade['seller_nick'])?$trade['seller_nick']:"";
        $ret['order']['buyer_nick'] = isset($trade['buyer_nick'])?$trade['buyer_nick']:"";
        $ret['order']['receiver_name'] = isset($trade['receiver_name'])?$trade['receiver_name']:"";
        $ret['order']['receiver_province'] = isset($trade['receiver_state'])?$trade['receiver_state']:"";
        $ret['order']['receiver_city'] = isset($trade['receiver_city'])?$trade['receiver_city']:"";
        $ret['order']['receiver_district'] = isset($trade['receiver_district'])?$trade['receiver_district']:"";
        $ret['order']['receiver_addr'] = isset($trade['receiver_address'])?$trade['receiver_address']:"";
        $ret['order']['receiver_zip_code'] = isset($trade['receiver_zip'])?$trade['receiver_zip']:"";
        $ret['order']['receiver_mobile'] = isset($trade['receiver_mobile'])?$trade['receiver_mobile']:"";
        $ret['order']['receiver_phone'] = isset($trade['receiver_phone'])?$trade['receiver_phone']:"";
        $ret['order']['receiver_email'] = isset($trade['buyer_email'])?$trade['buyer_email']:"";
        $ret['order']['express_code'] = isset($trade['shipping_type'])?$trade['shipping_type']:"";
        
        $ret['order']['buyer_remark'] = isset($trade['buyer_message'])?$trade['buyer_message']:"";
        $ret['order']['seller_remark'] = isset($trade['seller_memo'])?$trade['seller_memo']:"";
        $ret['order']['seller_flag'] = isset($trade['seller_flag'])?$trade['seller_flag']:"";
        
        $ret['order']['order_money'] = $trade['payment'];

        $ret['order']['express_money'] = $trade['post_fee'];
        $ret['order']['delivery_money'] = $trade['buyer_cod_fee'];

        /*$ret['order']['gift_coupon_money'] = $trade['receiver_address'];
        $ret['order']['gift_money'] = $trade['receiver_address'];*/
        
        $ret['order']['buyer_money'] = $trade['payment']; //- $trade['point_fee']/100;
        $ret['order']['alipay_no'] = isset($trade['alipay_no'])? $trade['alipay_no']:'';
  
        $ret['order']['integral_change_money'] = $trade['point_fee']/100;
        /*$ret['order']['coupon_change_money'] = $trade['receiver_address'];
        $ret['order']['balance_change_money'] = $trade['receiver_address'];*/
        $ret['order']['is_lgtype'] = isset($trade['is_lgtype'])?$trade['is_lgtype']:"";
        $ret['order']['seller_rate'] = isset($trade['seller_rate'])?$trade['seller_rate']:"";
        $ret['order']['buyer_rate'] = isset($trade['buyer_rate'])?$trade['buyer_rate']:"";
        $ret['order']['invoice_type'] = isset($trade['invoice_type'])?$trade['invoice_type']:"";
        $ret['order']['invoice_title'] = isset($trade['invoice_name'])?$trade['invoice_name']:"";
        $ret['order']['order_first_insert_time'] = isset($trade['created'])?$trade['created']:"";
        $ret['order']['order_last_update_time'] = isset($trade['modified'])?$trade['modified']:"";
        $ret['order']['last_update_time'] = add_time();
        $ret['order']['is_allow_change'] = $trade['status'] == "WAIT_SELLER_SEND_GOODS" ? 1:0;
        $ret['order']['status'] = $ret['order']['is_allow_change'];
        
        //计算均摊总金额

        $cod_fee = isset($ret['order']['cod_fee'])?$ret['order']['cod_fee']:0;
        $ret['order']['agv_order_money'] = $ret['order']['order_money'] - $ret['order']['express_money'] - $cod_fee;
        /*$ret['order']['invoice_content'] = $trade['receiver_address'];
        $ret['order']['invoice_money'] = $trade['receiver_address'];
        $ret['order']['invoice_pay_type'] = $trade['receiver_address'];*/
        $count_num = 0;
        $detail_list = $order['trade_fullinfo_get_response']['trade']['orders']['order'];
        
        //插入中间表
        $trade['shop_code'] = $this->shop_code;
        $db->autoReplace("api_taobao_trade",$trade,true);
        foreach ($detail_list as $detail){
            $ret_detail = array();
            $ret_detail['source'] = "taobao";
            $ret_detail['tid'] = $trade['tid'];
            $ret_detail['oid'] = $detail["oid"];
            $ret_detail['status'] = $detail["status"];
            $ret_detail['return_status'] = isset($trade['refund_status'])?$trade['refund_status']:"";
            $ret_detail['title'] = $detail["title"];
            $ret_detail['price'] = $detail["price"];
            $ret_detail['num'] = $detail["num"];
            $ret_detail['goods_code'] = $detail["num_iid"];
            $ret_detail['sku_id'] = isset($detail["sku_id"])?$detail["sku_id"]:"";
            $ret_detail['goods_barcode'] = isset($detail["outer_sku_id"])?$detail["outer_sku_id"]:"";
            $ret_detail['total_fee'] = $detail["total_fee"];
            $ret_detail['payment'] = $detail["payment"];
            $ret_detail['discount_fee'] = $detail["discount_fee"];
            $ret_detail['adjust_fee'] = $detail["adjust_fee"];
            /*$ret_detail['avg_money'] = "taobao";*/
            $ret_detail['end_time'] = isset($detail['end_time'])?$detail['end_time']:"";
            $ret_detail['consign_time'] = isset($detail['consign_time'])?$detail['consign_time']:"";
            $ret_detail['express_code'] = isset($detail['shipping_type'])?$detail['shipping_type']:"";
            $ret_detail['express_company_name'] = isset($detail['logistics_company'])?$detail['logistics_company']:"";
            $ret_detail['express_no'] = isset($detail['invoice_no'])?$detail['invoice_no']:"";
            $ret_detail['pic_path'] = isset($detail['pic_path'])?$detail['pic_path']:"";
            $ret_detail['sku_properties'] = isset($detail['sku_properties_name'])?$detail['sku_properties_name']:"";
            $ret['order']['detail'][] = $ret_detail;
            
            $count_num += $detail["num"];
            
            $detail['ttid'] = $detail['oid'];
            $detail['tid'] =  $trade['tid'];
            $detail['shop_code'] = $this->shop_code;
            $detail['seller_nick'] = isset($trade['seller_nick'])?$trade['seller_nick']:"";
            $detail['buyer_nick'] = isset($trade['buyer_nick'])?$trade['buyer_nick']:"";
            //插入中间表详情
            $db->autoReplace("api_taobao_order",$detail,true);
        }
        $ret['order']['num'] = $count_num;
        
        return $ret;
    }
    
    function get_avg_money($order_money,$order_detail){
        $order_money = $this->format_money($order_money);
        
        $detail_count = count($order_detail);
        
        $order_detail_money = 0;//订单详情总金额
        $avg_money_count = 0;////已经被分摊的金额
        
        foreach ($order_detail as &$detail){
            $payment = $this->format_money($detail['payment']);
            $order_detail_money += $payment;
        }
    
        foreach ($order_detail as &$detail){
            $payment = $this->format_money($detail['payment']);
            if ($detail_count != 1) {
                                if($order_detail_money!=0.00){
                                    $avg_money = $order_money * ($payment / $order_detail_money);
                                    $avg_money_count += $this->format_money($avg_money);
                                }else{
                                    $avg_money = 0.00;
                                }
			} else {
				$avg_money = $order_money - $avg_money_count;
			}
			$detail['avg_money'] = $avg_money;
			$detail_count--;
        }
        return $order_detail;
    }
    
    function format_money($parameter) {
        return sprintf("%.2f", $parameter);
    }
    
    /**
     * taobao.itemcats.authorize.get 查询商家被授权品牌列表和类目列表
     * @param unknown $param
     */
    function getItemCatsAuthorize($param){
        $ret = array();
        
        $data= $this->mdl->getItemCatsAuthorize($param);
        if($data){
            $datas =$data['itemcats_authorize_get_response']['seller_authorize'];
            //$datas 里包含brands 和item_cats
            return $datas;
             
        }
    
    }
    /**
     * taobao.itemcats.get 获取后台供卖家发布商品的标准商品类目(子类目)
     * @param unknown $param
     * @return unknown
     */
    function getItemCats($param){
        $ret = array();
    
        $data= $this->mdl->getItemCats($param);
        if($data){
            $datas =$data['itemcats_get_response']['item_cats'];
            //$datas 里包含brands 和item_cats
            return $datas;
             
        }
        else{
            return '';
        }
    
    }
    /**
     * 获取子类目的属性信息
     * @param unknown $param
     * @return unknown
     */
    function getItemCatsProps($param){
        $ret = '';
    
        $data= $this->mdl->getItemCatsProps($param);
        if($data){
            if (isset($data['itemprops_get_response']['item_props'])) {
                $ret =json_encode($data['itemprops_get_response']['item_props']);
            }
        }
    
        return $ret;
    }
}