<?php

require_model("wms/WmsBaseModel");
ini_set('memory_limit', '1000M');
class WmsInvModel extends WmsBaseModel {
    public $is_compare = false;
    function __construct() {
        parent::__construct();
    }

    private $down_start_time = 0;

    function down_wms_stock($api_product='') {
        set_time_limit(0);
        $sql = "select s.shop_store_code,w.wms_system_code from sys_api_shop_store s
                    INNER JOIN wms_config w ON w.wms_config_id = s.p_id
                        where  s.shop_store_type = 1 AND s.store_type = 1";
        if($api_product!=''){
            $sql.=" AND w.wms_system_code='{$api_product}'";
        }
        
       
        $data = ctx()->db->get_all($sql);
        foreach ($data as $row) {
            if ($row['wms_system_code'] == 'sfwms') {
                $status = $this->check_is_sf_run($row['wms_system_code']);
                if ($status === FALSE) {
                    continue;
                }
                $this->down_wms_stock_by_store_code_all($row['shop_store_code']);
            }else if ($row['wms_system_code'] == 'iwms' && CTX()->saas->get_saas_key() == '2380') {
                $this->sync_incr($row['shop_store_code']);
            } else {
                $this->down_wms_stock_by_store_code($row['shop_store_code'], $row['wms_system_code']);
            }
        }

        //下载完成，直接更新库存
        $this->update_efast_stock_from_wms();
    }

    function down_wms_stock_compare() {

        set_time_limit(0);
        $this->down_start_time = time();
        $sql = "select s.shop_store_code,w.wms_system_code,s.outside_code from sys_api_shop_store s
                    INNER JOIN wms_config w ON w.wms_config_id = s.p_id
                        where  s.shop_store_type = 1 AND s.store_type = 1 AND s.p_type=1";
        $data = ctx()->db->get_all($sql);
        foreach ($data as $row) {
            if ($row['wms_system_code'] == 'qimen') {
                $this->is_compare = true;
                $this->down_wms_stock_by_store_code_day($row['shop_store_code']);
               //  $this->down_wms_stock_by_store_code($row['shop_store_code']);

                $this->create_compare_data($row['shop_store_code'], $row['wms_system_code'], $row['outside_code']);
            }
        }
    }

    function create_compare_data($store_code, $wms_system_code, $wms_code) {

        $down_date = date('Y-m-d', $this->down_start_time);
        $down_time = date('Y-m-d H:i:s', $this->down_start_time);
        $compare_code = $store_code . '_' . date('Ymd');

        $sql = "select * from wms_inv_compare where compare_code=:compare_code AND  store_code=:store_code";
        $row = $this->db->get_row($sql, array(':compare_code' => $compare_code,':store_code'=>$store_code));
        if (!empty($row)) {
            return $this->format_ret(2, '', '已经生成！');
        }

        $compare_data = array(
            'compare_code' => $compare_code,
            'store_code' => $store_code,
            'wms_type' => $wms_system_code,
            'wms_store_code' => $wms_code,
            'compare_time' => $down_date,
        );
        $day_time = date('Y-m-d', strtotime("-1 day"))." 00:00:00";
        $sql_insert = "insert ignore into wms_inv_compare_detail (compare_code,wms_type,wms_store_code,store_code,barcode,sku,wms_num,sys_num,compare_time) ";

        $sql_insert .= "select '{$compare_code}' as compare_code,'{$wms_system_code}' as wms_type,'{$wms_code}' as wms_store_code,i.store_code,b.barcode,i.sku,w.num as wms_num,i.stock_num as sys_num,'{$down_time}' as compare_time
                    from goods_inv i
                    INNER JOIN goods_barcode b ON b.sku=i.sku
                    LEFT JOIN wms_goods_inv w  ON w.barcode=b.barcode AND w.efast_store_code=i.store_code AND  w.is_success = 0 and  w.down_time>='{$down_time}'  ";
        $sql_insert.=" WHERE i.store_code = '{$store_code}' AND (w.num<>i.stock_num or w.num is null) AND  i.lastchanged>='{$day_time}'  ";
        $this->db->query($sql_insert);
        $up_sql = "update wms_inv_compare_detail set remark='接口未返回库存',wms_num=0  where compare_code='{$compare_code}'  AND wms_num is null";
        $this->db->query($up_sql);

        $sql_num = "select count(1) as compare_sku_num,sum(abs(wms_num-sys_num)) as compare_num from wms_inv_compare_detail where compare_code = :compare_code";
        $data_num = $this->db->get_row($sql_num, array(':compare_code' => $compare_code));
        if (!empty($data_num)) {
            $compare_data = array_merge($compare_data, $data_num);
            $compare_data['compare_num'] = is_null($compare_data['compare_num'])?0:$compare_data['compare_num'];
        }
        $this->db->insert('wms_inv_compare', $compare_data);

        return $this->format_ret(1);
    }

