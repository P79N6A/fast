<?php

require_model('tb/TbModel');

class GiftStrategyOpModel extends TbModel {

    static $gift_strategy = null;

    function set_trade_gift(&$sell_record, &$sell_record_detail, &$trade_detail) {
        $this->set_version_strategy();
        return self::$gift_strategy->action_strategy($sell_record, $sell_record_detail, $trade_detail);
    }

    private function set_version_strategy() {

//          if(empty(self::$gift_strategy)){
//             $product_version_no = load_model('sys/SysAuthModel')->product_version_no();
//             if($product_version_no==0){
//                 self::$gift_strategy = load_model('op/GiftStrategy/GiftStrategyStandardModel');
//             }else{
        self::$gift_strategy = load_model('op/GiftStrategy/GiftStrategyEnterpriseModel');
//             }
//          }
    }

    public function get_strategy_log() {
        return self::$gift_strategy->get_strategy_log();
    }

    public function save_strategy_log() {

        $log_data = $this->get_strategy_log();

        if (!empty($log_data)) {
           // load_model("op/StrategyLogModel")->insert_multi($log_data);
            $this->insert_multi_exp('op_gift_strategy_log', $log_data);
            
        }
    }

    public function is_combine_send() {
        
    }

}
