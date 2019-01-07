<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('oms');
class SellRecordLofModel extends TbModel {
    /**
     * @var string 表名
     */
    protected $table = 'oms_sell_record_lof';
    public $sell_record_info;
    public $sell_record_mx_info;

    function get_list_by_params($params,$is_td=false){
        $data = $this->get_all($params);
      //  filter_fk_name($data['data'], array('spec1_code|spec1_code','spec2_code|spec2_code','sku|barcode'));

            $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();
           // $sys_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('default_lof_no', 'default_lof_production_date'));
            $sys_cfg = $ret_lof['data'];
            foreach($data['data'] as $key=>&$val){
                $key_arr = array('goods_name','spec1_code','spec1_name','spec2_code','spec2_name','barcode');
                $sku_info =  load_model('goods/SkuCModel')->get_sku_info($val['sku'],$key_arr);
                $val = array_merge($val,$sku_info);
                $data['data'][$key] = $val;
                
                if($is_td){
                    $info = load_model("prm/GoodsModel")->get_by_goods_code($val['goods_code']);
                    if($sys_cfg['lof_no'] == $val['lof_no']){
                        $val['lof_no'] = '默认批次号';
                    }
                    if($sys_cfg['production_date'] == $val['production_date']){
                        $val['lof_no'] = '默认生产日期';
                    }
                    if($info['status']=='1'){
                        $val['goods_name'] = $info['data']['goods_name'];
                    }else{
                        $val['goods_name'] = '';
                    }
                    //根据sku和批次号获取实物库存
                    $val['lof_inv_num'] = load_model("prm/InvLofModel")->get_lof_inv_num(array('sku'=>$val['sku'],'lof_no'=>$val['lof_no']));
                }
            }

        
        
        
        return $data;
    }

    function get_sell_record_info($order_sn){
        if(!isset($this->sell_record_info[$order_sn])){
            $this->sell_record_info[$order_sn] = load_model("oms/SellRecordModel")->get_record_by_id($order_sn);
        }
    }

    function get_sell_record_mx_info($order_sn){
        if(!isset($this->sell_record_mx_info[$order_sn])){
            $this->sell_record_mx_info[$order_sn] = load_model("oms/SellRecordModel")->get_record_by_id($order_sn);
        }
    }

    function mx_kc_info($order_sn){
        $store_code = $info['store_code'];
        $info = load_model("oms/SellRecordModel")->get_detail_by_id($order_sn);
        $kc_info = array();
        foreach($info as $sub_info){
            $kc_info[] = array(
                'store_code'=>$store_code,
                'goods_code'=>$sub_info['goods_code'],
                'spec1_code'=>$sub_info['spec1_code'],
                'spec2_code'=>$sub_info['spec2_code'],
                'num'=>(int)$sub_info['num']-(int)$sub_info['lock_num']);
        }
        return $kc_info;
    }
    //锁定库存
    function lock_lof($order_sn){
        $kc_info = $this->mx_kc_info($order_sn);
        $log = array();
        $log['order_type'] = 'order_info';
        $log['order_sn'] = $order_sn;
        $log['desc'] = '锁定库存';
        $ret = load_model("wrm/InventoryLockModel")->add($kc_info);
        if($ret['status']<=0){
            return $ret;
        }
        return true;
    }
    //扣减库存
    function deduction_inv($record_code, $store_code, $order_date, $force_negative_inv = 0) {
        $ret = $this->get_all(array('record_code' => $record_code,'record_type' => 1));
        if ($ret['status'] > 0) {
            $invobj = new InvOpModel($record_code, 'oms', $store_code, 2, $ret['data']);
            if ($force_negative_inv == 1) {
                $invobj->force_negative_inv(); //强制允许负库存
            }
            $invobj->order_date = $order_date;
            $ret = $invobj->adjust();
        }
        return $ret;
    }

    //释放锁定
    function reduce_lof($order_sn){
        $kc_info = $this->mx_kc_info($order_sn);
        $log = array();
        $log['order_type'] = 'order_info';
        $log['order_sn'] = $order_sn;
        $log['desc'] = '释放锁定';
        $ret = load_model("wrm/InventoryLockModel")->reduce($kc_info);
        if($ret['status']<=0){
            return $ret;
        }
        return true;
    }
    
	/**
     * @todo 根据单据编号和sku更新单据库存数量
     */
    function update_num_by_code_sku($record_code,$sku,$num){
        $data = array('num' => $num);
        $where = array('record_code' => $record_code, 'sku' => $sku);
        $ret = parent::update($data, $where);
        return $ret;
    }
    
    //根据单据编号添加库存
    function create_num_by_code_sku($record_code, $sku, $num) {
        $sql = "SELECT * FROM oms_sell_return WHERE sell_return_package_code = '{$record_code}'";
        $return_arr = $this->db->get_row($sql);
        
        $sql = "SELECT * FROM goods_sku WHERE sku = '{$sku}'";
        $sku_arr = $this->db->get_row($sql);
        
        $sql = "SELECT r2.lof_no,r2.production_date FROM oms_sell_record rl LEFT JOIN oms_sell_record_lof r2 ON rl.sell_record_code = r2.record_code WHERE rl.sell_record_code = '{$return_arr['sell_record_code']}' AND r2.sku = '{$sku}'";
        $lof_arr = $this->db->get_row($sql);
        if  (empty($lof_arr)) {
            $moren = load_model('prm/GoodsLofModel')->get_sys_lof();
            $lof_arr['lof_no'] = isset($moren['data']['lof_no']) ? $moren['data']['lof_no'] : '';
            $lof_arr['production_date'] = isset($moren['data']['production_date']) ? $moren['data']['production_date'] : '';
        }

        $pack_lof_mx[] = array(
            'record_type' => 2,
            'record_code' => $record_code,
            'deal_code' => $return_arr['deal_code'],
            'store_code' => $return_arr['store_code'],
            'goods_code' => $sku_arr['goods_code'],
            'spec1_code' => $sku_arr['spec1_code'],
            'spec2_code' => $sku_arr['spec2_code'],
            'sku' => $sku,
            'barcode' => $sku_arr['barcode'],
            'lof_no' => $lof_arr['lof_no'],
            'production_date' => $lof_arr['production_date'],
            'num' => $num,
            'stock_date' => date('Y-m-d'),
            'occupy_type' => 0,
            'create_time' => time()
        );
        $update_str = "num = VALUES(num)";
        $ret = M('oms_sell_record_lof')->insert_multi_duplicate('oms_sell_record_lof', $pack_lof_mx, $update_str);
        if ($ret['status'] < 0) {
            return $ret;
        }
        
    }
    
    /**
     * 根据单据号和类型获取批次数据
     * @param string $record_code
     * @param string $record_type
     * @return array 数据集
     */
    function get_by_order_code($record_code, $record_type) {
        $sql = "SELECT id,pid,p_detail_id,record_code,record_type,deal_code,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,num,occupy_type,order_date,create_time FROM {$this->table} WHERE record_code=:record_code AND record_type=:record_type";
        $sql_values = array(':record_code' => $record_code, ':record_type' => $record_type);
        $data = $this->db->get_all($sql, $sql_values);
        return $data;
    }

}
