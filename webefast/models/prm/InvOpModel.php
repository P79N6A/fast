<?php

/**
 * 库存帐操作核心类
 * 实现功能
 * 1 调整 库存表 批次库存表
 * 2 写 库存表 批次库存表 的变化日志
 * 3 如果是占用库存时，自动生成 oms_sell_record_lof 和 b2b_lof_datail 的按批次占用明细
 * 4 在占用库存 和 扣减实物库存时,负库存验证
 * @author huanghy
 * 第一次 $auto_fill_lof = 0 调用 check 方法，返回哪些商品没有录入批次号 或 生成日期，让用户确认，是否用默认值来生成
 * 按用户确认结果确认调用类时 $auto_fill_lof 的值为0或1
 * 注意调整单的明细不要构造函数的 $occupy_type,而要指定 record_info中的occupy_type,因为可能是加或减库存 1实物锁定，2实物扣减，3实物增加，0无效库存
 * 如果是要占用库存的，record_info 不要传批次号 和 生产日期
 * 采购入库 仓库调整单 record_info 可以不提供 批次号 和 生产日期,用默认值代替。
 * $invobj = new InvOpModel($order_code,$order_type,$store_code,$occupy_type,$record_info,$auto_fill_lof);
 * $invobj->check();//验证数据是否有误，要验证是否录入批次信息，是否有库存扣减等
 * $invobj->adjust();//调整库存
 */
require_model('tb/TbModel');
require_lang('inv');
ini_set('memory_limit', '800M'); //内存限制 
set_time_limit(0); //
class InvOpModel extends TbModel {

    //是否开启批次
    var $lof_status;
    var $delivery_lof_sort;
    //默认批次编号
    var $default_lof_no;
    //默认批次生成日期
    var $default_lof_production_date;
    //订单编号
    var $order_code;
    //单据类型：adjust调整单,purchase采购入库单
    var $order_type;
    //仓库代码 如果存在订单分仓发货的业务,这个参数可以不指定,由库存明细的数组来指定
    var $store_code;
    //库存变化类型 1实物库存增加,2实物库存扣减,3实物锁定增加,4实物锁定扣减  这个如果是调整单可以不指定,由库存明细的数组来指定
    var $occupy_type;

    /**
     * array $record_info 库存变动相关单据信息,
     *     goods_code 商品编码,
     *     sku 商品SKU,
     *     lof_no 批次号,
     *     production_date 生产日期,
     *     num 库存数量,
     *     store_code 仓库代码 -- 这个要写到明细级，比如订单分仓发货的业务会用到
     *     occupy_type 库存变化类型 -- 这个要写到明细级，仓库调整单会用到
     */
    var $record_info;
    //auto_fill_lof = 1 如果 record_info 没指定批次号或生产日期，根据系统默认值自动填
    //  var $auto_fill_lof;
    //在 实物库存扣减 时，是否允许负库存(场景：强制解除缺货时,订单要出库)
    var $allow_minus_inv_by_type2 = 0;
    //在 实物锁定增加 时，是否允许负库存(场景：B2C 退单换货，强行锁库存)
    var $allow_minus_inv_by_type3 = 0;
    var $order_type_arr = array();
    var $check_data = array();
    var $lof_record_data = array();
    var $occupy_type_check = array();
    var $is_check_error = 0;
    var $adjust_after = array();
    var $occupy_inv_type = 0;
    var $sys_record_time = '';
    var $is_adjust_lock = 0; //是否调整锁定 //大于0是已经调整批次，调整批次后不需要检查批次库存
    var $order_date = '';
    var $allow_negative_inv = 0;
    var $effect_inv = true;
    var $oms_lock_type = array(); //oms允许锁定类型
    var $is_has_lock = 1;
    var $is_continue_lock = 0;
    
    public $is_all_lock = 0; //锁定情况 0 未锁定 1 部分锁定， 2全部锁定

    function __construct($order_code = '', $order_type = '', $store_code = '', $occupy_type = 0, $record_info = array(), $auto_fill_lof = 0) {
        parent::__construct();
        $this->order_code = $order_code;
        $this->order_type = $order_type;
        $this->store_code = $store_code;
        $this->occupy_type = $occupy_type;
        $this->record_info = $record_info;
        //  $this->auto_fill_lof = $auto_fill_lof;
        $this->sys_record_time = date('Y-m-d H:i:s');
        $this->order_date = date('Y-m-d');
        $this->_init_inv_info();
        $this->_init_store_info();
        //取消取单据业务日期，以入库日期为准
        $this->set_record_date();
        $this->is_all_lock = 0;
    
    }

    function _init_inv_info() {
        if ($this->occupy_type == 5) {//部分取消 批发通知使用
            $this->occupy_type = 0;
            $this->is_adjust_lock = 1; //调整 锁定数量
        }
        
        if ($this->occupy_type == 6) {//门店直接扣减
            $this->occupy_type = 2;
            $this->is_has_lock = 0; 
        }       
        if ($this->occupy_type == 7) {//追加锁定
            $this->occupy_type = 1;
            $this->is_continue_lock = 1;
        }             
        
        
        $this->order_type_arr = array(
            'adjust' => array('occupy_type' => '0,3'),
            'purchase' => array('occupy_type' => '0,3'),
            'pur_return' => array('occupy_type' => '0,2'),
            'oms' => array('occupy_type' => '0,1,2'),
            'oms_change' => array('occupy_type' => '0,1'),
            'shift_out' => array('occupy_type' => '0,1,2'),
            'shift_in' => array('occupy_type' => '0,3'),
            'oms_return' => array('occupy_type' => '0,3'),
            'wbm_store_out' => array('occupy_type' => '0,2'), //批发销货 1,2 取消掉
            'wbm_return' => array('occupy_type' => '0,3'), //批发退
            'wbm_notice' => array('occupy_type' => '0,1'), //批发通知单
            'pur_return_notice' => array('occupy_type' => '0,1'), //采购退货通知单
            'oms_shop' => array('occupy_type' => '0,1,2'), //门店销售单
            'oms_shop_return' => array('occupy_type' => '0,3'), //门店销售退单
            'stm_stock_lock' => array('occupy_type' => '0,1'), //锁定单
            
        );
        $this->occupy_type_check = array(
            '3' => '0', //3增加库存 《=0不占用库存 采购和调整
            '1' => '0', //1锁定库存 《=0不占用库存 订单，批发，移仓，
            '2' => '1', //2扣减 《=1 锁定库存 订单，批发，移仓，
            '0' => '1', //0取消 《=1 锁定 订单，批发，移仓，
        );
        //occupy_inv_type 为增加
        if ($this->occupy_type == 3) {
            $this->occupy_inv_type = 1; //增加
            if ($this->order_type == 'adjust') {
                $this->occupy_inv_type = 0; //增加，减少都存在
            }
        }
        $order_type_arr = array('wbm_store_out', 'pur_return');
        if ($this->occupy_type == 2 && in_array($this->order_type, $order_type_arr)) {
            $this->occupy_inv_type = -1; //增加
            $this->occupy_type_check['2'] = '0'; //通知单锁定，直接扣减
        }

        $this->oms_lock_type = array('oms', 'oms_change','oms_shop','oms_shop_return');
    }
    function get_type_name(){
        $type_name_arr = array('oms'=>1,'oms_return'=>2,'oms_change'=>3,'oms_shop'=>4,'oms_shop_return'=>5);
        return  isset($type_name_arr[$this->order_type])?$type_name_arr[$this->order_type]:$this->order_type;
 
    }
            
    function _init_store_info() {

        $lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status', 'delivery_lof_sort'));
        $this->lof_status = $lof_manage['lof_status'];
        $this->delivery_lof_sort = $lof_manage['delivery_lof_sort'];


        if (in_array($this->order_type, $this->oms_lock_type) && $this->occupy_type == 1) {
            //订单不允许负库存占用
            $this->allow_negative_inv = 0;
        } else {
            $ret_store = load_model('base/StoreModel')->get_by_code($this->store_code);
            $this->allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        }




        $this->effect_inv = true;

        //wms影响库存盘点
        $effect_type = array('purchase', 'oms_return', 'wbm_return');

        if (in_array($this->order_type, $effect_type)) {
            $not_effect_arr = load_model('sys/ShopStoreModel')->get_no_effect_inv($this->store_code);
            if (in_array($this->order_type, $not_effect_arr)){
                $this->effect_inv = false;
            }
        }
    }

    /*
     * 1允许
     * 0不允许
     */

    function lock_allow_negative_inv($status) {
        if ($status == 1) {
            $ret_store = load_model('base/StoreModel')->get_by_code($this->store_code);
            $this->allow_negative_inv = isset($ret_store['data']['allow_negative_inv']) ? $ret_store['data']['allow_negative_inv'] : 0;
        } else {
            $this->allow_negative_inv = 0;
        }
        
        if($this->lof_status ==0&&$this->occupy_type==1){
            $this->is_adjust_lock = 1;
        }

      
    }
    
