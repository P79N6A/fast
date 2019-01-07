<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_model('tb/TbModel');

class OspValueauthKeyModel extends TbModel {

    public function get_table() {
        return 'osp_valueauth_key';
    }

    /**
     * 生成授权apikey
     * @param string $kh_id 客户id
     */
    public function generateApi($kh_id) {
        /** 通过客户id查询授权id */
        $sql = "SELECT `vra_id` FROM `osp_valueorder_auth` WHERE `vra_kh_id` = \"{$kh_id}\"";
        extract(CTX()->db->get_row($sql));
        /** 如果为空则说明尚未付款 */
        if (is_null($vra_id)) {
            return $this->format_ret('3', '0', '尚未付款');
        }
        /** 检查key是否已经生成 */
        $get = $this->isApiExists($kh_id);
        if ($get['status']) {
            return $this->format_ret('2', $get['data'], 'API已经生成');
        }
        /** 检测授权key是否已生成 */
        $end = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] + 10 * 3600 * 24 * 365);
        $last = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        return $this->insert(array(
                    'vra_id' => $vra_id,
                    'authkey' => md5($vra_id . $kh_id . $end),
                    'kh_id' => $kh_id,
                    'end_date' => $end,
                    'lastchanged' => $last
        ));
    }

    /**
     * 判断API是否存在，存在返回api
     * @param string $kh_id 客户id
     * @return array ['status' => (1|-1), 'data' => api, 'message' => exists|not_exists]
     */
    public function isApiExists($kh_id) {
        $get = $this->get_row(array('kh_id' => $kh_id));
        if (count($get['data']) > 0) {
            return $this->format_ret("1", $get['data']['authkey'], 'exists');
        } else {
            return $this->format_ret("0", '0', 'not_exists');
        }
    }

    function get_kh_api_auth($kh_id) {
        $data = array();
        $sql = 'select pra_authkey from osp_productorder_auth where pra_kh_id = :pra_kh_id and pra_state="1" ';
        $sql_val[':pra_kh_id'] = $kh_id;
        $data['key'] = CTX()->db->get_value($sql, $sql_val);
        if (!empty($data['key'])) {
            return $this->format_ret(-1, '', '客户授权无效');
        }


        $sql = "select end_date,authkey from osp_valueauth_key where kh_id=:kh_id";
        $auth_data = CTX()->db->get_value($sql, array(':kh_id' => $kh_id));
        if (empty($auth_data)) {
            return $this->format_ret(-1, '', '客户没有授权key');
        }
        $now_time = time();
        $end_date_time = strtotime($auth_data['end_date']);
        if ($end_date_time < $now_time) {
            return $this->format_ret(-1, '', '客户没有授权key过期');
        }

        $data['secret'] = $auth_data['authkey'];
        return $this->format_ret(1, $data);
    }

}
