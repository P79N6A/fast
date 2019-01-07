<?php

require_model('tb/TbModel');

class JxcReportModel extends TbModel {

    private $jxc_info_mx_tbls;
    private $select_data = array();
    private $select_condition = "";

    function __construct() {
        @ini_set('memory_limit', '2048M');
        set_time_limit(0);
        date_default_timezone_set('PRC');
        parent::__construct();
        $this->jxc_info_mx_tbls = $this->db->get_all_col("show TABLES like 'jxc_info_mx_%'");
        rsort($this->jxc_info_mx_tbls);
    }

    function init_cur_month_mx_tbl($year, $month) {
        $cur_tbl = 'jxc_info_mx_' . $year . '_' . $month;
        if (in_array($cur_tbl, $this->jxc_info_mx_tbls)) {
            return $this->format_ret(1, $cur_tbl, $cur_tbl . '已存在');
        }
        $sql = "CREATE TABLE IF NOT EXISTS `{$cur_tbl}` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `order_code` varchar(20) DEFAULT '' COMMENT '单据编号',
				  `order_type` varchar(20) DEFAULT '' COMMENT '单据类型',
				  `goods_code` varchar(20) DEFAULT '' COMMENT '商品编码',
				  `spec1_code` varchar(20) DEFAULT '' COMMENT '规格编码',
				  `spec2_code` varchar(20) DEFAULT '' COMMENT '规格2编码',
				  `sku` varchar(128) DEFAULT '' COMMENT '商品编码',
				  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
				  `lof_no` varchar(20) DEFAULT '' COMMENT '批次号',
				  `production_date` date DEFAULT NULL COMMENT '生产日期',
				  `num` int(11) DEFAULT '0' COMMENT '库存数量',
				  `order_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
				  `tbl_last_modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '业务表的数据最后更新时间',
				  `tbl_id` int(11) NOT NULL COMMENT '业务表数据的ID号',
				  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `_index_key` (`order_code`,`order_type`,`sku`,`lof_no`,`production_date`,`order_date`) USING BTREE
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='进销存明细表';";
        $ret = ctx()->db->query($sql);
        if ($ret != true) {
            return $this->format_ret(-1, '', '新建表' . $cur_tbl . '失败');
        }
        $this->jxc_info_mx_tbls[] = $cur_tbl;
        rsort($this->jxc_info_mx_tbls);
        return $this->format_ret(1, $cur_tbl, '新建表' . $cur_tbl . '成功');
    }

    //汇总进销存明细表到总表
    function group_mx_to_jxc_info($mx_tbl) {
        $_t = str_replace('jxc_info_mx_', '', $mx_tbl);
        $ymonth = str_replace('_', '-', $_t);
        $sql = "select order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,'{$ymonth}' as ymonth,sum(num) as num,max(lastchanged) as mx_lastchanged from {$mx_tbl} group by sku,order_type,store_code,lof_no,production_date";
        $db_mx = ctx()->db->get_all($sql);
        if (empty($db_mx)) {
            return $this->format_ret(1);
        }
        $ret = $this->insert_multi_duplicate('jxc_info',$db_mx, ' num = VALUES(num) ');
        return $ret;
    }

     function group_mx_to_jxc_info_inr() {
        $mx_tbl = 'jxc_info_mx_' . date('Y_m');
        $ymonth = date('Y-m');
        $sql = "select lastchanged from jxc_info_group   where tbl_mx='{$mx_tbl}'";
        $lastchanged = $this->db->get_value($sql);

        $sql = "select order_type,goods_code,spec1_code,spec2_code,sku,store_code,lof_no,production_date,'{$ymonth}' as ymonth,sum(num) as num,max(lastchanged) as mx_lastchanged from {$mx_tbl} where lastchanged>'{$lastchanged}' group by sku,order_type,store_code,lof_no,production_date";

        $db_mx = ctx()->db->get_all($sql);
        if (empty($db_mx)) {
            return $this->format_ret(1);
        }
        $ret = $this->insert_multi_duplicate('jxc_info',$db_mx, ' num = num+VALUES(num) ');
        $new_lastchanged = date('Y-m-d H:i:s');
        $sql = "insert  into jxc_info_group(tbl_mx,lastchanged) values('{$mx_tbl}','{$new_lastchanged}') ON DUPLICATE KEY UPDATE lastchanged= VALUES(lastchanged)";

        ctx()->db->query($sql);
        return $ret;
    }




    //如果是前一个月没有汇总数据，那么汇总前一个月的数据
    function auto_group_mx_to_jxc_info($date = '',$is_force = 0) {
        //
        $cur_mx_tbl = 'jxc_info_mx_' . date('Y_m');
        if ($date != '') {
            $cur_mx_tbl = 'jxc_info_mx_' . $date;
        }else{
            $is_force = 1;//当月强制刷新
        }



        $sql = "select tbl_mx,lastchanged from jxc_info_group";
        $tbl_mx_arr = ctx()->db->get_all($sql);
        $_update_tb_arr = array();
        $now_tbl = 'jxc_info_mx_' . date('Y_m');
        foreach ($tbl_mx_arr as $val) {
            $sql = "select max(lastchanged) from {$val['tbl_mx']} ";
            $mx_max_lastchanged = $this->db->get_value($sql);
            $mx_max_last_date = strtotime($mx_max_lastchanged);
            $last_date =  strtotime($val['lastchanged']);
            if ($mx_max_last_date >= $last_date||$now_tbl==$val['tbl_mx']) {
                $_update_tb_arr[] = $val['tbl_mx'];
            }
        }


        foreach ($this->jxc_info_mx_tbls as $_tbl) {

            if (in_array($_tbl, $_update_tb_arr)&&$is_force==0) {
                continue;
            }

            $ret = $this->group_mx_to_jxc_info($_tbl);
            if ($ret['status'] > 0) {
                $lastchanged = date('Y-m-d H:i:s');
                $sql = "insert  into jxc_info_group(tbl_mx,lastchanged) values('{$_tbl}','{$lastchanged}') ON DUPLICATE KEY UPDATE lastchanged= VALUES(lastchanged)";

                ctx()->db->query($sql);
            }
            $this->check_data_is_ok($_tbl);
        }
    }

    //取mx表最大的last_modified 和 id ,$type = b2b | oms
    function get_mx_max_info($type = 'b2b') {
        $order_type_limit = $type == 'b2b' ? 'adjust,purchase,pur_return,shift_out,shift_in,wbm_return,wbm_store_out' : 'sell_record,sell_return';
        $order_type_limit = "'" . join("','", explode(',', $order_type_limit)) . "'";
        $max_tbl_last_modified = '0000-00-00 00:00:00';
        $max_tbl_id = 0;
        foreach ($this->jxc_info_mx_tbls as $_tbl) {
            $sql = "select max(tbl_last_modified) as last_modified,max(tbl_id) as tbl_id from {$_tbl} where order_type in({$order_type_limit})";
            $max_row = ctx()->db->get_row($sql);
            if (!empty($max_row) && !empty($max_row['last_modified'])) {
                $max_tbl_last_modified = $max_row['last_modified'];
                $max_tbl_id = $max_row['tbl_id'];
                break;
            }
        }

        $ret = array('max_tbl_last_modified' => $max_tbl_last_modified, 'max_tbl_id' => $max_tbl_id);
        return $ret;
    }

    function sync_data($is_auto = 0) {
        $this->sync_data_by_type('b2b');
        $this->sync_data_by_type('oms');

        if ($is_auto == 1) {
            $this->auto_group_mx_to_jxc_info();
            $this->other_data();
        }else{
            $date = date('Y-m');
            list($year,$month) = explode('-', $date);
            $this->init_cur_month_mx_tbl($year, $month);
        }
        return $this->format_ret(1);
    }

