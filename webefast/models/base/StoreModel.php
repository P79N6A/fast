<?php

/**
 * 仓库相关业务
 *
 * @author dfr
 *
 */
require_model('tb/TbModel');
require_lang('store');

class StoreModel extends TbModel {

    private $user_id = 0;
    private $is_manage = -1;
    private $store_property = 0; //0：普通店铺;1-门店店铺;2-所有店铺，门店店铺暂不需要权限控制，暂不使用该方法控制

    function get_table() {
        return 'base_store';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        $sql_values = array();
        //仓库名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            //$sql_main .= " AND (rl.store_code LIKE '%" . $filter['code_name'] . "%' or rl.store_name LIKE '%" . $filter['code_name'] . "%' )";
            $sql_main .= " AND (rl.store_code LIKE :code_name or rl.store_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }

        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_type_code_arr = explode(",", $filter['store_type_code']);
            $store_type_code_str = $this->arr_to_in_sql_value($store_type_code_arr, 'store_type_code', $sql_values);
            $sql_main .= " AND rl.store_type_code in({$store_type_code_str}) ";
        }

        if (isset($filter['__sort']) && $filter['__sort'] != '') {
            $filter['__sort_order'] = $filter['__sort_order'] == '' ? 'asc' : $filter['__sort_order'];
            $sql_main .= ' order by ' . trim($filter['__sort']) . ' ' . $filter['__sort_order'];
        }

