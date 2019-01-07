<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class TaobaoClient extends ApiClient implements ApiClientInterface {

    /**
     * @var string
     */
    private $appKey = '12651526';
    //沙箱测试帐号授权：sandbox_b_00
    //appkey :1023300032
    //$appSecret :sandbox345cf996ba9257bc7bd877770
    //session: 6202826c41880c4dc08004236bb651d582ZZ4cc662918062054718217
    /**
     * @var string
     */
    private $rdsDb = "sys_info";

    /**
     * @var string
     */
    private $appSecret = '11b9128693bfb83d095ad559f98f2b07';

    /**
     * @var string
     */
    private $sessionkey = '';

    /**
     * @var string
     */
    private $nick = '';

    /**
     * @var PDODB
     */
    private $db;
    private $user_id;
    private $shop_code;

    /**
     * @param string $shop_code
     */
    public function __construct($shop_code = '') {
        $this->db = $GLOBALS['context']->db;
        if (!empty($shop_code)) {
            $sql = "select * from base_shop_api where shop_code = :shop_code";
            $shop_api = $this->db->get_row($sql, array(":shop_code" => $shop_code));
            if (empty($shop_api['api'])) {
                return; // 无商店参数时不处理赋值
            }
            $this->shop_code = $shop_code;
            $api = json_decode($shop_api['api']);

            if (isset($api->app_key)) {
                $this->appKey = $api->app_key;
            }
            if (isset($api->app_secret)) {
                $this->appSecret = $api->app_secret;
            }
            if (isset($api->session)) {
                $this->sessionkey = $api->session;
            }
            if (isset($api->nick)) {
                $this->nick = $api->nick;
            }
            if (isset($api->user_id)) {
                $this->user_id = $api->user_id;
            } else {
                $this->user_id = '';
            }
        }
    }

    /**
     * 设置接口参数
     * @param type $conf
     */
    function set_api_config($conf) {
        if (isset($conf['app_key']) && !empty($conf['app_key'])) {
            $this->appKey = $conf['app_key'];
        }
        if (isset($conf['app_secret']) && !empty($conf['app_secret'])) {
            $this->appSecret = $conf['app_secret'];
        }
        if (isset($conf['session']) && !empty($conf['session'])) {
            $this->sessionkey = $conf['session'];
        }
        if (isset($conf['nick']) && !empty($conf['nick'])) {
            $this->nick = $conf['nick'];
        }
        if (isset($conf['user_id']) && !empty($conf['user_id'])) {
            $this->user_id = $conf['user_id'];
        }
    }

    //获取并保存user_id
    function get_api_user_id() {
        if (empty($this->user_id)) {
            $params['fields'] = 'user_id';
            $data = $this->taobaoUsersSellerGet($params);
            if (isset($data['user_id'])) {
                $this->user_id = $data['user_id'];
                $sql = "select api from base_shop_api where shop_code = :shop_code";
                $shop_api = $this->db->get_row($sql, array(":shop_code" => $this->shop_code));
                $api = json_decode($shop_api['api'], true);
                $api['user_id'] = $this->user_id;
                $up_data = array('api' => json_encode($api));
                $this->db->update('base_shop_api', $up_data, array('shop_code' => $this->shop_code));
            }
        }
        return $this->user_id;
    }

    function getOrderApi($param) {
        $fields = "tid";
        if (!isset($param['start_time']) || $param['start_time'] == "") {
            return $this->format_ret(-1, "", "开始时间不能为空");
        }
        if (!isset($param['end_time']) || $param['end_time'] == "") {
            return $this->format_ret(-1, "", "结束时间不能为空");
        }
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $param['start_modified'] = $param['start_time'];
        $param['end_modified'] = $param['end_time'];
        $param['fields'] = $fields;

        return $this->getTaobaoData("taobao.trades.sold.increment.get", $param);
    }

    function getOrderDetail($param) {
        $fields = "tid,status,trade_from,pay_time,seller_nick,buyer_nick,receiver_name,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,receiver_mobile,receiver_phone,buyer_email,shipping_type,receiver_address,buyer_message,seller_memo,seller_flag,payment,post_fee,buyer_cod_fee,alipay_no,point_fee,is_lgtype,seller_rate,buyer_rate,invoice_type,invoice_name,created,modified,orders";
        if (!isset($param['tid']) || $param['tid'] == "") {
            return $this->format_ret(-1, "", "tid不能为空");
        }
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        return $this->getTaobaoData(
                        "taobao.trade.fullinfo.get", array(
                    "fields" => $fields,
                    "tid" => $param['tid']
                        )
        );
    }

    function getSeller() {
        return $this->getTaobaoData(
                        "taobao.user.seller.get", array(
                    "fields" => "nick,type,user_id"
                        )
        );
    }

    function getExpress() {
        return $this->getTaobaoData(
                        "taobao.logistics.companies.get", array(
                    "fields" => "id,code,name,reg_mail_no"
                        )
        );
    }

    function sendOrder($param) {
        $taobao_param = array();
        $taobao_param['tid'] = $param['tid'];
        $taobao_param['out_sid'] = $param['express_no'];
        $taobao_param['company_code'] = $param['company_code'];
        if (isset($param['is_split'])) {
            $taobao_param['is_split'] = $param['is_split'];
        }
        if (isset($param['sub_tid'])) {
            $taobao_param['sub_tid'] = $param['sub_tid'];
        }
        return $this->getTaobaoData("taobao.logistics.offline.send", $taobao_param);
    }

    function getInvGoods($param) {
        $taobao_param = array();
        $fields = "num_iid";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        if (isset($param['start_time'])) {
            $taobao_param['start_modified'] = $param['start_time'];
        }
        if (isset($param['end_time'])) {
            $taobao_param['end_modified'] = $param['end_time'];
        }
        if (isset($param['banner'])) {
            $taobao_param['banner'] = $param['banner'];
        }
        if (isset($param['page_size'])) {
            $taobao_param['page_size'] = $param['page_size'];
        }

        if (isset($param['page'])) {
            $taobao_param['page_no'] = $param['page'];
        }


        return $this->getTaobaoData("taobao.items.inventory.get", $taobao_param);
    }

    function getOnsaleGoods($param) {
        $taobao_param = array();
        $fields = "num_iid";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        if (isset($param['start_time'])) {
            $taobao_param['start_modified'] = $param['start_time'];
        }
        if (isset($param['end_time'])) {
            $taobao_param['end_modified'] = $param['end_time'];
        }
        if (isset($param['page_size'])) {
            $taobao_param['page_size'] = $param['page_size'];
        }
        if (isset($param['page'])) {
            $taobao_param['page_no'] = $param['page'];
        }
        return $this->getTaobaoData("taobao.items.onsale.get", $taobao_param);
    }

    function getGoodsDetail($param) {
        $taobao_param = array();

        $fields = "num_iid,num,with_hold_quantity,title,outer_id,nick,approve_status,sub_stock,price,pic_url";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        $taobao_param['num_iid'] = $param['goods_from_id'];

        return $this->getTaobaoData("taobao.item.get", $taobao_param);
    }

    function getGoodsSkus($param) {
        $taobao_param = array();
        $goods_id_str = "";
        $taobao_param['fields'] = "properties_name,sku_spec_id,with_hold_quantity,sku_delivery_time,change_prop,outer_id,barcode,sku_id,iid,num_iid,properties,quantity,price,created,modified,status";

        foreach ($param['goods_list'] as $goods) {
            $goods_id_str .= $goods['goods_from_id'] . ",";
        }
        $goods_id_str = substr($goods_id_str, 0, strlen($goods_id_str) - 1);
        $taobao_param['num_iids'] = $goods_id_str;
        return $this->getTaobaoData("taobao.item.skus.get", $taobao_param);
    }

    function updateSkuStock($param) {
        $skuid_quantities = "";
        $sku_list = $param['sku_list'];
        foreach ($sku_list as $sku) {
            $skuid_quantities .= $sku['sku_id'] . ":" . $sku['inv_num'] . ";";
        }
        return $this->getTaobaoData(
                        "taobao.skus.quantity.update", array(
                    "num_iid" => $param['goods_from_id'],
                    "skuid_quantities" => $skuid_quantities,
                        )
        );
    }

    function updateGoodsStock($param) {
        return $this->getTaobaoData(
                        "taobao.item.quantity.update", array(
                    "num_iid" => $param['goods_from_id'],
                    "quantity" => $param['num'] == 0 ? "0" : $param['num'],
                        )
        );
    }

    function getNcmOrderRds($param) {
        $rdsDb = $this->rdsDb;
        $num = 100;
        $page = 1;
        $this->mydb = $GLOBALS['context']->db;
        $sql = "select * from " . $rdsDb . ".jdp_fx_trade where supplier_username = '" . $this->nick . "'";
        if (isset($param['start_time'])) {
            $sql .= " and modified >= '" . $param['start_time'] . "'";
        }
        if (isset($param['end_time'])) {
            $sql .= " and modified <= '" . $param['end_time'] . "'";
        }
        if (isset($param['page'])) {
            $page = $param['page'];
        }
        $data = $this->mydb->get_limit($sql, NULL, $num, ($page - 1) * $num);
        return $this->getTaobaoDataRds($data);
    }

    /**
     * 获取电子面单号
     * {"province":"浙江省","city":"杭州市","area":"余杭区","division_id":"330110","address_detail":"文一西路969号"}
     *
     * @param $cp_code
     * @param array $shipping_address
     * @param $trade_order_info_cols
     * @return mixed
     */
    function wlbWaybillGet($cp_code, $shipping_address, $trade_order_info_cols) {
        return $this->getTaobaoData(
                        "taobao.wlb.waybill.get", array(
                    "cp_code" => strtoupper($cp_code),
                    "shipping_address" => urldecode(json_encode($shipping_address)),
                    "trade_order_info_cols" => urldecode(json_encode($trade_order_info_cols)),
                        )
        );
    }

    /**
     * 查询面单服务订购及面单使用情况
     * @param $cp_code 物流公司CODE
     * @return mixed
     */
    function wlbWaybillSearch($cp_code) {
        return $this->getTaobaoData("cainiao.waybill.ii.search", array("cp_code" => strtoupper($cp_code)));
    }

    /**
     * 电子面单云打印
     * {{"city":"杭州市","detail":"文一西路969号","district":"余杭区","province":"浙江省","division_id":"330110",}"mobile":"139xxxxxxxx", "name":"xxx", "phone":"xxx"}
     *
     * @param $cp_code 物流公司CODE
     * @param $sender 发货信息
     * @param $trade_order_info_dtos
     * @return mixed
     */
    function cloudWlbWaybillPrint($params) {
        foreach ((array) $params as $_key => $_value) {
            $_params = array();
            $_consignee_address = array();
            $_trade_order_info_dtos = array();
            //物流公司Code
            if (isset($_value['express_code'])) {
                //安能物流特殊处理
                $cp_code = strtoupper($_value['express_code']);
                $_params['cp_code'] = ($cp_code == 'ANE') ? '2608021499_235' : $cp_code;
            }
            //产品类型编码
            if ($_value['pay_type'] == 'cod') {
                //货到付款
                $_params['product_code'] = 'COD';
                $_trade_order_info_dtos['logistics_services'] = json_encode(array('SVC-COD' => array('value' => number_format($_value['payable_money'], 2, '.', ''))));
            }
            /* ---------------------------------------------------------- */
            /* ---------------sender(发货人信息)---START------------------ */
            /* ---------------------------------------------------------- */
            //发货人地址信息
            if (isset($_value['sender_province_name'])) {
                $sender['province'] = $_value['sender_province_name'];
            }
            if (isset($_value['sender_city_name'])) {
                $sender['city'] = $_value['sender_city_name'];
            }
            if (isset($_value['sender_district_name'])) {
                $sender['district'] = $_value['sender_district_name'];
            }
            if (isset($_value['sender_street_name']) && !empty($_value['sender_street_name'])) {
                $sender['town'] = $_value['sender_street_name'];
            }
            if (isset($_value['sender_addr'])) {
                $sender['detail'] = $_value['sender_addr'];
            }
            //发货地址
            if (!empty($sender)) {
                $_params['sender']['address'] = $sender;
            }
            //姓名
            if (isset($_value['sender_name'])) {
                $_params['sender']['name'] = $_value['sender_name'];
            }
            //手机号码
            if (isset($_value['sender_phone'])) {
                $_params['sender']['phone'] = $_value['sender_phone'];
            }

            /* ---------------------------------------------------------- */
            /* ---------------sender(发货人信息)---END-------------------- */
            /* ---------------------------------------------------------- */

            /* ================================================================ */
            /* -----trade_order_info_dtos(请求面单信息，数量限制为10)---START----- */
            /* ================================================================ */

            //请求ID，要保证唯一
            $_trade_order_info_dtos['object_id'] = date('YmdHis') . rand(00000, 99999);

            //订单信息
            if (isset($_value['order_channels_type'])) {
                $_trade_order_info_dtos['order_info']['order_channels_type'] = $_value['order_channels_type'];
            } else {
                $_trade_order_info_dtos['order_info']['order_channels_type'] = 'TB';
            }
            $_trade_order_info_dtos['order_info']['trade_order_list'] = $_value['sell_record_code'];
            if (isset($_value['deal_code_list']) && $_value['sale_channel_code'] == 'taobao') {
                $deal_code_list_arr = explode(",", $_value['deal_code_list']);
                $deal_code_list_arr = array_values(array_unique($deal_code_list_arr));
                foreach ($deal_code_list_arr as &$deal_code) {
                    $deal_code = (string) $deal_code;
                }
                $_trade_order_info_dtos['order_info']['trade_order_list'] = $deal_code_list_arr;
            }
            /* ================================================================= */
            /* --------trade_order_info_dtos(请求面单信息，数量限制为10)---END----- */
            /* ================================================================= */


            /* ----------------------------------------------------------------- */
            /* -----package_info(包裹信息)---END----- */
            /* ----------------------------------------------------------------- */

            if (empty($_value['package_no'])) {
                $_trade_order_info_dtos['package_info']['id'] = $_value['sell_record_code'];
            } else {
                $_trade_order_info_dtos['package_info']['id'] = $_value['sell_record_code'] . '-' . $_value['package_no'];
            }
            if (isset($_value['goods_list'])) {
                $package_items = array();
                foreach ($_value['goods_list'] as $val) {
                    $package_items[] = array('name' => $val['goods_name'], 'count' => $val['num']);
                }
                $_trade_order_info_dtos['package_info']['items'] = $package_items;
            }

            /* ----------------------------------------------------------------- */
            /* -----package_info(包裹信息)---END----- */
            /* ----------------------------------------------------------------- */

            /* ----------------------------------------------------------------- */
            /* -----------------recipient(收件人信息)---START-------------------- */
            /* ----------------------------------------------------------------- */
            //收货人信息
            if (isset($_value['receiver_province_name'])) {
                $_consignee_address['province'] = $_value['receiver_province_name'];
            }
            if (isset($_value['receiver_city_name'])) {
                $_consignee_address['city'] = $_value['receiver_city_name'];
            }
            if (isset($_value['receiver_district_name'])) {
                $_consignee_address['district'] = $_value['receiver_district_name'];
            }
            if (isset($_value['receiver_street_name'])) {
                $_consignee_address['town'] = $_value['receiver_street_name'];
            }
            if (isset($_value['receiver_addr'])) {
                $_consignee_address['detail'] = $_value['receiver_addr'];
            }
            if (empty($_consignee_address['detail'])) {
                $_consignee_address['detail'] = $_value['receiver_street_name'];
            }
            //收货人地址信息
            $_trade_order_info_dtos['recipient']['address'] = $_consignee_address;
            //收货人姓名
            if (isset($_value['receiver_name'])) {
                $_trade_order_info_dtos['recipient']['name'] = $_value['receiver_name'];
            }
            //收货人手机号码
            if (isset($_value['receiver_mobile'])) {
                $_trade_order_info_dtos['recipient']['mobile'] = $_value['receiver_mobile'];
            }

            /* ----------------------------------------------------------------- */
            /* -----------------recipient(收件人信息)---START-------------------- */
            /* ----------------------------------------------------------------- */


            //标准模版url
            $_trade_order_info_dtos['template_url'] = $_value['template_url'];
            //使用者ID
            $_trade_order_info_dtos['user_id'] = $this->get_api_user_id();

            $_params['trade_order_info_dtos'] = $_trade_order_info_dtos;
            $outer_params['param_waybill_cloud_print_apply_new_request'] = json_encode($_params);
            $handles[$_key] = $this->newHandle('cainiao.waybill.ii.get ', $outer_params);
        }
        $re = $this->multiExec($handles);
        $result = array();
        foreach ((array) $params as $_key => $_value) {
            if (isset($re[$_key])) {
                $r = $this->jsonDecode($re[$_key]);
                if ($r == null) {
                    $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $re[$_key]);
                } else {
                    if (isset($r['cainiao_waybill_ii_get_response']['modules']['waybill_cloud_print_response'])) {
                        $result[$_key] = array('status' => '1', 'data' => $r['cainiao_waybill_ii_get_response']['modules']['waybill_cloud_print_response'], 'message' => '');
                    } else if (isset($r['error_response']['sub_msg'])) {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['sub_msg']);
                    } else if (isset($r['error_response']['msg'])) {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['msg']);
                    } else {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $re[$_key]);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 取消获取的电子面单号
     * @param $cp_code 物流公司CODE
     * @param $waybill_code 面单号
     * @return boolean
     */
    function cloudWlbWaybillCancel($cp_code, $waybill_code) {
        $m = 'cainiao.waybill.ii.cancel';
        $outer_param = array('cp_code' => $cp_code, 'waybill_code' => $waybill_code);
        $data = $this->getTaobaoData($m, $outer_param);
        if (isset($data['cainiao_waybill_ii_cancel_response'])) {
            return $data['cainiao_waybill_ii_cancel_response'];
        }
        return $data;
    }

    /**
     * 获取全部的菜鸟电子面单标准模板信息
     */
    function cloudPrintStdtemplatesGet() {
        return $this->getTaobaoData("cainiao.cloudprint.stdtemplates.get", array());
    }

    /**
     * 获取用户使用的菜鸟电子面单模板信息
     */
    function cloudPrintMyStdtemplatesGet() {
        return $this->getTaobaoData("cainiao.cloudprint.mystdtemplates.get", array());
    }

    /**
     * 获取用户自定义区域的菜鸟电子面单模板信息
     * @param int $template_id 用户使用的标准模板id
     */
    function cloudPrintCustomaresGet($template_id) {
        return $this->getTaobaoData("cainiao.cloudprint.customares.get", array('template_id' => $template_id));
    }

    /**
     * taobao.itemcats.authorize.get 查询商家被授权品牌列表和类目列表
     * @param unknown $param
     * @return mixed
     */
    function getItemCatsAuthorize($param) {
        $taobao_param = array();
        $fields = "brand.vid, brand.name, item_cat.cid, item_cat.name, item_cat.status,item_cat.sort_order,item_cat.parent_cid,item_cat.is_parent, xinpin_item_cat.cid, xinpin_item_cat.name, xinpin_item_cat.status, xinpin_item_cat.sort_order, xinpin_item_cat.parent_cid, xinpin_item_cat.is_parent";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        if (isset($param['start_time'])) {
            $taobao_param['start_modified'] = $param['start_time'];
        }
        if (isset($param['end_time'])) {
            $taobao_param['end_modified'] = $param['end_time'];
        }
        return $this->getTaobaoData("taobao.itemcats.authorize.get", $taobao_param);
    }

    /**
     * 获取后台供卖家发布商品的标准商品类目,获取子节点类目信息
     * @param unknown $param
     * @return Ambigous <string, multitype:NULL unknown >
     */
    function getItemCats($param) {
        $taobao_param = array();
        $fields = "cid,parent_cid,name,is_parent";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        if (isset($param['start_time'])) {
            $taobao_param['start_modified'] = $param['start_time'];
        }
        if (isset($param['end_time'])) {
            $taobao_param['end_modified'] = $param['end_time'];
        }

        if (isset($param['cids'])) {
            $taobao_param['cids'] = $param['cids'];
        }
        if (isset($param['parent_cid'])) {
            $taobao_param['parent_cid'] = $param['parent_cid'];
        }

        return $this->getTaobaoData("taobao.itemcats.get", $taobao_param);
    }

    function getItemCatsProps($param) {
        $taobao_param = array();
        $fields = "pid,name,must,multi,prop_values,is_key_prop,is_sale_prop,is_color_prop,is_enum_prop,is_item_prop,must";
        if (isset($param['fields'])) {
            $fields = $param['fields'];
        }
        $taobao_param['fields'] = $fields;

        if (isset($param['start_time'])) {
            $taobao_param['start_modified'] = $param['start_time'];
        }
        if (isset($param['end_time'])) {
            $taobao_param['end_modified'] = $param['end_time'];
        }
        if (isset($param['cid'])) {
            $taobao_param['cid'] = $param['cid'];
        }
        if (isset($param['type'])) {
            $taobao_param['type'] = $param['type'];
        }

        return $this->getTaobaoData("taobao.itemprops.get", $taobao_param);
    }

    /**
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {
        $arr = $this->createArrayParam($parameters);
        $arr['method'] = $apiName;
        $arr['sign'] = $this->createSign($this->appSecret, $arr);

        $handle = array();
        $handle['type'] = "post";
        $_dev = CTX()->get_app_conf('api_dev_mode');
        if (empty($_dev) || $_dev == 1) {
            $handle['url'] = "http://gw.api.taobao.com/router/rest";
        } else if ($_dev == 2) {
            $handle['url'] = "http://223.4.54.191/taobao_trans_req.php";
        } else if ($_dev == 3) {
            $handle['url'] = "http://gw.api.tbsandbox.com/router/rest";
        }
        //$handle['url'] = "http://gw.api.tbsandbox.com/router/rest";
        $https_method_arr = array(
            'alibaba.einvoice.createreq',
            'alibaba.einvoice.create.result.get',
            'alibaba.einvoice.create.results.increment.get',
        );
        if(in_array($apiName,$https_method_arr)){
              $handle['url'] = "https://eco.taobao.com/router/rest";
        }
        
        $handle['body'] = $arr;
        return $handle;
    }

    function getTaobaoDataRds($data) {
        foreach ($data as &$v) {
            $v['jdp_response'] = preg_replace('/([^\\\])(":)(\d{9,})/i', '${1}${2}"${3}"', $v['jdp_response']);
            $v['jdp_response'] = object_to_array(json_decode($v['jdp_response']));
        }
        return $data;
    }

    /**
     * @param $api
     * @param $param
     * @return mixed
     * @throws Exception
     */
    function getTaobaoData($api, $param) {
        $p = $this->exec($api, $param);
        // $p='{"wlb_waybill_i_fullupdate_response":{"waybill_apply_update_info":{"short_address":"021","trade_order_info":{"consignee_address":{"address_detail":"302室","area":"奉贤区","city":"上海市","province":"上海"},"consignee_name":"周芳","consignee_phone":"15839967422","order_channels_type":"TB","package_id":"-1","package_items":{"package_item":[{"count":1,"item_name":"花朵珍珠耳钉[白色,M]"}]},"product_type":"STANDARD_EXPRESS","real_user_id":875981563,"trade_order_list":{"string":["20150731002"]},"volume":0,"weight":0},"waybill_code":"889043740026"},"request_id":"5rg9p82pavh5"}}';
        //$p = '{"wlb_waybill_i_cancel_response":{"cancel_result":true,"request_id":"5rg9p9kf4zve"}}';
        $p = preg_replace('/([^\\\])(":)(\d{9,})/i', '${1}${2}"${3}"', $p);
        $data = object_to_array(json_decode($p));

        if (isset($data['error_response'])) {
            $data['status'] = -1;
            $data['message'] = $data['error_response']['code'] . ":" . $data['error_response']['msg'];
            if (isset($data['error_response']['sub_msg'])) {
                $data['message'] .= $data['error_response']['sub_msg'];
            }
        }
        return $data;
    }

    /**
     * 合并系统参数
     * @param $param
     * @return array
     */
    function createArrayParam($param) {
        $paramArr = array(
            'app_key' => $this->appKey,
            'session' => $this->sessionkey,
            'format' => 'json',
            'v' => '2.0',
            'sign_method' => 'md5',
            'timestamp' => date('Y-m-d H:i:s')
        );
        if (empty($paramArr['session'])) {
            unset($paramArr['session']);
        }
        return array_merge($paramArr, $param);
    }

    /**
     * 签名函数
     * @param $appSecret
     * @param $paramArr
     * @return string
     */
    function createSign($appSecret, $paramArr) {
        $sign = $appSecret;
        ksort($paramArr);

        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . $val;
            }
        }

        $sign .= $appSecret;
        $sign = strtoupper(md5($sign));
        return $sign;
    }

    /**
     * 获取物流服务商电子面单号
     * @param array $params
     * @return array
     */
    function multiWlbWaybillGet($params = array()) {
        $handles = array();
        foreach ((array) $params as $_key => $_value) {
            $_params = array();
            if (isset($_value['express_code'])) {//安能物流特殊处理
                $cp_code = strtoupper($_value['company_express_code']);
                $_params['cp_code'] = ($cp_code == 'ANE') ? '2608021499_235' : $cp_code;
            }

            if (isset($_value['sender_province_name'])) {
                $sender['province'] = $_value['sender_province_name'];
            }
            if (isset($_value['sender_city_name'])) {
                $sender['city'] = $_value['sender_city_name'];
            }
            if (isset($_value['sender_district_name'])) {
                $sender['area'] = $_value['sender_district_name'];
            }
            if (isset($_value['sender_street_name']) && !empty($_value['sender_street_name'])) {
                $sender['town'] = $_value['sender_street_name'];
            }
            if (isset($_value['sender_addr'])) {
                $sender['address_detail'] = $_value['sender_addr'];
            }

            if (!empty($sender)) {
                // $_params['shipping_address'] = urldecode(json_encode($sender));
                $_params['shipping_address'] = $sender;
            }


            $_consignee_address = $_trade_order_info_cols = array();
            if (isset($_value['receiver_province_name'])) {
                $_consignee_address['province'] = $_value['receiver_province_name'];
            }
            if (isset($_value['receiver_city_name'])) {
                $_consignee_address['city'] = $_value['receiver_city_name'];
            }
            if (isset($_value['receiver_district_name'])) {
                $_consignee_address['area'] = $_value['receiver_district_name'];
            }
            if (isset($_value['receiver_street_name'])) {
                $_consignee_address['town'] = $_value['receiver_street_name'];
            }
            // var_dump($_value['receiver_addr']);die;
            if (isset($_value['receiver_addr'])) {
                $_consignee_address['address_detail'] = $_value['receiver_addr'];
            }

            if (empty($_consignee_address['address_detail'])) {
                $_consignee_address['address_detail'] = $_value['receiver_street_name'];
            }


            $_trade_order_info_cols['consignee_address'] = $_consignee_address;


            if (isset($_value['receiver_name'])) {
                $_trade_order_info_cols['consignee_name'] = $_value['receiver_name'];
            }
            if (isset($_value['receiver_tel'])) {
                $_trade_order_info_cols['consignee_phone'] = $_value['receiver_tel'];
            }

            $_trade_order_info_cols['trade_order_list'] = $_value['sell_record_code'];

            if (isset($_value['deal_code_list']) && $_value['sale_channel_code'] == 'taobao') {
                $deal_code_list_arr = explode(",", $_value['deal_code_list']);
                $deal_code_list_arr = array_values(array_unique($deal_code_list_arr));

                foreach ($deal_code_list_arr as &$deal_code) {
                    $deal_code = (string) $deal_code;
                }
                $_trade_order_info_cols['trade_order_list'] = $deal_code_list_arr;
            }


            $_trade_order_info_cols['product_type'] = "STANDARD_EXPRESS"; //后续需要改造

            $_trade_order_info_cols['real_user_id'] = $this->get_api_user_id();
            // send_phone
            //send_name
            //weight
            if (isset($_value['order_channels_type'])) {
                $_trade_order_info_cols['order_channels_type'] = $_value['order_channels_type'];
            } else {
                $_trade_order_info_cols['order_channels_type'] = 'TB';
            }
            if (isset($_value['goods_list'])) {
                //$_trade_order_info_cols['item_name'] = $_value['goods_name'];
                $package_items = array();
                foreach ($_value['goods_list'] as $val) {
                    $package_items[] = array('item_name' => $val['goods_name'], 'count' => $val['num']);
                }
                $_trade_order_info_cols['package_items'] = $package_items;
            }


            if (empty($_value['package_no'])) {
                $_trade_order_info_cols['package_id'] = $_value['sell_record_code'];
            } else {
                $_trade_order_info_cols['package_id'] = $_value['sell_record_code'] . '-' . $_value['package_no'];
            }

            $service_list = array();
            if ($_value['pay_type'] == 'cod' && $_value['company_express_code'] != 'SF') {
                //货到付款
                $service = array(
                    'service_code' => 'SVC-COD',
                    'service_value4_json' => json_encode(array(
                        'value' => number_format($_value['payable_money'], 2, '.', ''),
                        'currency' => "CNY",
                        'payment_type' => 'CASH',
                    )),
                );
                $service_list[] = $service;
            }
            //服务
            if (!empty($service_list)) {
                $_trade_order_info_cols['logistics_service_list'] = $service_list;
            }
            $_params['trade_order_info_cols'] = $_trade_order_info_cols;


            //var_dump($_params);die;
            //
            //
            //    if(!empty($_trade_order_info_cols)){
            // $_params['trade_order_info_cols'] = urldecode(json_encode($_trade_order_info_cols));
//print_r($_params);die;
            $outer_param['waybill_apply_new_request'] = json_encode($_params);

            //outer_param
            //   }

            /* $_params = array(
              'cp_code' => 'YTO',
              'shipping_address' => urldecode(json_encode(array(
              'province' => '广东省',
              'city' => '深圳市',
              'area' => '福田区',
              'address_detail' => '车公庙泰然九路红松大厦B座7楼',
              ))),
              'trade_order_info_cols' => urldecode(json_encode(array(array(
              'consignee_address' => array(
              'province' => '广东省',
              'city' => '深圳市',
              'area' => '福田区',
              'address_detail' => '车公庙泰然九路红松大厦B座7楼',
              ),
              'consignee_name' => '廖召贵',
              'consignee_phone' => '13698789898',
              'trade_order_list' => array('11111-guid1422514151'),
              'order_channels_type'=>'TB',
              'item_name'=>'小西装',
              )))),
              ); */

            // $handles[$_key] = $this->newHandle('taobao.wlb.waybill.get', $_params);
            //
            //测试返回提交
            //  var_dump($outer_param);die;
            $handles[$_key] = $this->newHandle('taobao.wlb.waybill.i.get', $outer_param);
        }

        //multi请求
        $re = $this->multiExec($handles);

        //测试返回
        //$re[$_key] = '{"wlb_waybill_i_get_response":{"waybill_apply_new_cols":{"waybill_apply_new_info":[{"print_config":"c2lnbmI3OWVlZGU0ZjU0MzE1MmYxZDQ3MWNiNDgwZGI2ZjU0JnsiS0VGX05BTUUiOiJhbGlfd2F5YmlsbF93YXliaWxsX2NvZGUiLCJLRUZfVkFMVUUiOiI4ODkwNDM3NDAwMjYiLCJ2ZXJzaW9uIjoxfQ==","shipping_branch_code":"SF","short_address":"021","trade_order_info":{"consignee_address":{"address_detail":"302室","area":"奉贤区","city":"上海市","province":"上海","town":"上海海港综合经济开发区"},"consignee_name":"周芳","consignee_phone":"15839967422","order_channels_type":"TB","package_id":"-1","package_items":{"package_item":[{"count":1,"item_name":"花朵珍珠耳钉[白色,M]"}]},"product_type":"STANDARD_EXPRESS","real_user_id":875981563,"trade_order_list":{"string":["20150731002"]},"volume":0,"weight":0},"waybill_code":"889043740026"}]},"request_id":"5rg9p7z9pjs7"}}';
        $result = array();
        foreach ((array) $params as $_key => $_value) {
            if (isset($re[$_key])) {
                $r = $this->jsonDecode($re[$_key]);
                if ($r == null) { // decode fail.
                    $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $re[$_key]);
                } else {
                    // if(isset($r['wlb_waybill_get_response']['results']['waybill_apply_new_info'])){

                    if (isset($r['wlb_waybill_i_get_response']['waybill_apply_new_cols']['waybill_apply_new_info'])) {
                        //  $ret3['results']['waybill_apply_new_info'][0] = $ret['waybill_apply_new_cols']['waybill_apply_new_info'][0]
                        //$result[$_key] = array('status'=>'1', 'data'=>$r['wlb_waybill_get_response']['results']['waybill_apply_new_info'], 'message'=>'');
                        $result[$_key] = array('status' => '1', 'data' => $r['wlb_waybill_i_get_response']['waybill_apply_new_cols']['waybill_apply_new_info'], 'message' => '');
                    } else if (isset($r['error_response']['sub_msg'])) {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['sub_msg']);
                    } else if (isset($r['error_response']['msg'])) {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['msg']);
                    } else {
                        $result[$_key] = array('status' => '-1', 'data' => '', 'message' => $re[$_key]);
                    }
                }
            }
        }
        // var_dump($result);die;
        return $result;
    }

    /**
     * 打印提交验证
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.4.ztSzUK&path=cid:20647-apiId:23872
     * @author wq  2015-09-07 下午1:27:56
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoWlbWaybillIPrint($param) {
        $m = 'taobao.wlb.waybill.i.print';
        $outer_param['waybill_apply_print_check_request'] = json_encode($param);

        $data = $this->getTaobaoData($m, $outer_param);


        $data = $this->getTaobaoData($m, $outer_param);

        // '{"wlb_waybill_i_print_response":{"waybill_apply_print_check_infos":{"waybill_apply_print_check_info":[{"print_quantity":1,"waybill_code":"889043740026"}]},"request_id":"5rg9p1jqi36c"}}';
        //需要测试检查
        if (isset($data['wlb_waybill_i_print_response']['waybill_apply_print_check_infos']['waybill_apply_print_check_info'])) {
            return $data['wlb_waybill_i_print_response']['waybill_apply_print_check_infos']['waybill_apply_print_check_info'];
        }
        return $data;
    }

    /**
     * 更新面单信息
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.3.dkClDc&path=cid:20647-apiId:23871
     * @author wq  2015-09-07 下午1:27:56
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoWlbWaybillIFullupdate($param) {
        $m = 'taobao.wlb.waybill.i.fullupdate';
        $outer_param['waybill_apply_full_update_request'] = json_encode($param);

        $data = $this->getTaobaoData($m, $outer_param);


        //    var_export($data);die;


        if (isset($data['wlb_waybill_i_fullupdate_response']['waybill_apply_update_info'])) {
            return $data['wlb_waybill_i_fullupdate_response']['waybill_apply_update_info'];
        }
        return $data;
    }

    /**
     * 打印单据取消
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.6.us3LQl&path=cid:20647-apiId:23874
     * @author wq  2015-09-07 下午1:27:56
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoWlbWaybillICancel($param) {
        $m = 'taobao.wlb.waybill.i.cancel';
        $outer_param['waybill_apply_cancel_request'] = json_encode($param);

        $data = $this->getTaobaoData($m, $outer_param);
        if (isset($data['wlb_waybill_i_cancel_response'])) {
            return $data['wlb_waybill_i_cancel_response'];
        }
        return $data;
    }

    /**
     * 获取产品匹配的规则
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.SyubRo&path=cid:4-apiId:23258
     * @author wzd  2015-2-7 下午1:27:56
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductMatchSchemaGet($params) {
        $m = 'tmall.product.match.schema.get';
        $opts = array(
            "category_id" => $params['category_id']
        );
        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 产品匹配接口
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.mvtLC3&path=cid:4-apiId:23259
     * @author wzd  2015-2-7 下午1:27:46
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductSchemaMatch($params) {
        $m = 'tmall.product.schema.match';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'propvalues'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 查询产品状态
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.ACkAnM&path=cid:4-apiId:23565
     * @author wzd  2015-2-7 下午1:27:40
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductSchemaGet($params) {
        $m = 'tmall.product.schema.get';
        $opts = array(
            "product_id" => $params['product_id']
        );
        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 获取产品发布涉及的规则
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.l3jInv&path=cid:4-apiId:23257
     * @author wzd  2015-2-7 下午1:27:31
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductAddSchemaGet($params) {
        $m = 'tmall.product.add.schema.get';
        $opts = array(
            "category_id" => $params['category_id']
        );
        $this->_merge_opts($opts, $params, array('brand_id'));
        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 发布产品
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.VRURCk&path=cid:4-apiId:23260
     * @author wzd  2015-2-7 下午1:27:23
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductSchemaAdd($params) {
        $m = 'tmall.product.schema.add';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'xml_data'));
        $this->_merge_opts($opts, $params, array('brand_id '));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 天猫发布商品规则获取
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.2.cIwm8h&path=scopeId:11430-apiId:23256
     * @author wzd  2015-2-7 下午1:27:15
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemAddSchemaGet($params) {
        $m = ' tmall.item.add.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'product_id'));
        $this->_merge_opts($opts, $params, array('type', 'isv_init'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'tmall_item_add_schema_get_response', 'add_item_result');
    }

    /**
     * 天猫根据规则发布商品
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.SXu3wo&path=cid:4-apiId:23255
     * @author wzd  2015-2-7 下午1:26:29
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemSchemaAdd($params) {
        $m = ' tmall.item.schema.add';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'product_id', 'xml_data'));

        $ret = $this->getTaobaoData($m, $opts);
        return $this->_unwrap($ret, 'tmall_item_schema_add_response', 'add_item_result');
    }

    /**
     * 产品更新规则获取接口
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.11.I10J2L&path=scopeId:11430-apiId:23432
     * @author wzd  2015-2-10 上午9:50:17
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductUpdateSchemaGet($params) {
        $m = 'tmall.product.update.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'product_id'));
        $this->_merge_opts($opts, $params, array('type', 'isv_init'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     *  产品更新接口
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.12.I10J2L&path=scopeId:11430-apiId:23433
     * @author wzd  2015-2-10 上午10:01:37
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallProductSchemaUpdate($params) {
        $m = 'tmall.product.schema.update';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'xml_data'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 天猫根据规则编辑商品
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.13.sdrw37&path=scopeId:11430-apiId:23434
     * @author wzd  2015-2-10 上午10:05:59
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemSchemaUpdate($params) {
        $m = 'tmall.item.schema.update';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id', 'xml_data'));
        $this->_merge_opts($opts, $params, array('category_id', 'product_id'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'tmall_item_schema_update_response', 'update_item_result');
    }

    /**
     * 天猫编辑商品规则获取
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.14.sdrw37&path=scopeId:11430-apiId:23435
     * @author wzd  2015-2-10 上午10:07:23
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemUpdateSchemaGet($params) {
        $m = 'tmall.item.update.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id'));
        $this->_merge_opts($opts, $params, array('category_id', 'product_id'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'tmall_item_update_schema_get_response', 'update_item_result');
    }

    /**
     * 天猫增量更新商品规则获取
     * @see http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.iN0rIX&apiId=24365
     * @author wzd  2016-5-12
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemIncrementUpdateSchemaGet($params) {
        $m = 'tmall.item.increment.update.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id'));
        $this->_merge_opts($opts, $params, array('xml_data'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'tmall_item_increment_update_schema_get_response', 'wholesale_category_result');
    }

    private function _unwrap($data, $index0, $index1 = NULL, $index2 = NULL) {
        if (!isset($data[$index0])) {
            return $data[$index0];
        }
        if ($index1 != NULL && isset($data[$index0][$index1])) {
            $_ret = $data[$index0][$index1];
            if ($index2 != NULL && isset($_ret[$index2])) {
                $_ret = $_ret[$index2];
            }

            return $_ret;
        }

        return array();
    }

    /**
     * 天猫根据规则增量更新商品
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.17.sdrw37&path=scopeId:11430-apiId:23782
     * @author wzd  2015-2-10 上午10:09:36
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function tmallItemSchemaIncrementUpdate($params) {
        $m = 'tmall.item.schema.increment.update';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id'));
        $this->_merge_opts($opts, $params, array('xml_data'));

        $ret = $this->getTaobaoData($m, $opts);
        //
        return $this->_unwrap($ret, 'tmall_item_schema_increment_update_response', 'update_item_result');
    }

    /**
     * 商品编辑规则信息获取接口
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.7.sdrw37&path=scopeId:11430-apiId:23265
     * @author wzd  2015-2-10 上午10:15:59
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemUpdateSchemaGet($params) {
        $m = 'taobao.item.update.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id'));

        $ret = $this->getTaobaoData($m, $opts);
//        if (isset($ret['item_add_schema_get_response'])) {
//            return $ret['item_add_schema_get_response'];
//        }
        return $this->_unwrap($ret, 'item_update_schema_get_response', 'update_rules');
    }

    /**
     *  商品发布规则信息获取接口
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.8.sdrw37&path=scopeId:11430-apiId:23266
     * @author wzd  2015-2-10 上午10:17:25
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemAddSchemaGet($params) {
        $m = 'taobao.item.add.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id'));
        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'item_add_schema_get_response', 'add_rules');
    }

    /**
     *  基于xml格式的商品发布api
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.9.sdrw37&path=scopeId:11430-apiId:23267
     * @author wzd  2015-2-10 上午10:19:09
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemSchemaAdd($params) {
        $m = 'taobao.item.schema.add';
        $opts = array();
        $this->_merge_params($opts, $params, array('category_id', 'xml_data'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     *  新模式下的商品编辑接口
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.10.sdrw37&path=scopeId:11430-apiId:23268
     * @author wzd  2015-2-10 上午10:19:54
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemSchemaUpdate($params) {
        $m = 'taobao.item.schema.update';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id', 'xml_data'));
        $this->_merge_opts($opts, $params, array('category_id', 'incremental'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     *  集市schema增量编辑
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.18.sdrw37&path=scopeId:11430-apiId:24269
     * @author wzd  2015-2-10 上午10:21:42
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemSchemaIncrementUpdate($params) {
        $m = 'taobao.item.schema.increment.update';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id', 'parameters'));
        $this->_merge_opts($opts, $params, array('category_id'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     *  获取增量更新规则
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.19.sdrw37&path=scopeId:11430-apiId:24302
     * @author wzd  2015-2-10 上午10:22:43
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemIncrementUpdateSchemaGet($params) {
        $m = 'taobao.item.increment.update.schema.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('item_id', 'category_id'));
        $this->_merge_opts($opts, $params, array('update_fields'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 获取一个产品的信息
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.raBzpq&path=cid:4-apiId:4
     * @author wzd  2015-2-10 下午5:38:44
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoProductGet($params) {
        $m = 'taobao.product.get';
        $opts = array();
        if (!isset($params['fields'])) {
            $params['fields'] = 'product_id,cid,cat_name,props,props_str,name,binds,binds_str,sale_props,sale_props_str,price,desc,pic_url,created,modified,product_img.id,product_img.url,product_img.position,product_prop_img.id,product_prop_img.props,product_prop_img.url,product_prop_img.position';
        }
        $this->_merge_params($opts, $params, array('fields'));
        $this->_merge_opts($opts, $params, array('product_id', 'cid', 'props', 'customer_props', 'market_id'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 一口价商品上架
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.20.aOrec3&path=cid:4-apiId:32
     * @author wzd  2015-2-15 下午12:27:46
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemUpdateListing($params) {
        $m = 'taobao.item.update.listing';
        $opts = array();

        $this->_merge_params($opts, $params, array('num_iid', 'num'));

        return $this->getTaobaoData($m, $opts);
    }

    /**
     * 商品下架
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.19.aOrec3&path=cid:4-apiId:31
     * @author wzd  2015-2-15 下午12:28:08
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemUpdateDelisting($params) {
        $m = 'taobao.item.update.delisting';
        $opts = array();

        $this->_merge_params($opts, $params, array('num_iid'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'item_update_delisting_response', 'item');
    }

    /**
     * 获取用户下所有模板
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7395905.0.14&path=categoryId:7-apiId:10916
     * @author wzd  2015-3-5 下午3:45:27
     * @param unknown $params
     * @return Ambigous <multitype:, unknown>
     */
    function taobaoDeliveryTemplatesGet($params) {
        $m = 'taobao.delivery.templates.get';
        $opts = array();
        if (!isset($params['fields'])) {
            $params['fields'] = 'template_id,template_name,created,modified,supports,assumer,valuation,query_express,query_ems,query_cod,query_post';
        }

        $this->_merge_params($opts, $params, array('fields'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'delivery_templates_get_response', 'delivery_templates', 'delivery_template');
    }

    /**
     * 添加商品图片
     * @link http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.11.ZuP8UX&path=cid:4-apiId:23
     * @author wzd  2015-3-7 下午6:02:06
     * @param array $params
     * @return array
     */
    function taobaoItemImgUpload($params) {
        $m = 'taobao.item.img.upload';
        $opts = array();
        $this->_merge_params($opts, $params, array('num_iid'));
        $this->_merge_opts($opts, $params, array('id', 'position', 'image', 'is_major'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'item_img_upload_response', 'item_img');
    }

    /**
     * 删除商品图片
     * @link http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7629065.0.0.1aMdSp&apiId=24
     * @author wzd  2015-3-7 下午6:02:06
     * @param array $params
     * @return array
     */
    function taobaoItemImgDelete($params) {
        $m = 'taobao.item.img.delete';
        $opts = array();
        $this->_merge_params($opts, $params, array('num_iid', 'id'));
        $this->_merge_opts($opts, $params, array('is_sixth_pic'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'item_img_delete_response', 'item_img');
    }

    /**
     * 更新商品价格
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.36.xWTSwR&path=cid:4-apiId:10927
     * @author hunter  2015-4-1 下午12:28:08
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoItemPriceUpdate($params) {
        $m = 'taobao.item.price.update';
        $opts = array();

        $this->_merge_params($opts, $params, array('num_iid', 'price'));

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'item_price_update_response', 'item');
    }

    /**
     * 更新商品价格
     * @see http://open.taobao.com/apidoc/api.htm?spm=a219a.7386789.1998342952.36.xWTSwR&path=cid:4-apiId:10927
     * @author hunter  2015-4-1 下午12:28:08
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaWholesaleCategoryGet($params = array()) {
        $m = 'alibaba.wholesale.category.get';
        $opts = array();

        $this->_merge_params($opts, $params, array());

        $ret = $this->getTaobaoData($m, $opts);

        return $this->_unwrap($ret, 'alibaba_wholesale_category_get_response', 'item');
    }

    /**
     * 更新商品价格
     * @see http://api.taobao.com/apidoc/api.htm?spm=a219a.7386797.1998343897.1.EyovZ9&path=cid:1-apiId:21349
     * @author hunter  2015-4-30 下午12:28:08
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoUsersSellerGet($params) {
        $m = 'taobao.user.seller.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('fields'));
        $ret = $this->getTaobaoData($m, $opts);
        return $this->_unwrap($ret, 'user_seller_get_response', 'user');
    }

    /**
     * taobao.picture.pictures.get
     * @see http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.KYxtxK&apiId=25591
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function taobaoPicturePicturesGet($params) {
        $m = 'taobao.picture.pictures.get';
        $opts = array();
        $this->_merge_params($opts, $params, array('current_page'));
        $this->_merge_opts($opts, $params, array('title'));
        $ret = $this->getTaobaoData($m, $opts);
        //picture_pictures_get_response
        if (isset($ret['picture_pictures_get_response'])) {
            $ret = &$ret['picture_pictures_get_response'];
        }
        return $ret;
    }

    //图片总数查询
    function taobaoPicturePicturesCount($params) {
        $m = 'taobao.picture.pictures.count';
        $opts = array();
        $this->_merge_opts($opts, $params, array('title'));
        $ret = $this->getTaobaoData($m, $opts);
        //picture_pictures_get_response
        if (isset($ret['picture_pictures_count_response'])) {
            $ret = &$ret['picture_pictures_count_response'];
        }
        return $ret;
    }

    /**
     * alibaba.provider.einvoice.create
     * 发票接口同步spi
     * @see http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7629140.0.0.YvvqY1&apiId=24771
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaProviderEinvoiceCreate($params) {
        $m = 'alibaba.provider.einvoice.create';


        $ret = $this->getTaobaoData($m, $params);
        //picture_pictures_get_response
        if (isset($ret['alibaba_provider_einvoice_create_response'])) {
            $ret = &$ret['alibaba_provider_einvoice_create_response'];
        }
        return $ret;
    }

    /**
     *
     * 发票请求异步接口
     * alibaba.einvoice.createreq
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.hbQZ2b&apiId=25794
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaEinvoiceCreatereq($params) {
        $m = 'alibaba.einvoice.createreq';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_einvoice_createreq_response'])) {
            $ret = &$ret['alibaba_einvoice_createreq_response'];
        }
        return $ret;
    }

    /**
     *
     * ERP开票结果获取
     * alibaba.einvoice.create.result.get
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.Pj6qir&apiId=25796
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaEinvoiceCreateResultGet($params) {
        $m = 'alibaba.einvoice.create.result.get';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_einvoice_create_result_get_response'])) {
            $ret = &$ret['alibaba_einvoice_create_result_get_response'];
        }
        return $ret;
    }

    /**
     *
     * ERP增量开票结果获取
     * alibaba.einvoice.create.result.get
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.Pj6qir&apiId=25796
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaEinvoiceCreateResultsIncrementGet($params) {
        $m = 'alibaba.einvoice.create.results.increment.get';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_einvoice_create_results_increment_get_response'])) {
            $ret = &$ret['alibaba_einvoice_create_results_increment_get_response'];
        }
        return $ret;
    }

    /**
     *
     * 电子发票状态更新
     * alibaba.electronic.invoice.prepare
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7386797.0.0.YVfm08&apiId=25233
     * @author wq  2016-7-6
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaElectronicInvoicePrepare($params) {
        $m = 'alibaba.electronic.invoice.prepare';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_electronic_invoice_prepare_response'])) {
            $ret = &$ret['alibaba_electronic_invoice_prepare_response'];
        }
        return $ret;
    }

    /**
     *
     * ERP上传电子发票数据给天猫，天猫给消费者提供下载功能
     *   alibaba.electronic.invoice.detail.upload
     * @see http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.BSKIdV&apiId=26320
     * @author wq  2016-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibabaElectronicInvoiceDetailUpload($params) {
        $m = 'alibaba.electronic.invoice.detail.upload';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_electronic_invoice_detail_upload_response'])) {
            $ret = &$ret['alibaba_electronic_invoice_detail_upload_response'];
        }
        return $ret;
    }

    /**
     *
     * 智能发货引擎获取接口（获取智选cp和电子面单信息）)
     *   cainiao.smartdelivery.i.get
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7386797.0.0.PY5mZh&apiId=28302
     * @author wq  2016-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function CainiaoSmartdeliveryIGet($params) {
        $m = 'cainiao.smartdelivery.i.get';
        $ret = $this->getTaobaoData($m, $params);
        if (isset($ret['cainiao_smartdelivery_i_get_response'])) {
            $ret = &$ret['cainiao_smartdelivery_i_get_response'];
        }
        return $ret;
    }

    /**
     *
     * 智能发货引擎获取接口（获取智选cp和电子面单信息）)
     *  cainiao.smartdelivery.strategy.i.update
     * @see http://open.taobao.com/docs/doc.htm?spm=a219a.7629140.0.0.WT0QgC&treeId=319&articleId=28230&docType=2
     * @author wq  2016-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function CainiaoSmartdeliveryStrategyIUpdate($params) {

        $m = 'cainiao.smartdelivery.strategy.i.update';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['cainiao_smartdelivery_strategy_i_update_response'])) {
            $ret = &$ret['cainiao_smartdelivery_strategy_i_update_response'];
        }
        return $ret;
    }

    //

    /**
     * 合并必填参数
     * @author wzd  2015-2-7 下午1:26:53
     * @param unknown $dest
     * @param unknown $src
     * @param unknown $fields
     */
    function _merge_params(&$dest, $src, $fields) {
        foreach ($fields as $f) {
            if (isset($src[$f])) {
                $dest[$f] = $src[$f];
            }
        }
    }

    /**
     * 合并可选参数
     * @author wzd  2015-2-7 下午1:26:58
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

    /**
     * 菜鸟智能
     * @param $params
     * @return array
     */
    function cainiaoIntelligentDelivery($params) {
        $handles = array();
        //总参数
        $_params = array();
        foreach ((array) $params as $_key => $_value) {
            //发货人信息
            $send_address = array();
            //地址信息
            if (isset($_value['sender_province_name'])) {
                $send_address['province'] = $_value['sender_province_name'];
            }
            if (isset($_value['sender_city_name'])) {
                $send_address['city'] = $_value['sender_city_name'];
            }
            if (isset($_value['sender_district_name'])) {
                $send_address['district'] = $_value['sender_district_name'];
            }
            if (isset($_value['sender_street_name']) && !empty($_value['sender_street_name'])) {
                $send_address['town'] = $_value['sender_street_name'];
            }
            if (isset($_value['sender_addr'])) {
                $send_address['detail'] = $_value['sender_addr'];
            }
            //发货地址
            if (!empty($send_address)) {
                $_params['sender']['address'] = $send_address;
            }
            //姓名
            if (isset($_value['sender_name'])) {
                $_params['sender']['name'] = $_value['sender_name'];
            }
            //固定电话
            if (isset($_value['sender_phone'])) {
                $_params['sender']['phone'] = $_value['sender_phone'];
            }
            //移动电话
            if (isset($_value['sender_mobile'])) {
                $_params['sender']['mobile'] = $_value['sender_mobile'];
            }
            break;
        }
        //交易订单信息
        $trade_order_info_list_old = array();
        $i = 1;
        $obj_arr = array();
        foreach ((array) $params as $_key => $_value) {
            /* -----$trade_order_info_list(交易订单信息，数量限制为10)---START----- */
            //请求ID，要保证唯一
            $trade_order_info_list_old[$_key]['object_id'] = $i;
            $obj_arr[$i] = $_key;
            //订单信息
            $order_info = array();
            //订单渠道平台编码
            if (isset($_value['order_channels_type'])) {
                $order_info['order_channel_type'] = $_value['order_channels_type'];
            } else {
                $order_info['order_channel_type'] = 'TB';
            }
            //订单信息，数量限制100
            $trade_order_list = array();
            //交易号
            $trade_order_list['trade_order_id'] = $_value['sell_record_code'];
            if (isset($_value['deal_code_list']) && $_value['sale_channel_code'] == 'taobao') {
                $deal_code_list_arr = explode(",", $_value['deal_code_list']);
                $deal_code_list_arr = array_values(array_unique($deal_code_list_arr));
                foreach ($deal_code_list_arr as &$deal_code) {
                    $deal_code = (string) $deal_code;
                }
                $trade_order_list['trade_order_id'] = $deal_code_list_arr;
            }
            //买家留言
            if (!empty($_value['buyer_remark'])) {
                $trade_order_list['buyer_message'] = $_value['buyer_remark'];
            }


            $order_info['trade_order_list'] = $trade_order_list;
            $trade_order_info_list_old[$_key]['order_info'] = $order_info;
            //包裹信息
            $package_info = array();
            //包裹id,拆合单使用
            if (empty($_value['package_no'])) {
                $package_info['id'] = $_value['sell_record_code'];
            } else {
                $package_info['id'] = $_value['sell_record_code'] . '-' . $_value['package_no'];
            }
            //商品信息,数量限制为100
            if (isset($_value['goods_list'])) {
                $package_items = array();
                foreach ($_value['goods_list'] as $val) {
                    $package_items[] = array('name' => $val['goods_name'], 'count' => $val['num']);
                }
                $package_info['item_list'] = $package_items;
            }
            $trade_order_info_list_old[$_key]['package_info'] = $package_info;

            //收件人信息
            $recipient = array();
            //收件人详细地址
            if (isset($_value['receiver_province_name'])) {
                $recipient['address']['province'] = $_value['receiver_province_name'];
            }
            if (isset($_value['receiver_city_name'])) {
                $recipient['address']['city'] = $_value['receiver_city_name'];
            }
            if (isset($_value['receiver_district_name'])) {
                $recipient['address']['district'] = $_value['receiver_district_name'];
            }
            if (isset($_value['receiver_street_name'])) {
                $recipient['address']['town'] = $_value['receiver_street_name'];
            }
            if (isset($_value['receiver_addr'])) {
                $recipient['address']['detail'] = $_value['receiver_addr'];
            }
            //收货人姓名
            if (isset($_value['receiver_name'])) {
                $recipient['name'] = $_value['receiver_name'];
            }
            //收货人手机号码
            if (isset($_value['receiver_mobile'])) {
                $recipient['mobile'] = $_value['receiver_mobile'];
            }
            $trade_order_info_list_old[$_key]['recipient'] = $recipient;

            //使用者ID
            $trade_order_info_list_old[$_key]['user_id'] = $this->get_api_user_id();
            //$trade_order_info_list_old[$_key]['user_id'] = '2054718218';
            $i++;
        }

        //交易订单信息，数量限制为10
        $trade_order_info_list_arr = array_chunk($trade_order_info_list_old, 10);
        foreach ($trade_order_info_list_arr as $trade_order_info_list) {
            $object_id_arr = array_column($trade_order_info_list, 'object_id');
            $object_id_str = implode(',', $object_id_arr);
            $_params['trade_order_info_list'] = $trade_order_info_list;
            $outer_params['smart_delivery_batch_request'] = json_encode($_params);
            $handles[$object_id_str] = $this->newHandle('cainiao.smartdelivery.i.get', $outer_params);
        }
        $re = $this->multiExec($handles);
        $result = array();
        foreach ($re as $key_object_id => $ret_value) {
            $r = $this->jsonDecode($ret_value);
            //失败
            $object_arr = explode(',', $key_object_id);
            if (isset($r['error_response']['sub_msg']) || isset($r['error_response']['msg'])) {
                foreach ($object_arr as $object_id) {
                    $sell_record_code = $obj_arr[$object_id];
                    if (isset($r['error_response']['sub_msg'])) {
                        $result[$sell_record_code] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['sub_msg']);
                    } else if (isset($r['error_response']['msg'])) {
                        $result[$sell_record_code] = array('status' => '-1', 'data' => '', 'message' => $r['error_response']['msg']);
                    }
                }
            } else if (isset($r['cainiao_smartdelivery_i_get_response']['smart_delivery_response_wrapper_list']['smart_delivery_response_wrapper'])) {
                $res = $r['cainiao_smartdelivery_i_get_response']['smart_delivery_response_wrapper_list']['smart_delivery_response_wrapper'];
                foreach ($res as $cainiao_info) {
                    $sell_record_code = $obj_arr[$cainiao_info['smart_delivery_response']['object_id']];
                    if ($cainiao_info['success'] == 'true') {
                        $result[$sell_record_code] = array('status' => '1', 'data' => $cainiao_info, 'message' => '');
                    } else {
                        $result[$sell_record_code] = array('status' => '-1', 'data' => '', 'message' => $cainiao_info['error_message']);
                    }
                }
            } else {
                foreach ($object_arr as $object_id) {
                    $sell_record_code = $obj_arr[$object_id];
                    $result[$sell_record_code] = array('status' => '-1', 'data' => '', 'message' => $r);
                }
            }
        }
        return $result;
    }

    /**
     * 淘宝取消发货
     * @param $out_params
     */
    function taobao_rdc_aligenius_sendgoods_cancel($out_params) {
        $handles = array();
        foreach ($out_params as $val) {
            $api_param = array();
            $param['oid'] = $val['oid'];
            $param['refund_id'] = $val['refund_id'];
            $param['operate_time'] = $val['operate_time'];
            //$param['refund_fee'] = $val['refund_fee'];
            $param['status'] = $val['status'];
            $param['tid'] = $val['tid'];
            $api_param['param'] = json_encode($param);
            $handles[$val['refund_id']] = $this->newHandle('taobao.rdc.aligenius.sendgoods.cancel', $api_param);
        }
        $ret = $this->multiExec($handles);
        $result = array();
        foreach ($ret as $refund_id => $value) {
            if (empty($value)) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口连接异常');
                continue;
            }
            $ret_val = $this->jsonDecode($value);
            if (isset($ret_val['error_response'])) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $ret_val['error_response']['sub_msg'].$ret_val['error_response']['msg']);
                continue;
            }
            if (isset($ret_val['rdc_aligenius_sendgoods_cancel_response'])) {
                $api_response = $ret_val['rdc_aligenius_sendgoods_cancel_response'];
                if ($api_response['result']['success'] == 'true') {
                    $result[$refund_id] = array('status' => '1', 'data' => '', 'message' => '成功');
                    continue;
                } else {
                    $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $api_response['result']['error_info']);
                    continue;
                }
            } else {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口异常！');
            }
        }
        return $result;
    }

    /**
     * 淘宝退款信息审核
     * @param $out_params
     */
    function taobao_rdc_aligenius_refunds_check($out_params) {
        $handles = array();
        foreach ($out_params as $val) {
            $api_param = array();
            $param['refund_id'] = $val['refund_id'];
            $param['tid'] = $val['tid'];
            $param['oid'] = $val['oid'];
            //$param['refund_fee'] = $val['refund_fee'];
            $param['status'] = $val['status'];
            $param['msg'] = $val['msg'];
            $param['operate_time'] = $val['operate_time'];
            $api_param['param'] = json_encode($param);
            $handles[$val['refund_id']] = $this->newHandle('taobao.rdc.aligenius.refunds.check', $api_param);
        }
        $ret = $this->multiExec($handles);
        $result = array();
        foreach ($ret as $refund_id => $value) {
            if (empty($value)) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口连接异常');
                continue;
            }
            $ret_val = $this->jsonDecode($value);
            if (isset($ret_val['error_response'])) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $ret_val['error_response']['sub_msg'].$ret_val['error_response']['msg']);
                continue;
            }
            if (isset($ret_val['rdc_aligenius_refunds_check_response'])) {
                $api_response = $ret_val['rdc_aligenius_refunds_check_response'];
                if ($api_response['result']['success'] == 'true') {
                    $result[$refund_id] = array('status' => '1', 'data' => '', 'message' => '成功');
                    continue;
                } else {
                    $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $api_response['result']['error_info']);
                    continue;
                }
            } else {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口异常！');
            }
        }
        return $result;
    }

    /**
     * AG退货入仓状态写接口
     * @param $out_params
     * @return array
     */
    function taobao_nextone_logistics_warehouse_update($out_params) {
        $handles = array();
        foreach ($out_params as $val) {
            $api_param = array();
            $api_param['refund_id'] = $val['refund_id'];
            $api_param['warehouse_status'] = $val['warehouse_status'];
            $handles[$val['refund_id']] = $this->newHandle('taobao.nextone.logistics.warehouse.update', $api_param);
        }
        $ret = $this->multiExec($handles);
        $result = array();
        foreach ($ret as $refund_id => $value) {
            if (empty($value)) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口连接异常');
                continue;
            }
            $ret_val = $this->jsonDecode($value);
            if (isset($ret_val['error_response'])) {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $ret_val['error_response']['sub_msg'].$ret_val['error_response']['msg']);
                continue;
            }
            if (isset($ret_val['nextone_logistics_warehouse_update_response'])) {
                $api_response = $ret_val['nextone_logistics_warehouse_update_response'];
                if ($api_response['succeed'] == 'true') {
                    $result[$refund_id] = array('status' => '1', 'data' => '', 'message' => '成功');
                    continue;
                } else {
                    $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => $api_response['err_info']);
                    continue;
                }
            } else {
                $result[$refund_id] = array('status' => '-1', 'data' => '', 'message' => '接口异常！');
            }
        }
        return $result;
    }

    /**
     *
     * ERP开票请求接口
     *  alibaba.einvoice.createreq
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7629140.0.0.1jVDO9&apiId=25794
     * @author wq  2017-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibaba_einvoice_createreq($params) {

        $m = 'alibaba.einvoice.createreq';

        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_einvoice_createreq_response'])) {
            $ret = &$ret['alibaba_einvoice_createreq_response'];
        }
        return $ret;
    }

    /**
     *
     * ERP开票结果获取
     * alibaba.einvoice.create.result.get
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.03CvrJ&apiId=25796
     * @author wq  2017-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function alibaba_einvoice_create_result_get($params) {
        $m = 'alibaba.einvoice.create.result.get';
        $ret = $this->getTaobaoData($m, $params);

        if (isset($ret['alibaba_einvoice_create_result_get_response'])) {
            $ret = &$ret['alibaba_einvoice_create_result_get_response'];
        }
        return $ret;
    }
  /**
     *
     * ERP开票结果获取
     * alibaba.einvoice.create.result.get
     * @see http://open.taobao.com/docs/api.htm?spm=a219a.7395905.0.0.03CvrJ&apiId=25796
     * @author wq  2017-11-21
     * @param unknown $params
     * @return Ambigous <mixed, string, multitype:NULL unknown >
     */
    function aliexpress_trade_redefining_findorderlistquery($params) {
        $m = 'aliexpress.trade.redefining.findorderlistquery';
        $ret = $this->getTaobaoData($m, $params);

        return $ret;
    }
}
