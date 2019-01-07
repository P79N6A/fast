<?php

require_model('tb/TbModel');
require_model('api/apitpl/ArchivesApiModel');

/**
 * 供应商档案接口类
 * @author WMH
 */
class BrandApiModel extends TbModel implements ArchivesApiModel {

    protected $table = 'base_brand';

    public function api_archives_create($param) {

    }
    public function api_archives_get($param){
        $key_option = array(
            's' => array('page', 'page_size','start_lastchanged','end_lastchanged','brand_code','brand_name')
        );
        $arr_option = array();
        $ret_option = valid_assign_array($param, $key_option, $arr_option);
        unset($param);

        if (isset($arr_option['page_size']) && $arr_option['page_size'] > 100) {
            return $this->format_ret('-1', array('page_size' => $arr_option['page_size']), 'API_RETURN_MESSAGE_PAGE_SIZE_TOO_LARGE');
        }

        $select = 'brand_code,brand_name,remark,lastchanged';
        $sql_values = array();
        $sql_main = "FROM {$this->table} where 1=1 ";
        $this->get_record_sql_where($arr_option, $sql_main, $sql_values);
        $ret = $this->get_page_from_sql($arr_option, $sql_main, $sql_values, $select);
        $data = $ret['data'];
        if (empty($data)) {
            return $this->format_ret(-10002, (object) array(), 'API_RETURN_MESSAGE_10002');
        }
        $filter = get_array_vars($ret['filter'], array('page', 'page_size', 'page_count', 'record_count'));
        $revert_data = array(
            'filter' => $filter,
            'data' => $data,
        );
        return $this->format_ret(1, $revert_data);
    }

    public function api_archives_update($param) {

    }
    /**
     * 生成单据查询sql条件语句
     * @param array $filter 参数条件
     * @param string $sql_main sql主体
     * @param string $sql_values sql映射值
     * @param string $ab 表别名
     */
    private function get_record_sql_where($filter, &$sql_main, &$sql_values, $ab = '') {
        foreach ($filter as $key => $val) {
            if (in_array($key, array('page', 'page_size')) || $val === '' || $val === NULL) {
                continue;
            }
            if ($key == 'start_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged>=:{$key}";
            } else if ($key == 'end_lastchanged') {
                $sql_main .= " AND {$ab}lastchanged<=:{$key}";
            } else {
                $sql_main .= " AND {$ab}{$key}=:{$key}";
            }
            $sql_values[":{$key}"] = $val;
        }

        if (!isset($filter['start_lastchanged'])) {
            $start_time = date("Y-m-d H:i:s", strtotime("today"));
            $sql_main .= " AND {$ab}lastchanged >= :start_lastchanged";
            $sql_values[':start_lastchanged'] = $start_time;
        }
        if (!isset($filter['end_lastchanged'])) {
            $end_time = date("Y-m-d H:i:s", strtotime("today +1 days"));
            $sql_main .= " AND {$ab}lastchanged <= :end_lastchanged";
            $sql_values[':end_lastchanged'] = $end_time;
        }
    }
}