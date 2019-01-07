<?php

require_model('tb/TbModel');

class BaseInvModel extends TbModel {

    private $model_sync = '';

    function __construct() {
        parent::__construct();
        $this->init_sync();
    }

    private function init_sync() {
        $sync_status = load_model('sys/SysParamsModel')->get_val_by_code('inv_sync');
        if ($sync_status['inv_sync'] == 1) {
            $sql = "select count(1) from op_inv_sync where status=1";
            $t = $this->db->get_value($sql);
            if ($t != 0) {
                $this->model_sync = load_model('op/InvSyncHandleModel');
            } else {
                $this->model_sync = load_model('prm/InvStrategyModel');
            }
        } else {
            $this->model_sync = load_model('prm/InvStrategyModel');
        }
    }

    //刷新店铺库存
    function update_shop_sku($shop_code) {
        $page = 1;
        $check = TRUE;
        while (true) {
            $ret = $this->get_shop_sku($shop_code, $page);
            if ($ret['status'] <> 1 || empty($ret['data']['data'])) {
                break;
            }
            $page++;
            $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $ret['data']['data'], true);
            if ($ret_inv['status'] <> 1 || empty($ret_inv['data'])) {
                if ($page > (int) $ret['data']['filter']['page_count']) {
                    break;
                } else {
                    continue;
                }
            }
            load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], 0);
            if ($page > (int) $ret['data']['filter']['page_count']) {
                break;
            }
        }
    }

    //刷新分销店铺库存
    function update_fenxiao_shop_sku($shop_code) {
        $page = 1;
        $check = TRUE;
        while (true) {
            $ret = $this->get_fenxiao_shop_sku($shop_code, $page);
            if ($ret['status'] <> 1 || empty($ret['data']['data'])) {
                break;
            }
            $page++;
            $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $ret['data']['data'], true);
            if ($ret_inv['status'] <> 1 || empty($ret_inv['data'])) {
                if ($page > (int) $ret['data']['filter']['page_count']) {
                    break;
                } else {
                    continue;
                }
            }
            load_model("api/FxTaoBaoProductModel")->update_inv($shop_code, $ret_inv['data'], 0);
            if ($page > (int) $ret['data']['filter']['page_count']) {
                break;
            }
        }
    }

    //获取店铺库存
    //api接口使用
    function get_shop_inv($filter = array()) {
        if (empty($filter['shop_code'])) {
            return $this->format_ret(-1, array('shop_code'), '必填参数不能为空');
        }
        $shop_code = &$filter['shop_code'];
        $ret_store = $this->model_sync->get_sync_store($shop_code);
        if ($ret_store['status'] < 1) {
            return $ret_store;
        }
        $store_arr = &$ret_store['data'];

        $sql_values = array();
        if (isset($filter['barcode'])) {
            $barcode_arr = explode(",", $filter['barcode']);
            $barcode_str = $this->arr_to_in_sql_value($barcode_arr, 'barcode', $sql_values);
            $sql = "SELECT barcode,sku FROM goods_sku WHERE barcode IN({$barcode_str})";
            $sku_data = $this->db->get_all($sql, $sql_values);
            $barcode_exists = array_column($sku_data, 'barcode');
            $barcode_diff = array_diff($barcode_arr, $barcode_exists);
            if (!empty($barcode_diff)) {
                return $this->format_ret(-10002, ['barcode' => implode(',', $barcode_diff)], '商品条码不存在');
            }
        }

        $filter['page'] = isset($filter['page']) ? $filter['page'] : 1;
        $filter['page_size'] = isset($filter['page_size']) ? $filter['page_size'] : 20;
        if ($filter['page_size'] > 100) {
            $filter['page_size'] = 100;
        }
        $sql_main = " FROM goods_inv i INNER JOIN goods_sku b ON i.sku=b.sku  WHERE 1  ";
        
        $store_code_str = " i.store_code='" . implode("' OR i.store_code='", $store_arr) . "'";
        $sql_main.= " AND ({$store_code_str}) ";
        if (isset($filter['barcode'])) {
            $sql_main.= " AND b.barcode IN({$barcode_str}) ";
        }
        if (isset($filter['start_time']) && !empty($filter['start_time'])) {
            $sql_main.=" AND i.lastchanged >= :start_time";
            $sql_values[':start_time'] = $filter['start_time'];
        }
        if (isset($filter['end_time']) && !empty($filter['end_time'])) {
            $sql_main.=" AND i.lastchanged <= :end_time";
            $sql_values[':end_time'] = $filter['end_time'];
        }
        if (!isset($filter['start_time']) && !isset($filter['ende_time']) && empty($filter['start_time']) && empty($filter['end_time'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));

            $sql_main.=" AND i.lastchanged >= :start_time";
            $sql_values[':start_time'] = $start_time;
            $sql_main.=" AND i.lastchanged <= :end_time";
            $sql_values[':end_time'] = $end_time;
        }
        $select = " b.barcode,i.lastchanged ";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $barcode_arr = array();
        $lastchange_data = array();
        foreach ($data['data'] as $val) {
            $barcode = strtolower($val['barcode']);
            $barcode_arr[] = $barcode;
            if (!isset($lastchange_data[$barcode]) || (isset($lastchange_data[$barcode]) && $lastchange_data[$barcode] < $val['lastchanged'])) {
                $lastchange_data[$barcode] = $val['lastchanged'];
            }
        }
        if (empty($barcode_arr)) {
            return $this->format_ret(-10002, array(), '未找到指定更新时间内库存数据');
        }

        $ret_inv = $this->model_sync->get_shop_sku_inv($filter['shop_code'], $barcode_arr, true);
        if ($ret_inv['status'] < 1) {
            return $ret_inv;
        }

        $temp_data = $ret_inv['data'];
        foreach ($temp_data as &$row) {
            $row['lastchange_time'] = $lastchange_data[$row['barcode']];
        }
        $data['data'] = $temp_data;
        return $this->format_ret(1, $data);
    }

    function get_shop_sku($shop_code, $page = 1) {
        $filter['page'] = $page;
        $filter['page_size'] = 500;
        $filter['shop_code'] = $shop_code;
        $filter['goods_barcode_null'] = true;
        // $filter['status'] = 0;
        // $filter['is_inv_sync'] = 0;

        $ret = load_model("api/sys/ApiGoodsModel")->get_by_page_sku($filter);

        $barcode_arr = array();
        if ($ret['status'] == 1) {
            foreach ($ret['data']['data'] as $val) {
                //去掉单引号 条码
                if (!empty($val['goods_barcode']) && strpos($val['goods_barcode'], "'") === false) {
                    $barcode_arr[] = $val['goods_barcode'];
                }
            }
        }
        $ret['data']['data'] = $barcode_arr;
        return $ret;
    }

    function get_fenxiao_shop_sku($shop_code, $page = 1) {
        $filter['page'] = $page;
        $filter['page_size'] = 500;
        $filter['shop_code'] = $shop_code;
        $filter['goods_barcode_null'] = true;
        // $filter['status'] = 0;
        // $filter['is_inv_sync'] = 0;

        $ret = load_model("api/FxTaoBaoProductModel")->get_by_page_sku($filter);

        $barcode_arr = array();
        if ($ret['status'] == 1) {
            foreach ($ret['data']['data'] as $val) {
                if (!empty($val['outer_id'])) {
                    $barcode_arr[] = $val['outer_id'];
                }
            }
        }
        $ret['data']['data'] = $barcode_arr;
        return $ret;
    }

    function update_inv_shop() {
        $exec_key = 'update_inv';
        $now_time = time();
        $ret = load_model('sys/SysScheduleRecordModel')->get_record($exec_key);
        $check_cha = $now_time - $ret['data']['exec_time'];
        $recode = array();
        $recode['exec_time'] = $now_time;
        $recode['all_loop_time'] = 43200;
        $all_exec_time = $ret['data']['all_exec_time'] + $ret['data']['all_loop_time'];

        if ($check_cha > 3600 || $all_exec_time < time()) {//执行全量
            $recode['all_exec_time'] = $now_time;
            $this->update_all_shop();
            load_model('sys/SysScheduleRecordModel')->update_code($recode, $exec_key);
            return true;
        }
        $check_time = date('Y-m-d H:i:s', $ret['data']['exec_time'] - 1800); //半小时
        $now_time = date('Y-m-d H:i:s',time()-30);
        $this->update_goods_inv($check_time);
        $data = $this->db->get_all("select shop_code,inv_syn from base_shop where is_active=1");
        foreach ($data as $value) {
            $new_now_time =  date('Y-m-d H:i:s',time()-5);
            $this->update_inv_new_record($value['shop_code'],$now_time);
            //判断是否开启无库存记录同步
            if ($value['inv_syn'] == 1) {
                $this->update_inv_no_record($value['shop_code'],$new_now_time);
            }
        }

        $this->update_goods_combo_diy_inv($check_time);

        load_model('sys/SysScheduleRecordModel')->update_code($recode, $exec_key);
    }

    //普通商品增量库存计算
    function update_goods_inv($check_time) {
        $barcode_arr = $this->get_all_barcode($check_time);
        if ($barcode_arr === FALSE) {
            return FALSE;
        }

        if (!empty($barcode_arr)) {
            $data = $this->db->get_all("select shop_code from base_shop where is_active=1");
            foreach ($data as $val) {
                $this->update_inv_increment($val['shop_code'], $barcode_arr, 1);
            }
        }

        //维护分销商品库存更新数据
        $this->update_goods_inv_fenxiao($check_time);
    }

    private function get_all_barcode($check_time, $is_fenxiao = 0) {
        $barcode_type = array(
            array('goods_sku', 'barcode'),
            array('goods_sku', 'gb_code'),
            array('goods_barcode_child', 'barcode'),
        );
        $barcode_arr = array();
        foreach ($barcode_type as $key => $val) {
            $group_by = $key == 2 ? 'barcode' : 'sku';
            $sql_common = "( SELECT b.{$val[1]} AS barcode,MAX(r.record_time) as record_time FROM goods_inv_record r
                            INNER JOIN {$val[0]} AS b ON r.sku=b.sku
                            WHERE r.record_time>:record_time
                            GROUP BY b.{$group_by} ) AS r";
            if ($is_fenxiao == 1) {
                $sql = "SELECT DISTINCT s.outer_id AS goods_barcode FROM api_taobao_fx_product_sku AS s,{$sql_common}
                        WHERE r.barcode=s.outer_id AND r.record_time>s.sys_update_time AND r.barcode IS NOT NULL AND r.barcode<>''";
            } else {
                $sql = "SELECT DISTINCT s.goods_barcode FROM api_goods_sku AS s,{$sql_common}
                        WHERE r.barcode=s.goods_barcode AND r.record_time>s.sys_update_time AND r.barcode IS NOT NULL AND r.barcode<>''";
            }

            $data = $this->db->get_all($sql, array(':record_time' => $check_time));
            if (empty($data) && $key == 0) {
                return false;
            }
            $barcode_arr = array_merge($barcode_arr, array_column($data, 'goods_barcode'));
        }

        return $barcode_arr;
    }

    //维护分销商品库存更新数据
    function update_goods_inv_fenxiao($check_time) {
        $shop_data = $this->db->get_all("select shop_code from base_shop where is_active=1 and ((sale_channel_code='taobao' and fenxiao_status=1) or sale_channel_code='fenxiao') ");
        if (empty($shop_data)) {
            return;
        }
        $barcode_arr = $this->get_all_barcode($check_time, 1);
        if ($barcode_arr === FALSE) {
            return FALSE;
        }

        if (!empty($barcode_arr)) {
            foreach ($shop_data as $val) {
                $this->update_inv_increment_fenxiao($val['shop_code'], $barcode_arr, 1);
            }
        }
    }

    //套装库存同步计算
    function update_goods_combo_diy_inv($check_time) {

        $sql = "select DISTINCT s.goods_barcode  from api_goods_sku s,
        (select cb.barcode,MAX(r.record_time) as record_time
        from goods_inv_record r
        INNER JOIN goods_sku b ON r.sku=b.sku
        INNER JOIN  goods_combo_diy c ON c.sku =b.sku
        INNER JOIN  goods_combo_barcode cb  ON cb.sku =c.p_sku
         where r.record_time>'{$check_time}'
        GROUP BY cb.barcode) as r
                 where r.barcode=s.goods_barcode and r.record_time>s.sys_update_time  ";
        $data = $this->db->get_all($sql);
        if (empty($data)) {
            return false;
        }

        $barcode_arr = array();
        foreach ($data as $val) {
            $barcode_arr[] = $val['goods_barcode'];
        }
        $sql_values = array();
        $barcodes = $this->arr_to_in_sql_value($barcode_arr, 'goods_barcode', $sql_values); //"'" . implode("','", $barcode_arr) . "'";
        $data = $this->db->get_all("select shop_code from base_shop where is_active=1");
        foreach ($data as $val) {
            //获取接口存在的商品
            $shop_code = $val['shop_code'];
            $sql = "select DISTINCT goods_barcode from api_goods_sku where shop_code='{$shop_code}' and goods_barcode in({$barcodes})";
            $data = $this->db->get_all($sql, $sql_values);

            if (empty($data)) {
                continue;
            }

            $new_barcode_arr = array();
            foreach ($data as $row) {
                $new_barcode_arr[] = $row['goods_barcode'];
            }

            //获取库存
            $ret_inv = $this->model_sync->get_combo_diy_inv($shop_code, $new_barcode_arr);
            if ($ret_inv['status'] <> 1 || empty($ret_inv['data'])) {
                continue;
            }

            //更新库存
            load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], 0);
        }
    }

    function update_inv_increment($shop_code, $barcode_arr, $is_check = 0, $is_hand = 0) {
        if ($is_check == 1) {
            $sql_values = array();
            $barcodes = $this->arr_to_in_sql_value($barcode_arr, 'goods_barcode', $sql_values);
            $sql = "select DISTINCT goods_barcode from api_goods_sku where shop_code='{$shop_code}' and goods_barcode in({$barcodes})";
            $data = $this->db->get_all($sql, $sql_values);
            if (empty($data)) {
                return $this->format_ret(-1,'','没有需要同步的商品，商品可能为删除状态或者不允许同步库存！');
            }


            $barcode_arr = array();
            foreach ($data as $row) {
                $barcode_arr[] = $row['goods_barcode'];
            }
        }
        $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr);
        $is_increment = ($is_hand == 0) ? 1 : 0; //手工强制，非手工为增量
        if($ret_inv['status']>0){
            load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], $is_increment);
        }
        return $ret_inv;
    }

    function update_inv_new_record($shop_code,$now_date) {
        $sql = "select DISTINCT goods_barcode from api_goods_sku where shop_code='{$shop_code}' AND is_allow_sync_inv =1 AND inv_num=-1 AND status=1 AND lastchanged>'{$now_date}' ";
        $data1 = $this->db->get_all($sql);
        if (!empty($data1)) {
            $barcode_arr = array();
            foreach ($data1 as $row) {
                $barcode_arr[] = $row['goods_barcode'];
            }
            $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr, true);
            if (!empty($ret_inv['data'])) {
                load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], 0);
            }
        }
    }

    /**
     * 店铺无库存记录的商品以0库存同步
     */
    function update_inv_no_record($shop_code,$now_time) {
        $sql = "select api_goods_sku_id,goods_barcode from api_goods_sku where inv_num=-1 AND is_allow_sync_inv =1 AND status=1 AND goods_barcode!='' AND api_goods_sku.shop_code =:shop_code AND lastchanged<='{$now_time}'";
        $sql_values = array(
            ':shop_code' => $shop_code,
        );
         //必须是设置同步策略的
        $data = $this->db->get_all($sql, $sql_values);
        if (!empty($data)) {
            $id_arr = array();
            foreach ($data as $val) {          
               // $barcode_arr[] = $val['goods_barcode'];
                $id_arr[] = $val['api_goods_sku_id'];
            }
            //todo :增加系统无条码的不更新成0
            
//            if (!empty($barcode_arr)) {
//                $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr, true);
//                if (!empty($ret_inv['data'])) {
//                    load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], 0);
//                }
//               $is_set = $ret_inv['status']>0?true:false;
//                
//            }
           
            if (!empty($id_arr)) {
                $id_str = implode(",", $id_arr);
                $sql = "update api_goods_sku set inv_num =0,inv_update_time=now()
                where  api_goods_sku_id in({$id_str}) AND   inv_num=-1 ";
                $this->db->query($sql);
            }
        }
        //缺少 切换仓库原仓库有库存记录，新仓库无库存记录问题
    }

    function update_inv_increment_fenxiao($shop_code, $barcode_arr, $is_check = 0) {
        if ($is_check == 1) {
            $sql_values = array();
            $barcodes = $this->arr_to_in_sql_value($barcode_arr, 'outer_id', $sql_values);
            $sql = "select DISTINCT outer_id from api_taobao_fx_product_sku where shop_code='{$shop_code}' and outer_id in({$barcodes})";
            $data = $this->db->get_all($sql, $sql_values);
            if (empty($data)) {
                return $this->format_ret(-1,'','数据为空');
            }

            $barcode_arr = array();
            foreach ($data as $row) {
                $barcode_arr[] = $row['outer_id'];
            }
        }
        $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr);
        load_model("api/FxTaoBaoProductModel")->update_inv($shop_code, $ret_inv['data'], 1);
    }

    function update_all_shop() {
        $data = $this->db->get_all("select shop_code,sale_channel_code,fenxiao_status from base_shop where is_active=1 and authorize_state=1");
        foreach ($data as $val) {
            $this->is_increment = 0;
            $this->update_shop_sku($val['shop_code']);
            //是否开启分销
            if (($val['sale_channel_code'] == 'taobao' && $val['fenxiao_status'] == 1) || $val['sale_channel_code'] == 'fenxiao') {
                $this->update_fenxiao_shop_sku($val['shop_code']);
            }
        }
    }

    function update_barcode_inv($shop_code, $barcode_arr) {
        $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr);
        if ($ret_inv['status'] <> 1 || empty($ret_inv['data'])) {
            return false;
        }
        return load_model("api/sys/ApiGoodsModel")->update_inv($shop_code, $ret_inv['data'], 1);
    }

    /**
     * 唯品会专场商品库存更新
     * @param type $shop_code
     * @param type $barcode_arr
     * @return type
     */
    function wph_sales_inv_update($shop_code, $barcode_arr) {
        $ret_inv = $this->model_sync->get_shop_sku_inv($shop_code, $barcode_arr, true);
        if (empty($ret_inv['data'])) {
            return $this->format_ret(-1, '', '条码库存不存在');
        }
        
        return $ret_inv;
    }

}
