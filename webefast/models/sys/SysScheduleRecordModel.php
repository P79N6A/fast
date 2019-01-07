<?php
/**
 *自动服务执行时间记录
 *
 * @author wq
 */
require_model('tb/TbModel');
class SysScheduleRecordModel extends TbModel {
    public function __construct($table = '', $db = '') {
        $table = $this->get_table();
        parent::__construct($table);
    }

    function get_table() {
        return 'sys_schedule_record';
    }

     function get_record($code){
        $ret =  $this->get_row(array('type_code'=>$code));
        if(empty($ret['data'])){
            $this->insert(array('type_code'=>$code));
            $ret =  $this->get_row(array('type_code'=>$code));
        }
        return $ret;
     }
     function insert($data) {
         parent::insert($data);
     }
     function update_code($data, $code) {
        $data['exec_time'] = isset($data['exec_time'])?$data['exec_time']:time();
        return  parent::update($data, array('type_code'=>$code));
     }

}
