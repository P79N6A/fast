<?php

require_model('wms/WmsBaseModel');

class WmsArchiveModel extends WmsBaseModel {

    function sync($api_product = '') {
        error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
        set_time_limit(0);
        @ini_set('memory_limit', '2048M');

        //  $data = $this->get_sys_config_wms_product();
        $data = $this->get_sys_config_wms_by_config();
        if (!empty($data)) {
            foreach ($data as $val) {
                if ($api_product == '') {
                    $this->sync_wms_goods_info($val);
                } else if ($api_product == $val['wms_system_code']) {
                    $this->sync_wms_goods_info($val);
                }
            }
        }
    }

    function sync_wms_goods_info($info) {
        $class = ucfirst($info['wms_system_code']) . 'ArchiveModel';
        $s = require_model('wms/' . strtolower($info['wms_system_code']) . '/' . $class);
        if ($s) {
            $m = new $class($info['shop_store_code']);
            $m->sync();
        }
    }

    function sync_api_barcode($wms_config_id, $barcode) {
        $sql = "SELECT s.shop_store_code ,w.wms_system_code from wms_config  w INNER JOIN 
            sys_api_shop_store s on w.wms_config_id=s.p_id AND s.p_type=1
             where  w.wms_config_id=:wms_config_id AND s.shop_store_type=1 AND s.outside_type=1 AND s.store_type=1
            ";
        $info = $this->db->get_row($sql, array(':wms_config_id' => $wms_config_id));
        if (empty($info)) {
            return $this->format_ret(-1, '', '找不到对应仓储信息');
        }
        if ($info['wms_system_code'] != 'jdwms') {
            return $this->format_ret(-1, '', '不是京东沧海WMS');
        }
        $class = ucfirst($info['wms_system_code']) . 'ArchiveModel';
        require_model('wms/' . strtolower($info['wms_system_code']) . '/' . $class);
        $m = new $class($info['shop_store_code']);
        $param['barcode'] = $barcode;
        return $m->sync_api_barcode($param);
    }

    function sync_batch($type, $params) {
        if (empty($params['wms_config_id']) || empty($params['skus'])) {
            return $this->format_ret(-1, '', '参数错误,请刷新页面重试');
        }

        $sql = "SELECT s.shop_store_code ,w.wms_system_code FROM wms_config w INNER JOIN sys_api_shop_store s ON w.wms_config_id=s.p_id AND s.p_type=1 WHERE w.wms_config_id=:wms_config_id AND s.shop_store_type=1 AND s.outside_type=1 AND s.store_type=1";
        $wms_config = $this->db->get_row($sql, array(':wms_config_id' => $params['wms_config_id']));
        if (empty($wms_config)) {
            return $this->format_ret(-1, '', '找不到对应的WMS配置仓储信息');
        }
        if ($wms_config['wms_system_code'] !== 'qimen') {
            return $this->format_ret(-1, '', '该功能暂时仅支持奇门仓储');
        }
        $class = ucfirst($wms_config['wms_system_code']) . 'ArchiveModel';
        require_model('wms/' . strtolower($wms_config['wms_system_code']) . '/' . $class);
        $m = new $class($wms_config['shop_store_code']);
        $ret = $m->sync_batch($type, $params['skus']);
        $num = load_model("wms/WmsItemModel")->get_fail_num_by_id(-1,$params['wms_config_id']);//获取上传失败的数量
        $ret['data'] = $num;
        return $ret;
    }

}