    function down_compare_data($compare_code,$store_code) {
        $sql = "select * from wms_inv_compare_detail where compare_code = :compare_code AND  store_code=:store_code";
        $data = $this->db->get_all($sql, array(':compare_code' => $compare_code,':store_code'=>$store_code));
        $hear_data = array('compare_time' => '对照时间', 'barcode' => '商品条形码', 'store_name' => '系统仓库', 'sys_num' => '系统库存', 'wms_name' => 'wms仓库', 'wms_num' => 'wms库存', 'remark' => '备注');
        $content_csv = array();
        $hear_str = implode(',', $hear_data);
        $content_csv[] = iconv('utf-8', 'gbk', $hear_str);

        if (!empty($data)) {


            $defaut_row['store_name'] = $this->db->get_value("SELECT store_name from base_store  WHERE store_code=:store_code ", array(':store_code' => $store_code));
            $sql_wms = 'select c.wms_config_name from wms_config c
                        INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
                         where  s.p_type=1 AND s.outside_type=1
                        AND  s.shop_store_code=:shop_store_code';
            $sql_value = array( ':shop_store_code' => $store_code);
            $defaut_row['wms_name'] = $this->db->get_value($sql_wms, $sql_value);
            $key_arr = array_keys($hear_data);
            foreach ($data as $val) {
                $row = array();
                foreach ($key_arr as $k) {
                    $row[$k] = isset($val[$k]) ? $val[$k] : $defaut_row[$k];
                    $row[$k] = iconv('utf-8', 'gbk', $row[$k]);
                    if($k =='barcode'){
                         $row[$k] = $row[$k]."\t";
                    }
                }
                if (!empty($row['remark'])) {
                    $row['wms_num'] = '';
                }
                $content_csv[] = implode(',', $row);
            }
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition:inline;filename="' . iconv('utf-8', 'gbk', $compare_code) . '.csv"');
            header("Content-Transfer-Encoding: binary");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            echo join("\r\n", $content_csv) . "\r\n";
            die;
        }
    }

