<?php

/**
 * 商品库存帐管理相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('inv');

class InvLogModel extends TbModel {
    protected $record_log = array();
    protected $log_info = array();
    protected $record_detail = array();
    private  $condition_where = '';
    private $num_key = 'num';
            
    function get_table() {
        return 'goods_inv_record';
    }
    
    
    function init($log_info, $record_detail,$num_key='num'){
        $this->record_detail = $record_detail;
        $this->log_info = &$log_info;
        $this->num_key = $num_key;
        $this->init_condition_info();
        $this->set_inv_info(0);
       // $log_type = array('relation_code','relation_type','occupy_type','remark');
        //$detail_type = array('goods_code','spec1_code','spec2_code','sku','lof_no','production_date');

    }
    function set_inv_info($type = 0){

        if($type==0){
            $select = " l.goods_code,l.spec1_code,l.spec2_code,l.sku,lof_no,l.production_date,l.store_code,l.stock_num as  stock_lof_num_before_change,l.lock_num as  lock_lof_num_before_change";
            $select .= ",i.stock_num as  stock_num_before_change,i.lock_num as  lock_num_before_change";
        }else{
            $select = " l.goods_code,l.spec1_code,l.spec2_code,l.sku,lof_no,l.production_date,l.store_code,l.stock_num as  stock_lof_num_after_change,l.lock_num as  lock_lof_num_after_change";
            $select .= ",i.stock_num as  stock_num_after_change,i.lock_num as  lock_num_after_change";  
        }
        $sql = " select {$select}  from goods_inv i "
                . " INNER JOIN goods_inv_lof l ON i.sku=l.sku AND i.store_code=l.store_code"
                . " WHERE ".$this->condition_where;
        
        $data = $this->db->get_all($sql);
        
        foreach($data as $val){
            $key =  $this->get_key($val);
            $this->record_log[$key] = array_merge($this->record_log[$key],$val);
        }
        
    }
    
    function set_change_record($record_detail,$num_key='num'){
        $this->set_inv_info(1);
        foreach ($record_detail as $val){
             $this->set_change_record_detail($val,$num_key);
        }
        $this->save_log();
    }
    
    function set_log(){
        $this->set_inv_info(1);
        $this->save_log();
    }
    
    
    function set_change_record_detail($detail,$num_key){
        $key =  $this->get_key($detail);
        if($this->log_info['occupy_type']<2){
                $this->record_log[$key]['lock_change_num'] = $detail[$num_key] ;
        }else if($this->log_info['occupy_type']==2){
                $this->record_log[$key]['lock_change_num'] = $detail[$num_key] ; 
                $this->record_log[$key]['stock_change_num'] = $detail[$num_key] ;
        }else if($this->log_info['occupy_type']==3){
                $this->record_log[$key]['stock_change_num'] = $detail[$num_key] ;
                if ($detail[$num_key] > 0) {
                    $this->record_log[$key]['remark'] = lang('INV_STOCK_ADD');
                } else {
                    $this->record_log[$key]['remark'] = lang('INV_STOCK_MINUS');
                }
        }
 
        
    }
    
    
    function save_log(){
        return $this->insert_multi_exp('goods_inv_record',  $this->record_log);
    }
    
    function init_condition_info(){
        $where_arr =  array();
        $key_arr = array('sku','lof_no','production_date','store_code');
        $init_log = array('stock_lof_num_before_change'=>0,'lock_lof_num_before_change'=>0,
            'stock_num_after_change'=>0,'lock_num_after_change'=>0,
            'lock_change_num'=>0,'stock_change_num'=>0);
    
        $init_log = array_merge($this->log_info, $init_log);
        
        $remark_arr = array('0'=>lang('INV_LOCK_MINUS'),'1'=>lang('INV_LOCK_ADD'),'2'=>lang('INV_STOCK_MINUS'),'3'=>lang('INV_STOCK_ADD'));
      
        $init_log['remark'] = empty( $init_log['remark'])? $remark_arr[$init_log['occupy_type']]:$init_log['remark'];
        
        
        
        foreach ($this->record_detail as $sub_info){
            $sub_where = array();
            $info_key =  $this->get_key($sub_info);
            $this->record_log[$info_key] = $init_log;
            
            foreach($key_arr as $key){
                $sub_where[] = " l.{$key} = '{$sub_info[$key]}' ";
                
                $this->record_log[$info_key][$key] = $sub_info[$key];
                $this->set_change_record_detail($sub_info,$this->num_key);
                
            }
            $where_arr[] = "(".implode(' AND  ', $sub_where).")";
        }
        $this->condition_where = implode(' OR ', $where_arr);
      
        
    }
    

    function get_key($info){
        return $info['store_code'].'_'.$info['sku'].'_'.$info['lof_no'].'_'.$info['production_date'];
    }

       
    
    
}