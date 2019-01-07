<?php

require_model('pubdata/BasePubModel');
require_lib('apiclient/TaobaoClient');

class TaobaoPubModel extends BasePubModel {

    protected $api_config = array();

    protected function load_config() {

        $app_info = require_conf('api/app_info');
        $this->api_config = $app_info['taobao'];
    }

    function get_express() {
        $this->load_config();
        $api_client = new TaobaoClient();
        $api_client->set_api_config($this->api_config);


        $api_data = $api_client->getExpress();
        $ret = $this->format_ret(1);
        if (isset($api_data['logistics_companies_get_response']['logistics_companies']['logistics_company'])) {
            $logistics_company = $api_data['logistics_companies_get_response']['logistics_companies']['logistics_company'];
            $exporess_data = array();
            foreach ($logistics_company as $val) {
                $exporess_data[] = array(
                    'company_code' => $val['code'],
                    'company_name' => $val['name'],
                    'rule' => $val['reg_mail_no'],
                );
            }
            $update_str = " company_name = VALUES(company_name),rule = VALUES(rule)";
            $this->insert_multi_duplicate('base_express_company', $exporess_data, $update_str);
        } else {
            $ret = $this->format_ret(-1, $api_data, '接口获取数据异常');
        }
        return $ret;
    }

}
