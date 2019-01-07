<?php

/**
 * 商品相关业务
 * @author dfr
 */
require_model('tb/TbModel');
require_lang('prm');
require_lib('util/oms_util', true);

class GoodsModel extends TbModel {

    //商品属性
    public $prop = array(array('0' => '0', '1' => '普通商品'), array('0' => '1', '1' => '补邮商品'), array('0' => '2', '1' => '赠品'));
    //商品状态
    public $state = array(array('0' => '0', '1' => '在售'), array('0' => '1', '1' => '在库'));
    //操作状态
    public $status = array(array('0' => '0,1', '1' => '全部'), array('0' => '0', '1' => '启用'), array('0' => '1', '1' => '停用'));

    function get_table() {
        return 'base_goods';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        $login_type = load_model('base/CustomModel')->get_session_data('login_type');
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
//        if($login_type != 2) {
//            $tab = empty($filter['list_tab']) ? 'custom_money' : $filter['list_tab'];
//            switch ($tab) {
//                case 'tabs_all'://全部
//                    break;
//                case 'no_custom_money'://非分销款
//                    $filter['is_custom_money'] = 0;
//                    $filter['is_custom'] = array(0);
//                    break;
//                case 'custom_money'://分销款
//                    $filter['is_custom_money'] = 1;
//                    $filter['is_custom'] = array(0,1);
//                    break;
//                case 'custom_goods'://指定分销商
//                    $filter['is_custom'] = array(1);
//                    $filter['is_custom_money'] = 1;
//                    break;
//            }
//        }
        //导出明细拼接sql
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'fx_goods_record_detail') {
            $sql_join_1 = " LEFT JOIN fx_appoint_goods ag ON rl.goods_code = ag.goods_code LEFT JOIN base_custom bc ON ag.custom_code = bc.custom_code ";
        }
        $sql_values = array();
        $sql_join = $login_type == 2 ? " LEFT JOIN fx_appoint_goods AS r2 ON rl.goods_code = r2.goods_code " : '';
        $sql_main = "FROM {$this->table} rl {$sql_join}{$sql_join_1} WHERE status = 0 AND rl.is_custom_money=1 ";
        $user_code = load_model('base/CustomModel')->get_session_data('user_code');
        $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
        //导出明细拼接sql
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'fx_goods_record_detail' && $login_type == 2) {                     
            $sql_main .= empty($custom_data) ? " AND 1 != 1 " : " AND ((ag.custom_code = :custom_code AND rl.is_custom = 1) OR (rl.is_custom_money = 1 AND rl.is_custom = 0)) ";           
        }
        if($login_type != 2) {
            $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
            //品牌
            $sql_main .= load_model('prm/BrandModel', true, false, 'webefast')->get_sql_purview_brand('rl.brand_code', $filter_brand_code);

            //最后更新时间
            if (isset($filter['lastchanged_start']) && $filter['lastchanged_start'] !== '') {
                $sql_main .= " AND rl.lastchanged >= :lastchanged_start ";
                $sql_values[':lastchanged_start'] = $filter['lastchanged_start'] . ' 00:00:00';
            }
            if (isset($filter['lastchanged_end']) && $filter['lastchanged_end'] !== '') {
                $sql_main .= " AND rl.lastchanged <= :lastchanged_end ";
                $sql_values[':lastchanged_end'] = $filter['lastchanged_end'] . ' 23:59:59';
            }
            //分类
            if (isset($filter['category_code']) && $filter['category_code'] != '') {
                $category_code_arr = explode(',', $filter['category_code']);
                if (!empty($category_code_arr)) {
                    $sql_main .= " AND (";
                    foreach ($category_code_arr as $key => $value) {
                        $param_category = 'param_category' . $key;
                        if ($key == 0) {
                            $sql_main .= " category_code = :{$param_category} ";
                        } else {
                            $sql_main .= " or category_code = :{$param_category} ";
                        }

                        $sql_values[':' . $param_category] = $value;
                    }
                    $sql_main .= ")";
                }
            }


            //品牌
           /* if (isset($filter['brand_code']) && $filter['brand_code'] != '') {
                $brand_code_arr = explode(',', $filter['brand_code']);
                if (!empty($brand_code_arr)) {
                    $sql_main .= " AND (";
                    foreach ($brand_code_arr as $key => $value) {
                        $param_brand = 'param_brand' . $key;
                        if ($key == 0) {
                            $sql_main .= " brand_code = :{$param_brand} ";
                        } else {
                            $sql_main .= " or brand_code = :{$param_brand} ";
                        }

                        $sql_values[':' . $param_brand] = $value;
                    }
                    $sql_main .= ")";
                }
            }*/
            //年份
            if (isset($filter['year_code']) && $filter['year_code'] != '') {
                $year_code_arr = explode(',', $filter['year_code']);
                if (!empty($year_code_arr)) {
                    $sql_main .= " AND (";
                    foreach ($year_code_arr as $key => $value) {
                        $param_year = 'param_year' . $key;
                        if ($key == 0) {
                            $sql_main .= " year_code = :{$param_year} ";
                        } else {
                            $sql_main .= " or year_code = :{$param_year} ";
                        }

                        $sql_values[':' . $param_year] = $value;
                    }
                    $sql_main .= ")";
                }
            }
            //季节
            if (isset($filter['season_code']) && $filter['season_code'] != '') {
                $season_code_arr = explode(',', $filter['season_code']);
                if (!empty($season_code_arr)) {
                    $sql_main .= " AND (";
                    foreach ($season_code_arr as $key => $value) {
                        $param_season = 'param_season' . $key;
                        if ($key == 0) {
                            $sql_main .= " season_code = :{$param_season} ";
                        } else {
                            $sql_main .= " or season_code = :{$param_season} ";
                        }

                        $sql_values[':' . $param_season] = $value;
                    }
                    $sql_main .= ")";
                }
            }
        } else {
            if(empty($custom_data)) {
                $sql_main .= " 1 != 1 ";
            } else {
                $sql_main .= " AND ((r2.custom_code = :custom_code AND rl.is_custom = 1) OR (rl.is_custom_money = 1 AND rl.is_custom = 0)) ";
                $sql_values[':custom_code'] = $custom_data['custom_code'];
            }
        }
        //状态
        if (isset($filter['state']) && $filter['state'] != '') {
            $state_arr = explode(',', $filter['state']);
            if (!empty($state_arr)) {
                $sql_main .= " AND (";
                foreach ($state_arr as $key => $value) {
                    $param_state = 'param_state' . $key;
                    if ($key == 0) {
                        $sql_main .= " state = :{$param_state} ";
                    } else {
                        $sql_main .= " or state = :{$param_state} ";
                    }

                    $sql_values[':' . $param_state] = $value;
                }
                $sql_main .= ")";
            }
        }
        //操作状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $status_arr = explode(',', $filter['status']);
            if (!empty($status_arr)) {
                $sql_main .= " AND (";
                foreach ($status_arr as $key => $value) {
                    $param_status = 'param_status' . $key;
                    if ($key == 0) {
                        $sql_main .= " status = :{$param_status} ";
                    } else {
                        $sql_main .= " or status = :{$param_status} ";
                    }

                    $sql_values[':' . $param_status] = $value;
                }
                $sql_main .= ")";
            }
        }
        //属性
        if (isset($filter['goods_prop']) && $filter['goods_prop'] != '') {
            $goods_prop_arr = explode(',', $filter['goods_prop']);
            if (!empty($goods_prop_arr)) {
                $sql_main .= " AND (";
                foreach ($goods_prop_arr as $key => $value) {
                    $param_goods_prop = 'param_goods_prop' . $key;
                    if ($key == 0) {
                        $sql_main .= " goods_prop = :{$param_goods_prop} ";
                    } else {
                        $sql_main .= " or goods_prop = :{$param_goods_prop} ";
                    }

                    $sql_values[':' . $param_goods_prop] = $value;
                }
                $sql_main .= ")";
            }
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND rl.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND rl.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品名称
        if (isset($filter['goods_short_name']) && $filter['goods_short_name'] !== '') {
            $sql_main .= " AND rl.goods_short_name LIKE :goods_short_name ";
            $sql_values[':goods_short_name'] = '%' . $filter['goods_short_name'] . '%';
        }
        //分销款
//        if (isset($filter['is_custom_money']) && $filter['is_custom_money'] !== '') {
//            $sql_main .= " AND rl.is_custom_money = :is_custom_money ";
//            $sql_values[':is_custom_money'] = $filter['is_custom_money'];
//        }
        //指定分销商
        if (isset($filter['is_custom']) && $filter['is_custom'] !== '') {
            $filter['is_custom']=is_array($filter['is_custom'])?$filter['is_custom']:array($filter['is_custom']);
            $is_custom_str = $this->arr_to_in_sql_value($filter['is_custom'],'is_custom',$sql_values);
            $sql_main .= " AND rl.is_custom IN ({$is_custom_str}) ";
        }
        //分销商
        if (isset($filter['custom_code']) && $filter['custom_code'] !== '') {
            $custom_code_arr = explode(',', $filter['custom_code']);
            $sql_value_custom_code = array();
            $custom_code_str = $this->arr_to_in_sql_value($custom_code_arr, 'custom_code', $sql_value_custom_code);
            $sql_custom = "SELECT DISTINCT goods_code FROM fx_appoint_goods WHERE custom_code IN ({$custom_code_str});";
            $custom_data = $this->db->get_all($sql_custom, $sql_value_custom_code);
            if (!empty($custom_data)) {
                $goods_code_arr = array_column($custom_data, 'goods_code');
                $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_values);
                $sql_main .= " AND rl.goods_code IN ({$goods_code_str}) ";
            } else {
                $sql_main .= " AND 1=2 ";
            }
        }
        if ($filter['ctl_type'] == 'export' && $filter['ctl_export_conf'] == 'fx_goods_record_detail') {
            return $this->export_detail($sql_main, $sql_values, $filter);
        }
        $sql_main .= " GROUP BY rl.goods_code order by rl.is_add_time desc ";
        if($filter['is_return'] == 1) {
            return array('sql' => $sql_main, 'value' => $sql_values);
        }
        $select = $login_type != 2 ? 'rl.*' : "rl.*,r2.fx_price";

        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select,true);
        foreach ($data['data'] as $key => $value) {
            $data['data'][$key]['sell_price'] = isset($value['sell_price']) ? round($value['sell_price'], 2) : '';
            if($value['is_custom'] == 1) { //商品指定了分销商，取分销商品中间表的结算价格
                $data['data'][$key]['fx_price'] = isset($value['fx_price']) && !empty($value['fx_price']) ? $value['fx_price'] : '0.00';
            } else { // 没有指定分销商     
                $data['data'][$key]['fx_price'] = 0;
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    //导出方法
    function export_detail($sql_main, $sql_values, $filter) {
        $select = "rl.*,ag.custom_code,ag.fx_price,ag.fx_rebate,bc.custom_name";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        return $this->format_ret(1, $data);
    }
    
    function get_goods_custom_list($goods_code) {
        $sql = "SELECT ag.*,bc.custom_name FROM fx_appoint_goods AS ag LEFT JOIN base_custom AS bc ON ag.custom_code = bc.custom_code  WHERE goods_code = :goods_code";
        $custom_data = $this->db->get_all($sql,array(':goods_code' => $goods_code));
        return $custom_data;
    }
    function get_goods_barcode_list($param) {
        //获取当前登录的分销商
        $user_code = load_model('base/CustomModel')->get_session_data('user_code');
        $custom_arr = load_model('base/CustomModel')->get_custom_by_user_code($user_code);
        $sql = "SELECT barcode,sku,goods_code FROM goods_sku WHERE goods_code = :goods_code ";
        $goods_data = $this->db->get_all($sql, array(':goods_code' => $param['goods_code']));
        $current_time = date('Y-m-d H:i:s');
        foreach ($goods_data as $key => &$val) {
            if ($param['is_custom'] == 0) { //没有指定分销商 ,计算分销价
                $fx_price = load_model('fx/GoodsManageModel')->compute_fx_price($custom_arr['custom_code'],$val, $current_time);
                $val['fx_price'] = sprintf('%01.2f', $fx_price);
            } else {
                $val['fx_price'] = $param['fx_price'];
            }
            $key_arr = array('spec1_name', 'spec2_name');
            $sku_info = load_model('goods/SkuCModel')->get_sku_info($val['sku'], $key_arr);
            $val['spec_str'] = $sku_info['spec1_name'] . '：' . $sku_info['spec2_name'];
        }
        return $goods_data;
    }
    /**
     * 设置分销款
     * params $goods_list
     * return array()
     */
    function set_custom_money($goods_list,$is_goods_custom) {
        $goods_data = explode(",", $goods_list);
        $data = array('is_custom_money' => $is_goods_custom);
        
        $sql_value = array();
        $goods_str = $this->arr_to_in_sql_value($goods_data,'goods_code',$sql_value);
        $sql = "UPDATE base_goods SET is_custom_money = :is_custom_money ";
        $sql_value[':is_custom_money'] = $is_goods_custom;
        if($is_goods_custom == 0) {
            $sql .= ",is_custom = 0";
        }
        $sql .= " WHERE goods_code in ({$goods_str}) ";
        $this->query($sql,$sql_value);
        
        if($is_goods_custom == 0) { //取消分销款，删除分销商
            $sql_value = array();
            $goods_str = $this->arr_to_in_sql_value($goods_data,'goods_code',$sql_value);
            $sql = "DELETE FROM fx_appoint_goods WHERE goods_code in ({$goods_str})";
            $ret = $this->query($sql, $sql_value);
        }
        return $this->format_ret(1,'','修改成功');
    }
    /**
     * 添加指定分销商
     * params $goods_list
     */
    function set_goods_custom($goods_list,$custom_list) {
        $goods_arr = explode(",", $goods_list);
        $custom_arr = explode(",", $custom_list);
        $user_code = CTX()->get_session('user_code');
        $user_name = CTX()->get_session('user_name');
        $update_str = "user_code = VALUES(user_code),modify_name = VALUES(modify_name)";
        
        $sql_value = array();
        $goods_str = $this->arr_to_in_sql_value($goods_arr,'goods_code',$sql_value);
        $sql = "SELECT goods_code,sell_price FROM base_goods WHERE goods_code IN ({$goods_str}) ";
        $goods_price_arr = $this->db->get_all($sql, $sql_value);
        
        $this->begin_trans();
        foreach($custom_arr as $val) {
            $data = array();
            foreach ($goods_price_arr as $v) {
                $data[] = array(
                    'goods_code' => $v['goods_code'],
                    'custom_code' => $val,
                    'user_code' => $user_code,
                    'modify_name' => $user_name,
                    'fx_price' => $v['sell_price'],
                    'fx_rebate' => 1
                );
            }
            $ret = $this->insert_multi_duplicate('fx_appoint_goods', $data, $update_str);
            if($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1,'','添加分销商失败');
            }
        }
        $sql_value = array();
        $goods_str = $this->arr_to_in_sql_value($goods_arr,'goods_code',$sql_value);
        $sql = "UPDATE base_goods SET is_custom = 1,is_custom_money = 1 WHERE goods_code IN ({$goods_str})";
        $ret = $this->query($sql, $sql_value);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','添加分销商失败');
        }
        $this->commit();
        return $this->format_ret(1,'','添加分销商成功');
    }
    function save_fx_price($data) {
        $fx_price = sprintf("%.2f",$data['fx_price']);
        //查询商品吊牌价
        $goods = $this->get_row(array('goods_code' => $data['goods_code']));
        $sell_price = $goods['data']['sell_price'];
        //结算分销商折扣
        $fx_rebate = 1;
        if(!empty($sell_price) && $fx_price != 0) {
            $fx_rebate = (float)$fx_price / $sell_price;
            $fx_rebate = sprintf("%.2f",$fx_rebate);
        }
        $param = array('fx_price' => $fx_price,'fx_rebate' => $fx_rebate);
        $ret = $this->update_exp('fx_appoint_goods',$param, array('custom_code' => $data['custom_code'],'goods_code' => $data['goods_code']));
        if($ret['status'] < 0) {
            return $this->format_ret(-1,'','修改分销价失败');
        }
        $fx_data = array('fx_price' => $fx_price,'fx_rebate' => $fx_rebate);
        return $this->format_ret(1,$fx_data,'修改成功');
    }
    function save_fx_rebate($data) {
        $fx_rebate = sprintf("%.2f",$data['fx_rebate']);
        //查询商品吊牌价
        $goods = $this->get_row(array('goods_code' => $data['goods_code']));
        $sell_price = $goods['data']['sell_price'];
        //结算分销商折扣
        $fx_price = 0;
        if(!empty($sell_price) && $fx_rebate != 1) {
            $fx_price = (float)$sell_price * $fx_rebate;
            $fx_price = sprintf("%.2f",$fx_price);
        }
        $param = array('fx_price' => $fx_price,'fx_rebate' => $fx_rebate);
        $ret = $this->update_exp('fx_appoint_goods',$param, array('custom_code' => $data['custom_code'],'goods_code' => $data['goods_code']));
        if($ret['status'] < 0) {
            return $this->format_ret(-1,'','修改分销价失败');
        }
        $fx_data = array('fx_price' => $fx_price,'fx_rebate' => $fx_rebate);
        return $this->format_ret(1,$fx_data,'修改成功');
    }
    /**
     * 获取商品指定分销商的分销价
     * @param type $goods_code
     * @param type $custom_code
     * return $ret
     */
    function get_goods_custom_price($goods_code,$custom_code) {
        $sql = "SELECT * FROM fx_appoint_goods WHERE goods_code = :goods_code AND custom_code = :custom_code";
        $ret = $this->db->get_row($sql,array(':goods_code' => $goods_code,'custom_code' => $custom_code));
        return $ret;
    }
    /*
     * 删除指定分销商
     */
    function delete_custom ($data) {
        $this->begin_trans();
        $ret = $this->delete_exp('fx_appoint_goods', array('goods_code' => $data['goods_code'], 'custom_code' => $data['custom_code']));
        if($ret == false) {
            $this->rollback();
            return $this->format_ret( -1,'','删除失败');
        }
        //修改商品状态
        $sql = "SELECT COUNT(*) FROM fx_appoint_goods WHERE goods_code = :goods_code ";
        $goods_count = $this->db->get_value($sql,array(':goods_code' => $data['goods_code']));
        if($goods_count == 0) {        //修改商品状态
            $ret = $this->update(array('is_custom' => 0), array('goods_code' => $data['goods_code']));
            if($ret['status'] < 0) {
                $this->rollback();
                return $this->format_ret(-1, '', '修改商品状态失败');
            }
        }
        $this->commit();
        return $this->format_ret(1, '', '删除成功');
    }
    /**
     * 分销商登录获取开启分销款的商品和指定分销商的商品
     * @param type $cutom_code
     */
    function get_fx_money_goods($select = "rl.*,r2.*", $type = 'sql', $alias = '',$custom_code = '') {
        $custom_model = load_model('base/CustomModel');
        $data = array();
        $sql_values = array();
        if(!empty($custom_code)) {
            /*$user_code = empty($list_user_code) ? $custom_model->get_session_data('user_code') : $list_user_code;
            $custom_data = load_model('base/CustomModel')->get_custom_by_user_code($user_code);*/
            $sql = "SELECT {$select} FROM base_goods AS rl LEFT JOIN fx_appoint_goods AS r2 ON rl.goods_code = r2.goods_code WHERE (r2.custom_code = :custom_code OR (rl.is_custom_money = 1 AND rl.is_custom = 0)) AND rl.`status` = 0 ";
            $ret = $this->db->get_all($sql,array(':custom_code' => $custom_code));
            if(!empty($ret) && $type == 'sql') {
                $goods_arr = array_column($ret,'goods_code');
                $goods_str = $this->arr_to_in_sql_value($goods_arr,'goods_code',$sql_values);
                $sql_main .= " AND " . $alias . "goods_code IN ({$goods_str}) ";
                $arr = array('sql' => $sql_main , 'value' => $sql_values);
                $data = $arr;
            } else if(!empty($ret) && $type == 'data'){
                $data = $ret;
            }
        } else { //没有传入分销商，查询所有分销款商品
            $sql = "SELECT {$select} FROM base_goods AS rl WHERE rl.is_custom_money = 1 AND rl.`status` = 0 ";
            $ret = $this->db->get_all($sql);
            if(!empty($ret) && $type == 'sql') {
                $goods_arr = array_column($ret,'goods_code');
                $goods_str = $this->arr_to_in_sql_value($goods_arr,'goods_code',$sql_values);
                $sql_main .= " AND " . $alias . "goods_code IN ({$goods_str}) ";
                $arr = array('sql' => $sql_main , 'value' => $sql_values);
                $data = $arr;
            } else if(!empty($ret) && $type == 'data'){
                $data = $ret;
            }
        }
        return $data;                
    }
    /**
     * 商品调价单获取分销商品名
     *
     *
     */
    function get_fx_goods_name($goods_code){
        $sql='SELECT goods_name FROM base_goods WHERE is_custom_money = 1 AND status = 0 AND goods_code = :goods_code';
        $res=$this->db->get_row($sql,array(':goods_code'=>$goods_code));
        return $res['goods_name'];

    }
    
    
    /**
     * 导入分销款
     * @param type $file
     * @return type
     */
    function imoprt_detail($file) {
        $user_code = CTX()->get_session('user_code');
        $user_name = CTX()->get_session('user_name');
        $this->read_csv_sku($file, $goods_code_arr, $goods_code_info);
        //编码去重
        $goods_code_arr = array_unique($goods_code_arr);
        $all_num = count($goods_code_info); //导入总数量
        $error_msg = array(); //错误信息
        $err_num = 0; //错误数量
        //验证编码是否存在
        $sql_value = array();
        $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_value);
        $sql = " SELECT goods_code,sell_price FROM base_goods WHERE goods_code IN ({$goods_code_str})";
        $goods_info = $this->db->get_all($sql, $sql_value);

        //符合条件的分销商
        $purview_custom_arr = $this->get_purview_custom('pt_fx');
        $import_goods = array(); //组装导入商品信息
        $fx_appoint_goods = array(); //指定分销商
        $base_goods_info = load_model('util/ViewUtilModel')->get_map_arr($goods_info, 'goods_code');
        foreach ($goods_code_info as $key => $value) {
            //商品编码是否存在
            if (!array_key_exists($value['goods_code'], $base_goods_info)) {
                $error_msg[] = array($value['goods_code'] . "\t" => '商品编码在系统中不存在！');
                $err_num++;
                continue;
            }
            //指定分销商
            if (isset($value['custom_code']) && $value['custom_code'] != '') {
                //判断分销商是否满足条件
                if (!in_array($value['custom_code'], $purview_custom_arr)) {
                    $error_msg[] = array($value['goods_code'] . "({$value['custom_code']})" . "\t" => '分销商编码不存在！');
                    $err_num++;
                    continue;
                }
                //分销价
                if (isset($value['fx_price']) && $value['fx_price'] != '') {
                    if (!preg_match("/^[0-9]*(\.[0-9]{1,2})?$/", $value['fx_price'])) {
                        $error_msg[] = array($value['goods_code'] . "({$value['custom_code']})" . "\t" => '金额格式不正确！');
                        $err_num++;
                        continue;
                    }
                    $value['fx_price'] = sprintf("%.2f", $value['fx_price']);
                    $value['fx_rebate'] = (float) $value['fx_price'] / $base_goods_info[$value['goods_code']]['sell_price'];
                    $value['fx_rebate'] = sprintf("%.2f", $value['fx_rebate']);
                } else if (isset($value['fx_rebate']) && $value['fx_rebate'] != '') {
                    if ($value['fx_rebate'] > 1 || $value['fx_rebate'] < 0) {
                        $error_msg[] = array($value['goods_code'] . "({$value['custom_code']})" . "\t" => '折扣格式不正确！');
                        $err_num++;
                        continue;
                    }
                    $value['fx_rebate'] = sprintf("%.2f", $value['fx_rebate']);
                    $value['fx_price'] = (float) $base_goods_info[$value['goods_code']]['sell_price'] * $value['fx_rebate'];
                    $value['fx_price'] = sprintf("%.2f", $value['fx_price']);
                } else {
                    $error_msg[] = array($value['goods_code'] . "({$value['custom_code']})" . "\t" => '折扣和分销价为空');
                    $err_num++;
                    continue;
                }
                $fx_appoint_goods[] = array(
                    'goods_code' => $value['goods_code'],
                    'custom_code' => $value['custom_code'],
                    'user_code' => $user_code,
                    'modify_name' => $user_name,
                    'fx_price' => $value['fx_price'],
                    'fx_rebate' => $value['fx_rebate'],
                );
            }
            $import_goods[] = $value['goods_code'];
        }
        $this->begin_trans();
        //更新分销款
        if (!empty($import_goods)) {
            $sql_value_money=array();
            $is_custom_money_update = $this->arr_to_in_sql_value($import_goods, 'is_custom_money_goods_code', $sql_value_money);
            $sql_update = "UPDATE base_goods SET is_custom_money=1 WHERE goods_code IN ({$is_custom_money_update}) ";
            $ret = $this->query($sql_update, $sql_value_money);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '设置分销款失败！');
            }
        }
        //指定分销商
        if (!empty($fx_appoint_goods)) {
            $sql_value_custom=array();
            $update_str = "user_code = VALUES(user_code),modify_name = VALUES(modify_name),fx_price = VALUES(fx_price),fx_rebate = VALUES(fx_rebate)";
            $ret = $this->insert_multi_duplicate('fx_appoint_goods', $fx_appoint_goods, $update_str);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '指定分销商失败！');
            }
            //更新指定分销商
            $is_custom_goods_code = array_column($fx_appoint_goods, 'goods_code');
            $is_custom_update = $this->arr_to_in_sql_value($is_custom_goods_code, 'is_custom_goods_code', $sql_value_custom);
            $sql_update = "UPDATE base_goods SET is_custom=1 WHERE goods_code IN ({$is_custom_update}) ";
            $ret = $this->query($sql_update, $sql_value_custom);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $this->format_ret('-1', '', '指定分销商失败！');
            }
        }
        $this->commit();
        $ret['data'] = '';
        $ret['status'] = '1';
        $success_num = $all_num - $err_num;
        $message = '导入成功' . $success_num;
        if ($err_num > 0 || !empty($error_msg)) {
            $ret['status'] = '-1';
            $message .=',' . '失败数量:' . $err_num;
            $fail_top = array('商品编码', '错误信息');
            $file_name = $this->create_import_fail_files($fail_top, $error_msg);
//            $message .= "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" >下载</a>";
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        $ret['message'] = $message;
        return $ret;
    }

    /**
     * 读取数据
     * @param type $file
     * @param type $sku_arr
     * @param type $sku_num
     */
    function read_csv_sku($file, &$goods_code_arr, &$goods_code_info) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i > 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $goods_code = trim($row[0]);
                    $goods_code_arr[] = $goods_code;
                    $goods_code_info[$i]['custom_code'] = trim($row[1]);
                    $goods_code_info[$i]['fx_rebate'] = trim($row[2]);
                    $goods_code_info[$i]['fx_price'] = trim($row[3]);
                    $goods_code_info[$i]['goods_code'] = $goods_code;
                }
            }
            $i++;
        }
        fclose($file);
    }

    /**
     * 处理特殊字符
     * @param type $row
     */
    private function tran_csv(&$row) {
        if (!empty($row)) {
            foreach ($row as &$val) {
             //   $val = iconv('gbk', 'utf-8', $val);
                $val = str_replace('"', '', $val);
            }
        }
    }

    
    //获取符合条件的分销商
    function get_purview_custom($custom_type = 'pt_fx') {
        $sql_values = array();
        $sql_main = "SELECT custom_code FROM base_custom bs where is_effective = 1  ";
        $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('bs.custom_code');
        
        $sql_user = "SELECT user_code FROM sys_user WHERE (status = 2 OR status = 0) AND login_type = 2;";
        $user_code = $this->db->get_all_col($sql_user);
        if (!empty($user_code)) {
            $user_str = $this->arr_to_in_sql_value($user_code, 'user_code', $sql_values);
            $sql_main .= " AND (bs.user_code NOT IN ({$user_str}) OR bs.user_code is null) ";
        }
        //分销商类型，淘分销/普通分销
        if (isset($custom_type) && $custom_type != '') {
            $sql_main .= " AND bs.custom_type = :custom_type";
            $sql_values[':custom_type'] = "{$custom_type}";
        }
        $ret = $this->db->get_all_col($sql_main, $sql_values);
        return $ret;
    }

    
        /**
     * 下载错误信息
     * @param type $fail_top
     * @param type $error_msg
     * @return type
     */
    function create_import_fail_files($fail_top, $error_msg) {
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($error_msg as $key => $val) {
            $key = array_keys($val);
            $val_data = array($key[0], $val[$key[0]]);
            $file_str .= implode(",", $val_data) . "\r\n";
        }
        $filename = md5("stock_adjust_record" . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
    
    /**
     * 添加分销款
     * @param type $goods_info
     * @return type
     */
        function add_fx_goods($goods_info) {
        $goods_code_arr = array_column($goods_info, 'goods_code');
        $sql_value = array();
        $goods_code_str = $this->arr_to_in_sql_value($goods_code_arr, 'goods_code', $sql_value);
        $sql = "UPDATE base_goods SET is_custom_money=1 WHERE goods_code IN ({$goods_code_str})";
        $ret = $this->query($sql, $sql_value);
        if ($ret['status'] != 1) {
            return $this->format_ret('-1', '', '添加失败！');
        }
        return $this->format_ret('1', '', '添加成功！');
    }
    
    /**
     * 一键清除分销款商品
     */
    function remove_all_goods($filter) {
        $filter['is_return'] = 1;
        $ret = $this->get_by_page($filter);
        $sql = 'SELECT rl.goods_code ' . $ret['sql'];
        $goods_code_arr = $this->db->get_all_col($sql, $ret['value']);
        
        $this->begin_trans();
        
        $sql_values = array();
        $sql_main = "UPDATE base_goods SET is_custom_money = 0, is_custom = 0 WHERE 1 ";
        if(!empty($goods_code_arr)) {
            $goods_str = $this->arr_to_in_sql_value($goods_code_arr,'goods_code',$sql_values);
            $sql_main .= " AND goods_code IN ({$goods_str}) ";
        }
        $ret = $this->query($sql_main,$sql_values);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','清除失败');
        }
        
        //分销商品中间表清空
//        $ret = $this->delete_exp('fx_appoint_goods');
        $sql_values = array();
        $sql_del = "DELETE FROM fx_appoint_goods WHERE 1 ";
        if(!empty($goods_code_arr)) {
            $goods_str = $this->arr_to_in_sql_value($goods_code_arr,'goods_code',$sql_values);
            $sql_del .= " AND goods_code IN ({$goods_str}) ";
        }
        $ret = $this->query($sql_del,$sql_values);
        if($ret['status'] < 0) {
            $this->rollback();
            return $this->format_ret(-1,'','清空分销商品失败');
        }
        
        $this->commit();
        return $this->format_ret(-1,'','清空成功');
    }
    /**
     * 一键添加分销款商品
     */
    function set_all_goods_fx($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $sql_values = array();
        $sql_main = "UPDATE base_goods SET is_custom_money = 1 WHERE 1 ";
        //品牌
        $sql_main .= load_model('prm/BrandModel', true, false, 'webefast')->get_sql_purview_brand('brand_code', $filter_brand_code);

        //分类
        if (isset($filter['category_code']) && $filter['category_code'] != '') {
            $category_code_arr = explode(',', $filter['category_code']);
            if (!empty($category_code_arr)) {
                $sql_main .= " AND (";
                foreach ($category_code_arr as $key => $value) {
                    $param_category = 'param_category' . $key;
                    if ($key == 0) {
                        $sql_main .= " category_code = :{$param_category} ";
                    } else {
                        $sql_main .= " or category_code = :{$param_category} ";
                    }

                    $sql_values[':' . $param_category] = $value;
                }
                $sql_main .= ")";
            }
        }

        //年份
        if (isset($filter['year_code']) && $filter['year_code'] != '') {
            $year_code_arr = explode(',', $filter['year_code']);
            if (!empty($year_code_arr)) {
                $sql_main .= " AND (";
                foreach ($year_code_arr as $key => $value) {
                    $param_year = 'param_year' . $key;
                    if ($key == 0) {
                        $sql_main .= " year_code = :{$param_year} ";
                    } else {
                        $sql_main .= " or year_code = :{$param_year} ";
                    }

                    $sql_values[':' . $param_year] = $value;
                }
                $sql_main .= ")";
            }
        }
        //季节
        if (isset($filter['season_code']) && $filter['season_code'] != '') {
            $season_code_arr = explode(',', $filter['season_code']);
            if (!empty($season_code_arr)) {
                $sql_main .= " AND (";
                foreach ($season_code_arr as $key => $value) {
                    $param_season = 'param_season' . $key;
                    if ($key == 0) {
                        $sql_main .= " season_code = :{$param_season} ";
                    } else {
                        $sql_main .= " or season_code = :{$param_season} ";
                    }

                    $sql_values[':' . $param_season] = $value;
                }
                $sql_main .= ")";
            }
        }
        //商品编码
        if (isset($filter['goods_code']) && $filter['goods_code'] !== '') {
            $sql_main .= " AND goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] !== '') {
            $sql_main .= " AND goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        $ret = $this->query($sql_main, $sql_values);
        return $ret;
    }

    /**
     * 获取分销款商品的sku信息
     */
    function get_by_fx_goods_sku($select = '*',$barcode_arr = '') {
        $goods_data = $this->get_fx_money_goods('rl.goods_code','data');
        $goods_arr = array_column($goods_data,'goods_code');
        if(empty($goods_arr)) {
            return array();
        }
        $sql_values = array();
        $goods_str = $this->arr_to_in_sql_value($goods_arr,'goods_code',$sql_values);
        $sql = "SELECT {$select} FROM goods_sku WHERE goods_code in ($goods_str) ";
        if(!empty($barcode_arr)) {
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr,'barcode',$sql_values);
            $sql .= " AND barcode in ({$barcode_str}) ";
        }
        $sku_arr = $this->db->get_all($sql,$sql_values);
        return $sku_arr;
    }
    /*
     * 获取指定了分销商的商品的sku信息
     */
    function get_custom_goods_sku($select = 'rl.*,r2.*', $custom_code_arr = '') {
        $sql_values = array();
        $custom_code_str = $this->arr_to_in_sql_value($custom_code_arr, 'custom_code', $sql_values);
        $sql = "SELECT {$select} FROM fx_appoint_goods AS rl INNER JOIN goods_sku AS r2 ON rl.goods_code = r2.goods_code WHERE rl.custom_code IN ($custom_code_str) ";
        $goods_data = $this->db->get_all($sql,$sql_values);
        if(empty($goods_data)) {
            return array();
        }
        $custom_goods_arr = array();
        foreach($goods_data as $val) {
            $custom_goods_arr[$val['custom_code']][] = $val['barcode'];
        }
        return $custom_goods_arr;
    }
    /**
     * 查找开启分销款没指定分销商的商品
     */
    function fx_goods_no_custom($barcode_arr, $select = 'rl.*,r2.*') {
        $sql_values = array();
        $barcode_str = $this->arr_to_in_sql_value($barcode_arr,'barcode',$sql_values);
        $sql = "SELECT {$select} FROM base_goods AS rl INNER JOIN goods_sku AS r2 ON rl.goods_code = r2.goods_code WHERE rl.is_custom_money = 1 AND rl.is_custom = 0 AND `status` = 0 AND r2.barcode IN ({$barcode_str}) ";
        $fx_barcode = $this->db->get_all($sql,$sql_values);
        return $fx_barcode;        
    }

    /**
     * 根据条件查询分销商品
     * @param $cond
     * @param string $select
     */
    public function get_col_by_cond($cond,$select='*'){
        $sql_values = array();
        $fx_sql = 'select '.$select.' from base_goods where is_custom_money = 1 ';
        foreach($cond as $k=>$value){
            if(is_array($value)){
                $k_str = $this->arr_to_in_sql_value($value,$k,$sql_values);
                $fx_sql .= ' and '.$k.' in('.$k_str.') ';
            }else{
                $k_str = ':'.$k;
                $sql_values[$k_str] = $value;
                $fx_sql .= ' and '.$k.' = '.$k_str.' ';
            }

        }
        return $this->db->get_all($fx_sql,$sql_values);
    }
}

    
  
