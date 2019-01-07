<?php

/**
 * 商品调价单相关业务
 */
require_model('tb/TbModel');
require_lang('base');
require_lib('util/oms_util', true);

class GoodsAdjustPriceDetailModel extends TbModel {
    //0 => '吊牌价', 1 => '成本价', 2 => '批发价', 3 => '进货价'
    public $settlement_price_type = array(
        0 => 'sell_price', 1 => 'cost_price', 2 => 'trade_price', 3 => 'purchase_price'
    );

    function get_table() {
        return 'fx_goods_adjust_price_detail';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_join = " INNER JOIN fx_goods_adjust_price_record r2 ON r2.record_code = rl.record_code LEFT JOIN goods_sku AS r3 ON rl.sku = r3.sku ";
        $sql_values = array();
        $sql_main = " FROM {$this->table} rl $sql_join WHERE 1 AND rl.record_code = :record_code";
        $sql_values[':record_code'] = $filter['record_code'];
               
        //商品编码/商品条形码
        if (isset($filter['code_name']) && $filter['code_name'] !== '') {            
            $sql_main .= " AND (rl.goods_code LIKE :code_name OR r3.barcode LIKE :code_name )";
            $sql_values[':code_name'] = '%' . $filter['code_name'] . '%';
        }

        $select = 'rl.*,r2.record_status';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $key_arr = array('spec1_name', 'spec2_name', 'goods_name', 'barcode');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_arr);
            $val = array_merge($val, $sku_info);
            $val['spec_str'] = $val['spec1_name'] . '；' . $val['spec2_name'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    function get_by_page_log($filter) {
        $sql_values = array();
        $sql_main = " FROM fx_goods_adjust_price_log rl WHERE rl.adjust_price_record_id = :adjust_price_record_id";
        $sql_values[':adjust_price_record_id'] = $filter['pid'];
        $select = 'rl.*';
        $sql_main .= " ORDER BY rl.action_time DESC ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$val) {
            $val['add_time'] = date('Y-m-d H:i:s', $val['action_time']);
            $val['record_status_str'] = $val['record_status'] == 0 ? '未启用' : '已启用';
        }
        
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    /**
     * 添加调价单商品明细
     * @param type $id
     * @param type $data
     */
    function do_add_detail($id,$data) {
        $record_data = load_model('fx/GoodsAdjustPriceModel')->get_by_id($id);
        $check = load_model('fx/GoodsAdjustPriceModel')->check_record($record_data);
        if($check['status'] < 0) {
            return $check;
        }
        $params = array();
        $barcode_arr = array();
        foreach($data as $val) {
            //计算明细的结算价格
            $price = $this->get_settlement_money($record_data['settlement_price_type'],$val);
            $money = sprintf("%01.3f",$price * $record_data['settlement_rebate']);
            $params[] = array(
                'record_code' => $record_data['record_code'],
                'pid' => $id,
                'sku' => $val['sku'],
                'goods_code' => $val['goods_code'],
                'spec1_code' => $val['spec1_code'],
                'spec2_code' => $val['spec2_code'],
                'settlement_price' => $price,
                'settlement_money' => $money,
                'settlement_rebate' => $record_data['settlement_rebate']
            );
            $barcode_arr[] = $val['barcode'];
        }
        $update_str = "settlement_money=VALUES(settlement_money),settlement_rebate=VALUES(settlement_rebate),settlement_price=VALUES(settlement_price)";
        $this->begin_trans();
        $ret = $this->insert_multi_duplicate($this->table, $params, $update_str);
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        //新增日志
        $barcode_str = implode(",", $barcode_arr);
        $ret = $this->insert_adjust_goods_price_log($id, $record_data['record_status'], '新增商品', '新增条形码:'.$barcode_str);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','添加日志失败');
        }
        
        $this->commit();
        return $ret;
    }
    
    function get_settlement_money ($settlement_price_type, $goods_data) {
        $settlement_price_type = $this->settlement_price_type[$settlement_price_type];
        $price = 0;
        if($settlement_price_type == 'sell_price' ) { //先去sku级再取商品级
            if(!empty($goods_data['price']) && $goods_data['price'] != 0) {
                $price = $goods_data['price'];
            } else {
                $price = $goods_data['sell_price'];
            }
        } else if($settlement_price_type == 'cost_price') { //先去sku级再取商品级
            if(!empty($goods_data['cost_price']) && $goods_data['cost_price'] != 0) {
                $price = $goods_data['cost_price'];
            } else {
                $ret = load_model('prm/GoodsModel')->get_by_goods_code($goods_data['goods_code']);
                $price = $ret['data']['cost_price'];
            }
        } else if($settlement_price_type == 'purchase_price') {
            $price = $goods_data['purchase_price'];
        } else if($settlement_price_type == 'trade_price') {
            $price = $goods_data['trade_price'];
        }
        return $price;
    }
    