    //增量更新进销存明细表 $type = b2b | oms
    function sync_data_by_type($type) {
        $ret = $this->get_mx_max_info($type);
        $max_tbl_last_modified = $ret['max_tbl_last_modified'];
        $max_tbl_id = $ret['max_tbl_id'];
        if (empty($max_tbl_last_modified)) {
            return $this->format_ret(-1, '', '取 max_tbl_last_modified 出现异常');
        }
        $flag = '=';
        $_tbl_last_modified = $max_tbl_last_modified;
        $_tbl_id = $max_tbl_id;
        $limit_next = 0;
        while (1) {
            //$limit_next = ($flag == '=') ? $limit_next : 0;

            $ret = $this->sync_data_each($type, $_tbl_last_modified, $_tbl_id, $flag, 5000, $limit_next);
            if ($ret['status'] < 0) {
                break;
            }
            if ($ret['status'] ==10) {
                break;
            }

            $ret_data = $ret['data'];

            if (!empty($ret_data)) {
                if ($ret_data['tbl_last_modified'] > $_tbl_last_modified) {//|| ($ret_data['tbl_last_modified'] == $_tbl_last_modified && $ret_data['tbl_id'] == $_tbl_id)
                    $_tbl_last_modified = $ret_data['tbl_last_modified'];
                    $_tbl_id = $ret_data['tbl_id'];
                    //$flag = '=';
                }else{
                       $limit_next++;
                }
            } else {
                break;
            }
        }
        return $this->format_ret(1);
    }

    function sync_data_each($type, $max_tbl_last_modified, $max_tbl_id, $flag, $batch_num = 5000, $next_limit = 0) {
        if ($type == 'b2b') {
            $sql = "select id as tbl_id, order_code,order_type, order_date, goods_code,spec1_code,spec2_code,sku, store_code, lof_no, production_date,  num, lastchanged as tbl_last_modified,occupy_type from b2b_lof_datail where occupy_type in (2, 3)";
        } else {
            $sql = "select id as tbl_id, record_code as order_code,record_type as order_type, order_date, goods_code, spec1_code, spec2_code, sku, store_code, lof_no, production_date, num, lastchanged as tbl_last_modified, occupy_type from oms_sell_record_lof where occupy_type in (2, 3)";
        }

        $sql .= " and lastchanged >='{$max_tbl_last_modified}'";

        if ($next_limit == 0) {
            $sql .= " order by lastchanged limit {$batch_num}";
        } else {
            $start_num = $next_limit * $batch_num;
            $sql .= " order by lastchanged limit {$start_num},{$batch_num}";
        }

        $db_detail = ctx()->db->get_all($sql);

        if (empty($db_detail)) {
            return $this->format_ret(-10);
        }
        $count = count($db_detail);
        $ret_status = ($count<$batch_num)?10:1;
        $detail_arr = array();
        foreach ($db_detail as $sub_detail) {
            if ($sub_detail['occupy_type'] == 2) {
                $sub_detail['num'] = $sub_detail['num'] * -1;
            }
            if ($type == 'oms') {
                $sub_detail['order_type'] = $sub_detail['order_type'] == 1 ? 'sell_record' : 'sell_return';
            }

            unset($sub_detail['occupy_type']);
            $detail_arr[$sub_detail['order_date']][] = $sub_detail;
        }

        foreach ($detail_arr as $order_date => $sub_detail) {
            if ($order_date == '0000-00-00') {
                $_y = '1970';
                $_m = '01';
            } else {
                $_t = explode('-', $order_date);
                $_y = $_t[0];
                $_m = $_t[1];
            }
            $ret = $this->init_cur_month_mx_tbl($_y, $_m);
            if ($ret['status'] < 0) {
                return $this->format_ret(-1, '', $ret['message']);
            }
            M($ret['data'])->insert_multi($sub_detail, true);
        }
        $last_row = end($db_detail);
        return $this->format_ret($ret_status, $last_row);
    }

    function check_data_is_ok($_tbl) {

        //'jxc_info_mx_' . $year . '_' . $month;
        $date = str_replace('jxc_info_mx_', '', $_tbl);
        $date = str_replace('_', '-', $date);
        $sql = "select sum(num) from jxc_info where ymonth=:ymonth";
        $sql_value = array(':ymonth' => $date);
        $m_mont_num = $this->db->get_value($sql, $sql_value);

        $sql = "select sum(num) from {$_tbl}";
        $month_num = $this->db->get_value($sql);
        if ($m_mont_num != $month_num) {
            $sql = "delete from jxc_info where ymonth='{$date}'";
            $this->db->query($sql);
            $sql = "delete from jxc_info_group where tbl_mx='{$_tbl}'";
            $this->db->query($sql);
        }
        $ret = $this->group_mx_to_jxc_info($_tbl);
        if ($ret['status'] > 0) {
            $sql = "insert ignore into jxc_info_group(tbl_mx) values('{$_tbl}')";
            //echo $sql."<br/>";
            ctx()->db->query($sql);
        }
    }

    function get_goods_code_by_search($filter) {
        if (!empty($filter['goods_code'])) {
            $sql = "select goods_code from base_goods where goods_code like :goods_code";
            $goods_code_arr = ctx()->db->get_all_col($sql, array(':goods_code' => "%{$filter['goods_code']}%"));
            return $goods_code_arr;
        }

        $sql_main_arr = array();
        $sql_values = array();

        if (!empty($filter['category_code'])) {
            $code_arr = explode(',', $filter['category_code']);
            $in_sql = CTX()->db->get_in_sql('category_code', $code_arr, $sql_values);
            $sql_main_arr[] = " AND rl.category_code IN (" . $in_sql . ") ";
        }
        if (!empty($filter['goods_name'])) {
            $arr = explode(',', $filter['goods_name']);
            $goods_name = $this->arr_to_like_sql_value($arr, 'goods_name', $sql_values,'rl.');
            $sql_main_arr[] = " AND {$goods_name}";
        }

        if (!empty($filter['brand_code'])) {
            $code_arr = explode(',', $filter['brand_code']);
            $in_sql = CTX()->db->get_in_sql('brand_code', $code_arr, $sql_values);
            $sql_main_arr[] = " AND rl.brand_code IN (" . $in_sql . ") ";
        }

        if (!empty($filter['season_code'])) {
            $code_arr = explode(',', $filter['season_code']);
            $in_sql = CTX()->db->get_in_sql('season_code', $code_arr, $sql_values);
            $sql_main_arr[] = " AND rl.season_code IN (" . $in_sql . ") ";
        }

        if (!empty($filter['season_code'])) {
            $code_arr = explode(',', $filter['season_code']);
            $in_sql = CTX()->db->get_in_sql('season_code', $code_arr, $sql_values);
            $sql_main_arr[] = " AND rl.season_code IN (" . $in_sql . ") ";
        }
        // 年份
        if (!empty($filter['year_code'])) {
            $code_arr = explode(',', $filter['year_code']);
            $in_sql = CTX()->db->get_in_sql('year_code', $code_arr, $sql_values);
            $sql_main_arr[] = " AND rl.year_code IN (" . $in_sql . ") ";
        }
        if (empty($sql_main_arr)) {
            return false;
        }

        $sql = "select rl.goods_code from base_goods rl where 1=1 " . join(' ', $sql_main_arr);
        $goods_code_arr = ctx()->db->get_all_col($sql, $sql_values);
        return $goods_code_arr;
    }