    function get_compare_list($filter) {

        $sql_main = "FROM wms_inv_compare WHERE 1";
        $sql_values = array();

        $select = '*';
        $sql_main.=" ORDER BY compare_time DESC";
        //$data =  $this->get_page_from_sql($filter, $sql_main, $select);
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $store_arr = array();
        $wms_arr = array();
        foreach ($data['data'] as &$val) {
            if (!isset($store_arr[$val['store_code']])) {
                $store_name = $this->db->get_value("SELECT store_name from base_store  WHERE store_code=:store_code ", array(':store_code' => $val['store_code']));
                $store_arr[$val['store_code']] = $store_name;
            }
            $val['store_name'] = $store_arr[$val['store_code']];
            if (!isset($wms_arr[$val['wms_type']])) {
                $sql_wms = 'select c.wms_config_name from wms_config c
                        INNER JOIN sys_api_shop_store s ON c.wms_config_id=s.p_id
                        where  s.p_type=1 AND s.outside_type=1
                        AND c.wms_system_code=:wms_system_code AND  s.shop_store_code=:shop_store_code';
                $sql_value = array(':wms_system_code' => $val['wms_type'], ':shop_store_code' => $val['store_code']);
                $wms_name = $this->db->get_value($sql_wms, $sql_value);
                $wms_arr[$val['wms_type']] = $wms_name;
            }
            $val['wms_name'] = $wms_arr[$val['wms_type']];
            //     $val['store_name']
        }


        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    private function check_is_sf_run($wms_system_code) {
        $status = TRUE;
        if ($wms_system_code == 'sfwms') {
            $conf = require_conf('wms/sfwms_inv');
            $date = date('Y-m-d');
            $now_time = time();
            $start_time = strtotime($date . " 04:00:00");
            $end_time = strtotime($date . " 05:00:00");
            $kh_id = CTX()->saas->get_saas_key();

            if (in_array($kh_id, $conf['inv_cli_time'])) {
                $status = FALSE;
                if ($start_time <= $now_time && $end_time >= $now_time) {
                    $status = TRUE;
                } else {
                    echo "no run time! \n";
                }
            }
        }
        return $status;
    }

    function down_wms_stock_by_store_code_all($efast_store_code, $batch_num = 100) {
        $page = 1;
        while (1) {
            $param['pagesize'] = $batch_num;
            $param['page'] = $page;
            $ret = $this->down_wms_stock_by_page($efast_store_code, $param);

            if ($ret['status'] < 0) {
                echo '处理失败' . "\n";
                print_r($ret['message']);
                break;
            } else {
                echo '处理成功' . "\n";
                print_r($ret);
            }
            if(empty($ret['data'])){
                    break;
            }
            
            if (isset($ret['data']['pagecount'])&&$ret['data']['pagecount'] <= $page) {
                break;
            }
            
            $page++;
        }
    }

    //全量下载wms库存到中间表
    function down_wms_stock_by_store_code($efast_store_code, $wms_system_code = '', $batch_num = 50) {
        $min_id = 0;
        while (1) {
            $sql = "select barcode_id,barcode from goods_barcode where barcode_id>$min_id  AND barcode is not NULL AND barcode<>''"
                    . " order by barcode_id asc limit {$batch_num}";
            //奇门WMS，若开启按照配置商品下发，则支持按配库存获取        
            if($wms_system_code == 'qimen'){
                $wms_cfg = $this->get_wms_cfg($efast_store_code);
                if($wms_cfg['wms_split_goods_source'] == 1) {
                    $sql = "SELECT
                                g.barcode_id,
                                g.barcode
                            FROM
                                goods_barcode AS g
                            INNER JOIN wms_custom_goods_sku wms ON g.sku = wms.sku
                            WHERE
                                g.barcode_id > {$min_id}
                            AND 
                                wms.wms_config_id={$wms_cfg['wms_config_id']}
                            ORDER BY
                                g.barcode_id ASC
                            LIMIT {$batch_num}";
                }
            }
            //iwms按sku下发商品，取sku去获取库存
            if($wms_system_code == 'iwms'){
                $wms_cfg = $this->get_wms_cfg($efast_store_code);
                if($wms_cfg['goods_upload_type'] == 1) {
                    $sql = "SELECT sku_id AS barcode_id,sku AS barcode FROM goods_sku WHERE sku_id>{$min_id}  AND barcode is not NULL AND barcode<>'' ORDER BY sku_id ASC LIMIT {$batch_num}";
                }
            }
            $db_barcode = $this->db->getAll($sql);

            if (count($db_barcode) == 0) {
                echo "任务完成";
                break;
            }

            $barcode_arr = array();
            foreach ($db_barcode as $sub_barcode) {
                $barcode_arr[] = $sub_barcode['barcode'];
                $cur_min_id = $sub_barcode['barcode_id'];
            }
            if ($cur_min_id <= $min_id) {
                $msg = isset($wms_cfg) && $wms_cfg['goods_upload_type'] == 1 ? "goods_sku min_id 验证出错" : "goods_barcode min_id 验证出错";
                echo $msg;
                break;
            } else {
                $min_id = $cur_min_id;
            }

            echo "全量同步WMS库存到EFAST: 处理中 [{$efast_store_code}] barcode_id={$min_id} \n";
            $ret = $this->down_wms_stock_by_barcode($efast_store_code, $barcode_arr);
            if ($ret['status'] < 0) {
                echo '处理失败' . "\n";
                print_r($ret['message']);
            } else {
                echo '处理成功' . "\n";
                print_r($ret);
            }
        }
    }
    //全量下载wms库存到中间表
    function down_wms_stock_by_store_code_day($efast_store_code, $batch_num = 50) {
        $min_id = 0;
        $day_time = date('Y-m-d', strtotime("-1 day"))." 00:00:00";
        while (1) {
//            $sql = "select barcode_id,barcode from goods_barcode where barcode_id>$min_id"
//                    . " order by barcode_id asc limit {$batch_num}";
            $sql = "select b.barcode_id,b.barcode from goods_barcode b
            INNER JOIN goods_inv i on b.sku=i.sku and i.store_code='{$efast_store_code}'
             where b.barcode_id>{$min_id} AND  i.lastchanged>='{$day_time}'
                     order by b.barcode_id asc limit {$batch_num}
            ";
            $db_barcode = $this->db->getAll($sql);
            if (count($db_barcode) == 0) {
                echo "任务完成";
                break;
            }

            $barcode_arr = array();
            foreach ($db_barcode as $sub_barcode) {
                $barcode_arr[] = $sub_barcode['barcode'];
                $cur_min_id = $sub_barcode['barcode_id'];
            }

            if ($cur_min_id <= $min_id) {
                echo "goods_barcode min_id 验证出错";
                break;
            } else {
                $min_id = $cur_min_id;
            }

            echo "全量同步WMS库存到EFAST: 处理中 [{$efast_store_code}] barcode_id={$min_id} \n";
            $ret = $this->down_wms_stock_by_barcode($efast_store_code, $barcode_arr);
            if ($ret['status'] < 0) {
                echo '处理失败' . "\n";
                print_r($ret['message']);
            } else {
                echo '处理成功' . "\n";
                print_r($ret);
            }
        }
    }

    //下载指定barcode的库存
    function down_wms_stock_by_barcode($efast_store_code, $barcode_arr) {
        $m = $this->get_wms_mod($efast_store_code);

        if ($m !== FALSE) {
            $m->is_compare = $this->is_compare;
            $ret = $m->inv_search($efast_store_code, $barcode_arr);


            if ($ret['status'] > 0) {
                $this->insert_wms_goods_inv($efast_store_code, $ret['data']);
            }
        } else {
            $ret = $this->format_ret(-1, '', '未找到指定wms库存类');
        }
        return $ret;
    }

    //下载指定barcode的库存
    function down_wms_stock_by_page($efast_store_code, $param) {
        $m = $this->get_wms_mod($efast_store_code);
        if ($m !== FALSE) {
            $m->wms_cfg = array();
            $ret = $m->inv_search($efast_store_code, $param);
            if ($ret['status'] > 0&&isset($ret['data']['data'])&&!empty($ret['data']['data'])) {
                $this->insert_wms_goods_inv($efast_store_code, $ret['data']['data']);
            }else{
                $ret = $this->format_ret(-1, '', '数据返回为空');
            }
        } else {
            $ret = $this->format_ret(-1, '', '未找到指定wms库存类');
        }
        return $ret;
    }

    function get_wms_mod($efast_store_code) {

        $this->wms_cfg = array();

        $this->get_wms_cfg($efast_store_code);

        //  $this->wms_cfg['api_product']
        $wms_system_code = $this->wms_cfg['api_product'];

        $class = ucfirst($wms_system_code) . 'InvModel';
        $s = require_model('wms/' . strtolower($wms_system_code) . '/' . $class);
        if ($s) {
            return new $class();
        }
        return $s;
    }

    //更新指定的barcode的库存到EFAST
    function update_efast_stock_by_barcode($efast_store_code, $barcode_arr) {
        $sql1 = "select r1.* from wms_config r1 left join sys_api_shop_store r2 on r1.wms_config_id=r2.p_id "
               . "where r2.p_type=1 and r2.shop_store_type=1 and r2.outside_type=1 and r2.shop_store_code='{$efast_store_code}';";
        $v=$this->db->get_row($sql1);
        $config_all = require_conf("sys/wms");
        $ret= json_decode($v['wms_params'],TRUE);
         if(isset($ret["effect_inv_type"]) && $ret["effect_inv_type"] == 1){
                return $this->format_ret(-1, '', '外包仓库存，进销存模式，可以获取库存但是不能更新本地库存!');
            }elseif(!isset($ret["effect_inv_type"])){
                if(isset($config_all[$v['wms_system_code']]['effect_inv_type']) && $config_all[$v['wms_system_code']]['effect_inv_type'] == 1){
                 return $this->format_ret(-1,'','外包仓库存，进销存模式，可以获取库存但是不能更新本地库存!');
                }
            }
        $barcode_list = "'" . join("','", $barcode_arr) . "'";
        $sql = "select efast_store_code as store_code,barcode,num from wms_goods_inv where efast_store_code = '{$efast_store_code}' and barcode in({$barcode_list})";
        $inv_info = ctx()->db->get_all($sql);
        if (!empty($inv_info)) {
            $ret = $this->update_inv_by_wms($inv_info);
            return $ret;
        }
        return $this->format_ret(1);
    }

    //用wms库存表更新efast库存
    function update_efast_stock_from_wms() {
        $sql_config = "
                select wms_system_code,wms_params,shop_store_code from wms_config w
                INNER JOIN sys_api_shop_store s
                ON w.wms_config_id =s.p_id
                AND s.shop_store_type=1  and s.outside_type=1 
                ";
        $wms_data = $this->db->get_all($sql_config);

        $config_all = require_conf("sys/wms");
        $no_sync_store = array();
        //todo;后续改成系统参数 判断是否是库存覆盖模式
        foreach ($wms_data as $val) {
            if (!empty($val['shop_store_code'])) {
                $wms_params = json_decode($val['wms_params'], TRUE);
                if (isset($wms_params['effect_inv_type'])) {
                    if ($wms_params['effect_inv_type'] == 1) {
                        $no_sync_store[] = $val['shop_store_code'];
                    }
                } else {
                    if (isset($config_all[$val['wms_system_code']]['effect_inv_type']) && $config_all[$val['wms_system_code']]['effect_inv_type'] == 1) {
                        $no_sync_store[] = $val['shop_store_code'];
                    }
                }
            }
        }

        $ret = array();
        $is_continue = true;
        while($is_continue){
            $sql = "select efast_store_code as store_code,barcode,num from wms_goods_inv where is_sync = 1 and is_success<= 0   ";
            if (!empty($no_sync_store)) {
                $sql .= " AND efast_store_code not in ('" . implode("','", $no_sync_store) . "') ";
            }
            $sql.="  ORDER BY down_time limit 5000";
      
            
            $inv_info = ctx()->db->get_all($sql);
            if (!empty($inv_info)) {
                $ret = $this->update_inv_by_wms($inv_info);
                print_r($ret);
                $is_continue = count($inv_info)<5000?false:true;
            }else{
                $is_continue = false;
            }
        }
       return $ret;
        
    }

    function update_inv_by_wms($inv_info) {
        $ret = $this->update_inv($inv_info);
        if ($ret['status'] < 0) {
            return $ret;
        }
        $err = array();
        if (isset($ret['data']['success'])) {
            $sync_time = date('Y-m-d H:i:s');
            foreach ($ret['data']['success'] as $sub_ret) {
                $sql = "update wms_goods_inv set is_success = 1,sync_time = '{$sync_time}' where efast_store_code = '{$sub_ret['store_code']}' and barcode = '{$sub_ret['barcode']}'";
                ctx()->db->query($sql);
            }
        }
        if (isset($ret['fail']['no_find_sku'])) {
            foreach ($ret['fail']['no_find_sku'] as $sub_ret) {
                $sql = "update wms_goods_inv set is_success = -1 where efast_store_code = '{$sub_ret['store_code']}' and barcode = '{$sub_ret['barcode']}'";
                ctx()->db->query($sql);
            }
            $err[] = 'efast找不到条码 ' . join(',', $ret['fail']['no_find_sku']);
        }
        if (isset($ret['fail']['update_fail'])) {
            foreach ($ret['fail']['update_fail'] as $sub_ret) {
                $sql = "update wms_goods_inv set is_success = -2 where efast_store_code = '{$sub_ret['store_code']}' and barcode = '{$sub_ret['barcode']}'";
                ctx()->db->query($sql);
            }
            $err[] = '条码更新失败 ' . join(',', $ret['fail']['no_find_sku']);
        }
        if (empty($err)) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', join(' ', $err));
        }
    }

