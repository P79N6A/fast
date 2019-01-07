<?php

require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');

class GoodsModel extends TbModel {

    function get_table() {
        return 'api_taobao_goods';
    }

    function get_by_page($filter) {


        $sql_join = "";
        //$sql_main = "FROM {$this->table} rl  WHERE 1";
        $sql_main = "FROM {$this->table} rl LEFT JOIN api_taobao_sku r2 on rl.goods_from_id = r2.goods_from_id WHERE 1";
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
        //商品状态
        if (isset($filter['status']) && !empty($filter['status'])) {
            $sql_main .= " AND rl.status in (:status) ";
            $sql_values[':status'] = $filter['status'];
        }

        //
        //销售平台
        if (isset($filter['source']) && !empty($filter['source'])) {
            $sql_main .= " AND rl.source = in (:sale_channel_code) ";
            $sql_values[':sale_channel_code'] = $filter['source'];
        }

        //店铺
        if (isset($filter['shop_code']) && !empty($filter['shop_code'])) {
            $sql_main .= " AND rl.shop_code in (:shop_code) ";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //增值服务
        $sql_main .= load_model('base/SaleChannelModel')->get_values_where('rl.source');
        $select = 'rl.*';
        //echo $sql_main;
        $sql_main .= " group by rl.goods_from_id ";
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        //$data =  $this->get_page_from_sql($filter, $sql_main,$sql_values, $select);
        filter_fk_name($data['data'], array('shop_code|shop',));
        //print_r($data);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    //根据id查询
    function get_by_id($id) {
        return $this->get_row(array('num_iid' => $id));
    }

    function update_active($active, $id) {

        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('is_inv_sync' => $active), array('api_goods_id' => $id));
        return $ret;
    }

    //铺货列表
    function get_ph_by_page($filter) {
        $sql_main = "FROM {$this->table} rl LEFT JOIN api_taobao_sku r2 on rl.num_iid = r2.num_iid WHERE 1";
        $sql_values = array();
        //过滤店铺权限
        $filter_shop_code = isset($filter['shop_code']) ? $filter['shop_code'] : null;
        $sql_main .= load_model('base/ShopModel')->get_sql_purview_shop('rl.shop_code', $filter_shop_code);
        //商品编码
        if (isset($filter['outer_id']) && $filter['outer_id'] != '') {

            $sql_main .= " AND rl.outer_id LIKE :outer_id ";
            $sql_values[':outer_id'] = '%' . $filter['outer_id'] . '%';
        }
        //商品条形码
        if (isset($filter['sku_outer_id']) && $filter['sku_outer_id'] != '') {

            $sql_main .= " AND r2.outer_id LIKE :sku_outer_id ";
            $sql_values[':sku_outer_id'] = '%' . $filter['sku_outer_id'] . '%';
        }
        //货号填写状态
        if (isset($filter['goods_code_status']) && $filter['goods_code_status'] != '' && $filter['goods_code_status'] != 'all') {
            if ($filter['goods_code_status'] == 0) {
                $sql_main .= " AND (rl.outer_id is null or rl.outer_id='') ";
            } elseif ($filter['goods_code_status'] == 1) {
                $sql_main .= " AND (rl.outer_id is not null or rl.outer_id!='')";
            }
        }
        //商家编码填写状态
        if (isset($filter['sku_status']) && $filter['sku_status'] != '' && $filter['sku_status'] != 'all') {
            if ($filter['sku_status'] == 0) {
                $sql_main .= " AND (r2.outer_id is null or r2.outer_id='') ";
            } elseif ($filter['sku_status'] == 1) {
                $sql_main .= " AND (r2.outer_id is not null or r2.outer_id!='')";
            }
        }

        //商家编码填写状态
        if (isset($filter['approve_status']) && $filter['approve_status'] != '' && $filter['approve_status'] != 'all') {
            if ($filter['approve_status'] == 0) {
                $sql_main .= " AND rl.approve_status='instock' ";
            } elseif ($filter['approve_status'] == 1) {
                $sql_main .= " AND rl.approve_status='onsale' ";
            }
        }
        //商品绑定状态
        if (isset($filter['relation_status']) && $filter['relation_status'] != '' && $filter['relation_status'] != 'all') {
            $sql_main .= " AND rl.is_relation={$filter['relation_status']} ";
        }
        //sku绑定状态
        if (isset($filter['sku_relation_status']) && $filter['sku_relation_status'] != '' && $filter['sku_relation_status'] != 'all') {
            $sql_main .= $filter['sku_relation_status'] == 1 ? " AND r2.is_relation <> 0 " : " AND r2.is_relation = 0 ";
        }
        if ($filter['ctl_type'] == 'export') {
            return $this->goods_ph_export_csv($sql_main, $sql_values, $filter);
        }
        $select = 'rl.*,count(r2.num_iid) as sku_num';
        $sql_main .= " group by rl.num_iid order by rl.modified desc ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        filter_fk_name($data['data'], array('shop_code|shop',));
        foreach ($data['data'] as &$row) {
            $row['approve_status_txt'] = $row['approve_status'] == 'onsale' ? '在售' : '在库';
            $row['has_sku'] = $row['sku_num'] == 0 ? '否' : '是';
            if (empty($row['outer_id'])) {
                $row['outer_id'] = '未填写';
                $row['outer_id'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function goods_ph_export_csv($sql_main, $sql_values, $filter) {
        $sql = "select rl.is_relation,rl.num_iid,rl.title,rl.outer_id,rl.price,rl.approve_status,rl.num,rl.modified,r2.sku_id,r2.outer_id as sku_outer_id,r2.properties_name,r2.price as sku_price,r2.quantity,r2.last_update_time,r2.is_relation as sku_is_relation ";
        $sql .= $sql_main . " order by rl.modified desc";
        //echo $sql;die;
        $data = $this->db->get_all($sql, $sql_values);
        foreach ($data as &$row) {
            $row['is_relation_txt'] = $row['is_relation'] == '1' ? '是' : '否';
            $row['sku_is_relation_txt'] = $row['sku_is_relation'] == '1' ? '是' : '否';
            $row['approve_status_txt'] = $row['approve_status'] == 'onsale' ? '在售' : '在库';
        }
        $ret['data'] = $data;
        return $this->format_ret(1, $ret);
    }

    function get_sku_list_by_num_iid($num_iid) {
        $sql = "select * from api_taobao_sku where num_iid='$num_iid'";
        $result = $this->db->get_all($sql);
        foreach ($result as &$row) {
            if (empty($row['outer_id'])) {
                $row['outer_id'] = '未填写';
                $row['outer_id'] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        return $result;
    }

    function get_sku_by_skuid($sku_id) {
        $sql = "select * from api_taobao_sku where sku_id='$sku_id'";
        $result = $this->db->get_row($sql);
        return $result;
    }

    function update_outer_id($num_iid, $outer_id, $sku_id = "") {
        /*
          $no_find = true;
          $sql = "select count(1) from goods_barcode where barcode='{$outer_id}'";
          $num = $this->db->getOne($sql);
          if ($num > 0){
          $no_find = false;
          } else {
          $sql = "select count(1) from goods_barcode_child where barcode='{$outer_id}'";
          $num = $this->db->getOne($sql);
          if ($num > 0){
          $no_find = false;
          } else {
          if (empty($sku_id)){
          $sql = "select count(1) from base_goods where goods_code='{$outer_id}'";
          $num = $this->db->getOne($sql);
          if ($num > 0){
          $no_find = false;
          }
          }
          }
          } */
        $type = "sku";
        if (empty($sku_id)) {
            //是否为通色通码
            $sql = "select count(1) from api_taobao_sku where num_iid='{$num_iid}'";
            $sku_count = $this->db->getOne($sql);
            if ($sku_count == 0) {
                $type = "goods";
            }
        }
        $find_ret = $this->find_barcode($outer_id, $type);
        if ($find_ret['status'] == -1) {
            return $this->format_ret(-1, '', '输入的商家编码没有在系统的商品编码或商品条形码或商品子条码中找到');
        }
        //更新淘宝后台条码
        if (empty($sku_id)) {
            $goods_ret = $this->get_by_id($num_iid);
            $data_row = $goods_ret['data'];
        } else {
            $data_row = $this->get_sku_by_skuid($sku_id);
        }
        $shop_api = load_model('base/ShopApiModel')->get_shop_api_by_shop_code($data_row['shop_code']);
        //天猫店铺
        if (strtoupper($shop_api['api']['shop_type']) == 'B') {
            $api_method = "taobao_api/tmall_item_update";
            $params = array('shop_code' => $data_row['shop_code'], 'num_iid' => $data_row['num_iid'], 'outer_id' => $outer_id);
            if (!empty($sku_id)) {
                $params['sku_id'] = $sku_id;
            }
        } else {
            //淘宝集市店商家编码更新
            $api_method = "taobao_api/taobao_item_update";
            $params = array('shop_code' => $data_row['shop_code'], 'num_iid' => $data_row['num_iid'], 'outer_id' => $outer_id);
            if (!empty($sku_id)) {
                $params['properties'] = $data_row['properties'];
                $params['quantity'] = $data_row['inv_num'];
                $params['price'] = $data_row['price'];
                $api_method = "taobao_api/taobao_item_sku_update";
            }
        }

        $result = load_model('sys/EfastApiModel')->request_api($api_method, $params);

        if ($result['resp_data']['code'] !== 0) {
            $msg = '失败,' . $result['resp_data']['msg'];
            return $this->format_ret(-1, '', $msg);
        }
        if (empty($sku_id)) {
            $sql = "update api_taobao_goods set outer_id='{$outer_id}' where num_iid='{$num_iid}'";
        } else {
            $sql = "update api_taobao_sku set outer_id='{$outer_id}' where sku_id='{$sku_id}'";
        }
        $this->db->query($sql);
        return $this->format_ret(1, '');
    }

    function do_goods_init($shop_code) {
        $sql = "call tools_goods_init_from_taobao_shop('{$shop_code}')";
        $this->db->query($sql);

        $sql = "select sys_goods_barcode,goods_from_id,sku_id,sku_properties,price,inv_num from api_goods_sku where shop_code=:shop_code and sys_goods_barcode!='' and goods_barcode=''";
        $init_ret = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        $shop_api = load_model('base/ShopApiModel')->get_shop_api_by_shop_code($shop_code);
        $shop_type = strtoupper($shop_api['api']['shop_type']);


        if ($shop_type == 'B') {
            //天猫店铺
            $api_method = "taobao_api/tmall_item_update";
        } else {
            //淘宝集市店商家编码更新
            $api_method = "taobao_api/taobao_item_update";
        }
        $msg = "";
        foreach ($init_ret as $init_row) {
            $params = array('shop_code' => $shop_code,
                'num_iid' => $init_row['goods_from_id'],
                'outer_id' => $init_row['sys_goods_barcode'],
            );
            //非通色通码
            if ($init_row['goods_from_id'] != $init_row['sku_id']) {
                if ($shop_type == 'B') {
                    //天猫店铺
                    $params['sku_id'] = $init_row['sku_id'];
                } else {
                    //淘宝集市店商家编码更新
                    $params['properties'] = $init_row['sku_properties'];
                    $params['price'] = $init_row['price'];
                    $params['quantity'] = $init_row['inv_num'];
                    $api_method = "taobao_api/taobao_item_sku_update";
                }
            }

            $result = load_model('sys/EfastApiModel')->request_api($api_method, $params);

            if ($result['resp_data']['code'] !== 0) {
                $msg .= '失败,' . $result['resp_data']['msg'];
                continue;
            }
            $data = array('goods_barcode' => $init_row['sys_goods_barcode']);
            $this->db->update('api_goods_sku', $data, array('sku_id' => $init_row['sku_id'], 'shop_code' => $shop_code));
        }
        if (!empty($msg)) {
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '');
    }

    function get_gg_info($properties_name) {
        $pro_arr = explode(';', $properties_name);
        $gg_name_arr = array('gg1' => array('颜色'),
            'gg2' => array('尺码')
        );
        $tb_gg = array();
        foreach ($pro_arr as $pro) {
            foreach ($gg_name_arr as $gg_key => $gg) {
                foreach ($gg as $gg_name) {
                    if (strpos($pro, $gg_name) !== false) {
                        $gg_pro = explode(':', $pro);
                        $len = count($gg_pro);
                        $tb_gg[$gg_key] = $gg_pro[$len - 1];
                        break 2;
                    }
                }
            }
        }
        return $tb_gg;
    }

    //条码是否在系统中存在
    function find_barcode($outer_id, $type = 'sku') {
        $find = -1;
        //商家编码 goods_code
        if ($type != 'sku') {
            $sql = "select count(1) from base_goods where goods_code='{$outer_id}'";
            $num = $this->db->getOne($sql);
            if ($num > 0) {
                $find = 1;
            }
            return $this->format_ret($find, '');
        }
        //sku商家编码
        $sql = "select goods_code,spec1_code,spec2_code,sku,barcode from goods_sku where barcode='{$outer_id}'";
        $barcode_row = $this->db->getRow($sql);
        if (!empty($barcode_row)) {
            $find = 1;
        } else {
            $sql = "select goods_code,spec1_code,spec2_code,sku,barcode from goods_barcode_child where barcode='{$outer_id}'";
            $barcode_row = $this->db->getRow($sql);
            if (!empty($barcode_row)) {
                $find = 1;
            }
        }
        return $this->format_ret($find, $barcode_row, '');
    }

    //更新关联标识
    function update_relation($num_iid, $sku_id = "", $is_relation = 1, $msg = '') {
        if (empty($sku_id)) {
            $tb_name = "api_taobao_goods";
            $wh = " num_iid='{$num_iid}'";
        } else {
            $tb_name = "api_taobao_sku";
            $wh = " sku_id='{$sku_id}'";
        }
        $sql = "update $tb_name set is_relation=$is_relation,relation_msg='$msg' where $wh";
        $this->db->query($sql);
    }

    /**
      p_code varchar(50), 商品编码 不空
      p_name varchar(50), 商品名称 不空
      p_cat_name varchar(50), 商品分类名 不空
      p_brand_name varchar(50), 商品品牌名 可空
      p_price varchar(50), 商品价格 可空
      p_state int, 商品状态 status
      p_barcode varchar(50), 商品条形码 不空
      p_sku_desc varchar(150), 商品sku描述 不空
      p_sku_id varchar(50), 商品sku_id
      p_spec1_name varchar(50), 商品规格1名称 可空
      p_spec2_name varchar(50)) 商品规格2名称 可空
     */
    function opt_init_create($api_goods_sku_id) {
        $sql = "SELECT
                    r1.goods_code,r1.goods_name,r1.cat,r1.brand,r1.price,r1.status,r2.goods_barcode,r2.sku_properties_name,r2.sku_id
                FROM
                    api_goods r1
                INNER JOIN
                    api_goods_sku r2
                ON
                    r1.goods_from_id=r2.goods_from_id
                WHERE
                    r2.api_goods_sku_id=:api_goods_sku_id";
        $sql_value = array(":api_goods_sku_id" => $api_goods_sku_id);
        $ret = $this->db->get_row($sql, $sql_value);
        $call_str = "'" . join("','", $ret) . "'";
        $sqls = "call tools_goods_init_from_taobao({$call_str})";
        $res = $this->db->get_all($sqls);
        if (empty($res)) {
            return $this->format_ret(-1, '', '商品初始化失败');
        }
        $sku_sql = "SELECT
                   shop_code,goods_from_id,sys_goods_barcode,sku_id,sku_properties,goods_barcode, price, inv_num, 
                FROM
                    api_goods_sku
                WHERE
                    api_goods_sku_id=:api_goods_sku_id AND sys_goods_barcode!='' AND (goods_barcode='' or goods_barcode is NULL)";
        $sku_sql_value = array(":api_goods_sku_id" => $api_goods_sku_id);
        $init_ret = $this->db->get_row($sku_sql, $sku_sql_value);
        //若存在sys_goods_barcode，不存在goods_barcode,回写平台
        if (!empty($init_ret)) {
            $shop_api = load_model('base/ShopApiModel')->get_shop_api_by_shop_code($init_ret['shop_code']);
            $shop_type = strtoupper($shop_api['api']['tb_shop_type']);
            if ($shop_type == 'B') {
                //天猫店铺
                $api_method = "taobao_api/tmall_item_update";
            } else {
                //淘宝集市店商家编码更新
                $api_method = "taobao_api/taobao_item_update";
            }
            $msg = "";
            $params = array('shop_code' => $init_ret['shop_code'],
                'num_iid' => $init_ret['goods_from_id'],
                'outer_id' => $init_ret['sys_goods_barcode'],
            );
            //非通色通码
            if ($init_ret['goods_from_id'] != $init_ret['sku_id']) {
                if ($shop_type == 'B') {
                    //天猫店铺
                    $api_method = "taobao_api/tmall_item_update";
                    $params['sku_id'] = $init_ret['sku_id'];
                } else {
                    $api_method = "taobao_api/taobao_item_sku_update";
                    //淘宝集市店商家编码更新
                    $params['properties'] = $init_ret['sku_properties'];
                    $params['price'] = $init_ret['price'];
                    $params['quantity'] = $init_ret['inv_num'];
                }
            }
            $result = load_model('sys/EfastApiModel')->request_api($api_method, $params);
            if ($result['resp_data']['code'] !== 0) {
                $msg .= '失败,' . $result['resp_data']['msg'];
            }
            if (!empty($msg)) {
                return $this->format_ret(-1, '', $msg);
            }
            $data = array('goods_barcode' => $init_ret['sys_goods_barcode']);
            $this->db->update('api_goods_sku', $data, array('api_goods_sku_id' => $api_goods_sku_id));
        }
        $data = array('is_goods_init' => 1);
        $this->db->update('api_goods_sku', $data, array('api_goods_sku_id' => $api_goods_sku_id));
        return $this->format_ret(1, $init_ret['goods_from_id']);
    }

    /**
     * 商家编码匹配
     */
    function match_code() {
        try {
            $this->begin_trans();
            $arr = array('base_goods' => 1, 'goods_sku' => 2, 'goods_barcode_child' => 2);
            foreach ($arr as $k => $v) {
                $ret = $this->update_data($k, $v);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $ret;
                }
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            return $this->format_ret(-1, '', '数据操作失败');
        }
    }

    function match_spec() {
        $ret = $this->update_data('goods_sku', 3);
        return $ret;
    }

    /**
     * 获取查询匹配SQL
     */
    function get_match_sql($table, $type) {
        $sql = '';
        switch ($type) {
            case 1:
                $sql = "SELECT tg.num_iid FROM api_taobao_goods AS tg
                        INNER JOIN base_goods bg ON tg.outer_id=bg.goods_code
                        WHERE tg.is_relation=0";
                break;
            case 2:
                $sql = "SELECT ts.sku_id FROM api_taobao_sku AS ts
                        INNER JOIN {$table} gs ON ts.outer_id=gs.barcode
                        WHERE ts.is_relation=0";
                break;
            case 3:
                $sql = "SELECT ts.sku_id FROM api_taobao_sku AS ts
                        INNER JOIN goods_sku gs ON ts.outer_id=gs.barcode AND ts.spec1_name=gs.spec1_name AND ts.spec2_name=gs.spec2_name
                        WHERE ts.is_relation<>2";
                break;
            default:
                break;
        }
        return $sql;
    }

    /**
     * 更新匹配商品状态
     */
    function update_data($tab, $type) {
        $sql = $this->get_match_sql($tab, $type);
        if (empty($sql)) {
            return $this->format_ret(-1, '', '操作失败');
        }

        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return $this->format_ret(1, '', '本次操作未找到匹配的商品');
        }

        if ($type == 1) {
            $table = 'api_taobao_goods';
            $wh_fld = 'num_iid';
            $srccess_msg = '商家编码匹配成功';
            $fail_msg = '商家编码匹配失败';
            $status = 1;
        } else {
            $table = 'api_taobao_sku';
            $wh_fld = 'sku_id';
            $status = $type == 2 ? 1 : '2';
            $srccess_msg = $type == 2 ? 'SKU商家编码匹配成功' : '商品规格匹配成功';
            $fail_msg = $type == 2 ? 'SKU商家编码匹配失败' : '商品规格匹配失败';
        }
        $wh_data = deal_array_with_quote(array_column($data, $wh_fld));
        $this->begin_trans();
        $sql = "UPDATE {$table} SET is_relation='{$status}',relation_msg = '{$srccess_msg}' WHERE {$wh_fld} IN({$wh_data})";
        $ret = $this->db->query($sql);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret(-1, '', '匹配失败');
        }
        $sql = "UPDATE {$table} SET relation_msg = '{$fail_msg}' WHERE {$wh_fld} NOT IN({$wh_data}) AND is_relation=0";
        $ret = $this->db->query($sql);
        if (!$ret) {
            $this->rollback();
            return $this->format_ret(-1, '', '匹配失败');
        }
        $this->commit();
        return $this->format_ret(1);
    }

}
