<?php

/**
 * 营销中心相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class ProductorderModel extends TbModel {

    public $pro_add_type = array(
        1 => '续费',
    );

    public $pro_check_status = array(
        0 => '未审核',
        1 => '已审核',
    );

    public $product_version= array(
        '1' => '标准版',
        '2' => '企业版',
        '3' => '旗舰版',
    );

    function get_table() {
        return 'osp_productorder';
    }

    /*
     * 获取增值服务信息方法
     */

    function get_porder_info($filter) {
//        $sql_join .= "left join osp_organization og on a.org_code=og.org_code ";
        $sql_main = "FROM {$this->table} a  WHERE 1";
        /** 根据客户名称查询客户id start*/
        if(!empty($filter['customer'])) {
            global $context;
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE \"%{$filter['customer']}%\"";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $context->db->get_all($sql));
        }
        /** 根据客户名称查询客户id end*/
        //关联产品搜索条件
        if (isset($filter['cp_id']) && $filter['cp_id'] != '') {
            $sql_main .= " AND pro_cp_id =" . $filter['cp_id'];
        }
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
//            $sql_main .= " AND pro_kh_id =" . $filter['kh_id'];
              $sql_main .= ' AND pro_kh_id IN ("'.implode('","', $filter['kh_id']).'")';
        }
        //营销类型
        if (isset($filter['pro_type']) && $filter['pro_type'] != '') {
            $sql_main .= " AND a.pro_st_id =" . $filter['pro_type'];
        }
        
        //排序条件
        $sql_main .= " order by pro_num desc";
        $select = 'a.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        foreach ($data['data'] as &$value) {
            $value['pro_add_type_name'] = $this->pro_add_type[$value['pro_add_type']];
            $value['pro_check_status_name'] = $this->pro_check_status[$value['pro_check_status']];
            $value['product_version_name'] = $this->product_version[$value['pro_product_version']];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联代码表
        filter_fk_name($ret_data['data'], array('pro_cp_id|osp_chanpin', 'org_id|org_channel', 'pro_kh_id|osp_kh', 'pro_channel_id|org_channel', 'pro_st_id|market_plan', 'pro_price_id|plan_price'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('pro_num' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('pro_channel_id|org_channel', 'pro_kh_id|osp_kh', 'pro_seller|osp_user_id', 'pro_price_id|plan_price'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加产品订购
     */

    function insert($porders) {
        if (isset($porders)) {
            $porders['pro_num'] = create_fast_bill_sn('DGBH');
            return $ret = parent::insert($porders);
        }
    }

    /*
     * 修改产品订购信息。
     */
    function update($porders, $id) {
        if (isset($porders) && isset($id)) {
            $ret = parent::update($porders, array('pro_num' => $id));
            return $ret;
        }
    }
    
    
    function get_planprice($id) {
        //获取报价方案详情
        $sql_hmx = "SELECT price_name,price_cpid,price_stid,price_note,price_dot,price_base FROM osp_plan_price WHERE price_id=:planid ";
        $sql_mx[':planid'] = $id;
        $data = $this->db->get_row($sql_hmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    function get_plan_detail($id) {
        //获取报价方案详情
        $sql_hmx = "SELECT pd_pt_id,pd_shop_amount from osp_plan_detail WHERE pd_price_id =:pcid ";
        $sql_mx[':pcid'] = $id;
        $data = $this->db->get_all($sql_hmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    function get_shop_num($id) {
        //获取报价方案详情
        $sql_hmx = "SELECT sum(pd_shop_amount) as shopnum from osp_plan_detail WHERE pd_price_id = :pcid ";
        $sql_mx[':pcid'] = $id;
        $data = $this->db->get_row($sql_hmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }


    /**系统部署独享添加
     * @param
     * @return array
     */
    function exclusive_add($request) {
        $vm = load_model('basedata/HostModel')->get_row(array('ali_outip' => $request['ali_outip']));
        if ($vm['status'] != 1) {
            return $this->format_ret('-1', '', 'VM信息不存在');
        }
        $vm_info = $vm['data'];
        $rds = load_model('basedata/RdsModel')->get_row(array('rds_id' => $request['rds_id']));
        if ($rds['status'] != 1) {
            return $this->format_ret('-1', '', 'RDS信息不存在');
        }
        $rds_info = $rds['data'];
        $product = $this->get_by_id($request['pro_num']);
        $product_info = $product['data'];
        $insert_data = array(
            'rem_db_pid' => $request['rds_id'],
            'rem_db_name' => $request['rem_db_name'],
            'rem_db_version_ip' => $vm_info['ali_outip'],
            'rem_db_is_bindkh' => '1',
            'rem_db_bindtype' => '1',
            'rem_db_khid' => $product_info['pro_kh_id'],
            'rem_db_createdate' => date('Y-m-d H:i:s'),
        );
        $update_str = "rem_db_pid = VALUES(rem_db_pid),rem_db_version_ip=VALUES(rem_db_version_ip)";
        $ret = $this->insert_multi_duplicate('osp_rdsextmanage_db', array($insert_data), $update_str);
        if ($ret['status'] == 1) {
            //更新产品表的系统部署状态
           $ret = $this->update(array('pro_is_arrange' => 1), $request['pro_num']);
           //回写客户id
//            $up_arr = array(
//                'kh_id' => $product_info['pro_kh_id'],
//            );
//            $this->db->update('osp_aliyun_host', $up_arr, array('ali_outip' => $request['ali_outip']));
//            $this->db->update('osp_aliyun_rds', $up_arr, array('rds_id' => $request['rds_id']));
        }
        if ($ret['status'] == 1) {
            return $this->format_ret('1', '', '部署成功！');
        } else {
            return $this->format_ret('-1', '', '部署失败！');
        }
    }

    /**系统部署共享添加
     * @param $request
     */
    function share_add($request) {
        $vm_time = load_model('basedata/HostModel')->get_row(array('ali_outip' => $request['time_ip']));
        if ($vm_time['status'] != 1) {
            return $this->format_ret('-1', '', '定时器IP不存在');
        }
        $time_info = $vm_time['data'];
        $vm_api = load_model('basedata/HostModel')->get_row(array('ali_outip' => $request['api_ip']));
        if ($vm_api['status'] != 1) {
            return $this->format_ret('-1', '', '接口IP不存在');
        }
        $api_info = $vm_api['data'];
        $rds = load_model('basedata/RdsModel')->get_row(array('rds_id' => $request['rds_id']));
        if ($rds['status'] != 1) {
            return $this->format_ret('-1', '', 'RDS信息不存在');
        }
        $rds_info = $rds['data'];
        $product = $this->get_by_id($request['pro_num']);
        $product_info = $product['data'];
        $insert_data = array(
            'rem_db_pid' => $request['rds_id'],
            'rem_db_name' => $request['rem_db_name'],
            'rem_db_version_ip' => $time_info['ali_outip'],
            'rem_db_is_bindkh' => '1',
            'rem_db_bindtype' => '1',
            'rem_db_khid' => $product_info['pro_kh_id'],
            'rem_db_createdate' => date('Y-m-d H:i:s'),
            'rem_db_api_ip' => $api_info['ali_outip'],
        );
        $update_str = "rem_db_pid = VALUES(rem_db_pid),rem_db_version_ip=VALUES(rem_db_version_ip),rem_db_api_ip=VALUES(rem_db_api_ip)";
        $ret = $this->insert_multi_duplicate('osp_rdsextmanage_db', array($insert_data), $update_str);
        if ($ret['status'] == 1) {
            //更新产品表的系统部署状态
            $ret = $this->update(array('pro_is_arrange' => 1), $request['pro_num']);
            //回写客户id
//            $up_arr = array(
//                'kh_id' => $product_info['pro_kh_id'],
//            );
//            $this->db->update('osp_aliyun_host', $up_arr, array('ali_outip' => $request['api_ip']));
//            $this->db->update('osp_aliyun_host', $up_arr, array('ali_outip' => $request['time_ip']));
//            $this->db->update('osp_aliyun_rds', $up_arr, array('rds_id' => $request['rds_id']));
        }
        if ($ret['status'] == 1) {
            return $this->format_ret('1', '', '部署成功！');
        } else {
            return $this->format_ret('-1', '', '部署失败！');
        }
    }
    
    /**
     * 产品部署，订购列表
     * @global type $context
     * @param type $filter
     * @return type
     */
     function get_arrange_porder_info($filter) {
        $sql_main = "FROM {$this->table} a  WHERE 1 AND a.pro_check_status=1 AND a.pro_pay_status=1 ";
        /** 根据客户名称查询客户id start*/
        if(!empty($filter['customer'])) {
            global $context;
            $sql = "SELECT `kh_id` FROM `osp_kehu` WHERE `kh_name` LIKE \"%{$filter['customer']}%\"";
            $filter['kh_id'] = array_map(function ($item) {
                return $item['kh_id'];
            }, $context->db->get_all($sql));
        }
        /** 根据客户名称查询客户id end*/
        //关联产品搜索条件
        if (isset($filter['cp_id']) && $filter['cp_id'] != '') {
            $sql_main .= " AND pro_cp_id =" . $filter['cp_id'];
        }
        //客户名称条件过滤
        if (isset($filter['kh_id']) && $filter['kh_id'] != '') {
//            $sql_main .= " AND pro_kh_id =" . $filter['kh_id'];
              $sql_main .= ' AND pro_kh_id IN ("'.implode('","', $filter['kh_id']).'")';
        }
        //营销类型
        if (isset($filter['pro_type']) && $filter['pro_type'] != '') {
            $sql_main .= " AND a.pro_st_id =" . $filter['pro_type'];
        }
        
        //排序条件
        $sql_main .= " order by pro_num desc";
        $select = 'a.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联代码表
        filter_fk_name($ret_data['data'], array('pro_cp_id|osp_chanpin', 'org_id|org_channel', 'pro_kh_id|osp_kh', 'pro_channel_id|org_channel', 'pro_st_id|market_plan', 'pro_price_id|plan_price'));
        return $this->format_ret($ret_status, $ret_data);
    }

    
        /*
     * 删除记录
     * */

    function delete($pro_num) {
        $ret = parent::delete(array('pro_num' => $pro_num));
        return $ret;
    }
    
}
