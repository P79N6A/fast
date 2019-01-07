<?php

/**
 * 门店商品相关业务
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);

class ShopGoodsModel extends TbModel {

    function __construct() {
        parent::__construct('base_shop_sku');
    }

    /**
     * 根据条件查询数据
     */
    public function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $login_type = CTX()->get_session('login_type');
        if ($login_type > 0) {
            if (!empty(CTX()->get_session('oms_shop_code'))) {
                $filter['shop_code'] = CTX()->get_session('oms_shop_code');
            }
        }

        $sql_values = array();
        $sql_join = 'INNER JOIN base_goods bg ON sk.goods_code=bg.goods_code
                     INNER JOIN base_shop bs ON (sk.shop_code=bs.shop_code AND bs.shop_type=1) ';

        $select = 'sk.*,bs.shop_name,bg.goods_name,bg.category_name,bg.brand_name,bg.season_name,
                   bg.year_name,bg.sell_price,bg.goods_thumb_img';

        if ($filter['ctl_type'] == 'export' || (isset($filter['barcode']) && $filter['barcode'] !== '')) {
            $sql_join .=" INNER JOIN goods_sku gk ON sk.sku=gk.sku ";
        }
        if ($filter['ctl_type'] == 'export') {
            $select .= ",gk.spec1_code,gk.spec2_code,gk.spec1_name,gk.spec2_name,gk.barcode";
        }

        $sql_main = "FROM {$this->table} sk {$sql_join} WHERE 1";
        //是否启用
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND sk.status= :status";
            $sql_values[':status'] = $filter['status'];
        }
        //门店
        if (isset($filter['shop_code']) && $filter['shop_code'] !== '') {
            $shop_arr = explode(',', $filter['shop_code']);
            $shop_str = $this->arr_to_in_sql_value($shop_arr, 'shop_code', $sql_values);
            $sql_main .= " AND sk.shop_code IN({$shop_str}) ";
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND sk.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND bg.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sql_main .= " AND gk.barcode LIKE :barcode ";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        if ($filter['ctl_type'] != 'export') {
            $sql_main .= ' GROUP BY sk.shop_code,sk.goods_code ';
        }
        $sql_main .= ' ORDER BY sk.shop_code,sk.goods_code ';

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);

        foreach ($data['data'] as $key => &$value) {
            $value['pic_path'] = !empty($value['goods_thumb_img']) ? "<img width='50px' height='50px' src='{$value['goods_thumb_img']}' />" : '';
            $value['is_active'] = $value['status'];
            $value['compare_price'] = $value['goods_price'];
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @todo 获取列表点击展开的商品详细信息
     */
    public function get_detail_by_code($goods_code, $shop_code) {
        if (empty($goods_code) || empty($shop_code)) {
            return array();
        }
        $sql = "SELECT sk.*,gk.spec1_code,gk.spec2_code,gk.spec1_name,gk.spec2_name,gk.barcode
                FROM {$this->table} sk INNER JOIN goods_sku gk ON sk.sku=gk.sku WHERE sk.goods_code=:goods_code AND sk.shop_code=:shop_code";
        $detail = $this->db->get_all($sql, array(':goods_code' => $goods_code, ':shop_code' => $shop_code));
        if (load_model('sys/PrivilegeModel')->check_priv('prm/shop_goods/update_price')) {
            foreach ($detail as &$value) {
                $value['sku_price'] = "<a id='price' priceid={$value['shop_sku_id']} onclick='change_sku_price(this)'>{$value['sku_price']}</a>";
            }
        }
        return $detail;
    }

    /**
     * @todo 更新启用状态
     */
    public function update_active($params) {
        $active = array('enable' => 1, 'disable' => 0);
        if (!isset($active[$params['active']])) {
            return $this->format_ret(-1, '', '参数有误');
        }
        $status = $active[$params['active']];

        //判断原商品状态，若要原商品未启用，则门店商品不能启用
        $goods = load_model('prm/GoodsModel')->get_by_field('goods_code', $params['goods_code'], 'status', 'base_goods');
        if ($goods['status'] != 1) {
            return $this->format_ret(-1, '', '关联系统商品不存在，无法启用');
        }

        if ($status == 1 && $goods['data']['status'] == 1) {
            return $this->format_ret(-1, '', '系统商品未启用，不能启用门店商品');
        }

        $ret = parent::update(array('status' => $status), array('shop_sku_id' => $params['id']));
        return $ret;
    }

    /**
     * @todo 批量更新启用状态
     */
    public function batch_update_active($params) {
        $active = array('enable' => 1, 'disable' => 0);
        if (!isset($active[$params['active']])) {
            return $this->format_ret(-1, '', '状态参数有误');
        }
        $status = $active[$params['active']];

        $ids = array();
        $msg = array();
        foreach ($params['id'] as $key => $val) {
            //判断原商品状态，若原商品未启用，则门店商品不能启用
            $goods = load_model('prm/GoodsModel')->get_by_field('goods_code', $val, 'status', 'base_goods');
            if ($goods['status'] != 1) {
                $msg[$key] = "商品编码{$val}关联的系统商品不存在";
            } else if ($status == 1 && $goods['data']['status'] == 1) {
                $msg[$key] = " 商品编码{$val}关联的系统商品未启用";
            }
            if (empty($msg[$key])) {
                $ids[] = $key;
            }
        }
        if (empty($ids)) {
            return $this->format_ret(-1, '', implode('<br>', $msg));
        }
        $ids_str = deal_array_with_quote($ids);

        $sql = "UPDATE {$this->table} SET status={$status} WHERE shop_sku_id in ({$ids_str})";
        $ret = $this->db->query($sql);
        if ($ret === true) {
            $msg_str = empty($msg) ? '' : implode('<br>', $msg);
            if (empty($msg)) {
                return $this->format_ret(1, '', '操作成功');
            } else {
                return $this->format_ret(1, '', '部分操作成功<br>' . $msg_str);
            }
        }
        return $this->format_ret(-1, '', '操作失败');
    }

    public function update_price($params) {
        $shop_goods = $this->get_row(array('shop_sku_id' => $params['id']));
        if ($shop_goods['status'] != 1) {
            return $this->format_ret(-1, '', '门店商品不存在');
        }
        $shop_goods = $shop_goods['data'];
        if ($params['type'] == 1) {
            $data = array('goods_price' => $params['goods_price']);
            $log_xq = "门店商品售价由{$shop_goods['goods_price']}变更为{$data['goods_price']}";
        } else {
            $data = array('sku_price' => $params['sku_price']);
            $log_xq = "门店商品sku：{$shop_goods['sku']},售价由{$shop_goods['sku_price']}变更为{$data['sku_price']}";
        }

        $ret = parent::update($data, array('shop_sku_id' => $params['id']));
        if ($ret['status'] != 1) {
            return $this->format_ret(-1, '', '更新失败');
        }
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => '商品', 'yw_code' => $shop_goods['goods_code'], 'operate_type' => '编辑', 'operate_xq' => $log_xq);
        load_model('sys/OperateLogModel')->insert($log);

        return $this->format_ret(1);
    }

    /**
     * @todo 添加门店商品
     */
    public function add_goods($data) {
        if (empty($data['shop_code'])) {
            return $this->format_ret(-1, '', '没有关联门店');
        }
        if (empty($data['data'])) {
            return $this->format_ret(-1, '', '未选择商品');
        }
        $shop_goods = array();
        foreach ($data['data'] as $key => $value) {
            $shop_goods[$key]['shop_code'] = $data['shop_code'];
            $shop_goods[$key]['goods_code'] = $value['goods_code'];
            $shop_goods[$key]['sku'] = $value['sku'];
            $shop_goods[$key]['goods_price'] = $value['sell_price'];
            $shop_goods[$key]['sku_price'] = $value['sell_price'];
            $shop_goods[$key]['create_person'] = CTX()->get_session('user_name');
            $shop_goods[$key]['create_time'] = date('Y-m-d H:i:s');
        }
        $update_str = "shop_code = VALUES(shop_code)";
        $ret = $this->insert_multi_duplicate($this->table, $shop_goods, $update_str);
        return $ret;
    }

    /**
     * 删除记录
     */
    public function delete($goods_id) {
        $ret = parent::delete(array('goods_id' => $goods_id));
        return $ret;
    }

    public function is_exists($value, $field_name = 'goods_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 商品删除
     * 仅针对未启用的且未产生销售记录和库存记录的商品进行删除操作
     * @param string $goods_code 商品编码
     * @return array
     */
    public function goods_delete($goods_code) {
        $msg = '';
        $sql_inv = "SELECT goods_code FROM goods_inv WHERE goods_code=:goods_code";
        $sql_value = array(':goods_code' => $goods_code);
        $ret_inv = $this->db->get_row($sql_inv, $sql_value);
        if (!empty($ret_inv)) {
            $msg = '商品存在库存记录,';
        }
        $sql_record = "SELECT goods_code FROM oms_sell_record_detail WHERE goods_code=:goods_code";
        $ret_record = $this->db->get_row($sql_record, $sql_value);
        if (!empty($ret_record)) {
            $msg .= '存在销售记录,';
        }
        if (!empty($msg)) {
            $msg.='不能删除';
            return $this->format_ret(-1, '', $msg);
        }

        $ret = parent::delete(array('goods_code' => $goods_code));
        return $ret;
    }

    function deal_array_key($arr) {
        $new_arr = array();
        foreach ($arr as $val) {
            $new_arr[$val['barcode']] = $val;
        }
        return $new_arr;
    }

    /**
     * @todo 导入商品数据
     */
    function import_goods_data($file) {
        //读取excel
        $goods = array();
        $num = $this->read_csv($file, $goods);

        //获取存在的shop_code
        $shop_code = array_unique(array_column($goods, 'shop_code'));
        $shop_code = deal_array_with_quote($shop_code);
        $sql = "SELECT shop_code FROM base_shop WHERE shop_type=1 AND shop_code in({$shop_code})";
        $shop_code = $this->db->get_all($sql);
        $shop_code = array_column($shop_code, 'shop_code');

        //获取存在的条码信息
        $barcode = array_unique(array_column($goods, 'barcode'));
        $barcode = deal_array_with_quote($barcode);
        $sql = "SELECT bg.goods_code,bg.sell_price AS goods_price,gk.sku,gk.barcode  FROM base_goods bg INNER JOIN goods_sku gk ON bg.goods_code=gk.goods_code WHERE gk.barcode in({$barcode})";
        $barcode = $this->db->get_all($sql);
        $barcode_arr = $this->deal_array_key($barcode);

        $import_count = count($goods);
        $error_msg = array();
        $err_num = 0;
        foreach ($goods as $key => &$val) {
            if (!in_array($val['shop_code'], $shop_code)) {
                $err_num++;
                $error_msg[] = array($val['barcode'] . ',' . $val['shop_code'] . ',' . $val['sku_price'] => '系统中不存在此门店店铺');
                unset($goods[$key]);
                continue;
            }
            if (!array_key_exists($val['barcode'], $barcode_arr)) {
                $err_num++;
                $error_msg[] = array($val['barcode'] . ',' . $val['shop_code'] . ',' . $val['sku_price'] => '系统中不存在此商品条码');
                unset($goods[$key]);
                continue;
            }
            if (!is_numeric($val['sku_price']) || $val['sku_price'] < 0) {
                $err_num++;
                $error_msg[] = array($val['barcode'] . ',' . $val['shop_code'] . ',' . $val['sku_price'] => '商品价格必须大于0');
                unset($goods[$key]);
                continue;
            }
            $val = array_merge($val, $barcode_arr[$val['barcode']]);
            $val['create_person'] = CTX()->get_session('user_name');
            $val['create_time'] = date('Y-m-d H:i:s');
            unset($val['barcode']);
        }

        if (!empty($goods)) {
            $this->begin_trans();
            $update_str = 'goods_price = VALUES(goods_price),sku_price=VALUES(sku_price)';
            $goods_arr = array_chunk($goods, 2000);
            foreach ($goods_arr as $v) {
                $ret = $this->insert_multi_duplicate($this->table, $goods, $update_str);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret(-1, '', '导入出错');
                }
            }
            $this->commit();
        }

        $success_num = $import_count - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('条码', '门店代码', '门店价格', '错误信息');
            $file_name = load_model('wbm/StoreOutRecordModel')->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }

        $ret['message'] = $message;
        return $ret;
    }

    /**
     * 读取文件，保存到数组中
     */
    function read_csv($file, &$sync_perc) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sync_perc[$i]['barcode'] = $row[0];
                    $sync_perc[$i]['shop_code'] = $row[1];
                    $sync_perc[$i]['sku_price'] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * @todo 编码转换
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
//                $val = iconv('utf-8', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
            }
        }
    }

}