    /**
     * $inv_info = array('store_code'=>xx,barcode'=>xx,'num'=>xx);
     */
    function update_inv($inv_info) {
        if (empty($inv_info)) {
            return $this->format_ret(-1, '', '库存信息不能为空');
        }
        //$ret = load_model('sys/SysParamsModel')->get_val_by_code(array('default_lof_no','default_lof_production_date'));

        $ret_lof = load_model('prm/GoodsLofModel')->get_sys_lof();

        $default_lof_no = $ret_lof['data']['lof_no'];
        $default_lof_production_date = $ret_lof['data']['production_date'];

        $inv_map = load_model('util/ViewUtilModel')->get_map_arr($inv_info, "store_code,barcode", 0, "num");
        $barcode_list = load_model('util/ViewUtilModel')->get_arr_val_by_key($inv_info, "barcode", 'string', 'string');
        $sql = "select goods_code,spec1_code,spec2_code,sku,barcode from goods_sku where barcode in({$barcode_list})";
        $db_barcode = ctx()->db->get_all($sql);
        $barcode_arr = load_model('util/ViewUtilModel')->get_map_arr($db_barcode, "barcode",0,'',1);

        $update_result = array();
        $inv_sku_info = array();
        $store_code_arr = array();
        $sku_arr = array();

        foreach ($inv_info as $row) {
            $row['barcode'] = strtolower($row['barcode']);
            if (isset($barcode_arr[$row['barcode']]) && !empty($row['store_code'])) {
                $row['sku'] = $barcode_arr[$row['barcode']]['sku'];
                $row['goods_code'] = $barcode_arr[$row['barcode']]['goods_code'];
                $row['spec1_code'] = $barcode_arr[$row['barcode']]['spec1_code'];
                $row['spec2_code'] = $barcode_arr[$row['barcode']]['spec2_code'];
                $ks = "{$row['store_code']},{$row['sku']}";
                $inv_sku_info[$ks] = $row;
                $store_code_arr[] = $row['store_code'];
                $sku_arr[] = $row['sku'];
            } else {
                $update_result['fail']['no_find_sku'][] = array('store_code' => $row['store_code'], 'barcode' => $row['barcode']);
            }
        }
        $store_code_list = "'" . join("','", array_unique($store_code_arr)) . "'";
        $sku_list = "'" . join("','", array_unique($sku_arr)) . "'";
        $sql = "select goods_inv_id,store_code,sku,production_date,lof_no,stock_num,lock_num from goods_inv_lof where store_code in({$store_code_list}) and sku in({$sku_list})";
        $db_lof = ctx()->db->get_all($sql);
        $set_zero_id_arr = array();
        $lof_arr = array();
        foreach ($db_lof as $sub_lof) {
            if ($sub_lof['lof_no'] != $default_lof_no || $sub_lof['production_date'] != $default_lof_production_date) {
                if ($sub_lof['stock_num'] != 0) {
                    $set_zero_id_arr[] = $sub_lof['goods_inv_id'];
                }
            }
            $ks = "{$sub_lof['store_code']},{$sub_lof['sku']}";
            if (isset($lof_arr[$ks])) {
                $lof_arr[$ks]['stock_num'] += $sub_lof['stock_num'];
                $lof_arr[$ks]['lock_num'] += $sub_lof['lock_num'];
            } else {
                $lof_arr[$ks] = $sub_lof;
            }
        }

        $up_lof_arr = array();
        $up_inv_arr = array();
        $log_arr = array();
        $inv_record_log = array();
        $store_code_and_sku = array();
        $date = date('Y-m-d H:i:s');
        foreach ($inv_sku_info as $ks => $sub_sku) {
            $find_lof_num = isset($lof_arr[$ks]) ? $lof_arr[$ks] : array('stock_num' => 0, 'lock_num' => 0,'no_find'=>1);
            $_row = array('store_code' => $sub_sku['store_code'],
                'goods_code' => $sub_sku['goods_code'],
                'spec1_code' => $sub_sku['spec1_code'],
                'spec2_code' => $sub_sku['spec2_code'],
                'sku' => $sub_sku['sku'],
                'production_date' => $default_lof_production_date,
                'lof_no' => $default_lof_no,
                'stock_num' => $sub_sku['num']
            );
            $store_code_and_sku[$sub_sku['store_code'] . ',' . $sub_sku['sku']] = $_row;

            if (isset($find_lof_num['no_find'])||$find_lof_num['stock_num'] != $sub_sku['num']) {


                $store_code_arr[] = $sub_sku['store_code'];
                $sku_arr[] = $sub_sku['sku'];
//				if ($count > 0) {
//					$sql_update = "";
//				} else {
//					$sql_insert = "";
//				}

                $up_lof_arr[] = $_row;
                $up_inv_arr[] = array('store_code' => $sub_sku['store_code'],
                    'goods_code' => $sub_sku['goods_code'],
                    'spec1_code' => $sub_sku['spec1_code'],
                    'spec2_code' => $sub_sku['spec2_code'],
                    'sku' => $sub_sku['sku'],
                    'stock_num' => $sub_sku['num'],
                    'record_time' => date('Y-m-d H:i:s'),
                );
                $log_arr[] = array('efast_store_code' => $sub_sku['store_code'],
                    'goods_code' => $sub_sku['goods_code'],
                    'spec1_code' => $sub_sku['spec1_code'],
                    'spec2_code' => $sub_sku['spec2_code'],
                    'sku' => $sub_sku['sku'],
                    'barcode' => $sub_sku['barcode'],
                    'prev_num' => $find_lof_num['stock_num'],
                    'after_num' => $sub_sku['num'],
                );
                $stock_change_num = $sub_sku['num'] - $find_lof_num['stock_num'];
                $remark = $stock_change_num > 0 ? '实物增加' : '实物扣减';
                $stock_change_num = abs($stock_change_num);
                $inv_record_log[] = array('store_code' => $sub_sku['store_code'],
                    'goods_code' => $sub_sku['goods_code'],
                    'spec1_code' => $sub_sku['spec1_code'],
                    'spec2_code' => $sub_sku['spec2_code'],
                    'sku' => $sub_sku['sku'],
                    'barcode' => $sub_sku['barcode'],
                    'production_date' => $default_lof_production_date,
                    'lof_no' => $default_lof_no,
                    'occupy_type' => 3,
                    'stock_change_num' => $stock_change_num,
                    'stock_lof_num_before_change' => $find_lof_num['stock_num'],
                    'stock_lof_num_after_change' => $sub_sku['num'],
                    'stock_num_before_change' => $find_lof_num['stock_num'],
                    'stock_num_after_change' => $sub_sku['num'],
                    'lock_num_before_change' => $find_lof_num['lock_num'],
                    'lock_num_after_change' => $find_lof_num['lock_num'],
                    'lock_lof_num_before_change' => $find_lof_num['lock_num'],
                    'lock_lof_num_after_change' => $find_lof_num['lock_num'],
                    'record_time' => $date,
                    'relation_code' => 'wms',
                    'relation_type' => 'adjust',
                    'remark' => $remark
                );
            } else {
                $update_result['success'][] = array('store_code' => $sub_sku['store_code'], 'barcode' => $sub_sku['barcode']);
            }
        }
        //更改记录方式
        /*
          if (!empty($store_code_arr) && !empty($sku_arr)) {
          $store_code = implode("','",$store_code_arr);
          $sku = implode("','",$sku_arr);
          $sql = "select store_code, sku, inv_record_id from goods_inv_record where relation_code = 'wms_init' and store_code in ('$store_code') and sku in ('$sku')";

          $res = CTX()->db->getAll($sql);


          if ($res) {
          $new_store_sku = load_model('util/ViewUtilModel')->get_map_arr($res, 'store_code,sku',0,'inv_record_id');

          foreach ($store_code_and_sku as $key => $store_sku) {
          if (array_key_exists($key, $new_store_sku)) {
          $sql = "update goods_inv_record set record_time='".date("Y-m-d H:i:s")."' where inv_record_id =".$new_store_sku[$key];
          CTX()->db->query($sql);
          } else {

          $values = "values ('".
          $store_sku['goods_code']."','".
          $store_sku['spec1_code']."','".
          $store_sku['spec2_code']."','".
          $store_sku['sku']."','{$default_lof_no}','{$default_lof_production_date}','".
          $store_sku['store_code']."',1,0,0,0,0,0,'".
          date("Y-m-d H:i:s")."','wms_init','adjust')";
          $sql = "insert into goods_inv_record(goods_code,
          spec1_code,spec2_code,
          sku,lof_no,production_date,
          store_code,occupy_type,
          stock_change_num,stock_lof_num_before_change,
          stock_num_before_change,stock_num_after_change,
          stock_lof_num_after_change,record_time,
          relation_code,relation_type )".$values;
          CTX()->db->query($sql);
          }
          }
          }
          }
         */

        /*
          echo '<hr/>$inv_info<xmp>'.var_export($inv_info,true).'</xmp>';
          echo '<hr/>$set_zero_id_arr<xmp>'.var_export($set_zero_id_arr,true).'</xmp>';
          echo '<hr/>$up_lof_arr<xmp>'.var_export($up_lof_arr,true).'</xmp>';
          echo '<hr/>$up_inv_arr<xmp>'.var_export($up_inv_arr,true).'</xmp>';
          echo '<hr/>$log_arr<xmp>'.var_export($log_arr,true).'</xmp>';
          echo '<hr/>$update_result<xmp>'.var_export($update_result['success'],true).'</xmp>';
          die; */
        $ret = $this->update_act($set_zero_id_arr, $up_lof_arr, $up_inv_arr, $log_arr);

        //插入日志
        $this->insert_multi_exp('goods_inv_record', $inv_record_log);

        foreach ($log_arr as $sub_log) {
            if ($ret['status'] < 0) {
                $update_result['fail']['update_fail'][] = array('store_code' => $sub_log['efast_store_code'], 'barcode' => $sub_log['barcode']);
            } else {
                $update_result['success'][] = array('store_code' => $sub_log['efast_store_code'], 'barcode' => $sub_log['barcode']);
            }
        }
        return $this->format_ret(1, $update_result);
    }

