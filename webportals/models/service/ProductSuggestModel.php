<?php

/**
 * 服务中心-提单管理-产品需求提单
 *
 * @author wangshouchong
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class ProductSuggestModel extends TbModel {

    /**
     * 获取产品建议列表
     */
    function get_by_page($filter) {
        $filter['xqsue_cp_code'] = 'efast365';
        $filter['kh_fwuser'] = CTX()->get_session('user_code');
        $ret = load_model('api/IServerModel')->osp_get_by_page('products.suggest.list.get', $filter);
        return $ret;
    }

    function get_by_id($id) {
        $params['xqsue_number'] = $id;
        $ret = load_model('api/IServerModel')->osp_server('products.suggest.detail.get', array($params));
        return $ret;
    }

}
