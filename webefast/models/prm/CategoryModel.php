<?php

/**
 * 分类相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('comm_util', true);
class CategoryModel extends TbModel {

    function get_table() {
        return 'base_category';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        //print_r($filter);
        $sql_values = array();
        $sql_main = "FROM {$this->table} rl WHERE 1";
        //名称或代码
        if (isset($filter['code_name']) && $filter['code_name'] != '') {
            $sql_main .= " AND (rl.category_code LIKE :code_name or rl.category_name LIKE :code_name)";
            $sql_values[':code_name'] = $filter['code_name'] . '%';
        }
        //上级分类
        if (isset($filter['p_code']) && $filter['p_code'] != '') {
            $sql_main .= " AND p_code = :p_code ";
            $sql_values[':p_code'] = $filter['p_code'];
        }
        /*
          if ($filter['category_type'] == '' && empty($filter['name'])) {
          $sql_main .= " AND p_id = '0' ";
          }

          if ($filter['category_type'] == 'child' && empty($filter['name'])) {
          $sql_main .= " AND p_id = :p_id";
          $sql_values[':p_id'] = $filter['category_id'];
          }

          if ($filter['category_type'] == 'parent' && empty($filter['name'])) {
          $sql = "select p_id from {$this->table} where category_id = " . $filter['category_id'];
          $parent_id_v = $this->db->getOne($sql);
          $sql_main .= " AND p_id = " . $parent_id_v;
          } */

        //api模式下按数据库更新时间戳逆序排序@2015-5-15 +++++++++++++++++++++++
        if (isset($filter['lastchanged']) && $filter['lastchanged'] !== '') {
            $sql_main .= " AND rl.lastchanged > :lastchanged ";
            $sql_values[':lastchanged'] =  $filter['lastchanged'] ;
        }
       if (isset($filter['no_category_id']) && $filter['no_category_id'] !== '') {
            $sql_main .= " AND rl.category_id <> :no_category_id ";
            $sql_values[':no_category_id'] =  $filter['no_category_id'] ;
        }
        
        
        if(isset($filter['is_api']) && $filter['is_api'] !== ''){
            $sql_main .= " order by rl.lastchanged desc ";
        }
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        $select = '*';
        // echo $sql_main;
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        if (!empty($ret_data['data'])) {
            foreach ($ret_data['data'] as $k => $row) {
                $sql = "select count(*) from {$this->table} where p_code = '{$row['category_code']}' ";
                $arr = $this->db->get_all_col($sql);
                $child_count = $arr[0];
                $has_next = $child_count ? 1 : 0;
                $has_parent = $row['p_code'] ? 1 : 0;
                $ret_data['data'][$k]['has_next'] = $has_next;
                $ret_data['data'][$k]['has_parent'] = $has_parent;
            }
        }


        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $ret = $this->get_row(array('category_id' => $id));
        if ($ret['status'] > 0) {
            filter_fk_name($ret['data'], array('p_id|category_id'));
        }

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

    //取得子类
    function get_child($p_code) {
        if ($p_code != '') {
            $sql = "select category_id,category_name,category_code FROM {$this->table} WHERE p_code = '$p_code' ";
            $rs = $this->db->get_all($sql);
            $data = array();
            foreach ($rs as $k => $v) {
                $sql_child = "select count(*) from {$this->table} where p_code = '{$v['category_code']}' ";
                $count = $this->db->getOne($sql_child);
                if ($count != 0) {
                    $data[$k]['leaf'] = false;
                }
                //$data[$k]['id'] = $v['category_id'];
                $data[$k]['id'] = $v['category_code'];
                $data[$k]['text'] = $v['category_name'];
            }
        }

        return $data;
    }

    //取得所有类别
    function get_category($p_code = 0, $i = 0) {
        $sql = "select category_id,category_code,category_name,p_code FROM {$this->table} where p_code = '{$p_code}'";
        $rs = $this->db->get_all($sql);
        foreach ($rs as $k => $v) {
            $i++;
            $arr = self::get_category($v['category_code'], $i);
            if ($arr) {
                $rs[$k]['child'] = $arr;

                //array_splice($rs,$k+1,0,$arr);
            }
        }
        return $rs;
    }

    //取得所有类别
    function get_category_trees() {
        $sql = "select category_id,category_code,category_name,p_code FROM {$this->table} WHERE 1=1";
        $rs = $this->db->get_all($sql);
        $arr = array();
        $cat_arr = array();
        $parent_arr = array();
        foreach ($rs as $k => $value) {

            $v = array('0' => $value['category_code'], '1' => $value['category_name'], 'category_code' => $value['category_code'], 'category_name' => $value['category_name']);
            $arr[$value['category_code']] = $v;
            $cat_arr[$value['category_code']] = $value['p_code'];
            $parent_arr[$value['p_code']][$value['category_code']] = $v;
        }

        foreach ($arr as $code => &$v) {
            $blank = '';
            $k = 0;
            $k = $this->get_child_num($k, $code, $cat_arr);
            if ($k > 0) {
                $j=0;
                for ($j = 0; $j < $k; $j++) {
                    $blank .= '&nbsp&nbsp&nbsp';
                }
                $v['1'] = $blank . $v['1'];
                $v['category_name'] = $blank . $v['category_name'];
            }
        }
        $new_arr = array();
        $p_arr = isset($parent_arr['0'])?$parent_arr['0']:'';
        $this->set_sort_chilid($p_arr,$arr,$parent_arr,$new_arr);

        return $new_arr;
    }

    private  function set_sort_chilid($arr_lood,$arr,$parent_arr,&$new_arr){
    	if(!empty($arr_lood)){
	        foreach($arr_lood as $val){
	            $new_arr[] = $arr[$val['category_code']];
	            if(isset($parent_arr[$val['category_code']])&&!empty($parent_arr[$val['category_code']])){
	                $this->set_sort_chilid($parent_arr[$val['category_code']],$arr,$parent_arr,$new_arr);
	            }
	        }
    	}
    }


    private function get_child_num($k, $code, $cat_arr) {
        if (isset($cat_arr[$code])) {
            $k++;
            return $this->get_child_num($k, $cat_arr[$code], $cat_arr);
        } else {
            return $k-1;
        }
    }

    /*
     * 添加新纪录
     */

    function insert($category) {
        $status = $this->valid($category);
        if ($status < 1) {
            return $this->format_ret($status);
        }

        $ret = $this->is_exists($category['category_code']);

        if (!empty($ret['data']))
            return $this->format_ret(-1,'','BRAND_ERROR_UNIQUE_CODE');

        return parent::insert($category);
    }

    /**
     * 修改纪录
     */
    function update($category, $category_id) {
        //print_r($category);
        $status = $this->valid($category, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        $ret2 = $this->get_row(array('category_id' => $category_id));
        if ($category['category_code'] != $ret2['data']['category_code']) {
            $ret1 = $this->is_exists($category['category_code'], 'category_code');
            if (!empty($ret1['data']))
                return $this->format_ret(CATEGORY_ERROR_UNIQUE_CODE);
        }

        $ret = parent::update($category, array('category_id' => $category_id));

         $data = array(
//                    'category_code'=>$category['category_code'],
                    'category_name'=>$category['category_name']
         );
        load_model('prm/GoodsModel')->update_property($data, array('category_code'=>$ret2['data']['category_code']) );



        return $ret;
    }

    function is_exists($value, $field_name = 'category_code') {
        $ret = parent :: get_row(array($field_name => $value));
        return $ret;
    }

    /**
     * @param $category_name
     * @param $p_code
     * @return array
     */
    function get_info_by_category_name_and_p_code($category_name, $p_code) {
        $ret = parent :: get_row(array('category_name' => $category_name, 'p_code' => $p_code));
        return $ret;
    }

    /*
     * 删除记录
     * */

    function delete($category_id) {
        $used = $this->is_used_by_id($category_id);
        if ($used) {
            return $this->format_ret(-1, array(), '已经在业务系统中使用，不能删除！');
        }
        $ret = parent::delete(array('category_id' => $category_id));
        return $ret;
    }

    /*
     * 服务器端验证
     */

    private function valid($data, $is_edit = false) {
        if (!$is_edit && (!isset($data['category_code']) || !valid_input($data['category_code'], 'required')))
            return BRAND_ERROR_CODE;
        //if (!isset($data['brand_name']) || !valid_input($data['brand_name'], 'required')) return BRAND_ERROR_NAME;

        return 1;
    }

    //树形数组转化为列表
    function _rebuild_new_array($arr, $k = 0) {  //rebuild a array
        static $tmp = array();
        foreach ($arr as $key => $value) {
            $blank = '';
            for ($j = 0; $j < $k; $j++) {
                $blank .= '&nbsp&nbsp&nbsp';
            }
            $tmp[$value['category_code']] = array('0' => $value['category_code'], '1' => $blank . $value['category_name'], 'category_code' => $value['category_code'], 'category_name' => $blank . $value['category_name']);
            if (isset($value['child']) && is_array($value['child'])) {
                $k++;
                self::_rebuild_new_array($value['child'], $k);
                $k--;
            } else {
                $tmp[$value['category_code']] = array('0' => $value['category_code'], '1' => $blank . $value['category_name'], 'category_code' => $value['category_code'], 'category_name' => $blank . $value['category_name']);
            }
        }
        return $tmp;
    }

    /**
     * 将get_category方法取到的数组, 格式化为BUI生成树形控件的数组
     *
     * @author  jhua.zuo<jhua.zuo@baisonmail.com>
     * @since   2014-11-07
     * @param  array $arr 传入数组
     * @param   int  $k
     * @return array
     */
    function _rebuild_new_array_for_bui($arr, $k = '0') {
        $return = array();
        foreach ($arr as $value) {
            if ((string) $value['p_code'] === (string) $k) {
                $tmp = array(
                    'text' => $value['category_name'],
                    'id' => $value['category_code'],
                    //'checked' => true,
                    'expanded' => false,
                );
                if (isset($value['child']) && is_array($value['child'])) {
                    $tmp['children'] = self::_rebuild_new_array_for_bui($value['child'], $value['category_code']);
                }
                $return[] = $tmp;
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
        $result = $this->get_value("select category_code from {$this->table} where category_id=:id", array(':id' => $id));
        $code = $result['data'];
        $num = $this->get_num('select * from base_goods where category_code=:code', array(':code' => $code));
        if (isset($num['data']) && $num['data'] > 0) {
            //已经在业务系统使用
            return true;
        } else {
            //尚未在业务系统使用
            return false;
        }
    }

    /**
     *
     * 方法名                               api_goods_category_update
     *
     * 功能描述                           更新产品类别
     *
     * @author      BaiSon PHP R&D
     * @date        2015-06-15
     * @param       array $param
     *              array(
     *                  必选: 'category_code', 'category_name',
     *                  可选: 'p_code', 'remark'
     *                 )
     * @return      json [string status, obj data, string message]
     *              {"status":"1","message":"保存成功"," data":"10146"}
     */
    public function api_goods_category_update($param) {
        //必选字段【说明：i=>代码数据检测类型为数字型  s=>代表数据检测类弄为字符串型】
        $key_required = array(
                's' => array('category_code', 'category_name')
        );
        //可选字段
        $key_option = array(
                's' => array('p_code', 'remark')
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
            if (isset($arr_deal['p_code']) && $arr_deal['p_code'] == $arr_deal['category_code']) {
                return $this->format_ret(-1, array('p_code' => $arr_deal['p_code'], 'category_code' => $arr_deal['category_code']), '上级分类代码不能和分类代码相同');
            }

            //检测是否已经存在spec1
            $ret = $this->is_exists($arr_deal['category_code']);
            if (1 == $ret['status']) {
                unset($arr_deal['category_code']);
                //更新数据
                $ret = $this->update($arr_deal, $ret['data']['category_id']);
            } else {
                //插入数据
                $ret = $this->insert($arr_deal);
            }
            return $ret;
        } else {
            return $this->format_ret("-10001", $ret_required['req_empty'], "API_RETURN_MESSAGE_10001");
        }
    }
    /**
     * 获取分类api
     * @param type $params
     * @return type
     */
    public function api_goods_category_get($params){
        $key_option = array(
            's' => array('page','page_size','start_lastchanged', 'end_lastchanged', 'category_code', 'category_name'),
        );
        $r_option = array();
        $ret_option = valid_assign_array($params, $key_option, $r_option);
        if (!empty($r_option['page'])) {
            if (!check_value_valid($r_option['page'], 'pint')) {
                return $this->format_ret(-10005, array('page' => $r_option['page']), '页码必须为正整数');
            }
        } else {
            $r_option['page'] = 1;
        }
       //页码
        if (!empty($r_option['page_size'])) {
            if (!check_value_valid($r_option['page_size'], 'pint')) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '页数必须为正整数');
            }
            if ($r_option['page_size'] > 100) {
                return $this->format_ret(-10005, array('page_size' => $r_option['page_size']), '每页最多100条');
            }
        } else {
            $r_option['page_size'] = 100;
        }
        //开始时间
        if (!empty($r_option['start_lastchanged'])) {
            $start_lastchanged = strtotime($r_option['start_lastchanged']);
            if ($start_lastchanged === FALSE) {
                return $this->format_ret(-10005, array('start_lastchanged' => $r_option['start_lastchanged']), '最后修改时间-开始格式错误');
            }
        }
        //结束时间
        if (!empty($r_option['end_lastchanged'])) {
            $end_lastchanged = strtotime($r_option['end_lastchanged']);
            if ($end_lastchanged === FALSE) {
                return $this->format_ret(-10005, array('end_lastchanged' => $r_option['end_lastchanged']), '最后修改时间-结束格式错误');
            }
        }
        //是否存在分类代码
        if(!empty($r_option['category_code'])){
            $ret = $this->is_exists($r_option['category_code'],'category_code');
            if($ret['status'] !=1 ){
                return $this->format_ret(-10006, array('category_code' => $r_option['category_code']), '分类代码不存在');
            }
        }
        //是否存在分类名称
        if(!empty($r_option['category_name'])){
            $res = $this->is_exists($r_option['category_name'],'category_name');
            if($res['status'] != 1){
                return $this->format_ret(-10006, array('category_name' => $r_option['category_name']), '分类名称不存在');
            }
        }
        $sql_values = [];
        $select = 'bc.category_code,bc.category_name,bc.remark,bc.lastchanged';
        $sql_main = 'FROM `base_category` AS bc WHERE 1 ';
        //组装SQL
        $this->get_record_sql_where($r_option, $sql_main, $sql_values,'bc.');
        //查询数据
        $rus = $this->get_page_from_sql($r_option, $sql_main, $sql_values, $select);
        $data = $rus['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), '数据不存在');//数据不存在
        }
        $filter = get_array_vars($rus['filter'], array('page', 'page_size', 'page_count', 'record_count'));

        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }
    
    /**
     * 组装查询sql
     * @param type $filter
     * @param type $sql_main
     * @param type $sql_values
     * @param type $ab
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values,$ab) {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '') {
                continue;
            }
            if ($key == 'start_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'end_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else{
                 $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }

        if (!isset($filter['start_lastchanged'])) {
            $start_time = date('Y-m-d');
            $sql_main .= " AND {$ab}lastchanged >= :start_lastchanged";
            $sql_values[':start_lastchanged'] = $start_time;
        }
        if (!isset($filter['end_lastchanged'])) {
            $end_time = date('Y-m-d',strtotime('+1 day'));
            $sql_main .= " AND {$ab}lastchanged <= :end_lastchanged";
            $sql_values[':end_lastchanged'] = $end_time;
        }
    }
}

