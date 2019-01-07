<?php
require_model('tb/TbModel');
class OrderTagModel extends TbModel{
    protected $table = "api_order_tag";
    public $tag_names = array('prime' =>'售后无忧', 'etShopName'=>'预约门店名称','etSerTime'=>'预约时间','etPlateNumber'=>'车牌号码','shShip' =>'屏蔽发货','imei_required'=>'强制IMEI');
    
    
    function get_all_source_tag(){
       
        $sql = "select source,tag_name from {$this->table} group by source,tag_name";
        $data = $this->db->get_all($sql);
        $trade_tag = array();
        $sale_channel_codes = load_model('base/SaleChannelModel')->get_data_map();
        foreach ($data as $row) {
        	$key = $row['source']."/".$row['tag_name'];
        	$tag_name = $row['tag_name'];
        	if (isset($this->tag_names[$row['tag_name']])){
        		$tag_name = $this->tag_names[$row['tag_name']];
        	}
        	$trade_tag[$key] = $sale_channel_codes[$row['source']]."/".$tag_name;
        }
       return $trade_tag;
    }
 
}
