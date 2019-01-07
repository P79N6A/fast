<?php
require_model('tb/TbModel');
/**
 * 日志清除类
 */
class LogCleanUpLogModel extends TbModel
{
    /** 表名 */
    protected $table = 'sys_log_clean_up_log';
    
    /**
     * 获取最近的删除记录
     */
    public function lastchanged()
    {
        $sql = 'SELECT `type`, max(`lastchanged`) AS `lastchanged` '
             . 'FROM `sys_log_clean_up_log` '
             . 'GROUP BY `type`';
        $data = $this->db->get_all($sql);
        /** 转化成['type' => 'lastchanged'(int)]的形式 */
        $return = array();
        while($pop = array_pop($data)) {
            $return[$pop['type']] = strtotime($pop['lastchanged']);
        }
        return $return;
    }
    
    /**
     * 清除日志记录
     * @param string $type
     * @param string $status
     */
    public function insertLog($type, $status, $remark)
    {
        $this->insert(array(
            'type'        => $type,
            'status'      => $status,
            'remark'      => $remark,
            'lastchanged' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])
        ));
    }
}

