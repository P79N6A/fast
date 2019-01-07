<?php
require_model('tb/TbModel');
class WmsExpressActionModel extends TbModel {
    public function __construct($table = '')
    {
        $table = $this->get_table();
        parent::__construct($table);
    }
    public function get_table(){
        return 'wms_express_action';
    }
    public function get_by_page($filter){
        $sql_main = 'from '.$this->table.' where wms_id = :wms_id order by lastchanged desc';
        $sql_values = array(':wms_id'=>$filter['wms_id']);
        $select = '*';
        $data = $this->get_page_from_sql($filter,$sql_main,$sql_values,$select);
        return $this->format_ret(1,$data);
    }
}