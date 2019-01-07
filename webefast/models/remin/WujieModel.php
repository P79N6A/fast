<?php

require_model('tb/TbModel');

/**
 * 无界热敏
 * @author WMH
 */
class WujieModel extends TbModel {

    private $jdClient = NULL;

    private function getClient($shop_code) {
        if ($this->jdClient === NULL) {
            require_lib('apiclient/JdClient');
            $this->jdClient = new JdClient($shop_code);
        }
    }

    public function get_provider_sign_list($filter) {
        $empty_data = ['filter' => ['record_count' => 0], 'data' => []];
        if (empty($filter['shop_code'])) {
            return $this->format_ret(1, $empty_data);
        }
        $select = 'es.id AS sign_id,es.shop_code,es.provider_code,es.branch_code,es.branch_name,es.settlement_code,es.amount,es.address,ej.provider_name,ej.company_code,ej.operation_type';
        $sql_main = 'FROM base_express_jd_sign AS es LEFT JOIN base_express_jd AS ej ON es.provider_code=ej.provider_code WHERE 1';
        $sql_values = [];

        if (!empty($filter['company_code'])) {
            $sql_main .= ' AND ej.company_code=:company_code';
            $sql_values[':company_code'] = $filter['company_code'];
        }
        if (!empty($filter['shop_code'])) {
            $sql_main .= ' AND es.shop_code=:shop_code';
            $sql_values[':shop_code'] = $filter['shop_code'];
        }

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        if (empty($data['data'])) {
            return $this->format_ret(1, $data);
        }

        $sign_id = 0;
        if (!empty($filter['express_code'])) {
            $sign_id = $this->db->get_value('SELECT sign_id FROM base_express WHERE express_code=:express_code', [':express_code' => $filter['express_code']]);
        }
        foreach ($data['data'] as &$row) {
            if ($row['sign_id'] == $sign_id) {
                $row['bind'] = 1;
                $row['bind_txt'] = '已绑定';
            } else {
                $row['bind'] = 0;
                $row['bind_txt'] = '未绑定';
            }
        }
        return $this->format_ret(1, $data);
    }

    /**
     * 获取京东承运商信息
     * @param string $company_code 系统快递公司编码
     * @return array
     */
    public function get_provider_by_company($company_code) {
        $sql = 'SELECT jd.provider_code,jd.provider_name,jd.provider_id,jd.operation_type FROM base_express_jd AS jd WHERE jd.company_code=:_code';
        $data = $this->db->get_row($sql, [':_code' => $company_code]);

        if (empty($data)) {
            return $this->format_ret(-1, '', '暂未找到该快递公司对应的京东承运商');
        }
        return $this->format_ret(1, $data);
    }

    public function get_provider_by_express_code($express_code) {
        static $express_arr = NULL;
        if (isset($express_arr[$express_code])) {
            return $this->format_ret(1, $express_arr[$express_code]);
        }
        $sql = 'SELECT be.rm_shop_code AS shop_code,es.provider_code,ej.provider_id,es.branch_code,es.settlement_code,es.address_json FROM base_express AS be INNER JOIN base_express_jd_sign AS es ON be.sign_id=es.id INNER JOIN base_express_jd ej ON es.provider_code=ej.provider_code AND be.company_code=ej.company_code WHERE be.print_type=3 AND be.express_code=:express_code';
        $express = $this->db->get_row($sql, [':express_code' => $express_code]);
        if (empty($express)) {
            return $this->format_ret(-2, '', '配送方式未绑定无界热敏京东承运商');
        }
        if (empty($express['shop_code'])) {
            return $this->format_ret(-1, '', '配送方式未设置热敏店铺');
        }
        $express_arr[$express_code] = $express;
        
        return $this->format_ret(1, $express);
    }

    /**
     * 获取京东承运商签约信息
     * @param string $shop_code
     * @return array
     */
    public function get_provider_sign_info_api($shop_code) {
        if (empty($shop_code)) {
            return $this->format_ret(-1, '', '请设置热敏挂靠店铺');
        }
        $this->getClient($shop_code);
        if (empty($this->jdClient->vender_code)) {
            $ret = $this->get_vender_code_api($shop_code);
            if ($ret['status'] < 1) {
                return $ret;
            }

            $this->jdClient = NULL;
            $this->getClient($shop_code);
        }

        $ret = $this->jdClient->ldopAlphaProviderSignSuccessInfoGet();
        if ($ret['status'] < 1) {
            return $ret;
        }

        $ins_data = [];
        $data = $ret['data'];
        foreach ($data as $row) {
            $temp = [
                'shop_code' => $shop_code,
                'provider_code' => $row['providerCode'],
                'support_cod' => $row['supportCod'] ? 1 : 0,
                'branch_code' => empty($row['branchCode']) ? NULL : $row['branchCode'],
                'branch_name' => empty($row['branchName']) ? NULL : $row['branchName'],
                'settlement_code' => empty($row['settlementCode']) ? NULL : $row['settlementCode'],
                'address' => $row['address']['address'],
                'address_json' => json_encode($row['address']),
            ];

            if ($row['operationType'] == 2) {
                //加盟型承运商获取单号库存
                $params = [
                    'providerCode' => $row['providerCode'],
                    'branchCode' => $row['branchCode']
                ];
                $ret = $this->jdClient->ldopAlphaVendorStockQueryByProviderCode($params);
                if ($ret['status'] < 1) {
                    return $ret;
                }

                $temp['amount'] = isset($ret['data'][0]['amount']) ? $ret['data'][0]['amount'] : 0;
            }
            $ins_data[] = $temp;
        }

        $ret = $this->insert_multi_duplicate('base_express_jd_sign', $ins_data, 'amount=VALUES(amount)');

        return $ret;
    }

    /**
     * 接口获取商家编码(POP商家ID)
     * @param string $shop_code 店铺代码
     * @return array
     */
    public function get_vender_code_api($shop_code) {
        $this->getClient($shop_code);
        $ret = $this->jdClient->sellerVenderInfoGet();
        if ($ret['status'] < 1) {
            return $ret;
        }

        $verder_info = $ret['data']['vender_info_result'];
        $verder_code = $verder_info['vender_id'];

        $ret = $this->update_exp('base_shop_api', ['vender_code' => $verder_code], ['shop_code' => $shop_code]);
        if ($ret['status'] == 1) {
            $ret['data'] = $verder_code;
        }
        return $ret;
    }

}