    function update_act($set_zero_id_arr, $up_lof_arr, $up_inv_arr, $log_arr) {
        ctx()->db->begin_trans();
        if (!empty($set_zero_id_arr)) {
            $set_zero_id_list = join(',', $set_zero_id_arr);
            $sql = "update goods_inv_lof set stock_num = 0 where goods_inv_id in({$set_zero_id_list})";
            ctx()->db->query($sql);
        }
        $update_str = "stock_num = VALUES(stock_num)";
        $ret = $this->insert_multi_duplicate('goods_inv_lof', $up_lof_arr, $update_str);
        if ($ret['status'] < 0) {
            ctx()->db->rollback();
            return $this->format_ret(-1, '', '更新库存批次表失败');
        }
        $update_str = "stock_num = VALUES(stock_num),record_time = VALUES(record_time)";
        $ret = $this->insert_multi_duplicate('goods_inv', $up_inv_arr, $update_str);
        if ($ret['status'] < 0) {
            ctx()->db->rollback();
            return $this->format_ret(-1, '', '更新库存失败');
        }
        $ret = M('wms_goods_inv_log')->insert_multi($log_arr);
        if ($ret['status'] < 0) {
            ctx()->db->rollback();
            return $this->format_ret(-1, '', '记录库存日志失败');
        }
        ctx()->db->commit();
    }

