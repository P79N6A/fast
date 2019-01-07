<?php

/**
 * 系统地址区域相关业务
 *
 * @author huanghy
 */
require_model('tb/TbModel');
require_lang('sys');

class TaobaoAreaModel extends TbModel {

    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent :: __construct($table);
    }

    function get_table() {
        return 'base_area';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        //print_r($filter);
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
        } else {
            if (!isset($filter['area_type'])) {
                $sql_main .= " AND type = :type";
                $sql_values[':type'] = '1';
            }
        }

        // if ( isset($filter['area_type']) && $filter['area_type'] == '' && empty($filter['name']) && empty($filter['zip'])) {
        //   $sql_main .= " AND parent_id = 1";
        //}

        if (isset($filter['area_type']) && $filter['area_type'] == 'child' && empty($filter['name']) && empty($filter['zip'])) {
            $sql_main .= " AND parent_id = :parent_id";
            $sql_values[':parent_id'] = $filter['area_id'];
            //$filter['page'] = 1;
        }

        if (isset($filter['area_type']) && $filter['area_type'] == 'parent' && empty($filter['name']) && empty($filter['zip'])) {
            $sql = "select parent_id from {$this->table} where id = " . $filter['area_id'];
            $parent_id_v = $this->db->getOne($sql);
            $sql_main .= " AND parent_id = " . $parent_id_v;
            // $filter['page'] = 1;
        }

        // $filter['page_size'] = 250;
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        $type_map = array('1' => '国家', '2' => '省/自治区/直辖市', '3' => '地级市', '4' => '县/市(县级市)/区', '5' => '街道');
        $id_arr = array();
        foreach ($ret_data['data'] as $k => $row) {
            $ret_data['data'][$k]['type_txt'] = isset($type_map[$row['type']]) ? $type_map[$row['type']] : '';
            $id_arr[] = $row['id'];
        }

        if (!empty($id_arr)) {
            $id_list = join(',', $id_arr);
            $sql = "select parent_id from {$this->table} where parent_id in($id_list)";
            $exists_parent_id_arr = $this->db->get_all_col($sql);
            foreach ($ret_data['data'] as $k => $row) {
                $has_next = in_array($row['id'], $exists_parent_id_arr) ? 1 : 0;
                $has_parent = $row['type'] == 1 ? 0 : 1;
                $ret_data['data'][$k]['has_next'] = $has_next;
                $ret_data['data'][$k]['has_parent'] = $has_parent;
            }
        }
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * 取得区域数据
     * @param $parent_id  父类
     */
    function get_area($parent_id) {
        $rs = array();

        if (strlen($parent_id) == 6) {
            $p = $parent_id . '000000';
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' or parent_id = '$p'  ";
            $rs = $this->db->get_all($sql);
        } else {
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' ";
            $rs = $this->db->get_all($sql);
        }
        return $rs;
    }

    function get_area_id_by_name($parent_id, $name, $id = '') {
        $rs = array();
        $new_name = $name . "省";
        if (strlen($parent_id) == 6) {
            $p = $parent_id . '000000';
            $sql = "select id FROM {$this->table} WHERE (parent_id = :parent_id or parent_id = :p) and (name=:name or name=:new_name) ";
            $rs = $this->db->getOne($sql, array(':parent_id' => $parent_id, ':p' => $p, ':name' => $name, ':new_name' => $new_name));
        } else {
            $sql = "select id FROM {$this->table} WHERE parent_id = :parent_id and (name=:name or name=:new_name) ";
            $rs = $this->db->getOne($sql, array(':parent_id' => $parent_id, ':name' => $name, ':new_name' => $new_name));
        }

        if (empty($rs) && !empty($id)) {
            $sql = "select id FROM {$this->table} WHERE id = '{$id}' ";
            $rs = $this->db->getOne($sql);
        }

        return $rs;
    }

    /**
     * 淘宝下载
     * @param $taobao
     */
    function get_taobao_areas($taobao_util) {
        $params['fields'] = "id,type,name,parent_id,zip";
        $data = $taobao_util->post('taobao.areas.get', $params);
        if ($data['status'] != '1') {
            //错误处理
            return $this->format_ret(-1, '', "淘宝区域信息获取失败！");
        }

        foreach ($data['data']['areas']['area'] as $area) {
            $sql = "INSERT INTO `api_taobao_area_bak` (id, TYPE,NAME,parent_id,zip) VALUES ('{$area['id']}','{$area['type']}','{$area['name']}','{$area['parent_id']}','{$area['zip']}')";

            $ret = $this->db->query($sql);
        }
        echo "success";
        exit;
    }

    //淘宝部分数据移
    function convert() {

        //type =3
        $sql = "select * FROM api_taobao_area_bak WHERE type =  '3' and parent_id = '820000' ";
        $rs = $this->db->get_all($sql);
        //print_r($rs);exit;
        foreach ($rs as $area) {


            $sql = "INSERT INTO `api_taobao_area` (id, TYPE,NAME,parent_id,zip) VALUES ('{$area['id']}','{$area['type']}','{$area['name']}','{$area['parent_id']}','{$area['zip']}')";
            echo $sql . '<br>';
            $ret = $this->db->query($sql);
        }


        /*
          $sql = "select * FROM api_taobao_area_bak WHERE type =  '3' and parent_id = '810000' ";
          $rs = $this->db->get_all($sql);
          //print_r($rs);exit;
          foreach($rs as $value){
          $sql2 = "select * FROM api_taobao_area_bak WHERE type =  '4' and parent_id = '{$value['id']}' ";
          $rs2 = $this->db->get_all($sql2);
          foreach($rs2 as $area){
          $sql = "INSERT INTO `api_taobao_area` (id, TYPE,NAME,parent_id,zip) VALUES ('{$area['id']}','{$area['type']}','{$area['name']}','{$area['parent_id']}','{$area['zip']}')";
          echo $sql.'<br>';
          $ret = $this -> db -> query($sql);
          }
          }
         */


        echo "success";
        exit;
    }

    /**
     * 修改纪录
     */
    function update($order_label, $id) {
        $status = $this->valid($order_label, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->get_row(array('order_label_id' => $id));

        $ret = parent :: update($order_label, array('order_label_id' => $id));
        return $ret;
    }

    private function is_exists($value, $field_name = 'order_label_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    //抓取数据保存
    function area_insert($area) {
        $sql = "INSERT INTO `api_taobao_area_new` (id, TYPE,NAME,parent_id,url) VALUES ('{$area['id']}','{$area['type']}','{$area['name']}','{$area['parent_id']}','{$area['url']}')";
        //echo $sql.'<br>';
        $ret = $this->db->query($sql);
    }

    //修改省代号
    function update_code() {
        $sql = "select * FROM api_taobao_area_new WHERE type = '2' ";
        $rs = $this->db->get_all($sql);
        foreach ($rs as $value) {
            $sql2 = "select * FROM api_taobao_area WHERE name = '{$value['name']}' limit 1 ";
            $rs2 = $this->db->get_all($sql2);

            if (isset($rs2[0]['name']) && $rs2[0]['name'] != '') {
                $sql3 = "UPDATE api_taobao_area_new SET id = '{$rs2[0]['id']}' WHERE name = '{$value['name']}' limit 1 ";
                $this->db->query($sql3);
            }
        }
    }

    //分段取
    function get_area_type_limit($type, $limit) {
        $rs = array();
        if ($type) {
            $sql = "select * FROM api_taobao_area_new WHERE type = '{$type}' and catch != '1' ORDER BY id ASC limit {$limit} ";

            $rs = $this->db->get_all($sql);
            /*
              foreach($rs as $v){
              $sql3 = "UPDATE api_taobao_area_new SET catch = '1' WHERE id = '{$v['id']}' limit 1 ";
              $this -> db -> query($sql3);
              } */
        }

        return $rs;
    }

    function update_flag($id) {
        $sql = "UPDATE api_taobao_area_new SET catch = '1' WHERE id = '{$id}' limit 1 ";
        //echo $sql.'<br>';
        $this->db->query($sql);
    }

    function get_area_type($type) {
        $rs = array();
        if ($type) {
            $sql = "select * FROM api_taobao_area_new WHERE type = '{$type}' ";
            $rs = $this->db->get_all($sql);
        }

        return $rs;
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

    //多字段查询
    public function get_by_field_2($field_name1, $value1, $field_name2, $value2, $select = "*") {
        $sql = "select {$select} from {$this->table} where {$field_name1} = :{$field_name1} and {$field_name2} = :{$field_name2}";

        $data = $this->db->get_row($sql, array(":{$field_name1}" => $value1, ":{$field_name2}" => $value2));
        if ($data) {
            return $this->format_ret('1', $data);
        } else {
            return $this->format_ret('-1', '', 'get_data_fail');
        }
    }

    // $street_like_mode = 1 街道模糊匹配
    public function get_by_field_all($country, $province, $city, $district, $street, $street_like_mode = 1) {
//        $arr = array('country_id' => '1', 'province' => '2');
        //国家
        $sql = "select * from {$this->table} where name = :country and type = '1'";
        $country_arr = $this->db->get_row($sql, array(":country" => $country));
        $country_id = isset($country_arr['id']) ? $country_arr['id'] : '';
        $district_id = '';
        //省
        if (isset($country_id) && $country_id <> '') {
            $sql = "select * from {$this->table} where name = :province and parent_id = :country_id";
            $province_arr = $this->db->get_row($sql, array(":province" => $province, ":country_id" => $country_id));
            $province_id = isset($province_arr['id']) ? $province_arr['id'] : '';
        } else {
            $sql = "select count(*) as cnt from {$this->table} where name = :province and type = '2' ";
            $province_count = $this->db->get_row($sql, array(":province" => $province));

            if ($province_count['cnt'] > 1) {
                $sql = "select * from {$this->table} where name = :province and type = '2' ";
                $sheng = $this->db->get_all($sql, array(":province" => $province));
                foreach ($sheng as $v) {
                    $sql = "select * from {$this->table} where name = :city and parent_id = :province_id";
                    $shi = $this->db->get_row($sql, array(":city" => $city, ":province_id" => $v['id']));
                    if (isset($shi['id']) && $shi['id'] <> '') {
                        $province_id = $v['id'];
                        $country_id = $v['parent_id'];
                        break;
                    }
                }
                //print_r($sheng);
            } else {
                $sql = "select * from {$this->table} where name = :province and type = '2' ";
                $province_arr = $this->db->get_row($sql, array(":province" => $province));
                $province_id = isset($province_arr['id']) ? $province_arr['id'] : '';
                $country_id = isset($province_arr['parent_id']) ? $province_arr['parent_id'] : '';
            }
        }


        //市
        $sql = "select * from {$this->table} where name = :city and parent_id = :province_id";
        $city_arr = $this->db->get_row($sql, array(":city" => $city, ":province_id" => $province_id));
        $city_id = '';

        if (empty($city_arr['id'])) {
            $sql = "select * from {$this->table} where name = :city and parent_id in ( select id from {$this->table} where parent_id = :province_id) ";
            $city_arr = $this->db->get_row($sql, array(":city" => $city, ":province_id" => $province_id));

            if (!empty($city_arr)) {
                $city_id = isset($city_arr['parent_id']) ? $city_arr['parent_id'] : '';
                $district_id = $city_arr['id'];
            }
        } else {
            $city_id = $city_arr['id'];
        }

        //  var_dump($district_id);die;
        if (empty($district_id)) {
            //区县
            $sql = "select * from {$this->table} where name = :district and parent_id = :city_id";
            $district_arr = $this->db->get_row($sql, array(":district" => $district, ":city_id" => $city_id));
            $district_id = isset($district_arr['id']) ? $district_arr['id'] : '';
            if (empty($district_arr)) {
                $area_cfg = require_conf('sys/area_change');
                if (isset($area_cfg[$district])) {
                    $district = $area_cfg[$district];
                    $district_arr = $this->db->get_row($sql, array(":district" => $district, ":city_id" => $city_id));
                    $district_id = isset($district_arr['id']) ? $district_arr['id'] : '';
                }
            }
        }

        //街道
        if ($street_like_mode == 0) {
            $sql = "select * from {$this->table} where name = :street and parent_id = :district_id";
            $street_arr = $this->db->get_row($sql, array(":street" => $street, ":district_id" => $district_id));
            $street_id = isset($street_arr['id']) ? $street_arr['id'] : '';
        } else {
            $ret = $this->match_street($street, $district_id);
            if ($ret['status'] < 0) {
                $street_id = '';
                $street_name = '';
            } else {
                $street_info = $ret['data'];
                $street_id = $street_info['id'];
                $street_name = $street_info['name'];
            }
        }
        //print_r($street_id);

        $arr = array('country_id' => $country_id, 'province' => $province_id, 'city' => $city_id, 'district' => $district_id, 'street' => $street_id, 'street_name' => $street_name);
        return $arr;
    }

    function match_street($street, $district_id) {
        $sql = "select id,name from {$this->table} where parent_id = :district_id";
        $db_street_arr = $this->db->get_all($sql, array(":district_id" => $district_id));
        foreach ($db_street_arr as $k => $v) {
            $_t = strpos($street, $v['name']);
            if ($_t !== false) {
                return $this->format_ret(1, $v);
            }
        }
        return $this->format_ret(-1, '', '模糊匹配不到对应的街道');
    }

    function get_area_select($parent_id) {

        $data = $this->get_area($parent_id);
        $area_data = array();
        foreach ($data as &$val) {
            $area_data[] = array($val['id'], $val['name']);
        }
        return $area_data;
    }

    /**
     * 获取外部特殊地址对照
     * @param string $_code 档案代码
     * @param string $_type 档案类型
     * @param string $area_id 系统地址id
     * @return array
     */
    public function get_out_area_id($_code, $_type, $area_id = []) {
        static $area_data = NULL;
        if (!empty($area_id) && !is_array($area_id) && isset($area_data[$area_id])) {
            return $area_data;
        }

        $sql = 'SELECT area_id,out_area_id,out_area_name FROM base_area_compare WHERE ident_code=:_code AND ident_type=:_type';
        $sql_values = [':_code' => $_code, ':_type' => $_type];
        if (!empty($area_id)) {
            $area_id = is_array($area_id) ? $area_id : [$area_id];
            $area_id_str = $this->arr_to_in_sql_value($area_id, 'area_id', $sql_values);
            $sql .= " AND area_id IN({$area_id_str})";
        }
        $data = $this->db->get_all($sql, $sql_values);
        $data = load_model('util/ViewUtilModel')->get_map_arr($data, 'area_id');
        return $data;
    }

}