    /**
     * 新增操作日志
     * @param type $id
     * @param type $action_name
     * @param type $action_remark
     */
    function insert_adjust_goods_price_log($id, $record_status, $action_name, $action_remark) {
        //查询单据状态
        $params = array(
            'adjust_price_record_id' => $id,
            'user_id' => CTX()->get_session('user_id'),
            'user_code' => CTX()->get_session('user_code'),
            'action_name' => $action_name,
            'action_time' => time(),
            'action_remark' => $action_remark,
            'record_status' => $record_status
        );
        $ret = $this->insert_exp('fx_goods_adjust_price_log', $params);
        return $ret;
    } 
    /**
     * 删除明细
     * @param type $id
     */
    function do_delete_detail($id, $detail_id, $barcode) {
        $record_data = load_model('fx/GoodsAdjustPriceModel')->get_by_id($id);
        $check = load_model('fx/GoodsAdjustPriceModel')->check_record($record_data);
        if($check['status'] < 0) {
            return $check;
        }
        $this->begin_trans();
        //删除
        $ret = $this->delete(array('adjust_price_detail_id' => $detail_id));
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        //添加日志
        $ret = $this->insert_adjust_goods_price_log($id, $record_data['record_status'], '删除商品', '删除条形码:' . $barcode);
        if($ret['status'] < 0) {
            $this->rollback();
            return $ret;
        }
        $this->commit();
        return $ret;
    }
    
    
    function edit_rebate_or_money($data,$settlement_type) {
        if($settlement_type == 'rebate') {
            $ret = $this->all_edit_rebate($data);
        } else if($settlement_type == 'money') {
            $ret = $this->all_edit_money($data);
        } else {
            return $this->format_ret(-1, '', '修改折扣失败');
        }
        return $ret;
    }    
    function all_edit_money($data) {
        $adjust_model = load_model('fx/GoodsAdjustPriceModel');
        $record_data = $adjust_model->get_by_id($data['pid']);
        $check = $adjust_model->check_record($record_data);
        if($check['status'] < 0) {
            return $check;
        }
        $detail_arr = $this->get_by_detail($data['pid'], 'pid', 'sku,settlement_price');
        foreach ($detail_arr as $val) {
            if((float)($data['money'] / $val['settlement_price']) > 1) {
//                $arr = array('barcode');
//                $sku_arr = load_model('goods/SkuCModel')->get_sku_info($val['sku'],$arr);
                return $this->format_ret(-1,'','折扣必须是大于等于0并且小于等于1');
            }
        }
        
        $this->begin_trans();
        $sql = "UPDATE {$this->table} SET settlement_money = :settlement_money, settlement_rebate = (settlement_money / settlement_price) WHERE pid = :pid";
        $ret = $this->query($sql,array(':settlement_money' => $data['money'],':pid' => $data['pid']));
        if($ret == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改结算金额失败');
        }
        //添加日志
        $action_name = '一键修改结算金额'; 
        $action_remark = '一键修改为' . $data['money'];
        $ret = $this->insert_adjust_goods_price_log($data['pid'], $record_data['record_status'], $action_name, $action_remark);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $ret;
    }
    
