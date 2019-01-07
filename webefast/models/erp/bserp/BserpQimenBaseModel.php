<?php

require_model('tb/TbModel');
require_lib('apiclient/QmErpClient');
class BserpQimenBaseModel extends TbModel {

    protected $bserp_type = '';
    protected $config = array();
    protected $config_shop = array();
    protected $config_store = array();
    protected $tb_key = "bserp";
    protected $erp_client = null;

    function __construct($table = '') {
        parent::__construct($table);
    }

    function get_erp_config_id() {

        $sql = "select erp_config_id from erp_config where erp_type=1";
        return $this->db->get_all($sql);
    }

    function get_erp_config($erp_config_id) {
        $sql = " select * from erp_config where erp_type=1 AND erp_config_id=:erp_config_id ";
        $this->config = $this->db->get_row($sql, array(':erp_config_id' => $erp_config_id));
        $sql = "select outside_code,shop_store_code,outside_type,o2o_store from sys_api_shop_store where p_id=:p_id and p_type=0  ";
        $out_sd_cks = $this->db->get_all($sql, array(':p_id' => $erp_config_id));

        foreach ($out_sd_cks as $sd_ck_row) {
            //商店
            if ($sd_ck_row['outside_type'] == 0) {
                $this->config_shop[$sd_ck_row['shop_store_code']] = $sd_ck_row['outside_code'];
            }
            //仓库&& $sd_ck_row['o2o_store']==0
            if ($sd_ck_row['outside_type'] == 1) {
                $this->config_store[$sd_ck_row['shop_store_code']] = $sd_ck_row['outside_code'];
            }
        }

        $this->tb_key = $this->config['erp_system'] == 1 ? 'bs3000j' : $this->tb_key;
    }

    function create_client() {
        if ($this->erp_client == null) {
            //todo $api_config
            $api_config = array();
            $erp_param = json_decode($this->config['erp_params'], true);
            $erp_param['customerid'] = !empty($this->config['customer_id']) ? $this->config['customer_id'] : $erp_param['customer_id'];
            $erp_param['target_key'] = !empty($this->config['target_key']) ? $this->config['target_key'] : $erp_param['target_key'];
            if (!empty($erp_param['api_url'])) {
                $api_config['api_url'] = $erp_param['api_url'];
            }
//                 if(!empty($erp_param['api_url'])){
//                  $api_config['target_key'] 
//            }
//                 if(!empty($erp_param['api_url'])){
//                  $api_config['target_key'] 
//            }

            $api_config['target_key'] = $erp_param['target_key'];
            $api_config['customer_id'] = $erp_param['customer_id'];
            require_lib('apiclient/QmErpClient');
            $this->erp_client = new QmErpClient($api_config);         
        }
    }

    function get_record_time($type_code) {
        $sql = "select exec_time from sys_schedule_record where type_code=:type_code ";
        $exec_time = $this->db->get_value($sql, array(':type_code' => $type_code));
        return $exec_time;
    }

    function save_record_time($type_code, $time) {
        $data = array(
            'type_code' => $type_code,
            'exec_time' => $time,
        );
        $update_str = " exec_time= VALUES(exec_time) ";
        $this->insert_multi_duplicate('sys_schedule_record', array($data), $update_str);
    }

}