    /**
     * 强制设置允许负库存
     */
    function force_negative_inv() {
        $this->allow_negative_inv = 1;
    }

    function add_adjust_after($mod, $method, $param) {
        $this->adjust_after['mod'] = $mod;
        $this->adjust_after['method'] = $method;
        $this->adjust_after['param'] = $param;
    }

    function run_adjust_after() {
        if (!empty($this->adjust_after)) {
            $mod = $this->adjust_after['mod'];
            $method = $this->adjust_after['method'];
            $param = $this->adjust_after['param'];
            return $mod->$method($param);
        }
    }

    private function check_record() {

      //  $spec_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        $chk_fld_arr = array(
            'goods_code' => lang('INV_GOODS_CODE'),

            'lof_no' => lang('INV_LOF_NO'), //锁定判断不带批号
            'production_date' => lang('INV_PRODUCATION_DATE'), //锁定判断不带批号
            'sku' => lang('INV_SKU'),
            'num' => lang('INV_NUM'),
        );

        $occupy_type_check = &$this->occupy_type_check;
        $this->check_data = array('record_info' => array(), 'record_inv_info' => array(), 'record_conditions' => array(), 'err_arr' => array());
        $record_info = &$this->check_data['record_info'];
        $record_inv_info = &$this->check_data['record_inv_info'];
        $record_conditions = &$this->check_data['record_conditions'];
        $err_arr = &$this->check_data['err_arr'];