    /**
     * 一键修改折扣
     * @param type $data
     * @return type
     */
    function all_edit_rebate($data) {
        $record_data = load_model('fx/GoodsAdjustPriceModel')->get_by_id($data['pid']);
        $check = load_model('fx/GoodsAdjustPriceModel')->check_record($record_data);
        if($check['status'] < 0) {
            return $check;
        }
        if($data['rebate'] > 1 || $data['rebate'] < 0) {
            return $this->format_ret(-1, '', '折扣必须是大于等于0并且小于等于1');
        }
        
        $where = 'pid';
        $this->begin_trans();
        $sql = "UPDATE {$this->table} SET settlement_rebate = :settlement_rebate, settlement_money = (settlement_price * settlement_rebate) WHERE {$where} = :{$where}";
        $ret = $this->query($sql,array(':settlement_rebate' => $data['rebate'],':'.$where => $data[$where]));
        if($ret == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改折扣失败');
        }
        //一键修改，回写主表的折扣
        $update_data = array('settlement_rebate' => $data['rebate']);
        $where = array('adjust_price_record_id' => $data['pid']);
        $ret = load_model("fx/GoodsAdjustPriceModel")->update_data($update_data, $where);
        if ($ret == false) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改主单据折扣失败');
        }
        //添加日志
        $action_name = '一键修改折扣'; 
        $action_remark = '一键修改为' . $data['rebate'];
        $ret = $this->insert_adjust_goods_price_log($data['pid'], $record_data['record_status'], $action_name, $action_remark);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $ret;
    }
    
    
    function get_by_detail($filter, $where, $select = '*') {
        $sql = "SELECT {$select} FROM {$this->table} WHERE {$where} = :{$where} ";
        $ret = $this->db->get_all($sql,array(':'.$where => $filter));
        return $ret;
    }
    /**
     * 单个修改结算折扣和金额
     * @param type $params
     */
    function do_edit_detail($params) {
        $record_data = load_model('fx/GoodsAdjustPriceModel')->get_by_id($params['pid']);
        $check = load_model('fx/GoodsAdjustPriceModel')->check_record($record_data);
        if($check['status'] < 0) {
            return $check;
        }
        $detail = $this->get_by_detail($params['adjust_price_detail_id'], 'adjust_price_detail_id');
        if(empty($detail)) {
            return $this->format_ret(-1,'','明细信息为空');
        }
        $detail_data = $detail[0];
        
        $arr = array('barcode');
        $sku_arr = load_model('goods/SkuCModel')->get_sku_info($detail_data['sku'],$arr);
        
        if($detail_data['settlement_money'] == $params['money'] && $detail_data['settlement_rebate'] == $params['rebate']) {
            return $this->format_ret(-1, '', '没有修改信息');
        }
        $data = array();
        if($detail_data['settlement_money'] != $params['money']) { //结算金额变动,计算结算折扣
            $action_name = '单个修改结算金额';
            $action_remark = '条码' . $sku_arr['barcode'] .'：金额从' .$detail_data['settlement_money'] . '修改为' . $params['money'];
            $data['settlement_money'] = $params['money'];
            if((float)($params['money'] / $detail_data['settlement_price']) > 1) {
                return $this->format_ret(-1, '', '折扣必须是大于等于0并且小于等于1');
            }
            $data['settlement_rebate'] = sprintf('%01.2f',$params['money'] / $detail_data['settlement_price']);
        } else if($detail_data['settlement_rebate'] != $params['rebate']){//结算折扣变动,计算结算金额
            $action_name = '单个修改结算折扣';
            $action_remark = '条码' . $sku_arr['barcode'] .'：折扣从' .$detail_data['settlement_rebate'] . '修改为' . $params['rebate'];
            $data['settlement_rebate'] = $params['rebate'];
            $data['settlement_money'] = sprintf('%01.3f',$detail_data['settlement_price'] * $params['rebate']);
        } else {
            return $this->format_ret(-1, '', '没有修改信息');
        }
        if($data['settlement_rebate'] > 1 || $data['settlement_rebate'] < 0) {
            return $this->format_ret(-1, '', '折扣必须是大于等于0并且小于等于1');
        }
        $this->begin_trans();
        $ret = $this->update($data, array('adjust_price_detail_id' => $params['adjust_price_detail_id']));
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '修改失败');
        }
        //添加日志
        $ret = $this->insert_adjust_goods_price_log($params['pid'], $record_data['record_status'], $action_name, $action_remark);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加日志失败');
        }
        $this->commit();
        return $this->format_ret(1, '', '修改成功');
    }
    /**
     * 导入商品
     * @param type $id
     * @param type $file
     */
    function imoprt_detail($id,$file) {
        $barcode_data = array();
        $this->read_csv_sku($file,$barcode_data);
        $barcode_data_suc = array();
        $barcode_data_err = array();
        if(count($barcode_data) > 5000){
            return $this->format_ret(-1,count($barcode_data),'导入数据超过五千条');
        }
        array_walk($barcode_data,function($param)use(&$barcode_data_suc,&$barcode_data_err){
            if($param['barcode'] === '' && $param['rebate'] === NULL && $param['money'] === ''){
                return true;
            }elseif($param['barcode'] === '' || $param['barcode'] === NULL){
                $param['error_message'] = '商品条形码必须填写';
                $barcode_data_err[] = $param;
            }elseif(($param['money'] === '' || $param['money'] === Null) && ($param['rebate'] === '' || $param['rebate'] === Null)){
                $param['error_message'] = '折扣和结算金额不能同时为空';
                $barcode_data_err[] = $param;
            }elseif(($param['rebate'] !== '' && $param['rebate'] !== Null) && ($param['rebate'] < 0 || $param['rebate'] > 1)){
                $param['error_message'] = '折扣不能小于0或者大于1';
                $barcode_data_err[] = $param;
            }elseif(($param['money'] !== '' && $param['money'] !== Null) && $param['money'] < 0){
                $param['error_message'] = '结算金额不能小于0';
                $barcode_data_err[] = $param;
            }else{
                $barcode_data_suc[$param['barcode']] = $param;
            }
        });
        $barcode_arr = array_column($barcode_data_suc,'barcode');
        //查询开启分销款的商品信息
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
        $sql = "SELECT g.is_custom_money,b.goods_code,b.spec1_code,b.spec2_code,b.sku,b.barcode,g.sell_price,g.trade_price,IF(b.cost_price<>0,b.cost_price,g.cost_price) AS cost_price,g.purchase_price,b.price FROM goods_sku b INNER JOIN  base_goods g ON g.goods_code = b.goods_code WHERE b.barcode IN({$barcode_str}) GROUP BY b.barcode ";
        $detail_data = $this->db->get_all($sql,$sql_values);
        foreach ($detail_data as $k=>$val){
            if($val['is_custom_money'] != 1 && isset($barcode_data_suc[$val['barcode']])){
                $barcode_data_suc[$val['barcode']]['error_message'] = '不是分销商品';
                $barcode_data_err[] = $barcode_data_suc[$val['barcode']];
                unset($barcode_data_suc[$val['barcode']]);
                unset($detail_data[$k]);
            }
        }
        $barcode_data_suc1 = $barcode_data_suc;
        $ret = $this->add_adjust_detail($id,$barcode_data_suc1,$detail_data,1);
        $inexi_barcode = array_diff($barcode_arr,array_column($detail_data,'barcode'));
        foreach ($inexi_barcode as $v){
            if(isset($barcode_data_suc[$v])){
                $barcode_data_suc[$v]['error_message'] = '商品条形码不存在';
                $barcode_data_err[] = $barcode_data_suc[$v];
                unset($barcode_data_suc[$v]);
            }
        }
        foreach($ret['barcode'] as $v){
            if(isset($barcode_data_suc[$v])){
                $barcode_data_suc[$v]['error_message']='结算金额必须大于0且小于结算价';
                $barcode_data_err[] = $barcode_data_suc[$v];
                unset($barcode_data_suc[$v]);
            }
        }
        if($ret['status'] != '-1'){
            $sucess_num = count($barcode_data_suc);
            $sucess_num = $sucess_num ? $sucess_num : 0;
            $ret['data'] = '';
            $message = '导入成功' . $sucess_num.'条记录';
            if (!empty($barcode_data_err)) {
                $message .=',' . '失败' . count($barcode_data_err).'条';
                $file_name = $this->create_import_fail_files($barcode_data_err,1);
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $message .= "。错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            }
            $ret['message'] = $message;
        }
        return $ret;
    }
    
    function read_csv_sku($file, &$barcode_data) {
        //    $key_arr = array('0'=>'sku','1'=>'num');
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                $barcode = trim($row[0]);
                $barcode_data[] = array('money' => trim($row[2]), 'rebate' => $row[1],'barcode'=>$barcode);
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 根据商品编码导入调价单
     * @param $id
     * @param $files
     */
    public function imoprt_detail_goods($id,$files){
        $barcode_data = $goods_code_data = array();
        $this->read_csv_good($files, $goods_code_data);
        if(count($goods_code_data) > 5000){
            return $this->format_ret(-1,count($barcode_data),'导入数据超过五千条');
        }
        $goods_code_data_suc = array();
        $goods_code_data_err = array();
        array_walk($goods_code_data,function($param)use(&$goods_code_data_suc,&$goods_code_data_err){
            $param['goods_code'] = trim($param['goods_code']);
            if($param['goods_code'] === '' && $param['rebate'] === NULL && $param['money'] === ''){
                return true;
            }if($param['goods_code'] === '' || $param['goods_code'] === NULL){
                $param['error_message'] = '商品编码必须填写';
                $goods_code_data_err[] = $param;
            }elseif(($param['money'] === '' || $param['money'] === Null) && ($param['rebate'] === '' || $param['rebate'] === Null)){
                $param['error_message'] = '折扣和结算金额不能同时为空';
                $goods_code_data_err[] = $param;
            }elseif(($param['rebate'] !== '' && $param['rebate'] !== Null) && ($param['rebate'] < 0 || $param['rebate'] > 1)){
                $param['error_message'] = '折扣必须大于等于0并且小于等于1';
                $goods_code_data_err[] = $param;
            }elseif($param['money'] < 0){
                $param['error_message'] = '结算金额不能小于0';
                $goods_code_data_err[] = $param;
            }else{
                $goods_code_data_suc[$param['goods_code']] = $param;
            }
        });
        unset($goods_code_data);
        $goods_code_arr = array_unique(array_column($goods_code_data_suc,'goods_code'));
        //查询开启分销款的商品信息
        $sql_values = array();
        $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
        $sql = "SELECT 
                      g.goods_code,
                      b.spec1_code,
                      b.spec2_code,
                      b.sku,
                      b.barcode,
                      g.sell_price,
                      g.trade_price,
                      IF(b.cost_price<>0,b.cost_price,g.cost_price) AS cost_price,
                      g.purchase_price,
                      g.is_custom_money,
                      b.price 
                FROM base_goods g
                LEFT JOIN goods_sku b ON g.goods_code = b.goods_code 
                WHERE g.goods_code IN({$goods_code_str})";
        $detail_data = $this->db->get_all($sql,$sql_values);
        $empty_barcode = array();
        foreach ($detail_data as $key=>$val){
            if($val['is_custom_money'] == 1){
                $barcode_data[$val['sku']]['money'] = $goods_code_data_suc[$val['goods_code']]['money'];
                $barcode_data[$val['sku']]['rebate'] = $goods_code_data_suc[$val['goods_code']]['rebate'];
                if($val['barcode'] !== '' && $val['barcode'] !== NULL){
                    $goods_code_data_suc[$val['goods_code']]['is_ext'] = 1;
                }else{
                    $goods_code_data_suc[$val['goods_code']]['is_ext'] = 3;
                    $empty_barcode[] = $val['goods_code'];
                    unset($detail_data[$key]);
                }
            }else{
                if(!isset($goods_code_data_suc[$val['goods_code']]['is_ext']) || $goods_code_data_suc[$val['goods_code']]['is_ext'] = 2){
                    $goods_code_data_suc[$val['goods_code']]['is_ext'] = 2;
                    unset($detail_data[$key]);
                }
            }
        }
        if(!empty($empty_barcode)){
            foreach ($detail_data as $k=>$v){
                if(in_array($v['goods_code'],$empty_barcode)){
                    $goods_code_data_suc[$v['goods_code']]['is_ext'] = 3;
                    unset($detail_data[$k]);
                }
            }
        }

        foreach ($goods_code_data_suc as $k=>$v){
            if(!isset($v['is_ext'])){
                $v['error_message'] = '编码不存在';
                $goods_code_data_err[] = $v;
                unset($goods_code_data_suc[$k]);
            }elseif($v['is_ext'] == 2){
                $v['error_message'] = '不是分销商品';
                $goods_code_data_err[] = $v;
                unset($goods_code_data_suc[$k]);
            }elseif($v['is_ext'] == 3){
                $v['error_message'] = '此编码下部分条形码为空';
                $goods_code_data_err[] = $v;
                unset($goods_code_data_suc[$k]);
            }
        }
        $ret = $this->add_adjust_detail($id,$barcode_data,$detail_data,2);
        if(!empty($ret['goods_code'])){
            foreach ($ret['goods_code'] as $k=>$value){
                if(isset($goods_code_data_suc[$value])){
                    $goods_code_data_suc[$value]['error_message'] = '结算金额必须大于0且小于结算价';
                    $goods_code_data_err[] = $goods_code_data_suc[$value];
                    unset($goods_code_data_suc[$value]);
                }
            }
        }
        $sucess_num = count($goods_code_data_suc);
        $sucess_num = $sucess_num ? $sucess_num : 0;
        $ret['data'] = '';
        $message = '导入成功' . $sucess_num.'条记录';
        if (!empty($goods_code_data_err)) {
            $message .=',' . '失败' . count($goods_code_data_err).'条';
            $file_name = $this->create_import_fail_files($goods_code_data_err,2);
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "。错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;

        return $ret;
    }

    /**
     * 生成错误信息
     * @param $fail_data
     * @param $msg
     * @return string
     */
    function create_import_fail_files($fail_data,$type) {
        $filename = $type == 2 ? md5("import_price" . time()) : md5("import_detail_price" . time());
        $column = $type == 2 ? 'goods_code' : 'barcode';
        $field = $type == 2 ? '商品编码' : '商品条形码';
        $file_str = $field.",折扣,结算金额,错误信息\r\n";
        foreach ($fail_data as $barcode => $val) {
            $file_str .= "\t".$val[$column]."\t,\t".$val['rebate']."\t,\t".$val['money']."\t,".$val['error_message']."\r\n";
        }
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        //var_dump($file_str);die;
        return $filename;
    }

    /**
     * 按商品读取数据
     * @param $file
     * @param $barcode_arr
     * @param $barcode_data
     */
    public function read_csv_good($file, &$goods_code_data){
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 2) {
                $this->tran_csv($row);
                $goods_code_data[] = array('money' => trim($row[2]), 'rebate' => $row[1],'goods_code'=>$row[0]);
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 添加调整单明细
     */
    public function add_adjust_detail($id,&$barcode_data,&$detail_data,$type){
        if(empty($detail_data)) return $this->format_ret(1);
        $error_msg = array();
        $err_num = 0;
        $key_index = $type == 1? 'barcode':'sku';
        $record_data = load_model('fx/GoodsAdjustPriceModel')->get_by_id($id);
        $params = array();
        $bar_goods_arr = array('goods_code'=>array(),'barcode'=>array());
        foreach ($detail_data as $val) {
            $price = $this->get_settlement_money($record_data['settlement_price_type'],$val);
            $k = $val[$key_index] ;
            if(isset($barcode_data[$k])) {
                if($barcode_data[$k]['money'] !== '' && $barcode_data[$k]['money'] !== NULL) { //优先添加填写金额的商品
                    $settlement_money = $barcode_data[$k]['money'];
                    $settlement_rebate = (float)$settlement_money / (float)$price;
                    if($settlement_rebate > 1){
                        $bar_goods_arr['goods_code'][] = $val['goods_code'];
                        $bar_goods_arr['barcode'][] = $val['barcode'];
                        unset($barcode_data[$k]);
                        continue;
                    }
                } else if($barcode_data[$k]['rebate'] !== '' && $barcode_data[$k]['rebate'] !== NULL){ //添加填写折扣的商品
                    $settlement_rebate = $barcode_data[$k]['rebate'];
                    $settlement_money = (float)$price * (float)$settlement_rebate;
                } else {
                    $error_msg[] = array($k => '折扣和金额为空');
                    $err_num++;
                    unset($barcode_data[$k]);
                    continue;
                }
            }
            //输入结算金额的优先添加
            $params[] = array(
                'record_code' => $record_data['record_code'],
                'pid' => $id,
                'sku' => $val['sku'],
                'goods_code' => $val['goods_code'],
                'spec1_code' => $val['spec1_code'],
                'spec2_code' => $val['spec2_code'],
                'settlement_price' => $price,
                'settlement_money' => $settlement_money,
                'settlement_rebate' => $settlement_rebate
            );
            unset($barcode_data[$k]);
        }
        if (!empty($barcode_data)) {
            $sku_error = array_keys($barcode_data);
            foreach ($sku_error as $err) {
                $error_msg[] = array($err => '系统不存在该条码信息或条码不是分销款商品');
                $err_num++;
            }
        }
        //如果编码导入时，条形码折扣不符合条件则不允许导入该编码
        if($type == 2 && !empty($bar_goods_arr['goods_code'])){
            foreach ($params as $pk=>$pv){
                if(in_array($pv['goods_code'],$bar_goods_arr['goods_code'])){
                    unset($params[$pk]);
                }
            }
        }
        $update_str = "settlement_money=VALUES(settlement_money),settlement_rebate=VALUES(settlement_rebate),settlement_price=VALUES(settlement_price)";
        $this->begin_trans();
        if(!empty($params)){
            $ret = $this->insert_multi_duplicate($this->table, $params, $update_str);
            if($ret['status'] < 0) {
                $this->rollback();
                return $ret;
            }
            //新增日志
            $ret = $this->insert_adjust_goods_price_log($id, $record_data['record_status'], '导入商品', '');
            if($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1,'','添加日志失败');
            }
            $this->commit();
        }
        return $ret['data'] = $bar_goods_arr;
    }

}