    //----------------------------------------------------------------------------------------------
    /**
     * 2015-01-05 - 2015-03-07
     * 1) <2015-01 qc
     * 2) >=2015-01-01 and <=2015-01-31 qcmx
     * 3) >=2015-03-01 and <=2015-03-07 qmmx
     * 4) >2015-01 and <2015-03 btwmx
     */
    function get_page_data($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $order_date_end_limit = '';
        //echo '<hr/>$jxc_info_mx_tbls<xmp>'.var_export($this->jxc_info_mx_tbls,true).'</xmp>';
        if (isset($this->jxc_info_mx_tbls[0])) {
            $tbl = $this->jxc_info_mx_tbls[0];
            $sql = "select max(order_date) from {$tbl}";
            //echo $sql;
            $tv = ctx()->db->getOne($sql);
            if (empty($tv) && $tv > '0000-00-00') {
                $order_date_end_limit = $tv;
            }
        }

        $order_date_start = load_model('prm/JxcReportModel')->get_cur_month_first_day(date('Y-m-d'));
        $order_date_end = date('Y-m-d');
        if (empty($filter['order_date_start']) && empty($filter['order_date_end'])) {
            $start_date = $order_date_start;
            $end_date = $order_date_end;
        } else {
            $start_date = $filter['order_date_start'];
            $end_date = $filter['order_date_end'];
        }

        if ($end_date > $order_date_end_limit && $order_date_end_limit != '') {
            $end_date = $order_date_end_limit;
        }
        //echo '<hr/>$end_date<xmp>'.var_export($end_date,true).'</xmp>';die;
        $price_type = $filter['price_type'];
        $cur_page = $filter['page'];
        if ($cur_page == 1) {
            $this->sync_data();
            $this->group_mx_to_jxc_info_inr();
        }
        $page_size = $filter['page_size'];
        $ctl_type = $filter['ctl_type'];
        $wh = '';
        //仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $wh .= load_model('base/StoreModel')->get_sql_purview_store('store_code', $filter_store_code);
        if (!empty($filter['goods_barcode'])) {
            $sql = "select sku from goods_sku where barcode like :barcode";
            $sku_limit = ctx()->db->get_all_col($sql, array(':barcode' => "%{$filter['goods_barcode']}%"));
            if (!empty($sku_limit)) {
                $sku_list = "'" . join("','", $sku_limit) . "'";
                $wh .= " and sku in({$sku_list})";
            } else {
                $wh .= " and 1!=1";
            }
        } else {
            $goods_code_limit = $this->get_goods_code_by_search($filter);
            if ($goods_code_limit !== false) {
                if (!empty($goods_code_limit)) {
                    $goods_code_list = "'" . join("','", $goods_code_limit) . "'";
                    $wh .= " and goods_code in({$goods_code_list})";
                } else {
                    $wh .= " and 1!=1";
                }
            }
        }

        if (!empty($filter['store_code'])) {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_list = "'" . join("','", $store_code_arr) . "'";
            $wh .= " and store_code in({$store_code_list})";
        }
        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if (empty($store_arr)) {
                $wh .= " and 1=2";
            } else {
                $store_code_str = "'" . implode("','", $store_arr) . "'";
                $wh .= " and store_code in({$store_code_str})";
            }
        }

        $cur_date = date('Y-m-d');
        $cur_ymonth = date('Y-m');
        if ($end_date > $cur_date) {
            $end_date = $cur_date;
        }

        $qcmx_start_date = $this->get_cur_month_first_day($start_date);
        $qcmx_end_date = $this->get_cur_month_last_day($end_date);



        $qcmx_ret1 = $this->get_qcmx($qcmx_start_date, $qcmx_end_date, $start_date, $wh);
        $qcmx_data = $qcmx_ret1['mx2'];

        $qc_ym = date('Y-m', strtotime($start_date));
        $qc_data = $this->get_qc($qc_ym, $qcmx_ret1['mx1'], $wh);


        $qmmx_start_date = $this->get_cur_month_first_day($end_date);
        $qmmx_end_date = $end_date;

        if ($qcmx_start_date < $qmmx_start_date) {
            $qmmx_data = $this->get_qmmx($qmmx_start_date, $qmmx_end_date, $wh);
        } else {
            $qmmx_data = array();
        }

        $btwmxmx_start_ym = date('Y-m', strtotime($start_date));
        $btwmxmx_end_ym = date('Y-m', strtotime($end_date));
        /*
          echo '<hr/>$end_date<xmp>'.var_export($end_date,true).'</xmp>';
          echo '<hr/>$btwmxmx_end_ym<xmp>'.var_export($btwmxmx_end_ym,true).'</xmp>';
          echo '<hr/>$cur_ymonth<xmp>'.var_export($cur_ymonth,true).'</xmp>';
         */
        /*
          if ($btwmxmx_end_ym == $cur_ymonth){
          $btwmxmx_end_ym = $this->get_prev_month($end_date);
          } */
        $btwmxmx_data = $this->get_btwmx($btwmxmx_start_ym, $btwmxmx_end_ym, $wh);
        //echo '<hr/>$btwmxmx_data<xmp>'.var_export($btwmxmx_data,true).'</xmp>';die;

        $ret = $this->get_jxc($qc_data, $qcmx_data, $qmmx_data, $btwmxmx_data, $price_type, $cur_page, $page_size, $ctl_type);

        $page_data = $ret['page_data'];
        $rs_count = $ret['rs_count'];
        $page_num = $ret['page_num'];

        $filter['record_count'] = $rs_count;
        $result = array('data' => $page_data, 'filter' => $filter);
        return $this->format_ret(1, $result);
    }

    function get_list_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $order_date_start = $this->get_cur_month_first_day(date('Y-m-d'));
        $order_date_end = date('Y-m-d');
        if (empty($filter['order_date_start']) && $filter['order_date_end']) {
            $start_date = $order_date_start;
            $end_date = $filter['order_date_end'];
        }elseif(empty($filter['order_date_end']) && $filter['order_date_start']){
            $start_date = $filter['order_date_start'];
            $end_date = $order_date_end;
        }elseif(empty($filter['order_date_end']) && empty($filter['order_date_start'])) {
            $start_date = $order_date_start;
            $end_date = $order_date_end;
        }else{
            $start_date = $filter['order_date_start'];
            $end_date = $filter['order_date_end'];
        }

        $price_type = empty($filter['price_type'])?'sell_price':$filter['price_type']; //价格
        $page = (int) $filter['page'];
        if ($page == 1) {
            $this->sync_data();
            $this->group_mx_to_jxc_info_inr();
        }

        $ctl_type = $filter['ctl_type']; //是否导出

        $wh = $this->get_search_where($filter);
        $end_month = date('Y-m',  strtotime($end_date));
        $sql_num = " SELECT count(1) from( SELECT  1 from jxc_info i left join base_goods bs on bs.goods_code=i.goods_code where i.ymonth<='{$end_month}'  {$wh}  group by i.store_code,i.sku) as tb";

         if($filter['is_have_change']==1){ //有库存变化

            //SELECT  i.store_code,i.sku
             $sql_select = " from goods_inv i "
                     . "inner join base_goods bs on bs.goods_code=i.goods_code "
                     . "left join b2b_lof_datail d ON i.sku=d.sku AND i.store_code=d.store_code  AND d.occupy_type>1 AND d.order_date>='{$start_date}' AND  d.order_date<='{$end_date}' "
                      . "left join oms_sell_record_lof l ON i.sku=l.sku AND i.store_code=l.store_code  AND l.occupy_type>1 AND l.order_date>='{$start_date}' AND l.order_date<='{$end_date}'  "
                     . " where 1  AND (d.sku is not null OR l.sku is not null  )   {$wh}  group by i.store_code,i.sku";
                     $sql_num= " SELECT count(1) " .$sql_select;
        }else if($filter['is_have_change']==2){//无库存变化

            //SELECT  i.store_code,i.sku
             $sql_select = " from goods_inv i "
                     . "inner join base_goods bs on bs.goods_code=i.goods_code "
                     . "left join b2b_lof_datail d ON i.sku=d.sku AND i.store_code=d.store_code  AND d.occupy_type>1 AND d.order_date>='{$start_date}' AND  d.order_date<='{$end_date}' "
                    . "left join oms_sell_record_lof l ON i.sku=l.sku AND i.store_code=l.store_code  AND l.occupy_type>1 AND l.order_date>='{$start_date}' AND l.order_date<='{$end_date}'  "
                     . " where 1 AND (d.sku is null AND l.sku is null  )    {$wh}  group by i.store_code,i.sku";
             $sql_num= " SELECT count(1) " .$sql_select;
        }



        $record_count = $this->db->get_value($sql_num);


        if ($record_count == 0) {
            $filter['record_count'] = 0;
            $result = array('data' => array(), 'filter' => $filter);
            return $this->format_ret(1, $result);
        }
