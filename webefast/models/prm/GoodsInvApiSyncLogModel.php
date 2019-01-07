<?php
require_model('tb/TbModel');

class GoodsInvApiSyncLogModel extends TbModel {
    function __construct() {
        parent::__construct('goods_inv_api_sync_log');
    }
    
    function save_log($data){
        return $this->insert_multi($data);
        
    }
    
    
}
?>
