<?php

/**
 * 商品条码管理相关业务
 *
 * @author dfr
 *
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('prm');

class GoodsUniqueCodeTLModel extends TbModel {

    function get_table() {
        return 'goods_unique_code_tl';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_values = array();
        $sql_join = "";
        $sql_main = "FROM {$this->table}  WHERE 1";

        
         $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
         $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('store_code', $filter_store_code);
        //商品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
        //商品条码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND barcode LIKE :barcode ";
            $sql_values[':barcode'] = $filter['barcode'] . '%';
        }

	//批量查询唯一码
        if (isset($filter['unique_code']) && $filter['unique_code'] != '') {
            $arr = explode(',', $filter['unique_code']);
            $str = $this->arr_to_like_sql_value($arr, 'unique_code', $sql_values);
            $sql_main .= " AND " . $str;
        }
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND store_code in ( " . $str. " ) ";
        }
        //tab 标签

        if (isset($filter['do_list_tab']) && $filter['do_list_tab'] != '') {
            if ($filter['do_list_tab'] == 'tabs_allow') {
                $sql_main .= " AND  status = :status ";
                $sql_values[':status'] = 0;
            }
            if ($filter['do_list_tab'] == 'tabs_not_allow') {
                $sql_main .= " AND  status = :status ";
                $sql_values[':status'] = 1;
            }
        }
        $sql_main .= " ORDER BY lastchanged DESC ";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => &$value) {
            $value['store_name'] = oms_tb_val('base_store', 'store_name', array('store_code' => $value['store_code']));
            $value['is_allow_name'] = $value['status'] == 1 ? '不可用' : '可用';
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {

        return $this->get_row(array('barcode_id' => $id));
    }

    function get_by_unique_code($unique_code) {
        $sql_data = "select * from goods_unique_code_tl where unique_code=:unique_code";
        $sql_value = array(':unique_code' => $unique_code);
        $ret = $this->db->get_row($sql_data, $sql_value);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '查询失败');
        }
        return $ret;
    }

    /*
     * 修改唯一码纪录
     */

    function update($goods, $unique_code) {
        //var_dump($goods);die;
//        $status = $this->valid($goods, true);
//        if ($status < 1) {
//            return $this->format_ret($status);
//        }
        //$ret['data'] = get_by_unique_code($request['unique_code']); //取旧数据
//        $log_info = '';
//        $log_info .= $this->get_log_data((float)$ret['data']['total_price'],(float)$goods['total_price'],'销售含税价');
//        if(!empty($log_info)) {
//            $data = array(
//                'unique_code' => $unique_code,
//                'operation_note' => $log_info,
//                'operation_name' => '修改唯一码商品'
//            );
//            //添加日志
//            $ret = $this->insert_goods_log($data);
//            if($ret['status'] < 0) {
//                return $this->format_ret(-1,'','保存日志出错');
//            }
//           }
        //$ret = parent::update($goods, array('unique_code' => $unique_code));
        //var_dump($ret);die;
        $sql = "update goods_unique_code_tl set total_price='{$goods['total_price']}' where unique_code ='{$unique_code}'";
        $ret1 = $this->db->query($sql);
        return $ret1;
    }

    /*
     * 删除唯一吗
     * */

    function unique_delete($unique_code) {
        //特殊字符转义
        $unique_code = html_entity_decode($unique_code);
        $msg = '';
        $sql_inv = "SELECT unique_code FROM goods_unique_code_log WHERE unique_code=:unique_code";
        $sql_value = array(':unique_code' => $unique_code);
        $ret_inv = $this->db->get_row($sql_inv, $sql_value);

        if (!empty($ret_inv)) {
            $msg = '唯一码已经在业务中被使用,';
        }

        $sql_status = "SELECT status FROM goods_unique_code_tl WHERE unique_code=:unique_code";
        $ret_status = $this->db->get_row($sql_status, $sql_value);
        //var_dump($ret_status);die;
        if ($ret_status['status'] != 0) {
            $msg .= '唯一码已出库,';
        }
        if (!empty($msg)) {
            $msg .= '不能删除';
            return $this->format_ret(-1, '', $msg);
        }

        $this->begin_trans();
        //$sql_delete = "delete from goods_unique_code_tl where unique_code=:unique_code";
        //$ret_delete = $this->db->get_row($sql_delete, $sql_value);
        $ret = parent::delete_exp('goods_unique_code_tl', array('unique_code' => $unique_code));
        $ret2 = parent::delete_exp('goods_unique_code', array('unique_code' => $unique_code));
        //var_dump($ret2);die;
        if ($ret != TRUE || $ret2 != TRUE) {
            $this->rollback();

            return $ret;
        }

//        //删除绑定的库位
//        $ret2 = parent::delete_exp('goods_shelf', array('goods_code' => $goods_code));
//        if($ret2 != TRUE) {
//            $this->rollback();
//            return $ret;
//        }
        $this->commit();
        return $ret;
    }

    //编辑唯一码商品
    function do_edit($unique_code) {
        
    }

    /*
     * 服务器端验证
     */

    private function valid($goods_code, $spec1_code, $spec2_code, $is_edit = false) {
        if (!$is_edit && (!isset($goods_code) || !valid_input($goods_code, 'required')))
            return GOODS_ERROR_CODE1;
        if (!isset($spec1_code) || !valid_input($spec1_code, 'required'))
            return GOODS_ERROR_NAME2;
        if (!isset($spec2_code) || !valid_input($spec2_code, 'required'))
            return GOODS_ERROR_NAME3;
        return 1;
    }

    /**
     * 判断是否存在
     * @param $value
     * @param string $field_name
     * @return array
     */
    function is_exists($value, $field_name = 'barcode_code') {
        $ret = parent::get_row(array($field_name => $value));

        return $ret;
    }

    /**
     * 新增
     * @param $skuCode
     * @param $storeCode
     * @param $shelfCode
     * @param string $batchNumber
     * @return array
     */
    function add($file, $name, $store_code = '') {
        require_lib('csv_util');
        $exec = new execl_csv();
        $key_arr = array(
            'unique_code',
            'barcode',
            'good_revenue_code',
            'factory_code',
            'tongling_code',
            'goods_name',
            'relative_purity',
            'relative_purity_of_gold',
            'international_num',
            'check_station_num',
            'identity_num',
            'jewelry_brand',
            'jewelry_brand_child',
            'metal_color',
            'jewelry_color',
            'jewelry_clarity',
            'jewelry_cut',
            'pri_diamond_weight',
            'pri_diamond_count',
            'ass_diamond_weight',
            'ass_diamond_count',
            'total_weight',
            'jewelry_type',
            'ring_size',
            'total_price',
            'credential_type',
            'credential_weight',
            'record_num',
            'short_name',
            'user_defined_property_1',
            'user_defined_property_2',
            'user_defined_property_3',
            'user_defined_property_4',
            'user_defined_property_5',
            'user_defined_property_6',
            'user_defined_property_7',
            'user_defined_property_8',
        );
        $csv_data = $exec->read_csv($file, 1, $key_arr, $key_arr);
        $result_old_data = array();
        if (is_array($csv_data) && count($csv_data) > 0) {
            $tips = array();
            foreach ($csv_data as $key => $value) {
                $rus = $this->is_valid_excel_data($value, $key);
                if ($rus['status'] == 1) {
                    $sql = "select sku from goods_sku where barcode = :code";
                    $sku = $this->db->get_row($sql, array(':code' => $value['barcode']));
                    if (empty($sku)) {
                        $tips[] = "商品条形码不存在:" . $value['barcode'] . ",";
                        continue;
                    }
                    $sql = "select * from goods_unique_code_tl where unique_code = :unique_code";
                    $unique = $this->db->get_row($sql, array(':unique_code' => $value['unique_code']));
                    if (!empty($unique) && $unique['status'] != 0) { //存在唯一码且不可用
                        $tips[] = "唯一码已存在且不可用:" . $value['unique_code'] . ",";
                        continue;
                    }
                    if(empty($unique)){//唯一码不存在的记录下来
                      $unique_arr[] = $value['unique_code'];
                    }
                    $in_data = $value;
                    $in_data['store_code'] = $store_code;
                    $in_data['sku'] = $sku['sku'];
                    $in_data['lastchanged'] = date("Y-m-d H:i:s", time());
                    $d2 = array(
                        'unique_code' => $value['unique_code'],
                        'sku' => $sku['sku'],
                        'status' => 0,
                    );
                    $result_data[] = $in_data;
                    array_push($result_old_data, $d2);
                } else {
                    
                    $tips[] = $rus;
                }
            }
        } else {
            return $this->format_ret('-1', '', '没有需要导入的唯一码！');
        }

        $success_num = count($result_data);
        $sut = array();
        $all_count = count($csv_data);
        
        $ret = $this->insert_multi_duplicate('goods_unique_code_tl', $result_data,$result_data);
        $this->insert_multi_duplicate('goods_unique_code', $result_old_data, $result_old_data);

          if(isset($unique_arr) && !empty($unique_arr)){
              $sut = $unique_arr;
          }
 
        if ($ret['status'] == 1) {
            if (!empty($tips)) {
                $msg = $this->get_error_msg($tips, $success_num, $all_count, 1);
            } else {
                $msg = '导入成功';
            }
            $ret = array('status' => '1',
                'data' => $sut,
                'msg' => $msg
            );
        } else {
            $msg = $this->get_error_msg($tips, $success_num, $all_count, 0);
            $ret = array(
                'status' => '-1',
                'tip' => '',
                'msg' => $msg
            );
        }
        return $ret;
    }

    /**
     * 判定导入数据是否有效
     * @param type $row_data 行数据
     * @return true 有效 false 无效
     */
    function is_valid_excel_data($row_data, $key) {
        $key += 2;
        if ($row_data['unique_code'] == '') {
            $err = '第' . $key . '行唯一码不能为空;';
            return $err;
        }
        if ($row_data['barcode'] == '') {
            $err = '第' . $key . '行条形码不能为空;';
            return $err;
        }
        if ($row_data['good_revenue_code'] == '') {
            $err = '第' . $key . '行税收分类编码不能为空;';
            return $err;
        }
        return $this->format_ret(1);
    }

    function get_error_msg($err_msg, $success_num, $all_count, $type) {
        $msg = '';
        if ($type == 1) {
            $msg .= '导入成功' . $success_num . '条信息，导入失败' . ($all_count - $success_num) . '条信息，失败信息';
        } else {
            $msg .= '导入成功 0 条信息，导入失败' . $all_count . '条信息，失败信息';
        }
        $file_name = load_model('prm/GoodsImportModel')->create_import_fail_files($err_msg, 'goods_unique_code_tl_import');
//    	$msg .= "<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name=error\" > 下载 </a>";
        $url = set_download_csv_url($file_name, array('export_name' => 'error'));
        $msg .= "<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $msg;
    }

    /**
     * 批量添加纪录
     */
    function insert($data) {
        $ret = $this->insert_multi($data, true);
        return $ret;
    }

    //获取导入成功的唯一码的信息
    function get_detail($ids) {
        $sql_arr = array();
        $sql = "select count(*) as num,a.goods_name,b.* from goods_unique_code_tl a inner join goods_sku b on a.sku=b.sku where 1 AND jewelry_id IN ({$ids}) group by a.barcode";
        $data = $this->db->get_all($sql);
        //var_dump($data);die;
        return $data;
    }

    //获取仓库代码
    function get_store($ids) {
        $sql = "select a.store_name,a.store_code from base_store a left join goods_unique_code_tl b on a.store_code = b.store_code where jewelry_id in ({$ids});";
        $data = $this->db->get_row($sql);
        return $data;
    }

    //判断所选仓库代码是否一致
    function opt_store($ids) {
        $str = implode(',', $ids);
        if (empty($str)) {
            return array('status' => '-1', 'data' => '', 'message' => '传入参数不正确');
        }

        $sql = "SELECT store_code FROM `goods_unique_code_tl` WHERE jewelry_id in ({$str}) GROUP BY store_code;";
        $data = $this->db->get_all($sql);
        if (isset($data) && count($data) > 1) {
            return array('status' => '-1', 'data' => '', 'message' => '请选择同一个仓库');
        }

        return $this->format_ret(1, $str, '');
    }

    function unsave_store($store_code, $ids) {
        $sql = "update goods_unique_code_tl set store_code = '{$store_code}' where jewelry_id in ({$ids})";
        $ret = $this->query($sql);
        return $ret;
    }

    //生成移仓单所需数据
    function get_ware_detail($ids) {
        $sql_values = [];
        $arr = explode(',',$ids);
        $str = $this->arr_to_in_sql_value($arr, 'jewelry_id', $sql_values);
        $sql = "select count(*) as num,b.*,bg.purchase_price,bg.sell_price from goods_unique_code_tl a inner join goods_sku b on a.sku=b.sku 
                inner join base_goods bg on bg.goods_code = b.goods_code
                where 1 AND jewelry_id IN ({$str}) group by a.barcode";
        $data = $this->db->get_all($sql,$sql_values);
        return $data;
    }
    
    public function get_detail_by_unique($unique_str) {
        $sql = "select count(*) as num,a.goods_name,b.*,bg.purchase_price,bg.sell_price from goods_unique_code_tl a inner join goods_sku b on a.sku=b.sku 
                inner join base_goods bg on bg.goods_code = b.goods_code
                 where 1 AND unique_code IN ({$unique_str}) group by a.barcode";
        $data = $this->db->get_all($sql);
        return $data;
    }
    
    /*
     * 唯一码库存查询列表
     */
    
    function get_inv_by_page($filter){
        $sql_values = array();
        
        $sql_main = "FROM {$this->table} rl
        			INNER JOIN goods_sku r2 on rl.sku = r2.sku
                                INNER JOIN base_goods bg on r2.goods_code = bg.goods_code
        			WHERE 1";
        
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('rl.store_code', $filter_store_code);
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str. " ) ";
        }
         //状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND rl.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
        //商品名称
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $sql_main .= " AND bg.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND rl.barcode LIKE :barcode ";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        //商品唯一码
        if (isset($filter['unique_code']) && $filter['unique_code'] != '') {
            $sql_main .= " AND rl.unique_code LIKE :unique_code ";
            $sql_values[':unique_code'] = '%' . $filter['unique_code'] . '%';
        }
         //商品名称
        if (isset($filter['goods_code_name']) && $filter['goods_code_name'] != '') {
            $sql_main .= " AND bg.goods_name LIKE :goods_code_name ";
            $sql_values[':goods_code_name'] = '%' . $filter['goods_code_name'] . '%';
        }
         //品牌
        if (isset($filter['jewelry_brand']) && $filter['jewelry_brand'] != '') {
            $sql_main .= " AND rl.jewelry_brand LIKE :jewelry_brand ";
            $sql_values[':jewelry_brand'] = '%' . $filter['jewelry_brand'] . '%';
        }
         //子品牌
        if (isset($filter['jewelry_brand_child']) && $filter['jewelry_brand_child'] != '') {
            $sql_main .= " AND rl.jewelry_brand_child LIKE :jewelry_brand_child ";
            $sql_values[':jewelry_brand_child'] = '%' . $filter['jewelry_brand_child'] . '%';
        }
         //检测站证书号
        if (isset($filter['check_station_num']) && $filter['check_station_num'] != '') {
            $sql_main .= " AND rl.check_station_num LIKE :check_station_num ";
            $sql_values[':check_station_num'] = '%' . $filter['check_station_num'] . '%';
        }
          //金属颜色
        if (isset($filter['metal_color']) && $filter['metal_color'] != '') {
            $sql_main .= " AND rl.metal_color LIKE :metal_color ";
            $sql_values[':metal_color'] = '%' . $filter['metal_color'] . '%';
        }
          //手寸长度
        if (isset($filter['ring_size']) && $filter['ring_size'] != '') {
            $sql_main .= " AND rl.ring_size LIKE :ring_size ";
            $sql_values[':ring_size'] = '%' . $filter['ring_size'] . '%';
        }
         //系统sku码
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND rl.sku LIKE :sku ";
            $sql_values[':sku'] = '%' . $filter['sku'] . '%';
        }
          //通灵款
        if (isset($filter['tongling_code']) && $filter['tongling_code'] != '') {
            $sql_main .= " AND rl.tongling_code LIKE :tongling_code ";
            $sql_values[':tongling_code'] = '%' . $filter['tongling_code'] . '%';
        }
          //饰品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND rl.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
          //成色
        if (isset($filter['relative_purity']) && $filter['relative_purity'] != '') {
            $sql_main .= " AND rl.relative_purity LIKE :relative_purity ";
            $sql_values[':relative_purity'] = '%' . $filter['relative_purity'] . '%';
        }
           //金成色
        if (isset($filter['relative_purity_of_gold']) && $filter['relative_purity_of_gold'] != '') {
            $sql_main .= " AND rl.relative_purity_of_gold LIKE :relative_purity_of_gold ";
            $sql_values[':relative_purity_of_gold'] = '%' . $filter['relative_purity_of_gold'] . '%';
        }
           //国际证书号
        if (isset($filter['international_num']) && $filter['international_num'] != '') {
            $sql_main .= " AND rl.international_num LIKE :international_num ";
            $sql_values[':international_num'] = '%' . $filter['international_num'] . '%';
        }
           //颜色
        if (isset($filter['jewelry_color']) && $filter['jewelry_color'] != '') {
            $sql_main .= " AND rl.jewelry_color LIKE :jewelry_color ";
            $sql_values[':jewelry_color'] = '%' . $filter['jewelry_color'] . '%';
        }
            //身份证
        if (isset($filter['identity_num']) && $filter['identity_num'] != '') {
            $sql_main .= " AND rl.identity_num LIKE :identity_num ";
            $sql_values[':identity_num'] = '%' . $filter['identity_num'] . '%';
        }
           //商品税收分类编码
        if (isset($filter['good_revenue_code']) && $filter['good_revenue_code'] != '') {
            $sql_main .= " AND rl.good_revenue_code LIKE :good_revenue_code ";
            $sql_values[':good_revenue_code'] = '%' . $filter['good_revenue_code'] . '%';
        }
          //厂家款号
        if (isset($filter['factory_code']) && $filter['factory_code'] != '') {
            $sql_main .= " AND rl.factory_code LIKE :factory_code ";
            $sql_values[':factory_code'] = '%' . $filter['factory_code'] . '%';
        }
          //净度
        if (isset($filter['jewelry_clarity']) && $filter['jewelry_clarity'] != '') {
            $sql_main .= " AND rl.jewelry_clarity LIKE :jewelry_clarity ";
            $sql_values[':jewelry_clarity'] = '%' . $filter['jewelry_clarity'] . '%';
        }
          //切工
        if (isset($filter['jewelry_cut']) && $filter['jewelry_cut'] != '') {
            $sql_main .= " AND rl.jewelry_cut LIKE :jewelry_cut ";
            $sql_values[':jewelry_cut'] = '%' . $filter['jewelry_cut'] . '%';
        }
          //主石重量
        if (isset($filter['pri_diamond_weight']) && $filter['pri_diamond_weight'] != '') {
            $sql_main .= " AND rl.pri_diamond_weight LIKE :pri_diamond_weight ";
            $sql_values[':pri_diamond_weight'] = '%' . $filter['pri_diamond_weight'] . '%';
        }
          //主石数量
        if (isset($filter['pri_diamond_count']) && $filter['pri_diamond_count'] != '') {
            $sql_main .= " AND rl.pri_diamond_count LIKE :pri_diamond_count ";
            $sql_values[':pri_diamond_count'] = '%' . $filter['pri_diamond_count'] . '%';
        }
          //辅石重量
        if (isset($filter['ass_diamond_weight']) && $filter['ass_diamond_weight'] != '') {
            $sql_main .= " AND rl.ass_diamond_weight LIKE :ass_diamond_weight ";
            $sql_values[':ass_diamond_weight'] = '%' . $filter['ass_diamond_weight'] . '%';
        }
         //辅石数量
        if (isset($filter['ass_diamond_count']) && $filter['ass_diamond_count'] != '') {
            $sql_main .= " AND rl.ass_diamond_count LIKE :ass_diamond_count ";
            $sql_values[':ass_diamond_count'] = '%' . $filter['ass_diamond_count'] . '%';
        }
        //珠宝总重量
        if (isset($filter['total_weight']) && $filter['total_weight'] != '') {
            $sql_main .= " AND rl.total_weight LIKE :total_weight ";
            $sql_values[':total_weight'] = '%' . $filter['total_weight'] . '%';
        }
        //类别
        if (isset($filter['jewelry_type']) && $filter['jewelry_type'] != '') {
            $sql_main .= " AND rl.jewelry_type LIKE :jewelry_type ";
            $sql_values[':jewelry_type'] = '%' . $filter['jewelry_type'] . '%';
        }
        //销售含税价
        if (isset($filter['total_price']) && $filter['total_price'] != '') {
            $sql_main .= " AND rl.total_price LIKE :total_price ";
            $sql_values[':total_price'] = '%' . $filter['total_price'] . '%';
        }
        //证书类型
        if (isset($filter['credential_type']) && $filter['credential_type'] != '') {
            $sql_main .= " AND rl.credential_type LIKE :credential_type ";
            $sql_values[':credential_type'] = '%' . $filter['credential_type'] . '%';
        }
        //证书重量
        if (isset($filter['credential_weight']) && $filter['credential_weight'] != '') {
            $sql_main .= " AND rl.credential_weight LIKE :credential_weight ";
            $sql_values[':credential_weight'] = '%' . $filter['credential_weight'] . '%';
        }
        //货单号
        if (isset($filter['record_num']) && $filter['record_num'] != '') {
            $sql_main .= " AND rl.record_num LIKE :record_num ";
            $sql_values[':record_num'] = '%' . $filter['record_num'] . '%';
        }
        //饰品简称
        if (isset($filter['short_name']) && $filter['short_name'] != '') {
            $sql_main .= " AND rl.short_name LIKE :short_name ";
            $sql_values[':short_name'] = '%' . $filter['short_name'] . '%';
        }
        //自定义属性1
        if (isset($filter['user_defined_property_1']) && $filter['user_defined_property_1'] != '') {
            $sql_main .= " AND rl.user_defined_property_1 LIKE :user_defined_property_1 ";
            $sql_values[':user_defined_property_1'] = '%' . $filter['user_defined_property_1'] . '%';
        }
        //自定义属性2
        if (isset($filter['user_defined_property_2']) && $filter['user_defined_property_2'] != '') {
            $sql_main .= " AND rl.user_defined_property_2 LIKE :user_defined_property_2 ";
            $sql_values[':user_defined_property_2'] = '%' . $filter['user_defined_property_2'] . '%';
        }
         //自定义属性3
        if (isset($filter['user_defined_property_3']) && $filter['user_defined_property_3'] != '') {
            $sql_main .= " AND rl.user_defined_property_3 LIKE :user_defined_property_3 ";
            $sql_values[':user_defined_property_3'] = '%' . $filter['user_defined_property_3'] . '%';
        }
         //自定义属性4
        if (isset($filter['user_defined_property_4']) && $filter['user_defined_property_4'] != '') {
            $sql_main .= " AND rl.user_defined_property_4 LIKE :user_defined_property_4 ";
            $sql_values[':user_defined_property_4'] = '%' . $filter['user_defined_property_4'] . '%';
        }
         //自定义属性5
        if (isset($filter['user_defined_property_5']) && $filter['user_defined_property_5'] != '') {
            $sql_main .= " AND rl.user_defined_property_5 LIKE :user_defined_property_5 ";
            $sql_values[':user_defined_property_5'] = '%' . $filter['user_defined_property_5'] . '%';
        }
        //自定义属性6
        if (isset($filter['user_defined_property_6']) && $filter['user_defined_property_6'] != '') {
            $sql_main .= " AND rl.user_defined_property_6 LIKE :user_defined_property_6 ";
            $sql_values[':user_defined_property_6'] = '%' . $filter['user_defined_property_6'] . '%';
        }
        //自定义属性7
        if (isset($filter['user_defined_property_7']) && $filter['user_defined_property_7'] != '') {
            $sql_main .= " AND rl.user_defined_property_7 LIKE :user_defined_property_7 ";
            $sql_values[':user_defined_property_7'] = '%' . $filter['user_defined_property_7'] . '%';
        }
        //自定义属性8
        if (isset($filter['user_defined_property_8']) && $filter['user_defined_property_8'] != '') {
            $sql_main .= " AND rl.user_defined_property_8 LIKE :user_defined_property_8 ";
            $sql_values[':user_defined_property_8'] = '%' . $filter['user_defined_property_8'] . '%';
        }
      
        $sql_main .= " ORDER BY rl.lastchanged DESC ";
        $select = 'rl.*,bg.goods_code,bg.goods_name as goods_code_name';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $store_arr= load_model('base/StoreModel')->get_purview_store();
        $store_list = array_column($store_arr, 'store_name', 'store_code');
        
        foreach ($data['data'] as $key => &$value) {
            $value['store_code_name'] = $store_list[$value['store_code']];
            if($value['status'] == 1){
                $value['goods_num'] = 0;
            } else {
                $value['goods_num'] = 1;
            }
        }

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    //唯一吗库存数量统计
    function get_summary($filter){
         if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_main = "";
        $sql_values = array();
        
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('store_code', $filter_store_code);
        $bg = false;
        //仓库
        if (isset($filter['store_code']) && $filter['store_code'] != '') {
            $arr = explode(',',$filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND rl.store_code in ( " . $str. " ) ";
        }
         //状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND rl.status = :status ";
            $sql_values[':status'] = $filter['status'];
        }
        //商品名称
        if (isset($filter['goods_code']) && $filter['goods_code'] != '') {
            $bg = true;
            $sql_main .= " AND bg.goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        //商品条形码
        if (isset($filter['barcode']) && $filter['barcode'] != '') {
            $sql_main .= " AND rl.barcode LIKE :barcode ";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        //商品唯一码
        if (isset($filter['unique_code']) && $filter['unique_code'] != '') {
            $sql_main .= " AND rl.unique_code LIKE :unique_code ";
            $sql_values[':unique_code'] = '%' . $filter['unique_code'] . '%';
        }
         //商品名称
        if (isset($filter['goods_code_name']) && $filter['goods_code_name'] != '') {
            $bg = true;
            $sql_main .= " AND bg.goods_name LIKE :goods_code_name ";
            $sql_values[':goods_code_name'] = '%' . $filter['goods_code_name'] . '%';
        }
         //品牌
        if (isset($filter['jewelry_brand']) && $filter['jewelry_brand'] != '') {
            $sql_main .= " AND rl.jewelry_brand LIKE :jewelry_brand ";
            $sql_values[':jewelry_brand'] = '%' . $filter['jewelry_brand'] . '%';
        }
         //子品牌
        if (isset($filter['jewelry_brand_child']) && $filter['jewelry_brand_child'] != '') {
            $sql_main .= " AND rl.jewelry_brand_child LIKE :jewelry_brand_child ";
            $sql_values[':jewelry_brand_child'] = '%' . $filter['jewelry_brand_child'] . '%';
        }
         //检测站证书号
        if (isset($filter['check_station_num']) && $filter['check_station_num'] != '') {
            $sql_main .= " AND rl.check_station_num LIKE :check_station_num ";
            $sql_values[':check_station_num'] = '%' . $filter['check_station_num'] . '%';
        }
          //金属颜色
        if (isset($filter['metal_color']) && $filter['metal_color'] != '') {
            $sql_main .= " AND rl.metal_color LIKE :metal_color ";
            $sql_values[':metal_color'] = '%' . $filter['metal_color'] . '%';
        }
          //手寸长度
        if (isset($filter['ring_size']) && $filter['ring_size'] != '') {
            $sql_main .= " AND rl.ring_size LIKE :ring_size ";
            $sql_values[':ring_size'] = '%' . $filter['ring_size'] . '%';
        }
         //系统sku码
        if (isset($filter['sku']) && $filter['sku'] != '') {
            $sql_main .= " AND rl.sku LIKE :sku ";
            $sql_values[':sku'] = '%' . $filter['sku'] . '%';
        }
          //通灵款
        if (isset($filter['tongling_code']) && $filter['tongling_code'] != '') {
            $sql_main .= " AND rl.tongling_code LIKE :tongling_code ";
            $sql_values[':tongling_code'] = '%' . $filter['tongling_code'] . '%';
        }
          //饰品名称
        if (isset($filter['goods_name']) && $filter['goods_name'] != '') {
            $sql_main .= " AND rl.goods_name LIKE :goods_name ";
            $sql_values[':goods_name'] = '%' . $filter['goods_name'] . '%';
        }
          //成色
        if (isset($filter['relative_purity']) && $filter['relative_purity'] != '') {
            $sql_main .= " AND rl.relative_purity LIKE :relative_purity ";
            $sql_values[':relative_purity'] = '%' . $filter['relative_purity'] . '%';
        }
           //金成色
        if (isset($filter['relative_purity_of_gold']) && $filter['relative_purity_of_gold'] != '') {
            $sql_main .= " AND rl.relative_purity_of_gold LIKE :relative_purity_of_gold ";
            $sql_values[':relative_purity_of_gold'] = '%' . $filter['relative_purity_of_gold'] . '%';
        }
           //国际证书号
        if (isset($filter['international_num']) && $filter['international_num'] != '') {
            $sql_main .= " AND rl.international_num LIKE :international_num ";
            $sql_values[':international_num'] = '%' . $filter['international_num'] . '%';
        }
           //颜色
        if (isset($filter['jewelry_color']) && $filter['jewelry_color'] != '') {
            $sql_main .= " AND rl.jewelry_color LIKE :jewelry_color ";
            $sql_values[':jewelry_color'] = '%' . $filter['jewelry_color'] . '%';
        }
            //身份证
        if (isset($filter['identity_num']) && $filter['identity_num'] != '') {
            $sql_main .= " AND rl.identity_num LIKE :identity_num ";
            $sql_values[':identity_num'] = '%' . $filter['identity_num'] . '%';
        }
           //商品税收分类编码
        if (isset($filter['good_revenue_code']) && $filter['good_revenue_code'] != '') {
            $sql_main .= " AND rl.good_revenue_code LIKE :good_revenue_code ";
            $sql_values[':good_revenue_code'] = '%' . $filter['good_revenue_code'] . '%';
        }
          //厂家款号
        if (isset($filter['factory_code']) && $filter['factory_code'] != '') {
            $sql_main .= " AND rl.factory_code LIKE :factory_code ";
            $sql_values[':factory_code'] = '%' . $filter['factory_code'] . '%';
        }
          //净度
        if (isset($filter['jewelry_clarity']) && $filter['jewelry_clarity'] != '') {
            $sql_main .= " AND rl.jewelry_clarity LIKE :jewelry_clarity ";
            $sql_values[':jewelry_clarity'] = '%' . $filter['jewelry_clarity'] . '%';
        }
          //切工
        if (isset($filter['jewelry_cut']) && $filter['jewelry_cut'] != '') {
            $sql_main .= " AND rl.jewelry_cut LIKE :jewelry_cut ";
            $sql_values[':jewelry_cut'] = '%' . $filter['jewelry_cut'] . '%';
        }
          //主石重量
        if (isset($filter['pri_diamond_weight']) && $filter['pri_diamond_weight'] != '') {
            $sql_main .= " AND rl.pri_diamond_weight LIKE :pri_diamond_weight ";
            $sql_values[':pri_diamond_weight'] = '%' . $filter['pri_diamond_weight'] . '%';
        }
          //主石数量
        if (isset($filter['pri_diamond_count']) && $filter['pri_diamond_count'] != '') {
            $sql_main .= " AND rl.pri_diamond_count LIKE :pri_diamond_count ";
            $sql_values[':pri_diamond_count'] = '%' . $filter['pri_diamond_count'] . '%';
        }
          //辅石重量
        if (isset($filter['ass_diamond_weight']) && $filter['ass_diamond_weight'] != '') {
            $sql_main .= " AND rl.ass_diamond_weight LIKE :ass_diamond_weight ";
            $sql_values[':ass_diamond_weight'] = '%' . $filter['ass_diamond_weight'] . '%';
        }
         //辅石数量
        if (isset($filter['ass_diamond_count']) && $filter['ass_diamond_count'] != '') {
            $sql_main .= " AND rl.ass_diamond_count LIKE :ass_diamond_count ";
            $sql_values[':ass_diamond_count'] = '%' . $filter['ass_diamond_count'] . '%';
        }
        //珠宝总重量
        if (isset($filter['total_weight']) && $filter['total_weight'] != '') {
            $sql_main .= " AND rl.total_weight LIKE :total_weight ";
            $sql_values[':total_weight'] = '%' . $filter['total_weight'] . '%';
        }
        //类别
        if (isset($filter['jewelry_type']) && $filter['jewelry_type'] != '') {
            $sql_main .= " AND rl.jewelry_type LIKE :jewelry_type ";
            $sql_values[':jewelry_type'] = '%' . $filter['jewelry_type'] . '%';
        }
        //销售含税价
        if (isset($filter['total_price']) && $filter['total_price'] != '') {
            $sql_main .= " AND rl.total_price LIKE :total_price ";
            $sql_values[':total_price'] = '%' . $filter['total_price'] . '%';
        }
        //证书类型
        if (isset($filter['credential_type']) && $filter['credential_type'] != '') {
            $sql_main .= " AND rl.credential_type LIKE :credential_type ";
            $sql_values[':credential_type'] = '%' . $filter['credential_type'] . '%';
        }
        //证书重量
        if (isset($filter['credential_weight']) && $filter['credential_weight'] != '') {
            $sql_main .= " AND rl.credential_weight LIKE :credential_weight ";
            $sql_values[':credential_weight'] = '%' . $filter['credential_weight'] . '%';
        }
        //货单号
        if (isset($filter['record_num']) && $filter['record_num'] != '') {
            $sql_main .= " AND rl.record_num LIKE :record_num ";
            $sql_values[':record_num'] = '%' . $filter['record_num'] . '%';
        }
        //饰品简称
        if (isset($filter['short_name']) && $filter['short_name'] != '') {
            $sql_main .= " AND rl.short_name LIKE :short_name ";
            $sql_values[':short_name'] = '%' . $filter['short_name'] . '%';
        }
        //自定义属性1
        if (isset($filter['user_defined_property_1']) && $filter['user_defined_property_1'] != '') {
            $sql_main .= " AND rl.user_defined_property_1 LIKE :user_defined_property_1 ";
            $sql_values[':user_defined_property_1'] = '%' . $filter['user_defined_property_1'] . '%';
        }
        //自定义属性2
        if (isset($filter['user_defined_property_2']) && $filter['user_defined_property_2'] != '') {
            $sql_main .= " AND rl.user_defined_property_2 LIKE :user_defined_property_2 ";
            $sql_values[':user_defined_property_2'] = '%' . $filter['user_defined_property_2'] . '%';
        }
         //自定义属性3
        if (isset($filter['user_defined_property_3']) && $filter['user_defined_property_3'] != '') {
            $sql_main .= " AND rl.user_defined_property_3 LIKE :user_defined_property_3 ";
            $sql_values[':user_defined_property_3'] = '%' . $filter['user_defined_property_3'] . '%';
        }
         //自定义属性4
        if (isset($filter['user_defined_property_4']) && $filter['user_defined_property_4'] != '') {
            $sql_main .= " AND rl.user_defined_property_4 LIKE :user_defined_property_4 ";
            $sql_values[':user_defined_property_4'] = '%' . $filter['user_defined_property_4'] . '%';
        }
         //自定义属性5
        if (isset($filter['user_defined_property_5']) && $filter['user_defined_property_5'] != '') {
            $sql_main .= " AND rl.user_defined_property_5 LIKE :user_defined_property_5 ";
            $sql_values[':user_defined_property_5'] = '%' . $filter['user_defined_property_5'] . '%';
        }
        //自定义属性6
        if (isset($filter['user_defined_property_6']) && $filter['user_defined_property_6'] != '') {
            $sql_main .= " AND rl.user_defined_property_6 LIKE :user_defined_property_6 ";
            $sql_values[':user_defined_property_6'] = '%' . $filter['user_defined_property_6'] . '%';
        }
        //自定义属性7
        if (isset($filter['user_defined_property_7']) && $filter['user_defined_property_7'] != '') {
            $sql_main .= " AND rl.user_defined_property_7 LIKE :user_defined_property_7 ";
            $sql_values[':user_defined_property_7'] = '%' . $filter['user_defined_property_7'] . '%';
        }
        //自定义属性8
        if (isset($filter['user_defined_property_8']) && $filter['user_defined_property_8'] != '') {
            $sql_main .= " AND rl.user_defined_property_8 LIKE :user_defined_property_8 ";
            $sql_values[':user_defined_property_8'] = '%' . $filter['user_defined_property_8'] . '%';
        }
        $sql_main_tb = " FROM {$this->table} rl   ";
        if ($bg === true) {
            $sql_main_tb.="INNER JOIN goods_sku r2 on rl.sku = r2.sku
                           INNER JOIN base_goods bg on r2.goods_code = bg.goods_code";
        }
        $sql_main_tb.=" WHERE 1 ";
        $sql_main = $sql_main_tb . $sql_main;
        $data = array();
        $sql_ky = $sql_main.' AND rl.status = 0';
        //可用
        $row = $this->db->getRow('select count(1) as available_num'.$sql_ky,$sql_values);
        $data['available_num'] = empty($row['available_num']) ? 0 : $row['available_num'];
        //不可用
        $sql_not = $sql_main.' AND rl.status = 1';
        $row = $this->db->getRow('select count(1) as un_available_num'.$sql_not,$sql_values);
        $data['un_available_num'] = empty($row['un_available_num']) ? 0 : $row['un_available_num'];
        //全部
        $data['all_num'] = (int)$data['available_num']  + (int)$data['un_available_num'];
         return $this->format_ret(1, $data);
    }
}