    /**
     * $wms_inv_data = array('barcode'=>'xx','num'=>xx);
     */
    function insert_wms_goods_inv($efast_store_code, $wms_inv_data) {
        $cp_wms_arr = array('ydwms', 'qimen', 'shunfeng');
        if (in_array($this->wms_cfg['api_product'], $cp_wms_arr)) {
            return $this->insert_wms_goods_inv_all($efast_store_code, $wms_inv_data);
        }
      //  $barcode_list = load_model('util/ViewUtilModel')->get_arr_val_by_key($wms_inv_data, 'barcode', 'string', 'string');
        $barcode_arr = array(); 
        foreach ($wms_inv_data as $sub_arr) {
            $_t = isset($sub_arr['barcode']) ? $sub_arr['barcode'] : null;
            if (empty($_t)) {
                continue;
            }
            $barcode_arr[] = addslashes($_t);//处理特殊字符转义
        }
        $barcode_list = "'" . join("','", $barcode_arr) . "'";

        
        $sql = "select barcode,num,is_sync,sync_time from wms_goods_inv where efast_store_code = :store_code and barcode in({$barcode_list})";
        $db_inv = ctx()->db->get_all($sql, array(':store_code' => $efast_store_code));
        $inv_arr = load_model('util/ViewUtilModel')->get_map_arr($db_inv, 'barcode', 0);

        $now_time = date('Y-m-d H:i:s');
        $up_data = array();
        foreach ($wms_inv_data as $sub_wms) {
            $_barcode = $sub_wms['barcode'];
            $find_efast = isset($inv_arr[$_barcode]) ? $inv_arr[$_barcode] : null;
            $efast_num = isset($find_efast) ? $find_efast['num'] : 0;
            $efast_is_sync = isset($find_efast) ? $find_efast['is_sync'] : 1;
            $sync_time = $efast_is_sync == 1 ? $find_efast['sync_time'] : $now_time;
            $row = array(
                'efast_store_code' => $efast_store_code,
                'barcode' => $_barcode,
                'num' => $sub_wms['num'],
                'down_time' => $now_time,
                'is_sync' => $efast_is_sync,
                'sync_time' => $sync_time,
                'is_success' => 0
            );
            if ($sub_wms['num'] != $efast_num) {
                $row['is_sync'] = 1;
            }
            $up_data[] = $row;
        }
        $update_str = "num = VALUES(num),is_sync = VALUES(is_sync),down_time = VALUES(down_time),sync_time = VALUES(sync_time),is_success = VALUES(is_success)";
        /*
          echo '<hr/>$wms_inv_data<xmp>'.var_export($wms_inv_data,true).'</xmp>';
          echo '<hr/>$up_data<xmp>'.var_export($up_data,true).'</xmp>';
          die; */
        $ret = $this->insert_multi_duplicate('wms_goods_inv', $up_data, $update_str);
        return $ret;
    }

