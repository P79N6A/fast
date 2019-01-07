<?php

/**
 * 商品条形码相关业务
 */
require_model('tb/TbModel');

class BarcodeModel extends TbModel {

    protected $table = "goods_barcode";

    //根据指定的条码取商品的档案的基本信息
    function get_goods_info_from_gcz($barcode_arr) {
        echo '<hr/>barcode_arr<xmp>' . var_export($barcode_arr, true) . '</xmp>';
        $barcode_list = $this->unique_arrel_list($barcode_arr);
        $sql = "select sku,barcode from goods_sku where barcode in($barcode_list)";
        $db_arr = $this->db->getAll($sql);
        $tbl_cfg = array(
            'goods_sku' => array('fld' => 'weight,price,goods_code,color_code,size_code', 'relation_fld' => 'sku+sku'),
            'prm_spec1' => array('fld' => 'spec1_name', 'relation_fld' => 'spec1_id+spec1_id'),
            'prm_spec2' => array('fld' => 'spec2_name', 'relation_fld' => 'spec2_id+spec2_id'),
        );
        require_model('tb/GetSqlRelationData');
        $obj_relation = new GetSqlRelationData();
        $obj_relation->tbl_cfg = $tbl_cfg;
        $db_arr = $obj_relation->get_data_by_cfg(null, $db_arr);
        $arr = $this->conv_kv_arr($db_arr, array('keys' => 'barcode'));
        echo '<hr/>arr<xmp>' . var_export($arr, true) . '</xmp>';
        return $arr;
    }

}
