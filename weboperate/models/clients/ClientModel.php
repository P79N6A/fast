<?php

/**
 * 客户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');

//require_lang('sys');

class ClientModel extends TbModel {

    function get_table() {
        return 'osp_kehu';
    }

    /*
     * 获取岗位信息方法
     */

    function get_clients_info($filter) {

        $sql_main = "FROM {$this->table}  WHERE 1";

//        //是否云客户搜索条件
//        if (isset($filter['isaliyun']) && $filter['isaliyun'] != '') {
//            $sql_main .= " AND kh_is_ykh='{$filter['isaliyun']}'";
//        }
        //客户关联产品搜索条件
        if (isset($filter['cp_id']) && $filter['cp_id'] != '') {
            $sql_main .= " AND cp_id='{$filter['cp_id']}'";
        }
        //客户名称搜索条件
        if (isset($filter['client_name']) && $filter['client_name'] != '') {
            $sql_main .= " AND kh_name LIKE '%" . $filter['client_name'] . "%'";
        }
        //客户销售渠道搜索条件
        if (isset($filter['kh_place']) && $filter['kh_place'] != '') {
            $sql_main .= " AND kh_place='{$filter['kh_place']}'";
        }
        //客户备注搜索条件
        if (isset($filter['kh_memo']) && $filter['kh_memo'] != '') {
            $sql_main .= " AND kh_memo LIKE '%" . $filter['kh_memo'] . "%'";
        }
        //产品授权客户过滤
        if (isset($filter['is_auth']) && $filter['is_auth'] != '') {

            $sql_main .= " AND kh_id in (select pra_kh_id from osp_productorder_auth where pra_kh_id not in(select rem_db_khid from osp_rdsextmanage_db where rem_db_is_bindkh=1))";
        }
        //拼接排序字段条件
        $sql_main .= " order by kh_createdate desc";

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联产品名称
        filter_fk_name($ret_data['data'], array('cp_id|osp_chanpin', 'kh_fwuser|osp_user_id', 'kh_place|org_channel'));
        return $this->format_ret($ret_status, $ret_data);
    }

    //获取店铺明细
    function get_shop_info($filter) {
        $sql_main = "FROM osp_shangdian sd  WHERE 1";

        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND sd.sd_kh_id='{$filter['kh_id']}'";
        }
        //店铺平台类型
        if (isset($filter['platform']) && $filter['platform'] != '') {
            $sql_main .= " AND sd.sd_pt_id='{$filter['platform']}'";
        }
        //店铺名称搜索条件
        if (isset($filter['shopname']) && $filter['shopname'] != '') {
            $sql_main .= " AND sd.sd_name LIKE '%" . $filter['shopname'] . "%'";
        }
        //构造排序条件
        $sql_main .= " order by sd_createdate desc";
        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联店铺平台类型
        filter_fk_name($ret_data['data'], array('sd_pt_id|osp_pt_type', 'sd_servicer|osp_user_id'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_vm_info($filter) {
        $sql_main = "FROM osp_aliyun_host h WHERE 1";

        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND h.kh_id='{$filter['kh_id']}'";
        }

        //IP地址搜索条件
        if (isset($filter['ipaddr']) && $filter['ipaddr'] != '') {
            $sql_main .= " AND h.ali_outip LIKE '%" . $filter['ipaddr'] . "%'";
        }
        //主机类型
        if (isset($filter['ali_type']) && $filter['ali_type'] != '') {
            $sql_main .= " AND h.ali_type =" . $filter['ali_type'];
        }
        //排序条件及过滤客户
        $sql_main .= " order by host_id desc";

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('ali_type|osp_cloud_server', 'ali_server_model|osp_cloud_type'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_rds_info($filter) {
        $sql_main = "FROM osp_aliyun_rds r WHERE 1";

        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND r.kh_id='{$filter['kh_id']}'";
        }

        //平台类型搜索条件
        if (isset($filter['dbtype']) && $filter['dbtype'] != '') {
            $sql_main .= " AND r.rds_dbtype ='{$filter['dbtype']}'";
        }
        //rds实例名搜索条件
        if (isset($filter['dbname']) && $filter['dbname'] != '') {
            $sql_main .= " AND r.rds_dbname LIKE '%" . $filter['dbname'] . "%'";
        }
        //排序条件
        $sql_main .= " order by rds_id desc";

        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('rds_dbtype|osp_cloud_server'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('kh_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('kh_fwuser|osp_user_id_p', 'kh_xsuser|osp_user_id_p', 'kh_place|org_channel', 'kh_createuser|osp_user_id', 'kh_updateuser|osp_user_id'));
        //处理扫描件显示
        $orderpath = CTX()->get_app_conf('orderurl');
        if (!empty($orderpath)) {
            $data['kh_licence_img'] = $orderpath . $data['kh_licence_img'];
        }
        return $this->format_ret($ret_status, $data);
//        return $this->get_row(array('kh_id' => $id));
    }

    /*
     * 添加新客户信息
     */

    function insert($client) {
        $status = $this->valid($client);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->is_exists($client['kh_name'], 'kh_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('name_is_exist');
        return parent::insert($client);
    }

    /*
     * 修改客户信息。
     */

    function update($client, $id) {
        $status = $this->valid($client, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('kh_id' => $id));
        if ($client['kh_name'] != $ret['data']['kh_name']) {
            $retname = $this->is_exists($client['kh_name'], 'kh_name');
            if ($retname['status'] > 0 && !empty($retname['data']))
                return $this->format_ret('name_is_exist');
        }
        //print_r($client);die;
        $ret = parent::update($client, array('kh_id' => $id));
        $c_data[] = array(
            'code' => 'star_code',
            'value' => $client['star_code'],
            'name' => '星联卡号',
        );
        $c_data[] = array(
            'code' => 'star_password',
            'value' => $client['star_password'],
            'name' => '星联密码',
        );
        load_model('pubdata/SyncPubModel')->sync_auth($id, $c_data);

//        print_r($ret);die;
        return $ret;
    }

    function update_check_client($client, $id) {
        $ret = parent::update($client, array('kh_id' => $id));
        return $ret;
    }

    function get_kh_id($name) {


        $sql = "select k.kh_id from osp_kehu k
                INNER JOIN osp_rdsextmanage_db r ON k.kh_id=r.rem_db_khid AND r.rem_db_bindtype=1
                WHERE 1 
                ";
        $sql_values = array();
        if (is_numeric($name)) {
            $sql.=" AND k.kh_id=:kh_id";
            $sql_values[':kh_id'] = $name;
        } else {
            $sql.=" AND k.kh_name like :kh_name";
            $sql_values[':kh_name'] = '%' . $name . '%';
        }
        return $this->db->get_value($sql, $sql_values);
    }

    /*
     * 服务器端验证提交的数据是否重复
     */

    private function valid($data, $is_edit = false) {
//        if (!$is_edit && (!isset($data['kh_code']) || !valid_input($data['kh_code'], 'required')))
//            return '客户代码已存在！';
        if (!isset($data['kh_name']) || !valid_input($data['kh_name'], 'required'))
            return 'name_is_exist';
        return 1;
    }

    private function is_exists($value, $field_name = 'kh_name') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

    function get_select_carry() {
        $sql = "select k.kh_id,k.kh_name from osp_kehu k
INNER JOIN osp_rdsextmanage_db r ON k.kh_id=r.rem_db_khid AND r.rem_db_bindtype=1";
        $data = $this->db->get_all($sql);
        $arr = array();
        foreach ($data as $val) {
            $arr[] = array(
                $val['kh_id'],
                $val['kh_name'],
            );
        }
        return $arr;
    }

    function update_kh_sys_auth($kh_id = '') {
        $sql = "SELECT  k.kh_id,k.kh_name, k.star_code,k.star_password,a.pra_authnum, a.pra_product_version ,a.pra_authkey, a.pra_app_key,a.pra_enddate,pra_serverpath 
            from osp_kehu k
            INNER JOIN osp_productorder_auth a
            on k.kh_id=a.pra_kh_id
            where 1";
        $sql_values = array();

        if (!empty($kh_id)) {
            $kh_id_arr = implode(",", $kh_id);
            $sql.="  AND k.kh_id in ({$kh_id_arr})";
        }
        $data = $this->db->get_all($sql, $sql_values);
        $error_msg = array();
        $num = 0;
        foreach ($data as $val) {
            $ret = $this->set_kh_auth_info($val);
            if ($ret['status'] > 0) {
                $num++;
            } else {
                $error_msg[] = $ret['message'];
            }
        }
        $msg = "成功：{$num}";
        if (!empty($error_msg)) {
            $msg.=",失败：" . implode(",", $error_msg);
        }
        return $this->format_ret(1, '', $msg);
    }

    function set_kh_auth_info($kh_info) {
        if (strtotime($kh_info['pra_enddate']) < time()) {
            return $this->format_ret(-1, '', $kh_info['kh_id'] . '授权过期');
        }

        $auth_arr = array();
        $pra_product_version_map = array('1' => 'efast5_Standard', '2' => 'efast5_Enterprise', '3' => 'efast5_Ultimate');
        // $pra_product_area_map = array('1' => 'online', '2' => 'offline', '3' => 'o2o');
        $auth_arr[] = array(
            'code' => 'company_name',
            'name' => '授权公司名称',
            'value' => $kh_info['kh_name'],
        );
        $auth_arr[] = array(
            'code' => 'auth_key',
            'name' => '授权产品密钥',
            'value' => $kh_info['pra_authkey'],
        );
        $auth_arr[] = array(
            'code' => 'app_key',
            'name' => '应用主key',
            'value' => $kh_info['pra_app_key'],
        );
        $auth_arr[] = array(
            'code' => 'auth_num',
            'name' => '授权注册用户',
            'value' => $kh_info['pra_authnum'],
        );
        $auth_arr[] = array(
            'code' => 'cp_code',
            'name' => '产品类型代码',
            'value' => $pra_product_version_map[$kh_info['pra_product_version']], //efast5_Enterprise
        );

        $auth_arr[] = array(
            'code' => 'kh_id',
            'name' => '客户ID号',
            'value' => $kh_info['kh_id'],
        );

        $auth_arr[] = array(
            'code' => 'login_server_url',
            'name' => '登录服务器的URL',
            'value' => $kh_info['pra_serverpath'],
        );
        $auth_arr[] = array(
            'code' => 'auth_enddate',
            'name' => '软件授权到期时间',
            'value' => $kh_info['pra_enddate'],
        );

        $auth_arr[] = array(
            'code' => 'star_code',
            'name' => '星联卡号',
            'value' => $kh_info['star_code'],
        );
        $auth_arr[] = array(
            'code' => 'star_password',
            'name' => '星联密码',
            'value' => $kh_info['star_password'],
        );
        return load_model('pubdata/SyncPubModel')->sync_auth($kh_info['kh_id'], $auth_arr);
    }

}