    //韵达使用，包含次品仓库
    function insert_wms_goods_inv_all($efast_store_code, $wms_inv_data) {
        $barcode_arr = array_keys($wms_inv_data);
        $barcode_list = "'" . implode("','", $barcode_arr) . "'";
        $sql = "select barcode,num,is_sync,sync_time from wms_goods_inv where efast_store_code = :store_code and barcode in({$barcode_list})";
        $db_inv = ctx()->db->get_all($sql, array(':store_code' => $efast_store_code));
        $inv_arr = load_model('util/ViewUtilModel')->get_map_arr($db_inv, 'barcode', 0);

        $cp_store_code = $this->get_cp_store_code($efast_store_code);
        $now_time = date('Y-m-d H:i:s');
        $up_data = array();
        foreach ($wms_inv_data as $sub_wms) {
            $_barcode = $sub_wms['barcode'];
            $find_efast = isset($inv_arr[$_barcode]) ? $inv_arr[$_barcode] : null;
            $efast_num = isset($find_efast) ? $find_efast['num'] : 0;
            $efast_is_sync = isset($find_efast) ? $find_efast['is_sync'] : 1;
            $sync_time = $efast_is_sync == 1 ? $find_efast['sync_time'] : $now_time;
            if (isset($sub_wms['num'])) {
                $row = array(
                    'efast_store_code' => $efast_store_code,
                    'barcode' => $_barcode,
                    'num' => $sub_wms['num'],
                    'down_time' => $now_time,
                    'is_sync' => $efast_is_sync,
                    'sync_time' => $sync_time,
                    'is_success' => 0
                );
            }
            if (!empty($cp_store_code) && isset($sub_wms['cp_num'])) {
                $up_data[] = array(
                    'efast_store_code' => $cp_store_code,
                    'barcode' => $_barcode,
                    'num' => $sub_wms['cp_num'],
                    'down_time' => $now_time,
                    'is_sync' => $efast_is_sync,
                    'sync_time' => $sync_time,
                    'is_success' => 0
                );
            }

            if (!empty($row)) {
                if ($sub_wms['num'] != $efast_num) {
                    $row['is_sync'] = 1;
                }
                $up_data[] = $row;
            }
        }
        $update_str = "num = VALUES(num),is_sync = VALUES(is_sync),down_time = VALUES(down_time),sync_time = VALUES(sync_time),is_success = VALUES(is_success)";
        /*
          echo '<hr/>$wms_inv_data<xmp>'.var_export($wms_inv_data,true).'</xmp>';
          echo '<hr/>$up_data<xmp>'.var_export($up_data,true).'</xmp>';
          die; */
        $ret = $this->insert_multi_duplicate('wms_goods_inv', $up_data, $update_str);
        return $ret;
    }