        //对外接口 增量下载
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] = $filter['lastchanged'];
        }
        if (isset($filter['is_api']) && $filter['is_api'] !== '') {
            $sql_main .= " order by rl.lastchanged desc ";
        }
        $select = 'rl.*';

        //$data =  $this->get_page_from_sql($filter, $sql_main, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as &$value) {
            if (!empty($value['store_type_code'])) {
                $value['store_type_code_name'] = $this->db->get_value("select type_name from base_store_type where type_code = :type_code ", array(":type_code" => $value['store_type_code']));
            } else {
                $value['store_type_code_name'] = '';
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($store_id) {
        $ret = $this->get_row(array('store_id' => $store_id));
        if (!empty($ret['data'])) {
            if (!empty($ret['data']['custom_code'])) {
                $ret['data']['custom_name'] = $this->db->get_value("select custom_name from base_custom where custom_code = :custom_code", array(":custom_code" => $ret['data']['custom_code']));
            } else {
                $ret['data']['custom_name'] = '';
            }
        }
        return $ret;
    }

    function get_by_code($store_code) {

        return $this->get_row(array('store_code' => $store_code));
    }

    /*
     * 添加新纪录
     */

    function insert($store) {
        $status = $this->valid($store);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($store['store_code']);
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', 'STORE_ERROR_UNIQUE_CODE');

        $ret = $this->is_exists($store['store_name'], 'store_name');
        if ($ret['status'] > 0 && !empty($ret['data']))
            return $this->format_ret(-1, '', 'STORE_ERROR_UNIQUE_NAME');


        return parent::insert($store);
    }

    /**
     * 更新仓库状态
     * @param array $params array('id','type')
     * @return array 更新结果
     */
    function update_active($id, $active) {
        if (!in_array($active, array(0, 1)) || empty($id)) {
            return $this->format_ret(-1, '', '参数错误，请刷新页面重试');
        }
        if ($active == 0) {
            $ret_used = $this->check_is_used($id, 'update');
            if (!empty($ret_used)) {
                $store_name = implode(",", $ret_used);
                return $this->format_ret(-1, '', "仓库已被店铺" . $store_name . "使用，请先清除店铺中设置的发货仓/退货仓/库存来源");
            }
        }
        $ret = parent :: update(array('status' => $active), array('store_id' => $id));
        return $ret;
    }

    //检测是否存在
    private function is_exists($value, $field_name = 'store_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //列表数据
    function get_list() {
        $sql = "select store_id,store_code,store_name FROM {$this->table} ";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    //仓库列表，删除总仓时，若已被店铺使用，删除时给予提示：仓库已被店铺X使用，请先清除店铺中设置的发货仓/退货仓/库存来源。
    function check_is_used($id, $type) {
        $code = $this->db->get_value("select store_code from {$this->table} where store_id=:id", array(':id' => $id));
        $shop_name = array();

        if (($code == '001' && $type == 'delete') || $type == 'update') {
            $sql = "select shop_name,send_store_code,refund_store_code,stock_source_store_code from base_shop where shop_type=0 and (send_store_code = :code or refund_store_code = :code or stock_source_store_code like '%{$code}%')";
            if ($type == 'update') {
                $sql .=' and is_active=1';
            }
            $shop_arr = $this->db->get_all($sql, array(":code" => $code));
            foreach ($shop_arr as $val) {
                $store = explode(',', $val['stock_source_store_code']);
                $store[] = $val['send_store_code'];
                $store[] = $val['refund_store_code'];
                if (in_array($code, $store, true)) {
                    $shop_name[] = $val['shop_name'];
                }
            }
        }
        return $shop_name;
    }

    /*
     * 删除记录
     * */

    function delete($store_id) {
        $ret_used = $this->check_is_used($store_id, 'delete');
        if (!empty($ret_used)) {
            $store_name = implode(",", $ret_used);
            return $this->format_ret(-1, '', "仓库已被店铺" . $store_name . "使用，请先清除店铺中设置的发货仓/退货仓/库存来源");
        }
        $used = $this->is_used_by_id($store_id);
        if ($used) {
            return $this->format_ret(-1, array(), '已经在业务系统中使用，不能删除！');
        }
        $store = $this->get_value("select store_code from {$this->table} where store_id=:id", array(':id' => $store_id));
        $store_code = $store['data'];
        //校验单据中的仓库
        $ret = $this->check_store_record($store_code);
        if ($ret['status'] != 1) {
            return $ret;
        }
        $ret = parent::delete(array('store_id' => $store_id));
        return $ret;
    }

    /**
     * 验证仓库在单据中是否存在
     * @param $store_code
     * @return array
     */
    function check_store_record($store_code) {
        $sql = "SELECT 1 FROM oms_sell_return WHERE store_code=:store_code";
        $sql_value[':store_code'] = $store_code;
        $ret = $this->db->get_row($sql, $sql_value);
        if (!empty($ret)) {
            return $this->format_ret('-1', '', '仓库在售后服务单中已使用，不能删除！');
        }
        return $this->format_ret(1, '', '');
    }




    /*
     * 修改纪录
     */

    function update($store, $store_id) {
        $status = $this->valid($store, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret = $this->get_row(array('store_id' => $store_id));
        if (isset($store['store_name']) && $store['store_name'] != $ret['data']['store_name']) {
            $ret = $this->is_exists($store['store_name'], 'store_name');
            if ($ret['status'] > 0 && !empty($ret['data']))
                return $this->format_ret('STORE_ERROR_UNIQUE_NAME');
        }
        $ret = parent::update($store, array('store_id' => $store_id));
        return $ret;
    }

    /**
     * 通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function get_by_field($field_name, $value, $select = "*") {

        $sql = "select {$select} from {$this->table} where {$field_name} = :{$field_name}";

        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /**
     * 选择表通过field_name查询
     *
     * @param  $ :查询field_name
     * @param  $select ：查询返回字段
     * @return array (status, data, message)
     */
    public function table_get_by_field($table, $field_name, $value, $select = "*") {

        $sql = "select {$select} from {$table} where {$field_name} = :{$field_name}";

        $data = $this->db->get_row($sql, array(":{$field_name}" => $value));

        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['store_code']) || !valid_input($data['store_code'], 'required')))
            return 'STORE_ERROR_CODE';
        if (!$is_edit) {
            if (!isset($data['store_name']) || !valid_input($data['store_name'], 'required'))
                return 'STORE_ERROR_NAME';
        }
        return 1;
    }

    /**
     * 返回code=>name数组, 默认仅返回启用的仓库
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @since 2014-11-06
     * @param   array   $filter  过滤条件, 如array('status'=>'1')
     * @return  array
     */
    public function get_code_name($filter = array('status' => 1)) {
        $filter['page_size'] = 10000;
        $result = $this->get_by_page($filter);
        $return = array();
        if (isset($result['data']['data']) && is_array($result['data']['data'])) {
            foreach ($result['data']['data'] as $value) {
                $return[$value['store_code']] = $value['store_name'];
            }
        }
        return $return;
    }

    /**
     * 根据id判断在业务系统是否使用
     * @param int $id
     * @return boolean 已使用返回true, 未使用返回false
     */
    public function is_used_by_id($id) {
        $result = $this->get_value("select store_code from {$this->table} where store_id=:id", array(':id' => $id));
        $code = $result['data'];
        //查询库存流水表和库存批次流水表
        $num = $this->get_num('select * from goods_inv_record where store_code=:code', array(':code' => $code));
        if (isset($num['data']) && $num['data'] > 0) {
            //已经在业务系统使用
            return true;
        } else {
            //尚未在业务系统使用
            return false;
        }
    }

    /**
     * 取出有权限的仓库
     * @return type
     */
    function get_purview_store($fld = 'store_code,store_name') {

        $this->set_user_manage();

        $ret_cfg = load_model('sys/SysParamsModel')->get_val_by_code(array('store_power'));
        $store_power = $ret_cfg['store_power'];

        $sql = "select $fld FROM {$this->table} t where status=1 ";
        if ($this->store_property == 0) {
            $sql .= ' AND store_property=0 ';
        } else if ($this->store_property == 1) {
            $sql .= ' AND store_property=1 ';
        }
        //$login_type = CTX()->get_session('login_type');// 是否分销商登录
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if ((int) $this->is_manage == 0 && $store_power == 1 && $login_type != 2) {
            if (CTX()->is_in_cli()) {
                $store_code_arr = load_model('sys/RoleProfessionModel')->get_user_profession($this->user_id, 2);
                $store_code = '';
                if (!empty($store_code_arr)) {
                    $store_code = implode(",", $store_code_arr);
                }
            } else {
                $store_code = CTX()->get_session('store_code');
            }

            if (empty($store_code)) {
                return array();
            } else {
                $store_code = str_replace(",", "','", $store_code);
                $sql .=" and store_code in ('" . $store_code . "')";
            }
        } else if ($login_type == 2) { //分销商登录
            $sql .= " AND t.is_enable_custom = 1 ";
        }

        $rs = $this->db->get_all($sql);


        return $rs;
    }

    /**
     * 获取实体仓库数据
     */
    function get_entity_store($fld = 'store_code,store_name') {
        $sql = "select $fld FROM {$this->table} t where status=1  and store_property = 1";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    function get_entity_store_select($fld = 'store_code,store_name') {
        $sql = "select $fld FROM {$this->table} t where status=1";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    function get_entity_store_view_select() {
        $rs = $this->get_entity_store();
        $store_arr = array();
        foreach ($rs as $val) {
            $store_arr[$val['store_code']] = $val['store_name'];
        }
        return json_encode(bui_bulid_select($store_arr));
    }

    function set_user_manage() {
        if ($this->is_manage < 0) {
            if (CTX()->is_in_cli()) {
                $user_code = load_model('sys/UserTaskModel')->get_user_code();
                $sql_user = "select user_id,is_manage from sys_user where user_code=:user_code";
                $sql_values = array(':user_code' => $user_code);
                $user_row = $this->db->get_row($sql_user, $sql_values);
                $this->user_id = $user_row['user_id'];
                $this->is_manage = 0;
                $sql_role = "select r.role_code from  sys_role r
                    INNER JOIN sys_user_role u ON r.role_id=u.role_id
                    where r.role_code='manage' AND u.user_id=:user_id ";
                $sql_values2 = array(':user_id' => $this->user_id);
                $role_row = $this->db->get_row($sql_role, $sql_values2);
                if (!empty($role_row)) {
                    $this->is_manage = 1;
                }
            } else {
                $this->is_manage = CTX()->get_session('is_manage');
            }
        }
    }

    /**
     * @todo 取出所有仓库拼装成sql
     */
    function get_sql_all_store($fld = 'store_code') {
        $sql = "select store_code FROM {$this->table} where status=1";
        $ret = $this->db->get_all($sql);
        $store_code_arr = array();
        foreach ($ret as $sub_ret) {
            $store_code_arr[] = $sub_ret['store_code'];
        }
        if (empty($store_code_arr)) {
            $str = " and 1!=1 ";
        } else {
            $str = ' and ' . $fld . ' in (\'' . join("','", $store_code_arr) . '\')';
        }
        return $str;
    }

    /**
     * 取出有权限的仓库
     * @return type
     */
    function get_select($type = 0, $store_property = 0) {
        $this->store_property = $store_property;
        $data = $this->get_purview_store();
        if ($type == 1)
            $data = array_merge(array(array('', '全部')), $data);
        else if ($type == 2)
            $data = array_merge(array(array('', '请选择')), $data);
        else if ($type == 3)
            $data = array_merge(array(array('', '...')), $data);
        return $data;
    }

    /**
     * 取出有权限的仓库
     * @return type
     */
    function get_view_select() {
        $rs = $this->get_purview_store();
        $store_arr = array();
        foreach ($rs as $val) {
            $store_arr[$val['store_code']] = $val['store_name'];
        }
        return json_encode(bui_bulid_select($store_arr));
    }

    //取出设置了分销商货权的仓库（单选）
    function get_fx_select($type = '') {
        $rs = $this->get_fx_store();
        if ($type == 1) {
            $data = array_merge(array(array('store_code' => '', 'store_name' => '请选择')), $rs);
        } else {
            $data = array_merge(array(array('', '请选择')), $rs);
        }
        return $data;
    }

    function get_store_no_contain_wms() {
        $data = $this->get_purview_store();
        $wms_store = load_model('sys/WmsConfigModel')->get_wms_store_all();
        foreach ($data as $k => $val) {
            if (in_array($val['store_code'], $wms_store)) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    //获取配置sap的仓库
    function get_sap_store() {
        $sql = "SELECT efast_store_code FROM sap_config";
        $data = $this->db->get_row($sql);
        $efast_store_code_arr = explode(",", $data['efast_store_code']);
        $store_code_str = "'" . implode("','", $efast_store_code_arr) . "'";

        $sql = "select store_code,store_name FROM {$this->table} t where status=1 AND store_code in ({$store_code_str})";
        $rs = $this->db->get_all($sql);
        return $rs;
    }

    /**
     * 取出有权限的仓库 拼装SQL时用
     * $fld sql字段名 多表查的要传参如r1.store_code ,
     * $req_code 客户端传来的store_code（要去掉客户端传来没权限的store_code）
     * @return array()
     */
    function get_sql_purview_store($fld = 'store_code', $req_code = null, $fun = 'get_purview_store', $store_property = 0) {
        $this->store_property = $store_property;
//        $this->set_user_manage();
//        if ((int) $this->is_manage == 1 && empty($req_code) && !in_array($fun, array('get_fx_store', 'get_entity_store'))) {
//            return '';
//        }

        $ret = $this->$fun();
        $req_store_code_arr = array();
        if (!empty($req_code)) {
            $req_store_code_arr = explode(',', $req_code);
        }
        $store_code_arr = array();
        foreach ($ret as $sub_ret) {
            $store_code_arr[] = $sub_ret['store_code'];
        }
        if (empty($store_code_arr)) {
            $str = " and 1!=1 ";
        } else {
            if (!empty($req_store_code_arr)) {
                $store_code_arr = array_intersect($store_code_arr, $req_store_code_arr);
            }
            if (empty($store_code_arr)) {
                $str = " and 1!=1 ";
            } else {
                $str = ' and ' . $fld . ' in ("' . join('","', $store_code_arr) . '")';
            }
        }
        return $str;
    }

    /**
     * 取出单选的仓库
     * @return type
     */
    function get_select_purview_store($type = 1) {

        $ret = $this->get_purview_store();
        if ($type == 1) {
            $arr = array('store_code' => '', 'store_name' => '全部');
            array_unshift($ret, $arr);
        }
        return $ret;
    }

    /**
     *
     * 方法名                               api_store_update
     *
     * 功能描述                           更新仓库数据
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-15
     * @param       array $param
     *              array(
     *                  必选: 'store_code', 'store_name', 'allow_negative_inv',
     *                  可选: 'shop_contact_person', 'contact_person', 'contact_phone',
     *                       'country', 'province', 'city', 'district', 'street',
     *                       'address', 'zipcode', 'message', 'message2'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_store_update($param) {
        $key_required = array(
            's' => array('store_code', 'store_name'),
            'i' => array('allow_negative_inv')
        );
        //可选字段
        $key_option = array(
            's' => array('shop_contact_person', 'contact_person', 'contact_phone', 'country', 'province', 'city', 'district', 'street', 'address', 'zipcode', 'message', 'message2')
        );

        $arr_required = array();
        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $arr_required, TRUE);
        //必填项检测通过
        if (TRUE == $ret_required['status']) {
            $arr_option = array();
            //提取可选字段中已赋值数据
            $ret_option = valid_assign_array($param, $key_option, $arr_option);

            //合并数据
            $arr_deal = array_merge($arr_required, $arr_option);

            //清空无用数据
            unset($arr_required);
            unset($arr_option);

            //检测客户提交的仓库是否已经存在
            $ret = $this->is_exists($arr_deal['store_code']);
            if (1 == $ret['status']) {
                unset($arr_deal['store_code']);
                //更新数据
                $ret = $this->update($arr_deal, $ret['data']['store_id']);
            } else {
                //插入数据
                $ret = $this->insert($arr_deal);
            }
            return $ret;
        } else {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
    }

    /**
     *
     * 方法名                               api_store_list_get
     *
     * 功能描述                           获取仓库列表
     *
     * @author      BaiSon PHP R&D
     * @date        2016-02-25
     * @param       array $param
     *              array(
     *                  必选: 'user_code',
     *                  可选: 'page', 'page_size',
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_store_list_get($param) {
        $key_required = array(
            's' => array('user_code'),
        );
        //可选字段
        $key_option = array(
            's' => array('page', 'page_size','store_code','store_name','lastchanged_start','lastchanged_end','store_type_code')
        );

        $r_required = array();
        $r_option = array();

        //验证必选字段是否为空并提取必选字段数据
        $ret_required = valid_assign_array($param, $key_required, $r_required, TRUE);
        if ($ret_required['status'] === FALSE) {
            return $this->format_ret("-10001", $param, "API_RETURN_MESSAGE_10001");
        }
        //提取可选字段中已赋值数据
        $ret_option = valid_assign_array($param, $key_option, $r_option);
        $user = $r_required;
        $arr_option = $r_option;
        //销毁无用数据
        unset($r_required, $r_option, $param);

        //检查单页数据条数是否超限
        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }

        //用户校验
        $ret = $this->check_user($user, 0);
        if ($ret['status'] != 1) {
            return $ret;
        }
        //获取用户角色
        $role = load_model('sys/EfastUserModel')->get_role_list($ret['data'], array());
        $role = $role['data']['data'];
        if (empty($role)) {
            return $this->format_ret(-1, '', 'API_USER_NOT_ROLE');
        }
        
        $select = "s.store_code,s.store_name,s.allow_negative_inv,s.store_type_code,s.lastchanged";
        $sql_main = "FROM {$this->table} s WHERE s.`status`=1";
        $sql_values = array();
        $role_arr = array_column($role, 'role_code');
        if (!in_array('manage', $role_arr)) {
            $role_str = $this->arr_to_in_sql_value($role_arr, 'role_code', $sql_values);
            $arr_shop = load_model('sys/SysParamsModel')->get_val_by_code(array('store_power'));
            if ($arr_shop['store_power'] == 1) {
                $sql_main = "FROM {$this->table} s LEFT JOIN sys_role_profession rp ON s.store_code=rp.relate_code WHERE rp.profession_type=2 AND rp.role_code in ({$role_str})  AND s.`status`=1";
            }
        }
        $this->get_record_sql_where($arr_option,$sql_main, $sql_values, 's.');

        $sql_main .= ' GROUP BY s.store_code';

        $store = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select, TRUE);
        if (empty($store)) {
            return $this->format_ret("-10002", $param, "API_RETURN_MESSAGE_10002");
        }
        $type_arr = array_unique(array_column($store['data'],'store_type_code'));
        if(!empty($type_arr)){
            $type_sql_string = $this->arr_to_in_sql_value($type_arr,'store_type_code',$type_sql_values);
            $type_ret = $this->db->get_all('select type_code,type_name from base_store_type where type_code in('.$type_sql_string.')',$type_sql_values);
            $store_type_arr = load_model('util/ViewUtilModel')->get_map_arr($type_ret, 'type_code');
        }
        foreach ($store['data'] as &$value){
            if(isset($store_type_arr[$value['store_type_code']])){
                $value['store_type_name'] = $store_type_arr[$value['store_type_code']]['type_name'];
            }else{
                $value['store_type_name'] = '';
            }
        }
        //御城河日志
        load_model('common/TBlLogModel')->set_log_multi($store, '开放接口列表', 'sendOrder');
        //返回请求数据
        return $this->format_ret(1, $store);
    }

    /**
     * 用户校验
     * @return 用户id
     */
    function check_user($user, $is_check_pwd = 1) {
        $sql = "select user_id,user_code,password from sys_user where user_code = :user_code AND status=1";
        $row = ctx()->db->get_row($sql, array(':user_code' => $user['user_code']));
        if (empty($row)) {
            return $this->format_ret(-10004, '', 'API_RETURN_MESSAGE_10004');
        }
        if ($is_check_pwd == 1) {
            $pwd = load_model('sys/EfastUserModel')->encode_pwd($user['password']);
            if ($pwd != $row['password']) {
                return $this->format_ret(-10004, '', 'API_RETURN_MESSAGE_10004');
            }
        }
        return $this->format_ret(1, $row['user_id']);
    }

    function get_store_by_code_arr($store_code) {
        $store_code = deal_strs_with_quote($store_code);
        $sql = "SELECT store_name FROM base_store WHERE store_code in({$store_code})";
        $store_name_arr = $this->db->get_all($sql);
        $store_name = array();
        foreach ($store_name_arr as $val) {
            $store_name[] = $val['store_name'];
        }
        return $store_name;
    }

    function get_by_store_code_type($type) {
        $type_str = explode(",", $type);
        $type_arr = implode("','", $type_str);
        $sql = "select store_code from base_store where store_type_code in ('{$type_arr}')";
        $store_code = $this->db->get_all($sql, array('store_type_code' => $type));
        $store_arr = array();
        if (!empty($store_code)) {
            foreach ($store_code as $store) {
                $store_arr[] = $store['store_code'];
            }
        }
        return $store_arr;
    }

    /**
     * 供库存策略选择仓库使用
     */
    function get_store_select($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} bs WHERE status=1 AND bs.store_property=0";

        if (isset($filter['store_name']) && $filter['store_name'] != '') {
            $sql_main .= " AND bs.store_name LIKE :store_name ";
            $sql_values[':store_name'] = "%{$filter['store_name']}%";
        }

        if (isset($filter['store_property']) && $filter['store_property'] != '') {
            $sql_main .= " AND bs.store_property = :store_property ";
            $sql_values[':store_property'] = $filter['store_property'];
        }

        $select = 'bs.store_name,bs.store_code';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_fx_store($type = 0) {
        $sql = "select store_code,store_name,custom_code from {$this->table} where 1 AND status = 1 AND store_property = 0 AND is_enable_custom = 1";
        $store = $this->db->get_all($sql);
        if (empty($store)) {
            return array();
        }
        $new_store = array();
        $i = 0;
        foreach ($store as $key => $val) {
            $new_store[$i]['store_code'] = $val['store_code'];
            $new_store[$i]['store_name'] = $val['store_name'];
            $i ++;
        }
        if ($type == 1) {
            $new_store = array_merge(array(array('store_code' => '', 'store_name' => '请选择')), $new_store);
        }
        return $new_store;
    }

    function get_fx_store_sql() {
        $sql = "select store_code from {$this->table} where 1 AND status = 1 AND store_property = 0 AND is_enable_custom = 1";
        $store = $this->db->get_all_col($sql);
        if (!empty($store)) {
            $store = implode(",", $store);
        }
        return $store;
    }

    /**
     * @todo 根据店铺代码获取发货仓库
     */
    function get_store_by_shop($shop_code) {
        $sql = "SELECT store_code,store_name FROM base_shop sh INNER JOIN base_store st ON sh.send_store_code=st.store_code WHERE sh.shop_code=:shop_code";
        $ret_store = $this->db->get_row($sql, array(':shop_code' => $shop_code));
        return array($ret_store);
    }

    /**
     * @todo 获取系统中存在的所有仓库代码
     */
    function get_all_store() {
        $new_db = array();
        $sql = "SELECT store_code FROM {$this->table}";
        $db = $this->db->get_all($sql);
        foreach ($db as $key => $store) {
            $new_db[$key] = $store['store_code'];
        }
        return $new_db;
    }

    function erp_store_select_action($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} bs WHERE status=1 AND bs.store_property=0";

        if (isset($filter['store_name']) && $filter['store_name'] != '') {
            $sql_main .= " AND bs.store_name LIKE :store_name ";
            $sql_values[':store_name'] = "%{$filter['store_name']}%";
        }

        if (isset($filter['store_property']) && $filter['store_property'] != '') {
            $sql_main .= " AND bs.store_property = :store_property ";
            $sql_values[':store_property'] = $filter['store_property'];
        }
        $sql = "SELECT join_sys_code FROM mid_api_join where param_val1=2 AND join_sys_type =1";
        $erp_store_data = $this->db->get_all($sql);
        $erp_store_data_column = array_column($erp_store_data, 'join_sys_code');
        $store_str = $this->arr_to_in_sql_value($erp_store_data_column, 'store_code', $sql_values);
        $sql_main .= " AND bs.store_code IN ({$store_str})";
        $select = 'bs.store_name,bs.store_code';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    /**
     * 生成单据查询sql条件语句
     * @param array $filter 参数条件
     * @param string $sql_main sql主体
     * @param string $sql_values sql映射值
     * @param string $ab 表别名
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values, $ab = '') {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '' || $val === NULL) {
                continue;
            }
            if ($key == 'lastchanged_start') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'lastchanged_end') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else {
                $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }
    }

}
