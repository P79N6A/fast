<?php

/**
 * 库单详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class StmGoodsDiyRecordDetailModel extends TbModel {

    function get_table() {
        return 'stm_goods_diy_record_detail';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM stm_goods_diy_record rl  
		            INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code 
		            INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
		            INNER JOIN goods_sku r4 on r2.sku = r4.sku
		            WHERE  1=1  ";
        $sql_values = array();
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name  or r4.barcode LIKE :code_name)";
            $sql_values[':code_name'] =  '%' .$filter['code_name'] . '%';
        }
        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND (r2.type = :type )";
            $sql_values[':type'] = $filter['type'];
        }

        $select = 'r2.*,r3.goods_name,r4.barcode,r4.spec1_name,r4.spec2_name,rl.is_sure,rl.is_check';
       // var_dump($sql_main);var_dump($sql_values);exit;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);//var_dump($data);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['price'] = round($value['price'], 2);
            $data['data'][$key]['money'] = round($value['money'], 2);
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_page_lof($filter) {
        $sql_join = "";
        $sql_main = "FROM stm_goods_diy_record rl
		INNER JOIN {$this->table} r2 on rl.record_code = r2.record_code
                INNER JOIN base_goods r3 on r3.goods_code = r2.goods_code
                ";

        $sql_values = array();
        //$sql_values = array();
        // record_code查询
        if (isset($filter['record_code']) && $filter['record_code'] != '') {
            $sql_main .= " AND (r2.record_code = :record_code )";
            $sql_values[':record_code'] = $filter['record_code'];
        } else {
            return;
        }
        
        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND (r2.type = :type )";
            $sql_values[':type'] = $filter['type'];
        }
        //商品货号或原因
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (r2.goods_code LIKE :code_name or r3.goods_name LIKE :code_name)";
            $sql_values[':code_name'] = '%'.$filter['code_name'] . '%';
        }

        $sql_main .= " order by r2.diy_sku";
        //$select = 'r2.*,r3.goods_name,r4.barcode,rl.is_store_out';
        $select = 'r2.*,rl.is_sure,rl.is_check,r3.goods_name,r3.purchase_price';
        //echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            $key_arr = array('spec1_name', 'spec2_name','barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($value['sku'], $key_arr);
            if(!empty($value['diy_sku'])){
                $diy_sku_arr = array('goods_name','barcode');
                $diy_sku_info = load_model('goods/SkuCModel')->get_sku_info($value['diy_sku'], $diy_sku_arr);
            }
            $data['data'][$key]['diy_barcode'] = $diy_sku_info['barcode'];
            $data['data'][$key]['diy_goods_name'] = $diy_sku_info['goods_name'];
            $data['data'][$key]['spec1_name'] = $sku_info['spec1_name'];
            $data['data'][$key]['spec2_name'] = $sku_info['spec2_name'];
            $data['data'][$key]['barcode'] = $sku_info['barcode'];
            $data['data'][$key]['price1'] = round($data['data'][$key]['price'], 3);
            $data['data'][$key]['money'] = $data['data'][$key]['price'] * $value['num'];
            $data['data'][$key]['money'] = round($data['data'][$key]['money'], 3);
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_select_data($id, $select_sku_arr) {
        $sku_str = "'" . implode("','", $select_sku_arr) . "'";
        $sql = " select goods_code,spec1_code,spec2_code,sku,refer_price,price,rebate,money,num,finish_num,(num-finish_num) as diff_num
    	from  pur_return_notice_record_detail  where pid='{$id}' and sku in($sku_str)";
        $data = $this->db->get_all($sql);

        return $this->format_ret(1, $data);
    }

    //根据单据编号查询
    function get_by_record_code($record_code) {
        $data = $this->get_all(array('record_code' => $record_code));
        return $data;
    }

    /**
     * 更新库存调整明细
     * @param array $ary_detail
     * @param array $where
     * @return array
     * @throws Exception
     */
    function update($ary_detail, $where) {
        //如果规格1 规格2 不存在, 通过sku获取到规格1 规格2的代码和名称
        if (isset($ary_detail['sku']) && !empty($ary_detail['sku'])) {
            $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
            $ary_detail['goods_id'] = $info['goods_id'];
            $ary_detail['goods_code'] = $info['goods_code'];
            $ary_detail['spec1_id'] = $info['spec1_id'];
            $ary_detail['spec1_code'] = $info['spec1_code'];
            $ary_detail['spec2_id'] = $info['spec2_id'];
            $ary_detail['spec2_code'] = $info['spec2_code'];
        }
        return parent::update($ary_detail, $where);
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete($id) {
        $detail = $this->get_row(array('goods_diy_record_detail_id' => $id));
        if (isset($detail['data']['pid'])) {
            $record = $this->is_exists($detail['data']['pid'], 'goods_diy_record_id');
            if (isset($record['data']['is_sure']) && 1 == $record['data']['is_sure']) {
                return $this->format_ret(false, array(), '单据已确认!不能删除明细');
            }
        }
        //子商品删除
        if(!empty($detail['data']['diy_sku'])){
             $sql = "delete from {$this->table} where goods_diy_record_detail_id = :id";
             $ret = $this->db->query($sql,array(':id' => $id));
        }else{
            //组装商品删除删除商品
            $sql = "delete from {$this->table} where goods_diy_record_detail_id = :id  OR (pid = :pid AND diy_sku = :sku)";
            $ret = $this->db->query($sql,array(':id' => $id, ':pid' => $detail['data']['pid'], ':sku' => $detail['data']['sku']));
        }
        $this->mainWriteBack($detail['data']['pid']);
        if ($ret) {
            return $this -> format_ret("1", '', 'delete_success');
        } else {
            return $this -> format_ret("-1", '', 'delete_error');
        }
    }

    /**
     * 主单据数据回写
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBackDetail($record_id, $sku, $order_type = 'goods_diy') {
        $sql = "update stm_goods_diy_record set
    	num = (select sum(num) from {$this->table} where pid = :pid),
    	money = (select sum(money) from {$this->table} where pid = :pid )
    	where pid = :pid and sku = :sku";
        $res = $this->query($sql, array(':pid' => $record_id));
        return $res;
    }

    /**
     * 根据明细的record_code判断主表单据是否存在
     * @param string $value 值
     * @param string $field_name 字段名
     * @return array 
     */
    public function is_exists($value, $field_name = 'record_code') {

        $m = load_model('stm/StmGoodsDiyRecordModel');
        $ret = $m->get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    public function is_detail_exists($pid, $sku) {
        $ret = $this->get_row(array(
            'pid' => $pid,
            'sku' => $sku
        ));
        if ($ret['status'] && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证明细参数是否正确
     * @param array $data 明细单据
     * @param boolean $is_edit 是否为编辑
     * @return int
     */
    public function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['pid']) || !valid_input($data['pid'], 'required')))
            return RECORD_ERROR_CODE;
        return 1;
    }

    public function add_detail_action($data) {
        $pid = isset($data['pid']) ? $data['pid'] : '';
        $tabs_id = isset($data['tabs_id']) ? $data['tabs_id'] : '';
        $diy_sku = isset($data['diy_sku']) ? $data['diy_sku'] : '';
        $diy_lof_no = isset($data['diy_lof_no']) ? $data['diy_lof_no'] : '';
        $default_lof_Info = load_model('prm/GoodsLofModel')->get_sys_lof();
        $record = $this->is_exists($pid, 'goods_diy_record_id');
        $pici_arr = array();
        $this->begin_trans();
        foreach ($data['data'] as &$detail){
            //组装类型数量为正值，拆分数量为负值
            if ($record['data']['record_type'] == '0') {
                $detail['num'] = abs($detail['num']);
            } else {
                $detail['num'] = 0 - abs($detail['num']);
            }
            $detail['lof_no'] = isset($detail['lof_no']) && $detail['lof_no'] !== '' ? $detail['lof_no'] : $default_lof_Info['data']['lof_no'];
            $detail['production_date'] = !empty($detail['production_date']) ? $detail['production_date'] : $default_lof_Info['data']['production_date'];
            $detail['diy_sku'] = !empty($detail['diy_sku']) ? $detail['diy_sku'] : $data['diy_sku'];
            $pici_exist = load_model('prm/GoodsLofModel')->pici_exist($detail['sku'], $detail['lof_no'], $detail['production_date']);
            if($pici_exist){
                continue;
            }
            $pici_arr['lof_no'] = $detail['lof_no'];
            $pici_arr['production_date'] = $detail['production_date'];
			$pici_arr['sku'] = $detail['sku'];
            $ret = $this->db->insert('goods_lof', $pici_arr);
            if($ret === FALSE){
                $this->rollback();
                return $this->format_ret($ret);
            }
        }
        $this->commit();
        $ary_details = $data['data'];
        //批次档案维护
        $ret = load_model('prm/GoodsLofModel')->add_detail_action($pid, $ary_details, 'goods_diy');
        if($tabs_id == 'tabs_batch'){
            $sql_diy = "select num from {$this->table} where sku = :sku and type = 'diy' and lof_no = :lof_no and pid = :pid";
            $diy_num = $this->db->get_value($sql_diy,array(':sku'=> $diy_sku,':lof_no' => $diy_lof_no,':pid' => $pid));
            foreach ($ary_details as &$info){
                $sql = "select sku,num from goods_diy d WHERE p_sku = :p_sku and sku=:sku ";
                $diy_data = $this->db->get_row($sql,array(":sku" => $info['sku'],':p_sku'=>$info['diy_sku']));
                if(!empty($diy_data)){
                    $info['num'] = 0 - $diy_num*$diy_data['num'];
                }
            }
        }
        $ret = $this->add_detail($pid, $ary_details,$tabs_id,'add');
        return $ret;
    }
    
    //存在更新数量，不存在插入
    function manage_data($data,$type,$add = ''){
        if($type == 'diy'){
            $sql = "select * from {$this->table} where sku = :sku and lof_no = :lof_no and record_code = :record_code";
            $data_row = $this->db->get_row($sql,array(":sku"=> $data['sku'],':lof_no' => $data['lof_no'],':record_code' => $data['record_code']));
        } else {
            $sql = "select * from {$this->table} where sku = :sku and record_code = :record_code and diy_sku = :diy_sku";
            $data_row = $this->db->get_row($sql,array(":sku"=> $data['sku'],':record_code' => $data['record_code'],':diy_sku' => $data['diy_sku']));
        }
        if(!empty($data_row)){
            $params = array();
            $params['num'] = $data['num'];
            $params['money'] = round($params['num']*$params['price'], 3);
            return parent::update($params, array('goods_diy_record_detail_id' => $data_row['goods_diy_record_detail_id']));
        } else {
            $ret = $this->insert($data);
            return $ret;
        }
        
    }
    
    /**
     * 新增多条库存调整单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail($pid, $ary_details,$tabs_id= '',$add = '') {
        //判断主单据的pid是否存在
        $record = $this->is_exists($pid, 'goods_diy_record_id');
        if (empty($record['data'])) {
            return $this->format_ret(false, array(), '商品组装单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret(false, array(), '商品组装单已确认, 不能修改明细!');
        }
        $type = 'diy';
        if(!empty($tabs_id) && $tabs_id == 'tabs_batch'){
            $type = 'lof';
        }
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail){
                if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                    $this->format_ret(1);
                }
                $data = array();
                $data['pid'] = $pid;
                $data['record_code'] = $record['data']['record_code'];
                $data['price'] = round($ary_detail['purchase_price'], 3);
                $data['lof_no'] = isset($ary_detail['lof_no'])?$ary_detail['lof_no']:'';
                $data['goods_code'] = $ary_detail['goods_code'];
                $data['sku'] = $ary_detail['sku'];
                $data['num'] = $ary_detail['num'];
                $data['money'] = abs($data['num']) * $data['price'];
//                $data['money'] = round($ary_detail['money'], 3);
                $data['type'] = $type;
                $data['diy_sku'] = isset($ary_detail['diy_sku'])?$ary_detail['diy_sku']:'';
                $data['production_date'] = !empty($ary_detail['production_date'])?$ary_detail['production_date']:"";
                $ret = $this->manage_data($data,$type,$add);
                if (1 != $ret['status']) {
                    return $ret;
                }
            }

            //回写数量和金额
            $this->mainWriteBack($pid);
            $this->commit();
            
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 编辑多条库存调整单明细记录
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-10-28
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function edit_detail_action($pid, $ary_details) {
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {

                $ary_detail['pid'] = $pid;

                //更新明细数据
                $ary_detail['money'] = $ary_detail['num'] * $ary_detail['price'];
                $ret = $this->update($ary_detail, array(
                    'pid' => $pid, 'sku' => $ary_detail['sku']
                ));

                if (1 != $ret['status']) {
                    return $ret;
                }
            }
            //回写数量和金额
            $this->mainWriteBack($pid);
            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    //批次删除
    function delete_detail_lof($id) {
        $row = $this->db->get_row("select * from {$this->table} where goods_diy_record_detail_id = :id" ,array(":id" => $id));
        if(empty($row)){
            return $this->format_ret(-1,'','组装商品明细不存在');
        }
        if($row['is_sure'] == 1){
            return $this->format_ret(-1,'','已确认单据不能删除明细');
        }
        $this->begin_trans();
        try {
            $result = $this->delete_exp("stm_goods_diy_record_detail", array("goods_diy_record_detail_id" => $id));
            if($result['status'] < 0){
                return $result;
            }
            $res = $this->mainWriteBack($row['pid']);
            if($res['status'] < 0 ){
                return $res;
            }
            $this->commit();
            return $this->format_ret(1, array(), '删除成功');
        } catch (Exception $ex) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $ex->getMessage());
        }
    }

    /**
     * 主单据数据回写
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-11
     * @param  int $record_id 主单据编号
     * @return boolean
     */
    public function mainWriteBack($record_id) {
        //回写数量和金额
        $sql = "update stm_goods_diy_record set
                  stm_goods_diy_record.num = (select sum(num) from stm_goods_diy_record_detail where pid = :id and type = 'diy'),
                  stm_goods_diy_record.money = (select sum(money) from stm_goods_diy_record_detail where pid = :id and type = 'diy')
                where stm_goods_diy_record.goods_diy_record_id = :id ";
        $res = $this->query($sql, array(':id' => $record_id));
        return $res;
    }

    public function get_combo_sku($record_code,$sku){
        if(empty($record_code)){
            return $this->format_ret(-1,'','组装单不存在！');
        }
        $sql = "select rr.sku from {$this->table} r inner join goods_diy rr on r.sku = rr.p_sku where record_code = :record_code and r.sku = :sku";
        $combo_sku = $this->db->get_all($sql,array(":record_code" => $record_code,':sku' => $sku));
        if(empty($combo_sku)){
           return $this->format_ret(-1,'','组装商品不存在！'); 
        }
        $combo_array = array();
        foreach ($combo_sku as $sku){
            $combo_array[] = $sku['sku'];
        }
        return $this->format_ret(1,$combo_array);
    }
    
    public function get_sku_info($sku_info,&$error_msg,&$err_num,$type){
        foreach ($sku_info as $key => &$info ){
            $sql = "select b.goods_code,b.spec1_code,b.spec2_code,b.sku,g.price,g.purchase_price,g.sell_price from
                            goods_sku b
                            inner join base_goods g ON g.goods_code = b.goods_code
                            where b.barcode =:barcode";
            $ret = $this->db->get_row($sql,array(':barcode'=>$info['barcode']));
            if(empty($ret)){
                $error_msg[] = array($info['barcode'] => '该条码不存在');
                $err_num ++;
            } else {
                $info['goods_code'] = $ret['goods_code'];
                $info['spec1_code'] = $ret['spec1_code'];
                $info['spec2_code'] = $ret['spec2_code'];
                $info['sku'] = $ret['sku'];
                
                $info['price'] = $ret['price'];
                $info['purchase_price'] = $ret['purchase_price'];
                $info['sell_price'] = $ret['sell_price'];
                if($type =='lof'){
                    if(isset($info['diy_barcode']) && $info['diy_barcode'] != ''){
                        $info['diy_sku'] = $this->db->get_value("select sku from goods_sku where barcode = '{$info['diy_barcode']}'");
                    }
                    $sql = "select sku,num from goods_diy d WHERE p_sku = :p_sku and sku=:sku ";
                    $diy_data = $this->db->get_row($sql,array(":sku" => $info['sku'],':p_sku'=>$info['diy_sku']));
                    if(!empty($diy_data)){
                        $info['num'] = 0 - $info['num']*$diy_data['num'];
                        $detail_data_lof[$key] = $info;
                    } else {
                        $error_msg[] = array($info['barcode'] => '该条码不存在组装商品');
                        $err_num ++;
                    }
                } else {
                    $detail_data_lof[$key] = $info;
                }
                
            }
        }
        return $this->format_ret(1,$detail_data_lof);
    }
    
    function imoprt_detail($id, $file) {
        $ret = load_model('stm/StmGoodsDiyRecordModel')->get_row(array('goods_diy_record_id' => $id));
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '商品组装单不存在！');
        }
        $sku_arr = $sku_info = array();
        $sku_lof_info =$sku_lof_arr = array();
        $error_msg = array();
        $msg = array();
        $barcode_msg = array();
        $lof_msg = array();
        $err_num = 0;
        $line_num = $this->read_csv_lof($file, $sku_arr, $sku_lof_info, $sku_lof_arr, $sku_info, $msg);
       //组装类型数量为正数，拆分为负数
        foreach ($sku_info as $k_sku => $v_sku) {
            if ($ret['data']['record_type'] == '0') {
                $sku_info[$k_sku]['num'] = abs($v_sku['num']);
            } else {
                $sku_info[$k_sku]['num'] = 0 - abs($v_sku['num']);
            }
        }
        foreach ($sku_lof_info as $k_sku_lof => $v_sku_lof) {
            if ($ret['data']['record_type'] == '0') {
                $sku_lof_info[$k_sku_lof]['num'] = abs($v_sku_lof['num']);
            } else {
                $sku_lof_info[$k_sku_lof]['num'] = 0 - abs($v_sku_lof['num']);
            }
        }
        $this->check_exist_barcode($sku_arr, $sku_lof_arr, $sku_info, $sku_lof_info,$barcode_msg);
        $this->check_lof_info($sku_info, $sku_lof_info,$lof_msg);
        $all_err_msg = array_merge($msg, $barcode_msg, $lof_msg);
        $this->begin_trans();
        $type = '';
        try {
            $success = count($sku_lof_info);
            if (!empty($sku_info)) {
                $ret = $this->get_sku_info($sku_info,$error_msg,$err_num,'diy');
                if($ret['status']<0){
                    return $ret;
                }
                $new_sku_info = $ret['data'];
                //批次档案维护
                $ret_goods_lof = load_model('prm/GoodsLofModel')->add_detail_action($id, $new_sku_info);
                if($ret_goods_lof['status']<0){
                    $this->rollback();
                    return $ret_goods_lof;
                }
                $ret = $this->add_detail($id, $new_sku_info,'','import');
                if($ret['status']<0){
                    $this->rollback();
                    return $ret;
                }
            }
            
            if (!empty($sku_lof_info)) {
                $type = 'tabs_batch';
                $ret = $this->get_sku_info($sku_lof_info,$error_msg,$err_num,'lof');
                if($ret['status']<0){
                    return $ret;
                }
                //批次档案维护
                $ret_goods_lof = load_model('prm/GoodsLofModel')->add_detail_action($id, $ret['data']);
                if($ret_goods_lof['status']<0){
                    $this->rollback();
                    return $ret_goods_lof;
                }
                $ret = $this->add_detail($id,$ret['data'],'tabs_batch','','import');
                if($ret['status']<0){
                    $this->rollback();
                    return $ret;
                }
            }
  
            //日志
            $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "stm_goods_diy_record", 'pid' => $id);
            load_model('pur/PurStmLogModel')->insert($log);

            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
        $err_num = $err_num;
        $succ_num = $success - $err_num;
        $message = "导入成功:".$succ_num.'条';
        $error_msg = array_merge($error_msg, $all_err_msg);
        if (!empty($error_msg)) {
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
        $filename = md5("stm_goods_diy_record_import" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }


    function read_csv_lof($file, &$sku_arr, &$sku_lof_info,&$sku_lof_arr, &$sku_info, &$msg) {
        $file = fopen($file, "r");
        $i = 0;
        $old_row = array();
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                if(empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || $row[2] == 0){
                    if($row[0] == $old_row[0]){
                        unset($sku_info[$old_row[0]]);
                        $key_lof = $old_row[0].'_'.$old_row[3].'_'.$old_row[4];
                        unset($sku_lof_info[$key_lof]);
                    }
                    if(!empty($row[0])){
                        $msg[$i][$row[0]] = '存在空数据或数据为0';
                    }
                    $i++;
                    continue;
                }
                $old_row = $row;
                if (!empty($row[0])) {
                    if(!array_key_exists($row[0], $sku_info)){
                        $sku_arr[] = $row[0];
                        $sku_info[$row[0]]['barcode'] = preg_replace("/\s/", "", trim($row[0]));
                        $sku_info[$row[0]]['lof_no'] = preg_replace("/\s/", "", trim($row[1]));
                        $sku_info[$row[0]]['num'] = $row[2];
                    }
                }
                if(!empty($row[4])){
                    $sku_lof_arr[] = $row[3];
                    $key_lof = $row[0].'_'.$row[3].'_'.$row[4];
                    $sku_lof_info[$key_lof]['barcode'] = preg_replace("/\s/", "", trim($row[3]));
                    $sku_lof_info[$key_lof]['diy_barcode'] = preg_replace("/\s/", "", trim($row[0]));
                    $sku_lof_info[$key_lof]['lof_no'] = preg_replace("/\s/", "", trim($row[4]));
                    $sku_lof_info[$key_lof]['num'] = $row[2];
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
                $val = iconv('gbk', 'utf-8', $val);
                $val = trim(str_replace('"', '', $val));
                //   $row[$key] = $val;
            }
        }
    }
    
    public function get_diy_goods($data){
        $sql = "select * from {$this->table} r1 where record_code = :record_code and type = 'diy' ";
        $diy_goods = $this->db->get_all($sql,array(':record_code' => $data['record_code']));
        foreach ($diy_goods as &$goods){
            $key_arr = array('goods_name','barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($goods['sku'], $key_arr);
            $goods['goods_name'] = $sku_info['goods_name'];
            $goods['barcode'] = $sku_info['barcode'];
        }
        return $diy_goods;
    }
    
    /**
     * @todo 检查导入商品的批次信息
     */
    private function check_lof_info(&$main_info, &$detail_info, &$msg) {
        $exists_barcode = $this->get_exists_barcode_lof($main_info, $detail_info);
        $detail_diy_barcode = array();
        $all_m_barcode = array();
        $all_d_barcode = array();
        foreach ($detail_info as $key_lof => $d_info) {
            if ($d_info['num'] > 0) {
                $all_d_barcode[] = $d_info['barcode'];
            } else {
                $all_m_barcode[] = $d_info['diy_barcode'];
            }
            //组装
            if (!in_array($d_info['barcode'], $exists_barcode['exist_detail_barcode'], TRUE) && $d_info['num'] > 0) {
                $detail_diy_barcode[] = $d_info['diy_barcode'];
                unset($detail_info[$key_lof]);
                continue;
            }
        }

        foreach ($detail_info as $key_lof => $d_info) {
            //组装
            if (in_array($d_info['diy_barcode'], $detail_diy_barcode, TRUE) && $d_info['num'] > 0) {
                $detail_diy_barcode[] = $d_info['diy_barcode'];
                unset($detail_info[$key_lof]);
                continue;
            }
        }

        foreach ($main_info as $main_barcode => $m_info) {
            if (!in_array($m_info['barcode'], $exists_barcode['exist_main_barcode'], TRUE) && $m_info['num'] < 0) {
                unset($main_info[$main_barcode]);
                foreach ($detail_info as $key_lof => $d_info) {
                    if ($d_info['diy_barcode'] == $m_info['barcode']) {
                        unset($detail_info[$key_lof]);
                    }
                }
                continue;
            }
            if (in_array($m_info['barcode'], $detail_diy_barcode)) {
                unset($main_info[$main_barcode]);
                continue;
            }
        }
        $diff_main_barcode = array_diff(array_unique($all_m_barcode, SORT_REGULAR), $exists_barcode['exist_main_barcode']);
        $diff_detail_barcode = array_diff(array_unique($all_d_barcode, SORT_REGULAR), $exists_barcode['exist_detail_barcode']);
        $new_diff_barcode = array_merge($diff_main_barcode, $diff_detail_barcode);
        foreach ($new_diff_barcode as $k => $barcode) {
           $msg[$k][$barcode] = '系统中不存在该条码的批次信息';
        }
    }

    /**
     * @todo 获取存在批次信息的barcode
     */
    private function get_exists_barcode_lof($main_info, $detail_info) {
        $exist_main_barcode = array();
        $exist_detail_barcode = array();
        foreach ($main_info as $m_info) {
            if ($m_info['num'] > 0) {
                continue;
            }
            $m_exist = load_model('prm/GoodsLofModel')->get_field_by_barcode($m_info['lof_no'], $m_info['barcode'], 's.barcode');
            $exist_main_barcode[] = (!empty($m_exist['barcode'])) ? $m_exist['barcode'] : '';
        }
        foreach ($detail_info as $d_info) {
            if ($d_info['num'] < 0) {
                continue;
            }
            $d_exist = load_model('prm/GoodsLofModel')->get_field_by_barcode($d_info['lof_no'], $d_info['barcode'], 's.barcode');
            $exist_detail_barcode[] = (!empty($d_exist['barcode'])) ? $d_exist['barcode'] : '';
        }
        return array('exist_main_barcode' => $exist_main_barcode, 'exist_detail_barcode' => $exist_detail_barcode);
    }

    /**
     * @todo 检查商品的barcode是否存在于系统中
     */
    private function check_exist_barcode($main_barcode, $detail_barcode, &$main_info, &$detail_info, &$msg) {
        //获取主商品和子商品不存在系统中的barcode
        $diff_main_barcode = $this->find_diff_barcode($main_barcode);
        $diff_detail_barcode = $this->find_diff_barcode($detail_barcode);

        $not_exist_barcode = array_unique(array_merge($diff_main_barcode, $diff_detail_barcode), SORT_REGULAR);
        foreach ($not_exist_barcode as $k => $barcode) {
            $msg[$k][$barcode] = '系统中不存在该条码';
        }
        $detail_diy_barcode = array();
        //销毁明细商品和主商品中不存在barcode的数据
        foreach ($detail_info as $key_lof => $d_info) {
            if (in_array($d_info['diy_barcode'], $diff_main_barcode)) {
                unset($detail_info[$key_lof]);
                continue;
            }
            if (in_array($d_info['barcode'], $diff_detail_barcode)) {
                $detail_diy_barcode[] = $d_info['diy_barcode'];
                unset($detail_info[$key_lof]);
                continue;
            }
        }
        foreach ($main_info as $main_barcode => $m_info) {
            if (in_array($m_info['barcode'], $diff_main_barcode)) {
                unset($main_info[$main_barcode]);
                continue;
            }
            if (in_array($m_info['barcode'], $detail_diy_barcode)) {
                unset($main_info[$main_barcode]);
                continue;
            }
        }
    }

    /**
     * @todo 获取不存在于系统的barcode
     */
    public function find_diff_barcode($barcode) {
        $barcode_str = deal_array_with_quote($barcode);
        $sql = "SELECT barcode FROM goods_sku WHERE barcode IN({$barcode_str})";
        $exist_barcode = $this->db->get_all($sql);
        if(empty($exist_barcode)){
            return FALSE;
        }
        foreach ($exist_barcode as $key => $value) {
            $new_barcode[$key] = $value['barcode'];
        }
        $diff_barcode = array_diff($barcode, $new_barcode);
        return $diff_barcode;
    }
    
    function imoprt_detail_no_lof($id, $file) {
        //查询主单信息
        $sql = "SELECT record_code FROM stm_goods_diy_record WHERE goods_diy_record_id = '{$id}'";
        $record_code = $this->db->get_value($sql);
        
        $ret = load_model('stm/StmGoodsDiyRecordModel')->get_row(array('goods_diy_record_id' => $id));
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '商品组装单不存在！');
        }
        $sku_arr = array();
        $sku_info = array();
        $msg = array();
        $err_num = 0;
        //组合数组
        $line_num = $this->read_csv($file, $sku_arr,$sku_info);
        $success = count($sku_info);
        $params = array();
         $lof_data = load_model("prm/GoodsLofModel")->get_sys_lof();
        //验证数量、组装码，组合信息
        foreach ($sku_info as $key => $val) {
            if(empty($val['num'])) {
                $msg[][$val['diy_barcode']] = '数量不能为空或为零'; 
                ++$err_num;
                unset($sku_info[$key]);
                continue;
            }
            //组装类型为正值，拆分为负值
            if ($ret['data']['record_type'] == '0') {
                $val['num'] = abs($val['num']);
            } else {
                $val['num'] = 0 - abs($val['num']);
            }
            //验证组装码
            $sql = "SELECT sku,price FROM goods_sku WHERE barcode = '{$val['diy_barcode']}'";
            $sku = $this->db->get_row($sql);
            if(empty($sku)) {
                $msg[][$val['diy_barcode']] = '组装码不存在'; 
                ++$err_num;
                unset($sku_info[$key]);
                continue;
            }
            $sql = "SELECT p_goods_code FROM goods_diy WHERE p_sku = '{$sku['sku']}'";
            $p_goods_code = $this->db->get_value($sql);
            if(empty($p_goods_code)) {
                $msg[][$val['diy_barcode']] = '该商品不是组装商品'; 
                ++$err_num;
                unset($sku_info[$key]);
                continue;
            }
            //获取单价、金额
            if(empty($sku['price'])) {
                $sql = "SELECT price FROM base_goods WHERE goods_code = '{$p_goods_code}'";
                $sku['price'] = $this->db->get_value($sql);
            }
            $money = $sku['price'] * abs($val['num']);
            $params[] = array(
                'pid' => $id,
                'goods_code' => $p_goods_code,
                'sku' => $sku['sku'],
                'price' => $sku['price'],
                'money' => $money,
                'num' => $val['num'],
                'record_code' => $record_code,
                'lof_no' =>$lof_data['lof_no'],
                'production_date' => $lof_data['production_date'],
                'type' => 'diy'
            );  
        }
        //批量添加
        $update_str = 'num=VALUES(num),price=VALUES(price),money=VALUES(money)';
        $ret = $this->insert_multi_duplicate('stm_goods_diy_record_detail', $params, $update_str);
        if ($ret['status'] < 0) {
            return $ret;
        }
        //回写数量和金额
        $this->mainWriteBack($id);
        
        //日志
        $log = array('user_id' => CTX()->get_session('user_id'), 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'sure_status' => '未确认', 'finish_status' => '未完成', 'action_name' => '增加明细', 'module' => "stm_goods_diy_record", 'pid' => $id);
        load_model('pur/PurStmLogModel')->insert($log);
        
        $err_num = $err_num;
        $succ_num = $success - $err_num;
        $message = "导入成功:".$succ_num.'条';
        if (!empty($msg)) {
            $fail_top = array('商品条码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
        
    }
    //组合数组
    private function read_csv($file, &$sku_arr,&$sku_info) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $sku_arr[] = $row[0];
                    $sku_info[$row[0]]['diy_barcode'] = preg_replace("/\s/", "", trim($row[0]));
                    $sku_info[$row[0]]['num'] = $row[1];
                }
            }
            ++$i;
        }
        fclose($file);
        return $i;
    }
}
