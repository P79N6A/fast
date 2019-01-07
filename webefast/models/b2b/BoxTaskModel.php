<?php

require_model('tb/TbModel');
require_lang('stm');

/**
 * 装箱任务单相关业务
 */
class BoxTaskModel extends TbModel {

    function get_table() {
        return 'b2b_box_task';
    }

    /**
     * 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $filter[$filter['keyword_type']] = $filter['keyword'];
        }
        $sql_join = ' INNER JOIN wbm_store_out_record AS sr ON bt.relation_code=sr.record_code';
        $sql_main = "FROM {$this->table} AS bt {$sql_join} WHERE 1";
        $select = 'bt.*,sr.distributor_code';
        $sql_values = array();

        //商店仓库权限
        $filter_store_code = isset($filter['store_code']) ? $filter['store_code'] : null;
        $sql_main .= load_model('base/StoreModel')->get_sql_purview_store('bt.store_code', $filter_store_code);
        //分销商
        $filter_custom_code = isset($filter['distributor_code']) ? $filter['distributor_code'] : null;
        $sql_main .= load_model('base/CustomModel')->get_sql_purview_custom('sr.distributor_code', $filter_custom_code);
        //装箱任务号
        if (isset($filter['task_code']) && $filter['task_code'] != '') {
            $sql_main .= " AND (bt.task_code LIKE :task_code )";
            $sql_values[':task_code'] = "%{$filter['task_code']}%";
        }
        //店铺
        if (isset($filter['store_code']) && $filter['store_code'] <> '') {
            $arr = explode(',', $filter['store_code']);
            $str = $this->arr_to_in_sql_value($arr, 'store_code', $sql_values);
            $sql_main .= " AND bt.store_code in ({$str}) ";
        }
        //关联单号
        if (isset($filter['relation_code']) && $filter['relation_code'] != '') {
            $sql_main .= " AND (bt.relation_code LIKE :relation_code )";
            $sql_values[':relation_code'] = "%{$filter['relation_code']}%";
        }
        //创建人
        if (isset($filter['create_user']) && $filter['create_user'] != '') {
            $sql_main .= " AND (bt.create_user LIKE :create_user )";
            $sql_values[':create_user'] = "%{$filter['create_user']}%";
        }
        //验收
        if (isset($filter['is_check_and_accept']) && $filter['is_check_and_accept'] != '' && $filter['is_check_and_accept'] != 'all') {
            $sql_main .= " AND (bt.is_check_and_accept = :is_check_and_accept )";
            $sql_values[':is_check_and_accept'] = $filter['is_check_and_accept'];
        }
        //转换
        if (isset($filter['is_change']) && $filter['is_change'] != '' && $filter['is_change'] != 'all') {
            $sql_main .= " AND (bt.is_change = :is_change )";
            $sql_values[':is_change'] = $filter['is_change'];
        }
        //业务日期
        if (isset($filter['record_time_start']) && $filter['record_time_start'] != '') {
            $sql_main .= " AND (bt.record_time >= :record_time_start )";
            $sql_values[':record_time_start'] = $filter['record_time_start'];
        }
        if (isset($filter['record_time_end']) && $filter['record_time_end'] != '') {
            $sql_main .= " AND (bt.record_time <= :record_time_end )";
            $sql_values[':record_time_end'] = $filter['record_time_end'];
        }
        //下单日期
        if (isset($filter['create_time_start']) && $filter['create_time_start'] != '') {
            $sql_main .= " AND (bt.create_time >= :create_time_start )";
            $sql_values[':create_time_start'] = $filter['create_time_start'] . " 00:00:00";
        }
        if (isset($filter['create_time_end']) && $filter['create_time_end'] != '') {
            $sql_main .= " AND (bt.create_time <= :create_time_end )";
            $sql_values[':create_time_end'] = $filter['create_time_end'] . " 23:59:59";
        }

        $sql_main .= "  ORDER BY create_time DESC";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        foreach ($data['data'] as $key => $value) {
            $sql = "select count(*) as cnt from b2b_box_record where task_code = :task_code";
            $data1 = $this->db->get_row($sql, array(":task_code" => $value['task_code']));
            $data['data'][$key]['x_count'] = $data1['cnt'];
        }
        filter_fk_name($data['data'], array('store_code|store', 'distributor_code|custom', 'record_type_code|record_type'));

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    //状态改变
    function update_sure($active, $field, $id) {

        if (!in_array($active, array(0, 1))) {
            return $this->format_ret('-1', '', 'ERROR_PARAMS');
        }
        $ret = parent:: update(array($field => $active), array('id' => $id));
        return $ret;
    }
    //判断验收条件
    function ys_box($request) {
        $task_code = $request['task_code'];
        $record_code = $request['record_code'];
        $this->begin_trans();
        require_model('sys/RecordScanBoxModel');
        $obj = new RecordScanBoxModel($request['dj_type']);
        //是否存在未完成扫描的装箱单
        $sql = "select record_code from b2b_box_record where task_code = :task_code and is_check_and_accept = 0";
        $box_code = ctx()->db->getOne($sql, array(':task_code' => $task_code));
        if (!empty($box_code)) {
            //验收装箱单
            $ret = $obj->b2b_box_record_ys($task_code, $box_code);
            if ($ret['status'] != 1) {
                $this->rollback();
                return $ret;
            }
        }
        //验收装箱任务单
        $ret = $obj->b2b_box_task_ys($task_code);
        if ($ret['status'] != 1) {
            $this->rollback();
            return $ret;
        }
        //验收批发销货单
//        $is_scan_tag = 1;
//        $ret = load_model('wbm/StoreOutRecordModel')->do_sure_and_shift_out($record_code, $is_scan_tag);
//        if ($ret['status'] != 1) {
//               $this->rollback();
//            return $ret;
//        }
        $this->commit();
        return $ret;
    }

    /**
     * 修改明细数量，回写装箱单主单据
     * @param string $task_code 装箱任务号
     * @return array 回写结果
     */
    public function mainWriteBack($task_code) {
        //回写数量和金额
        $sql = "UPDATE b2b_box_task AS bt SET
                  bt.num = (SELECT SUM(br.num) FROM b2b_box_record AS br WHERE br.task_code=:task_code)
                WHERE bt.task_code=:task_code";
        $res = $this->query($sql, array(':task_code' => $task_code));
        return $res;
    }

}
