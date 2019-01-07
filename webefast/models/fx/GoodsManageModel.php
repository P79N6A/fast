<?php

/**
 * 供应商类型相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class GoodsManageModel extends TbModel {

    function get_table() {
        return 'fx_goods_manage';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_join = " LEFT JOIN fx_goods AS r2 ON r2.goods_line_code = rl.goods_line_code LEFT JOIN fx_goods_price_custom_grade AS r3 ON rl.goods_line_code = r3.goods_line_code LEFT JOIN fx_goods_price_custom AS r4 ON rl.goods_line_code = r4.goods_line_code";
        $sql_main = " FROM {$this->table} AS rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['goods_line_name']) && $filter['goods_line_name'] != '') {
            $sql_main .= " AND rl.goods_line_name like :goods_line_name";
            $sql_values[':goods_line_name'] = $filter['goods_line_name'] . '%';
        }

        if (isset($filter['goods_line_code']) && $filter['goods_line_code'] !== '') {
            $sql_main .= " AND rl.goods_line_code = :goods_line_code ";
            $sql_values[':goods_line_code'] = $filter['goods_line_code'];
        }
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] !== '') {
            $sql_main .= " AND r2.goods_barcode = :goods_barcode ";
            $sql_values[':goods_barcode'] = $filter['goods_barcode'];
        }
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND r2.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //分销商分类
        if (isset($filter['grade_code']) && $filter['grade_code'] != '') {
            $arr = explode(',', $filter['grade_code']);
            $str = $this->arr_to_in_sql_value($arr,'grade_code',$sql_values);
            $sql_main .= " AND r3.grade_code in ({$str})";
        }
        //分销商代码
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $arr = explode(',', $filter['custom_code']);
            $str = $this->arr_to_in_sql_value($arr,'custom_code',$sql_values);
            $sql_main .= " AND r4.custom_code IN({$str})";
        }
        $select = 'rl.*';
        $sql_main .= " group by goods_line_code ";
//var_dump($sql_main,$sql_values);die;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 生成单据号
     */
    function create_fast_bill_sn() {
        $sql = "select id from {$this->table} order by id desc limit 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = "FXCPX" . add_zero($djh, 3);
        return $jdh;
    }

    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {
        return $this->get_row(array('id' => $id));
    }

    /*
     * 添加新纪录
     */

    function insert($data) {
        $valid_ret = $this->valid($data);
        if ($valid_ret['status'] < 0) {
            return $valid_ret;
        }
        $ret = $this->is_exists($data['goods_line_name'], 'goods_line_name');
        if (!empty($ret['data'])) {
            return $this->format_ret('-1', '', '产品线名称重复');
        }
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['fx_goods_manage'] = date("Y-m-d H:i:s");
        return parent::insert($data);
    }

    function get_goods_list($filter) {
        $sql_join = "";
        $sql_main = " FROM fx_goods rl $sql_join WHERE 1";
        $sql_values = array();
        if (isset($filter['goods_line_code']) && $filter['goods_line_code'] !== '') {
            $sql_main .= " AND rl.goods_line_code = :goods_line_code ";
            $sql_values[':goods_line_code'] = $filter['goods_line_code'];
        }
        $select = 'rl.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            $value['spec_name'] = "颜色：" . $value['spec1_name'] . "；尺码：" . $value['spec2_name'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_custom_grade_by_page($filter){
        $sql_join = " LEFT JOIN fx_custom_grades AS r2 ON r2.grade_code = rl.grade_code";
        $sql_main = " FROM fx_goods_price_custom_grade rl $sql_join WHERE 1";
        $sql_values = array();
        if (isset($filter['goods_line_code']) && $filter['goods_line_code'] !== '') {
            $sql_main .= " AND rl.goods_line_code = :goods_line_code ";
            $sql_values[':goods_line_code'] = $filter['goods_line_code'];
        }
        $select = 'rl.*,r2.grade_name AS grade_name_new';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    function get_custom_by_page($filter){
        $sql_join = " LEFT JOIN base_custom AS r2 ON r2.custom_code = rl.custom_code";
        $sql_main = " FROM fx_goods_price_custom rl $sql_join WHERE 1";
        $sql_values = array();
        if (isset($filter['goods_line_code']) && $filter['goods_line_code'] !== '') {
            $sql_main .= " AND rl.goods_line_code = :goods_line_code ";
            $sql_values[':goods_line_code'] = $filter['goods_line_code'];
        }
        $select = 'rl.*,r2.custom_name AS custom_name_new';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function insert_goods($data) {
        if (empty($data['goods_line_code'])) {
            return $this->format_ret(-1, '', '产品线代码为空!设置失败');
        }
        $params = array();
        foreach ($data['data'] as $val) {
            $p = array();
            $p['goods_line_code'] = $data['goods_line_code'];
            $p['goods_name'] = $val['goods_name'];
            $p['goods_code'] = $val['goods_code'];
            $p['goods_barcode'] = $val['barcode'];
            $p['goods_sku'] = $val['sku'];
            $p['spec1_code'] = $val['spec1_code'];
            $p['spec1_name'] = $val['spec1_name'];
            $p['spec2_code'] = $val['spec2_code'];
            $p['spec2_name'] = $val['spec2_name'];
            $params[] = $p;
        }
        $this->begin_trans();
        try {
            $ret = M('fx_goods')->insert_multi($params, true);
            if ($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            $update_ret = $this->update_goods_manage_num($data['goods_line_code']);
            if (!$update_ret) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新分销产品线管理商品数量失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $ret;
    }

    function delete_all_goods($goods_line_code) {
        if (empty($goods_line_code)) {
            return $this->format_ret(-1, '', '分销产品管理代码为空!删除失败');
        }
        $this->begin_trans();
        try {
            $sql = "delete from fx_goods where goods_line_code = :goods_line_code";
            $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $update_ret = $this->update_goods_manage_num($goods_line_code);
            if ($update_ret == false) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新分销产品管理商品数量失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
        return $this->format_ret(-1, '', '删除失败');
    }

    function do_delete($goods_line_code) {
        if (empty($goods_line_code)) {
            return $this->format_ret(-1, '', '分销产品管理代码为空!删除失败');
        }
        $this->begin_trans();
        try {
            $ret = $this->delete(array('goods_line_code' => $goods_line_code));
            if($ret['status'] < 0){
                return $this->format_ret(-1,'','删除分销产品线失败');
            }
            $sql = "delete from fx_goods where goods_line_code = :goods_line_code";
            $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $sql = "delete from fx_goods_price_custom where goods_line_code = :goods_line_code";
            $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $sql = "delete from fx_goods_price_custom_grade where goods_line_code = :goods_line_code";
            $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function update_goods_manage_num($goods_line_code) {
        $sql = "select count(DISTINCT goods_code) as goods_num, COUNT(DISTINCT goods_sku) as sku_num from fx_goods WHERE goods_line_code = :goods_line_code";
        $row = $this->db->get_row($sql, array(":goods_line_code" => $goods_line_code));
        $update_sql = "update fx_goods_manage set goods_num = :goods_num, sku_num = :sku_num,last_change_time=:last_change_time where goods_line_code = :goods_line_code";
        $ret = $this->db->query($update_sql, array(":goods_line_code" => $goods_line_code, ":goods_num" => $row['goods_num'], ":sku_num" => $row['sku_num'],":last_change_time"=>date("Y-m-d H:i:s")));
        return $ret;
    }

    function do_import_goods($file, $goods_line_code) {
        set_time_limit(0);
        $csv_data = $this->read_goods_csv($file);
        $bar = array();
        foreach ($csv_data as $csv_row) {
            $bar[] = $csv_row['barcode'];
        }
        //查询sku信息
        $bar_str = "'" . implode("','", $bar) . "'";
        $sql = "select sku,barcode from goods_sku where barcode in($bar_str)";
        $bar_ret = $this->db->get_all($sql);
        $sku_info = array();
        foreach ($bar_ret as $bar_row) {
            $sku_info[$bar_row['barcode']] = $bar_row;
        }
        //组装插入数据
        $msg_arr = array();
        $goods_data = array();
        $goods_data['goods_line_code'] = $goods_line_code;
        foreach ($csv_data as $key => $csv_row) {
            //条码不存在
            if (!isset($sku_info[$csv_row['barcode']])) {
                $msg_arr[] =  "条码：" . $csv_row['barcode'] . "在系统不存在";
                continue;
            }
            $key_arr = array('goods_name','goods_code','barcode','spec1_code','spec1_name','spec2_code','spec2_name');   
            $detail_info =  load_model('goods/SkuCModel')->get_sku_info($sku_info[$csv_row['barcode']]['sku'],$key_arr);
            $goods_data['data'][] = array(
                'sku' => $sku_info[$csv_row['barcode']]['sku'],
                'goods_name' => $detail_info['goods_name'],
                'barcode' => $detail_info['barcode'],
                'goods_code' => $detail_info['goods_code'],
                'spec1_code' => $detail_info['spec1_code'],
                'spec1_name' => $detail_info['spec1_name'],
                'spec2_code' => $detail_info['spec2_code'],
                'spec2_name' => $detail_info['spec2_name']
            );
        }
        $this->insert_goods($goods_data);

        if (!empty($msg_arr)) {
            $file_name = $this->create_import_fail_files($msg_arr, 'fx_goods_manage_import_fail');
//            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '');
    }

    //读取活动商品数据
    function read_goods_csv($file) {
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'barcode'
        );
        $csv_data = $exec->read_csv($file, 1, $key_arr);
        return $csv_data;
    }

    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg_arr as $key => $val) {
            $file_str .= $val . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
    
    function do_delete_goods($goods_id,$goods_line_code){
        if(empty($goods_id) || empty($goods_line_code)){
           return $this->format_ret('-1','','商品编号不存在，删除失败'); 
        }
        $this->begin_trans();
        try {
            $sql = "delete from fx_goods where goods_id = :goods_id";
            $delete_ret = $this->db->query($sql, array(":goods_id" => $goods_id));
            if ($delete_ret == FALSE) {
                $this->rollback();
                return $this->format_ret(-1, '', '删除失败！');
            }
            $update_ret = $this->update_goods_manage_num($goods_line_code);
            if ($update_ret == false) {
                $this->rollback();
                return $this->format_ret(-1, '', '更新分销产品管理商品数量失败！');
            }
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', $e->getMessage());
        }
    }

    function insert_custom_grade($data){
        if(empty($data['goods_line_code'])){
            return $this->format_ret(-1,'','添加失败');
        }
        $params =array();
        $params['goods_line_code'] = $data['goods_line_code'];
        $params['grade_code'] = $data['grade_code'];
        $params['grade_name'] = $this->db->get_value("select grade_name from fx_custom_grades where grade_code = :grade_code",array(":grade_code" => $data['grade_code']));
        $params['rebates'] = $data['rebates'];
        $ret = M('fx_goods_price_custom_grade')->insert_dup($params);
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $data['goods_line_code']));
        return $ret;
    }
    
    function delete_custom_grade($price_custom_grade_id,$goods_line_code){
        if(empty($price_custom_grade_id)){
           return $this->format_ret('-1','','分销商等级编号不存在，删除失败'); 
        }
        $sql = "delete from fx_goods_price_custom_grade where price_custom_grade_id = :price_custom_grade_id";
        $delete_ret = $this->db->query($sql, array(":price_custom_grade_id" => $price_custom_grade_id));
        if ($delete_ret == FALSE) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败！');
        }
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $goods_line_code));
        return $this->format_ret(1,'','删除成功');
    }
    
    function delete_all_custom_grade($goods_line_code){
        if (empty($goods_line_code)) {
            return $this->format_ret(-1, '', '分销产品管理代码为空!删除失败');
        }
     
        $sql = "delete from fx_goods_price_custom_grade where goods_line_code = :goods_line_code";
        $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
        if ($delete_ret == FALSE) {
            return $this->format_ret(-1, '', '删除失败！');
        }
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $goods_line_code));
        return $this->format_ret(1,'','删除成功');
    }
            
    function do_add_custom($data){
        if(empty($data['goods_line_code'])){
           return $this->format_ret('-1','','分销商等级编号不存在，删除失败'); 
        }
        $params = array();
        $params['goods_line_code'] = $data['goods_line_code'];
        $params['custom_code'] = $data['custom_code'];
        $params['custom_name'] = $this->db->get_value("select custom_name from base_custom where custom_code = :custom_code",array(":custom_code" => $data['custom_code']));
        $params['rebates'] = $data['rebates'];
        $ret = M('fx_goods_price_custom')->insert_dup($params);
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $data['goods_line_code']));
        return $ret;
    }
    
    function delete_custom($price_custom_id,$goods_line_code){
        if(empty($price_custom_id)){
           return $this->format_ret('-1','','分销商编号不存在，删除失败'); 
        }
        $sql = "delete from fx_goods_price_custom where price_custom_id = :price_custom_id";
        $delete_ret = $this->db->query($sql, array(":price_custom_id" => $price_custom_id));
        if ($delete_ret == FALSE) {
            $this->rollback();
            return $this->format_ret(-1, '', '删除失败！');
        }
        $ret = $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $goods_line_code));
        return $this->format_ret(1,'','删除成功');
    }
    
    function delete_all_custom($goods_line_code){
        if (empty($goods_line_code)) {
            return $this->format_ret(-1, '', '分销产品管理代码为空!删除失败');
        }
        $sql = "delete from fx_goods_price_custom where goods_line_code = :goods_line_code";
        $delete_ret = $this->db->query($sql, array(":goods_line_code" => $goods_line_code));
        if ($delete_ret == FALSE) {
            return $this->format_ret(-1, '', '删除失败！');
        }
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $goods_line_code));
        return $this->format_ret(1,'','删除成功');
    }
    
    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['goods_line_name']) || !valid_input($data['goods_line_name'], 'required'))) {
            return $this->format_ret(-1, '', '产品线名称');
        }
        if (!isset($data['goods_line_code']) || !valid_input($data['goods_line_code'], 'required')) {
            return $this->format_ret(-1, '', '产品线代码');
        }
        return 1;
    }

    private function is_exists($value, $field_name = 'goods_line_code') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function do_import_custom($file, $goods_line_code) {
        set_time_limit(0);
        $csv_data = $this->read_custom_csv($file);
        $custom = array();
        foreach ($csv_data as $csv_row) {
            $custom[] = $csv_row['custom_code'];
        }
        //查询sku信息
        $custom_str = "'" . implode("','", $custom) . "'";
        $sql = "select custom_code,custom_name from base_custom where custom_code in($custom_str)";
        $custom_ret = $this->db->get_all($sql);
        $custom_info = array();
        foreach ($custom_ret as $row) {
            $custom_info[$row['custom_code']] = $row;
        }
        //组装插入数据
        $msg_arr = array();
        $custom_data = array();
        foreach ($csv_data as $csv_row) {
            if (!isset($custom_info[$csv_row['custom_code']])) {
                $msg_arr[] =  "分销code：" . $csv_row['custom_code'] . "在系统不存在";
                continue;
            }
            if (empty($csv_row['rebates'])) {
                $msg_arr[] =  "分销code：" . $csv_row['custom_code'] . "折扣为空";
                continue;
            }
            $custom_data[] = array(
                'goods_line_code' => $goods_line_code,
                'custom_code' => $custom_info[$csv_row['custom_code']]['custom_code'],
                'custom_name' => $custom_info[$csv_row['custom_code']]['custom_name'],
                'rebates' => $csv_row['rebates'],
            );
        }
        $ret = M('fx_goods_price_custom')->insert_multi($custom_data, true);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $this->update(array("last_change_time" => date("Y-m-d H:i:s")), array("goods_line_code" => $goods_line_code));
        if (!empty($msg_arr)) {
            $file_name = $this->create_import_fail_files($msg_arr, 'fx_custom_manage_import_fail');
//            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $msg .= "部分导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            return $this->format_ret(-1, '', $msg);
        }
        return $this->format_ret(1, '');
    }
    
    //读取活动商品数据
    function read_custom_csv($file) {
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'custom_code','rebates'
        );
        $csv_data = $exec->read_csv($file, 1, $key_arr);
        return $csv_data;
    }
    /**
     * 
     * @param type $sku 
     * @param type $custom_code
     * @return type 返回最低折扣，没匹配到返回-1
     */
    function get_fx_price($sku,$custom_code){
        //获取吊牌价
        $key_arr = array('sell_price','trade_price');   
        $detail_info =  load_model('goods/SkuCModel')->get_sku_info($sku,$key_arr);
        $sell_price = $detail_info['sell_price'];
        $trade_price = $detail_info['trade_price'];
        //指定分销商定价高于指定分销等级定价，查询分销商在指定分销商的产品线
        $sql = "SELECT goods_line_code,rebates FROM fx_goods_price_custom WHERE custom_code = :custom_code";
        $custom_line_arr = $this->db->get_all($sql,array(':custom_code' => $custom_code));
        //如果存在指定分销商产品线,计算产品线最低折扣
        if(!empty($custom_line_arr)){
            //计算多个产品线中最低的折扣
            $min_rebates = $this->js_min_rebates($custom_line_arr,$sku);
            if($min_rebates == -1) { //没匹配到折扣计算分类折扣
                $min_rebates = $this->min_fx_grade_rebates($custom_code,$sku);
                //没匹配到分类的折扣返回
                if($min_rebates == -1) {
                    return -1;
                }
            }
            //返回最低折扣
            return $min_rebates * $sell_price;
        } else { // 不存在指定分销商产品线计算分销商分类产品线最低折扣
            //计算分类的最低折扣
            $min_rebates = $this->min_fx_grade_rebates($custom_code,$sku);
            //没匹配到分类的折扣返回
            if ($min_rebates == -1) {
                return -1;
            }
            //返回最低折扣
            return $min_rebates * $sell_price;
        }
    }
    /**
     * 计算产品线折扣（按分销商分类计算）
     * @param $custom_code
     * return min rebates
     */
    function min_fx_grade_rebates($custom_code,$sku) {
        //分销商分类
        $sql = "SELECT grade_code FROM fx_custom_grades_detail WHERE custom_code = :custom_code";
        $csutom_grades_detail = $this->db->get_row($sql, array(':custom_code' => $custom_code));
        //分销商产品线
        $sql = "SELECT goods_line_code,rebates FROM fx_goods_price_custom_grade WHERE grade_code = :grade_code";
        $custom_line_arr = $this->db->get_all($sql, array(':grade_code' => $csutom_grades_detail['grade_code']));
        //不存在任何产品线就返回
        if (empty($custom_line_arr)) {
            return -1;
        }
        //计算多个产品线中最低的折扣
        $min_rebates = $this->js_min_rebates($custom_line_arr,$sku);
        return $min_rebates;
    }

    /**
     * 计算产品线最低折扣
     * @param $custom_line_arr array(goods_line_code,rebates)
     * return min rebates
     */
    function js_min_rebates($custom_line_arr,$sku) {
        $rebates_arr = array();
        foreach ($custom_line_arr as $val) {
            //查询产品线的商品
            $sql = "SELECT goods_sku FROM fx_goods WHERE goods_line_code = :goods_line_code";
            $goods_sku_arr = $this->db->get_all_col($sql, array(':goods_line_code' => $val['goods_line_code']));
            //如果产品线没有设置商品,直接记录产品线折扣
            if (empty($goods_sku_arr)) {
                $rebates_arr[] = $val['rebates'];
            } else { //产品线有商品,判断设置该商品没有
                if (in_array($sku, $goods_sku_arr)) {
                    $rebates_arr[] = $val['rebates'];
                } else {
                    continue;
                }
            }
        }
        //匹配到折扣返回最小的
        if(!empty($rebates_arr)) {
            //升序排序
            sort($rebates_arr);
            return $rebates_arr[0];
        } else {
            return -1;
        }
        
    }

    function do_edit($goods_manager_info, $id) {
        if(empty($goods_manager_info['goods_line_name'])){
            return $this->format_ret(-1,'','产品线名称不能为空');
        }
        $ret = $this->db->get_row("select * from {$this->table} where goods_line_name = :goods_line_name and id != :id",array(":goods_line_name" => $goods_manager_info['goods_line_name'],":id" => $id));
        if (!empty($ret)) {
            return $this->format_ret(-1, '', '产品线名称已存在,修改失败');
        }
        $ret = parent::update($goods_manager_info, array('id' => $id));
        return $ret;
    }
    
    //计算分销结算单价
    function compute_fx_price($custom_code, $goods_data, $check_time = '') {
        //匹配调价单
        $price = $this->get_fx_adjust_price($custom_code, $goods_data['sku'], $check_time);
        if($price > -1) {
            return $price;
        }
        
        //查询该商品有没有指定分销商
        $goods_price = load_model('fx/GoodsModel')->get_goods_custom_price($goods_data['goods_code'],$custom_code);
        if(!empty($goods_price)) { //指定了分销商，分销价为0 也返回
            return $goods_price['fx_price'];
        }
        
        //获取分销商信息
        $ret = load_model('base/CustomModel')->get_by_code($custom_code);
        $custom_data = $ret['data'];
        //没有折扣默认折扣为1
        $custom_data['custom_rebate'] = empty($custom_data['custom_rebate']) ? 1 : $custom_data['custom_rebate'];
        //计算产品线的价格
        $price = load_model('fx/GoodsManageModel')->get_fx_price($goods_data['sku'], $custom_code);
        //没有产品线折扣，计算分销商折扣
        if ($price == -1) {
            $sql = "SELECT sell_price,trade_price FROM base_goods WHERE goods_code = :goods_code";
            $goods = $this->db->get_row($sql,array(':goods_code' => $goods_data['goods_code']));
            if($custom_data['custom_price_type'] == 0) {
                //吊牌价，先算sku级价格，没有就算商品级
                $sql = "SELECT price FROM goods_sku WHERE sku = :sku";
                $sell_price = $this->db->get_value($sql,array(':sku' => $goods_data['sku']));
                $price = empty($sell_price) || $sell_price == 0 ? $goods['sell_price'] * $custom_data['custom_rebate'] : $sell_price * $custom_data['custom_rebate'];
            } else if($custom_data['custom_price_type'] == 2){
                //批发价
                $price = $goods['trade_price'] * $custom_data['custom_rebate'];
            }
        }
        return $price;
    }
    /*
     * 获取退单分销结算金额
     */
    function get_record_fx_money($record_code, $goods_data, $custom_code,$num, $check_time) {
        //查询订单明细是否有匹配的商品
        $sql = "SELECT trade_price FROM oms_sell_record_detail WHERE is_gift = 0 AND sku = :sku AND sell_record_code = :sell_record_code";
        $detail_data = $this->db->get_row($sql,array(':sku' => $goods_data['sku'], ':sell_record_code' => $record_code));
        if($detail_data['trade_price'] != 0) {
            $detail_data['fx_amount'] = $detail_data['trade_price'] * $num;
            return $detail_data;
        }
        $price = $this->compute_fx_price($custom_code,$goods_data, $check_time);
        if($price == -1) {
            return array('fx_amount' => 0, 'trade_price' => 0);
        }
        $fx_amount = $price * $num;
        return array('fx_amount' => $fx_amount, 'trade_price' => $price);
    }
    /**
     * 获取调价单的价格
     * @param type $custom_code
     * @param type $goods_code
     */
    function get_fx_adjust_price ($custom_code, $sku, $check_time) {
        $check_time = strtotime($check_time);
        //查询分销商的分销
        $grades_data = load_model('base/CustomGradesModel')->get_by_detail($custom_code, 'custom_code', 'grade_code');
        $sql_values = array(':start_time' => $check_time, ':end_time' => $check_time, ':sku' => $sku);
        $sql = "SELECT rl.start_time,r2.sku,r2.settlement_money FROM fx_goods_adjust_price_record AS rl LEFT JOIN fx_goods_adjust_price_detail AS r2 ON rl.record_code = r2.record_code WHERE rl.record_status = 1 AND rl.start_time <= :start_time AND rl.end_time >= :end_time AND r2.sku = :sku ";
        if(!empty($grades_data)) {
            $grades_code_arr = array_column($grades_data, 'grade_code');
            $grades_code_str = $this->arr_to_in_sql_value($grades_code_arr,'object_code',$sql_values);
            $sql .= " AND (rl.object_code in ({$grades_code_str}) OR rl.object_code = :custom_code) ";
            $sql_values[':custom_code'] = $custom_code;
        } else {
            $sql .= " AND (rl.object_code = :custom_code) ";
            $sql_values[':custom_code'] = $custom_code;
        }
        $sql .= " GROUP BY rl.record_code ";
        $adjust_data = $this->db->get_all($sql, $sql_values);
        $price = -1;
        if(count($adjust_data) > 1) { //匹配多个取开始时间最接近的
            $max_time = '';
            foreach($adjust_data as $val) {
                if(empty($max_time)) {
                    $price = $val['settlement_money'];
                    $max_time = $val['start_time'];
                }
                if($val['start_time'] > $max_time) {
                    $price = $val['settlement_money'];
                    $max_time = $val['start_time'];
                }
            }
        } else if (count($adjust_data) == 1) {
            $price = $adjust_data[0]['settlement_money'];
        }
        return $price;
    }

}
