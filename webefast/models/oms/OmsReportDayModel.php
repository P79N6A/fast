<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);


class OmsReportDayModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_report_day';
    private $report_data = array();
    private $report_date ;   
    private  $record_time;

      /*
     * 获取数据
     */
    function get_report_data($date,$type_arr = array()){
     
//        $ret = $this->get_all(array('record_date'=>$date));
        $sql = "select * from {$this->table} where record_date=:record_date ";
        $sql_value = array('record_date'=>$date);
        if(!empty($type_arr)){
            $type_str = "'".implode("','", $type_arr)."'";
            $sql.=" AND  type in({$type_str})";
        }

        $data = $this->db->get_all($sql,$sql_value);

        if(empty($data)){
            $this->create_data_by_date($date,false);
            $ret = $this->format_ret(1, array_values($this->report_data));
        }else{
             $ret = $this->format_ret(1,$data);
        }
        return $ret;
    }
    
    /*
     * 检测数据是否需要查找
     */
    private function check_data($date,&$report_data_conf){
         $ret = $this->get_all(array('record_date'=>$date));
         foreach($ret['data'] as $val){
              if(!in_array($val['type'], array( 'sell_num','sell_money'))){
                $this->report_data[$val['type']] = $val;
              }
         }

         if(!empty($this->report_data)){
             $sell_num = $this->report_data['sell_num']['report_data'];
             $check_key = array('wait_confirm','wait_create_waves','wait_scan','oms_send');
             foreach($check_key as $key){
                 if(isset($this->report_data[$key]['report_data']) && $this->report_data[$key]['report_data']==$sell_num){
                     unset($report_data_conf[$key]);
                 }
             }
         }
    }
    
    function create_data(){

        $date_arr = $this->get_date(2);
        foreach($date_arr as $date){
           $this->create_data_by_date($date);
        }
        
    }
    
    function get_date($day = 3){
         $date_arr = array();
        for($i = $day-1;$i>0;$i--){
            $date_arr[] = date('Y-m-d',strtotime("-{$i} days"));
        }

        $date_arr[] = date('Y-m-d'); 
        return $date_arr ;
    }
    

    /*
     * 创建报表数据
     */        
    function create_data_by_date($date,$is_check = true){
        $this->report_date = $date;
        $this->record_time = time();

        if($is_check===TRUE){
            $now_date =  date('Y-m-d',$this->record_time); 
            $check_time = strtotime($now_date." 1:00:00");
            //昨天
            if($now_date!=$this->report_date&&$this->record_time>$check_time){
                return FALSE;
            }
        }
               

        $this->report_data = array();
      //获取成交量，成交金额 
       $sell_data = $this->create_api_sell(); 
       $this->set_report_data($sell_data,array('sell_num','sell_money'));//组装数据格式

       
        $report_data_conf = require_conf('oms/oms_report_day');
        if($is_check){
            $this->check_data($date,$report_data_conf);
        }
        foreach($report_data_conf as $key=>$conf){
           $key_arr = isset($conf['key'])?$conf['key']:array($key);
           $data = $this->create_sell($conf, $key);

            $this->set_report_data($data,$key_arr);
        }

        
        $update_str = " report_data = VALUES(report_data),record_time = VALUES(record_time)";

        $this->insert_multi_duplicate($this->table, $this->report_data, $update_str);

    }
    
    /**
     * 查找交易数，交易金额
     * @return type
     */
    function create_api_sell() {
        //店铺权限
        $shop = load_model('base/ShopModel')->get_purview_shop();
        $shop_code_arr = array_column($shop, 'shop_code');
        $filer = array();
        $this->get_filer($filer);
        //时间检索
        $start = $filer['pay_time_start'];
        $end = $filer['pay_time_end'];
        $order_info = array();
        foreach ($shop_code_arr as $shop_code) {
            $where_time = "BETWEEN '{$start}' AND '{$end}'";
            $api_model = load_model('oms/ApiOrderModel');
            /** 获取交易总笔数['total_order' => '', 'total_money' => ''] */
            $order_info[] = $api_model->getTotalOrderNum($where_time, $shop_code);
        }
        return array(
            'sell_num' => array_sum(array_column($order_info, 'total_order')),//成交量
            'sell_money' => array_sum(array_column($order_info, 'total_money'))//成交金额
        );
    }

    /*
     * 设置数据
     */   
    function set_report_data($data,$key_arr){

        foreach($key_arr as $key){
            $val_data = isset($data[$key])&&!empty($data[$key])?$data[$key]:0;
            $this->report_data[$key] = array('type'=>$key,'report_data'=>$val_data,'record_date'=>$this->report_date,'record_time'=>$this->record_time); 
       
        }

    }
        
    /*
     * 创建销售数据
     */   
    function create_sell($conf,$key){
        $select = &$conf['select'];
        $filer = &$conf['filer'];
        $sql_values=array();
        $sql = "SELECT  {$select} FROM oms_sell_record WHERE 1 ";
        //店铺权限
        $shop = load_model('base/ShopModel')->get_purview_shop();
        $shop_code_arr = array_column($shop, 'shop_code');
        if(empty($shop_code_arr)){
           $sql.=" AND 1=2 "; 
        }else{
           $str = $this->arr_to_in_sql_value($shop_code_arr, 'shop_code', $sql_values);   
           $sql .=" AND shop_code IN ({$str})";
        }
        //获取时间
        $this->get_filer($filer);
           if(isset($filer['shipping_status'])&&$filer['shipping_status']==4){
                $sql.= " AND  delivery_time>=:delivery_time_start AND delivery_time<=:delivery_time_end  ";
                $sql_values[':delivery_time_start'] =  $filer['pay_time_start'];
                $sql_values[':delivery_time_end'] = $filer['pay_time_end'];
        }

        //待确认订单
        if ($key == 'wait_confirm') {
            $sql.=" AND (`pay_type` = 'cod' OR `pay_status` = '2') ";
        }
        if (isset($filer['order_status'])) {
            $sql.= " AND  order_status=:order_status ";
            $sql_values[':order_status'] = $filer['order_status'];
        }
        if(isset($filer['shipping_status'])){
            $sql.= " AND  shipping_status=:shipping_status ";
            $sql_values[':shipping_status'] =  $filer['shipping_status'];
        }
       if(isset($filer['waves_record_id'])){
            $sql.= " AND  waves_record_id=:waves_record_id ";
            $sql_values[':waves_record_id'] =  $filer['waves_record_id'];
        }  
        
        if(isset($conf['where'])){
             $sql.= " AND ".$conf['where'];
        }

       return $this->db->get_row($sql,$sql_values);
        
    }
    
    private function get_filer(&$filer){
        $filer['pay_time_start'] =  $this->report_date." 0:00:00";
        $filer['pay_time_end'] =  $this->report_date." 23:59:59";
    }

    
    function get_sell_goods_data(){
        
    }
    
    function get_sell_money_data(){
        
    }
    
}

