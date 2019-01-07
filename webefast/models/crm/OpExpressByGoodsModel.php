<?php

/**
 * 订单快递适配策略相关业务
 * 商品指定快递
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class OpExpressByGoodsModel extends TbModel {

    function get_table() {
        return 'op_express_by_goods';
    }

    /**
     * 获取指定商品列表
     * @param type $filter
     * @return type
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_join = 'LEFT JOIN base_express be  ON  g.express_code = be.express_code';
        $sql_main = "FROM {$this->table} g $sql_join WHERE 1=1";
        if(!empty($filter['express_code'])) {
            $sql_main .= " AND g.express_code = :express_code";
            $sql_values[':express_code'] = $filter['express_code'];
        } else {
            $sql_main .= " AND 1 != 1";
        }
        $select = 'g.*,be.express_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        foreach ($ret_data['data'] as $key => &$value) {
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode', 'goods_code');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            $value = array_merge($value, $sku_info);
            //$ret_data['data'][$key] = $value;
            $ret_data['data'][$key]['is_diy_html'] = !empty($value['is_diy']) && $value['is_diy'] == 1 ? '<img src=' . get_theme_url("images/ok.png") . '>' : '<img src=' . get_theme_url("images/no.gif") . '>';
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 获取所有配送方式
     * @return array
     */
    function get_express_list($is_priority = 0) {
        $sql = "SELECT express_code,express_name FROM base_express WHERE status = 1";
        $express_data = $this->db->get_all($sql);
        if($is_priority == 1) {
            $priority_arr = array();
            foreach ($express_data as &$val) {
                $ret = $this->get_by_express_priority($val['express_code']);
                $priority = empty($ret['data']) ? 1 : $ret['data'];
                $val['express_name'] = $val['express_name'] .'[优先级：'.$priority.']';
                $priority_arr[$val['express_code']] = $priority;
            }
            return array('express' => $express_data , 'priority' => $priority_arr);
        }
        return $express_data;
    }
    
    /**
     * 查询配送方式的优先级
     * @return array
     */
    function get_by_express_priority ($express_code) {
        $sql = "SELECT priority FROM op_express_priority WHERE express_code = :express_code";
        $priority = $this->db->get_value($sql,array(':express_code' => $express_code));
        return $this->format_ret(1,$priority);
    }

    /**
     * 获取指定配送方式
     * @return arary
     */
    function get_appoint_express() {
        $sql = "SELECT express_code,sku FROM {$this->table}";
        $appoint_express = $this->db->get_all($sql);
        foreach ($appoint_express as $val) {
            if (!empty($val['express_code'])) {
                $appoint_express = $val['express_code'];
                break;
            }
        }
        return $appoint_express;
    }

    /**
     * 增加指定商品
     * @param arary $params
     * @return arary
     */
    function add_goods($params) {
        $goods_arr = array();
        $appoint_express = !empty($params['appoint_express']) ? $params['appoint_express'] : '';
        if(empty($appoint_express)) {
           return $this->format_ret(-1,'','没有配送方式'); 
        }
        $data = $this->db->get_row('SELECT goods_priority FROM op_express_by_goods WHERE express_code = :express_code',array(':express_code' => $appoint_express));
        $this->begin_trans();
        foreach ($params['data'] as $val) {
            $goods = array(
                'sku' => $val['sku'],
                'express_code' => $appoint_express,
                'goods_priority' => $data['goods_priority'],
            );
            $sql_diy = "select count(1) from goods_diy where p_sku='".$val['sku']."'";
            $count_diy = $this->db->getOne($sql_diy);
            if($count_diy > 0){
                $goods['is_diy'] = 1;
            }
            $goods_arr[] = $goods;
        }
        $update_str = "goods_priority = VALUES(goods_priority),express_code = VALUES(express_code)";
        $ret = $this->insert_multi_duplicate($this->table, $goods_arr, $update_str);
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $this->commit();
        return $ret;
    }
    /**
     * 导入商品到快递策略
     * @param int $id
     * @return array
     */
    function imoprt_detail($id, $sku_arr, $import_data, $is_lof = 0){
        $appoint_express = !empty($id) ? $id : '';
        if(empty($appoint_express)) {
            return $this->format_ret(-1,'','没有配送方式');
        }
        $data = $this->db->get_row('SELECT goods_priority FROM op_express_by_goods WHERE express_code = :express_code',array(':express_code' => $appoint_express));
        //获取导入的总条数
        $err_num = count($import_data);
        $error_msg = array();
        foreach($import_data as $val){
            $sql = "SELECT * FROM goods_sku WHERE barcode = '{$val['sku']}';";
            $barcode = $this->db->get_row($sql);
            if (empty($barcode)) {
                $error_msg[] = array($val['sku'] => '商品条码不存在');
                continue;
            }
            $check_sql="select * from op_express_by_goods where sku=:sku and express_code!=:express_code";
            $ret=$this->db->get_row($check_sql,array(':sku'=>$barcode['sku'],':express_code'=>$appoint_express));
            if(!empty($ret)){
                $error_msg[] = array($val['sku'] => '商品条码已匹配其他快递方式');
                continue;
            }
            $goods = array(
                'sku' => $barcode['sku'],
                'express_code' => $appoint_express,
                'goods_priority' => $data['goods_priority'],
            );
            $sql_diy = "select count(1) from goods_diy where p_sku='".$barcode['sku']."'";
            $count_diy = $this->db->getOne($sql_diy);
            if($count_diy > 0){
                $goods['is_diy'] = 1;
            }
            $goods_arr[] = $goods;
        }
        if(!empty($goods_arr)){
            $this->begin_trans();
            $update_str = "goods_priority = VALUES(goods_priority),express_code = VALUES(express_code)";
            $ret = $this->insert_multi_duplicate($this->table, $goods_arr, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
            }
            $this->commit();
        }
        $result['success'] = count($goods_arr);
        $success_num = $result['success'];
        $message = '导入成功' . $success_num;
        //失败数量
        $err_num = $err_num - $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }
    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("notice_record_detail_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
    /**
     * 根据id删除指定商品
     * @param int $id
     * @return array
     */
    function delete_goods($id) {
        $ret = parent::delete(array('op_express_id' => $id));
        return $ret;
    }

    /**
     * 删除全部指定商品
     * @return array
     */
    function delete_all_goods($express_code) {
        $ret = parent::delete(array('express_code' => $express_code));
        return $ret;
    }

    /**
     * 更新指定配送方式
     * @param string $express_code
     * @return array
     */
    function do_update_express($express_code) {
        $this->begin_trans();
        $ret = parent::update(array('express_code' => $express_code));
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $this->commit();
        return $ret;
    }
    /**
     * 更新优先级
     * @param  int $goods_priority $string $express_code
     * @return array
     */
    function do_update_priority($goods_priority,$express_code) {
        $this->begin_trans();
//        $sql = "UPDATE op_express_by_goods SET goods_priority = :goods_priority WHERE express_code = :express_code";
        $ret = parent::update(array('goods_priority' => $goods_priority),array('express_code' => $express_code));
        if ($ret['status'] != 1) {
            $this->rollback();
        }
        $data = array(
            'express_code' => $express_code,
            'priority' => $goods_priority
        );
        $update_str = "priority = VALUES(priority)";
        $this->insert_multi_duplicate('op_express_priority', array($data), $update_str);
        $this->commit();
        return $ret;
    }

    /**
     * 根据sku获取适配快递
     * @param array $info
     * @return array
     */
    function get_express_by_sku($info) {
        $sku_arr = array();
        foreach ($info as $val) {
            $sku_arr[] = $val['sku'];
        }
        $sql_values = array();
        $sku_str = $this->arr_to_in_sql_value($sku_arr,'sku',$sql_values);
        $sql = "SELECT express_code FROM op_express_by_goods WHERE sku IN ({$sku_str})";
        $express_code = $this->db->get_all_col($sql,$sql_values);
        if (empty($express_code)) {
            return $this->format_ret(-1,'','不存在指定快递');
        }
        $sql_values = array();
        $express_str = $this->arr_to_in_sql_value($express_code,'express_code',$sql_values);
        $sql = "SELECT priority AS goods_priority,express_code FROM op_express_priority WHERE express_code IN ({$express_str})";
        $express_data = $this->db->get_all($sql,$sql_values);
        if(empty($express_data)) { //商品有快递，没有优先级，返回第一个
            return $this->format_ret(1, $express_code[0], '按"商品指定快递"适配成功');
        }
        //取优先级最高的快递,同优先级随机取
        $max_express = '';
        $max_priority = 0;
        foreach ($express_data as $val) {
            if($val['goods_priority'] > $max_priority){
                $max_priority = $val['goods_priority'];
                $max_express = $val['express_code'];
            } else if($val['goods_priority'] == $max_priority) {
                $max_priority = $val['goods_priority'];
                $max_express = $val['express_code'];
            }
        }
        if(empty($max_express)) {
            return $this->format_ret(-1,'','不存在指定快递');
        }
        
        return $this->format_ret(1, $max_express, '按"商品指定快递"适配成功');
    }
    //获取所有以选择的信息
    function do_sku_all() {
        $sql = "SELECT sku FROM op_express_by_goods";
        $data = $this->db->get_all_col($sql);
        return $data;
    }
    

}
