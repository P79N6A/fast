<?php

require_model('tb/TbModel');

class MidArchiveModel extends TbModel {

    protected $sync_goods;
    protected $sync_sku;
    protected $sync_brand;
    protected $sync_category;
    protected $sync_season;
    protected $sync_spec1;
    protected $sync_spec2;
	protected $sync_year;

    function sync_archive() {
        //先做下载档案，同步到其他系统档案暂不开发
        //3	archive	bserp	to_sys	1
        $flow_type = 'to_sys';
        $record_type = 'archive';
        $data = load_model('mid/MidBaseModel')->check_flow($flow_type, $record_type);

        if (!empty($data)) {
            $data = load_model('mid/MidApiConfigModel')->get_mid_api_config_by_api_product($data['api_product']);
            foreach ($data as $val) {
                $this->download_api_archive($val);
            }
        }
    }

    /**
     * 下载档案
     * @param type $api_data
     */
    function download_api_archive($api_data) {
                   
        $mod = $this->get_api_product_mod($api_data['api_product'], $api_data);
        $param = array(
            'page' => 1,
            'page_size' => 100,
        );
        $ret = array();
        while (true) {

            $ret = $mod->sync_info($param);

            if ($ret['status'] < 1) {
                break;
            }
            if (!empty($ret['data'])) {
                $update_str = " api_json_data = VALUES(api_json_data),api_update_time = VALUES(api_update_time),down_time = VALUES(down_time)";
                $this->insert_multi_duplicate('mid_archive', $ret['data'], $update_str);
            } else {
                break;
            }
            if(count($ret['data'])<$param['page_size']){
                break;
            }
            $param['page'] = $param['page'] + 1;
        }
        //下载完同步档案到系统
         $this->sync_archive_to_sys();
                         
        return $ret;
    }

    function get_api_product_mod($api_product, $api_data) {

        static $api_mod_arr = array();

        if (!isset($api_mod_arr[$api_data['mid_code']])) {
            $api_mod_name = ucfirst($api_product) . "ArchiveModel";
            $mod_path = 'mid/' . $api_product . '/' . $api_mod_name;
            require_model($mod_path);

            $api_mod_arr[$api_data['mid_code']] = new $api_mod_name($this, $api_data);
        }
        return $api_mod_arr[$api_data['mid_code']];
    }

    /**
     * 同步档案到系统
     */
    function sync_archive_to_sys() {
        while (true) {
            $data = $this->get_sys_sync_archive();

            $id_arr = array();
            foreach ($data as $val) {
                $id_arr[] = $val['id'];
                $this->set_sync_info($val);
            }
            $this->save_goods_info();
            $up_data = array(
                'sys_update_time' => date('Y-m-d H:i:s'),
            );
            $id_str = "'" . implode("','", $id_arr) . "'";
            $where = " id in( {$id_str}) ";
            $this->update_exp('mid_archive', $up_data, $where);

            if (count($data) < 1000) {
                break;
            }
        }
        $this->save_other_info();
    }

    /**
     * 获取需要同步到的档案
     * @param type $limit 
     */
    function get_sys_sync_archive($limit = 1000) {
        $sql = "select * from  mid_archive where down_time>sys_update_time limit {$limit}  ";
        $data = $this->db->get_all($sql);
        return $data;
    }

    function set_sync_info($api_info) {
        $api_data = json_decode($api_info['api_json_data'], true);
        $mod = $this->get_mod($api_info['mid_code'], $api_info['api_product']);
        $data = $mod->conversion_info($api_data);

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
        if (!empty($data['year'])) {
            $this->sync_year[$data['year']['year_code']] = $data['year'];
        }
    }

    function get_mod($mid_code, $api_product) {
        static $mod_arr = null;
        if (!isset($mod_arr[$mid_code])) {
            $mod_name = ucfirst($api_product) . 'ArchiveModel';
            $mod_str = "mid/{$api_product}/" . $mod_name;
            $status = require_model($mod_str);
            if ($status !== false) {
                $config = array(); //todu: 从 base中获取，获取配置文件

                $mod_arr[$mid_code] = new $mod_name($this, $config);
            }
        }
        return $mod_arr[$mid_code];
    }

    /**
     * 
     * 保存商品主信息
     */
    private function save_goods_info() {

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
     * 
     * 保存商品其他档案信息
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
        if (!empty($this->sync_year)) {
            $upstr = " year_name = VALUES(year_name) ";
            $this->insert_multi_duplicate('base_year', $this->sync_year, $upstr);
        }
        $this->sync_spec1 = array();
        $this->sync_spec2 = array();
        $this->sync_brand = array();
        $this->sync_category = array();
        $this->sync_season = array();
		$this->sync_year = array();
    }

}
