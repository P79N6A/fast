<?php
require_model('tb/TbModel');

class ShortcutMenuModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'sys_shortcut_menu';
    }
    
     /**
      * 根据条件查询数据
      * @param type $filter
      * @return type
      */
    function get_by_page($filter) {
        $select = ' r3.action_id, r1.action_name AS cate_name, r2.action_name AS group_name, r3.action_name, r3.action_code';
        $sql_values = array();
        $sql_main = "FROM sys_action r1
            INNER JOIN sys_action r2 ON r2.parent_id=r1.action_id
            INNER JOIN sys_action r3 ON r3.parent_id=r2.action_id
            WHERE 1";
        
        //获取有权限菜单
        $prv_menus_url = $this->_get_prv_menus_url();
        $prv_action_ids = array_column($prv_menus_url, 'action_id');
        if (empty($prv_action_ids)){
            $sql_main .= " AND 1=2";
        }else{
            $action_ids_str = implode(',', $prv_action_ids);
            $sql_main .= " AND r3.action_id IN ({$action_ids_str})";
        }

        //搜索条件
        trim_array($filter);
        if (isset($filter['cate_name']) && $filter['cate_name'] != '') {
            $sql_main .= " AND r1.action_name LIKE :cate_name";
            $sql_values[':cate_name'] = '%' . $filter['cate_name'] . '%';
        }
        if (isset($filter['group_name']) && $filter['group_name'] != '') {
            $sql_main .= " AND r2.action_name LIKE :group_name";
            $sql_values[':group_name'] = '%' . $filter['group_name'] . '%';
        }
        if (isset($filter['action_name']) && $filter['action_name'] != '') {
            $sql_main .= " AND r3.action_name LIKE :action_name";
            $sql_values[':action_name'] = '%' . $filter['action_name'] . '%';
        }
        
        $sql_main .= " ORDER BY r1.sort_order ASC,r2.sort_order ASC,r3.sort_order ASC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        
        $sql_action = "SELECT action_id FROM sys_shortcut_menu WHERE user_id=:user_id";
        $shortcut_menu = $this->db->get_all_col($sql_action, array(':user_id'=>CTX()->get_session('user_id')));
        $shortcut_menu_new = array_flip($shortcut_menu);
        foreach ($data['data'] as &$value) {
            $value['status'] = isset($shortcut_menu_new[$value['action_id']]) ? 1: 0;
        }
        return $this->format_ret(1, $data);
    }
    //获取有权限的菜单集合 (第三级菜单url)
    private function _get_prv_menus_url() {
        //获取有权限菜单
        $menu_cote = load_model('sys/EfastPrivilegeModel')->get_menu_tree(true);
        $menu = array();
        foreach ($menu_cote as $val) {
            if (!isset($val['_child'])){
                continue;
            }
            foreach ($val['_child'] as $val_2) {
                if (!isset($val_2['_child'])){
                    continue;
                }
                $menu = array_merge($menu, $val_2['_child']);
            }
        }
        $new_menu = array_column($menu, null, 'action_id');
        return $new_menu;
    }
    /**
     * 更新快捷菜单启用状态
     * @param type $action_code
     * @param type $type
     */
    function update_active($action_code, $type) {
        if (!in_array($type, array(0, 1)) || empty($action_code)) {
            return $this->format_ret(-1, '', 'error_params');
        }
        $action_code = htmlspecialchars_decode($action_code);
        //验证权限
        if (!load_model('sys/PrivilegeModel')->check_priv($action_code)){
            return $this->format_ret(-1, '', '无权访问');
        }
        //验证菜单
        $sql = "SELECT * FROM sys_action WHERE action_code=:action_code AND status=1";
        $sql_values = array('action_code'=>$action_code);
        $action_info = $this->db->getRow($sql, $sql_values);
        if (empty($action_info)) {
             return $this->format_ret(-1, '', '菜单不存在');
        }
        //加入和移出快捷菜单
        $user_id = CTX()->get_session('user_id');
        $params = array('action_id' => $action_info['action_id'], 'user_id' =>$user_id);
        if (1 == $type){
            //添加快捷菜单
            $ret = parent::insert($params);
        }else{
            //删除快捷菜单
            $ret = parent::delete($params);
        }
        if ($ret['status'] == 1) {
            $module = '系统管理'; //模块名称
            $yw_code = ''; //业务编码
            $operate_xq = $type == 1 ? '加入快捷菜单' : '移出快捷菜单';
            $operate_xq .= "：{$action_info['action_name']}"; //操作详情
            $operate_type = '菜单';
            $log = array('user_id' => $user_id, 'user_code' => CTX()->get_session('user_code'), 'ip' => '', 'add_time' => date('Y-m-d H:i:s'), 'module' => $module, 'yw_code' => $yw_code, 'operate_type' => $operate_type, 'operate_xq' => $operate_xq);
            $ret1 = load_model('sys/OperateLogModel')->insert($log);
        }
        return $ret;
    }
    
    public function get_by_user_id() {
        //获取快捷菜单
        $user_id = CTX()->get_session('user_id');
        $sql = "SELECT action_id FROM sys_shortcut_menu WHERE user_id=:user_id ORDER BY action_id DESC";
        $pre_menu = $this->db->get_all_col($sql, array(':user_id'=>$user_id));
        $menu = array();
        if (empty($pre_menu)){
            return $menu;
        }
        //获取有权限菜单
        $prv_menus_url = $this->_get_prv_menus_url();
        foreach ($pre_menu as $val) {
            if (isset($prv_menus_url[$val])){
                $menu[] = $prv_menus_url[$val];
            }
        }
        return $menu;
    }
}
