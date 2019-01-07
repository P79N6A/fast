<?php

require_model('tb/TbModel');
require_lib('util/oms_util', true);

class OmsReportDaySkuModel extends TbModel {

    /**
     * @var string 表名
     */
    protected $table = 'oms_report_day_sku';


    /*
     * 获取数据
     */

    function get_report_data($day,$init = 0) {
        $date = date('Y-m-d');
        $sql = "select * from oms_report_day_sku WHERE type =:type AND record_date=:record_date  order by num desc";
        $sql_values = array(':type'=>$day,':record_date'=>$date);
        $data =  $this->db->get_all($sql,$sql_values);
        if(empty($data)&&$init==0){
            $this->create_data();
            return $this->get_report_data($day,1);
        }else{
            $spec_data = load_model('sys/SysParamsModel')->get_val_by_code(array('goods_spec1','goods_spec2'));
            foreach($data as &$val){
               $sku_info = $this->get_goods_info_by_sku($val['sku']);
               $val = array_merge($sku_info,$val);
               $val['spec'] = $spec_data['goods_spec1'].":".$val['spec1_name'].','.$spec_data['goods_spec2'].":".$val['spec2_name'];
            }
        }
        return $this->format_ret(1,$data);
    }
    
    private function get_goods_info_by_sku($sku){
//        $sql = "select b.barcode,g.goods_name,s1.spec1_name,s2.spec2_name from"
//                . " goods_barcode b  "
//                . " INNER JOIN base_goods g ON b.goods_code AND g.goods_code"
//                . "  INNER JOIN base_spec1 s1 ON b.spec1_code AND s1.spec1_code"
//                . " INNER JOIN base_spec2 s2 ON b.spec2_code AND s2.spec2_code "
//                . "  where b.sku=:sku " ;
//  return $this->db->get_row($sql,array(':sku'=>$sku));
      //   b.barcode,g.goods_name,s1.spec1_name,s2.spec2_name
       $key_arr = array('barcode','goods_name','spec1_name','spec2_name' );
        $sku_info =  load_model('goods/SkuCModel')->get_sku_info($sku,$key_arr);
 
        return $sku_info;
        

        
    }

    function create_data() {
        
        $this->set_sell_sku_data(7);
        $this->set_sell_sku_data(30);
    }

    function set_sell_sku_data($day = 7) {
        $pre_day = $day ;
        $pay_time_start = date('Y-m-d', strtotime("-$pre_day days")) . " 0:00:00";
        $pay_time_end = date('Y-m-d') . " 0:00:00";
        $date = date('Y-m-d');

        $sql = "select sum(d.num) as num ,d.sku,'{$date}' as record_date,'{$day}' as type from oms_sell_record r "
                . " INNER JOIN oms_sell_record_detail d ON r.sell_record_code=d.sell_record_code "
                . " WHERE r.order_status<>3  ";

        $sql.=" AND ( (r.pay_status=2 AND r.pay_time>=:pay_time_start AND  r.pay_time<:pay_time_end  ) OR ";
        $sql.="  (r.pay_type='cod' AND r.record_time>=:pay_time_start AND  r.record_time<:pay_time_end  ) )  ";
        $sql.="   GROUP BY d.sku  HAVING num=SUM(d.num) order by num desc  limit 10 ";
        $sql_values = array('pay_time_start'=>$pay_time_start,':pay_time_end'=>$pay_time_end);
       // var_dump($sql,$sql_values);die;
        $data = $this->db->get_all($sql,$sql_values);
        if(!empty($data)){
            $update_str = " num = VALUES(num) ";
            $this->insert_multi_duplicate($this->table, $data, $update_str);
        }
        
    }
    
    
    

}
