<?php
require_lib('apiclient/PlatformApiClient');
require_model('tb/TbModel');
require_lib('business_util', true);

//require_lang('sys');

class SysTaskModel extends TbModel {

    /**
     *
     * Enter description here ...
     * @param unknown_type $param array("start_time","end_time")
     */
    function get_goods($param) {
        $db = $GLOBALS['context']->db;
        $message = "";
        $sql = "select * from base_shop_api where source = 'taobao'";
        if (isset($param['shop_code'])) {
            $sql .= " and shop_code in (" . deal_strs_with_quote($param['shop_code']) . ")";
        }

        $shop_list = $db->get_all($sql);
        foreach ($shop_list as $shop_api) {
            $page = 1;
            $inv_goods_page = 1;
            $onsale_goods_page = 1;
            $inv_goods = isset($param['inv_goods']) ? $param['inv_goods'] : true;
            $onsale_goods = isset($param['onsale_goods']) ? $param['onsale_goods'] : true;
            $next = true;
            $shop_code = $shop_api['shop_code'];
            do {
                $mdl_api = new PlatformApiClient("taobao", $shop_api['shop_code']);

                if ($page > $inv_goods_page) {
                    $inv_goods = false;
                }

                if ($page > $onsale_goods_page) {
                    $onsale_goods = false;
                }

                $param['inv_goods'] = $inv_goods;
                $param['onsale_goods'] = $onsale_goods;
                $param['page'] = $page;
                $param['page_size'] = 20;
                $goods_list = $mdl_api->getGoods($param);

                if (isset($goods_list['status']) && $goods_list['status'] == -1) {
                    return $goods_list;
                }
                $inv_goods_page = $goods_list['inv_goods_page'];
                $onsale_goods_page = $goods_list['onsale_goods_page'];

                if (count($goods_list['data']) != 0) {
                    $db->begin_trans();
                    try {
                        $sku_list = $mdl_api->getGoodsSkus(array("goods_list" => $goods_list['data']));
                        foreach ($goods_list['data'] as &$goods) {
                            $message .= $goods['goods_from_id'] . ":" . $goods['goods_name'] . "下载成功<br />";
                            $goods['shop_code'] = $shop_api['shop_code'];
                            if (!isset($sku_list[$goods['goods_from_id']])) {
                                $sku['source'] = "taobao";
                                $sku['shop_code'] = $shop_code;

                                $sku['goods_from_id'] = $goods['goods_from_id'];
                                $sku['sku_id'] = $goods['goods_from_id'];
                                $sku['goods_barcode'] = $goods['goods_code'];
                                $sku['num'] = $goods['num'];
                                $sku['price'] = $goods['price'];
                                $sku['last_update_time'] = add_time();
                                $sku['source'] = $goods['source'];
                                $goods['is_sku'] = 1;
                                $sku_list[$sku['goods_from_id']][] = $sku;
                            }
                        }

                        $insert_sku_list = array();
                        foreach ($sku_list as $goods_sku) {

                            foreach ($goods_sku as $sku) {
                                $sku['shop_code'] = $shop_code;
                                $insert_sku_list[] = $sku;
                            }
                        }
                        if (count($goods_list['data']) > 0) {
                            $this->insert_multi_duplicate("api_goods", $goods_list['data'], $goods_list['data']);
                        }

                        if (count($insert_sku_list) > 0) {
                            $this->insert_multi_duplicate("api_goods_sku", array_values($insert_sku_list), array_values($insert_sku_list));
                        }

                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollback();
                        return $this->format_ret(-1, "", $e->getMessage());
                    }
                } else {
                    $next = false;
                }
                $page++;
                if ($page > $goods_list['inv_goods_page'] && $page > $goods_list['onsale_goods_page']) {
                    $next = false;
                }
            } while ($next);

            //淘宝的在库商品，卖完的商品需要单独拉取 start
            $next = true;
            $page = 1;
            do {
                $param['page'] = $page;
                $goods_list = $mdl_api->getTaobaoInvSoldOutGoods($param);
                if (count($goods_list['data']) != 0) {
                    $db->begin_trans();
                    try {
                        $sku_list = $mdl_api->getGoodsSkus(array("goods_list" => $goods_list['data']));

                        foreach ($goods_list['data'] as &$goods) {
                            $message .= $goods['goods_from_id'] . ":" . $goods['goods_name'] . "下载成功<br />";
                            $goods['shop_code'] = $shop_api['shop_code'];
                            if (!isset($sku_list[$goods['goods_from_id']])) {
                                $sku['source'] = "taobao";
                                $sku['goods_from_id'] = $goods['goods_from_id'];
                                $sku['sku_id'] = $goods['goods_from_id'];
                                $sku['goods_barcode'] = $goods['goods_code'];
                                $sku['num'] = $goods['num'];
                                $sku['price'] = $goods['price'];
                                $sku['last_update_time'] = add_time();
                                $sku['source'] = $goods['source'];
                                $goods['is_sku'] = 1;
                                $sku_list[$sku['goods_from_id']][] = $sku;
                            }
                        }

                        $insert_sku_list = array();
                        foreach ($sku_list as $goods_sku) {
                            foreach ($goods_sku as $sku) {
                                $insert_sku_list[] = $sku;
                            }
                        }
                        if (count($goods_list['data']) > 0) {
                            $this->insert_multi_duplicate("api_goods", $goods_list['data'], $goods_list['data']);
                        }

                        if (count($insert_sku_list) > 0) {
                            $this->insert_multi_duplicate("api_goods_sku", array_values($insert_sku_list), array_values($insert_sku_list));
                        }
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollback();
                        return $this->format_ret(-1, "", $e->getMessage());
                    }
                } else {
                    $next = false;
                }
                $page++;
                if ($page > $goods_list['page'] && $page > $goods_list['page']) {
                    $next = false;
                }
            } while ($next);
            //淘宝的在库商品，卖完的商品需要单独拉取 end
        }

        return $this->format_ret(1, "", "<b style='color:red'>" . $message . "</b>");
    }

