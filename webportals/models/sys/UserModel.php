<?php

/**
 * 用户相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');
require_lang('sys');
require_lib('net/HttpEx');

require_model('sys/OrgModel');

class UserModel extends TbModel {

    function get_table() {
        return 'osp_user';
    }

    /**
     * 根据条件查询数据,分页列表数据
     */
    function get_by_page($filter) {

        $sql_join = "";     //用户详细信息关联表
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";

        //关键字
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (rl.user_code LIKE '%" . $filter['keyword'] .
                    "%' OR rl.user_name LIKE '%" . $filter['keyword'] . "%') ";
        }
        //有效用户条件
        if (isset($filter['user_active']) && $filter['user_active'] != '') {
            $sql_main .= " AND (rl.user_active =" . $filter['user_active'] . ")";
        }
        //性别条件
        if (isset($filter['user_sex']) && $filter['user_sex'] != '') {
            $sql_main .= " AND (rl.user_sex =" . $filter['user_sex'] . ")";
        }
        //组织机构条件
        if (isset($filter['orgid']) && $filter['orgid'] != '') {
            $sql_main .= " AND (rl.user_org_code =" . $filter['orgid'] . ")";
        }
        //排序条件（框架暂时不支持）
        if (isset($filter['__sort']) && $filter['__sort'] != '') {
            $filter['__sort_order'] = $filter['__sort_order'] == '' ? 'asc' : $filter['__sort_order'];
            $sql_main .= ' order by ' . trim($filter['__sort']) . ' ' . $filter['__sort_order'];
        }
        //构造排序条件
        $sql_main .= " order by user_code asc";

        $select = 'rl.*';

        $data = $this->get_page_from_sql($filter, $sql_main, "", $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        //处理关联代码表
        filter_fk_name($ret_data['data'], array('user_highedrup|osp_user_id', 'user_org_code|osp_org_id'));

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('user_id' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('user_highedrup|osp_user_id', 'user_org_code|osp_org_id', 'user_create_code|osp_user_id', 'user_update_code|osp_user_id'));

        return $this->format_ret($ret_status, $data);
    }

    function reset_pwd($user_id) {
        $pwd_str = $this->generatePassword(8);
        $pwd = $this->getMd5_ToBase64($pwd_str);

        $ret = parent::update(array('user_login_pwd' => $pwd), array('user_id' => $user_id));
        if ($ret['status'] < 1) {
            return $ret;
        }
        return $this->format_ret($ret['status'], $pwd_str);
    }

    function generatePassword($length = 8) {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'), array('!', '@', '$', '%', '^', '&', '*'));
        shuffle($chars);
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[$i];
        }
        if (preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $password) == true) {
            return $password;
        }
        return $this->generatePassword($length);
    }

    /*
     * 修改纪录
     */

    function update($user, $id, $user_sys) {
        $status = $this->valid($user, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('user_id' => $id));

        //添加修改人和修改日期
        $user['user_update_code'] = CTX()->get_session("user_id");
        $user['user_update_date'] = date("Y-m-d h:i:sa");
        if ($ret['data']['user_create_code'] == "") {
            $user['user_create_code'] = CTX()->get_session("user_id");
            $user['user_create_date'] = date("Y-m-d h:i:sa");
        }
        $ret = parent::update($user, array('user_id' => $id));

        //同时更新sys_user表
        $data = $this->db->create_mapper('sys_user')->update($user_sys, array('user_id' => $id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['user_code']) || !valid_input($data['user_code'], 'required')))
            return USER_ERROR_CODE;
        if (!isset($data['user_name']) || !valid_input($data['user_name'], 'required'))
            return USER_ERROR_NAME;

        return 1;
    }

    private function is_exists($value, $field_name = 'user_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    //获取关联表列表
    function get_codelist($table, $fields, $fiter) {
        if (isset($table)) {
            $strSQL = " from " . $table;
            if (isset($fields)) {
                $strSQL = "select " . $fields[0] . " as id," . $fields[1] . " as name" . $strSQL;
                if (isset($fiter) && $fiter != "") {
                    $strSQL = $strSQL . " where " . $fiter;
                }
            }
            if ($strSQL != "") {
                $ret = $this->db->get_all($strSQL);
                $data = array(
                    array('id' => '', 'name' => '请选择')
                );
                return array_merge($data, $ret);
            } else {
                $GLOBALS['context']->log_error(__FUNCTION__ . ":{$key} not found in {$table}");
                return array();
            }
        }
    }

    function get_role_list($user_id, $filter) {
        if (empty($user_id)) {
            return $this->format_ret(OP_ERROR);
        }
        $select = '*';
        $sql_main = "FROM sys_user_role AS su,sys_role AS sr WHERE su.role_id=sr.role_id and su.user_id={$user_id}";
        $data = $this->get_page_from_sql($filter, $sql_main, $select);

        foreach ($data['data'] as $k => $sub_data) {
            $data['data'][$k]['role_code_txt'] = $sub_data['role_code'] . '<input type="hidden" value="' . $sub_data['role_id'] . '"/>';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        filter_fk_name($ret_data['data'], array('role_id'));

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_role_list1($user_id, $filter) {
        if (empty($user_id)) {
            return $this->format_ret(OP_ERROR);
        }
        $select = '*';
        $sql_main = "FROM sys_user_role WHERE user_id={$user_id}";
        $data = $this->get_page_from_sql($filter, $sql_main, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        filter_fk_name($ret_data['data'], array('role_id'));

        return $this->format_ret($ret_status, $ret_data);
    }

    function get_role_list_noset($user_id, $filter) {
        //echo '<hr/>$user_id<xmp>'.var_export($user_id,true).'</xmp>';die;
        if (empty($user_id)) {
            return $this->format_ret(OP_ERROR);
        }
        $wh = '';
        if (!empty($filter['keyword'])) {
            $wh = "(role_code like '%{$filter['keyword']}%' or role_name like '%{$filter['keyword']}%') and ";
        }
        $user_id = (int) $user_id;
        $select = '*';
        $sql_main = "from sys_role where {$wh} role_id not in(select role_id from sys_user_role where user_id = {$user_id})";
        //echo $sql_main;die;
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        foreach ($data['data'] as $k => $sub_data) {
            $data['data'][$k]['role_code_txt'] = $sub_data['role_code'] . '<input type="hidden" value="' . $sub_data['role_id'] . '"/>';
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function delete_role($user_id, $role_id_arr) {
        $where = array();
        $model = M('sys_user_role');
        foreach ($role_id_arr as $id) {
            $where = array('user_id' => $user_id, 'role_id' => $id);
            $model->delete($where);
        }

        return $this->format_ret(1, '', 'delete_success');
    }

    function add_role($user_id, $role_id_arr) {
        $rs_new = array();
        $model = M('sys_user_role');
        foreach ($role_id_arr as $id) {
            $rs_new[] = array('user_id' => $user_id, 'role_id' => $id);
        }

        return $model->insert($rs_new);
    }

    /**
     * 用户登录验证
     * @param type $username
     * @param type $password
     */
    function checklogOn($username, $password, $captcha) {

        if ($captcha != "") {
            if (CTX()->get_session("captcha_code") != strtolower($captcha)) {
                return $this->format_ret("-1", "", 'CaptchaError'); //验证码错误
            }
        }

        $ret_check = $this->check_sys_is_login($username);
        if ($ret_check['status'] < 1) {
            return $ret_check;
        }


        $password = $this->getMd5_ToBase64($password);  //获取md5密码
        $ret = $this->get_row(array('user_code' => $username));

        if (!empty($ret["data"])) {
            $strSQL = "select * from sys_user left join osp_user on osp_user.user_code=sys_user.user_code "
                    . "where sys_user.user_code=:user_code and osp_user.user_login_pwd=:password";
            $retRow = $this->db->get_row($strSQL, array(":user_code" => $username, ":password" => $password));

            if (!empty($retRow)) {
                if ($retRow['is_active'] != "1")
                    return $this->format_ret("-1", "", 'UserDisabled'); //用户已经禁用
                if ($retRow['user_out'] == "1")
                    return $this->format_ret("-1", "", 'UserOut'); //用户已经离职

                CTX()->set_session("IsLogin", true); // 登录状态
                CTX()->set_session("user_id", $retRow['user_id']); //用户ID
                CTX()->set_session("user_code", $retRow['user_code']); // 用户代码
                CTX()->set_session("user_name", $retRow['user_name']); // 用户姓名
                CTX()->set_session("user_org_code", $retRow['user_org_code']); // 用户所属机构
                CTX()->set_session("user_admin", $retRow['is_admin']); // 是否超级管理员 

                return $this->format_ret("1", "", 'success');
            }else {
                //密码错误
                $this->get_PortalData();
                return $this->format_ret("-1", "", 'PwdError');
            }
        } else {
            //用户不存在
            $this->get_PortalData();
            return $this->format_ret("-1", "", 'UserNotFind');
        }
    }

    private function check_sys_is_login($kh_id, $user_code) {
        $ip = $this->get_real_ip();
        $force_logout = require_conf('force_logout');
        if (in_array($ip, $force_logout['ip'])) {
            return $this->format_ret(-1, '', '禁止登录IP，请联系管理员');
        }
        if (isset($force_logout['user'][$kh_id]) && in_array($user_code, $force_logout['operate_user'][$kh_id])) {
            return $this->format_ret(-1, '', '禁止登录用户，请联系管理员');
        }
        return $this->format_ret(1);
    }

    //下载用户机构数据
    function get_PortalData() {
        $PortalService = CTX()->get_app_conf('PortalService');
        if ($PortalService != "") {
            //$HttpEx=new HttpEx($PortalService);
            $strSQL = "select timer_value from osp_id_timer order by timer_id desc";
            $timer = $this->db->get_row($strSQL);
            if (empty($timer)) {
                $PortalService = $PortalService . "?timer=0";
            } else {
                $PortalService = $PortalService . "?timer=" . $timer["timer_value"];
            }
            $result = file_get_contents($PortalService);
            if (!empty($result)) {
                $jsonResult = json_decode($result);
                if ($jsonResult->state == "success") {
                    CTX()->log_error('begin to sync org...');
                    //同步组织机构
                    $this->syn_OrgList($jsonResult->data->orgList);
                    CTX()->log_error('end to sync org...');
                    CTX()->log_error('begin to sync user...');
                    //同步用户信息
                    $this->syn_OrgUser($jsonResult->data->userList);
                    CTX()->log_error('end to sync user...');
                    //更新时间戳
                    //$jsonResult["timer"]
                    $data = array(
                        'timer_value' => $jsonResult->timer
                    );
                    $this->db->create_mapper('osp_id_timer')->insert($data);
                }
            }
        }
    }

    //同步组织机构
    function syn_OrgList($orgList) {
        foreach ($orgList as $list) {
            $strSQL = "select org_id from osp_organization where org_id='{$list->organizationId}'";
            $ExistRow = $this->db->get_row($strSQL);
            if ($ExistRow["org_id"] != "") {
                //表示已经存在
                if ($list->isDelete == "0") {  //机构已经停用
                    $list->name = $list->name . "(停用)";
                }
                CTX()->log_error('update org:' . $list->name);
                $data = array(
                    'org_name' => $list->name,
                    'org_code' => $list->name,
                    'org_parent_id' => $list->parentOrganizationId,
                    'org_active' => $list->isDelete,
                    'org_level' => $list->type2,
                    'org_update_user' => "2", //默认修改用户为admin
                    'org_update_date' => date("Y-m-d h:i:sa"),
                );
                $this->db->create_mapper('osp_organization')->update($data, array('org_id' => $list->organizationId));
            } else {
                if ($list->isDelete != "0") {
                    /* $strSQL="insert into osp_organization(org_id,org_name,org_code,org_parent_id,org_active,org_level) "
                      . "values ('{$list["organizationId"]}','{$list["name"]}','{$list["name"]}','{$list["parentOrganizationId"]}'"
                      . "'{$list["isDelete"]}','{$list["type2"]})"; */
                    CTX()->log_error('add org:' . $list->name);
                    $data = array(
                        'org_id' => $list->organizationId,
                        'org_name' => $list->name,
                        'org_code' => $list->name,
                        'org_parent_id' => $list->parentOrganizationId,
                        'org_active' => $list->isDelete,
                        'org_level' => $list->type2,
                        'org_create_user' => "2", //默认修改用户为admin
                        'org_create_date' => date("Y-m-d h:i:sa"),
                        'org_update_user' => "2", //默认修改用户为admin
                        'org_update_date' => date("Y-m-d h:i:sa"),
                    );
                    $this->db->create_mapper('osp_organization')->insert($data);
                }
            }
        }
        //更新IsLeaf操作
        $strSQL = "UPDATE osp_organization SET ORG_IS_LEAF='1' WHERE ORG_ID IN (SELECT ORG_ID FROM ("
                . "(SELECT ORG_ID FROM osp_organization A WHERE NOT EXISTS "
                . "(SELECT 1 FROM osp_organization B WHERE A.ORG_ID = B.ORG_PARENT_ID))C ))";
        $this->db->query($strSQL);

        $strSQL = "UPDATE osp_organization SET ORG_IS_LEAF='0' WHERE ORG_ID IN (SELECT ORG_ID FROM ("
                . "(SELECT ORG_ID FROM osp_organization A WHERE EXISTS "
                . "(SELECT 1 FROM osp_organization B WHERE A.ORG_ID = B.ORG_PARENT_ID))C ))";
        $this->db->query($strSQL);
    }

    //同步用户
    function syn_OrgUser($userList) {
        CTX()->log_error('user count:' . count($userList));
        foreach ($userList as $list) {
            $strSQL = "select user_id from sys_user where user_id='{$list->userId}'";
            $ExistRow = $this->db->get_row($strSQL);
            if ($ExistRow["user_id"] != "") {
                if (strtolower($list->screenName) != "admin") {
                    CTX()->log_error('update user:' . $list->screenName);
                    $data = array(
                        'user_code' => $list->screenName,
                        'user_name' => $list->userName,
                        'email' => $list->emailAddress,
                        'is_active' => $list->isDelete
                    );
                    $this->db->create_mapper('sys_user')->update($data, array('user_id' => $list->userId));
                    $data_other = array(
                        'user_code' => $list->screenName,
                        'user_name' => $list->userName,
                        'user_org_code' => $list->orgUser,
                        //'user_login_pwd'=>$list->password,  密码不需要同步
                        'user_highedrup' => $list->leaderUserId,
                        'user_email' => $list->emailAddress,
                        'user_active' => $list->isDelete,
                        'user_update_code' => "2", //默认修改用户为admin
                        'user_update_date' => date("Y-m-d h:i:sa"),
                    );
                    $this->db->create_mapper('osp_user')->update($data_other, array('user_id' => $list->userId));
                }
            } else {  //新增用户操作
                if ($list->isDelete != "0") {
                    CTX()->log_error('add user:' . $list->screenName);
                    $data = array(
                        'user_id' => $list->userId,
                        'user_code' => $list->screenName,
                        'user_name' => $list->userName,
                        'email' => $list->emailAddress,
                        'is_active' => $list->isDelete
                    );
                    //判断是否为admin超级管理员管理
                    if (strtolower($list->screenName) == "admin") {
                        $data["is_admin"] = "1";  //默认为超级管理员
                        $data["user_code"] = "admin";
                        $data["is_active"] = "1";
                    }
                    $this->db->create_mapper('sys_user')->insert($data);
                    $data_other = array(
                        'user_id' => $list->userId,
                        'user_code' => $list->screenName,
                        'user_name' => $list->userName,
                        'user_org_code' => $list->orgUser,
                        'user_login_pwd' => $list->password,
                        'user_highedrup' => $list->leaderUserId,
                        'user_email' => $list->emailAddress,
                        'user_active' => $list->isDelete,
                        'user_create_code' => "2", //默认创建用户为admin
                        'user_create_date' => date("Y-m-d h:i:sa"),
                        'user_update_code' => "2", //默认修改用户为admin
                        'user_update_date' => date("Y-m-d h:i:sa"),
                    );
                    //判断是否为admin超级管理员管理
                    if (strtolower($list->screenName) == "admin") {
                        $data_other["user_login_pwd"] = "z3muat26YK0Bg0c1m9FE0g==";  //默认为超级管理员
                        $data_other["user_admin"] = "1";  //默认为超级管理员
                        $data_other["user_code"] = "admin";
                        $data_other["user_active"] = "1";
                    }
                    $this->db->create_mapper('osp_user')->insert($data_other);
                }
            }
        }
    }

    //转换数组
    function object_to_array($obj) {
        $arr = array();
        if (is_object($obj) || is_array($obj)) {
            foreach ($obj as $key => $val) {
                if (!is_object($val)) {
                    if (is_array($val)) {
                        $arr[$key] = object_to_array($val);
                    } else {
                        $arr[$key] = $val;
                    }
                } else {
                    $arr[$key] = object_to_array($val);
                }
            }
        }
        return $arr;
    }

    /**
     * 获取md5加密(主要是为了以后匹配portal接口同步过来的密码验证)
     */
    public function getMd5_ToBase64($str) {
        return base64_encode(pack("H32", md5($str)));
    }

    //默认调用基类方法加载菜单
    function get_top_menu() {
        return load_model('sys/PrivilegeModel')->get_top_menu(true);
    }

    function get_menu_tree() {
        return load_model('sys/PrivilegeModel')->get_menu_tree(true);
    }

    //验证用户密码
    function getuser_pwd($userid) {
        $ret = $this->get_row(array('user_id' => $userid));
        if (isset($ret)) {
            return $ret['data']["user_login_pwd"];
        } else {
            return "";
        }
    }

    //修改密码
    function updatepwd($userid, $pwd) {
        $data = array('user_login_pwd' => $pwd);
        //$result=$this->db->create_mapper('sys_user')->update($data,array('user_id'=>$userid));
        $result = parent::update($data, array('user_id' => $userid));
        if ($result) {
            return $this->format_ret("1", "", 'update_success');
        } else {
            return $this->format_ret("-1", '', 'update_error');
        }
    }

    private function get_real_ip() {
        $ip = false;
        if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) {
                array_unshift($ips, $ip);
                $ip = FALSE;
            }
            for ($i = 0; $i < count($ips); $i++) {
                if (!preg_match("/^(10|172\.16|192\.168)\./", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    function user_remove_role($user_id, $role_id_list) {
        $user_id = (int) $user_id;
        $sql = "delete from sys_user_role where user_id = $user_id and role_id in($role_id_list)";
        $ret = CTX()->db->query($sql);
        return $ret;
    }

    function user_add_role($user_id, $role_id_list) {
        if (empty($role_id_list)) {
            return;
        }
        $user_id = (int) $user_id;
        $role_id_arr = explode(',', $role_id_list);
        $ins_arr = array();
        foreach ($role_id_arr as $role_id) {
            $ins_arr[] = "({$user_id},{$role_id})";
        }
        $sql = "insert ignore sys_user_role(user_id,role_id) values" . join(',', $ins_arr);
        $ret = CTX()->db->query($sql);
        return $ret;
    }

}
