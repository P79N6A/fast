<?php

/**
 * 库存调整详情管理相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('stm');

class GoodsInvLofRecordModel extends TbModel {

    private $default_lof = array();
            
    function __construct() {
        parent::__construct();
            $lof_data = load_model("prm/GoodsLofModel")->get_sys_lof();
            $this->default_lof['lof_no'] = $lof_data['data']['lof_no'];
           $this->default_lof['production_date'] = $lof_data['data']['production_date'];
    }
            
    function get_table() {
        return 'b2b_lof_datail';
    }

    /**
     * 新增多条单据批次表
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function add_detail_action($pid, $store_code, $type, $ary_details) {
        $record = array();
        $func = $type; //重写方法验证单据是否存在

        $ret1 = $this->$func($pid);
        if (isset($ret1['status']) && $ret1['status'] == '-1') {
            return $ret1;
        } else {
            $record = $ret1['data'];
        }
        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status'));
        $lof_status = $lof_manage['lof_status'];
        //$adjust_inv = array("pur_return", "shift","wbm_store_out");
        $adjust_inv = array("pur_return_notice", "shift", 'goods_diy');
        if (in_array($type, $adjust_inv)) {
            if ($lof_status == 0) {
                require_model('prm/InvOpModel');
                //调配库存
                $invobj = new InvOpModel($record['data']['record_code'], $type, $store_code, 0, $ary_details);
                $ret = $invobj->adjust_lock_record($ary_details, 0);
                if($type == 'pur_return_notice' && $ret['status'] != 1) {
                    return $ret;
                }
                $ary_details = $invobj->check_data['record_info'];
            }
        }
        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
        $lof_data = &$ret_lof['data'];
        $detail_data = array();
        $this->begin_trans();
        try {
            foreach ($ary_details as &$ary_detail) {
                if (isset($ary_detail['num_flag']) && $ary_detail['num_flag'] == '1') {
                    //兼容入库为0
                } else {
                    if ($type != 'purchase' && $type != 'wbm_store_out' && $type != 'take_stock' && $type != 'adjust' && $type != 'pur_return' && $type != 'wbm_return'&& $type != 'stm_stock_lock') {
                        if (!isset($ary_detail['num']) || $ary_detail['num'] == 0) {
                            continue;
                        }
                    }
                }
                
                $ary_detail['pid'] = $pid;
                $ary_detail['order_code'] = $record['data']['record_code'];
                $ary_detail['order_type'] = $type;
                $ary_detail['occupy_type'] = '0';
                $ary_detail['store_code'] = $store_code;
                $ary_detail['order_date'] = $record['data']['record_time'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                //$ary_detail['spec1_id'] = $ary_detail['spec1_id'];
                $ary_detail['spec1_code'] = $ary_detail['spec1_code'];
                //$ary_detail['spec2_id'] = $ary_detail['spec2_id'];
                $ary_detail['spec2_code'] = $ary_detail['spec2_code'];
                $ary_detail['init_num'] = isset($ary_detail['init_num']) ? $ary_detail['init_num'] : $ary_detail['num'];

                if (!isset($ary_detail['lof_no']) || $ary_detail['lof_no'] == '') {
                    $ary_detail['lof_no'] = $lof_data['lof_no'];
                    $ary_detail['production_date'] = $lof_data['production_date'];
                    //$ary_detail['production_date'] = isset($ary_detail['production_date']) ? $ary_detail['production_date'] : $moren['data']['production_date'];
                }
                $ary_detail['create_time'] = time();
                $detail_data[] = $ary_detail;
            }


            
            $data_all = array();
            if (count($detail_data) > 300) {
                $data_all = array_chunk($detail_data, 300);
            } else {
                $data_all[] = $detail_data;
            }

            foreach ($data_all as $detai_arr) {
                $update_str = " num = VALUES(num) , init_num = VALUES(init_num) ";
                $this->insert_multi_duplicate($this->table, $detai_arr, $update_str);
            }



            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    /**
     * 新增多条单据批次表
     * @param array $ary_detail 单据明细数组
     * @return array 返回新增结果
     */
    public function update_detail_action($pid, $store_code, $type, $ary_details) {
        $record = array();
        $func = $type; //重写方法验证单据是否存在

        $ret1 = $this->$func($pid);
        if (isset($ret1['status']) && $ret1['status'] == '-1') {
            return $ret1;
        } else {
            $record = $ret1['data'];
        }


        //print_r($ary_details);exit;
        $this->begin_trans();
        try {
            foreach ($ary_details as $ary_detail) {


                $ary_detail['pid'] = $pid;
                $ary_detail['order_code'] = $record['data']['record_code'];
                $ary_detail['order_type'] = $type;
                $ary_detail['occupy_type'] = '0';
                $ary_detail['store_code'] = $store_code;
                $ary_detail['order_date'] = $record['data']['record_time'];
                //todo 此处参考价格取goods_price表中的sell_price字段, 如需开启到sku级价格需要进行判断
                //$ary_detail['spec1_id'] = $ary_detail['spec1_id'];
                $ary_detail['spec1_code'] = $ary_detail['spec1_code'];
                //$ary_detail['spec2_id'] = $ary_detail['spec2_id'];
                $ary_detail['spec2_code'] = $ary_detail['spec2_code'];
                $ary_detail['init_num'] = isset($ary_detail['init_num']) ? $ary_detail['init_num'] : $ary_detail['num'];

                if (!isset($ary_detail['lof_no']) || $ary_detail['lof_no'] == '') {
                    $moren = load_model('prm/GoodsLofModel')->is_exists('1', 'type');
                    $ary_detail['lof_no'] = $moren['data']['lof_no'];
                    if (isset($ary_detail['production_date']) && $ary_detail['production_date'] <> '') {
                        $ary_detail['production_date'] = $ary_detail['production_date'];
                    } else {
                        $ary_detail['production_date'] = $moren['data']['production_date'];
                    }
                    //$ary_detail['production_date'] = isset($ary_detail['production_date']) ? $ary_detail['production_date'] : $moren['data']['production_date'];
                }
                $ary_detail['create_time'] = time();

                $update_str = " num = num +VALUES(num) ";

                $ret = $this->insert_multi_duplicate($this->table, array($ary_detail), $update_str);
                if (1 != $ret['status']) {
                    $this->rollback();
                    return $ret;
                }
            }

            $this->commit();
            return $this->format_ret(1);
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, array(), '数据库执行出错:' . $e->getMessage());
        }
    }

    //采购退货通知
    function pur_return_notice($pid) {
        //判断主单据的pid是否存在
        $record = load_model('pur/ReturnNoticeRecordModel')->is_exists($pid, 'return_notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '采购退货通知单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '采购退货通知单已确认, 不能修改明细!');
        }

        return $this->format_ret('1', $record);
    }

    //商品组装单
    function goods_diy($pid) {
        //判断主单据的pid是否存在
        $record = load_model('stm/StmGoodsDiyRecordModel')->is_exists($pid, 'goods_diy_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '组装单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '商品组装单已确认, 不能修改明细!');
        }

        return $this->format_ret('1', $record);
    }

    //批发通知单
    function wbm_notice($pid) {
        //判断主单据的pid是否存在
        $record = load_model('wbm/NoticeRecordModel')->is_exists($pid, 'notice_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '批发通知单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '批发通知单已确认, 不能修改明细!');
        }

        return $this->format_ret('1', $record);
    }

    //批发退货
    function wbm_return($pid) {
        //判断主单据的pid是否存在
        $record = load_model('wbm/ReturnRecordModel')->is_exists($pid, 'return_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '批发退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '批发退货单已确认, 不能修改明细!');
        }

        return $this->format_ret('1', $record);
    }

    //批发销货验证
    function wbm_store_out($pid) {
        //判断主单据的pid是否存在
        $record = load_model('wbm/StoreOutRecordModel')->is_exists($pid, 'store_out_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '批发销货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        //if ($record['data']['is_sure'] == 1) {
        //	return $this->format_ret('-1', array(), '批发销货单已确认, 不能修改明细!');
        //}

        return $this->format_ret('1', $record);
    }

    //移仓单据验证
    function shift_out($pid) {
        //判断主单据的pid是否存在
        $record = load_model('stm/StoreShiftRecordModel')->is_exists($pid, 'shift_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '移仓单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '移仓单已确认, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }

    function shift_in($pid) {
        $record = load_model('stm/StoreShiftRecordModel')->is_exists($pid, 'shift_record_id');
        return $this->format_ret('1', $record);
    }
    //删除数据
    function delete_record_data($arr_data){
       $ret=$this->delete($arr_data);
       return $ret;

    }

    //盘点验证
    function take_stock($pid) {
        //判断主单据的pid是否存在
        require_model('stm/TakeStockRecordModel');
        $mdl_take_stock = new TakeStockRecordModel();
        $record = $mdl_take_stock->is_exists($pid, 'take_stock_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '盘点单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_sure'] == 1) {
            return $this->format_ret('-1', array(), '盘点单已验收, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }

    //采购入库验证
    function purchase($pid) {
        //判断主单据的pid是否存在

        $record = load_model('pur/PurchaseRecordModel')->is_exists($pid, 'purchaser_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '采购入库单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_check_and_accept'] == 1) {
            return $this->format_ret('-1', array(), '采购入库单已验收, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }
    
    //分销采购订单验证
    function fx_purchase($pid) {
        //判断主单据的pid是否存在
        $record = load_model('fx/PurchaseRecordModel')->is_exists($pid, 'purchaser_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '分销采购单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_deliver'] == 1) {
            return $this->format_ret('-1', array(), '分销采购单已出库, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }
    //分销采购订单验证
    function fx_return($pid) {
        //判断主单据的pid是否存在
        $record = load_model('fx/PurchaseReturnRecordModel')->is_exists($pid, 'fx_purchaser_return_id');
        $record['data']['record_code'] = $record['data']['return_record_code'];
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '分销采购退货单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_store_in'] == 1) {
            return $this->format_ret('-1', array(), '分销采购退货单已入库, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }

    //调整单单据验证
    function adjust($pid) {
        //判断主单据的pid是否存在
        $record = load_model('stm/StockAdjustRecordModel')->is_exists($pid, 'stock_adjust_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '调整单明细所关联的主单据不存在!');
        }
        //判断主单据状态
        if ($record['data']['is_check_and_accept'] == 1) {
            return $this->format_ret('-1', array(), '调整单已验收, 不能修改明细!');
        }
        return $this->format_ret('1', $record);
    }

    //采购退货单据验证
    function pur_return($pid) {
        //判断主单据的pid是否存在
        $record = load_model('pur/ReturnRecordModel')->is_exists($pid, 'return_record_id');
        /*
          if (empty($record['data'])) {
          return $this->format_ret('-1', array(), '采购退货单明细所关联的主单据不存在!');
          }
          //判断主单据状态
          if ($record['data']['is_store_out'] == 1) {
          return $this->format_ret('-1', array(), '采购退货单已出库, 不能修改明细!');
          } */
        return $this->format_ret('1', $record);
    }

    /**库存锁定单
     * @param $pid
     * @return array
     */
    function stm_stock_lock($pid) {
        //判断主单据的pid是否存在
        $record = load_model('stm/StockLockRecordModel')->is_exists($pid, 'stock_lock_record_id');
        if (empty($record['data'])) {
            return $this->format_ret('-1', array(), '锁定单明细所关联的主单据不存在!');
        }
        return $this->format_ret('1', $record);
    }

    function get_by_pid($pid, $order_type) {
        $data = $this->get_all(array('pid' => $pid, 'order_type' => $order_type), 'id,pid,p_detail_id,order_code,order_type,goods_id,goods_code,spec1_id,spec1_code,spec2_id,spec2_code,sku,store_id,store_code,lof_no,production_date,num,occupy_type,init_num,fill_num');
        return $data;
    }
    
    /**
     * 根据单据号和类型获取批次数据
     * @param string $order_code
     * @param string $order_type
     * @return array 数据集
     */
    function get_by_order_code($order_code, $order_type) {
        $sql = "SELECT id,pid,p_detail_id,order_code,order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num,occupy_type,order_date,init_num,fill_num FROM {$this->table} WHERE order_code=:order_code AND order_type=:order_type";
        $sql_values = array(':order_code' => $order_code, ':order_type' => $order_type);
        $data = $this->db->get_all($sql, $sql_values);
        return $data;
    }

    function get_by_pid_num($pid, $order_type) {
        $sql = "select * from {$this->table} where pid=:pid AND order_type=:order_type AND num>0";
        $sql_values = array(':pid' => $pid, ':order_type' => $order_type);
        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }
    function get_by_order_num($order_code, $order_type) {
        $sql = "select * from {$this->table} where order_code=:order_code AND order_type=:order_type AND num>0";
        $sql_values = array(':order_code' => $order_code, ':order_type' => $order_type);
        $data = $this->db->get_all($sql, $sql_values);
        return $this->format_ret(1, $data);
    }
    //移仓用到仓库
    function get_by_pid_store($pid, $order_type, $store_code) {
        $data = $this->get_all(array('pid' => $pid, 'order_type' => $order_type, 'store_code' => $store_code), 'pid,p_detail_id,order_code,order_type,goods_id,goods_code,spec1_id,spec1_code,spec2_id,spec2_code,sku,store_id,store_code,lof_no,production_date,num,occupy_type');
        return $data;
    }

    /**
     * 获得该单据数量
     * @param   string  $type 
     * @param   int    $pid 
     * @param   string  $sku    SKU编号
     */
    function get_detail_cnt($type, $pid, $sku) {
        $sql = "select  sum(num) as cnt  from b2b_lof_datail where  order_type = :type and pid = :pid and sku = :sku ";
        $arr = array(':type' => $type, ':pid' => $pid, ':sku' => $sku);
        //echo $sql;
        //print_r($arr);
        $data = $this->db->get_all($sql, $arr);
        return $data;
    }

    /**
     * 获得该单据数量(通知单)
     * @param   string  $type
     * @param   int    $pid
     * @param   string  $sku    SKU编号
     */
    function get_detail_notice_cnt($type, $pid, $sku) {
        $sql = "select  sum(num) as num, sum(init_num) as init_num,sum(fill_num) as fill_num from b2b_lof_datail where  order_type = :type and pid = :pid and sku = :sku ";
        $arr = array(':type' => $type, ':pid' => $pid, ':sku' => $sku);
        //echo $sql;
        //print_r($arr);
        $data = $this->db->get_all($sql, $arr);
        return $data;
    }

    //采购单批发单强制入库设置
    function set_init_num($order_code, $order_type) {
        $sql = " update b2b_lof_datail set num=init_num where order_code='{$order_code}' AND order_type='{$order_type}' ";
        return $this->query($sql);
    }

    function set_lof_datail($order_code, $order_type, $detail_data, $check = 1) {
        $detail_data_new = array();
        $sku_arr = array();
        foreach ($detail_data as $val) {
            if ($check == 0 && $val['num'] == 0) {
                continue;
            }
            $detail_data_new[$val['sku']] = $val;
            $sku_arr[] = $val['sku'];
        }

        $sql = "select goods_code,spec1_code,spec2_code,sku,lof_no,production_date,store_code,num from b2b_lof_datail  where order_code=:order_code AND order_type=:order_type ";
        $sql_values = array(':order_type' => $order_type, ':order_code' => $order_code);
        $sql.=" AND sku in('" . implode("','", $sku_arr) . "')";
        $data = $this->db->get_all($sql, $sql_values);
        $new_detaile_lof = array();
        foreach ($data as $val) {
            if (isset($detail_data_new[$val['sku']])) {
                $detail_info = &$detail_data_new[$val['sku']];
                $num = ($check == 1) ? $detail_info['enotice_num'] : $detail_info['num'];
                if ($val['num'] >= $num) {
                    $val['num'] = $num;
                    unset($detail_data_new[$val['sku']]);
                } else {
                    $num -=$val['num'];
                    if ($check == 1) {
                        $detail_info['enotice_num'] = $num;
                    } else {
                        $detail_info['num'] = $num;
                    }
                }
                $val['occupy_type'] = 0;
                $new_detaile_lof[] = $val;
            }
        }

        if (!empty($detail_data_new)) {
            return $this->format_ret(-1, $detail_data_new);
        }
        return $this->format_ret(1, $new_detaile_lof);
    }

    /**
     * 根据明细的sku和主单据id,判断明细是否已经存在
     * @param   int     $pid    主单据ID
     * @param   string  $sku    SKU编号
     * @return  boolean 存在返回true
     */
    private function is_detail_exists($pid, $type, $store_code, $sku, $lof, $production_date) {
        $ret = $this->get_row(array(
            'pid' => $pid,
            'order_type' => $type,
            'store_code' => $store_code,
            'sku' => $sku,
            'lof_no' => $lof,
            'production_date' => $production_date
        ));
        if ($ret['status'] && !empty($ret['data'])) {
            return true;
        } else {
            return false;
        }
    }

    //数据
    public function detail_list($pid, $type, $store_code, $sku) {
        $ret = $this->get_row(array(
            'pid' => $pid,
            'order_type' => $type,
            'store_code' => $store_code,
            'sku' => $sku
        ));
        return $ret;
    }

    public function detail_all($pid, $type, $store_code, $sku) {
        $ret = $this->get_all(array(
            'pid' => $pid,
            'order_type' => $type,
            'store_code' => $store_code,
            'sku' => $sku
        ));
        return $ret;
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete_pid($pid, $sku, $order_type) {
        $result = parent::delete(array('pid' => $pid, 'sku' => $sku, 'order_type' => $order_type));
        //$this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    /**
     * 根据ID删除行数据
     * @param $id
     * @return array|void
     */
    function delete_lof($id) {
        $result = parent::delete(array('id' => $id));
        //$this->mainWriteBack($detail['data']['pid']);
        return $result;
    }

    function update($ary_detail, $where) {
        return parent::update($ary_detail, $where);
    }

    function insert($ary_detail) {

        //如果规格1 规格2 不存在, 通过sku获取到规格1 规格2的代码和名称
        if (isset($ary_detail['sku']) && !empty($ary_detail['sku'])) {
            $info = load_model('prm/SkuModel')->get_spec_by_sku($ary_detail['sku']);
            //if(!isset($info['goods_code'])){
            if ($info['goods_code'] == '') {
                return $this->format_ret(-1, array(), 'SKU信息不存在:' . $ary_detail['sku']);
            }
            $ary_detail['goods_id'] = $info['goods_id'];
            $ary_detail['goods_code'] = $info['goods_code'];
            $ary_detail['spec1_id'] = $info['spec1_id'];
            $ary_detail['spec1_code'] = $info['spec1_code'];
            $ary_detail['spec2_id'] = $info['spec2_id'];
            $ary_detail['spec2_code'] = $info['spec2_code'];
        } else {
            return $this->format_ret(-1, array(), 'SKU信息不存在:' . $ary_detail['sku']);
        }
        return parent::insert($ary_detail);
    }

    function modify_store_code($store_code, $order_code) {
        $sql = " update {$this->table} set store_code = '{$store_code}' where order_code='{$order_code}'";
        $status = $this->db->query($sql);
        return $this->format_ret(1, array());
    }
    
    /**
     * 维护进销存批次表数据(仅适用不开启批次)
     * @param GoodsInvOptOrderType $order_type
     * @param GoodsInvOptLofOccupyType $occupy_type
     * @param type $record
     * @param type $details
     */
    function update_lof_record(
            $order_type,
            $occupy_type,$store_code,$pid,$order_code,$order_date,$details=array())
    {
        $b2b_lof_datails = array();
        
        $goods_lof_list = array();
        
        foreach ($details as $detail)
        {
			if($detail['num'] == 0)
			{
				continue;
			}

            $b2b_lof_datail = array();

			$b2b_lof_datail['pid'] = $pid;

            $b2b_lof_datail['order_type'] = $order_type;
            $b2b_lof_datail['occupy_type'] = $occupy_type;
            
            $b2b_lof_datail['order_code'] = $order_code;
            $b2b_lof_datail['store_code'] = $store_code;
            $b2b_lof_datail['order_date'] = $order_date;

            $b2b_lof_datail['lof_no'] = $this->default_lof['lof_no'];
            $b2b_lof_datail['production_date'] = $this->default_lof['production_date'];
            
            $b2b_lof_datail['sku'] = $detail['sku'];
			$b2b_lof_datail['goods_code'] = $detail['goods_code'];
            $b2b_lof_datail['num'] = $detail['num'];
            $b2b_lof_datail['create_time'] = strtotime(date('Y-m-d H:i:s'));

            $b2b_lof_datails[] = $b2b_lof_datail;
            
            $goods_lof_list[] = array(
                'sku'=> $detail['sku'],'lof_no'=> $this->default_lof['lof_no'],
                'production_date'=> $this->default_lof['production_date'],'shelf_life'=>0,'type'=>0);
        }

        $sql = "DELETE FROM b2b_lof_datail WHERE order_code =:order_code AND order_type = :order_type";
        $this->query($sql, array(':order_code'=>$order_code,':order_type'=>$order_type));
        $this->insert_multi($b2b_lof_datails);
        $this->insert_multi_duplicate('goods_lof',$goods_lof_list,'lof_no=VALUES(lof_no)');
        
        return $this->format_ret(1, $b2b_lof_datails);
    }
}

/**
 * 库存操作单据类型
 */
class GoodsInvOptOrderType
{
    //批发退货单
    public static  $WBM_RETURN = 'wbm_return';
}

/**
 * 库存操作单据类型
 */
class GoodsInvOptLofOccupyType
{
    //实物库存增加
    public static  $STOCK_NUM_ADD = 3;
    
    //实物库存减少
    public static  $STOCK_NUM_REDUCE = 2;
    
    //锁定库存增加
    public static  $LOCK_NUM_ADD = 1;
}


