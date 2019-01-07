<?php

require_lib('tb_xml');
require_model('tb/TbModel');
require_model('api/item/TbItemModel');

/**
 * 淘宝商品发布业务
 * @author wmh
 */
class TbGoodsIssueModel extends TbModel {

    protected $sku_table = 'api_tb_goods_sell_prop';

    function __construct() {
        parent::__construct('api_tb_goods_issue');
    }

    /**
     * 获取宝贝上新列表数据
     * @param array $filter 条件数组
     * @return array 结果集
     */
    function get_issue_goods_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} gi WHERE 1";

        //发布状态
        $issue_status = array('tabs_stay' => 0, 'tabs_fail' => 2, 'tabs_history' => 1);
        if (isset($filter['issue_status']) && array_key_exists($filter['issue_status'], $issue_status)) {
            $sql_main .= " AND gi.issue_status=:issue_status";
            $sql_values[':issue_status'] = $issue_status[$filter['issue_status']];
        }
        //信息完整状态
        $full_status = array('full_all', 'full_ok', 'full_not');
        if (isset($filter['full_status']) && in_array($filter['full_status'], $full_status)) {
            switch ($filter['full_status']) {
                case 'full_ok':
                    $sql_main .= " AND gi.is_base_full=1 && gi.is_item_full=1 && gi.is_spec_full=1";
                    break;
                case 'full_not':
                    $sql_main .= " AND (gi.is_base_full<>1 || gi.is_item_full<>1 || gi.is_spec_full<>1)";
                    break;
                default:
                    break;
            }
        }
        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND gi.shop_code=:shop_code";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //标题或编码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (gi.goods_code LIKE :code_name or gi.title LIKE :code_name)";
            $sql_values[':code_name'] = "%{$filter['code_name']}%";
        }
        $sql_main .=' ORDER BY gi.lastchanged DESC ';
        $select = 'gi.title,gi.goods_code,gi.shop_code,gi.price,gi.category_id,gi.issue_status,gi.is_base_full,gi.is_item_full,gi.is_spec_full,gi.fail_reason,gi.lastchanged';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        foreach ($data['data'] as &$val) {
            $val['full_state'] = $val['is_base_full'] == 1 && $val['is_item_full'] == 1 && $val['is_spec_full'] == 1 ? '完整' : '不完整';
            $val['is_base_full'] = $val['is_base_full'] == 1 ? '完成' : '未完成';
            $val['is_item_full'] = $val['is_item_full'] == 1 ? '完成' : '未完成';
            $val['is_spec_full'] = $val['is_spec_full'] == 1 ? '完成' : '未完成';
//            if ($val['issue_status'] == 1 || $val['full_state'] == '不完整') {
//                $val['disabled'] = true;
//            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取已发布的宝贝列表数据
     * @param array $filter 条件数组
     * @return array 结果集
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = ' INNER JOIN api_goods gd ON gi.shop_code=gd.shop_code AND gi.outer_id=gd.goods_code ';
        $sql_join .= ' LEFT JOIN api_tb_itemcats ti ON gi.category_id=ti.cid ';
        $sql_main = "FROM {$this->table} gi {$sql_join} WHERE gd.source='taobao'";

        //在售状态
        $shelf_status = array('tabs_sale' => 1, 'tabs_stock' => 0);
        if (isset($filter['shelf_status']) && array_key_exists($filter['shelf_status'], $shelf_status)) {
            $sql_main .= " AND gd.status=:shelf_status";
            $sql_values[':shelf_status'] = $shelf_status[$filter['shelf_status']];
        }
        //店铺代码
        if (isset($filter['shop_code']) && $filter['shop_code'] != '') {
            $sql_main .= " AND gi.shop_code=:shop_code";
            $sql_values[':shop_code'] = $filter['shop_code'];
        }
        //标题或编码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (gi.goods_code LIKE :code_name or gi.title LIKE :code_name)";
            $sql_values[':code_name'] = "%{$filter['code_name']}%";
        }
        $sql_main .=' ORDER BY gi.lastchanged DESC ';
        $select = 'gi.issue_id,gi.num_iid,gi.shop_code,gi.goods_code,gi.title, gi.outer_id, gi.barcode, ti.name AS category_name, gi.category_id, gi.price, gi.quantity, gi.lastchanged';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取编辑信息
     * @param array $param 条件数组
     * @param string $_type baseinfo,item_prop,cowry_desc
     * @return array 结果集
     */
    function get_edit_info($param, $_type) {
        $select = 'gi.shop_code,gi.goods_code';
        $sql_join = '';
        if ($_type == 'baseinfo') {
            $select .= ',gi.title,gi.category_id,gi.sub_title,gi.price,gi.quantity,gi.barcode,gi.outer_id,gi.pic_url,gi.shelf_time,gi.timing,gi.prov,gi.city,gi.postage_template,gi.weight,gi.cubage,gi.is_base_full,tb.name AS category_name';
            $sql_join = 'LEFT JOIN api_tb_itemcats tb ON gi.category_id=tb.cid';
        } else if ($_type == 'item_prop') {
            $select .= ',gi.item_prop';
        } else if ($_type == 'cowry_desc') {
            $select .= ',gi.desc';
        }
        $sql_values = array(':shop_code' => $param['shop_code'], ':goods_code' => $param['goods_code']);
        $sql = "SELECT {$select} FROM {$this->table} gi {$sql_join} WHERE gi.shop_code=:shop_code AND gi.goods_code=:goods_code";
        $data = $this->db->get_row($sql, $sql_values);
        if ($_type == 'baseinfo') {
            $data['shop_name'] = get_shop_name_by_code($data['shop_code']);
            if (empty($data['prov']) && empty($data['city'])) {
                $data['location'] = '';
            } else {
                $data['location'] = $data['prov'] . '/' . $data['city'];
            }
            $data['pic_url'] = json_decode($data['pic_url'], true);
        }

        return $data;
    }

    /**
     * 获取已发布商品SKU销售属性信息
     */
    function get_goods_sku_list($param) {
        if (!isset($param['shop_code']) || !isset($param['goods_code'])) {
            return array();
        }
        $data['table'] = $this->sku_table;
        $data['where'] = array('shop_code' => $param['shop_code'], 'goods_code' => $param['goods_code']);
        $data['select'] = 'sell_prop_id,goods_code,sku,spec1_code,spec2_code,spec1_name,spec2_name,sku_barcode,sku_price,sku_quantity';
        $ret = $this->get_table_data($data);
        return $ret;
    }

    /**
     * 获取完整度状态
     * @param array $params 条件数组
     * @return int 完成状态
     */
    function get_status($params, $tab_type = 'baseinfo') {
        $status = array('baseinfo' => 'is_base_full', 'item_prop' => 'is_item_full', 'sell_prop' => 'is_spec_full', 'cowry_desc' => 'is_base_full');
        $_param = array(
            'table' => $this->table,
            'select' => $status[$tab_type],
            'where' => $params
        );
        $ret = $this->get_table_data($_param, 'value');
        return $ret;
    }

    /**
     * 根据条件查询数据
     * @param array $param table=>表名,where=>条件数组('字段名' => 字段值),select=>查询字段
     * @param string $type =all：get_all,  =row：get_row,  =value：get_value
     * @return array 结果集
     */
    function get_table_data($param, $type = 'all') {
        $wh_arr = array();
        $sql_values = array();
        foreach ($param['where'] as $key => $val) {
            $wh_arr[] = "{$key}=:{$key}";
            $sql_values[":{$key}"] = $val;
        }
        $where = implode(' AND ', $wh_arr);
        $sql = "SELECT {$param['select']} FROM {$param['table']} WHERE 1 AND {$where}";

        $act = 'get_all';
        if ($type == 'row') {
            $act = 'get_row';
        } else if ($type == 'value') {
            $act = 'get_value';
        }
        $ret = $this->db->$act($sql, $sql_values);
        return $ret;
    }

    /**
     * 保存类目属性
     */
    function save_item_prop($param) {
        $data = get_array_vars($param, array('shop_code', 'goods_code'));

        foreach ($param as $key => $val) {
            if (strpos($key, 'prop') === 0 && !empty($val)) {
                $item_prop[$key] = $val;
            }
        }
        $item_prop = json_encode($item_prop);
        $data['item_prop'] = $item_prop;
        $data['tab_type'] = 'item_prop';
        $ret = $this->update_data($data);
        return $ret;
    }

    /**
     * 保存销售属性
     */
    function save_sell_prop($param) {
        $fields = array('spec1_name', 'spec2_name', 'sku_price', 'sku_quantity', 'sku_barcode', 'spec1_code', 'spec2_code', 'sku');
        $sku_goods = array();
        $quantity = 0;
        foreach ($param['goods'] as $key => $val) {
            foreach ($val as $k => $v) {
                $sku_goods[$key][$fields[$k]] = $v;
            }
            $sku_goods[$key]['shop_code'] = $param['shop_code'];
            $sku_goods[$key]['goods_code'] = $param['goods_code'];
            $sku_goods[$key]['sku_outer_id'] = $sku_goods[$key]['sku_barcode'];
            $sku_goods[$key]['create_time'] = date('Y-m-d H:i:s');
            $quantity += $sku_goods[$key]['sku_quantity'];
        }

        $update_str = 'sku_price = VALUES(sku_price), sku_quantity = VALUES(sku_quantity)';
        $ret = $this->insert_multi_duplicate($this->sku_table, $sku_goods, $update_str);
        if ($ret['status'] == 1) {
            $_param = get_array_vars($param, array('shop_code', 'goods_code'));
            $_param['quantity'] = $quantity;
            $_param['is_spec_full'] = 1;
            $this->update_field($_param);
        }

        return $ret;
    }

    function create_location_file() {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_cat/";
        $xml_path = $path . "/biz-province-city.xml";
        $xml = file_get_contents($xml_path);

        require_lib('tb_xml');
        $xmlobj = new tb_xml();
        $return = array();
        $xmlobj->xml2array($xml, $return);
        $arr_path = $path . "/location.txt";
        file_put_contents($arr_path, serialize($return));
        return $return;
    }

    /**
     * 获取所在地，供树形选择使用
     */
    function get_location() {
        $path = ROOT_PATH . CTX()->app_name . "/" . "data/tb_item/tb_item_cat/location.txt";
        if (!file_exists($path)) {
            $this->create_location_file();
        }
        $arr = file_get_contents($path);
        $location = unserialize($arr);

        $location = $location['beans']['bean']['property']['map']['entry'];
        $location_arr = array();
        foreach ($location as $key => $val) {
            $location_arr[$key]['id'] = $val['@attributes']['key'];
            $location_arr[$key]['value'] = $val['@attributes']['key'];
            $location_arr[$key]['parent'] = 0;
            $location_arr[$key]['children'] = array();
            $location_arr[$key]['isleaf'] = FALSE;
            $children = array();
            if (is_array($val['list']['value'])) {
                foreach ($val['list']['value'] as $k => $v) {
                    $children[$k]['id'] = $v;
                    $children[$k]['value'] = $v;
                    $children[$k]['parent'] = $val['@attributes']['key'];
                    $children[$k]['children'] = array();
                    $children[$k]['isleaf'] = TRUE;
                }
                $location_arr[$key]['children'] = $children;
            } else {
                $children['id'] = $val['list']['value'];
                $children['value'] = $val['list']['value'];
                $children['parent'] = $val['@attributes']['key'];
                $children['children'] = array();
                $children['isleaf'] = TRUE;
                $location_arr[$key]['children'][] = $children;
            }
        }
        return $location_arr;
    }

    /**
     * 获取店铺运费模板
     */
    function get_postage_template($shop_code) {
        $mod = new TbItemModel($shop_code);
        $param['fields'] = "template_id,template_name";
        $temp = $mod->get_postage_template($param);
        return $temp;
    }

    /**
     * 获取指定商品的所有sku数据
     */
    function get_goods_sku($param) {
        $sell_element = load_model('api/TbGoodsIssueOptModel')->get_file_cache($param['category_id'], 'sell_prop');
        $data = $this->get_goods_sku_list($param);
        if (!empty($data)) {
            return array('sell_element' => $sell_element, 'sku_data' => $data);
        }

        //获取库存来源仓
        $sql = "SELECT stock_source_store_code FROM base_shop WHERE shop_code=:shop_code";
        $store = $this->db->get_value($sql, array(':shop_code' => $param['shop_code']));
        $store = deal_strs_with_quote($store);

        $sql = "SELECT gk.spec1_name,gk.spec2_name,gk.barcode AS sku_barcode,gk.sku,gk.price AS sku_price,bg.sell_price AS goods_price,sum(gi.stock_num) AS sku_quantity
                FROM base_goods bg
                INNER JOIN goods_sku gk ON bg.goods_code=gk.goods_code
                INNER JOIN goods_inv gi ON bg.goods_code=gi.goods_code AND gk.sku=gi.sku AND gi.store_code in({$store})
                WHERE bg.goods_code=:goods_code GROUP BY gk.sku";
        $data = $this->db->get_all($sql, array(':goods_code' => $param['goods_code']));
        foreach ($data as $key => &$val) {
            if (empty($val['sku_price'])) {
                $val['sku_price'] = $val['goods_price'];
            }
            unset($val['goods_price']);

            if (in_array($val['spec1_name'], $sell_element['prop_1627207']['list_data']) && in_array($val['spec2_name'], $sell_element['prop_20509']['list_data'])) {
                $val['spec1_code'] = array_search($val['spec1_name'], $sell_element['prop_1627207']['list_data']);
                $val['spec2_code'] = array_search($val['spec2_name'], $sell_element['prop_20509']['list_data']);
            } else {
                unset($data[$key]);
            }
        }
        return array('sell_element' => $sell_element, 'sku_data' => $data);
    }

    /**
     * 批量添加上新宝贝
     */
    function add_multi_goods($param) {
        if (!isset($param['shop_code']) || empty($param['shop_code'])) {
            return $this->format_ret(-1, '', '未选择店铺');
        }
        if (empty($param['goods'])) {
            return $this->format_ret(-1, '', '未选择商品');
        }
        $shop_code = $param['shop_code'];
        $goods = $param['goods'];
        unset($param);
        //获取商品库存数
        $goods_num = array();
        $this->get_goods_inv($goods, $goods_num, $shop_code);
        //组装数据
        foreach ($goods as &$val) {
            $k = $val['goods_code'];
            $val['shop_code'] = $shop_code;
            $val['title'] = $val['goods_name'];
            $val['quantity'] = isset($goods_num[$k]) && $goods_num[$k] > 0 ? $goods_num[$k] : 0;
            $val['barcode'] = $val['goods_code'];
            $val['outer_id'] = $val['goods_code'];
            $val['shelf_time'] = 'stock';
            $val['create_time'] = date('Y-m-d H:i:s');
            $val['create_person'] = CTX()->get_session('user_name');
            unset($val['goods_name']);
        }
        unset($val, $goods_num);
        //拆分-插入表数据
        $goods_arr = array_chunk($goods, 500);
        unset($goods);
        $this->begin_trans();
        foreach ($goods_arr as $_goods) {
            $ret = $this->insert_multi($_goods);
            $aff_row = $this->affected_rows();
            if ($ret['status'] != 1 && $aff_row < 1) {
                $this->rollback();
                return $ret;
            }
        }
        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 获取商品库存
     */
    function get_goods_inv($goods, &$goods_num, $shop_code) {
        $goods_code = array_column($goods, 'goods_code');
        $goods_code = deal_array_with_quote($goods_code);
        $sql = "SELECT gi.goods_code,SUM(gi.stock_num) AS quantity FROM base_shop bs INNER JOIN goods_inv gi ON bs.send_store_code=gi.store_code WHERE gi.goods_code IN({$goods_code}) AND bs.shop_code=:shop_code";
        $goods_num = $this->db->get_all($sql, array(':shop_code' => $shop_code));
        $goods_num = array_column($goods_num, 'quantity', 'goods_code');
    }

    /**
     * 删除宝贝
     */
    function delete_goods($param) {
        if (!isset($param['shop_code']) || !isset($param['goods_code'])) {
            return $this->format_ret(-1, '', '未选择店铺或宝贝');
        }
        $this->begin_trans();
        $where = array('shop_code' => $param['shop_code'], 'goods_code' => $param['goods_code']);
        $ret = $this->delete($where);
        $aff_row = $this->affected_rows();
        $check = 1;
        if ($ret['status'] == 1 && $aff_row == 1) {
            $ret = $this->delete_exp($this->sku_table, $where);
            if (!$ret) {
                $check = 0;
            }
        } else {
            $check = 0;
        }
        if ($check == 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除宝贝失败');
        }

        $this->commit();
        return $this->format_ret(1);
    }

    /**
     * 更新主表数据
     * @param array $param 更新数据
     * @return array 结果状态
     */
    function update_data($param) {
        if (!isset($param['tab_type']) || empty($param['tab_type'])) {
            return $this->format_ret(-1, '', '请刷新页面重试');
        }
        $tab_type = $param['tab_type'];
        unset($param['tab_type']);

        $status = array('baseinfo' => 'is_base_full', 'item_prop' => 'is_item_full', 'sell_prop' => 'is_spec_full', 'cowry_desc' => 'is_base_full');
        $param[$status[$tab_type]] = 1;

        if ($tab_type == 'baseinfo') {
            //第一次更新基本信息时获取添加规则
            $_param = get_array_vars($param, array('shop_code', 'goods_code'));
            $status = $this->get_status($_param);
            if ($status == 0) {
                $param['is_base_full'] = 2;
                $_param['category_id'] = $param['category_id'];
                load_model('api/TbGoodsIssueOptModel')->down_add_data($_param);
            }

            $location = explode('/', $param['location']);
            $param['prov'] = $location[0];
            $param['city'] = $location[1];
        }

        $ret = $this->update_field($param, 'goods');
        return $ret;
    }

    /**
     * 更新字段值（慎重修改！）
     * 宝贝列表更新、编辑基本信息更新 通用
     * @param array $param 更新数据 + 条件（shop_code,goods_code）
     * @param string $type =goods：宝贝主信息，=sku：宝贝列表单个SKU信息
     */
    function update_field($param, $type = 'goods') {
        if (isset($param['id']) && !empty($param['id'])) {
            $id_fld = $type == 'goods' ? 'issue_id' : 'sell_prop_id';
            $where = array($id_fld => $param['id']);
            unset($param['id']);

            if ($type == 'sku') {
                $data = array(
                    'table' => $this->sku_table,
                    'where' => $where,
                    'select' => 'shop_code,goods_code'
                );
                $res = $this->get_table_data($data, 'row');
            }
        } else {
            $where = array('shop_code' => $param['shop_code'], 'goods_code' => $param['goods_code']);
            unset($param['shop_code'], $param['goods_code']);
        }

        $table = $type == 'goods' ? $this->table : $this->sku_table;
        $this->begin_trans();
        $ret = $this->update_exp($table, $param, $where);
        $aff_row = $this->affected_rows();
        if ($ret['status'] == 1 && $aff_row == 1 && $type == 'sku') {
            //更新sku数量时回写宝贝数量
            $ret = $this->write_goods_quantity($res);
        }
        if ($ret['status'] != 1 && $aff_row != 1) {
            $this->rollback();
        } else {
            $this->commit();
        }
        return $ret;
    }

    /**
     * 更改SKU数量后，回写商品数量
     * @param array $param shop_code , goods_code
     */
    function write_goods_quantity($param) {
        $sql = "UPDATE {$this->table} SET quantity=(
                    SELECT SUM(sku_quantity) FROM {$this->sku_table} WHERE shop_code=:shop_code AND goods_code=:goods_code
                ) WHERE shop_code=:shop_code AND goods_code=:goods_code";
        $sql_values = array(':shop_code' => $param['shop_code'], ':goods_code' => $param['goods_code']);
        $ret = $this->query($sql, $sql_values);
        $aff_row = $this->affected_rows();
        if ($ret['status'] != 1 || $aff_row != 1) {
            return $this->format_ret(-1, '', '更新商品数量失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 获取淘宝所有商品类目
     */
    function get_items($shop_code) {
        $mod = new TbItemModel($shop_code);

        $param['fields'] = "cid,parent_cid,name,is_parent,status,sort_order";
        $param['parent_cid'] = '0';
        $data = $mod->get_itemcats($param);
        if (!empty($data)) {
            $cids = array_column($data, 'cid');
            foreach ($cids as $val) {
                $param['parent_cid'] = (string) $val;
                $items = $mod->get_itemcats($param);
                if (!empty($items)) {
                    $data = array_merge($data, $items);
                }
            }
            foreach ($data as &$val) {
                $val['is_parent'] = $val['is_parent'] == true ? '1' : '0';
            }
            $this->db->query("Truncate Table api_tb_itemcats");
            $res = $this->insert_multi_exp('api_tb_itemcats', $data);
            return $res;
        } else {
            return $this->format_ret(-1, '', '数据为空');
        }
    }

    //取得子类
    function get_select_itemcats($parent_cid) {
        $parent_cid = empty($parent_cid) ? 0 : $parent_cid;
        $sql = "SELECT cid AS id,name AS text,is_parent AS leaf FROM api_tb_itemcats WHERE status='normal' AND parent_cid = :parent_cid ";
        $itemcats = $this->db->get_all($sql, array(':parent_cid' => $parent_cid));

        foreach ($itemcats as &$val) {
            $val['leaf'] = $val['leaf'] == 1 ? FALSE : TRUE;
        }

        return $itemcats;
    }

}
