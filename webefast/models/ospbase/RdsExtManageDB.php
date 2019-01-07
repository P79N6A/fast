<?php
/**
 * 已绑定数据库的用户
 * 对应运营中心osp_rdsextmanage_db 表
 */
require_model('tb/TbModel');
class RdsExtManageDB extends TbModel{
    function get_table() {
        return 'osp_rdsextmanage_db';
    }

    /**
     * 根据rem_db_khid获取已绑定客户对应的数据库名称 以及对应的rds 编号rem_db_pid，该编号对应osp_aliyun_rds 的主键rds_id
     * @param $kh_id
     * @return array|bool|int|string
     * @throws Exception
     */
    function get_dbname_dbid($rem_db_khid){
        $sql = "
        SELECT
            rem_db_pid,
            rem_db_name
        FROM
            osp_rdsextmanage_db
        WHERE
	        rem_db_khid =:rem_db_khid";
        $sql_value[':rem_db_khid'] = $rem_db_khid;
        $data = $this->db->get_all($sql,$sql_value);
        if (isset($data)) {
            return $data[0];
        } else {
            return "";
        }
    }
}