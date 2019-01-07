<?php

require_model('tb/TbModel');

/**
 * 公共接口配置类
 * @author WMH
 */
class ApiServiceConfigModel extends TbModel {

    protected $table = 'api_service';

    public function get_service($service_code, $method_type = 0) {
        $sql = "SELECT * FROM {$this->table} WHERE service_status=1 AND service_code=:service_code AND method_type=:method_type";
        $service = $this->db->get_row($sql, [':service_code' => $service_code, ':method_type' => $method_type]);
        return $service;
    }

    public function get_service_by_archives($param) {
        $sql = 'SELECT service_id FROM api_service_relate_archives WHERE archives_type=:archives_type AND archives_code=:archives_code';
        $service_id = $this->db->get_value($sql, [':archives_type' => $param['archives_type'], ':archives_code' => $param['archives_code']]);
        if (empty($service_id)) {
            return $this->format_ret(10, '', '该订单仓库未配置接口参数');
        }

        $sql = "SELECT * FROM {$this->table} WHERE service_id=:service_id";
        $sql_values = [':service_id' => $service_id,];
        $service = $this->db->get_row($sql, $sql_values);
        if (empty($service)) {
            return $this->format_ret(-1, '', '接口配置异常');
        }
        return $this->format_ret(1, $service);
    }

}
