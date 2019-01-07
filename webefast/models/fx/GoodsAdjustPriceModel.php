<?php

/**
 * 商品调价单相关业务
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class GoodsAdjustPriceModel extends TbModel {

    function get_table() {
        return 'fx_goods_adjust_price_record';
    }
    private $settlement_price_type = array(
        0 => '吊牌价', 1 => '成本价', 2 => '批发价', 3 => '进货价'
    );

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_join = " LEFT JOIN fx_goods_adjust_price_detail r2 ON rl.record_code=r2.record_code ";
        $sql_main = " FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        //单据编号
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND rl.record_code LIKE :record_code";
            $sql_values[':record_code'] = '%' . $filter['record_code'] . '%'; 
        }
        
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND r2.goods_code = :goods_code ";
            $sql_values[':goods_code'] = $filter['goods_code'];
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] !== '') {
            $sku_arr = load_model('prm/GoodsBarcodeModel')->get_sku_by_barcode($filter['barcode']);
            if (empty($sku_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $sku_str = "'" . implode("','", $sku_arr) . "'";
                $sql_main .= " AND r2.sku in({$sku_str}) ";
            }
        }
        //分销商
        if (isset($filter['custom_code']) && $filter['custom_code'] != '') {
            $custom_arr = explode(",", $filter['custom_code']);
            $custom_str = $this->arr_to_in_sql_value($custom_arr, 'object_code', $sql_values);
            $sql_main .= " AND rl.object_code in ({$custom_str})";
        }
        //分销商分类
        if (isset($filter['grade_code']) && $filter['grade_code'] != '') {
            $arr = explode(',', $filter['grade_code']);
            $str = $this->arr_to_in_sql_value($arr,'object_code',$sql_values);
            $sql_main .= " AND rl.object_code in ({$str})";
        }


         //时间查询
        if (isset($filter['start_time']) && $filter['start_time'] != '') {
            $filter['start_time'] = strtotime($filter['start_time']);
            switch ($filter['time_type']) {
                //创建时间
                case 'add_time':
                    $sql_main .= " AND rl.add_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //开始时间
                case 'start_time':
                    $sql_main .= " AND rl.start_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
                //结束时间
                case 'end_time':
                    $sql_main .= " AND rl.end_time >= :start_time ";
                    $sql_values[':start_time'] = $filter['start_time'];
                    break;
            }
        }
        if (isset($filter['end_time']) && $filter['end_time'] != '') {
            $filter['end_time'] = strtotime($filter['end_time']);
            switch ($filter['time_type']) {
                //创建时间
                case 'add_time':
                    $sql_main .= " AND rl.add_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //开始时间
                case 'start_time':
                    $sql_main .= " AND rl.start_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
                //结束时间
                case 'end_time':
                    $sql_main .= " AND rl.end_time <= :end_time ";
                    $sql_values[':end_time'] = $filter['end_time'];
                    break;
            }
        }
        if($filter['ctl_export_conf']=="goods_adjust_price_do_list_detail"){
            $sql_main .= '  ORDER BY rl.add_time DESC ';
        }else{
            $sql_main .= ' GROUP BY rl.record_code ORDER BY rl.add_time DESC ';
        }
        $select = 'rl.*';
        if($filter['ctl_export_conf']=="goods_adjust_price_do_list_detail"){
            $select = 'rl.adjust_price_record_id,rl.record_code,rl.settlement_price_type,rl.start_time,rl.end_time,rl.user_code,rl.add_time,rl.adjust_price_object,rl.object_code,r2.sku,r2.goods_code,r2.spec1_code,r2.spec2_code,r2.settlement_price,r2.settlement_money,r2.settlement_rebate';
        }
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, TRUE);
        foreach ($data['data'] as &$val) {
            $val['add_time'] = date('Y-m-d H:i:s',$val['add_time']);
            $val['start_time'] = date('Y-m-d H:i:s',$val['start_time']);
            $val['end_time'] = date('Y-m-d H:i:s',$val['end_time']);
            $val['object_name'] = $this->get_adjust_object_name($val['object_code'],$val['adjust_price_object']);
            $val['settlement_price_type'] = $this->settlement_price_type[$val['settlement_price_type']];
            $key_arr = array('goods_name','barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_arr);
            $val = array_merge($val, $sku_info);
            $spec1_name=load_model('prm/Spec1Model')->get_spec1_name($val['spec1_code']);
            $spec2_name=load_model('prm/Spec2Model')->get_spec2_name($val['spec2_code']);
            if(!empty($spec1_name)||!empty($spec2_name)){
                $val['spec1_spec2']=$spec1_name.';'.$spec2_name;
            }else{
                $val['spec1_spec2']='';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    /**
     * 
     */
    function get_adjust_object_name($object_code, $adjust_object_type) {
        if ($adjust_object_type == 1) { //针对分销商调价，取分销商名称
            $objece_name = oms_tb_val('base_custom', 'custom_name', array('custom_code' => $object_code));
        } else if ($adjust_object_type == 2) {
            $objece_name = oms_tb_val('fx_custom_grades', 'grade_name', array('grade_code' => $object_code));
        }
        return $objece_name;
    }

    /**
     * 生成单据号
     * @return string
     */
    
    function create_fast_bill_sn() {
        $sql = "SELECT adjust_price_record_id FROM {$this->table} ORDER BY adjust_price_record_id DESC LIMIT 1 ";
        $data = $this->db->get_all($sql);
        if ($data) {
            $djh = intval($data[0]['adjust_price_record_id']) + 1;
        } else {
            $djh = 1;
        }
        require_lib('comm_util', true);
        $jdh = 'TJ' . date('Ymd') . add_zero($djh) . mt_rand(100,999);
        return $jdh;
    }
    
    /**
     * 新建商品调价单
     */
    function create_goods_adjust_record($params) {
        IF(empty($params['start_time']) || empty($params['end_time'])) {
            return $this->format_ret(-1, '', '开始时间或者结束时间为空');            
        }
        if (strtotime($params['start_time']) <= time()) {
            return $this->format_ret(-1, '', '开始时间不能小于当前时间');
        }
        if (strtotime($params['start_time']) >= strtotime($params['end_time'])) {
            return $this->format_ret(-1, '', '开始时间不能大于结束时间');
        }
        $data = array(
            'record_code' => $params['record_code'],
            'record_status' => 0,
            'adjust_price_object' => $params['adjust_price_object'],
            'object_code' => $params['adjust_price_object'] == 1 ? $params['custom_code'] : $params['custom_grades'],
            'settlement_price_type' => $params['settlement_price_type'],
            'settlement_rebate' => $params['settlement_rebate'],
            'start_time' => strtotime($params['start_time']),
            'end_time' => strtotime($params['end_time']),
            'user_code' => CTX()->get_session('user_code'),
            'add_time' => time(),
        );
        $ret = $this->insert($data);
        return $ret;
    }
    /**
     * 获取商品调价单信息
     * @param type $id
     * @param type $select
     */
    function get_by_id($id,$select = '*') {
        $sql = "SELECT {$select} FROM fx_goods_adjust_price_record WHERE adjust_price_record_id = :adjust_price_record_id ";
        $ret = $this->db->get_row($sql,array(':adjust_price_record_id' => $id));
        //获取对象名称        
        $ret['object_name'] = !empty($ret['object_code']) ? $this->get_adjust_object_name($ret['object_code'], $ret['adjust_price_object']) : '';
        $user_data = !empty($ret['user_code']) ? load_model('sys/UserModel')->get_by_code($ret['user_code'],'user_name') : '';
        $ret['user_name'] = $user_data['user_name'];
        $ret['settlement_price_name'] = isset($ret['settlement_price_type']) && $ret['settlement_price_type'] != '' ? $this->settlement_price_type[$ret['settlement_price_type']] : '';
        return $ret;
    }
    
    function check_record($record_data) {
        if(empty($record_data)) {
            return $this->format_ret(-1,'','主单据信息不存在');
        }
        if($record_data['record_status'] == 1) {
            return $this->format_ret(-1,'','单据已启用');
        }
        return $this->format_ret(1);
    }
    /**
     * 停用或启用单据
     * @param type $id
     * @param type $active
     * @return type
     */
    
    function update_active($id, $active) {
        if(!in_array($active, array(1, 0))) {
            return $this->format_ret(-1,'','单据异常');
        }
        if($active == 1) {
            $record_data = $this->get_by_id($id);
            $check = $this->check_record($record_data);
            if($check['status'] < 0) {
                return $check;
            }
        }
        $detail_model = load_model('fx/GoodsAdjustPriceDetailModel');
        $detail = $detail_model->get_by_detail($id, 'pid');
        if(empty($detail)) {
            return $this->format_ret(-1,'','明细为空，不能启用');
        }
        $this->begin_trans();
        //启用调价单
        $ret = $this->update(array('record_status' => $active), array('adjust_price_record_id' => $id));
        if($ret['status'] < 0) {
            return $this->format_ret(-1,'','启用失败');
        }
        //添加日志
        $action_name = $active == '1' ? '启用' : '停用'; 
        $action_remark = $active == '1' ? '启用单据' : '停用单据';
        $ret = $detail_model->insert_adjust_goods_price_log($id, $active, $action_name, $action_remark);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $ret;
    }
    /*
     * 修改调价单主表信息
     */
    function update_data($data, $where) {
        $ret = $this->update($data, $where);
        return $ret;
    }
    /**
     * 删除调价单
     * @param type $id
     */
    function do_delete($id) {
        $record_data = $this->get_by_id($id);
        $check = $this->check_record($record_data);
        if ($check['status'] < 0) {
            return $check;
        }
        $this->begin_trans();
        $ret = $this->delete(array('adjust_price_record_id' => $id));
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','删除失败');
        }
        //删除明细
        $ret = $this->delete_exp('fx_goods_adjust_price_detail', array('pid' => $id));
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','删除失败');
        }
        $this->commit();
        return $this->format_ret(1,'','删除成功');;
    }

}
