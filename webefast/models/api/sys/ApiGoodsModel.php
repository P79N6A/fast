<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
set_time_limit(0);
class ApiGoodsModel extends TbModel {

    function get_table() {
        return 'api_goods';
    }

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_main = "";
        $sql = "FROM {$this->table} rl LEFT JOIN api_goods_sku AS r2 ON rl.goods_from_id = r2.goods_from_id WHERE 1 AND rl.source<>'weipinhui' ";
        $sql_values = array();

        //店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);

        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $arr = explode(',', $filter['goods_code']);
            $str = $this->arr_to_like_sql_value($arr, 'goods_code', $sql_values);
            $str = str_replace('goods_code like', 'rl.goods_code like', $str);
            $sql_main .= " AND " . $str;
        }

        //$sql_mx = "";
        //$sql_mx_values = array();
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $goods_name_arr = explode(',', $filter['goods_name']);
            $sql_str = $this->arr_to_like_sql_value($goods_name_arr, 'goods_name', $sql_values, 'rl.');
            $sql_main .= " AND {$sql_str}";
        }
        //商品ID
        if (isset($filter['goods_from_id']) && $filter['goods_from_id'] != '') {
            $sql_main .= " AND rl.goods_from_id LIKE :goods_from_id ";
            $sql_values[':goods_from_id'] = $filter['goods_from_id'] . '%';
        }

        //商品条形码
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
            $arr = explode(',', $filter['goods_barcode']);
            $str = $this->arr_to_like_sql_value($arr, 'goods_barcode', $sql_values, 'r2.');
            $sql_main .= " AND " . $str;
        }

        //商品skuid
        if (isset($filter['sku_id']) && $filter['sku_id'] != '') {
            $sql_main .= " AND r2.sku_id LIKE :sku_id ";
            $sql_values[':sku_id'] = $filter['sku_id'] . '%';
        }

        //是否库存同步
        if (isset($filter['is_sync_inv']) && $filter['is_sync_inv'] <> '') {
            $arr = explode(',', $filter['is_sync_inv']);
            /*$str = "'" . join("','", $arr) . "'";
            $sql_mx .= " AND is_allow_sync_inv  in ({$str}) ";*/
            $arr_str = $this->arr_to_in_sql_value($arr,'is_allow_sync_inv',$sql_values);
            $sql_main .= " AND r2.is_allow_sync_inv in ({$arr_str}) ";
        }

        //是否允许上架
        if (isset($filter['is_allow_onsale']) && $filter['is_allow_onsale'] <> '') {
            $arr = explode(',', $filter['is_allow_onsale']);
            $str = "'" . join("','", $arr) . "'";
            $sql_main .= " AND rl.is_allow_onsale in ({$str}) ";
        }

        //销售平台
        if (isset($filter['sale_channel_code']) && $filter['sale_channel_code'] !== '') {
                        $arr = explode(',',$filter['sale_channel_code']);
            $str = $this->arr_to_in_sql_value($arr, 'sale_channel_code', $sql_values);
            $sql_main .= " AND rl.source in ( " . $str . " ) ";
        }

        /*if (!empty($sql_mx)) {
            $sql_mx = "select goods_from_id from api_goods_sku where 1 " . $sql_mx;
            $goods_from_id = ctx()->db->get_all_col($sql_mx, $sql_mx_values);
            if (empty($goods_from_id)) {
                $sql_main .= " AND 1 != 1";
            } else {
                $goods_from_id_list = "'" . join("','", $goods_from_id) . "'";
                $sql_main .= " AND rl.goods_from_id in ({$goods_from_id_list}) ";
            }
        }*/
        //商品状态
        if (isset($filter['status']) && $filter['status'] <> '') {
            $arr = explode(',', $filter['status']);
            $str = "'" . join("','", $arr) . "'";
            $sql_main .= " AND rl.status in ({$str}) ";
        }


        //销售平台
        if (isset($filter['source']) && $filter['source'] <> '') {
            $arr = explode(',', $filter['source']);
            $str = "'" . join("','", $arr) . "'";
            $sql_main .= " AND rl.source  in ({$str}) ";
        }

        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] <> '') {
            $arr = explode(',', $filter['shop_code']);
            $str = "'" . join("','", $arr) . "'";
            $sql_main .= " AND rl.shop_code in ({$str}) ";
        }
        //导出
        if ($filter['ctl_type'] == 'export') {
            return $this->api_goods_export_csv($sql_main, $sql_values, $filter);
        }
        $sql_main = $sql . $sql_main;
        $select = ' DISTINCT rl.* ';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        filter_fk_name($data['data'], array('shop_code|shop', 'source|source'));
        $base_goods_img = array();
        foreach ($data['data'] as $key => &$value) {
            if (isset($filter['is_sync_inv']) && $filter['is_sync_inv'] <> '') {
                $arr = explode(',', $filter['is_sync_inv']);
                if (count($arr) > 1) {
                    $value['is_allow_sync_inv_value'] = 'all';
                } else {
                    $value['is_allow_sync_inv_value'] = $arr[0];
                }
            } else {
                $value['is_allow_sync_inv_value'] = 'all';
            }
            //图片没有就从商品档案里面找
            if (!empty($value['goods_img'])) {
                $value['goods_img_view'] = "<img width='48px' height='48px' src='{$value['goods_img']}' />";
            } else {
                $goods_img = (isset($base_goods_img[$value['goods_code']])) ? $base_goods_img[$value['goods_code']] : oms_tb_val('base_goods', 'goods_img', array('goods_code' => $value['goods_code']));
                $base_goods_img[$value['goods_code']] = $goods_img;
                $value['goods_img_view'] = empty($goods_img) ? '' : "<img width='48px' height='48px' src='{$goods_img}' />";
            }

        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //导出
    function api_goods_export_csv($sql_main, $sql_values, $filter) {
        $sql = "select rl.shop_code,rl.goods_name,rl.goods_code,rl.goods_from_id,rl.source,rl.num,rl.status,rl.stock_type,rl.price,
    	r2.sku_id,r2.goods_barcode,r2.num,r2.price,r2.is_allow_sync_inv,r2.sku_properties,r2.sku_properties_name,r2.inv_num,r2.inv_up_time,r2.last_sync_inv_num FROM {$this->table} rl LEFT JOIN api_goods_sku r2 on rl.goods_from_id = r2.goods_from_id WHERE 1 AND r2.status=1 AND rl.source<>'weipinhui' ";
        $sql .= $sql_main . " order by rl.last_update_time desc";
        $data = $this->db->get_all($sql, $sql_values);
        filter_fk_name($data, array('shop_code|shop',));
        foreach ($data as &$row) {
            $row['stock_type_txt'] = $row['stock_type'] == '1' ? '拍下减少库存' : '付款减少库存';
            $row['status_txt'] = $row['status'] == '1' ? '在售' : '在库';
            $row['is_allow_sync_inv_txt'] = $row['is_allow_sync_inv'] == '1' ? '是' : '否';
            $row['last_sync_inv_num'] = ($row['last_sync_inv_num'] == -1) ? '' : $row['last_sync_inv_num'];
        }
        $ret['data'] = $data;
        return $this->format_ret(1, $ret);
    }

    function get_by_page_sku($filter) {
        $sql_join = "";
        //$sql_main = "FROM {$this->table} rl  WHERE 1";
        $sql_main = "FROM {$this->table} rl LEFT JOIN api_goods_sku r2 on rl.goods_from_id = r2.goods_from_id WHERE 1";
        $sql_values = array();


        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {

            $sql_main .= " AND rl.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //商品条形码
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {

            $sql_main .= " AND r2.goods_barcode LIKE :goods_barcode ";
            $sql_values[':goods_barcode'] = $filter['goods_barcode'] . '%';
        }

        //商品条形码
        if (isset($filter['goods_barcode_null']) && $filter['goods_barcode_null'] === true) {
            $sql_main .= " AND r2.goods_barcode <>'' AND  r2.goods_barcode IS NOT NULL ";
        }



        //商品状态
        if (isset($filter['status']) && !empty($filter['status'])) {
            $sql_main .= " AND rl.status in (:status) ";
            $sql_values[':status'] = $filter['status'];
        }

        //店铺
        if (isset($filter['shop_code']) && !empty($filter['shop_code'])) {
            $sql_main .= " AND rl.shop_code=:shop_code";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }



        //
        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
            $sql_main .= " AND rl.source = in (:sale_channel_code) ";
            $sql_values[':sale_channel_code'] = $filter['source'];
        }


        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $select = 'rl.shop_code,r2.*';
        //echo $sql_main;
        //$sql_main .= " group by rl.goods_from_id ";
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        //$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop',));
        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('api_order_id' => $id));
    }

    function update_active($active, $id) {

        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $data = $this->db->create_mapper('api_goods_sku')->update(array('is_allow_sync_inv' => $active), array('goods_from_id' => $id));
        $ret = parent :: update(array('is_allow_sync_inv' => $active), array('goods_from_id' => $id));
        return $ret;
    }

    function update_active_sku($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $sql = " select goods_barcode,shop_code from api_goods_sku  where api_goods_sku_id = '{$id}'";
        $yw_code = $this->db->get_row($sql);
        if ($active == 1) {
            $data = $this->db->create_mapper('api_goods_sku')->update(array('is_allow_sync_inv' => $active, 'sys_update_time' => '0000-00-00 00:00:00'), array('api_goods_sku_id' => $id));
            $barcode_arr[] = $yw_code['goods_barcode'];
            $ret_inv = load_model('api/BaseInvModel')->update_inv_increment($yw_code['shop_code'], $barcode_arr, 1);
            if($ret_inv['status']<1){
                return $ret_inv;
            }
            
        } else {
            $data = $this->db->create_mapper('api_goods_sku')->update(array('is_allow_sync_inv' => $active), array('api_goods_sku_id' => $id));
        }
        //添加系统日志
        $module = '网络订单'; //模块名称
        $operate_type = '编辑'; //操作类型
        if ($active == 1) {
            //$this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");//执行全量同步
            $log_xq = "设置允许库存同步";
        } else {
            $log_xq = "设置禁止库存同步";
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code['goods_barcode'], 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);
        return $this->format_ret(1);
    }

    //批量设置允许库存同步
    function p_update_active($active, $id, $ids) {
        //api_goods_sku
        //api_goods
        $str = explode(',', $id);
        if ($ids == 'pt' ) {//平台商品批量操作                      
            $sql_values[':active'] = $active;
            $goods_from_id = $this->arr_to_in_sql_value($str, 'goods_from_id', $sql_values);
            $sql2 = " UPDATE api_goods_sku SET is_allow_sync_inv = :active WHERE goods_from_id IN ( {$goods_from_id} ) ";
            //获取商品barcode
            $goods_from_id_ = $this->arr_to_in_sql_value($str, 'goods_from_id', $sql_value);
            $sql3 = "SELECT goods_barcode,api_goods_sku_id FROM api_goods_sku WHERE goods_from_id IN ({$goods_from_id_}) ";
            $barcode_arr = $this->db->get_all($sql3,$sql_value);
            foreach ($barcode_arr as $value) {
                $goods_barcode[] = $value['goods_barcode'];
                $goods_sku_id[] = $value['api_goods_sku_id'];
            }
        }else{//唯品会商品批量操作
            $api_goods_sku_id = implode(',', $ids);
            $sql_values[':active'] = $active;
            $api_goods_sku_id = $this->arr_to_in_sql_value($ids, 'api_goods_sku_id', $sql_values);
            $sql2 = " UPDATE api_goods_sku SET is_allow_sync_inv = :active WHERE  api_goods_sku_id IN ({$api_goods_sku_id}) ";
            //获取商品barcode
            $api_goods_sku_id_ = $this->arr_to_in_sql_value($ids, 'api_goods_sku_id', $sql_value);
            $sql3 = "SELECT goods_barcode,api_goods_sku_id FROM api_goods_sku WHERE api_goods_sku_id IN ({$api_goods_sku_id_}) ";
            $barcode_arr = $this->db->get_all($sql3,$sql_value);
            foreach ($barcode_arr as $value) {
                $goods_barcode[] = $value['goods_barcode'];
                $goods_sku_id[] = $value['api_goods_sku_id'];
            }
        }
        $ret2 = $this->db->query($sql2, $sql_values);
        
        if ($ret2 === true) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '批量设置成功';

            //添加系统日志
            $module = '进销存'; //模块名称          
            $yw_code = '';    //业务编码
            $operate_type = '编辑'; //操作类型
            if ($active == 1) {
                //$this->db->query("update sys_schedule_record set all_exec_time=0 where type_code='update_inv'");//执行全量同步
                $log_xq = "设置允许库存同步";            
                foreach ($goods_sku_id as $v) {
                    $sql_id = " select goods_barcode,shop_code from api_goods_sku  where api_goods_sku_id = :id  ";
                    $result = $this->db->get_row($sql_id, array(':id'=>$v));
                    $shop_code = $result['shop_code'];
                    $barcode_arr[] = $result['goods_barcode'];                  
                    $ret_inv = load_model('api/BaseInvModel')->update_inv_increment($shop_code, $barcode_arr, 1);
//                    if($ret_inv['status']<1){
//                        return $ret_inv;
//                    }         
                }
            } else {
                $log_xq = "设置禁止库存同步";
            }
            $p = array_chunk($goods_barcode, 10, true);
            foreach ($p as $key => $value) {
                $v = implode(',', $value);
                $xq = $v . $log_xq;
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $xq);
                load_model('sys/OperateLogModel')->insert($log);
            }
            return $ret;
        } else {
            $ret['status'] = '-1';
            $ret['data'] = '';
            $ret['message'] = '批量设置失败';     
            return $ret;
        }
    }
    
    //一键设置允许库存同步
    function once_update_active($active) {
        //获取有权限的店铺
        $shop_code_arr = load_model('base/ShopModel')->get_wepinhuijit_shop();
        foreach ($shop_code_arr as $key => $value) {
            $shop_code[] = $value['shop_code'];
        }
        $sql_values[':active'] = $active;
        $shop_code_str = $this->arr_to_in_sql_value($shop_code, 'shop_code', $sql_values);
        $sql = " UPDATE api_goods_sku SET is_allow_sync_inv = :active WHERE  source = 'weipinhui' AND shop_code IN ({$shop_code_str}) ";        
        $ret2 = $this->db->query($sql, $sql_values);
        if ($active == 1) {
            $shop_code_str_ = $this->arr_to_in_sql_value($shop_code, 'shop_code', $sql_value);
            $data = $this->db->get_all("SELECT api_goods_sku_id,goods_barcode,shop_code FROM api_goods_sku WHERE source = 'weipinhui' AND shop_code IN ({$shop_code_str_}) " , $sql_value);
            foreach ($data as $key => $value) {
                $shop_arr[$value['shop_code']][] = $value['goods_barcode'];
            }
            foreach ($shop_arr as $k => $barcode_arr) {               
            $ret_inv = load_model('api/BaseInvModel')->update_inv_increment($k, $barcode_arr, 1);
            }
        }      
        if ($ret2 === true) {
            $ret['status'] = '1';
            $ret['message'] = '一键设置成功';
            //添加系统日志
            $module = '进销存'; //模块名称          
            $yw_code = '';  //业务编码
            $operate_type = '编辑'; //操作类型                     
            $xq = $active == 1 ? "一键设置允许唯品会所有商品进行库存同步；" : "一键设置禁止唯品会所有商品进行库存同步；"; //操作日志                                          
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $xq);
            load_model('sys/OperateLogModel')->insert($log);
            return $ret;
        } else {
            $ret['status'] = '-1';
            $ret['message'] = '一键设置失败';
            return $ret;
        }
    }

    function update_inv($shop_code = '', $barcode_inv = array(), $is_increment = 1) {
        $where = "1";
        if (!empty($shop_code)) {
            // $where .= " AND api_goods.shop_code ='{$shop_code}' ";
            $where .= " AND api_goods_sku.shop_code ='{$shop_code}' ";
        }
        foreach ($barcode_inv as $inv) {
            if (!empty($inv)) {
                /*
                  $sql = "update api_goods_sku,api_goods set api_goods_sku.inv_num = {$inv['num']},api_goods_sku.inv_update_time=now(),api_goods_sku.sys_update_time='{$inv['inv_update_time']}'
                  where {$where} AND api_goods_sku.goods_barcode='{$inv['barcode']}' ";
                  if($is_increment==1){
                  $sql .="  AND api_goods_sku.sys_update_time<'{$inv['inv_update_time']}'";
                  } */

                //echo $sql;
                $sql = "update api_goods_sku set api_goods_sku.inv_num = {$inv['num']},api_goods_sku.inv_update_time=now(),api_goods_sku.sys_update_time='{$inv['inv_update_time']}'
                where {$where} AND api_goods_sku.goods_barcode='{$inv['barcode']}' ";
                if ($is_increment == 1) {
                    $sql .="  AND api_goods_sku.sys_update_time<'{$inv['inv_update_time']}'";
                }
                $this->db->query($sql);
            }
        }
        return true;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $param
     * @param unknown_type $type 1:获取数量 2:获取数据
     */
    function get_sku_num_or_data($param, $type = 1) {
        $db = $GLOBALS['context']->db;
        $num = 100;
        if ($type == 1) {
            $sql = "select count(*) from api_goods t1
        			left join api_goods_sku t2 on t1.goods_from_id = t2.goods_from_id
        			where 1=1 and t1.is_allow_sync_inv=1 and t2.is_allow_sync_inv=1";
        } else {
            $sql = "select * from api_goods t1
        			left join api_goods_sku t2 on t1.goods_from_id = t2.goods_from_id
        			where 1=1 and t1.is_allow_sync_inv=1 and t2.is_allow_sync_inv=1";
        }

        if (isset($param['shop_code'])) {
            $sql .= " and t1.shop_code in (" . deal_strs_with_quote($param['shop_code']) . ")";
        }
        if (isset($param['shop_code'])) {
            $sql .= " and t1.source in (" . deal_strs_with_quote($param['platform']) . ")";
        }
        if (isset($param['status'])) {
            $sql .= " and t1.status = " . $param['status'];
        }
        if (isset($param['goods_inv_status'])) {
            $sql .= " and t2.inv_update_time > t2.inv_up_time";
        }
        if ($type == 1) {
            return $db->get_value($sql);
        } else {
            return $db->get_limit($sql, array(), $num, ($param['page'] - 1) * $num);
        }
    }

    function sync_goods_inv($request) {
        $msg = "";
        if($request['api_name']=='weipinhuijit'){//唯品会商品列表，sku级库存同步
            $sql = "select source,shop_code,goods_from_id,goods_barcode from api_goods_sku  where api_goods_sku_id='{$request['id']}' and status=1";
            $sku = $this->db->get_row($sql);
            if (empty($sku)) {
                return $this->format_ret(-1, '', '商品的条码在平台已不存在');
            }
            $barcode_arr[] =$sku['goods_barcode'];
            $ret_inv = load_model('api/BaseInvModel')->update_inv_increment($sku['shop_code'], $barcode_arr, 1, 1);
            if($ret_inv['status']<1){
                return $ret_inv;
            }
            $ret = $this->sync_goods_inv_action($sku);
            if ($ret['status'] == '-1') {
                $msg .= $ret['message'];
            }
        }else{
            $id=$request['id'];
            $id_arr = explode(",", $id);
            $id_str = "'" . implode("','", $id_arr) . "'";
            $sql = "select is_allow_onsale,status,goods_from_id,source,shop_code from $this->table where api_goods_id in($id_str)";
            $goods = $this->db->get_all($sql);
            foreach ($goods as $row) {
                $sql2 = "select goods_barcode from api_goods_sku  where goods_from_id in ('" . $row['goods_from_id'] . "') and status=1";
                $v = $this->db->get_all($sql2);
                if (empty($v)) {
                    return $this->format_ret(-1, '', '商品的条码在平台已不存在');
                }
                $barcode_arr = array();
                $shop_code = $row['shop_code'];
                foreach ($v as $key => $value) {
                    $barcode_arr[] = $value['goods_barcode'];
                }
                $ret_inv =load_model('api/BaseInvModel')->update_inv_increment($shop_code, $barcode_arr, 1, 1);
                if($ret_inv['status']<1){
                    return $ret_inv;
                }
                $ret = $this->sync_goods_inv_action($row);
                if ($ret['status'] == '-1') {
                    $msg .= $ret['message'];
                }
            }
        }

        if (!empty($msg)) {
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '', '同步成功！');
    }

    function sync_goods_inv_action($goods) {
        $api_name = $goods['source'];
        if (in_array($goods['source'], array('meilishuo', 'mogujie'))) {
            $api_name = 'xiaodian';
        } else if ($goods['source'] == 'weipinhui') {
            $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
            $shop_api = $this->db->get_row($sql, array(':shop_code' => $goods['shop_code']));
            $shop_api = json_decode($shop_api['api'], TRUE);
            if ($shop_api['type'] == 'JIT') {
                $api_name = 'weipinhuijit';
            }
        }

        $fun = $api_name . '_api/item_quantity_sync';

        if($api_name = 'weipinhuijit'&&!empty($goods['goods_barcode'])){
            $params = array('shop_code' => $goods['shop_code'],'goods_barcode'=>$goods['goods_barcode'], 'goods_from_id' => $goods['goods_from_id']);
        }else{
            $params = array('shop_code' => $goods['shop_code'], 'goods_from_id' => $goods['goods_from_id']);
        }
        $result = load_model('sys/EfastApiModel')->request_api($fun, $params);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '同步成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }

    function activity_goods_sync_goods_inv_action($goods) {

        foreach ($goods as $val) {
            if($val['num']<0){
                continue;
            }
            $params = array('shop_code' => $val['shop_code'], 'goods_from_id' => $val['goods_from_id'], 'sku_id' => $val['sku_id'], 'num' => $val['num']);
            $api_name = ($val['source'] == 'taobao') ? 'taobao' : $val['source'];
            if( $params['goods_from_id'] == $val['sku_id'] ){
                unset($val['sku_id']);
            }
            
            $fun = $api_name . '_api/item_quantity_sync_one';
            $result = load_model('sys/EfastApiModel')->request_api($fun, $params);
        }
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '同步成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }

    /**
     * 预售计划库存同步
     * @param array $goods 库存数据
     * @return array 同步结果
     */
    function presell_goods_sync_inv_action($goods) {
        $api_name = $goods['source'];
        if (in_array($api_name, array('meilishuo', 'mogujie'))) {
            $api_name = 'xiaodian';
        } else if ($api_name == 'weipinhui') {
            $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
            $shop_api = $this->db->get_row($sql, array(':shop_code' => $goods['shop_code']));
            $shop_api = json_decode($shop_api['api'], TRUE);
            if ($shop_api['type'] == 'JIT') {
                $api_name = 'weipinhuijit';
            }
        }

        $fun = $api_name . '_api/item_quantity_sync_one';
        $log_arr = array();
        foreach ($goods['goods'] as $val) {
            $params = array('shop_code' => $val['shop_code'], 'goods_from_id' => $val['goods_from_id'], 'sku_id' => $val['sku_id'], 'num' => $val['num']);
            if ($val['goods_from_id'] == $val['sku_id']) {
                $params['sku_id'] = NULL;
            }

            $result = load_model('sys/EfastApiModel')->request_api($fun, $params);

            $log = $val;
            if ($result['resp_data']['code'] == '0') {
                $log['result'] = '同步成功';
            } else {
                $log['result'] = $result['resp_data']['msg'];
            }
            $log_arr[] = $log;
        }

        load_model('op/presell/PresellLogModel')->insert_sync_log($log_arr);
        if ($result['resp_data']['code'] == '0') {
            $ret['status'] = '1';
            $ret['message'] = '同步成功';
        } else {
            $ret['status'] = '-1';
            $ret['message'] = $result['resp_data']['msg'];
        }
        return $ret;
    }

    function update_goods_onsale($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $data = $this->db->create_mapper('api_goods')->update(array('is_allow_onsale' => $active), array('api_goods_id' => $id));
        //添加系统日志
        $module = '网络订单'; //模块名称
        $sql = " select goods_code from api_goods  where api_goods_id = '{$id}'";
        $val = $this->db->get_row($sql);
        $yw_code = $val["goods_code"];
        $operate_type = '编辑'; //操作类型
        if ($active == 1) {
            $log_xq = "设置允许上架";
        } else {
            $log_xq = "设置禁止上架";
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);
        return $this->format_ret(1);
    }

    //批量设置允许上架
    function p_update_onsale($active, $id) {
        $str = str_replace(",", "','", $id);
        $sql2 = " update  api_goods set is_allow_onsale = '$active'  where goods_from_id in ('" . $str . "')";
        $ret2 = $this->db->query($sql2);
        if ($ret2 === true) {
            $ret['status'] = '1';
            $ret['data'] = '';
            $ret['message'] = '批量设置成功';

            //添加系统日志
            $module = '网络订单'; //模块名称
            $sql3 = " select goods_code from api_goods  where goods_from_id in ('" . $str . "')";
            $code = $this->db->get_all($sql3);
            $yw = array();
            foreach ($code as $key => $value) {
                $yw[] = $value['goods_code'];
            }
            $yw_code = $yw[0];    //业务编码
            $operate_type = '编辑'; //操作类型
            if ($active == 1) {
                $log_xq = "设置允许上架";
            } else {
                $log_xq = "设置禁止上架";
            }
            $p = array_chunk($yw, 10, true);
            foreach ($p as $key => $value) {
                $v = implode(',', $value);
                $xq = $v . ',' . $log_xq;
                $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $xq);
                load_model('sys/OperateLogModel')->insert($log);
            }
            return $ret;
        } else {
            $ret['status'] = '-1';
            $ret['data'] = '';
            $ret['message'] = '批量设置失败';
            return $ret;
        }
    }

    //内置自动服务  删除平台已删除的商品数据
    function do_delete_goods() {
        $sql = "select rl.api_goods_id,r2.goods_from_id FROM {$this->table} rl LEFT JOIN api_goods_sku r2 on rl.goods_from_id = r2.goods_from_id WHERE r2.status=0";
        $api_goods_ids = $this->db->get_all($sql);
        $del_sql = "DELETE FROM api_goods_sku WHERE status=0";
        $this->db->query($del_sql);
        foreach ($api_goods_ids as $key => $value) {
            $is_sku = "SELECT goods_from_id FROM api_goods_sku WHERE  goods_from_id='{$value['goods_from_id']}' and status=1";
            if (empty($this->db->get_value($is_sku, array(':goods_from_id' => $value['goods_from_id'])))) {
                $del_sql_1 = "DELETE FROM api_goods WHERE goods_from_id='{$value['goods_from_id']}'";
                $this->db->query($del_sql_1);
            }
        }

        //删除淘分销商品
        $fenxiao_sql = "SELECT r1.pid FROM api_taobao_fx_product AS r1 LEFT JOIN api_taobao_fx_product_sku AS r2 ON r1.pid=r2.pid WHERE r2.sku_status=0";
        $fenxiao_data = $this->db->get_all($fenxiao_sql);
        //删除明细
        $fenxiao_del_sql = "DELETE FROM api_taobao_fx_product_sku WHERE sku_status=0";
        $this->query($fenxiao_del_sql);
        //删除明细为空的主单
        if(!empty($fenxiao_data)){
            foreach ($fenxiao_data as $fenxiao_value) {
                $sql_value = array();
                $sku_sql = "SELECT 1 FROM api_taobao_fx_product_sku WHERE pid=:pid AND sku_status=:sku_status";
                $sql_value[':pid'] = $fenxiao_value['pid'];
                $sql_value[':sku_status'] = 1;
                $result = $this->db->get_row($sku_sql, $sql_value);
                if (empty($result)) {
                    $sql_main_value = array();
                    $del_main_sql = "DELETE FROM api_taobao_fx_product WHERE pid=:pid";
                    $sql_main_value['pid'] = $fenxiao_value['pid'];
                    $this->query($del_main_sql, $sql_main_value);
                }
            }
        }

    }

    /**
     * @todo 删除平台记录
     */
    function do_delete($api_goods_id) {
        $sql = "SELECT * FROM {$this->table} where api_goods_id = :api_goods_id";
        $data = $this->db->get_row($sql, array(":api_goods_id" => $api_goods_id));
        $goods_from_id = $data['goods_from_id'];

        $this->begin_trans();
        $ret_main = parent::delete(array('api_goods_id' => $api_goods_id));
        if ($ret_main['status'] != 1) {
            $this->rollback();
            return $ret_main;
        }
        $ret_detail = $this->delete_exp('api_goods_sku', array('goods_from_id' => "{$goods_from_id}"));
        if ($ret_detail == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败');
        }
        $this->commit();
        //添加操作日志
        $log_xq = '删除平台商品，平台商品ID：' . $goods_from_id;
        $this->add_operate_log('网络订单', $goods_from_id, '删除', $log_xq);
        return $this->format_ret(1, '', '删除成功');
    }

    /**
     * @下载商品
     */
    function down_goods($request) {
        $params = array();
        $params['sale_channel_code'] = $request['sale_channel_code'];
        $params['shop_code'] = $request['shop_code'];
        $params['start_time'] = $request['start_time'];
        $params['end_time'] = $request['end_time'];
        $params['method'] = 'item_sync';

        if(!empty($request['shop_code'])&&$request['sale_channel_code']=='weipinhui'){
            $sql = "SELECT api FROM base_shop_api WHERE shop_code=:shop_code";
            $shop_api = $this->db->get_row($sql, array(':shop_code' => $request['shop_code']));
            $shop_api = json_decode($shop_api['api'], TRUE);
            if ($shop_api['type'] == 'JIT') {
                $params['sale_channel_code'] = 'weipinhuijit';
            }
        }

        $result = load_model('sys/EfastApiTaskModel')->request_api('sync', $params);

        return $result;
    }

    //下载进度
    function down_goods_check($request) {
        $params = array();
        $params['task_sn'] = $request['task_sn'];
        $result = load_model('sys/EfastApiTaskModel')->request_api('check', $params);

        return $result;
    }

    /*
     * 添加系统操作日志
     * */

    function add_operate_log($module, $yw_code, $operate_type, $operate_xq) {
        $log = array(
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'ip' => '', 'add_time' => date('Y-m-d H:i:s'),
            'module' => $module,
            'yw_code' => $yw_code,
            'operate_type' => $operate_type,
            'operate_xq' => $operate_xq
        );
        $ret = load_model('sys/OperateLogModel')->insert($log);
        return $ret;
    }

    /**
     * @todo 批量删除平台记录
     */
    function batch_delete($request) {
        $api_goods_id_arr = explode(',', $request['api_goods_id']);
        $goods_from_id_arr = array();
        $this->begin_trans();
        foreach ($api_goods_id_arr as $api_goods_id) {
            $sql = "SELECT * FROM {$this->table} where api_goods_id = :api_goods_id";
            $data = $this->db->get_row($sql, array(":api_goods_id" => $api_goods_id));
            $goods_from_id = $data['goods_from_id'];
            $ret_main = parent::delete(array('api_goods_id' => $api_goods_id));
            if ($ret_main['status'] != 1) {
                $this->rollback();
                return $ret_main;
            }
            $ret_detail = $this->delete_exp('api_goods_sku', array('goods_from_id' => "{$goods_from_id}"));
            if ($ret_detail == false) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败');
            }
            $goods_from_id_arr[] = $data['goods_from_id'];
        }
        $this->commit();
        //添加操作日志
        $goods_from_id_group = array_chunk($goods_from_id_arr, 10, true);
        foreach ($goods_from_id_group as $key => $value) {
            $group = implode(',', $value);
            $goods_log = '批量删除平台商品，平台商品ID：' . $group;
            $this->add_operate_log('网络订单', $goods_from_id, '批量删除', $goods_log);
        }
        return $this->format_ret(1, '', '删除成功!');
    }

    /**
     * 修改关联编码
     */
    function update_goods_barcode($params) {
        $params['goods_barcode'] = trim($params['goods_barcode']);
        if (empty($params['goods_barcode'])) {
            return $this->format_ret(3, '', '平台商品编码不能为空');
        }
        $result = $this->is_exist_barcode($params['goods_barcode']);
        if ($result['status'] != 1) {
            return $result;
        }
        $sql_sku = "SELECT goods_barcode FROM api_goods_sku WHERE api_goods_sku_id = :api_goods_sku_id";
        $sql_sku_value = array(":api_goods_sku_id" => $params['api_goods_sku_id']);
        $sku_info = $this->db->get_row($sql_sku, $sql_sku_value);
        if ($sku_info['goods_barcode'] !== $params['goods_barcode']) {
            $data = array('goods_barcode' => $params['goods_barcode']);
            $where = array('api_goods_sku_id' => $params['api_goods_sku_id']);
            $ret = $this->db->update('api_goods_sku', $data, $where);
            if ($ret) {
                //添加日志
                $goods_log = '修改商品平台规格编码，' . $sku_info['goods_barcode'] . '修改为' . $params['goods_barcode'];
                $this->add_operate_log('平台商品列表', $sku_info['goods_from_id'], '修改', $goods_log);
                return $this->format_ret(1, '', '');
            } else {
                return $this->format_ret(-1, '', '修改失败！');
            }
        } else {
            return $this->format_ret(2, '', '');
        }
    }

    /**
     * @todo 判断条码是否存在于系统中
     */
    function is_exist_barcode($input_barcode) {
        //判断api商品sku表
        $sql = "SELECT COUNT(1) FROM goods_sku WHERE barcode=:barcode";
        $sql_value = array(":barcode" => $input_barcode);
        $result = $this->db->get_value($sql, $sql_value);
        if (empty($result)) {
            //判断商品套餐条码
            $combo_sql = "SELECT COUNT(1) FROM goods_combo_barcode WHERE barcode=:barcode";
            $combo_result = $this->db->get_value($combo_sql, $sql_value);
            if (empty($combo_result)) {
                //判断子条码
                $child_sql = "SELECT COUNT(1) FROM goods_barcode_child WHERE barcode=:barcode";
                $child_result = $this->db->get_value($child_sql, $sql_value);
                if (empty($child_result)) {
                    return $this->format_ret(-1, '', '系统中不存在此条码！');
                }
            }
        }
        return $this->format_ret(1);
    }

    /**
     * 获取被预售计划标记的平台预售商品信息
     * @param array $shop_arr 店铺代码
     * @param array $barcode_arr 条码
     * @return array
     */
    public function get_pt_presell_goods_info($shop_arr, $barcode_arr) {
        $sql_values = array();
        $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'goods_barcode', $sql_values);
        $sql = "SELECT source,shop_code,goods_barcode AS barcode,goods_from_id,sku_id FROM api_goods_sku
                WHERE sale_mode='presale' AND shop_code IN({$shop_str}) AND goods_barcode IN({$barcode_str})";
        return $this->db->get_all($sql, $sql_values);
    }
    
    
    function get_sku_info_by_id($id){
        $sql = "select * from api_goods_sku where api_goods_sku_id=:id";
        return $this->db->get_row($sql,array(':id'=>$id));
    }

    //平台商品列表excel导入
    public function goods_import($shop_code, $goods_name, $sku_id,$goods_barcode) {
        if(empty($shop_code) || empty($goods_name) || empty($sku_id) || empty($goods_barcode)) {
            return array('status' => '-1', 'message' => '商店代码、商品名称、平台id、商品条形码都不能为空');
        }

        $sql_shop_code = "SELECT shop_code FROM base_shop WHERE shop_code = :shop_code";
        $sql_shop_code_value = array(":shop_code" => $shop_code);
        $_shop_code_ = $this->db->get_row($sql_shop_code, $sql_shop_code_value);
        if(empty($_shop_code_['shop_code'])){
            return array('status' => '-1', 'message' => $shop_code.'商店代码在系统中不存在');
        }

        $result = $this->is_exist_barcode($goods_barcode);
        if ($result['status'] != 1) {
            return array('status' => '-1', 'message' => $goods_barcode.'商品条形码在系统中不存在');
        }

        $sql_shop_api = "SELECT source,api FROM base_shop_api WHERE shop_code = :shop_code";
        $sql_shop_api_value = array(":shop_code" => $shop_code);
        $_shop_api_ = $this->db->get_row($sql_shop_api, $sql_shop_api_value);
        $seller_nick =  json_decode($_shop_api_['api'],true);
        if(isset($seller_nick['shop_nick'])){
            $shop_nick = $seller_nick['shop_nick'];
        }else{
            $shop_nick ='';
        }
        //商品和条码判断存在更新，不存在插入
        $sql = "INSERT INTO api_goods(`shop_code`,`goods_name`,`goods_from_id`,`source`,`seller_nick`,`goods_code`,`has_sku`) VALUES ('{$shop_code}','{$goods_name}','{$sku_id}','{$_shop_api_['source']}','{$shop_nick}','{$sku_id}',1) ON DUPLICATE KEY UPDATE  goods_name='{$goods_name}'";
        $this->db->query($sql);

        $sql_api_goods_sku = "INSERT INTO api_goods_sku(`goods_from_id`,`shop_code`,`sku_id`,`goods_barcode`,`source`) VALUES('{$sku_id}','{$shop_code}','{$sku_id}','{$goods_barcode}','{$_shop_api_['source']}') ON DUPLICATE KEY UPDATE  goods_barcode='{$goods_barcode}'";
        $this->db->query($sql_api_goods_sku);
        return array('status' => '1', 'message' => '导入成功');
    }
    function create_import_fail_files($msg, $fail_top, $filename) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg as $key => $val) {
            $val_data = array($key, $val);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5($filename . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }


    /**
     * 唯品会批量库存同步
     * @param $request
     * @return array
     */
    function weipinhui_multi_sync_goods_inv($request) {
        $error_msg = array();//记载错误信息
        $params = array();
        $params['api_name'] = 'weipinhuijit';
        $api_goods_sku_id_arr = explode(',', $request['id']);
        $sql_value = array();
        $api_goods_sku_id_str = $this->arr_to_in_sql_value($api_goods_sku_id_arr, 'api_goods_sku_id', $sql_value);
        $sql = "SELECT api_goods_sku_id,goods_barcode FROM api_goods_sku WHERE api_goods_sku_id IN({$api_goods_sku_id_str}) AND status=1";
        $goods_data = $this->db->get_all($sql, $sql_value);
        if (empty($goods_data)) {
            return $this->format_ret('-1', '', '商品信息不存在！');
        }
        $api_goods_info = load_model('util/ViewUtilModel')->get_map_arr($goods_data, 'api_goods_sku_id');
        foreach ($api_goods_sku_id_arr as $api_goods_sku_id) {
            $params['id'] = $api_goods_sku_id;
            //调用单个库存同步方法
            $ret = $this->sync_goods_inv($params);
            if ($ret['status'] != 1) {
                $goods_barcode = $api_goods_info[$api_goods_sku_id]['goods_barcode'];
                $error_msg[] = array($goods_barcode . "\t" => $ret['message']);
            }
        }
        //错误信息导出
        if (!empty($error_msg)) {
            $sum = count($api_goods_sku_id_arr);
            $error_num = count($error_msg);
            $success = $sum - $error_num;
            $msg = $this->create_fail_file($error_msg);
            return $this->format_ret(-1, '', '同步成功:' . $success . ', 失败:' . $error_num . $msg);
        }
        return $this->format_ret(1, '', '同步成功！');
    }


    function create_fail_file($error_msg) {
        $fail_top = array('平台商品条码', '错误信息');
        $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
        $message = '';
//        $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }


}
