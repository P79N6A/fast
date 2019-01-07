<?php

/**
 * 用户相关业务
 *
 * @author wzd
 *
 */
require_model('tb/TbModel');
require_lib('util/oms_util', true);

//require_lang('sys');

class ShopModel extends TbModel {

    function get_table() {
        return 'osp_shangdian';
    }

    /*
     * 获取岗位信息方法
     */
    function get_shop_info($filter) {
        $sql_join = "left join osp_kehu kh on sd.sd_kh_id=kh.kh_id  ";
        $sql_main = "FROM {$this->table} sd $sql_join WHERE 1";
       
//        //是否按单收费搜索条件
//        if (isset($filter['isad']) && $filter['isad'] != '') {
//            $sql_main .= " AND sd_is_adsf='{$filter['isad']}'";
//        }
        //店铺平台类型
        if (isset($filter['platform']) && $filter['platform'] != '') {
            $sql_main .= " AND sd.sd_pt_id='{$filter['platform']}'";
        }
        if (isset($filter['sd_pt_id']) && $filter['sd_pt_id'] != '') {
            $sql_main .= " AND sd.sd_pt_id='{$filter['sd_pt_id']}'";
        }
        //客户id搜索条件
        if (isset($filter['sd_kh_id']) && $filter['sd_kh_id'] != '') {
            $sql_main .= " AND sd.sd_kh_id='{$filter['sd_kh_id']}'";
        }

        //代理商名称
        if (isset($filter['agent_name']) && $filter['agent_name'] != '') {
            $sql_main .= " AND sd.sd_agent LIKE '%" . $filter['agent_name'] . "%'";
        }
        //店铺名称搜索条件
        if (isset($filter['shopname']) && $filter['shopname'] != '') {
            $sql_main .= " AND sd.sd_name LIKE '%" . $filter['shopname'] . "%'";
        }
        //客户名称搜索条件
        if (isset($filter['clientname']) && $filter['clientname'] != '') {
            $sql_main .= " AND kh.kh_name LIKE '%" . $filter['clientname'] . "%'";
        }
        //构造排序条件
        $sql_main .= " order by sd_createdate desc";
                
        $select = 'sd.*,kh.kh_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联店铺平台类型
        filter_fk_name($ret_data['data'], array('sd_pt_id|osp_pt_type','sd_servicer|osp_user_id'));   
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {

        $params = array('sd_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('sd_kh_id|osp_kh', 'sd_createuser|osp_user_id', 'sd_updateuser|osp_user_id','sd_pt_id|osp_pt_type','sd_servicer|osp_user_id_p'));
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加新岗位
     */
    function insert($shop) {
        $status = $this->valid($shop);
        if ($status < 1) {
            return $this->format_ret($status);
        }

//        $ret = $this->is_exists($shop['sd_code']);
//        if ($ret['status'] > 0 && !empty($ret['data']))
//            return $this->format_ret(USER_ERROR_UNIQUE_CODE);

        $ret = $this->is_exists($shop['sd_name'], 'sd_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret('店铺名称已存在！');
            return parent::insert($shop);
    }



    /*
     * 修改客户信息。
     */
    function update($shop, $id) {
        $status = $this->valid($shop, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('sd_id' => $id));
//        if ($shop['sd_code'] != $ret['data']['sd_code']) {
//            $retcode = $this->is_exists($shop['sd_code'], 'sd_code');
//            if ($retcode['status'] > 0 && !empty($retcode['data']))
//                return $this->format_ret(USER_ERROR_UNIQUE_CODE);
//        }
        if ($shop['sd_name'] != $ret['data']['sd_name']) {
            $retname = $this->is_exists($shop['sd_name'], 'sd_name');
            if ($retname['status'] > 0 && !empty($retname['data']))
                return $this->format_ret('店铺名称已存在！');
        }
        $ret = parent::update($shop, array('sd_id' => $id));
        return $ret;
    }


    /*
     * 服务器端验证
     */
    private function valid($data, $is_edit = false) {
//        if (!$is_edit && (!isset($data['sd_code']) || !valid_input($data['sd_code'], 'required')))
//            return SD_ERROR_CODE;
        if (!isset($data['sd_name']) || !valid_input($data['sd_name'], 'required'))
            return '店铺名称已存在！';
            return 1;
    }

    private function is_exists($value, $field_name = 'sd_name') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //获取平台店铺类型
    function getshop_type($pdid) {
        $sql_main = "SELECT pd_id,pd_shop_type from osp_platform_detail where pd_pt_id=:pt_id";
        $sql_values[':pt_id'] = $pdid;
        $data = $this->db->get_all($sql_main, $sql_values);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //数据推送绑定
    function update_shop_databind($bind, $id) {
        if (!in_array($bind, array(0, 1))) {
            return $this->format_ret('error_params');
        }
        //1.通过店铺获取客户数据库所在的RDS
        $khid=$this->db->get_value("select sd_kh_id from osp_shangdian where sd_id=:sd_id", array(":sd_id" => $id));
        if(empty($khid)){
            return $this->format_ret("-1", "", '没有所属客户');
        }
        $sd_session=$this->db->get_value("select sd_top_session from osp_shangdian where sd_id=:sd_id", array(":sd_id" => $id));
        $rdsid=$this->db->get_value("select rem_db_pid from osp_rdsextmanage_db where rem_db_khid=:rem_db_khid", array(":rem_db_khid" => $khid));
        if(empty($khid)){
            return $this->format_ret("-1", "", '匹配不到RDS');
        }
        $rdsinfo=$this->db->get_row("select * from osp_rdsextmanage where rem_rds_id=:rem_rds_id", array(":rem_rds_id" => $rdsid));
        //获取rds实例rds_dbname
        $rdsname=$this->db->get_row("select rds_dbname from osp_aliyun_rds where rds_id=:rds_id", array(":rds_id" => $rdsid));
        $rdskeyinfo=$this->db->get_row("select app_key,app_secret,access_token from osp_rds where rds_id=:rds_id", array(":rds_id" => $rdsinfo['rem_bindcpkey']));
        //2.调用淘宝API接口
        if($bind=='1'){
            //构造参数
            $params=array(
                'app_key'=>$rdskeyinfo['app_key'],
                'app_secret'=>$rdskeyinfo['app_secret'],
                //'access_token'=>$rdskeyinfo['access_token'],
                'access_token'=>$sd_session,
                'rdsname'=>$rdsname['rds_dbname'],
            );
            $state=$this->jushita_jdp_user_add($params);
            if($state){
                $ret = parent::update(array('sd_databind' => $bind), array('sd_id' => $id));
                return $ret;
            }else{
                return $this->format_ret("-1", "", '绑定失败');
            }
        }else{//取消绑定
            //构造参数
            $shopnick=$this->db->get_value("select sd_nick from osp_shangdian where sd_id=:sd_id", array(":sd_id" => $id));
            $params=array(
                'app_key'=>$rdskeyinfo['app_key'],
                'app_secret'=>$rdskeyinfo['app_secret'],
                //'access_token'=>$rdskeyinfo['access_token'],
                'access_token'=>$sd_session,
                'shopnick'=>$shopnick
            );
            $state=$this->jushita_jdp_user_delete($params);
            if($state){
                $ret = parent::update(array('sd_databind' => $bind), array('sd_id' => $id));
                return $ret;
            }else{
                return $this->format_ret("-1", "", '取消绑定失败');
            }
        }
    }
    
    /**
     * rds数据推送绑定
     */
    function jushita_jdp_user_add($params) {
        require_lib('util/taobao_util', true);
        
        $rds_name = $params['rdsname'];
        $taobao = new taobao_util($params['app_key'],$params['app_secret'], $params['access_token']);
        $taobao->topUrl = 'http://gw.api.taobao.com/router/rest?';
        
        $params = array();
        $params['rds_name'] = $rds_name;

        $status =$taobao->post('taobao.jushita.jdp.user.add', $params);
        if (1 != $status['status']) {
            CTX()->log_error('绑定失败'.print_r($status,true));
            return false;
        }else{
            return true;
        } 
    }
    
    /**
     * rds数据推送绑定—删除
     */
    function jushita_jdp_user_delete($params) {
        require_lib('util/taobao_util', true);
        $nick = $params['shopnick'];
        $taobao = new taobao_util($params['app_key'],$params['app_secret'], $params['access_token']);
        $taobao->topUrl = 'http://gw.api.taobao.com/router/rest?';
        $params = array();
        $params['nick'] = $nick;

        $status =$taobao->post('taobao.jushita.jdp.user.delete', $params);
        if (1 != $status['status']) {
            CTX()->log_error('取消绑定失败'.print_r($status,true));
            return false;
        }else{
            return true;
        } 
    }
    
    
    /**
     * 网络店铺授权信息填入运营平台
     * @param type $shop_param
     * @return type
     */
    public function save_shop_info($shop_param) {
        if (empty($shop_param['shop_code'])) {
            return $this->format_ret('-1', '', '插入失败，没有店铺code');
        }
        if (empty($shop_param['kh_id'])) {
            return $this->format_ret('-1', '', '插入失败，没有客户id');
        }
        //销售平台id
        $pt_id = empty($shop_param['source']) ? '' : oms_tb_val('osp_platform', 'pt_id', array('pt_code' => $shop_param['source']));
        //插入数据
        $insert_data = array(
            'sd_code' => $shop_param['shop_code'],
            'sd_name' => $shop_param['shop_name'],
            'sd_kh_id' => $shop_param['kh_id'],
            'api' => $shop_param['api'],
            'sd_bz' => $shop_param['remark'],
            'sd_nick' => $shop_param['nick'],
            'sd_pt_id' => $pt_id,
            'sd_pt_shoptype' => $shop_param['tb_shop_type'],
            'sd_isauth' => $shop_param['authorize_state'],
            'sd_end_time' => $shop_param['authorize_date'],
            'sd_createdate' => date('Y-m-d H:i:s'),
        );
        //更新
        $chk_fld_arr = explode(',', 'sd_name,api,sd_bz,sd_nick,sd_pt_shoptype,sd_isauth,sd_end_time');
        foreach ($chk_fld_arr as $_fld) {
            if(!empty($insert_data[$_fld])){
                 $on_dup_up_arr[] = "{$_fld}=values({$_fld})";
            }
        }
        //更新
        $update_str = implode(',', $on_dup_up_arr);
        $ret = $this->insert_multi_duplicate('osp_shangdian', array($insert_data), $update_str);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '插入失败！');
        }
        return $this->format_ret('1', '', '插入成功！');
    }

}
