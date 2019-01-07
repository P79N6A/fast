<?php

/**
 * 套餐商品相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);
require_lang('prm');

class GoodsComboModel extends TbModel {

    function get_table() {
        return 'goods_combo';
    }

    function get_by_id($id) {
        $data = $this->get_row(array('goods_combo_id' => $id));
        return $data;
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //$sql_join = "";
        if($filter['ctl_type'] == 'export'){
            $sql_main = "FROM {$this->table} rl LEFT JOIN goods_combo_barcode r2 on rl.goods_code = r2.goods_code LEFT JOIN goods_combo_diy r3 on r2.sku = r3.p_sku WHERE 1";
        } else {
            $sql_main = "FROM {$this->table} rl LEFT JOIN goods_combo_barcode r2 on rl.goods_code = r2.goods_code WHERE 1";
        }
        $sql_values = array();
        // 套餐编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = '%'.$filter['goods_code'] . '%';
        }
        //套餐名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (rl.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = '%'.$filter['goods_name'] . '%';
        }
        //套餐条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :barcode )";
            $sql_values[':barcode'] = '%'.$filter['barcode'] . '%';
        }
        //店铺
        if (isset($filter['status']) && $filter['status'] <> '') {
            $arr = explode(',', $filter['status']);
            $str = $this->arr_to_in_sql_value($arr, 'status', $sql_values);
            $sql_main .= " AND rl.status in ({$str}) ";
        }

        //业务日期
        if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
            $sql_main .= " AND (rl.create_time >= :create_time_start )";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . ' 00:00:00';
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
            $sql_main .= " AND (rl.create_time <= :create_time_end )";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . ' 23:59:59';
        }
        
        $select = 'rl.*,r2.barcode,r2.spec1_code AS combo_spec1_code,r2.spec2_code AS combo_spec2_code';
        if($filter['ctl_type'] == 'export'){
            $select = 'rl.*,r2.barcode,r3.sku,r3.num,
                   r3.price sell_price';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        foreach($data['data'] as $key => $val) {
            $data['data'][$key]['state_name'] = $val['status'] ? '是' : '否';
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name','goods_code', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $data['data'][$key]['goods1_code'] = $sku_info['goods_code'];
            $data['data'][$key]['goods1_name'] = $sku_info['goods_name'];
            $data['data'][$key]['goods_barcode'] = $sku_info['barcode'];
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $data['data'][$key]['combo_spec1_name'] = load_model('prm/Spec1Model')->get_spec1_name($val['combo_spec1_code']);
            $data['data'][$key]['combo_spec2_name'] = load_model('prm/Spec2Model')->get_spec2_name($val['combo_spec2_code']);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    //添加新纪录
    function insert( $data) {
        $ret = array();
        foreach($data['barcode'] as $key => $value) {
            $flag = $this->barcode_exist($value, $data['goods_code'], $key, $data['goods_combo_id']);  
            if ($flag) {
                $ret['status'] = '-2';
                $ret['data'] = 'true';
                $ret['message'] = '输入条形码与套餐条形码重复';
            } else {
                $flag1 = $this->barcode_goods_exist($value);
                //var_dump($flag);
                if ($flag1) {
                    $ret['status'] = '-2';
                    $ret['data'] = 'true';
                    $ret['message'] = '输入条形码与商品条形码重复';
                }
            }
        }
        if(!empty($ret['status'])) {
            return $ret;
        }
        $ret = $this->is_exists($data['goods_code']);

        if (!empty($ret['data']))
            return $this->format_ret('-1', '', '此code存在');
        //        $stock_adjus['is_add_time'] = date('Y-m-d H:i:s');

        return parent::insert($data);
    }

    public function is_exists($value, $field_name = 'goods_code') {

        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    /*
     * 修改纪录
     */

    function update($data, $goods_combo_id) {

        $ret = $this->get_row(array('goods_combo_id' => $goods_combo_id));
        if ($data['goods_code'] != $ret['data']['goods_code']) {
            $ret1 = $this->is_exists($data['goods_code'], 'goods_code');
            if (!empty($ret1['data']))
                return $this->format_ret('code已经被使用过');
        }

//        if (isset($data['goods_name']) && $data['goods_name'] != $ret['data']['goods_name']) {
//            $ret = $this->is_exists($data['goods_name'], 'goods_name');
//            if (!empty($ret['data']))
//                return $this->format_ret('code已经被使用过');
//        }
        $ret = parent::update($data, array('goods_combo_id' => $goods_combo_id));
        return $ret;
    }

    /**
     * 检查商品条码是否被用过
     * @param $barcode
     * $goods_code
     * $spec
     */
    function barcode_exist($barcode, $goods_code, $spec, $goods_combo_id = "") {

        $arr = explode('_', $spec);
        $spec1_code = $arr[0];
        $spec2_code = $arr[1];
        if (!empty($goods_combo_id)) {
            $goods_combo_ret = $this->get_by_id($goods_combo_id);
            //套餐编辑 套餐编码可能有修改
            $goods_code = $goods_combo_ret['data']['goods_code'];
        }

        $sql = "select goods_combo_barcode_id  from goods_combo_barcode where (goods_code <> :goods_code or spec1_code <> :spec1_code or spec2_code <> :spec2_code) and  barcode = :barcode limit 1 ";
        $arr = array(':barcode' => $barcode, ':goods_code' => $goods_code, ':spec1_code' => $spec1_code, ':spec2_code' => $spec2_code);
        //echo $sql;
        //print_r($arr);
        //$sql = "select goods_combo_barcode_id  from goods_combo_barcode where    barcode = :barcode limit 1 ";
        //$arr = array(':barcode' => $barcode);
        //$data = $this->db->get_row($sql, $arr);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    //基础barcode表
    function barcode_goods_exist($barcode) {
        $sql = "select sku_id  from goods_sku where   barcode = :barcode or sku = :sku limit 1 ";
        $arr = array(':barcode' => $barcode, ':sku' => $barcode);
        $data = $this->db->get_all($sql, $arr);
        if ($data) {
            return true;
        } else {
            return false;
        }
    }

    //查看库存
    function goods_inv($sku, $store_code) {
        $sql = "select stock_num, lock_num  from goods_inv where store_code = :store_code and  sku = :sku  ";
        $arr = array(':sku' => $sku, ':store_code' => $store_code);

        $data = $this->db->get_row($sql, $arr);
        return $data;
    }

    function update_active($active, $id) {
        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        $ret = parent :: update(array('status' => $active), array('goods_combo_id' => $id));
        return $ret;
    }

    /**
     * 批量启用 停用
     * @param $active
     * @param $id
     * @return array
     */
    function multi_update_active($active, $ids) {
        $sql_value = array();
        $id_arr = explode(',', $ids);
        $id_str = $this->arr_to_in_sql_value($id_arr, 'goods_combo_id', $sql_value);
        $sql = "UPDATE goods_combo SET status=:status WHERE goods_combo_id IN ({$id_str})";
        $sql_value[':status'] = $active;
        $ret = $this->query($sql, $sql_value);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '更新失败！');
        }
        return $ret;
    }
    
    /**
     * 删除
     * @param $active
     * @return array
     */
    function do_delete($barcode, $goods_code) {
        $ret = $this->delete_exp('goods_combo_barcode', array('barcode'=>$barcode));
        $data = $this->db->get_row("SELECT sku FROM goods_combo_barcode WHERE goods_code = :goods_code ", array(':goods_code'=>$goods_code));
        if (empty($data)) {
            $this->delete_exp('goods_combo', array('goods_code'=>$goods_code));
        }
        if ($ret) {
            $operate_xq = empty($barcode) ? '删除套餐商品':'删除套餐条码为'.$barcode.'的商品';//操作详情
            $yw_code = $goods_code; //业务编码          
            $module = '商品'; //模块名称
            $operate_type = '删除';
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_xq' => $operate_xq, 'operate_type' => $operate_type);
            load_model('sys/OperateLogModel')->insert($log);
            return $this->format_ret('1', '', '');
        } else {
            return $this->format_ret('-1', '', '');
        }
    }
    
    //检查状态
    function check_status($goods_combo_id, $barcode) {
        $sql = "SELECT status FROM goods_combo WHERE goods_combo_id = :goods_combo_id";
        $status = $this->db->get_value($sql, array(':goods_combo_id' => $goods_combo_id));
        if ($status) {
            return $this->format_ret('-1', '', '该商品套餐已启用，无法删除！');
        }
        $sql_1 = "SELECT sku FROM goods_combo_barcode WHERE barcode = :barcode";
        $sku = $this->db->get_value($sql_1, array(':barcode' => $barcode));
        if (!$sku) {
            return $this->format_ret('1', '', '');
        }
        $sql_2 = "SELECT sell_record_code FROM oms_sell_record_detail WHERE combo_sku = :combo_sku";
        $sell_record_code = $this->db->get_value($sql_2, array(':combo_sku' => $sku));
        if (empty($sell_record_code)) {
            return $this->format_ret('1', '', '');
        } else {
            return $this->format_ret('-1', '', '该商品套餐已使用过，无法删除！');
        }
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select goods_combo_id  from {$this->table}   order by goods_combo_id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['goods_combo_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "SPTC" . add_zero($djh);
        return $jdh;
    }

    function get_combo_sku_by_barcode($barcode) {
        $sql = "select sku from goods_combo_barcode where barcode=:barcode";
        $data = $this->db->get_all($sql, array(':barcode' => $barcode));
        $goods_sku_combo_arr = array();

        foreach ($data as $val) {
            $goods_sku_combo_arr[] = $val['sku'];
        }

        return $goods_sku_combo_arr;
    }

    function get_combo_barcode_by_sku($sku) {
        static $barcode_arr = null;
        if (!isset($barcode_arr[$sku])) {
            $sql = "select barcode from goods_combo_barcode where sku=:sku";
            $barcode_arr[$sku] = $this->db->get_value($sql, array(':sku' => $sku));
        }
        return $barcode_arr[$sku];
    }

    function get_combo_goods($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //todo 强制不分页
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 10000;
        $sql_value = '';
        $sql_join = " INNER JOIN goods_combo_barcode r2 ON rl.goods_code = r2.goods_code ";
        $sql_main = " FROM {$this->table} rl {$sql_join} WHERE status = 1 ";
        
        //套餐名称
//        if(isset($filter['goods_name']) && $filter['goods_name'] != '') {
//            $sql_main .= " AND (rl.goods_name LIKE :goods_name) ";
//            $sql_value[':goods_name'] = "%".$filter['goods_name']."%";
//        }
//        //套餐编号
//        if(isset($filter['goods_code']) && $filter['goods_code'] != '') {
//            $sql_main .= " AND (rl.goods_code LIKE :goods_code) ";
//            $sql_value[':goods_code'] = "%".$filter['goods_code']."%";
//        }
//        //套餐条形码
//        if(isset($filter['barcode']) && $filter['barcode'] != '') {
//            $sql_main .= " AND (r2.barcode LIKE :barcode) ";
//            $sql_value[':barcode'] = "%".$filter['barcode']."%";
//        }
        
        // 套餐编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
        //套餐名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND (rl.goods_name LIKE :goods_name )";
            $sql_values[':goods_name'] = $filter['goods_name'] . '%';
        }
        //套餐条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :barcode )";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }
        
        $select = "rl.goods_code,rl.goods_name,rl.goods_desc,r2.barcode,r2.sku";
        
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    
    /*
     * 套餐子商品条件查询数据
     */
    function get_detail_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        
        $sql_main = "FROM goods_combo_diy rl INNER JOIN goods_combo_barcode r2 on rl.p_sku = r2.sku  INNER JOIN goods_combo r3 ON r2.goods_code=r3.goods_code WHERE 1";
        $sql_values = array();
        // 子商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
         // 子商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }
       //套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :combo_barcode )";
            $sql_values[':combo_barcode'] = $filter['combo_barcode'] . '%';
        }
        //导出
        if($filter['ctl_type'] == 'export'){
            return $this->goods_combo_export_csv($filter);
        }
        $select = 'rl.*,r2.barcode as combo_barcode,r3.goods_name';    
        $sql_main .=" ORDER BY rl.sku";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
        foreach ($data['data'] as &$val) {
        $val['barcode']=oms_tb_val('goods_sku','barcode',array('sku'=>$val['sku']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /**套餐子商品导出
     */
    function goods_combo_export_csv($filter){
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        
        $sql_main = "FROM goods_combo_diy rl "
                . "INNER JOIN goods_combo_barcode r2 on rl.p_sku = r2.sku  "
                . "INNER JOIN goods_combo r3 ON r2.goods_code=r3.goods_code "
                . "LEFT JOIN goods_sku r4 ON rl.sku=r4.sku WHERE 1";
        $sql_values = array();
        // 子商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND (rl.goods_code LIKE :goods_code )";
            $sql_values[':goods_code'] = $filter['goods_code'] . '%';
        }
         // 子商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
                $sql_main .= " AND rl.sku in({$sku_str}) ";
            }
        }
       //套餐条形码
        if (isset($filter['combo_barcode']) && $filter['combo_barcode'] != '') {
            $sql_main .= " AND (r2.barcode LIKE :combo_barcode )";
            $sql_values[':combo_barcode'] = $filter['combo_barcode'] . '%';
        }
        $select = 'rl.*,r2.barcode as combo_barcode,r3.goods_name as combo_goods_name,r4.spec1_name,r4.spec2_name,r4.barcode'; 
        $sql_main .=" ORDER BY rl.sku";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, false);
        foreach ($data['data'] as &$val) {
        $val['goods_name']=oms_tb_val('base_goods','goods_name',array('goods_code'=>$val['goods_code']));
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     *
     * 方法名       api_goods_combo_add
     *
     * 功能描述     客户添加套餐数据接口
     *
     * @author      BaiSon PHP R&D
     * @date        2017-04-06
     * @param       array $param
     *              array(
     *                  必填: 'combo_code', 'combo_name', 'combo_barcode', 'goods_barcode', 'goods_num'
     *                  选填: 'combo_desc', 'price', 'combo_spce1', 'combo_spce2', 'goods_price', 'status', 'is_add_time'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_combo_add($param) {
        //必选字段【说明：i=>代表数据检测类型为数字型  s=>代表数据检测类为字符串型】
        $key_required = array(
            's' => array('combo_code', 'combo_name')
        );
        //可选字段
        $key_option = array(
            's' => array('combo_desc', 'is_add_time'),
            'i' => array('status', 'price')
        );

        $combo_required = array();
        $combo_option = array();

        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $combo_required, TRUE);
        if (TRUE == $ret_required['status']) {
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $combo_option);
            $combo = array_merge($combo_required, $combo_option);

            $combo_new = array();
            $combo_new['goods_code'] = $combo['combo_code'];
            $combo_new['goods_name'] = $combo['combo_name'];
            $combo_new['goods_desc'] = $combo['combo_desc'];
            $combo_new['status'] = $combo['status'] ? $combo['status'] : 1;
            $combo_new['price'] = $combo['price'];
            $combo_new['create_time'] = $combo['is_add_time'] ? $combo['is_add_time'] : date('Y-m-d H:i:s');

            unset($combo_required);
            unset($combo_option);
            unset($combo);

            $ret = $this->insert_multi_duplicate('goods_combo', array($combo_new), 'status = VALUES(status)');
            if (!$ret) {
                throw new Exception(lang('insert_error'), -1);
            }

            $combo_list = json_decode($param['combo_list'], true);
            if($combo_list){
                $combo_barcode_new = array();
                foreach ($combo_list as $k => $combo_barcode) {
                    //套餐条码插入
                    $key_required = array(
                        's' => array('combo_barcode', 'spec1_code', 'spec2_code')
                    );
                    $key_option = array(
                        'i' => array('barcode_price')
                    );
                    $combo_barcode_required = array();
                    $combo_barcode_option = array();

                    $ret_required = valid_assign_array($combo_barcode, $key_required, $combo_barcode_required, TRUE);
                    if (TRUE == $ret_required['status']) {
                        $ret_option = valid_assign_array($combo_barcode, $key_option, $combo_barcode_option);
                        $combo_barcode = array_merge($combo_barcode_required, $combo_barcode_option);

                        //检查客户传入的规格1是否存在
                        $spec1 = load_model('prm/Spec1Model')->get_by_code($combo_barcode['spec1_code']);
                        if (1 != $spec1['status']) {
                            return $this->format_ret("-10002", array('spec1_code' => $combo_barcode['spec1_code']), "API_RETURN_MESSAGE_10002");
                        }

                        //检查客户传入的规格2是否存在
                        $spec2 = load_model('prm/Spec2Model')->get_by_code($combo_barcode['spec2_code']);
                        if (1 != $spec2['status']) {
                            return $this->format_ret("-10002", array('spec2_code' => $combo_barcode['spec2_code']), "API_RETURN_MESSAGE_10002");
                        }

                        $combo_barcode_new[$k]['goods_code'] = $combo_new['goods_code'];
                        $combo_barcode_new[$k]['spec1_code'] = $combo_barcode['spec1_code'];
                        $combo_barcode_new[$k]['spec2_code'] = $combo_barcode['spec2_code'];
                        $combo_barcode_new[$k]['barcode'] = $combo_barcode['combo_barcode'];
                        $combo_barcode_new[$k]['sku'] = $combo_new['goods_code'].$combo_barcode['spec1_code'].$combo_barcode['spec2_code'].'_sku';
                        $combo_barcode_new[$k]['price'] = $combo_barcode['barcode_price'];

                        unset($combo_barcode_required);
                        unset($combo_barcode_option);
                        unset($combo_barcode);
//套餐子商品插入

                        $combo_goods_list = json_decode($param['combo_goods_list'], true);
                        if($combo_goods_list[$combo_barcode_new[$k]['barcode']]){
                            $goods_barcode_new = array();
                            foreach ($combo_goods_list[$combo_barcode_new[$k]['barcode']] as $kk => $goods_barcode) {
                                $key_required = array(
                                    's' => array('goods_barcode'),
                                    'i' => array('goods_num')
                                );
                                $key_option = array(
                                    'i' => array('goods_price')
                                );
                                $goods_barcode_required = array();
                                $goods_barcode_option = array();

                                $ret_required = valid_assign_array($goods_barcode, $key_required, $goods_barcode_required, TRUE);
                                if (TRUE == $ret_required['status']) {
                                    $ret_option = valid_assign_array($goods_barcode, $key_option, $goods_barcode_option);
                                    $goods_barcode = array_merge($goods_barcode_required, $goods_barcode_option);

                                    $sql = "select goods_code,sku,spec1_code,spec2_code from goods_sku where barcode = :barcode";
                                    $sql_val = array(':barcode' => $goods_barcode['goods_barcode']);
                                    $goods_sku = $this->db->get_row($sql, $sql_val);
                                    if (!$goods_sku) {
                                        return $this->format_ret("-10002", array('goods_barcode' => $goods_barcode['goods_barcode']), "API_RETURN_MESSAGE_10002");
                                    }

                                    $goods_barcode_new[$kk]['goods_code'] = $goods_sku['goods_code'];
                                    $goods_barcode_new[$kk]['sku'] = $goods_sku['sku'];
                                    $goods_barcode_new[$kk]['spec1_code'] = $goods_sku['spec1_code'];
                                    $goods_barcode_new[$kk]['spec2_code'] = $goods_sku['spec2_code'];
                                    $goods_barcode_new[$kk]['num'] = $goods_barcode['goods_num'];
                                    $goods_barcode_new[$kk]['price'] = $goods_barcode['goods_price'];
                                    $goods_barcode_new[$kk]['p_goods_code'] = $combo_barcode_new[$k]['goods_code'];
                                    $goods_barcode_new[$kk]['p_sku'] = $combo_barcode_new[$k]['sku'];

                                    unset($goods_barcode_required);
                                    unset($goods_barcode_option);
                                    unset($goods_barcode);

                                } else {
                                    return $this->format_ret("-10001", $goods_barcode, "API_RETURN_MESSAGE_10001");
                                }
                            }
                            $ret = $this->insert_multi_duplicate('goods_combo_diy', $goods_barcode_new, 'price = VALUES(price)');
                            if (!$ret) {
                                throw new Exception(lang('insert_error'), -1);
                            }
                        }

                    } else {
                        return $this->format_ret("-10001", $combo_barcode, "API_RETURN_MESSAGE_10001");
                    }
                }
                $ret = $this->insert_multi_duplicate('goods_combo_barcode', $combo_barcode_new, 'price = VALUES(price)');
                if (!$ret) {
                    throw new Exception(lang('insert_error'), -1);
                }
            }

            unset($param);

            return $ret;
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

}
