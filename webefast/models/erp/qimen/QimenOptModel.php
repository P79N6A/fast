<?php

require_model('tb/TbModel');
require_model('erp/bserp/BserpItemQimenModel');

class QimenOptModel extends TbModel {
    protected $sync_goods;
    protected $sync_sku;
    protected $sync_brand;
    protected $sync_category;
    protected $sync_season;
    protected $sync_spec1;
    protected $sync_spec2;
    
    
    function get_erp_item($erp_confg_id) {
        $item_model = new BserpItemQimenModel($erp_confg_id);
        $ret = $item_model->erp_item_get();//获取商品档案

        if($ret['status'] < 0){
            return $this->format_ret('-1','', '获取商品档案失败');
        }
        $this->update_sys_item();
    }
    /**
     * 保存对应的商品信息到系统
     */
    function update_sys_item() {
        while (true) {
           //中间表获取商品信息
            $data = $this->get_sys_item_info();
            $id_arr = array();
            foreach ($data as $val) {
                $id_arr[] = $val['id'];
                $this->set_item_info($val);//设置商品信息
            }
            $this->save_item_info();//保存商品信息
            $up_data = array(
                'sys_updated_time' => date('Y-m-d H:i:s'),//系统更新时间
                'is_updated' => 1,//已更新
            );
            $id_str = "'" . implode("','", $id_arr) . "'";
            $where = " id in( {$id_str}) ";
            $this->update_exp('api_erp_item', $up_data, $where);
            if (count($data) < 1000) {
                break;
            }
        }
        $this->save_other_info();//保存其他信息
    }

    function upload_trade($erp_confg_id) {
        require_model('erp/bserp/BserpTradeQimenModel');
        $item_model = new BserpTradeQimenModel($erp_confg_id);
        $item_model->erp_upload_trade();
    }
    
    function upload_wbm($erp_confg_id) {
        require_model('erp/bserp/BserpWbmQimenModel');
        $item_model = new BserpWbmQimenModel($erp_confg_id);
        $item_model->erp_upload_wbm();
    }

