<?php

/**
 * 平台列表相关业务
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");
require_lib('net/HttpClient');
class ProductorderauthModel extends TbModel {

    function get_table() {
        return 'osp_productorder_auth';
    }

    /*
     * 获取平台列表方法
     */

    function get_by_page($filter) {
        $sql_main = "FROM {$this->table}  WHERE 1";

        //代码名称搜索条件
        if (isset($filter['client']) && $filter['client'] != '') {
            $sql_main .= " AND (pra_kh_id in (select kh_id from osp_kehu where kh_name LIKE '%" . $filter['client'] . "%')) ";
        }
        if (isset($filter['product']) && $filter['product'] != '') {
            $sql_main .= " AND pra_cp_id =" . $filter['product'];
        }

        //客户中心-客户授权订购查询过滤客户条件
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
            $sql_main .= " AND pra_kh_id =" . $filter['kh_id'];
        }
        if (isset($filter['market']) && $filter['market'] != '') {
            $sql_main .= " AND pra_strategytype = " . $filter['market'];
        }

        if (isset($filter['pra_program_version']) && $filter['pra_program_version'] != '') {
            $sql_main .= " AND pra_program_version = '{$filter['pra_program_version']}'";
        }

        //排序条件
        $sql_main .= " order by pra_id desc";
        $select = '*';

        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        foreach ($data['data'] as &$value) {
            $value['pra_app_key'] = '23300032';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('pra_kh_id|osp_kh', 'pra_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('pra_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        filter_fk_name($data, array('pra_kh_id|osp_kh', 'pra_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $data);
    }

    function get_by_other($otherinfo) {
        $params = $otherinfo;
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    function get_ptshop_num($id) {
        $sql_main = "FROM osp_productorder_shopauth  WHERE 1";
        if (isset($id['pra_id']) && $id['pra_id'] != '') {
            $sql_main .= " AND pra_shop_pid = '" . $id['pra_id'] . "'";
        }
        //构造排序条件
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        filter_fk_name($ret_data['data'], array('pra_shop_pfid|osp_pt_type'));
        return $this->format_ret($ret_status, $ret_data);
    }

    //产品订购授权审核
    function insert_order_auth($orderauth) {
        if (isset($orderauth)) {


            $dup_update_arr = array(
                'pra_product_version', 'pra_product_area', 'pra_cp_version', 'pra_strategytype', 'pra_app_key', 'pra_authnum', 'pra_pro_num', 'pra_startdate', 'pra_enddate', 'pra_state', 'pra_bz',
            );
            $update_str = "";
            foreach ($dup_update_arr as $k) {
                $update_str .= $k . " = VALUES(" . $k . "),";
            }
            $update_str = substr($update_str, 0, strlen($update_str) - 1);
            $this->insert_multi_duplicate($this->table, array($orderauth), $update_str);
            $params = array(
                'pra_kh_id' => $orderauth['pra_kh_id'],
                'pra_cp_id' => $orderauth['pra_cp_id'],
            );

            $ret = $this->get_row($params);
            $pra_id = $ret['data']['pra_id'];

            $pro_shopau = array();
            foreach ($orderauth['plat_shopinfo'] as $value) {
                $pro_shopau[] = array(
                    'pra_shop_pfid' => $value['pd_pt_id'],
                    'pra_shop_pid' => $pra_id,
                    'pra_shop_num' => $value['pd_shop_amount']);
            }

            $data = $this->db->create_mapper('osp_productorder_shopauth')->insert($pro_shopau);
            return $this->format_ret("1", $data, '审核成功');
        } else {
            return $this->format_ret("-1", '', '审核失败');
        }
    }

    //产品订购授权审核
    function update_order_auth($orderauth, $id) {
        if (isset($orderauth) && isset($id)) {
            $ret = parent::update($orderauth, array('pra_id' => $id));
            //更新店铺明细
            foreach ($orderauth['plat_shopinfo'] as $value) {
                $sql_mx = "SELECT * FROM osp_productorder_shopauth WHERE pra_shop_pfid=:pra_shop_pfid  ";
                $sql_mx_values[':pra_shop_pfid'] = $value['pd_pt_id'];
                $data = $this->db->get_row($sql_mx, $sql_mx_values);
                $pro_shopau = array();
                if (!empty($data)) {
                    $pro_shopau = array(
                        'pra_shop_pfid' => $value['pd_pt_id'],
                        'pra_shop_pid' => $id,
                        'pra_shop_num' => $value['pd_shop_amount'] + $data['pra_shop_num'],
                    );
                    $data_n = $this->db->create_mapper('osp_productorder_shopauth')->update($pro_shopau, array('pra_id' => $data['pra_id']));
                } else {
                    $pro_shopau[] = array(
                        'pra_shop_pfid' => $value['pd_pt_id'],
                        'pra_shop_pid' => $id,
                        'pra_shop_num' => $value['pd_shop_amount']
                    );
                    $data_n = $this->db->create_mapper('osp_productorder_shopauth')->insert($pro_shopau);
                }
            }
            //pra_kh_id
            $this->update_kh_auth($orderauth['pra_kh_id']);
            return $ret;
        }
    }

    /*     * 在线客服，保证买断版（独享型）新客户（客户档期新增时间>2017-03-01）不能使用
     * @param $params
     * @return array
     */

    function get_strategytype($params) {
        $sql = "SELECT pra_strategytype FROM osp_productorder_auth WHERE pra_kh_id=:kh_id AND pra_startdate>'2017-03-01 00:00:00' ";
        $sql_value[':kh_id'] = $params['kh_id'];
        $ret = $this->db->get_row($sql, $sql_value);
        if (empty($ret)) {
            return $this->format_ret('-1', '', '');
        }
        //2544 淄博维度商贸有限公司 特殊处理
        $kh_arr = array('2544', '2526');
        if (in_array($params['kh_id'], $kh_arr)) {
            $ret['pra_strategytype'] = 2;
        }

        return $this->format_ret('1', $ret['pra_strategytype'], '');
    }

    function update_pra_program_version($request) {
        $ret_pra = $this->get_by_id($request['pra_id']);
         $kh_id = $ret_pra['data']['pra_kh_id'];
        $data['pra_program_version'] = $request['pra_program_version'];
        $where = "pra_id='{$request['pra_id']}'";
        $program_version = require_conf('program_version');

        if (!isset($program_version[$request['pra_program_version']])) {
            return $this->format_ret(-1, '', '程序版本异常');
        }

        $data['pra_serverpath'] = $program_version[$request['pra_program_version']];
        if ($request['pra_program_version'] == 'customer') {
            $data['pra_serverpath'] = str_replace('{ip}', $request['vm_ip'], $data['pra_serverpath']);
            //link
        } else if ($request['pra_program_version'] == 'beta') {
            $data['pra_serverpath'] = $program_version[$request['pra_program_version']]['link'];
            $request['vm_ip'] = !empty($request['vm_ip']) ? $request['vm_ip'] : $program_version[$request['pra_program_version']]['exec_ip'];

        }
        $sql = "select rem_db_version_ip from osp_rdsextmanage_db where rem_db_khid=:rem_db_khid";
        $rem_db_version_ip = $this->db->get_value($sql,array(':rem_db_khid'=>$kh_id));
        
        
        $ret = $this->update($data, $where);
        //更改自动服务IP
        if (!empty($request['vm_ip'])) {
       
            $ip_data['rem_db_version_ip'] = $request['vm_ip'];
            $ip_where = "  rem_db_khid='$kh_id' ";
            $this->update_exp('osp_rdsextmanage_db', $ip_data, $ip_where);
        }
        //同步RDS信息
        //测试风险大，暂时去掉
        $sql_rds = "select rem_db_pid from osp_rdsextmanage_db where rem_db_khid=:kh_id ";
        $rds_id = $this->db->get_value($sql_rds,array(':kh_id'=>$kh_id));
        $rds = load_model('basedata/RdsDataModel');
        $rds->update_rds($rds_id);
        $this->clear_cache_exec_service($rem_db_version_ip);
        if(!empty($rds->rds_db)){
            $sql = "update sysdb.sys_task_main set exec_ip='{$request['vm_ip']}',plan_exec_ip='{$request['vm_ip']}'   where customer_code='{$kh_id}' AND is_over=0 "  ;
            $rds->rds_db->query($sql);
        }
        $this->clear_cache_exec_service($request['vm_ip']);
        return $ret;
    }
    function clear_cache_exec_service($ip){
        $url ="http://{$ip}/efast365/webefast/web/index.php?app_act=tool/osp/clear_conf";
    
        $h = new HttpClient();
        $h->newHandle('0', 'post', $url);
        $h->exec();

        $result = $h->responses();
        if (!isset($result['0'])) {
            throw new Exception('请求出错, 返回结果错误');
        }

        return $result['0'];

    }
            
    
    function update_kh_auth($kh_id) {
        $pra_product_version_map = array('1' => 'efast5_Standard', '2' => 'efast5_Enterprise', '3' => 'efast5_Ultimate');

        $ret = $this->get_row(array('pra_kh_id' => $kh_id));
        $auth_arr = array();
        if (!empty($ret['data'])) {
            $auth_arr[] = array(
                'code' => 'cp_code',
                'name' => '产品类型代码',
                'value' => $pra_product_version_map[$ret['data']['pra_product_version']],
            );
            $auth_arr[] = array(
                'code' => 'auth_num',
                'name' => '授权注册用户',
                'value' => $ret['data']['pra_authnum'],
            );

            $auth_arr[] = array(
                'code' => 'app_key',
                'name' => '应用主key',
                'value' => $ret['data']['pra_app_key'],
            );
        }
        try {
            load_model('pubdata/SyncPubModel')->sync_auth($kh_id, $auth_arr);
        } catch (Exception $ex) {
            
        }
    }

    /**
     * 续费
     * @param $pra_id
     * @param $out_params
     * @return array
     */
    function do_renew_save($pra_id, $out_params) {
        if (empty($out_params['pro_hire_limit']) && empty($out_params['pro_dot_num'])) {
            return $this->format_ret(-1, '', '续费时长，变动点数必须填写一个！');
        }
        //if (!empty($out_params['pro_hire_limit'])) {
        //    if (!preg_match('/^\d+$/', $out_params['pro_hire_limit'])) {
        //        return $this->format_ret('-1', '', '续费时长必须填写正整数！');
        //    }
        //}
        //if (!empty($out_params['pro_dot_num'])) {
        //    if (!preg_match("/^\-?[1-9]{1}[0-9]*$|^[0]{1}$/", $out_params['pro_dot_num'])) {
        //        return $this->format_ret('-1', '', '变动点数必须填写整数！');
        //    }
        //}
        $auth_ret = $this->get_by_id($pra_id);
        if ($auth_ret['status'] != 1) {
            return $auth_ret;
        }
        $auth_data = $auth_ret['data'];
        //获取客户最近的一次订购记录
        $sql = "SELECT * FROM osp_productorder WHERE pro_kh_id=:pro_kh_id ORDER BY pro_num DESC ";
        $order_info = $this->db->get_row($sql, array(':pro_kh_id' => $auth_data['pra_kh_id']));
        //获取客户信息
        $sql = "SELECT * FROM osp_kehu WHERE kh_id=:kh_id";
        $kh_info = $this->db->get_row($sql, array(':kh_id' => $auth_data['pra_kh_id']));
        //插入产品订购列表
        $insert_param = array(
            'pro_num' => create_fast_bill_sn('DGBH'),
            'pro_channel_id' => $kh_info['kh_place'],
            'pro_kh_id' => $auth_data['pra_kh_id'],
            'pro_cp_id' => $auth_data['pra_cp_id'],
            'pro_product_area' => $auth_data['pra_product_area'],
            'pro_product_version' => $auth_data['pra_product_version'],
            'pro_price_id' => $order_info['pro_price_id'],
            'pro_st_id' => $order_info['pro_st_id'],
            'pro_sell_price' => '',
            'pro_rebate_price' => 0,
            'pro_real_price' => $out_params['pro_real_price'],
            'pro_hire_limit' => $out_params['pro_hire_limit'],
            'pro_dot_num' => $out_params['pro_dot_num'],
            'pro_seller' => $kh_info['kh_xsuser'],
            'pro_pay_status' => 1,
            'pro_paydate' => date('Y-m-d H:i:s'),
            'pro_orderdate' => date('Y-m-d H:i:s'),
            'pro_app_key' => $order_info['pro_app_key'],
            'pro_add_type' => 1,
        );
        $ret = $this->insert_exp('osp_productorder', $insert_param);
        if ($ret['status'] != 1) {
            return $ret;
        }
        return $this->format_ret(1);
    }

}
