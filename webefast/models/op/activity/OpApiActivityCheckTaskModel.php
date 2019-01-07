<?php

require_model('tb/TbModel');
require_lib('apiclient/TmallPageClient', false);

class OpApiActivityCheckTaskModel extends TbModel {

    private $fail_num = 0;

    function __construct() {
        parent::__construct('op_api_activity_check_task');
    }

    function get_by_page($filter) {
        parent::get_by_page($filter);
    }

    function insert($data) {
        parent::insert($data);
    }

    function delete($where) {
        return parent::delete($where);
    }

    function check_goods($check_sn, $shop_code) {
        $ret = load_model('op/activity/OpApiActivityGoodsModel')->get_goods_by_shop_code($shop_code);
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '未找到对应商品');
        }
        $bacode_arr = array();
        foreach ($ret['data'] as $val) {
            $bacode_arr[] = $val['barcode'];
        }
        $this->get_api_activity_goods($check_sn, $shop_code, $bacode_arr);
        $this->check_check($check_sn, $shop_code);
        //任务完成打标
    }

    private function get_api_activity_goods($check_sn, $shop_code, $bacode_arr) {

        $barcode_str = "'" . implode("','", $bacode_arr) . "'";
        $insert_sql = " INSERT  INTO op_api_activity_check_goods (check_sn,shop_code,barcode,api_item_id,api_sku_id,inv_num) ";
        $sql_select = " select '{$check_sn}' AS check_sn,shop_code,goods_barcode as barcode,goods_from_id as api_item_id,sku_id as api_sku_id,num as inv_num from api_goods_sku where status=1 AND shop_code='{$shop_code}' AND goods_barcode in({$barcode_str})";
        $update_str = " ON DUPLICATE KEY UPDATE  api_sku_id= VALUES(api_sku_id),api_sku_id= VALUES(api_sku_id),inv_num= VALUES(inv_num) ";
        $sql = $insert_sql . $sql_select . $update_str;
        $this->db->query($sql);
        $this->update_goods_sku($check_sn, $shop_code);
    }

    private function update_goods_sku($check_sn, $shop_code) {
        $sql = " select api_item_id from op_api_activity_check_goods where shop_code=:shop_code AND check_sn=:check_sn
                    and api_item_id is not null
                     GROUP BY api_item_id ";
        $data = $this->db->get_all($sql, array(':shop_code' => $shop_code, ':check_sn' => $check_sn));
        $this->fail_num = 0;
        $this->update_item_info($data, $check_sn, $shop_code);
    }

    private function update_item_info($data, $check_sn, $shop_code) {

        $tmall = new TmallPageClient();
        foreach ($data as $key => $val) {
            $item_data = $tmall->get_item_page_info($val['api_item_id']);
            if (!empty($item_data)) {
                $this->update_api_item_info($check_sn, $shop_code, $item_data);
                unset($data[$key]);
            }
        }

        if (!empty($data) && $this->fail_num < 10) {
            sleep(30);
            $this->fail_num = $this->fail_num + 1;
            $this->update_item_info($data, $check_sn, $shop_code);
        }
    }

    private function update_api_item_info($check_sn, $shop_code, $item_data) {
        $sku_id_arr = array();
        foreach ($item_data['defaultModel']['inventoryDO']['skuQuantity'] as $sku_id => $val) {
            $sku_id_arr[$sku_id] = array(
                'check_sn' => $check_sn,
                'shop_code' => $shop_code,
                'api_sku_id' => $sku_id,
            );
            $sku_id_arr[$sku_id]['inv_num'] = $val['totalQuantity'];
        }

        foreach ($item_data['defaultModel']['itemPriceResultDO']['priceInfo'] as $sku_id => $pval) {
            if (isset($pval['promotionList'])) {
                $sku_id_arr[$sku_id]['sale_price'] = $this->get_item_price($pval['promotionList']);
            }
        }
        if (!empty($sku_id_arr)) {
            $update_str = " inv_num= VALUES(inv_num),sale_price= VALUES(sale_price) ";
            $this->insert_multi_duplicate('op_api_activity_check_goods', $sku_id_arr, $update_str);
        }
    }

    private function get_item_price($promotionList) {
        $price = null;
        foreach ($promotionList as $val) {
            //双十一活动时间判断  双十一前需要确认 
            if (isset($val['startTime']) && $val['startTime'] == '1478793600000') {
                $price = $val['price'];
                break;
            }
        }
        return $price;
    }

    function create_check_task() {
        $sql = "select * from op_api_activity_check_task  order by id desc ";
        $data = $this->db->get_row($sql);
        if (!empty($data)) {
            if (isset($data['shop_code_list'])) {
                $shop_code_list = json_decode($data['shop_code_list'], true);
                $ret = $this->check_task($shop_code_list);
            }
            $this->db->update('op_api_activity_check_task', array('status'=>1)," id ='{$data['id']}' ");
            
        } else {
            $ret = $this->format_ret(1, '', '无可检查任务！');
        }
        return $ret;
    }

    function check_task($shop_code_list) {
        require_model('common/TaskModel');
        $task = new TaskModel();

        $task_data = array();

        $task_code = 'op_api_check_task';
        $request['app_act'] = 'op/op_api_activity_check/check_goods';
        $request['check_sn'] = date('Ymd');
        $request['app_fmt'] = 'json';

        foreach ($shop_code_list as $shop_code) {
            $request['shop_code'] = $shop_code;
            $task_data['code']  =  $task_code."_".$shop_code;
            $task_data['request'] = $request;
            $ret = $task->save_task($task_data);
   
            $process_task['check_sn'] = $request['check_sn'];
            $process_task['shop_code'] = $shop_code;
            $process_task['status'] = 1;
            $process_task['start_time'] = time();
            $process_task['barcode_num'] = 0;
            $process_task['inv_num'] = 0;
            $process_task['sale_price_num'] = 0;
            $process_task['sys_task_id'] = $ret['data'];
            $this->insert_exp('op_api_activity_check_process', $process_task);

        }
        return $this->format_ret(1);
    }



    function check_check($check_sn, $shop) {
        $sql_check_num = "select count(1) from op_api_activity_goods rl inner join op_api_activity_check_goods rr on rl.barcode=rr.barcode where rl.inv_num!=rr.inv_num and rr.check_sn= :check_sn and rl.shop_code= :shop_code";
        $sql_check_price = "select count(1) from op_api_activity_goods rl inner join op_api_activity_check_goods rr on rl.barcode=rr.barcode where rl.sale_price!=rr.sale_price and rr.check_sn=:check_sn and rl.shop_code=:shop_code";
        

        
        $data_num = $this->db->get_value($sql_check_num,array(':check_sn'=>$check_sn,':shop_code'=>$shop));
        $data_price = $this->db->get_value($sql_check_price,array(':check_sn'=>$check_sn,':shop_code'=>$shop));
//        $update_task = "update op_api_activity_check_process set inv_num={$data_num},status=2,sale_price_num={$data_price} where check_sn='{$check_sn['check_sn']}' and shop_code='{$shop['shop_code']}'";
        
        
        $ret = parent::update_exp('op_api_activity_check_process',array('inv_num'=>$data_num,'status'=>2,'sale_price_num'=>$data_price),array('check_sn'=>$check_sn,'shop_code'=>$shop));
//        $this->db->query($update_task);
    }

    function check_task_list() {
        $sql = 'select * from op_api_activity_check_process';
        $data = $this->db->getAll($sql);
        foreach ($data as $key => $value) {
            $arr["{$value['check_sn']}"]["check_sn"] = $value['check_sn'];
            $arr["{$value['check_sn']}"]["status"] = $value['status'];
            if ($value['end_time'] == 0) {
                $arr["{$value['check_sn']}"]["end_time"] = 0;
            } else {
                $arr["{$value['check_sn']}"]["end_time"] = date("Y-m-d H:i:s", $value['end_time']);
            }
            $arr["{$value['check_sn']}"]["start_time"] = date("Y-m-d H:i:s", $value['start_time']);
            $shop = load_model('base/ShopModel')->get_by_code($value['shop_code']);
            //print_r($shop);
            $arr["{$value['check_sn']}"]['data'][$key]["shop_code"] = $shop['data']['shop_name'];
            $arr["{$value['check_sn']}"]['data'][$key]["barcode_num"] = $value['barcode_num'];
            $arr["{$value['check_sn']}"]['data'][$key]["inv_num"] = $value['inv_num'];
            $arr["{$value['check_sn']}"]['data'][$key]["sale_price_num"] = $value['sale_price_num'];
        }
        //print_r($arr);
        return $arr;
    }

}
