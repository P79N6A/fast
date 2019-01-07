<?php

require_lib('util/web_util', true);

class goods_performance {

    public function do_list(array & $request, array & $response, array & $app)
    {

    }
    
    public function sync_delivery_person(array & $request, array & $response, array & $app)
    {
        /*
        $sql = "SELECT `sell_record_code` "
             . "FROM `oms_sell_record` "
             . "WHERE `order_status` = '1' AND `shipping_status` = '4' "
             . "AND `delivery_person` = '' LIMIT 1000";*/
        while (1) {
            $sql = "SELECT osr.sell_record_code from  `oms_sell_record` AS `osr`, `oms_sell_record_action` AS `osra` 
                WHERE `osr`.`sell_record_code` = `osra`.`sell_record_code` 
                AND `osr`.`delivery_person` = '' 
                AND `osr`.`order_status` = '1' 
                AND `osr`.`shipping_status` = '4' 
                AND `osra`.`action_name` = '扫描发货' ORDER BY delivery_time desc limit 1000";
              $find = ctx()->db->getAll($sql);
             if(empty($find)){
                 break;
             }   
            $in = "('".implode("','", array_column($find, 'sell_record_code'))."')";
            $sql2 = "UPDATE `oms_sell_record` AS `osr`, `oms_sell_record_action` AS `osra` "
                  . "SET `osr`.`delivery_person` = `osra`.`user_code` "
                  . "WHERE `osr`.`sell_record_code` = `osra`.`sell_record_code` "
                  . "AND `osr`.`delivery_person` = '' "
                  . "AND `osr`.`order_status` = '1' "
                  . "AND `osr`.`shipping_status` = '4' "
                  . "AND `osra`.`action_name` = '扫描发货' "
                  . "AND `osr`.`sell_record_code` IN {$in}";
            ctx()->db->query($sql2);
            
        }
        $response['status'] = 1;
    }
}