    function exec_erp_cli($type) {
        $data = load_model('erp/bserp/BserpQimenBaseModel')->get_erp_config_id();
        foreach ($data as $val) {
            if (method_exists($this, $type)) {
                $this->$type($val['erp_confg_id']);
            }
        }
    }
    /**
     * 获取未更新的商品信息
     * @param type $limit
     * @return type
     */
    function get_sys_item_info($limit=1000) {
        $sql = "select * from api_erp_item where down_time>sys_updated_time and is_updated = 0 limit {$limit}";
        $data = $this->db->getAll($sql);
        return $data;
    }
    /**
     * 设置商品信息
     * @param type $api_data
     */
    function set_item_info($api_data) {
        $data = $this->conver_info($api_data);//转换同步数据
        if (!empty($data['goods'])) {
            $this->sync_goods[$data['goods']['goods_code']] = $data['goods'];
        }
        if (!empty($data['sku'])) {
            $this->sync_sku[] = $data['sku'];
        }
        if (!empty($data['brand'])) {
            $this->sync_brand[$data['brand']['brand_code']] = $data['brand'];
        }
        if (!empty($data['category'])) {
            $this->sync_category[$data['category']['category_code']] = $data['category'];
        }
        if (!empty($data['season'])) {
            $this->sync_season[$data['season']['season_code']] = $data['season'];
        }
        if (!empty($data['spec1'])) {
            $this->sync_spec1[$data['spec1']['spec1_code']] = $data['spec1'];
        }
        if (!empty($data['spec2'])) {
            $this->sync_spec2[$data['spec2']['spec2_code']] = $data['spec2'];
        }
    }
    /**
     * 转换同步数据
     * @param array $api_data
     * @return type
     */
    function conver_info($api_data){
        $data = array();
        $data['goods'] = array(
            'goods_code' => $api_data['item_code'],//商品编码
            'goods_name' => $api_data['item_name'],//商品名称
            'goods_short_name' => $api_data['short_name'],//商品简称
            'unit_code' => $api_data['stock_unit'],//单位
            'weight' => $api_data['net_weight'],
            'category_name' => $api_data['category_name'],
            'category_code' => $api_data['category_id'],
            'sell_price' => $api_data['retail_price'], //零售价
            'cost_price' => $api_data['cost_price'], //成本价
            'buy_price' => $api_data['purchase_price'],//采购价
            'season_code' => $api_data['season_code'],
            'season_name' => $api_data['season_name'],
            'brand_code' => $api_data['brand_code'],
            'brand_name' => $api_data['brand_name'],
            'status' => $api_data['is_valid'],//是否有效 1为有效
        );
        $api_data['bar_code'] = str_replace('；', ';', $api_data['bar_code']);
        $sku_arr = explode(';', $api_data['bar_code']);
        $data['sku'] = array(
            'goods_code' => $api_data['item_code'],
            'spec1_code' => $api_data['color'],
            'spec2_code' => $api_data['size'],
        );
        $data['sku']['sku'] = !empty($sku_arr[0]) ? $sku_arr[0] : $api_data['item_code'];
        //条码不存在用SKU 代替条码
        $data['sku']['barcode'] = !empty($sku_arr[1]) ? $sku_arr[1] : $data['sku']['sku'];

        $skuProperty = explode(';', $api_data['sku_property']);//商品属性
        $data['spec1'] = array(
            'spec1_code' => $api_data['color'],
            'spec1_name' => $skuProperty[0],
        );
        $data['spec2'] = array(
            'spec2_code' => $api_data['size'],
            'spec2_name' => $skuProperty[1],
        );
        
        if (!empty($api_data['brand_code'])) {
            $data['brand'] = array(
                'brand_code' => $api_data['brand_code'],
                'brand_name' => $api_data['brand_name'],
            );
        }
        if (!empty($api_data['category_name'])) {
            $data['category'] = array(
                'category_name' => $api_data['category_name'],
                'category_code' => $api_data['category_id'],
            );
        }
        if (!empty($api_data['season_name'])) {
            $data['season'] = array(
                'season_code' => $api_data['season_code'],
                'season_name' => $api_data['season_name'],
            );
        }
        return $data;
    }
    /**
     * 保存主商品信息
     */
    private function save_item_info() {
        if (!empty($this->sync_goods)) {
            $goods_data = array_values($this->sync_goods);
            $this->insert_multi_duplicate('base_goods', $goods_data, $goods_data);
        }
        if (!empty($this->sync_sku)) {
            $sku_update = " barcode = VALUES(barcode) ";
            $barcode_update = " barcode = VALUES(barcode) ";
            $this->insert_multi_duplicate('goods_sku', $this->sync_sku, $sku_update,$sku_update);
            $this->insert_multi_duplicate('goods_barcode', $this->sync_sku, $barcode_update,$barcode_update);
            $sql1 = " update goods_sku,base_spec1 
                    set 
                    goods_sku.spec1_name=base_spec1.spec1_name
                    where goods_sku.spec1_code =base_spec1.spec1_code";
            $this->db->query($sql1);
               $sql2 = "update goods_sku,base_spec2 
                set 
                goods_sku.spec2_name=base_spec2.spec2_name
                where goods_sku.spec2_code =base_spec2.spec2_code"; 
            $this->db->query($sql2);
            
        }
        $this->sync_goods = array();
        $this->sync_sku = array();
    }
    /**
     * 保存其他信息
     */
    private function save_other_info() {
        if (!empty($this->sync_spec1)) {
            $upstr = " spec1_name = VALUES(spec1_name) ";
            $this->insert_multi_duplicate('base_spec1', $this->sync_spec1,$upstr);
        }
        if (!empty($this->sync_spec2)) {
             $upstr = " spec2_name = VALUES(spec2_name) ";
            $this->insert_multi_duplicate('base_spec2', $this->sync_spec2,$upstr);
        }
        if (!empty($this->sync_brand)) {
             $upstr= " brand_name = VALUES(brand_name) ";
            $this->insert_multi_duplicate('base_brand', $this->sync_brand,$upstr);
        }
        if (!empty($this->sync_category)) {
            $upstr = " category_name = VALUES(category_name) ";
            $this->insert_multi_duplicate('base_category', $this->sync_category,$upstr);
        }
        if (!empty($this->sync_season)) {
            $upstr = " season_name = VALUES(season_name) ";
            $this->insert_multi_duplicate('base_season', $this->sync_season,$upstr);
        }
        $this->sync_spec1 = array();
        $this->sync_spec2 = array();
        $this->sync_brand = array();
        $this->sync_category = array();
        $this->sync_season = array();
    }
}
