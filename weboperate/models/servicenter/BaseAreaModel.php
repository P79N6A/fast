<?php

/**
 * 服务中心 地址库
 * @author
 *
 */
require_model('tb/TbModel');
class BaseAreaModel extends TbModel {

    function get_table() {
        return 'base_area_new';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table} WHERE 1";
        if (isset($filter['name']) && $filter['name'] != '') {
            $sql_main .= " AND name LIKE :name";
            $sql_values[':name'] = $filter['name'] . '%';
        }
        if (isset($filter['zip']) && $filter['zip'] != '') {
            $sql_main .= " AND zip LIKE :zip";
            $sql_values[':zip'] = $filter['zip'] . '%';
        }
        if (isset($filter['type']) && $filter['type'] != '') {
            $sql_main .= " AND type = :type";
            $sql_values[':type'] = $filter['type'];
        }else{
            if(!isset($filter['area_type'])){
                $sql_main .= " AND type = :type";
                $sql_values[':type'] = '1';
            }
        }
        if (isset($filter['area_type']) && $filter['area_type'] == 'child' && empty($filter['name']) && empty($filter['zip'])) {
            $sql_main .= " AND parent_id = :parent_id";
            $sql_values[':parent_id'] = $filter['area_id'];
        }

        if (isset($filter['area_type']) && $filter['area_type'] == 'parent' && empty($filter['name']) && empty($filter['zip'])) {
            $sql = "select parent_id from {$this->table} where id = " . $filter['area_id'];
            $parent_id_v = $this->db->getOne($sql);
            $sql_main .= " AND parent_id = " . $parent_id_v;
        }
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        $type_map = array('1' => '国家','2' => '省/自治区/直辖市', '3' => '地级市', '4' => '县/市(县级市)/区','5' => '街道');
        $id_arr = array();
        foreach($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['type_txt'] = isset($type_map[$row['type']])?$type_map[$row['type']]:'';
            $id_arr[] = $row['id'];
        }
        if (!empty($id_arr)) {
            $id_list = join(',', $id_arr);
            $sql = "select parent_id from {$this->table} where parent_id in($id_list)";
            $exists_parent_id_arr = $this->db->get_all_col($sql);
            foreach($ret_data['data'] as $k => $row) {
                $has_next = in_array($row['id'], $exists_parent_id_arr) ? 1 : 0;
                $has_parent = $row['type'] == 1 ? 0 : 1;
                $ret_data['data'][$k]['has_next'] = $has_next;
                $ret_data['data'][$k]['has_parent'] = $has_parent;
            }
        }
        return $this->format_ret($ret_status, $ret_data);
    }


    /**新增
     * @param $params
     * @return array
     */
    function add_action($params) {
        if (!empty($params['address_id']) && empty($params['address'])) {
            return $this->format_ret('-1', '', '请填写街道名称！');
        }
        if (empty($params['address_id']) && !empty($params['address'])) {
            return $this->format_ret('-1', '', '请填写街道ID！');
        }
        try {
            $this->begin_trans();
            //区县
            $district = array(
                'id' => $params['district_id'],
                'type' => 4,
                'name' => $params['district'],
                'parent_id' => $params['city'],
            );
            $ret = $this->insert_dup($district);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '新增失败！');
            }
            if(!empty($params['address_id'])&&!empty($params['address'])){
                //街道
                $address = array(
                    'id' => $params['address_id'],
                    'type' => 5,
                    'name' => $params['address'],
                    'parent_id' => $params['district_id'],
                );
                $check = $this->check_add_info($address['id'], $address['name'], $address['parent_id']);
                if ($check['status'] != 1) {
                    $msg = ($check['status'] == -1) ? '街道(ID)已存在！' : '该区县的街道名称已存在!';
                    $this->rollback();
                    return $this->format_ret('-1', '', $msg);
                }
                $ret = $this->insert_dup($address);
                if ($ret['status'] != 1) {
                    $this->rollback();
                    return $this->format_ret('-1', '', '新增失败！');
                }
            }
            $this->commit();
            return $this->format_ret('1', '', '新增成功！');
        } catch (Exception $e) {
            $this->rollback();
            return $this->format_ret(-1, '', '添加失败:' . $e->getMessage());
        }
    }

    /**验证
     * @param $id
     * @param $name
     * @param $parent_id
     * @return array
     */
    function check_add_info($id, $name, $parent_id) {
        $ret = $this->get_row(array('id' => $id));
        if ($ret['status'] == 1) {
            return $this->format_ret('-1', '', 'ID已存在！');
        }
        $ret = $this->get_row(array('name' => $name, 'parent_id' => $parent_id));
        if ($ret['status'] == 1) {
            return $this->format_ret('-2', '', '地域名称和父节点区域标识已存在！');
        }
        return $this->format_ret('1', '', '');
    }




    /**
     * 取得区域数据
     * @param $parent_id  父类
     */
    function get_area($parent_id) {
        $rs = array();

        if(strlen($parent_id) == 6 ){
            $p = $parent_id.'000000';
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' or parent_id = '$p'  ";
            $rs = $this->db->get_all($sql);
        }else{
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' ";
            $rs = $this->db->get_all($sql);
        }
        return $rs;
    }

    
}
