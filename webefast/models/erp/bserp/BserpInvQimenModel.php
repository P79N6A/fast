<?php

require_model('erp/bserp/BserpQimenBaseModel');

class BserpInvQimenModel extends BserpQimenBaseModel {

//$tb_key
    function __construct($erp_config_id) {
        parent::__construct();
        $this->get_erp_config($erp_config_id);
    }
//taobao.erp.item.inventory.get(OMS实时查询ERP商品库存)
    function erp_inv_get(){
                   
        $this->create_client();              
        $param = [
            'page' => 1,
            'page_size' => 100,
        ];
        while (true) {
            $ret = $this->erp_client->get_item_inventory($param);//获取库存信息
            if ($ret['status'] < 1) {
                break;
            }
            if (!empty($ret['data']['items']['items'])) {
                $data = $this->save_item_inventory_list($ret['data']['items']['items']);//保存库存信息
                $update_str = " sl = VALUES(sl),updated = VALUES(updated)";
                $bserp_quantity_tb = "api_{$this->tb_key}_item_quantity";
                $this->insert_multi_duplicate($bserp_quantity_tb, $data, $update_str);
            } else {
                break;
            }
            if(count($ret['data']['items']['items'])<$param['page_size']){
                break;
            }
            $param['page'] = $param['page'] + 1;
        }
        return $ret;
    }
    //taobao.erp.inventory.lock(OMS生成库存锁定信息，需要同步至ERP锁定商品库存)
    
    /**
     * 库存信息明细
     * @param type $api_data
     * @return type
     */
    function save_item_inventory_list($api_data){
        $erp_config_id = $this->config['erp_config_id'];
        $data = [];
        foreach ($api_data as $val) {
            $row = array(
                    'erp_config_id' => $erp_config_id,
                    'sku' => $val['item']['sku'],
                    'ckdm' => $val['item']['warehouseCode'],
                    'spdm' => $val['item']['itemCode'],
                    'gg1dm' => $val['item']['colorCode'],
                    'gg2dm' => $val['item']['sizeCode'],
                    'sl' => $val['item']['number'],//库存数量
                    'updated' => date('Y-m-d H:i:s'),//下载时间
                );
                $key = $row['spdm'] . $row['gg1dm'] . $row['gg2dm'];
                $data[$key] = $row;
                $sku_arr[] = "  ( goods_code = '{$row['spdm']}' AND spec1_code = '{$row['gg1dm']}' AND spec2_code = '{$row['gg2dm']}' ) ";
        }
        $this->set_sku_data($data, $sku_arr);
        return $data;
    }
    /**
     * 获取系统sku
     * @param type $row
     * @return type
     */
   
     function set_sku_data(&$data, &$sku_arr) {
        $sql = "select sku,goods_code,spec1_code,spec2_code from goods_sku where ";
        $sql .= implode(' OR ', $sku_arr);
        $sku_data = $this->db->get_all($sql);
        foreach ($sku_data as $val) {
            $key = $val['goods_code'] . $val['spec1_code'] . $val['spec2_code'];
            $data[$key]['sku'] = $val['sku'];
        }
    }
    
}