    function get_express() {
        $db = $GLOBALS['context']->db;

        $sql = "select * from base_shop_api where source = 'taobao'";
        $shop_api = $db->get_row($sql);
        $mdl_api = new PlatformApiClient("taobao", $shop_api['shop_code']);
        $express_list = $mdl_api->getExpress();
        $db->begin_trans();
        try {
            foreach ($express_list as $express) {
                $db->autoReplace("api_express", $express, true);
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            return $this->format_ret(-1, "", $e->getMessage());
        }

        return $this->format_ret(1, "", "<b style='color:red'>下载成功</b>");
    }

    function send_order_all() {
        $sql = "select api_order_send_id from api_order_send where status=0 or status='-2'";
        $order_list = CTX()->db->get_all($sql);
        foreach ($order_list as $order) {
            $this->send_order($order['api_order_send_id'], 0);
            sleep(500);
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $id
     * @param unknown_type $type 0:正常回写 1:强制回写 2:本地回写
     */
    function send_order($id = "", $type = 0) {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('api/sys/order_send/callback1')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        $message = "";
        $status = 1;
        $db = $GLOBALS['context']->db;
        if ($id != "") {
            $sql = "select * from api_order_send where api_order_send_id in (" . $id . ")";
            $order_list = $db->get_all($sql);
        } else {
            //批量回写
        }

        foreach ($order_list as $order) {
            if ($type != 1) {
                $sql = "select * from api_taobao_order where tid = :tid";
                $taobao_order = $db->get_row($sql, array(":tid" => $order['tid']));
                if ($taobao_order && $taobao_order['status'] != "WAIT_SELLER_SEND_GOODS") {
                    continue;
                }
            }
            $mdl_api = new PlatformApiClient($order['source'], $order['shop_code']);
            $ret = $mdl_api->sendOrder($order);
            if ($ret['status'] == -1) {
                $status = $ret['status'];
            }
            $message .= $ret['message'];
            $this->update_exp("api_order_send", array("status" => $ret['status'], "upload_time" => date('Y-m-d H:i:s'), "error_remark" => $ret['message']), array("api_order_send_id" => $order['api_order_send_id']));
        }
        return $this->format_ret($status, "", $message);
    }

    function send_local($id = "", $type = 0, $action = '') {
        //#############权限
        if (!load_model('sys/PrivilegeModel')->check_priv('api/sys/order_send/callback1')) {
            return $this->return_value(-1, "无权访问");
        }
        //###########
        $message = "";
        $status = 1;
        $db = $GLOBALS['context']->db;
        if ($id != "") {
            $sql = "select * from api_order_send where api_order_send_id in (" . $id . ")";
            $order_list = $db->get_all($sql);

            $time = date("Y-m-d H:i:s");
            $sql = "update api_order_send set status=2,error_remark=' ',upload_time='" . $time . "' where api_order_send_id in (" . $id . ")";
            $result = CTX()->db->query($sql);

            if ($action == 'batch_send') {
                $message = '批量回写本地成功';
                $action = "批量本地回写";
            } else {
                $message = '回写本地成功';
                $action = "本地回写";
            }
            foreach ($order_list as $order) {
                $sql = "update oms_sell_record set is_back = 2, is_back_time= '" . $time . "' where sell_record_code = " . $order['sell_record_code'];
                $res = CTX()->db->query($sql);

                //添加日志
                load_model('oms/SellRecordActionModel')->add_action($order['sell_record_code'], $action, $message);
            }
            $status = 1;
        }
        return $this->format_ret($status, "", $message);
    }

    //已经废弃
    function get_order($param) {
        return $this->get_order_next_page($param);
    }

    //已经废弃
    function get_order_next_page($params) {
        $db = $GLOBALS['context']->db;
        //$param['use_has_next'] = "true";

        $sql = "select * from base_shop_api where source = 'taobao'";
        if (!empty($params['shop_code'])) {
            $sql .= " and shop_code in (" . deal_strs_with_quote($params['shop_code']) . ")";
        }
        $shop_list = $db->get_all($sql);

        $conf = require_conf('api_url');
        $url = $conf['api'] . 'api/order/order_download';
        require_lib('apiclient/Validation');
        foreach ($shop_list as $api) {
            $params['shop_code'] = $api['shop_code'];
            //$kh_id =  CTX()->get_session('kh_id'); //'1094';
            $kh_id = CTX()->saas->get_saas_key();
            $order = Validation::send($url, $kh_id, 'efast5', 'taobao', $params);
            if (isset($order['status']) && $order['status'] != 1) {
                return $order;
            }
        }
        return $this->format_ret(1, "", "<b style='color:red'>拉取成功</b>");

        /* $param['page_no'] = 1;
          foreach ($shop_list as $api){
          $next = true;
          //$mdl_api = new PlatformApiClient("taobao",$api['shop_code']);

          do{
          //$order = $mdl_api->getOrder($param,1);

          if(isset($order['status']) && $order['status'] == -1){
          return $order;
          }
          $db->begin_trans();
          try {
          //这里是入库业务
          foreach ($order['data'] as $o){
          $data_order = array();
          $data_order['tid'] = $o['tid'];
          $data_order['is_detail'] = 1;
          $data_order['source'] = "taobao";
          $data_order['shop_code'] = $api['shop_code'];

          $db->autoReplace("api_order",$data_order,true);
          }

          $db->commit();
          }catch (Exception $e) {
          $db->rollback();
          return $this->format_ret(-1,"",$e->getMessage());
          }

          $next = $order['has_next'];
          $param['page_no'] = $param['page_no']+1;

          }while($next);
          }
          return $this->format_ret(1,"","<b style='color:red'>拉取成功</b>"); */
    }

    function get_detail() {
        $next = true;
        $message = "";
        $db = $GLOBALS['context']->db;
        $sql = "select * from api_order where is_detail = 1";
        do {
            $order_list = $db->get_limit($sql, NULL, 100, 0);

            $db->begin_trans();
            try {
                foreach ($order_list as $order) {
                    $message .= $order['tid'] . "<br />";
                    $mdl_api = new PlatformApiClient($order['source'], $order['shop_code']);
                    $param['tid'] = $order['tid'];
                    $api_order = $mdl_api->getOrderDetail($param);
                    $api_order['order']['is_detail'] = 0;

                    $this->update_exp("api_order", $api_order['order'], array("tid" => $order['tid']));

                    foreach ($api_order['order']['detail'] as $detail) {
                        $db->autoReplace("api_order_detail", $detail, true);
                        /* $sql_detail = "select count(*) from api_order_detail where oid = :oid";
                          $num = $db->get_value($sql_detail,array(":oid"=>$detail['oid']));
                          if($num == 0){
                          $this->insert_exp("api_order_detail", $detail);
                          }else{
                          $this->update_exp("api_order_detail", $detail, array("oid"=>$detail['oid']));
                          } */
                    }
                }
                $db->commit();
            } catch (Exception $e) {
                $db->rollback();
                return $this->format_ret(-1, "", $e->getMessage());
            }
            $data_num = $this->get_num($sql);
            if ($data_num['data'] == 0) {
                $next = false;
            }
        } while ($next);

        $next = $db->get_value($sql);
        if ($next) {
            $next = true;
        } else {
            $next = false;
        }

        if ($message) {
            $message = "拉取详情:" . $message . "<br />";
        } else {
            $message = "没有需要下载的数据<br />";
        }
        return $this->format_ret(1, array("next" => $next), $message);
    }

    /**
     * 系统内部id
     * Enter description here ...
     * @param unknown_type $goods_id
     * @param unknown_type $sku_id
     */
    function sync_goods_inv($goods_id, $sku_id = "") {
        $message = "";
        $sku_id = "";
        $db = $GLOBALS['context']->db;

        //function update_barcode_inv($shop_code,$barcode_arr){

        $sql = "select g.shop_code,s.goods_barcode from api_goods g INNER JOIN
                 api_goods_sku s ON g.goods_from_id=s.goods_from_id
                where g.api_goods_id in (" . $goods_id . ") group by g.shop_code,goods_barcode";
        $sku_list = $db->get_all($sql);
        $shop_sku = array();
        foreach ($sku_list as $val) {
            if (!empty($val['goods_barcode'])) {
                $shop_sku[$val['shop_code']][] = $val['goods_barcode'];
            }
        }
        foreach ($shop_sku as $shop_code => $barcode_arr) {
            load_model("api/BaseInvModel")->update_barcode_inv($shop_code, $barcode_arr);
        }

        $time = add_time();
        $sql = "select * from api_goods where api_goods_id in (" . $goods_id . ")";
        $goods_list = $db->get_all($sql);
        $goods_id_arr_all = array();
        $not_allow = 0;
        foreach ($goods_list as $goods) {
            $num = 0;
            $mdl_api = new PlatformApiClient($goods['source'], $goods['shop_code']);
            $sql = "select * from api_goods_sku where goods_from_id = :goods_from_id and is_allow_sync_inv = 1";
            $sku_list = $db->get_all($sql, array(":goods_from_id" => $goods['goods_from_id']));
            if (empty($sku_list)) {
                $not_allow = 2;
            } else {
                $sql = "select * from api_goods_sku where goods_from_id = :goods_from_id and is_allow_sync_inv = 0";
                $not_allow_sync = $db->get_all($sql, array(":goods_from_id" => $goods['goods_from_id']));
                if (!empty($not_allow_sync)) {
                    $not_allow = 1;
                }
            }


            //判断商品类型获取需要同步的类型
            if ($goods['source'] == "taobao") {
                //  $goods_id_arr_all[] = $goods['goods_from_id'];
                foreach ($sku_list as &$sku) {
                    $sku_id .= $sku['api_goods_sku_id'] . ",";

//                    $sql = "select sum(t2.num) from api_order t1
//                    		left join api_order_detail t2 on t1.tid = t2.tid
//                    		where t2.sku_id = :sku_id and t1.status = 1 and t1.is_change = 0";
//                    $order_num = $db->get_value($sql,array(":sku_id"=>$sku['sku_id']));
                    //1：拍下减少库存 2：付款减少库存
                    // 2：付款减少库存
                    $num = $sku['inv_num']; //- $order_num ;
//                    if($goods['stock_type'] == 1){
//                        $sql = "select sum(t2.num) from api_order t1
//                    		left join api_order_detail t2 on t1.tid = t2.tid
//                    		where t2.sku_id = :sku_id and t1.status = 'WAIT_BUYER_PAY'";
//                        $wait_pay_order_num = $db->get_value($sql,array(":sku_id"=>$sku['sku_id']));
//                        $num = $sku['inv_num'] - $order_num - $wait_pay_order_num;
//                    }
                    //1：拍下减少库存
                    if ($goods['stock_type'] == 1) {
                        if ($num < $sku['with_hold_quantity']) {
                            $num = $sku['with_hold_quantity'];
                        }
                    }

                    //$sku['inv_num'] = $num > 0?$num:0;
                }
            }


            $param['goods_from_id'] = $goods['goods_from_id'];
            $param['sku_list'] = $sku_list;
            if ($sku_list[0]['inv_num'] == -1) {
                continue;
            }

            $param['num'] = intval($sku_list[0]['inv_num']);

            $message .= $goods['goods_name'];
            if ($goods['has_sku'] == 0) {
                $ret = $mdl_api->updateGoodsStock($param);
                if (isset($ret['status']) && $ret['status'] == -1) {
                    $message .= $ret['message'];
                } else {
                    $message .= "同步成功";
                }
            } else {
                $ret = $mdl_api->updateSkuStock($param);
                if (isset($ret['status']) && $ret['status'] == -1) {
                    $message .= $ret['message'];
                } else {
                    $message .= "同步成功";
                }
            }

            $message .= "<br />";
        }
        if ($not_allow == 1) {
            $message .= '明细中存在不允许库存同步的商品信息';
        }
        if ($not_allow == 2) {
            $message = '明细中存在不允许库存同步的商品信息';
        }
//        $sql = "select g.goods_from_id,sum(s.inv_num) as num from api_goods g
//            left join api_goods_sku s where g.goods_from_id = s.goods_from_id and
//            g.goods_from_id  in ()
//            ";
//         foreach($goods_id_arr_all as $goods_id){
//
//
//         }
//
        if ($sku_id != '') {
            $sku_id = substr($sku_id, 0, strlen($sku_id) - 1);
            $this->update_exp("api_goods_sku", array("last_update_time" => $time), "api_goods_sku_id in (" . $sku_id . ")");
        }
        return $this->format_ret(1, "", $message);
    }

    function get_ncm_order($param) {
        $db = $GLOBALS['context']->db;
        $message = "";
        $sql = "select * from base_shop_api where source = 'taobao'";
        if (isset($param['shop_code'])) {
            $sql .= " and shop_code in (" . deal_strs_with_quote($param['shop_code']) . ")";
        }

        $shop_list = $db->get_all($sql);

        foreach ($shop_list as $api) {
            $next = true;
            $mdl_api = new PlatformApiClient("taobao", $api['shop_code']);
            $page = 1;
            do {
                $param['page'] = $page;
                $order = $mdl_api->getNcmOrder($param, 2);

                if (!isset($order['order']) || count($order['order']) == 0) {
                    $next = false;
                }
                if (isset($order['status']) && $order['status'] == -1) {
                    return $order;
                }
                if (isset($order['order']) && count($order['order']) != 0) {
                    $db->begin_trans();
                    try {
                        //这里是入库业务
                        $order_list = array();
                        $order_detail_list = array();

                        foreach ($order['order'] as $o) {
                            $message .= "订单号：" . $o['fenxiao_id'] . "<br />";
                            $insert_order[0] = $o;

                            $ret = $this->insert_multi_duplicate("api_taobao_ncm_order", $insert_order, $insert_order);

                            if ($ret['data'] != "" && $ret['data'] != 0) {
                                $pid = $ret['data'];
                            } else {
                                $sql = "select taobao_ncm_order_id from api_taobao_ncm_order where fenxiao_id = :fenxiao_id";
                                $pid = $db->get_value($sql, array(":fenxiao_id" => $o['fenxiao_id']));
                            }

                            $this->delete_exp("api_taobao_ncm_order_detail", array("pid" => $pid));
                            $order_list[] = $o;
                            foreach ($o['detail'] as &$detail) {
                                $detail['pid'] = $pid;
                                $order_detail_list[] = $detail;
                            }
                        }

                        $this->insert_multi_duplicate("api_taobao_ncm_order_detail", array_values($order_detail_list), array_values($order_detail_list));
                        $db->commit();
                    } catch (Exception $e) {
                        $db->rollback();
                        return $this->format_ret(-1, "", $e->getMessage());
                    }
                }

                $page++;
            } while ($next);
        }
        return $this->format_ret(1, "", $message);
    }

}
