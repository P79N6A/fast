<?php

require_model('tb/TbModel');

class AddrCommModel extends TbModel {

    //地址匹配
    function match_addr($api_data) {
        $api_data['receiver_province'] = str_replace(' ', '', $api_data['receiver_province']);
        $api_data['receiver_city'] = str_replace(' ', '', $api_data['receiver_city']);
        $api_data['receiver_district'] = str_replace(' ', '', $api_data['receiver_district']);
        $c1 = substr($api_data['receiver_province'], 0, 6);
        
        //对贝贝和唯品会平台的新疆省直辖地址进行特殊处理
        $ss_map =array(
            '其它' => '自治区直辖县级行政区划',
            '省直辖' =>'自治区直辖县级行政区划',
            '省直辖县级行政单位' =>'自治区直辖县级行政区划',
            '省直辖县级市' =>'自治区直辖县级行政区划',
        );
        $source = array('beibei','weipinhui','xiaomizhijia');
        if(in_array($api_data['receiver_province'], ['新疆维吾尔自治区','新疆'])){
            if(in_array($api_data['source'],$source) && isset($ss_map[$api_data['receiver_city']])){
                $api_data['receiver_city'] = $ss_map[$api_data['receiver_city']];
            }
        }
        
        $c2 = $api_data['receiver_city'];
        
        $c3 = $api_data['receiver_district'];

        $db_c1 = $this->get_province_id($c1);

//        $sql = "select id from base_area where name like :c1 and type = 2";
//        $db_c1 = ctx()->db->get_row($sql, array(':c1' => $c1 . '%'));
        if (empty($db_c1)) {
            return $this->format_ret(-30, '', '地址匹配不到省份信息');
        }

        $zx_map = array(
            '上海' => '310100000000',
            '北京' => '110100000000',
            '天津' => '120100000000',
            '重庆' => '500100000000'
        );
        $find_id = 0;
        foreach ($zx_map as $k => $v) {
            if (strpos($api_data['receiver_province'], $k) !== false) {
                $find_id = $v;
            }
        }
        $db_c3['id'] = 0;

        if ($find_id > 0) {
            $db_c2 = array('id' => $find_id);
        } else {
            $sql = "select id from base_area where name like :c2 and parent_id = :parent_id";
            $db_c2 = ctx()->db->get_row($sql, array(':c2' => $c2 . '%', ':parent_id' => $db_c1['id']));
            if (empty($db_c2)) {
                $city_all = $this->get_area_by_parent_id($db_c1['id'], 3);
                $db_c2['id'] = $this->find_area_for_name($c2, $city_all);

                if ($db_c2['id'] === FALSE) {

                    $city_all = $this->get_area_by_parent_id_child($db_c1['id'], 3);

                    $db_c3['id'] = $this->find_area_for_name($c2, $city_all);
                    if (empty($db_c3['id'])) {
                        return $this->format_ret(-30, '', '地址匹配不到市信息');
                    } else {
                        $db_c2['id'] = $this->db->get_value("select parent_id from base_area where id=:id ", array(':id' => $db_c3['id']));
                    }
                }
            }
        }
        
        //遇到省直辖区市 
        
        
        //有些地方是没有区的
        $no_match_city_arr = explode(',', '东莞');
        $find_tag = 0;
        foreach ($no_match_city_arr as $v) {
            if (strpos($api_data['receiver_city'], $v) !== false) {
                $find_tag = 1;
            }
        }

        if ($db_c3['id'] == 0) {
            if (!empty($c3)) {
                $sql = "select id from base_area where name like :c3 and parent_id = :parent_id";
                $db_c3 = ctx()->db->get_row($sql, array(':c3' => $c3 . '%', ':parent_id' => $db_c2['id']));
            }else{
                $db_c3 = array();
            }
            if (empty($db_c3) && $find_tag == 0) {
                // return $this->format_ret(-30,'','地址匹配不到区/县信息');
                $district_all = $this->get_area_by_parent_id($db_c2['id'], 4);
                $db_c3['id'] = $this->find_area_for_name($c3, $district_all);
                if ($db_c3['id'] === FALSE) {
                    $db_c3['id'] = 0;
                }
                if ($db_c3['id'] == 0) {
                    $area_cfg = require_conf('sys/area_change');
                    if (isset($area_cfg[$c3])) {
                        $db_c3['id'] = $this->find_area_for_name($area_cfg[$c3], $district_all);
                    }
                }
                if ($db_c3['id'] === FALSE) {
                    $db_c3['id'] = 0;
                }
            }
        }
        $receiver_street = '';
        if(!empty($api_data['receiver_street'])){
            $sql = "select id from base_area where  name=:name and parent_id = :parent_id  ";
            $receiver_street  = $this->db->get_value($sql,array(':name'=>$api_data['receiver_street'],'parent_id'=>$db_c3['id']));
            $receiver_street =!empty($receiver_street)?$receiver_street:'';
        }    
                
                
        $result = array();
        $result['receiver_country'] = 1;
        $result['receiver_province'] = $db_c1['id'];
        $result['receiver_city'] = $db_c2['id'];
        $result['receiver_district'] = $db_c3['id'];
        $result['receiver_street'] = $receiver_street;
        $result['receiver_address'] = $api_data['receiver_address'];
        $result['receiver_addr'] = $db_c3['id'] == 0 ? $c3. $api_data['receiver_addr'] : $api_data['receiver_addr'];
        //echo '<hr/>$result<xmp>'.var_export($result,true).'</xmp>';die;
        return $this->format_ret(1, $result);
    }

    function get_area_by_parent_id($id, $type) {
        $sql = "select id,name from base_area where  type = :type and parent_id = :parent_id ";
        $data = ctx()->db->get_all($sql, array(':type' => $type, ':parent_id' => $id));
        $area_all = array();
        foreach ($data as $val) {
            $area_all[$val['id']] = $val['name'];
        }
        return $area_all;
    }

    function get_area_by_parent_id_child($id, $type) {
        $sql = "select id,name from base_area where  parent_id in ( select id from base_area where  type = :type and parent_id = :parent_id ) ";
        $data = ctx()->db->get_all($sql, array(':type' => $type, ':parent_id' => $id));
        $area_all = array();
        foreach ($data as $val) {
            $area_all[$val['id']] = $val['name'];
        }
        return $area_all;
    }

    function find_area_for_name($name, &$area_all) {
        $name_len = mb_strlen($name, 'utf-8');

        $find_area = array();
        foreach ($area_all as $area_id => $area_name) {
            $find_num = strpos($area_name, $name);
            if ($find_num === 0) {
                $find_area[] = $area_id;
            }
        }

        if (count($find_area) > 0) {
            if (count($find_area) == 1) {
                return $find_area[0];
            } else {
                return false;
            }
        }
 
        if ($name_len <3) {
            return false;
        }
        $name = mb_substr($name, 0, $name_len - 1, 'utf-8');
        //no find
        if (empty($find_area)) {
 
            return $this->find_area_for_name($name, $area_all);
        }
    }

    function get_province_id($province_name) {
        static $province_arr = NULL;
        if (is_null($province_arr)) {
            $sql = "select id,name  from base_area where type=2 AND parent_id=1";
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                $province_arr[$val['id']] = $val['name'];
            }
        }
        $province_data = array();
        foreach ($province_arr as $id => $name) {
            if (strpos($name, $province_name) !== false) {
                $province_data['id'] = $id;
            }
        }
        return $province_data;
    }

}