    /**@date 2017.5.25 修改
     * @todo wms库存增量下载，目前wms库存下载定时服务每15分钟执行一次，$each_time=20错开5分钟防止漏下
     */
    function sync_incr($efast_store_code, $each_time = 20) {
        $wms_cfg = $this->get_wms_cfg($efast_store_code);
        $api_product = $wms_cfg['api_product'];
        $class_name = ucfirst($api_product) . 'InvModel';
        require_model("wms/{$api_product}/" . $class_name);
        $wms_inv_obj = new $class_name();

        $biz_code = $this->api_product . '_stock_sync';
        //上次结束时间
        $prev_end_time = $this->get_incr_service_end_time($efast_store_code, $biz_code);
        //当前时间
        $now_zero_time = date('Y-m-d', time()) . ' 00:00:00';

        if (empty($prev_end_time)) {
            $start_time = $now_zero_time;
        } else {
            //$start_time = $prev_end_time < $now_zero_time ? $now_zero_time : $prev_end_time;
            //为了冗余
            $start_time = date('Y-m-d H:i:s', strtotime($prev_end_time) - 15);
        }
        //如果上次结束时间和当前时间不在同一天，执行一次全量库存同步，并插入一条数据值wms_incr_time_tag，使上次结束时间与当前时间同一天，继续增量下载
        if(date('m-d', strtotime($start_time)) != date('m-d') || empty($prev_end_time)) {
            $this->down_wms_stock_by_store_code($efast_store_code, $api_product);
            $ins_row = array(
                'efast_store_code' => $efast_store_code,
                'biz_code' => $biz_code,
                'start_time' => $start_time,
                'end_time' => date('Y-m-d H:i:s'),
                'status' => 1
            );
            $ret = $this->insert_exp('wms_incr_time_tag', $ins_row);
            return $this->format_ret(1, '', 'sync_success');
        }
        
        while (1) {
            $end_time = date('Y-m-d H:i:s', strtotime($start_time) + $each_time * 60);
            $end_time = $end_time > date('Y-m-d H:i:s') ? date('Y-m-d H:i:s') : $end_time;
            $ins_row = array(
                'efast_store_code' => $efast_store_code,
                'biz_code' => $biz_code,
                'start_time' => $start_time,
                'end_time' => $end_time,
            );
            $ret = $this->insert_exp('wms_incr_time_tag', $ins_row);
            $sync_ret = $wms_inv_obj->sync_inv_incr($efast_store_code, $start_time, $end_time);
            if ($sync_ret['status'] > 0) {
                if (is_array($sync_ret['data'])) {
                    $this->insert_wms_goods_inv($efast_store_code, $sync_ret['data']);
                }
                $this->db->update('wms_incr_time_tag', array('status' => 1), array('id' => $ret['data']));
            }
            $_nt = date('Y-m-d H:i:s', time());
            if (strtotime($end_time) >= strtotime($_nt)) {
                break;
            }
            $start_time = $end_time;
        }
        return $this->format_ret(1, '', 'sync_incr_success');
    }

    //调整单据
    function create_inv_order($order_info, $order_mx, $reocrd_type, $item_type = 1) {
        $new_record_code = $this->get_new_record_code($reocrd_type);
        $record_data = array();
        $record_data['record_code'] = $new_record_code;
        $record_data['record_type'] = $reocrd_type;
        $record_data['wms_record_code'] = $order_info['wms_record_code'];
        $record_data['process_time'] = $order_info['process_time'];
        $record_data['wms_order_from_flag'] = isset($order_info['wms_order_from_flag']) ? $order_info['wms_order_from_flag'] : 20;
        $store_code = $this->get_store_code_by_out_store_code($order_info['wms_store_code']);


        $record_data['efast_store_code'] = $store_code;
        if ($item_type == 0) {
            $record_data['efast_store_code'] = $this->get_cp_store_code($store_code);
        }
        if (empty($this->api_product)) {
            $this->get_wms_cfg($store_code);
        }
        
        if(isset($order_info['order_status'])&&$order_info['order_status']<> 'flow_end'){
                $record_data['wms_order_flow_end_flag'] = 0;
        }else{
                $record_data['wms_order_flow_end_flag'] = 1;
        }

        $record_data['upload_request_flag'] = 10;
        $record_data['upload_response_flag'] = 10;

        $record_data['api_product'] = $this->api_product;
        $record_detail = array();
        foreach ($order_mx as $val) {
            $record_detail[] = array('record_code' => $new_record_code, 'record_type' => $reocrd_type, 'barcode' => $val['barcode'], 'efast_sl' => '0', 'wms_sl' => $val['sl']);
        }
        $update_str = " wms_order_flow_end_flag = VALUES(wms_order_flow_end_flag) ";
        $this->insert_multi_duplicate('wms_b2b_trade', array($record_data),$update_str);
        
        $this->insert_multi_exp('wms_b2b_order', $record_detail);
        $mod = $this->get_biz_dj($reocrd_type);
        if ($mod === false) {
            return false;
        }
        $ret = $mod->order_shipping($new_record_code, $record_data['process_time'], $order_mx);

        if ($ret['status'] > 0) {
            load_model('wms/WmsRecordModel')->uploadtask_order_status_sync_success($new_record_code, $reocrd_type);
        } else {
            load_model('wms/WmsRecordModel')->uploadtask_order_status_sync_fail($new_record_code, $reocrd_type, $ret['message']);
        }
    }

    private function get_new_record_code($record_type) {
        $new_record_code = '';
        switch ($record_type) {
            case 'adjust':
                $new_record_code = $this->get_adjust_fast_bill_sn();
                break;
            default :
                break;
        }
        return $new_record_code;
    }

    function get_adjust_fast_bill_sn() {
        $num = 0;
        $new_record_code = '';
        while (true) {
            $new_record_code = load_model('stm/StockAdjustRecordModel')->create_fast_bill_sn($num);
            $num++;
            $sql = "select record_code from wms_b2b_trade where record_code=:record_code ";
            $row = $this->db->get_row($sql, array(':record_code' => $new_record_code));
            if (empty($row)) {
                break;
            }
        }
        return $new_record_code;
    }

    function get_biz_dj($record_type) {
        $record_type_arr = array('adjust');
        if (!in_array($record_type, $record_type_arr)) {
            return FALSE;
        }

        $ks = "{$record_type}";
        if (!isset($this->biz_dj[$ks])) {
            $class_name = 'Wms' . join('', array_map('ucfirst', explode('_', $record_type))) . "Model";
            require_model("wms/{$class_name}");
            $this->biz_dj[$ks] = new $class_name;
        }
        return $this->biz_dj[$ks];
    }

}