//        if($is_b2c===false){
//            $sql_select = "SELECT  i.store_code,i.sku from b2b_lof_datail i left join base_goods bs on bs.goods_code=i.goods_code where i.order_date<='{$end_date}'   AND i.occupy_type<>0   {$wh}  group by i.store_code,i.sku";
//        }else{
//            $sql_select = "SELECT  i.store_code,i.sku from oms_sell_record_lof i left join base_goods bs on bs.goods_code=i.goods_code where i.order_date<='{$end_date}'   AND i.occupy_type<>0   {$wh}  group by i.store_code,i.sku";
//        }
//


        //去掉此部分，有库存变化的
        //无库存变化　通过，jxc_info　连接　b2b ,oms lof 两个表 加入时间条件

        if($filter['is_have_change']==0){
            $sql_select = " SELECT  i.store_code,i.sku from jxc_info i left join base_goods bs on bs.goods_code=i.goods_code where i.ymonth<='{$end_month}'   {$wh}  group by i.store_code,i.sku";
        }else{
            $sql_select = " SELECT  i.store_code,i.sku ".$sql_select;
        }

        $page_size = (int) $filter['page_size'];

        $limit_start = ($page - 1) * $page_size;
        $sql_select .=" limit {$limit_start},{$page_size} ";

        $data = CTX()->db->get_all($sql_select);
        $order_type_arr = array(
            'adjust', 'purchase', 'pur_return', 'shift_out', 'shift_in', 'wbm_return', 'wbm_store_out', 'sell_record', 'sell_return', 'qc', 'qc_je', 'storage_in_num', 'storage_out_num', 'qm', 'qm_je'
        );

        $default_info = array();
        foreach ($order_type_arr as $defalut_k) {
            $default_info[$defalut_k] = 0;
        }



        $sql = "select store_code,store_name from base_store";
        $db_store = ctx()->db->get_all($sql);
        $store_arr = load_model('util/ViewUtilModel')->get_map_arr($db_store, 'store_code', 0, 'store_name');


        $this->select_data = array();
        $condition = array();

        foreach ($data as $sub_arr) {
            $_ks = "{$sub_arr['store_code']},{$sub_arr['sku']}";
            $key_arr = array('goods_code', 'goods_name', 'barcode', 'spec1_name', 'spec2_name', 'cost_price', 'price', 'purchase_price','sell_price');

            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_arr['sku'], $key_arr);
            $sku_info['price'] = isset($sku_info[$price_type]) ? $sku_info[$price_type] : $sku_info['price'];
            $sub_arr['store_name'] = $store_arr[$sub_arr['store_code']];

            $sub_arr = array_merge($sub_arr, $default_info);
            $this->select_data[$_ks] = array_merge($sub_arr, $sku_info);

            $condition[] = " store_code='{$sub_arr['store_code']}' AND sku='{$sub_arr['sku']}' ";
        }
        if (!empty($condition)) {
            $this->select_condition = " ( (" . implode(") OR (", $condition) . ") )";
        } else {
            $this->select_condition = " 1=1 ";
        }
        //获取开始时间月份的期初库存，期初金额
        $this->get_qc_data($start_date);
        //获取开始时间所在月份的数据
        $this->get_start_time_data($start_date, $end_date);
        //获取中间月份的数据
        $this->get_month_data($start_date, $end_date);
        //获取结束时间所在月份的数据
        $this->get_end_time_data($start_date, $end_date);

        $out_arr = array('pur_return', 'shift_out', 'wbm_store_out', 'sell_record');
        $in_arr = array('purchase', 'shift_in', 'wbm_return', 'sell_return');
        foreach ($this->select_data as &$val) {
            $val['qm'] += $val['qc'];//期末数
            foreach ($out_arr as $tou_k) {
                $val[$tou_k] = - abs($val[$tou_k]);
                $val['qm'] +=$val[$tou_k];
                $val['storage_out_num'] += abs($val[$tou_k]);//出库总数
            }
            foreach ($in_arr as $in_k) {
                $val['qm'] += abs($val[$in_k]);
                $val['storage_in_num'] += abs($val[$in_k]);//入库总数
            }
            $val['qm'] += $val['adjust'];
            $val['qm_je'] = $val['qm'] * $val['price'];//期末金额
        }


        $filter['record_count'] = $record_count;
        $filter['page_count'] = ceil($record_count / $page_size);
        $result = array('data' => $this->select_data, 'filter' => $filter);

        return $this->format_ret(1, $result);
    }

    private function get_qc_data($start_date) {

        $date_m = date('Y-m', strtotime($start_date));
        //统计开始时间之前月份的期初库存，期初金额
        $sql = "select  store_code,sku,sum(num) as num from jxc_info where  ymonth<'{$date_m}' AND  {$this->select_condition} group by store_code,sku ";
        $data_m = $this->db->get_all($sql);
        foreach ($data_m as $val) {
            $_ks = "{$val['store_code']},{$val['sku']}";
            $this->select_data[$_ks]['qc'] = $val['num'];
            $this->select_data[$_ks]['qc_je'] = $val['num'] * $this->select_data[$_ks]['price'];
        }
        $mx_tbl = 'jxc_info_mx_' . date('Y_m', strtotime($start_date));
        if (!in_array($mx_tbl, $this->jxc_info_mx_tbls)) {
            return $this->format_ret(-1, '', $mx_tbl . '不存在');
        }
        //统计开始时间所在月份的期初库存，期初金额
        $sql = "select store_code,sku,sum(num) as num from {$mx_tbl} where order_date<'{$start_date}' AND  {$this->select_condition} group by store_code,sku ";
        $data = $this->db->get_all($sql);
        foreach ($data as $val) {
            $_ks = "{$val['store_code']},{$val['sku']}";
            $this->select_data[$_ks]['qc'] += $val['num'];
            $this->select_data[$_ks]['qc_je'] += $val['num'] * $this->select_data[$_ks]['price'];
        }
    }

    private function get_start_time_data($start_date, $end_date) {
        $mx_tbl = 'jxc_info_mx_' . date('Y_m', strtotime($start_date));
        if (!in_array($mx_tbl, $this->jxc_info_mx_tbls)) {
            return $this->format_ret(-1, '', $mx_tbl . '不存在');
        }


        $sql = "select store_code,sku,order_type,sum(num) as num from {$mx_tbl} where order_date>='{$start_date}' ";

        $start_m = date('Y_m', strtotime($start_date));
        $end_m = date('Y_m', strtotime($end_date));
        //开始时间和结束时间不在同一个年月份的情况
        if ($start_m == $end_m) {//一个月
            $sql.=" AND order_date<='{$end_date}' ";
        }
        $sql.=" AND  {$this->select_condition} group by store_code,sku,order_type ";
        $data = $this->db->get_all($sql);
        foreach ($data as $val) {
            $_ks = "{$val['store_code']},{$val['sku']}";
            $this->select_data[$_ks][$val['order_type']] += $val['num'];
        }
    }

    private function get_month_data($start_date, $end_date) {
        $start_arr = explode('-', $start_date);
        $end_arr = explode('-', $end_date);
        $year_c = (int) $start_arr[0] - (int) $end_arr[0];
        $month_c = (int) $end_arr[1] - (int) $start_arr[1];
        //月份之间相差一个月
        //$check = $year_c * 12 + $month_c - 1;
        if ($year_c == 0) {//相同年份
            $check = $month_c - 1;
        } else {//跨年的情况
            $check = ($year_c == '-1' && $start_arr[1] == '12' && $end_arr[1] == '01') ? 0 : 1;
        }
        //获取中间月份的数据
        if ($check > 0) {
            $start_m = date('Y-m', strtotime($start_date));
            $end_m = date('Y-m', strtotime($end_date));
            $sql = "select store_code,sku,order_type,sum(num) as num from jxc_info where ymonth>'{$start_m}' AND ymonth<'{$end_m}' ";

            $sql.="AND  {$this->select_condition} group by store_code,sku,order_type ";
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                $_ks = "{$val['store_code']},{$val['sku']}";
                $this->select_data[$_ks][$val['order_type']] += $val['num'];
            }
        }
    }

    private function get_end_time_data($start_date, $end_date) {
        $start_m = date('Y_m', strtotime($start_date));
        $end_m = date('Y_m', strtotime($end_date));
        //不在同一个月的计算
        if ($start_m != $end_m) {
            $mx_tbl = 'jxc_info_mx_' . date('Y_m', strtotime($end_date));
            if (!in_array($mx_tbl, $this->jxc_info_mx_tbls)) {
                return $this->format_ret(-1, '', $mx_tbl . '不存在');
            }



            $sql = "select store_code,sku,order_type,sum(num) as num from {$mx_tbl} where order_date<='{$end_date}' ";

            $sql.="AND  {$this->select_condition} group by store_code,sku,order_type ";
            $data = $this->db->get_all($sql);
            foreach ($data as $val) {
                $_ks = "{$val['store_code']},{$val['sku']}";
                $this->select_data[$_ks][$val['order_type']] += $val['num'];
            }
        }
    }

    function get_search_where($filter) {
        //仓库权限
        $wh = '';
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $wh .= load_model('base/StoreModel')->get_sql_purview_store('i.store_code', $filter_store_code);
        //过滤品牌权限
        $filter_brand_code = isset($filter['brand_code']) ? $filter['brand_code'] : null;
        $wh .= load_model('prm/BrandModel')->get_sql_purview_brand('bs.brand_code', $filter_brand_code);
        if (!empty($filter['goods_barcode'])) {
            $sql = "select sku from goods_sku where barcode like :barcode";
            $sku_limit = ctx()->db->get_all_col($sql, array(':barcode' => "%{$filter['goods_barcode']}%"));
            if (!empty($sku_limit)) {
                $sku_list = "'" . join("','", $sku_limit) . "'";
                $wh .= " and i.sku in({$sku_list})";
            } else {
                $wh .= " and 1!=1";
            }
        } else {
            $goods_code_limit = $this->get_goods_code_by_search($filter);
            if ($goods_code_limit !== false) {
                if (!empty($goods_code_limit)) {
                    $goods_code_list = "'" . join("','", $goods_code_limit) . "'";
                    $wh .= " and i.goods_code in({$goods_code_list})";
                } else {
                    $wh .= " and 1!=1";
                }
            }
        }

        if (!empty($filter['store_code'])) {
            $store_code_arr = explode(',', $filter['store_code']);
            $store_code_list = "'" . join("','", $store_code_arr) . "'";
            $wh .= " and i.store_code in({$store_code_list})";
        }
        //仓库类别
        if (isset($filter['store_type_code']) && $filter['store_type_code'] != '') {
            $store_arr = load_model('base/StoreModel')->get_by_store_code_type($filter['store_type_code']);
            if (empty($store_arr)) {
                $wh .= " and 1=2";
            } else {
                $store_code_str = "'" . implode("','", $store_arr) . "'";
                $wh .= " and i.store_code in({$store_code_str})";
            }
        }

        return $wh;
    }

    /**
     * order_type adjust,purchase,pur_return,shift_out,shift_in,wbm_return,wbm_store_out,sell_record,sell_return
     */
    function get_jxc($qc_data, $qcmx_data, $qmmx_data, $btwmxmx_data, $price_type, $cur_page, $page_size, $ctl_type = '') {

        $m_data = array();
        $jxc_sku_arr = array();
        foreach ($qcmx_data as $sub_data) {
            $_ks = "{$sub_data['store_code']},{$sub_data['sku']}";
            $jxc_sku_arr[] = $_ks;
            if (!isset($m_data[$_ks])) {
                $m_data[$_ks] = array('store_code' => $sub_data['store_code'], 'sku' => $sub_data['sku']);
            }
            if (isset($m_data[$_ks][$sub_data['order_type']])) {
                $m_data[$_ks][$sub_data['order_type']]['num'] += $sub_data['num'];
            } else {
                $m_data[$_ks][$sub_data['order_type']] = $sub_data['num'];
            }
        }
        foreach ($btwmxmx_data as $sub_data) {
            $_ks = "{$sub_data['store_code']},{$sub_data['sku']}";
            $jxc_sku_arr[] = $_ks;
            if (!isset($m_data[$_ks])) {
                $m_data[$_ks] = array('store_code' => $sub_data['store_code'], 'sku' => $sub_data['sku']);
            }
            if (isset($m_data[$_ks][$sub_data['order_type']])) {
                $m_data[$_ks][$sub_data['order_type']] += $sub_data['num'];
            } else {
                $m_data[$_ks][$sub_data['order_type']] = $sub_data['num'];
            }
        }
        foreach ($qmmx_data as $sub_data) {
            $_ks = "{$sub_data['store_code']},{$sub_data['sku']}";
            $jxc_sku_arr[] = $_ks;
            if (!isset($m_data[$_ks])) {
                $m_data[$_ks] = array('store_code' => $sub_data['store_code'], 'sku' => $sub_data['sku']);
            }
            if (isset($m_data[$_ks][$sub_data['order_type']])) {
                $m_data[$_ks][$sub_data['order_type']] += $sub_data['num'];
            } else {
                $m_data[$_ks][$sub_data['order_type']] = $sub_data['num'];
            }
        }
        /*
          foreach($qc_data as $sub_data){
          $_ks = "{$sub_data['store_code']},{$sub_data['sku']}";
          $jxc_sku_arr[] = $_ks;
          } */
        $jxc_sku_arr = array_unique($jxc_sku_arr);
        sort($jxc_sku_arr);

        $start_idx = ($cur_page - 1) * $page_size;
        if ($ctl_type == 'export') {
            $page_sku = array_slice($jxc_sku_arr, $start_idx);
        } else {
            $page_sku = array_slice($jxc_sku_arr, $start_idx, $page_size);
        }
        $rs_count = count($jxc_sku_arr);
        $page_num = ceil($rs_count / $page_size);

        $sql = "select store_code,store_name from base_store";
        $db_store = ctx()->db->get_all($sql);
        $store_arr = load_model('util/ViewUtilModel')->get_map_arr($db_store, 'store_code', 0, 'store_name');
        $page_data = array();

        foreach ($page_sku as $_key) {
            $_find_qc_row = isset($qc_data[$_key]) ? $qc_data[$_key] : array();
            $_find_m_row = isset($m_data[$_key]) ? $m_data[$_key] : array();
            $_row = $_find_m_row;
            $_row['qc'] = isset($_find_qc_row['num']) ? $_find_qc_row['num'] : 0;
            $_row['store_code'] = isset($_row['store_code']) ? $_row['store_code'] : $_find_qc_row['store_code'];
            $_row['sku'] = isset($_row['sku']) ? $_row['sku'] : $_find_qc_row['sku'];
            $_row['store_name'] = isset($store_arr[$_row['store_code']]) ? $store_arr[$_row['store_code']] : '';
            $page_data[] = $_row;
        }
        $page_data = load_model('util/ViewUtilModel')->record_detail_append_goods_info($page_data, 1, 1);

        $sku_goods_code_map = array();
        foreach ($page_data as $sub_data) {
            $sku_goods_code_map[$sub_data['sku']] = $sub_data['goods_code'];
        }
        $ret = load_model('prm/GoodsModel')->get_goods_price($price_type, $sku_goods_code_map);
        $goods_price_info = (array) @$ret['data'];
        $order_type_arr = explode(',', 'adjust,purchase,pur_return,shift_out,shift_in,wbm_return,wbm_store_out,sell_record,sell_return');

        foreach ($page_data as $k => $sub_data) {
            $_price = isset($goods_price_info[$sub_data['sku']]) ? $goods_price_info[$sub_data['sku']] : 0;
            $page_data[$k]['price'] = $_price;
            $page_data[$k]['qc_je'] = $sub_data['qc'] * $_price;
            $qm_sl = $sub_data['qc'];
            foreach ($order_type_arr as $_order_type) {
                $page_data[$k][$_order_type] = isset($sub_data[$_order_type]) ? $sub_data[$_order_type] : 0;
                $qm_sl += $page_data[$k][$_order_type];
            }
            $page_data[$k]['qm'] = $qm_sl;
            $page_data[$k]['qm_je'] = $qm_sl * $_price;
            $page_data[$k]['storage_in_num'] = $page_data[$k]['purchase'] + $page_data[$k]['wbm_return'] + $page_data[$k]['sell_return'] + $page_data[$k]['shift_in'];
            $page_data[$k]['storage_out_num'] = $page_data[$k]['pur_return'] + $page_data[$k]['wbm_store_out'] + $page_data[$k]['sell_record'] + $page_data[$k]['shift_out'];
            if ($page_data[$k]['adjust'] > 0) {
                $page_data[$k]['storage_in_num'] += $page_data[$k]['adjust'];
            }
            if ($page_data[$k]['adjust'] < 0) {
                $page_data[$k]['storage_out_num'] += $page_data[$k]['adjust'];
            }
            $page_data[$k]['storage_out_num'] = abs($page_data[$k]['storage_out_num']);
            foreach ($order_type_arr as $_order_type) {
                if ($_order_type != 'adjust') {
                    $page_data[$k][$_order_type] = abs($page_data[$k][$_order_type]);
                }
            }
        }

        $rs_count = count($jxc_sku_arr);
        $page_num = ceil($rs_count / $page_size);

        $result = array('page_data' => $page_data, 'rs_count' => $rs_count, 'page_num' => $page_num);
        return $result;
    }

    function get_qc($end_ym, $add_mx, $wh) {
        $sql = "select store_code,sku,sum(num) as num from jxc_info where ymonth<'{$end_ym}' {$wh} group by store_code,sku";
        $db_qc = ctx()->db->get_all($sql);
        $add_mx = array_values($add_mx);
        $arr = array_merge($db_qc, $add_mx);
        $qc_data = array();
        foreach ($arr as $sub_arr) {
            $_ks = "{$sub_arr['store_code']},{$sub_arr['sku']}";
            if (isset($qc_data[$_ks])) {
                $qc_data[$_ks]['num'] += $sub_arr['num'];
            } else {
                $qc_data[$_ks] = $sub_arr;
            }
        }
        return $qc_data;
    }

    function get_qcmx($s_date, $e_date, $ywrq_start_date, $wh) {
        $mx_tbl = 'jxc_info_mx_' . date('Y_m', strtotime($s_date));
        if (!in_array($mx_tbl, $this->jxc_info_mx_tbls)) {
            $result = array('mx1' => array(), 'mx2' => array());
            return $result;
        }
        $sql = "select store_code,order_type,sku,sum(num) as num from {$mx_tbl} where order_date>='{$s_date}' and order_date<'{$ywrq_start_date}' {$wh} group by store_code,order_type,sku";
        $mx1_data = ctx()->db->get_all($sql);
        $sql = "select store_code,order_type,sku,sum(num) as num from {$mx_tbl} where order_date>='{$ywrq_start_date}' and order_date<='{$e_date}' {$wh} group by store_code,order_type,sku";
        $mx2_data = ctx()->db->get_all($sql);
        $result = array('mx1' => $mx1_data, 'mx2' => $mx2_data);
        return $result;
    }

    function get_qmmx($s_date, $e_date, $wh) {
        $mx_tbl = 'jxc_info_mx_' . date('Y_m', strtotime($s_date));
        if (!in_array($mx_tbl, $this->jxc_info_mx_tbls)) {
            return array();
        }
        $sql = "select store_code,order_type,sku,sum(num) as num from {$mx_tbl} where order_date>='{$s_date}' and order_date<='{$e_date}' {$wh} group by store_code,order_type,sku";
        $db_mx = ctx()->db->get_all($sql);
        $mx2_data = array(); //order_type,sku为维度统计
        foreach ($db_mx as $sub_mx) {
            $_ks2 = "{$sub_mx['store_code']},{$sub_mx['order_type']},{$sub_mx['sku']}";
            $mx2_data[$_ks2] = $sub_mx;
        }
        return $mx2_data;
    }

    function get_btwmx($btwmxmx_start_ym, $btwmxmx_end_ym, $wh) {
        if ($btwmxmx_start_ym == $btwmxmx_end_ym) {
            return array();
        }
        $sql = "select store_code,order_type,sku,sum(num) as num from jxc_info where ymonth>'{$btwmxmx_start_ym}' and ymonth<'{$btwmxmx_end_ym}' {$wh} group by store_code,order_type,sku";
        //echo $sql;die;
        $db_mx = ctx()->db->get_all($sql);
        $mx_data = array();
        foreach ($db_mx as $sub_mx) {
            $_ks = "{$sub_mx['store_code']},{$sub_mx['order_type']},{$sub_mx['sku']}";
            if ($mx_data[$_ks]) {
                $mx_data[$_ks]['num'] += $sub_mx['num'];
            } else {
                $mx_data[$_ks] = $sub_mx;
            }
        }
        return $mx_data;
    }

    //给定一个日期，获取其本月的第一天和最后一天
    function get_cur_month_first_day($date) {
        return date('Y-m-01', strtotime($date));
    }

    function get_cur_month_last_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month -1 day'));
    }

    //给定一个日期，获取其下月的第一天
    function get_next_month_first_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
    }

    //给定一个日期，获取其上月的第一天和最后一天
    function get_prev_month_first_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 month'));
    }

    function get_prev_month_last_day($date) {
        return date('Y-m-d', strtotime(date('Y-m-01', strtotime($date)) . ' -1 day'));
    }

    //给定一个日期，获取其上月 和 下月
    function get_prev_month($date) {
        return date('Y-m', strtotime(date('Y-m-01', strtotime($date)) . ' -1 month'));
    }

    function get_next_month($date) {
        return date('Y-m', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
    }

    function date_update() {
        set_time_limit(0);
        $sql = "  update b2b_lof_datail,pur_purchaser_record SET
b2b_lof_datail.order_date = pur_purchaser_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = pur_purchaser_record.record_code
AND  b2b_lof_datail.order_type='purchase'";
        $this->db->query($sql);


        $sql = "  update b2b_lof_datail,pur_return_record SET
b2b_lof_datail.order_date = pur_return_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = pur_return_record.record_code
AND  b2b_lof_datail.order_type='pur_return'";

        $this->db->query($sql);


        $sql = " update b2b_lof_datail,stm_stock_adjust_record SET
b2b_lof_datail.order_date = stm_stock_adjust_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = stm_stock_adjust_record.record_code
AND  b2b_lof_datail.order_type='adjust' ";


        $this->db->query($sql);
        $sql = " update b2b_lof_datail,wbm_store_out_record SET
b2b_lof_datail.order_date = wbm_store_out_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = wbm_store_out_record.record_code
AND  b2b_lof_datail.order_type='wbm_store_out' ";
        $this->db->query($sql);

        $sql = "update wbm_return_record SET
                record_time = lastchanged
            where is_sure=1 AND is_store_in=1 AND (record_time is NULL OR record_time='1970-01-01 00:00:00') ";
        $this->db->query($sql);


        $sql = "update b2b_lof_datail,wbm_return_record SET
b2b_lof_datail.order_date = wbm_return_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = wbm_return_record.record_code
AND  b2b_lof_datail.order_type='wbm_return' ";
        $this->db->query($sql);


        $sql = "update b2b_lof_datail,goods_inv_record SET
b2b_lof_datail.order_date = goods_inv_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = goods_inv_record.relation_code
AND  b2b_lof_datail.order_type='shift_out' and  goods_inv_record.relation_type='shift_out';";
        $this->db->query($sql);
        $sql = "update b2b_lof_datail,goods_inv_record SET
b2b_lof_datail.order_date = goods_inv_record.record_time,b2b_lof_datail.lastchanged=b2b_lof_datail.lastchanged
where b2b_lof_datail.order_code = goods_inv_record.relation_code
AND  b2b_lof_datail.order_type='shift_in' and  goods_inv_record.relation_type='shift_in';";
        $this->db->query($sql);

        $sql = "update oms_sell_record_lof,oms_sell_record SET
oms_sell_record_lof.order_date = oms_sell_record.delivery_date,oms_sell_record_lof.lastchanged=oms_sell_record_lof.lastchanged
where oms_sell_record_lof.record_code = oms_sell_record.sell_record_code
AND  oms_sell_record_lof.record_type='1';";
        $this->db->query($sql);

        $sql = "update oms_sell_record_lof,oms_return_package SET
oms_sell_record_lof.order_date = oms_return_package.stock_date,oms_sell_record_lof.lastchanged=oms_sell_record_lof.lastchanged
where oms_sell_record_lof.record_code = oms_return_package.return_package_code
AND  oms_sell_record_lof.record_type='2';";
        $this->db->query($sql);
        $sql = "update b2b_lof_datail set order_date=lastchanged ,lastchanged=lastchanged  where  order_date='1970-01-01'  AND occupy_type>1";
        $this->db->query($sql);

        $sql = "update b2b_lof_datail set order_date=lastchanged ,lastchanged=lastchanged  where   order_date is NULL  AND occupy_type>1";
        $this->db->query($sql);




//        $sql = "TRUNCATE jxc_info ";
//        $this->db->query($sql);
//
//        $sql = "TRUNCATE jxc_info_group ";
//        $this->db->query($sql);
//
//        $mx_all = $this->db->get_all_col("show TABLES like 'jxc_info_mx_%'");
//        foreach ($mx_all as $mx_tb) {
//            $drop_sql = 'TRUNCATE  ' . $mx_tb;
//            $this->db->query($drop_sql);
//        }
    }

    private $is_init = 0;

    function create_data_base() {
        set_time_limit(0);
//        $i = 0;
//
//        $this->sync_data_by_type('b2b');
//        $this->sync_data_by_type('oms');
//
//
//        $mx_all = $this->db->get_all_col("show TABLES like 'jxc_info_mx_%'");
//        $this->is_init = 1;
//        foreach ($mx_all as $tb) {
//            $tb = str_replace('jxc_info_mx_', '', $tb);
//            $this->auto_group_mx_to_jxc_info($tb);
//        }
    }

    function other_data() {


        $sql = "update jxc_info set num=-num where num>0 AND  order_type in('pur_return','shift_out','wbm_store_out','sell_record')";
        $this->db->query($sql);
        $sys_param= load_model('sys/SysParamsModel')->get_val_by_code(array('online_date'));
        $online_date = $sys_param['online_date'];
        $date =date('Y_m', strtotime($online_date));
        $now_date = date('Y_m');

        $online_date_int = strtotime($online_date);
        if($online_date_int>time()){
            //标识还未上线
            return false;
        }

        while(true){

            list($year, $month) = explode("_", $date);
            $this->init_cur_month_mx_tbl($year, $month);

            if($date==$now_date){
                break;
            }

            $date = $year."-".$month."-01";
            $date = date('Y_m', strtotime(date('Y-m-01', strtotime($date)) . ' +1 month'));
        }

        $mx_all = $this->db->get_all_col("show TABLES like 'jxc_info_mx_%'");

        $this->create_temp_tb();
        foreach ($mx_all as $tb) {
            $tb_date = str_replace('jxc_info_mx_', '', $tb);
            list($year, $month) = explode('_', $tb_date);

            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);



            $start_date = "{$year}-{$month}-01";
            $end_date = "{$year}-{$month}-{$days}";
            $check1 = $this->check_jxc_num($tb, 0, $start_date, $end_date); //零售

            $check2 = $this->check_jxc_num($tb, 1, $start_date, $end_date);

             $sql = "select count(1) from {$tb} where num>0 AND  order_type in('pur_return','shift_out','wbm_store_out','sell_record')";
             $check_num = $this->db->get_value($sql);
             $check3 = ($check_num>0)?true:false;
             if($check3){
                $sql = "update {$tb} set num=-num where num>0 AND  order_type in('pur_return','shift_out','wbm_store_out','sell_record')";
                $this->db->query($sql);
             }
            if ($check1 || $check2 || $check3) {
                $this->auto_group_mx_to_jxc_info($tb_date,1);
            }
        }
    }

    public function check_jxc_num($tb, $type, $start_date, $end_date) {


       $id_data = $this->get_id_info($tb, $type, $start_date, $end_date);
       if(!empty($id_data['del_id_arr'])){
           $id_str = implode(',', $id_data['del_id_arr']);
           $sql = " DELETE from  {$tb} where tbl_id in ({$id_str}) ";
           $this->db->query($sql);
       }

        if(!empty($id_data['add_id_arr'])){
             $id_str = implode(',', $id_data['add_id_arr']);
            $insert_sql = "insert  into {$tb}(order_code,
                order_type,
                goods_code,
                spec1_code,
                spec2_code,
                sku,
                store_code,
                lof_no,
                production_date,
                num,
                order_date,
                tbl_last_modified,
                tbl_id) ";
            if($type==0){
                $sql = "select  record_code,
                    if(record_type=1,'sell_record','sell_return') as record_type,
                   goods_code,
                   spec1_code,
                   spec2_code,
                   sku,
                   store_code,
                   lof_no,
                   production_date,
                    if(record_type=1,-num,num) as num,
                   order_date,
                   lastchanged,
                   id from oms_sell_record_lof where id in({$id_str})  ";
                   $this->db->query($insert_sql.$sql." ON DUPLICATE KEY UPDATE  num = VALUES(num) ");
            }else{

               $sql = "select  order_code,
                        order_type,
                        goods_code,
                        spec1_code,
                        spec2_code,
                        sku,
                        store_code,
                        lof_no,
                        production_date,
                         if(occupy_type=2,-num,num) as num,
                        order_date,
                        lastchanged,
                        id from b2b_lof_datail where id in({$id_str}) ";
                   $this->db->query($insert_sql.$sql." ON DUPLICATE KEY UPDATE  num = VALUES(num) ");
            }


       }
       return (!empty($id_data['del_id_arr']))|| (!empty($id_data['add_id_arr']))
       ?true:false;

    }
    private function create_temp_tb(){


        $sql = "   DROP TABLE IF EXISTS `a_id`;";
            $this->db->query($sql);
        $sql = " CREATE TABLE `a_id` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `num` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
    ";
        $this->db->query($sql);
        $sql = "   DROP TABLE IF EXISTS `a_tid`;";
        $this->db->query($sql);
        $sql = " CREATE TABLE `a_tid` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `num` int(11) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $this->db->query($sql);
    }

    private function get_id_info($tb, $type, $start_date, $end_date){

         $sql = "TRUNCATE a_id;";
        $this->db->query($sql);
        $sql = "TRUNCATE a_tid;";
         $this->db->query($sql);

        $lof_tb = ($type==0)?'oms_sell_record_lof':'b2b_lof_datail';
        $sql = " insert IGNORE into a_id (id)
        select l.id from {$lof_tb} l
        where   l.order_date>='{$start_date} 0:00:00' AND  l.order_date<='{$end_date} 0:00:00'  AND l.occupy_type>1 ";
        $this->db->query($sql);

        $sql = " insert IGNORE into a_tid (id)
        select  tbl_id from {$tb} where  ";

        $sql.=($type==0)?" order_type='sell_record' OR order_type='sell_return'"
                :
                " order_type<>'sell_record' AND order_type<>'sell_return'";
        $this->db->query($sql);

        $sql = "select a.id from a_id a
        LEFT JOIN a_tid  b on a.id=b.id
        where b.id is null OR abs(a.id)<>abs(b.num)";
        $data = $this->db->get_all($sql);
        $add_id_arr = array();
        if(!empty($data)){
            foreach($data as $val){
                 $add_id_arr[] = $val['id'];
            }

        }
          $del_id_arr = array();
        $sql ="select a.id from  a_tid  a
        LEFT JOIN a_id b on a.id=b.id
        where b.id is null OR abs(a.id)<>abs(b.num)";
        $data = $this->db->get_all($sql);

        if(!empty($data)){
             foreach($data as $val){
                 $del_id_arr[] = $val['id'];
            }
        }

        return array(
            'add_id_arr'=>$add_id_arr,
            'del_id_arr'=>$del_id_arr,
        );

    }


    /**
     * 汇总统计
     * @param $filter
     * @return array
     */
    function report_count($filter) {
        $count_all = array();
        $order_type_arr = array('adjust', 'purchase', 'pur_return', 'shift_out', 'shift_in', 'wbm_return', 'wbm_store_out', 'sell_record', 'sell_return', 'qc', 'qc_je', 'storage_in_num', 'storage_out_num', 'qm', 'qm_je');

        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        $order_date_start = $this->get_cur_month_first_day(date('Y-m-d'));
        $order_date_end = date('Y-m-d');
        if (empty($filter['order_date_start']) && $filter['order_date_end']) {
            $start_date = $order_date_start;
            $end_date = $filter['order_date_end'];
        } elseif (empty($filter['order_date_end']) && $filter['order_date_start']) {
            $start_date = $filter['order_date_start'];
            $end_date = $order_date_end;
        } elseif (empty($filter['order_date_end']) && empty($filter['order_date_start'])) {
            $start_date = $order_date_start;
            $end_date = $order_date_end;
        } else {
            $start_date = $filter['order_date_start'];
            $end_date = $filter['order_date_end'];
        }

        $price_type = empty($filter['price_type']) ? 'sell_price' : $filter['price_type']; //价格

        $wh = $this->get_search_where($filter);
        $end_month = date('Y-m', strtotime($end_date));
        $sql_num = " SELECT count(1) from( SELECT  1 from jxc_info i left join base_goods bs on bs.goods_code=i.goods_code where i.ymonth<='{$end_month}'  {$wh}  group by i.store_code,i.sku) as tb";

        if ($filter['is_have_change'] == 1) { //有库存变化
            $sql_select = " from goods_inv i "
                . "inner join base_goods bs on bs.goods_code=i.goods_code "
                . "left join b2b_lof_datail d ON i.sku=d.sku AND i.store_code=d.store_code  AND d.occupy_type>1 AND d.order_date>='{$start_date}' AND  d.order_date<='{$end_date}' "
                . "left join oms_sell_record_lof l ON i.sku=l.sku AND i.store_code=l.store_code  AND l.occupy_type>1 AND l.order_date>='{$start_date}' AND l.order_date<='{$end_date}'  "
                . " where 1  AND (d.sku is not null OR l.sku is not null  )   {$wh}  group by i.store_code,i.sku";
            $sql_num = " SELECT count(1) " . $sql_select;
        } else if ($filter['is_have_change'] == 2) {//无库存变化
            $sql_select = " from goods_inv i "
                . "inner join base_goods bs on bs.goods_code=i.goods_code "
                . "left join b2b_lof_datail d ON i.sku=d.sku AND i.store_code=d.store_code  AND d.occupy_type>1 AND d.order_date>='{$start_date}' AND  d.order_date<='{$end_date}' "
                . "left join oms_sell_record_lof l ON i.sku=l.sku AND i.store_code=l.store_code  AND l.occupy_type>1 AND l.order_date>='{$start_date}' AND l.order_date<='{$end_date}'  "
                . " where 1 AND (d.sku is null AND l.sku is null  )    {$wh}  group by i.store_code,i.sku";
            $sql_num = " SELECT count(1) " . $sql_select;
        }

        $record_count = $this->db->get_value($sql_num);
        //无记录
        if ($record_count == 0) {
            foreach ($order_type_arr as $count_type) {
                $type_key = $count_type . '_all';
                $count_all[$type_key] = 0;
            }
            return $count_all;
        }

        //去掉此部分，有库存变化的
        //无库存变化　通过，jxc_info　连接　b2b ,oms lof 两个表 加入时间条件
        if ($filter['is_have_change'] == 0) {
            $sql_select = " SELECT  i.store_code,i.sku from jxc_info i left join base_goods bs on bs.goods_code=i.goods_code where i.ymonth<='{$end_month}'   {$wh}  group by i.store_code,i.sku";
        } else {
            $sql_select = " SELECT  i.store_code,i.sku " . $sql_select;
        }

        $data = CTX()->db->get_all($sql_select);

        $default_info = array();
        foreach ($order_type_arr as $defalut_k) {
            $default_info[$defalut_k] = 0;
        }

        $sql = "select store_code,store_name from base_store";
        $db_store = ctx()->db->get_all($sql);
        $store_arr = load_model('util/ViewUtilModel')->get_map_arr($db_store, 'store_code', 0, 'store_name');

        $this->select_data = array();
        $condition = array();

        foreach ($data as $sub_arr) {
            $_ks = "{$sub_arr['store_code']},{$sub_arr['sku']}";
            $key_arr = array('goods_code', 'goods_name', 'barcode', 'spec1_name', 'spec2_name', 'cost_price', 'price', 'purchase_price', 'sell_price');

            $sku_info = load_model('goods/SkuCModel')->get_sku_info($sub_arr['sku'], $key_arr);
            $sku_info['price'] = isset($sku_info[$price_type]) ? $sku_info[$price_type] : $sku_info['price'];
            $sub_arr['store_name'] = $store_arr[$sub_arr['store_code']];

            $sub_arr = array_merge($sub_arr, $default_info);
            $this->select_data[$_ks] = array_merge($sub_arr, $sku_info);

            $condition[] = " store_code='{$sub_arr['store_code']}' AND sku='{$sub_arr['sku']}' ";
        }
        if (!empty($condition)) {
            $this->select_condition = " ( (" . implode(") OR (", $condition) . ") )";
        } else {
            $this->select_condition = " 1=1 ";
        }
        //获取开始时间月份的期初库存，期初金额
        $this->get_qc_data($start_date);
        //获取开始时间所在月份的数据
        $this->get_start_time_data($start_date, $end_date);
        //获取中间月份的数据
        $this->get_month_data($start_date, $end_date);
        //获取结束时间所在月份的数据
        $this->get_end_time_data($start_date, $end_date);

        $out_arr = array('pur_return', 'shift_out', 'wbm_store_out', 'sell_record');
        $in_arr = array('purchase', 'shift_in', 'wbm_return', 'sell_return');
        foreach ($this->select_data as &$val) {
            $val['qm'] += $val['qc'];//期末数
            foreach ($out_arr as $tou_k) {
                $val[$tou_k] = -abs($val[$tou_k]);
                $val['qm'] += $val[$tou_k];
                $val['storage_out_num'] += abs($val[$tou_k]);//出库总数
            }
            foreach ($in_arr as $in_k) {
                $val['qm'] += abs($val[$in_k]);
                $val['storage_in_num'] += abs($val[$in_k]);//入库总数
            }
            $val['qm'] += $val['adjust'];
            $val['qm_je'] = $val['qm'] * $val['price'];//期末金额
        }

        foreach ($order_type_arr as $count_type) {
            $type_key = $count_type . '_all';
            $num = array_sum(array_column($this->select_data, $count_type));
            $count_all[$type_key] = $num;
        }

        return $count_all;
    }

}
