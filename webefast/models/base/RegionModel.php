<?php
require_model('tb/TbModel');

class RegionModel extends TbModel
{
    /**
     * @var string 表名
     */
    protected $table = 'base_region';

    private static $_region = array();

    public function get_region_id_by_name($region_arr) {

        $_filter = array('省', '自治区', '维吾尔自治区', '回族自治区', '壮族自治区', '特别行政区', '地区');
        $_replace = array ('', '', '', '', '', '', '', '', '');

        foreach ($region_arr as &$value) {
            $pos = strpos($value, '(');
            if (false !== $pos) {
                $value = substr($value, 0, $pos);
            }
            $pos = strpos($value, '（');
            if (false !== $pos) {
                $value = substr($value, 0, $pos);
            }
        }

        $privince = str_replace($_filter, $_replace, $region_arr['province']);
        $city = str_replace($_filter, $_replace, $region_arr['city']);
        $district = str_replace($_filter, $_replace, $region_arr['district']);

        if('' != $privince){
            $province_region = $this->get_region_province($privince);
        }
        $province_id = isset($province_region['region_id']) ? $province_region['region_id'] : 0;

        if('' != $city){
            $city_region = $this->get_region_city($city);
        }
        $city_id = isset($city_region['region_id']) ? $city_region['region_id'] : 0;

        if (!empty($city_id)) {
            $district_region = $this->get_region_district($district, $city_id);
            $district_id = isset($district_region['region_id']) ? $district_region['region_id'] : 0;
        }elseif('' != $city){
            $district_region = $this->get_region_district($city, $province_id);
            if(isset($district_region['region_id'])){
                $city_id = $district_region['region_id'];
            }
            $district_id = 0;
        }else {
            $district_id = 0;
        }

        return array('receiver_province' => $province_id, 'receiver_city' => $city_id, 'receiver_district' => $district_id);
    }

    public function get_region_province($region_name) {
        $key = md5('province_' . $region_name);
        if (empty(self::$_region) || !isset(self::$_region[$key]) || !is_array(self::$_region[$key]) || empty(self::$_region[$key])) {
            $where = "region_type=1 AND region_name like'%$region_name%'";
            self::$_region[$key] = $this->_get_region($where);
        }
        return self::$_region[$key];
    }

    public function get_region_city($region_name) {
        $key = md5('city_' . $region_name);
        if (empty(self::$_region) || !isset(self::$_region[$key]) || !is_array(self::$_region[$key]) || empty(self::$_region[$key])) {
            $where = "region_type=2 AND region_name like'%$region_name%'";
            self::$_region[$key] = $this->_get_region($where);
        }
        return self::$_region[$key];
    }

    public function get_region_district($region_name, $parent_id = 0) {
        $key = md5('district_' . $parent_id . '_' . $region_name);
        if (empty(self::$_region) || !isset(self::$_region[$key]) || !is_array(self::$_region[$key]) || empty(self::$_region[$key])) {
            if (!empty($parent_id)) {
                $sql="SELECT region_id FROM base_region WHERE "."region_type=3 AND parent_id=:parent_id AND region_name like :region_name";
                $list = $this->db->get_all($sql,array(':parent_id'=>$parent_id,':region_name'=>'%'.$region_name.'%'));
                if(count($list)>1){
                    $where = "region_type=3 AND parent_id='$parent_id' AND region_name = '{$region_name}'";
                }else{
                    $where = "region_type=3 AND parent_id='$parent_id' AND region_name like'%$region_name%'";
                }
            } else {
                $sql="SELECT region_id FROM base_region WHERE "."region_type=3 AND region_name like :region_name";
                $list = $this->db->get_all($sql,array(':region_name'=>'%'.$region_name.'%'));
                if(count($list)>1){
                    $where = "region_type=3 AND region_name = '{$region_name}'";
                }else{
                    $where = "region_type=3 AND region_name like'%$region_name%'";
                }
            }
            self::$_region[$key] = $this->_get_region($where);
        }
        return self::$_region[$key];
    }

    private function _get_region($where) {
        $region_result = $this->db->get_row("SELECT * FROM base_region WHERE ". $where);
        $region = array();
        if (!empty($region_result)) {
            $region = $region_result;
        }
        return $region;
    }
}