        foreach ($this->record_info as $k => $sub_info) {
            $_check_msg = '';
            foreach ($chk_fld_arr as $fld => $fld_name) {
                if (!isset($sub_info[$fld])) {
                    $err_row[] = $fld_name;
                }
            }
            if (!empty($err_row)) {
                $_check_msg = $sub_info['sku'] . join(' ', $err_row) . lang('INV_CHECK_NULL_ERROR');
            }
            
            if ($this->occupy_type_check[$this->occupy_type] != $sub_info['occupy_type']) {
                $_check_msg.=lang('INV_OCCUPY_TYPE_ERROR');
            }
            if ($this->occupy_inv_type <> 0) {
                if ($sub_info['num'] < 1) {
                    $_check_msg.=lang('INV_NUM_ERROR');
                }
            }

            if (isset($sub_info['store_code']) && $sub_info['store_code'] != $this->store_code) {
                $_check_msg.=lang('INV_STORE_ERROR');
            }


            if ($_check_msg != '') {
                $err_arr[] = $_check_msg;
            }
            if (!empty($err_arr)) {
                continue;
            }




            //数据整理
            $sub_info['store_code'] = $this->store_code;
            if ($this->occupy_type == 3) {//库存
                if ($this->occupy_inv_type < 0) {
                    $sub_info['stock_num'] = -$sub_info['num'];
                } else {
                    $sub_info['stock_num'] = $sub_info['num'];
                }
            } else if ($this->occupy_type == 2) {//扣减库存，扣减锁定
                $sub_info['stock_num'] = -$sub_info['num'];
                $occupy_type_arr = explode(',', $this->order_type_arr[$this->order_type]['occupy_type']);
                if (in_array(1, $occupy_type_arr)) {
                    //新增，锁定数量与扫描出库数量不相等时，锁定数量取占用库存数，而非出库数
                    if(isset($sub_info['scan_lock_num'])){
                        $sub_info['lock_num'] = -$sub_info['scan_lock_num'];
                    }else{
                        $sub_info['lock_num'] = -$sub_info['num'];
                    }
                } else {
                    $sub_info['lock_num'] = 0;
                }
            } else if ($this->occupy_type == 1) {//锁定库存
                //表结构不同数据整理
                if (in_array($this->order_type, $this->oms_lock_type)) {
                    $sub_info['record_code'] = $this->order_code;
                } else {
                    $sub_info['order_code'] = $this->order_code;
                    $sub_info['order_type'] = $this->get_type_name();
                }
                $sub_info['lock_num'] = $sub_info['num'];
            } else if ($this->occupy_type == 0) { //取消锁定
                $sub_info['lock_num'] = -$sub_info['num'];
            }

            unset($sub_info['num']);
            if(isset($sub_info['scan_lock_num'])){
                unset($sub_info['scan_lock_num']);
            }
            $_inv_lof_key = $this->get_lof_key($sub_info);
            $record_info[$_inv_lof_key] = $sub_info;
            //查询条件准备
            $record_conditions[$_inv_lof_key] = array(
                "goods_code" => $sub_info['goods_code'],
                 "sku" => $sub_info['sku'],
                "store_code" => $sub_info['store_code'],
                "lof_no" => $sub_info['lof_no'],
               // "production_date" => $sub_info['production_date']
            );

            //库存主表使用
            unset($sub_info['lof_no']);
            unset($sub_info['production_date']);
            $_inv_key = $this->get_inv_key($sub_info);
            if (!isset($record_inv_info[$_inv_key])) {
                $record_inv_info[$_inv_key] = $sub_info;
            } else {
                if (isset($sub_info['stock_num'])) {
                    $record_inv_info[$_inv_key]['stock_num'] += $sub_info['stock_num'];
                }
                if (isset($sub_info['lock_num'])) {
                    $record_inv_info[$_inv_key]['lock_num'] += $sub_info['lock_num'];
                }
            }
        }
   
    }

    function add_lock_record() {
        //暂时缺少强制解除缺货逻辑 3.0有
       
     //   $spec_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1', 'goods_spec2'));
        $chk_fld_arr = array(
            'goods_code' => lang('INV_GOODS_CODE'),

            'sku' => lang('INV_SKU'),
            'num' => lang('INV_NUM'),
        );
        $record_info = array();
        $this->check_data = array('record_info' => array(), 'record_inv_info' => array(), 'record_conditions' => array(), 'err_arr' => array());
        $err_arr = &$this->check_data['err_arr'];
        $sku_arr = array();
        foreach ($this->record_info as $k => $sub_info) {
            $_check_msg = '';
            foreach ($chk_fld_arr as $fld => $fld_name) {
                if (!isset($sub_info[$fld])) {
                    $err_row[] = $fld_name;
                }
            }
            if (!empty($err_row)) {
                $_check_msg = join(' ', $err_row) . lang('INV_CHECK_NULL_ERROR');
            }
            if ($sub_info['num'] < 1) {
                $_check_msg.=lang('INV_LOCK_NUM_ERROR');
            }
            if (!empty($err_arr)) {
                continue;
            }
            $_inv_key = $this->get_inv_key($sub_info);
            $record_info[$_inv_key] = $sub_info;
         
           // $record_conditions[$_inv_key] = "( sku = '{$sub_info['sku']}' and    store_code = '{$sub_info['store_code']}'  )";
            $sku_arr[] = $sub_info['sku'];
            
        }
        if (!empty($err_arr)) {
            return $err_arr;
        }
        $sql_values = array();
        if(empty($sku_arr)){
            $sku_str = "'".$sku_arr."'";
        }else{
            $sku_str = $this->arr_to_in_sql_value($sku_arr, 'sku', $sql_values);
        }
        $sql = "SELECT l.goods_code,l.store_code,l.sku,l.lof_no,l.production_date,l.stock_num,l.lock_num,i.stock_num as i_stock_num,i.lock_num as i_lock_num FROM goods_inv_lof l
          INNER JOIN goods_inv i ON l.store_code=i.store_code AND l.sku=i.sku
              where  l.store_code='{$this->store_code}' AND l.sku in({$sku_str}) AND l.stock_num>l.lock_num AND  i.stock_num>i.lock_num ";
        $lof_data = $this->db->getAll($sql,$sql_values);
        $record_lof_info = array();

        foreach ($lof_data as $k => $sub_lof) {
            $_inv_key = $this->get_inv_key($sub_lof);
            $_inv_lof_key = $this->get_lof_key($sub_lof);
            $sub_lof['lock_num'] = $sub_lof['lock_num'] < 0 ? 0 : $sub_lof['lock_num'];
             
            //解决批次锁定异常，会导致解除缺货异常
            $lof_available_num = $sub_lof['stock_num']-$sub_lof['lock_num'];
            $i_available_num = $sub_lof['i_stock_num']-$sub_lof['i_lock_num'];
            if($lof_available_num>$i_available_num){
                $sub_lof['stock_num'] = $sub_lof['stock_num']-($lof_available_num-$i_available_num);
            }      
            $record_lof_info[$_inv_key][$_inv_lof_key] = $sub_lof;
        }

        //$lof_manage = load_model('sys/SysParamsModel')->get_val_by_code(array('lof_status', 'delivery_lof_sort'));
       

        $record_inv_info = &$this->check_data['record_inv_info']; //商品级别数据
        $new_record_info = &$this->check_data['record_info']; //批次级数据

        $ret_lof_data = load_model('prm/GoodsLofModel')->get_sys_lof();
        foreach ($record_lof_info as $inv_key => $sub_lof_info) {
            $_lock_num = $record_info[$inv_key]['num'];

            if (count($sub_lof_info) > 1) {//需要排序
                if ($this->lof_status == 0) {//不排序
                    $this->set_lock_record_info($record_info, $inv_key, $sub_lof_info, $_lock_num, $record_inv_info, $new_record_info);
                } else {
                    $sub_lof_info = $this->sort_lof_record_info($sub_lof_info, $this->delivery_lof_sort, $ret_lof_data['data']);
                    $this->set_lock_record_info($record_info, $inv_key, $sub_lof_info, $_lock_num, $record_inv_info, $new_record_info);
                }
            } else {//直接锁定
                $new_lof_info = array_values($sub_lof_info);
                $_inv_lof_key = $this->get_lof_key($new_lof_info[0]);
                $available_num = $new_lof_info[0]['stock_num'] - $new_lof_info[0]['lock_num']; //可用库存
                if ($available_num > 0) {//去掉可用库存为0情况
                    if ($available_num < $_lock_num) {
                        $now_lock_num = $available_num;
                    } else {
                        $now_lock_num = $_lock_num;
                    }
                    $_lock_num = $_lock_num - $now_lock_num;
                    $new_record_info[$_inv_lof_key] = $record_info[$inv_key];
                    $new_record_info[$_inv_lof_key]['lock_num'] = $now_lock_num;
                    $new_record_info[$_inv_lof_key]['production_date'] = $new_lof_info[0]['production_date'];
                    $new_record_info[$_inv_lof_key]['lof_no'] = $new_lof_info[0]['lof_no'];
                    $new_record_info[$_inv_lof_key]['occupy_type'] = $this->occupy_type;
                    $record_inv_info[$inv_key] = $record_info[$inv_key];
                    $record_inv_info[$inv_key]['lock_num'] = $now_lock_num;
                    $this->set_lock_record_conditions($_inv_lof_key, $new_lof_info[0]);
                }
            }
            //部分锁定，允许负库存
            if ($_lock_num > 0 && $this->allow_negative_inv == 1) {
                $lof_info = $this->get_sku_lof_new($record_info[$inv_key]['sku']);
                $_inv_lof_key = $this->get_lof_key($lof_info);
                if (isset($new_record_info[$_inv_lof_key])) {
                    $new_record_info[$_inv_lof_key]['lock_num'] +=$_lock_num;
                    $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                } else {
                    $lof_info['lock_num'] = $_lock_num;
                    $lof_info['occupy_type'] = $this->occupy_type;
                    $lof_info = array_merge($record_info[$inv_key], $lof_info);
                    $new_record_info[$_inv_lof_key] = $lof_info;

                    if (isset($record_inv_info[$inv_key])) {
                        $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                    } else {
                        $record_inv_info[$inv_key] = $record_info[$inv_key];
                        $record_inv_info[$inv_key]['lock_num'] = $_lock_num;
                    }
                    $this->set_lock_record_conditions($_inv_lof_key, $lof_info);
                }
                $_lock_num = 0;
            }
            if ($_lock_num == 0) {
                unset($record_info[$inv_key]);
            }
        }

        //允许负库存处理
        if ($this->allow_negative_inv == 1 && !empty($record_info)) {
            foreach ($record_info as $inv_key => $sub_info) {
                $_lock_num = $sub_info['num'];
                $lof_info = $this->get_sku_lof_new($sub_info['sku']);
                              
                //合并数据
                $_inv_lof_key = $this->get_lof_key($lof_info);
                if (isset($new_record_info[$_inv_lof_key])) {
                    $new_record_info[$_inv_lof_key]['lock_num'] +=$_lock_num;
                    $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                } else {
                    $lof_info['lock_num'] = $_lock_num;
                    $lof_info['occupy_type'] = $this->occupy_type;
                    $lof_info = array_merge($record_info[$inv_key], $lof_info);
                    $new_record_info[$_inv_lof_key] = $lof_info;
                    if (isset($record_inv_info[$inv_key])) {
                        $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                    } else {
                        $record_inv_info[$inv_key] = $record_info[$inv_key];
                        $record_inv_info[$inv_key]['lock_num'] = $_lock_num;
                    }
                    $this->set_lock_record_conditions($_inv_lof_key, $lof_info);
         
                }
             unset($record_info[$inv_key]);
            }
        }
        
        if(!empty($new_record_info)){
            $this->is_all_lock = 2;
            if(!empty($record_info)){
                 $this->is_all_lock = 1;
            }
        }
     

        foreach ($this->check_data['record_info'] as $key => $row) {
            $this->check_data['record_info'][$key]['p_detail_id'] = isset($row['sell_record_detail_id']) ? $row['sell_record_detail_id'] : 0;
            $this->check_data['record_info'][$key]['record_code'] = isset($row['sell_record_code']) ? $row['sell_record_code'] : $row['record_code'];
            $this->check_data['record_info'][$key]['stock_num'] = -1 * $row['lock_num'];
        }
    }

    function get_sku_lof_new($sku) {
        static $lof_sku_info_arr = NULL;

        if (!isset($lof_sku_info_arr[$sku])) {
            $def_row = $this->db->get_row("select lof_no,production_date from goods_lof where type=1");
            $goods_info = $this->db->get_row("select goods_code from goods_sku where sku='{$sku}'");
            $row = $this->db->get_row("select sku,lof_no,production_date from goods_lof
                where sku='{$sku}' and lof_no<>'{$def_row['lof_no']}' and production_date<>'{$def_row['production_date']}'
                order by production_date,lof_no desc ");
            $lof_info = array();
            if (!empty($row)) {
                $lof_info = array_merge($goods_info, $row);
            } else {
                $def_row['sku'] = $sku;
                $lof_info = array_merge($goods_info, $def_row);
            }

            $lof_info['store_code'] = $this->store_code;
            $lof_sku_info_arr[$sku] = $lof_info;

            $this->insert_multi_exp('goods_inv', array($lof_info), true);
            $this->insert_multi_exp('goods_inv_lof', array($lof_info), true);
        }


        return $lof_sku_info_arr[$sku];
    }

    private function set_lock_record_info(&$record_info, &$inv_key, &$sub_lof_info, &$_lock_num, &$record_inv_info, &$new_record_info) {

        foreach ($sub_lof_info as $new_sub_lof) {
            $inv_lof_key = $this->get_lof_key($new_sub_lof);
            $available_num = $new_sub_lof['stock_num'] - $new_sub_lof['lock_num'];
            if ($available_num == 0) {//去掉可用库存为0情况
                continue;
            }
            $now_lock_num = ($available_num >= $_lock_num) ? $_lock_num : $available_num;
            $new_record_info[$inv_lof_key] = $record_info[$inv_key];
            $new_record_info[$inv_lof_key]['lock_num'] = $now_lock_num;
            $new_record_info[$inv_lof_key]['production_date'] = $new_sub_lof['production_date'];
            $new_record_info[$inv_lof_key]['lof_no'] = $new_sub_lof['lof_no'];
            $new_record_info[$inv_lof_key]['occupy_type'] = $this->occupy_type;
            $this->set_lock_record_conditions($inv_lof_key, $new_sub_lof);
            if (!isset($record_inv_info[$inv_key])) {
                $record_inv_info[$inv_key] = $record_info[$inv_key];
                $record_inv_info[$inv_key]['lock_num'] = $now_lock_num;
            } else {
                $record_inv_info[$inv_key]['lock_num'] += $now_lock_num;
            }
            $_lock_num = $_lock_num - $now_lock_num; //剩余锁定数量
            if ($_lock_num == 0) {
                break;
            }
        }
    }

    function adjust_lock_record($adjust_record_info, $is_save = 1) {

        if(empty($adjust_record_info)){
            return $this->format_ret(-5, array(), 'INV_LOCK_ERROR');
        }
        //1、查询SKU 批次库存
        //2、去掉本次锁定数量
        //3、锁定批次
        $record_del_conditions = array();
        $record_conditions = array();
        $record_info = array();
        foreach ($adjust_record_info as $sub_info) {//原始批次级别
            $sub_info['store_code'] = isset($sub_info['store_code']) ? $sub_info['store_code'] : $this->store_code;
            $_inv_key = $this->get_inv_key($sub_info);
            $record_conditions[$_inv_key] = " ( sku = '{$sub_info['sku']}' and store_code = '{$sub_info['store_code']}' )";
            if (isset($sub_info['lof_no']) && isset($sub_info['production_date'])) {
                $_lof_key = $this->get_lof_key($sub_info);
                $record_del_conditions[$_lof_key] = "( sku = '{$sub_info['sku']}' and  lof_no = '{$sub_info['lof_no']}'  )";
            }
            unset($sub_info['lof_no']);
            unset($sub_info['production_date']);
            if (isset($record_info[$_inv_key])) {
                $record_info[$_inv_key]['num']+=$sub_info['num'];
            } else {
                $record_info[$_inv_key] = $sub_info;
            }
        }
        
        $sql = "SELECT goods_code,store_code,sku,lof_no,production_date,stock_num,lock_num
             FROM goods_inv_lof  where  " . implode(' OR ', $record_conditions) . " ";
        //获取库存
        $lof_data = $this->db->getAll($sql);
        $record_lof_info = array();
        if (!isset($this->check_data['record_info'])) {
            $this->check_data['record_info'] = array();
        }
        if (!isset($this->check_data['record_inv_info'])) {
            $this->check_data['record_inv_info'] = array();
        }

        $record_info_inv = &$this->check_data['record_inv_info']; //商品级别数据
        $record_info_lof = &$this->check_data['record_info']; //批次级数据

        //负库存可以跳过
        if (empty($lof_data) && $this->allow_negative_inv == 0) {
            return $this->format_ret(-5, array(), 'INV_LOCK_ERROR');
        }

        foreach ($lof_data as $k => $sub_lof) {
            $_inv_key = $this->get_inv_key($sub_lof);
            $_inv_lof_key = $this->get_lof_key($sub_lof);
            $sub_lof['lock_num'] = $sub_lof['lock_num'] < 0 ? 0 : $sub_lof['lock_num'];
            $old_lock_num = isset($record_info_lof[$_inv_lof_key]) ? $record_info_lof[$_inv_lof_key]['lock_num'] : 0;

            $sub_lof['lock_num'] +=$old_lock_num; //增加锁定数量

            $available_num = $sub_lof['stock_num'] - $sub_lof['lock_num'];
            if ($available_num > 0) {
                $record_lof_info[$_inv_key][$_inv_lof_key] = $sub_lof;
            }
        }
        //负库存可以跳过
        if (empty($record_lof_info) && $this->allow_negative_inv == 0) {
            return $this->format_ret(-4, array(), 'INV_LOCK_ERROR');
        }

        $record_inv_info = array();
        $new_record_info = array();

        foreach ($record_lof_info as $inv_key => $sub_lof_info) {
            $_lock_num = $record_info[$inv_key]['num'];
            if (count($sub_lof_info) > 1) {
                $this->set_lock_record_info($record_info, $inv_key, $sub_lof_info, $_lock_num, $record_inv_info, $new_record_info);
            } else {//直接锁定
                $new_lof_info = array_values($sub_lof_info);
                $_inv_lof_key = $this->get_lof_key($new_lof_info[0]);
                $available_num = $new_lof_info[0]['stock_num'] - $new_lof_info[0]['lock_num']; //可用库存
                if ($available_num < $_lock_num) {
                    $now_lock_num = $available_num;
                } else {
                    $now_lock_num = $_lock_num;
                }
                $_lock_num = $_lock_num - $now_lock_num;
                $new_record_info[$_inv_lof_key] = $record_info[$inv_key];
                $new_record_info[$_inv_lof_key]['lock_num'] = $now_lock_num;
                $new_record_info[$_inv_lof_key]['production_date'] = $new_lof_info[0]['production_date'];
                $new_record_info[$_inv_lof_key]['lof_no'] = $new_lof_info[0]['lof_no'];
                $new_record_info[$_inv_lof_key]['occupy_type'] = $this->occupy_type;
                $this->set_lock_record_conditions($_inv_lof_key, $new_lof_info[0]);
            }
            //部分锁定，允许负库存
            if ($_lock_num > 0 && $this->allow_negative_inv == 1) {
                $lof_info = $this->get_sku_lof_new($record_info[$inv_key]['sku']);
                $_inv_lof_key = $this->get_lof_key($lof_info);
                if (isset($new_record_info[$_inv_lof_key])) {
                    $new_record_info[$_inv_lof_key]['lock_num'] +=$_lock_num;
                } else {
                    $lof_info['lock_num'] = $_lock_num;
                    $lof_info['occupy_type'] = $this->occupy_type;
                    $lof_info = array_merge($record_info[$inv_key], $lof_info);
                    $new_record_info[$_inv_lof_key] = $lof_info;
                    $this->set_lock_record_conditions($_inv_lof_key, $lof_info);
                }
                $_lock_num = 0;
            }
            if ($_lock_num == 0) {
                unset($record_info[$inv_key]);
            }
        }

         
        //允许负库存处理
        if ($this->allow_negative_inv == 1 && !empty($record_info)) {
            foreach ($record_info as $inv_key => $sub_info) {
                $_lock_num = $sub_info['num'];
                $lof_info = $this->get_sku_lof_new($sub_info['sku']);
                $_inv_lof_key = $this->get_lof_key($lof_info);
                if (isset($new_record_info[$_inv_lof_key])) {
                    $new_record_info[$_inv_lof_key]['lock_num'] +=$_lock_num;
                    $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                } else {
                    $lof_info['lock_num'] = $_lock_num;
                    $lof_info['occupy_type'] = $this->occupy_type;
                    $lof_info = array_merge($record_info[$inv_key], $lof_info);
                    $new_record_info[$_inv_lof_key] = $lof_info;
                    if (isset($record_inv_info[$inv_key])) {
                        $record_inv_info[$inv_key]['lock_num'] += $_lock_num;
                    } else {
                        $record_inv_info[$inv_key] = $record_info[$inv_key];
                        $record_inv_info[$inv_key]['lock_num'] = $_lock_num;
                    }
                    $this->set_lock_record_conditions($_inv_lof_key, $lof_info);
                }
                unset($record_info[$inv_key]);
            }
        }


        if (!empty($record_info)) {
            return $this->format_ret(-3, $record_info, 'INV_LOCK_ERROR');
        }

        if (!empty($record_del_conditions)) {
            $sql_del = "delete  FROM b2b_lof_datail where  order_code = '{$this->order_code}' and  order_type= '{$this->order_type}' and  ( " . implode(' OR ', $record_del_conditions) . " )";
            $ret = $this->db->query($sql_del);
            $run_num = $this->affected_rows();
            if ($ret === FALSE || $run_num < count($record_del_conditions)) {
                return $this->format_ret(-1, array(), 'INV_LOCK_ERROR');
            }
        }
  
        //数据整理保存
        $new_lof_record = array();
        foreach ($new_record_info as $_inv_lof_key => $sub_lof_info) {
            if (isset($record_info_lof[$_inv_lof_key])) {
                $record_info_lof[$_inv_lof_key]['lock_num'] += $sub_lof_info['lock_num'];
                $inv_key = $this->get_inv_key($sub_lof_info);
                $record_info_inv[$inv_key]['lock_num'] += $sub_lof_info['lock_num'];

                if ($is_save == 1) {
                    $where = "  order_code = '{$this->order_code}' and  order_type= '{$this->order_type}' and   sku = '{$sub_lof_info['sku']}' and  lof_no = '{$sub_lof_info['lof_no']}'  ";
                    $update_data = array('num' => $record_info_lof[$_inv_lof_key]['lock_num'],'order_date'=>$this->order_date);
                    $this->db->update('b2b_lof_datail', $update_data, $where);
                }
            } else {

                $new_lof['order_type'] = $this->get_type_name();
                $new_lof['order_code'] = $this->order_code;
                $new_lof['goods_code'] = $sub_lof_info['goods_code'];

                $new_lof['sku'] = $sub_lof_info['sku'];
                $new_lof['store_code'] = $sub_lof_info['store_code'];
                $new_lof['lof_no'] = $sub_lof_info['lof_no'];
                $new_lof['production_date'] = $sub_lof_info['production_date'];
                $new_lof['occupy_type'] = 0;
                $new_lof['order_date'] =$this->order_date;

                $new_lof['num'] = $sub_lof_info['lock_num'];
                $new_lof['init_num'] = $sub_lof_info['lock_num'];
                $new_lof['pid'] = isset($sub_lof_info['pid']) ? $sub_lof_info['pid'] : 0;
                $new_lof['create_time'] = time();
                $new_lof_record[] = $new_lof;
                $sub_lof_info['num'] = $sub_lof_info['lock_num'];
                $record_info_lof[$_inv_lof_key] = $sub_lof_info;

                $inv_key = $this->get_inv_key($sub_lof_info);
                if (isset($record_info_inv[$inv_key])) {
                    $record_info_inv[$inv_key]['lock_num'] += $sub_lof_info['lock_num'];
                } else {
                    $record_info_inv[$inv_key] = $record_inv_info[$inv_key]; //商品级赋值
                    $record_info_inv[$inv_key]['lock_num'] = $sub_lof_info['lock_num'];
                }
            }
        }

        if ($is_save == 1) {

            $ret = $this->insert_multi_exp('b2b_lof_datail', $new_lof_record);
            $run_num = $this->affected_rows();
            if ($ret === FALSE || $run_num < count($new_lof_record)) {
                return $this->format_ret(-1, array(), 'INV_LOCK_ERROR');
            }
        }
        $this->is_adjust_lock++;
        return $this->format_ret(1);
    }

    //设置数据条件
    private function set_lock_record_conditions($inv_lof_key, $sub_lof) {
        $this->check_data['record_conditions'][$inv_lof_key] = array(
            "goods_code" => $sub_lof['goods_code'],
            "sku" => $sub_lof['sku'],
            "store_code" => $sub_lof['store_code'],
            "lof_no" => $sub_lof['lof_no'],
           // "production_date" => $sub_lof['production_date']
        );
    }

    private function sort_lof_record_info($sub_lof_info, $delivery_lof_sort, $sys_lof_data) {

        $new_sub_lof_info = array_values($sub_lof_info);
        $len = count($new_sub_lof_info);
        //1-商家自定义批次优先发货 0-默认批次优先发货
        for ($i = 1; $i < $len; $i++) {
            for ($j = $len - 1; $j >= $i; $j--) {
                $production_date_1 = strtotime($new_sub_lof_info[$j - 1]['production_date']);
                $production_date_2 = strtotime($new_sub_lof_info[$j]['production_date']);
                if ($delivery_lof_sort == 0) {

                    if ($new_sub_lof_info[$j]['lof_no'] == $sys_lof_data['lof_no']) {
                        if ($new_sub_lof_info[$j]['production_date'] == $sys_lof_data['production_date']) {
                            $x = $new_sub_lof_info[$j];
                            $new_sub_lof_info[$j] = $new_sub_lof_info[$j - 1];
                            $new_sub_lof_info[$j - 1] = $x;
                        } else if ($production_date_1 > $production_date_2) {
                            $x = $new_sub_lof_info[$j];
                            $new_sub_lof_info[$j] = $new_sub_lof_info[$j - 1];
                            $new_sub_lof_info[$j - 1] = $x;
                        }
                    } else {
                        if ($production_date_1 > $production_date_2) {
                            $x = $new_sub_lof_info[$j];
                            $new_sub_lof_info[$j] = $new_sub_lof_info[$j - 1];
                            $new_sub_lof_info[$j - 1] = $x;
                        }
                    }
                } else {
                    if ($new_sub_lof_info[$j - 1]['lof_no'] == $sys_lof_data['lof_no'] && $new_sub_lof_info[$j - 1]['production_date'] == $sys_lof_data['production_date']) {
                        $x = $new_sub_lof_info[$j];
                        $new_sub_lof_info[$j] = $new_sub_lof_info[$j - 1];
                        $new_sub_lof_info[$j - 1] = $x;
                    } else {
                        if ($production_date_1 > $production_date_2) {
                            $x = $new_sub_lof_info[$j];
                            $new_sub_lof_info[$j] = $new_sub_lof_info[$j - 1];
                            $new_sub_lof_info[$j - 1] = $x;
                        }
                    }
                }
            }
        }
        return $new_sub_lof_info;
    }

    /**
     * 库存批次变动公共方法
     *   occupy_type:1实物锁定，2实物扣减，3实物增加，0无效库存
     */
    public function adjust(&$trans_type = 1) {
        if (!isset($this->order_type_arr[$this->order_type])) {
            return $this->format_ret(-1, '', 'INV_ERROR_ORDER_TYPE');
        }

        $result = array();

        if ($this->occupy_type == 1) {//锁定
            if (in_array($this->order_type, $this->oms_lock_type)) {
                $this->add_lock_record(); //添加批次数据
            } else {//批发退
               // if ($this->is_adjust_lock == 0) {//大于0是否已经调整批次，调整批次后不需要检查批次库存
                    $this->check_record(); //支持批次锁定
               // }
            }

            if (!empty($this->check_data['err_arr'])) {
                return $this->format_ret(-2, '', implode(",", $this->check_data['err_arr']));
            }
 
            $this->set_record_check(1); //日志使用
            if ($trans_type == 0) {
                $this->begin_trans();
            }
            $status = $this->lock_record_save(); //锁定数据更新

            //获取批次库存
            if ($status === FALSE) {
                if ($trans_type == 0) {
                    $this->rollback(); //事务回滚
                }
                return $this->format_ret(-10, $this->check_data['record_inv_info'], 'INV_LOCK_ERROR');
            }

            $status = $this->save_order_record(); //保存批次单据记录
            //echo '<hr/>$status<xmp>'.var_export($status,true).'</xmp>';//die;
            if ($status === FALSE) {
                if ($trans_type == 0) {
                    $this->rollback(); //事务回滚
                }
                return $this->format_ret(-1, '', 'INV_LOCK_ORDER_ERROR');
            }
        } else {//调整
            if($this->is_has_lock==1){
                $this->check_record(); //数据整理检测
            }else{
                //实体门店直接扣减库存处理
                 $this->add_lock_record(); //添加批次数据
                $status = $this->save_order_record(); //保存批次单据记录
                 //echo '<hr/>$status<xmp>'.var_export($status,true).'</xmp>';//die;
                 if ($status === FALSE) {
                     if ($trans_type == 0) {
                         $this->rollback(); //事务回滚
                     }
                     return $this->format_ret(-1, '', 'INV_LOCK_ORDER_ERROR');
                 }           
                 
            }
            
            if (!empty($this->check_data['err_arr'])) {
                return $this->format_ret(-2, '', implode(",", $this->check_data['err_arr']));
            }
            $this->set_record_check(1); //日志使用
            if ($trans_type == 0) {
                $this->begin_trans();
            }
            

            $ret = $this->adjust_save();
            if ($ret['status'] < 1) {
                if ($trans_type == 0) {
                    $this->rollback(); //事务回滚
                }
                return $ret;
            }

            $status = $this->set_order_status(); //单据状态变更
            if ($status === FALSE) {
                if ($trans_type == 0) {
                    $this->rollback(); //事务回滚
                }
                return $this->format_ret(-1, '', 'INV_LOCK_ORDER_ERROR');
            }
        }

        if (!empty($this->adjust_after)) {
            $ret = $this->run_adjust_after();
            if ($ret['status'] != 1) {
                if ($trans_type == 0) {
                    $this->rollback(); //事务回滚
                }
                return $ret;
            }
        }

        if ($trans_type == 0) {
            $this->commit(); //事务回滚
        }

        $this->set_record_check(2); //日志使用
        return $this->format_ret(1, $this->check_data['record_inv_info']);
    }

    function lock_check() {
        $err_arr = array();
        foreach ($this->record_info as $suf_info) {
            $_inv_key = $this->get_inv_key($suf_info);
            if (!isset($this->check_data['record_inv_info'][$_inv_key]['lock_num']) || $this->check_data['record_inv_info'][$_inv_key]['lock_num'] < $suf_info['num']) {
                $err_arr[] = $suf_info;
            }
        }
        return true;
    }

    function set_record_check($run_code) {
        ////判断是否开启记录

        if ($run_code == 1) {
            $this->get_inv_data('record_info_old');
        } else {
            $this->get_inv_data('record_info_new');
            $ret = $this->adjust_lof_record();
            return $ret;
        }
    }

    function get_inv_data($inv_key) {
        $conditions_arr = $this->get_conditions($this->check_data['record_conditions'], "l");


        $sql = "SELECT l.goods_code,l.store_code,l.sku,l.lof_no,l.production_date,l.stock_num as stock_lof_num,l.lock_num as lock_lof_num, i.stock_num,i.lock_num
             FROM goods_inv_lof l inner join goods_inv i
             ON l.goods_code = i.goods_code and l.sku = i.sku  and l.store_code = i.store_code ";
        if (!empty($conditions_arr)) {
            $sql .= "where " . implode(' OR ', $conditions_arr);
        } else {
            return FALSE;
        }


        $data = $this->db->getAll($sql);
        $inv_data = array();
        foreach ($data as $val) {
            $this->lof_record_data[$inv_key][$this->get_lof_key($val)] = $val;
        }
        return TRUE;
    }

    private function get_conditions($record_conditions, $tb = "") {
        $conditions_arr = array();
        $tb = empty($tb) ? $tb : $tb . ".";
        foreach ($record_conditions as $val_data) {
            $and_arr = array();
            foreach ($val_data as $key => $val) {
                $and_arr[] = " {$tb}{$key}='{$val}' ";
            }
            $conditions_arr[] = " ( " . implode(" AND ", $and_arr) . " ) ";
        }
        return $conditions_arr;
    }

    private function get_lof_key($info) {
        return $info['store_code'] . ',' . $info['sku'] . ',' . $info['lof_no'] ;
    }

    private function get_inv_key($info) {
        return $info['store_code'] . ',' . $info['sku'] ;
    }

    function set_order_status() {
       if( $this->is_has_lock==0 ){ 
        // 实体门店扣减已经插入不需要更改状态
           return true;
       }
        
        
        $conditions_arr = $this->get_conditions($this->check_data['record_conditions']);
        
        if(empty($conditions_arr)){
             return false;
        }
        
        
        
        $occupy_type = &$this->occupy_type_check[$this->occupy_type];
        if (in_array($this->order_type, $this->oms_lock_type)) {//record_code
            if ($this->occupy_type != 1) {
                if ($this->occupy_type != 0) {
                    $this->order_date = empty($this->order_date) ? date('Y-m-d') : $this->order_date;
                    $sql = "update oms_sell_record_lof set occupy_type='{$this->occupy_type}',order_date='{$this->order_date}' 
                    where record_code='{$this->order_code}' and occupy_type ='{$occupy_type}' and
               (" . implode(" OR ", $conditions_arr) . ')';
                } else {
                    $sql = "delete from oms_sell_record_lof  where record_code='{$this->order_code}' and occupy_type ='{$occupy_type}' and
               (" . implode(" OR ", $conditions_arr) . ')';
                }
                $this->db->query($sql);
                $run_num = $this->affected_rows();
                if (count($conditions_arr) != $run_num) {//执行条数响应条数不相等
                    return FALSE;
                }
            }
        } else {
            if ($this->order_type == 'oms_return') {
                if ($this->occupy_type != 1) {
                    $this->order_date = empty($this->order_date) ? date('Y-m-d') : $this->order_date;
                    $sql = "update oms_sell_record_lof set occupy_type='{$this->occupy_type}',order_date='{$this->order_date}'  where record_code='{$this->order_code}' and occupy_type ='{$occupy_type}' and
                     ( " . implode(" OR ", $conditions_arr) . " )";
                    $this->db->query($sql);
                    $run_num = $this->affected_rows();
                    if (count($this->check_data['record_conditions']) != $run_num) {//执行条数响应条数不相等
                        return FALSE;
                    }
                }
            } else {
                if ($this->occupy_type == 0 && $this->is_adjust_lock == 1) {//部分取消锁定 批发通知使用 wbm_notice
                    $record_info = &$this->check_data['record_info'];
                    foreach ($record_info as $sub_lof) {
                        $num = abs($sub_lof['lock_num']);
                        $sql = " update b2b_lof_datail set num=if(num>{$num},num-{$num},0)  where order_type='{$this->order_type}' AND order_code='{$this->order_code}' ";
                        $sql .=" AND sku='{$sub_lof['sku']}' AND lof_no='{$sub_lof['lof_no']}'  and  occupy_type ='{$occupy_type}' ";

                        $this->db->query($sql);
                    }
                } else if ($this->occupy_type != 1) {
                    $sql = "update b2b_lof_datail set occupy_type='{$this->occupy_type}',order_date='{$this->order_date}' where order_code='{$this->order_code}' and  order_type='{$this->order_type}'  and occupy_type ='{$occupy_type}' and
                     ( " . implode(" OR ", $conditions_arr) . " )";
                    $this->db->query($sql);
                    $run_num = $this->affected_rows();
                    if (count($this->check_data['record_conditions']) != $run_num) {//执行条数响应条数不相等
                        return FALSE;
                    }
                }


                // wbm_notice
                //
                //少批发通知单，部分取消锁定
            }
        }
        return TRUE;
    }

    function save_order_record() {
        // $tb_record = ?'oms_sell_record_lof':'b2b_lof_datail';
        $run_num = 0;
        if (in_array($this->order_type, $this->oms_lock_type)) {
            $insert_data = $this->check_data['record_info'];
            foreach ($insert_data as $k => $sub_ins) {
                $insert_data[$k]['num'] = $sub_ins['lock_num'];
                $insert_data[$k]['create_time'] = time();
                $insert_data[$k]['record_type'] = $this->get_type_name();
                
            }
            $update_str = "num = num +VALUES(num)";
            $this->insert_multi_duplicate('oms_sell_record_lof', $insert_data, $update_str);
            $run_num = $this->affected_rows();
        } else {
            if($this->is_continue_lock ==1){ //stm_stock_lock 锁单单据追加锁定
                $insert_data = $this->check_data['record_info'];  
                $record_conditions = array();
                foreach($insert_data as $val){
                    $record_conditions[] = "( sku = '{$val['sku']}' and  lof_no = '{$val['lof_no']}'  )";
                }
                $sql = "select count(1) from b2b_lof_datail  where  order_type = '{$this->order_type}' and order_code='{$this->order_code}' AND    ( " . implode(' OR ', $record_conditions) . " )";
                $run_num = $this->db->get_value($sql);
            }else{
                $sql = " update b2b_lof_datail set  occupy_type = 1 where  order_type = '{$this->order_type}' and order_code='{$this->order_code}'";
                $this->db->query($sql);  
                $run_num = $this->affected_rows();
            }
        }
        
       
        if ($run_num < count($this->check_data['record_info'])) {
            return FALSE;
        }
        
        return TRUE;
    }

    function lock_record_save() {

        $record_info = &$this->check_data['record_info'];
        $record_inv_info = &$this->check_data['record_inv_info'];
        $this->check_data['adjust_record_info'] = array();
        $adjust_record_info = &$this->check_data['adjust_record_info'];

        if (empty($record_info) && $this->order_type != "oms") {
            return FALSE;
        }
        $error_info = array();
        //  $inv_update_arr = array();

        $allow_negative_inv = $this->allow_negative_inv;
        //处理非自动锁定库存情况
        if (!in_array($this->order_type, $this->oms_lock_type) && $this->lof_status == 0) {
            if ($this->is_adjust_lock > 0) {
                $allow_negative_inv = $this->allow_negative_inv; //已经调整批次后
            } else {
                $allow_negative_inv = 0; //未调整的，强制不允许负库存
            }
        }

        foreach ($record_info as $key => $sub_info) {
            if ($sub_info['lock_num'] == 0) {
                continue;
            }

            $sql_lof = "update goods_inv_lof set lock_num=lock_num+{$sub_info['lock_num']} where ";
            $sql_lof_where = " sku = '{$sub_info['sku']}'  AND  store_code = '{$sub_info['store_code']}' and   lof_no = '{$sub_info['lof_no']}' ";

            //允许负库存判断
            if ($allow_negative_inv == 0) {//不允许负库存，强制判断
                $sql_lof_where.=" and stock_num>={$sub_info['lock_num']}+lock_num"; //负库存锁定通过这个判断
            }
            $sql_lof .=$sql_lof_where;

            $status = $this->db->query($sql_lof);

            $run_num = $this->affected_rows();
            if ($run_num != 1 || $status === FALSE) {
                $sub_info['num'] = $sub_info['lock_num'];
                $sku_key = $this->get_inv_key($sub_info);
                $record_inv_info[$sku_key]['lock_num'] -=$sub_info['lock_num'];
                unset($sub_info['lock_num']);
                $adjust_record_info[$key] = $sub_info;
                 unset($record_info[$key]); //删掉批次
            
               
           
                
                continue;
            }


        }

        foreach ($record_inv_info as $sub_info) {
            if ($sub_info['lock_num'] == 0) {
                continue;
            }
            $sql_inv = "update goods_inv set lock_num=lock_num+{$sub_info['lock_num']},record_time='{$this->sys_record_time}' where ";
            $sql_inv .= " sku = '{$sub_info['sku']}'  and   store_code = '{$sub_info['store_code']}'  ";

            //允许负库存判断
            if ($allow_negative_inv == 0) {//不允许负库存，强制判断
                $sql_inv .= " and stock_num>={$sub_info['lock_num']}+lock_num"; //负库存锁定通过这个判断
            }
            $status = $this->db->query($sql_inv);

            $run_num = $this->affected_rows();
            if ($run_num != 1 || $status === FALSE) {
                $error_info[] = $sub_info; //库存异常  需要重新通过批次统计库存
            }
        }
        //echo '<hr/>$error_info<xmp>'.var_export($error_info,true).'</xmp>';
        //echo '<hr/>$adjust_record_info<xmp>'.var_export($adjust_record_info,true).'</xmp>';
        if(!empty($adjust_record_info) && $allow_negative_inv==1){
             $this->no_goods_inv_record_set($adjust_record_info);
             
             //恢复到批次数据
             $record_info = array_merge($record_info,$adjust_record_info);
             
             $adjust_record_info = array();
        }
        if (!empty($error_info) || !empty($adjust_record_info)) {
            return FALSE;
        }
        return true;
    }
    
    function no_goods_inv_record_set($adjust_record_info){
        $goods_inv_data = array();
        $goods_inv_lof_data = array();
        
        foreach($adjust_record_info as $val){
            $key = $this->get_inv_key($val);
            if(isset($goods_inv_data[$key])){
                $goods_inv_data[$key]['lock_num']+=$val['num'];
            }else{
                 $goods_inv_data[]= array(
                    'goods_code'=>$val['goods_code'],
                    'spec1_code'=>$val['spec1_code'],
                    'spec2_code'=>$val['spec2_code'],
                    'sku'=>$val['sku'],
                    'store_code'=>$val['store_code'],
                    'stock_num'=>0,
                    'lock_num'=>$val['num'],
                    'record_time'=>$this->sys_record_time,
                );    
            }
            $goods_inv_lof_data[] = array(
                'goods_code'=>$val['goods_code'],
                'spec1_code'=>$val['spec1_code'],
                'spec2_code'=>$val['spec2_code'],
                'sku'=>$val['sku'],
                'store_code'=>$val['store_code'],
                'stock_num'=>0,
                'lock_num'=>$val['num'],
                'lof_no'=>$val['lof_no'],
                'production_date'=>$val['production_date'],
            );
        }
        $update_str_inv = " record_time = VALUES(record_time),lock_num = VALUES(lock_num)+lock_num " ;
        $this->insert_multi_duplicate('goods_inv', $goods_inv_data, $update_str_inv);
        $update_str = " lock_num = VALUES(lock_num)+lock_num " ;
        $this->insert_multi_duplicate('goods_inv_lof', $goods_inv_lof_data, $update_str);
        
    }

    

    function adjust_save() {//不包含锁定
        $record_info = &$this->check_data['record_info'];

        $record_inv_info = &$this->check_data['record_inv_info'];
        $tb_inv_lof = 'goods_inv_lof';
        
        if ($this->effect_inv === FALSE) {
            return $this->format_ret(1); //不影响库存
        }


        if ($this->occupy_type == 3) {//库存增加减少
            if ($this->occupy_inv_type == 1) {//增加
                return $this->add_goods_inv($record_info, $record_inv_info);
            } else if ($this->occupy_inv_type == -1) {//扣减存在
                if ($this->allow_negative_inv == 1) {
                    return $this->add_goods_inv($record_info, $record_inv_info);
                } else {
                    return $this->reduce_goods_inv($record_info);
                }
            } else if ($this->occupy_inv_type == 0) {//增加 扣减都存在
                if ($this->allow_negative_inv == 1) {
                    return $this->add_goods_inv($record_info, $record_inv_info);
                } else {
                    $record_info_reduce = array();
                    $record_inv_info = array();
                    $record_info_new = array();
                    foreach ($record_info as $key => $sub_info) {
                        if ($sub_info['stock_num'] < 0) {
                            $record_info_reduce[$key] = $sub_info; //扣减
                        } else {
                            $_inv_key = $sub_info['store_code'] . ',' . $sub_info['sku']  ;
                            //unset($sub_info['lof_no']);unset($sub_info['production_date']);
                            if (isset($record_inv_info[$_inv_key])) {
                                $record_inv_info[$_inv_key]['stock_num'] += $sub_info['stock_num'];
                            } else {
                                $record_inv_info[$_inv_key] = $sub_info;
                            }
                            $record_info_new[$key] = $sub_info;
                        }
                    }
                    if (!empty($record_info_new)) {//增加
                        $ret = $this->add_goods_inv($record_info_new, $record_inv_info);
                        if ($ret['status'] < 1) {
                            return $ret;
                        }
                    }
                    if (!empty($record_info_reduce)) {//扣减
                        return $this->reduce_goods_inv($record_info_reduce);
                    }
                }
            }
        } else if ($this->occupy_type == 2) {//锁定扣减
            foreach ($record_info as $sub_info) {
                $change_num = abs($sub_info['stock_num']);
                if ($this->order_type == 'wbm_store_out' || $this->order_type == 'pur_return') {
                    $set_lock_num = "";
                } else if ($this->is_has_lock == 1) {
                    $set_lock_num = ", lock_num = lock_num +{$sub_info['lock_num']} ";
                }
                $sql_lof = "update goods_inv_lof set stock_num=stock_num+{$sub_info['stock_num']} " . $set_lock_num;
                $sql_lof .= " where sku = '{$sub_info['sku']}'   and   store_code = '{$sub_info['store_code']}' and   lof_no = '{$sub_info['lof_no']}' ";
                if ($this->allow_negative_inv == 0) {
                    $sql_lof .= " and stock_num>={$change_num} "; //负库存锁定通过这个判断  
                }
                $status = $this->db->query($sql_lof);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {

                    return $this->format_ret(-1, '', 'INV_ADJUST_DB_EXP');
                }

                $sql_inv = "update goods_inv set stock_num=stock_num+{$sub_info['stock_num']} ,record_time='{$this->sys_record_time}' " . $set_lock_num;
                $sql_inv .= " where sku = '{$sub_info['sku']}' and   store_code = '{$sub_info['store_code']}' ";
                if ($this->allow_negative_inv == 0) {
                    $sql_lof .= " and stock_num>={$change_num} "; //负库存锁定通过这个判断  
                }
                
                $status = $this->db->query($sql_inv);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {
                    return $this->format_ret(-1, '', 'INV_ADJUST_DB_EXP');
                }
            }
        } else if ($this->occupy_type == 0) {//锁定取消
            $notice_type_arr = array('wbm_notice', 'pur_return_notice', 'stm_stock_lock');
            foreach ($record_info as $sub_info) {
                if (in_array($this->order_type, $notice_type_arr) && abs($sub_info['lock_num']) == 0) {
                    continue;
                }
                $sql_lof = "update goods_inv_lof set  lock_num = lock_num +{$sub_info['lock_num']} ";
                $sql_lof .= " where sku = '{$sub_info['sku']}'  and   store_code = '{$sub_info['store_code']}' and   lof_no = '{$sub_info['lof_no']}' ";

                $sql_lof .= " and lock_num>=" . abs($sub_info['lock_num']);
                $status = $this->db->query($sql_lof);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {
                    return $this->format_ret(-1, '', 'INV_ADJUST_DB_EXP');
                }
                $sql_inv = "update goods_inv set  lock_num = lock_num +{$sub_info['lock_num']},record_time='{$this->sys_record_time}' ";
                $sql_inv .= " where sku = '{$sub_info['sku']}' and   store_code = '{$sub_info['store_code']}' ";
                $sql_inv .= " and   lock_num>=" . abs($sub_info['lock_num']);
                $status = $this->db->query($sql_inv);
                $run_num = $this->affected_rows();
                if ($run_num != 1 || $status === FALSE) {
                    return $this->format_ret(-1, '', 'INV_ADJUST_DB_EXP');
                }
            }
        }

        return $this->format_ret(1);
    }

    private function add_goods_inv($record_info, $record_inv_info) {

        $tb_inv_lof = 'goods_inv_lof';
        $update_str = " stock_num = stock_num +VALUES(stock_num)";
        $ret = $this->insert_multi_duplicate($tb_inv_lof, $record_info, $update_str);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', 'INV_ADJUST_DB_ERROR');
        }
        $tb_inv = "goods_inv";
        $update_str = " stock_num = stock_num +VALUES(stock_num),record_time='{$this->sys_record_time}'";
        $this->adjust_goods_inv_info($record_inv_info);
        $ret = $this->insert_multi_duplicate($tb_inv, $record_inv_info, $update_str);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', 'INV_ADJUST_DB_ERROR');
        }
        return $this->format_ret(1);
    }

    private function adjust_goods_inv_info(&$record_inv_info) {
        foreach ($record_inv_info as &$recode) {
            $recode['record_time'] = $this->sys_record_time;
        }
    }

   private function reduce_goods_inv($record_info) {

        foreach ($record_info as $sub_info) {

            if (($this->order_type == 'wbm_store_out' || $this->order_type == 'pur_return') && $sub_info['stock_num'] == 0) {
                //差异验收
                continue;
            }
            $sql_lof = "update goods_inv_lof set stock_num=stock_num+{$sub_info['stock_num']} ";
            $sql_lof .= " where sku = '{$sub_info['sku']}' and   store_code = '{$sub_info['store_code']}' and   lof_no = '{$sub_info['lof_no']}' ";
            if ($this->allow_negative_inv == 0) {
                $sql_lof .= " and stock_num>=" . abs($sub_info['stock_num']); //负库存锁定通过这个判断      
            }
            $status = $this->db->query($sql_lof);
            $run_num = $this->affected_rows();
            if ($run_num != 1 || $status === FALSE) {
                return $this->format_ret(-1, '', 'INV_ADJUST_MINUS_EXP');
            }
            $sql_inv = "update goods_inv set stock_num=stock_num+{$sub_info['stock_num']},record_time='{$this->sys_record_time}'";
            $sql_inv .= " where sku = '{$sub_info['sku']}'   and   store_code = '{$sub_info['store_code']}' ";
            if ($this->allow_negative_inv == 0) {
                $sql_inv .= " and stock_num>=" . abs($sub_info['stock_num']); //负库存锁定通过这个判
            }
            $status = $this->db->query($sql_inv);
            $run_num = $this->affected_rows();
            if ($run_num != 1 || $status === FALSE) {
                return $this->format_ret(-1, '', 'INV_ADJUST_MINUS_EXP');
            }
        }
        return $this->format_ret(1);
    }
    
    //查询可用库存情况 返回不足的sku和可用库存
    function get_usable_inv($details){
    	$lof_status = $this->lof_status;
    	$insufficient_sku = array();
    	if($this->allow_negative_inv == 0){
    		if ($lof_status == 1){
    			$table = 'goods_inv_lof';
    		} else {
    			$table = 'goods_inv';
    		}
    		foreach ($details as $detail){
    			$sql = "select stock_num,lock_num from {$table} where sku = '{$detail['sku']}'  and store_code = '{$detail['store_code']}'";
    			if ($lof_status == 1){
    				$sql .= " and lof_no = '{$detail['lof_no']}' ";
    			}
    			$inv_row = $this->db->get_row($sql);
    			$num = $detail['num'];
    			$available_num = $inv_row['stock_num'] - $inv_row['lock_num'];
    			if ($num > $available_num){
    				$insufficient_sku[$detail['sku']] = $available_num;
    			}
    		}
    	}
    	return $insufficient_sku;
    }

    /**
     * hhy
     *  根据批次库存表的数量 和 要改变库存的数量 来计算出最新的库存量
     *  要处理锁定数不能小于0，是否允许负库存
     */
    function get_adjust_result($record_info, $inv_num_arr, $inv_db_info) {
        $minus_inv_err = array();
        $inv_lof_result = array();
        $inv_lof_record_result = array();
        if ($this->occupy_type == 3) {
            foreach ($record_info as $k => $sub_info) {
                $tk1 = $sub_info['store_code'] . ',' . $sub_info['sku'];
                $inv_db_arr = $inv_db_info[$tk1];
                //当前要锁定多少库存
                $stock_assign_num = $sub_info['num'];
                if (!empty($inv_db_arr)) {
                    foreach ($inv_db_arr as $sub_inv) {
                        $k = $sub_inv['store_code'] . ',' . $sub_inv['sku'] . ',' . $sub_inv['lof_no'] . ',' . $sub_inv['production_date'];
                        //当前批次的明细可用库存是多少
                        $_lock_num = (int) $sub_inv['lock_num'] < 0 ? 0 : $sub_inv['lock_num'];
                        $_stock_usable = $sub_inv['stock_num'] - $_lock_num;
                        $_new_lock_num = $stock_assign_num > $_stock_usable ? $_stock_usable : $stock_assign_num;
                        $row_inv_lof_result = array(
                            'store_code' => $sub_inv['store_code'],
                            'goods_code' => $sub_info['goods_code'],

                            'sku' => $sub_inv['sku'],
                            'lof_no' => $sub_inv['lof_no'],
                            'production_date' => $sub_inv['production_date'],
                        );
                        $row_inv_lof_record_result = $row_inv_lof_result;
                        $row_inv_lof_record_result['order_code'] = $sub_info['order_code'];
                        $row_inv_lof_record_result['order_type'] = $sub_info['order_type'];

                        $inv_lof_result[] = $row_inv_lof_result;
                        $inv_lof_record_result[] = $row_inv_lof_record_result;

                        $stock_assign_num = $stock_assign_num - $_new_lock_num;
                        if ($stock_assign_num <= 0) {
                            break;
                        }
                    }
                }
            }
        } else {
            foreach ($record_info as $k => $sub_info) {
                $tk1 = $sub_info['store_code'] . ',' . $sub_info['sku'];
                $tk2 = $sub_info['lof_no'] . ',' . $sub_info['production_date'];
                $k = $tk1 . ',' . $tk2;
                $inv_db_row = $inv_db_info[$tk1][$tk2];
                $inv_lof_result[$k] = array(
                    'store_code' => $sub_info['store_code'],
                    'goods_code' => $sub_info['goods_code'],
                    'sku' => $sub_info['sku'],
                    'lof_no' => $sub_info['lof_no'],
                    'production_date' => $sub_info['production_date'],
                    'stock_num' => (int) $inv_db_row['stock_num'],
                    'lock_num' => (int) $inv_db_row['lock_num'] < 0 ? 0 : $inv_db_row['lock_num'],
                );
                if ($sub_info['occupy_type'] == 1) {
                    $inv_lof_result[$k]['stock_num'] = $inv_lof_result[$k]['stock_num'] + $sub_info['num'];
                }
                if ($sub_info['occupy_type'] == 2) {
                    $inv_lof_result[$k]['stock_num'] = $inv_lof_result[$k]['stock_num'] - $sub_info['num'];
                    $inv_lof_result[$k]['stock_num'] = $inv_lof_result[$k]['stock_num'] - $sub_info['num'];
                }
                if ($sub_info['occupy_type'] == 4) {
                    $inv_lof_result[$k]['lock_num'] = $inv_lof_result[$k]['lock_num'] - $sub_info['num'];
                    $inv_lof_result[$k]['lock_num'] = $inv_lof_result[$k]['lock_num'] < 0 ? 0 : $inv_lof_result[$k]['lock_num'];
                }
            }
        }
    }

    /**
     * 调整【批次库存表】的日志
     */
    public function adjust_lof_record() {
        $record_info = &$this->check_data['record_info'];
        $record_info_new = &$this->lof_record_data['record_info_new'];
        $record_info_old = &$this->lof_record_data['record_info_old'];
        $record_data = array();

        foreach ($record_info as $key => $info) {
            $record_data[$key] = $info;
            if ($this->occupy_type <> 1) {
                if (isset($info['stock_num'])) {
                    $record_data[$key]['stock_change_num'] = abs($info['stock_num']); //订单扣减库存
                    unset($record_data[$key]['stock_num']);
                } else {
                    $record_data[$key]['stock_change_num'] = 0;
                }
            }
            if ($this->occupy_type <> 1) {
                if (isset($info['lock_num']) && $this->is_has_lock == 1) {
                    $record_data[$key]['lock_change_num'] = abs($info['lock_num']); //订单扣减库存
                    unset($record_data[$key]['lock_num']);
                } else {
                    $record_data[$key]['lock_change_num'] = 0;
                }
            } else {
                $record_data[$key]['lock_change_num'] = isset($info['lock_num'])? abs($info['lock_num']):$info['num']; //锁定增加
            }

            $record_data[$key]['stock_lof_num_before_change'] = isset($record_info_old[$key]['stock_lof_num']) ? $record_info_old[$key]['stock_lof_num'] : 0;
            $record_data[$key]['lock_lof_num_before_change'] = isset($record_info_old[$key]['lock_lof_num']) ? $record_info_old[$key]['lock_lof_num'] : 0;

            $record_data[$key]['stock_num_before_change'] = isset($record_info_old[$key]['stock_num']) ? $record_info_old[$key]['stock_num'] : 0;
            $record_data[$key]['lock_num_before_change'] = isset($record_info_old[$key]['lock_num']) ? $record_info_old[$key]['lock_num'] : 0;


            $record_data[$key]['stock_lof_num_after_change'] = $record_info_new[$key]['stock_lof_num'];
            $record_data[$key]['lock_lof_num_after_change'] = $record_info_new[$key]['lock_lof_num'];

            $record_data[$key]['stock_num_after_change'] = $record_info_new[$key]['stock_num'];
            $record_data[$key]['lock_num_after_change'] = $record_info_new[$key]['lock_num'];

            if ($this->occupy_type <> 1) {
                $num = isset($info['stock_num']) ? $info['stock_num'] : $info['lock_num'];
            } else {
                $num = $info['lock_num'];
            }
            $record_data[$key]['remark'] = $this->get_num_msg($num);
            $record_data[$key]['relation_type'] = $this->order_type;
            $record_data[$key]['relation_code'] = $this->order_code;
            $record_data[$key]['record_time'] = $this->sys_record_time; //业务执行时间
            $record_data[$key]['occupy_type'] = $this->occupy_type;
            if(isset( $record_data[$key]['lastchanged'])){
                unset( $record_data[$key]['lastchanged']);
            }
        }
        
        $ret = $this->insert_multi_exp('goods_inv_record', $record_data);
        if ($ret['status'] < 0) {
            return $this->format_ret(-1, '', 'INV_LOG_ERROR');
        }
    }

    private function get_num_msg($num) {
        if ($this->occupy_type == 1) {
            return lang('INV_LOCK_ADD');
        } else if ($this->occupy_type == 0) {
            return lang('INV_LOCK_MINUS');
        } else {
            if ($num > 0) {
                return lang('INV_STOCK_ADD');
            } else {
                return lang('INV_STOCK_MINUS');
            }
        }
    }

    private function set_record_date() {
        $record_type_date_arr = array(
            'adjust' => 'select record_time as record_date from stm_stock_adjust_record  where record_code=:record_code',
//            'purchase' => 'select record_time as record_date from pur_purchaser_record  where record_code=:record_code',
//            'pur_return' => 'select record_time as record_date from pur_return_record  where record_code=:record_code',
//            'shift_out' => 'select record_time  from stm_store_shift_record  where record_code=:record_code', //1
//            'shift_in' => 'select shift_in_time AS record_time from stm_store_shift_record  where record_code=:record_code', //1
//            'wbm_store_out' => 'select record_time as record_date from wbm_store_out_record  where record_code=:record_code',
//            'wbm_return' => 'select record_time from wbm_return_record  where record_code=:record_code', //1
        );
        // $this->order_date = date('');
        if (($this->occupy_type == 3 || $this->occupy_type == 2) && isset($record_type_date_arr[$this->order_type])) {  //更改库存
            $sql = $record_type_date_arr[$this->order_type];
            $row = $this->db->get_row($sql, array(':record_code' => $this->order_code));
            if (isset($row['record_date'])) {
                $this->order_date = $row['record_date'];
            } else {
                $this->order_date = date('Y-m-d', strtotime($row['record_time']));
            }
        }
    }
}
