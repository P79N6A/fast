<?php
/**
 * Created by PhpStorm.
 * User: BS
 * Date: 2018/1/31
 * Time: 16:36
 */
require_lib('util/oms_util', true);
require_model('tb/TbModel');
class WmsExpressConfigModel extends TbModel {
    private $keys = array('wms_config_id','express_code','out_express_code','desc');
    private $column_name = array(
        'wms_config_id'=>'wms配置名称',
        'express_code'=>'系统配送方式代码',
        'out_express_code'=>'wms配送方式代码',
        'desc'=>'描述'
    );
    public function __construct($table = '', $pk = '')
    {
        $table = $this->get_table();
        parent::__construct($table);
    }
    public function get_table()
    {
        return 'wms_express_config';
    }
    public function get_by_page($filter)
    {
        $sql_values = array();
        $sql_main = 'from '.$this->table.' as wec inner join wms_config wc on wec.wms_config_id = wc.wms_config_id where 1=1 ';
        if(isset($filter['wms_system_code']) && $filter['wms_system_code'] != '' && strpos('all',$filter['wms_system_code']) === false){
            $wms_config_id = $this->arr_to_in_sql_value(explode(',',$filter['wms_system_code']),'wms_system_code',$sql_values);
            $sql_main .= ' and wec.wms_config_id in ('.$wms_config_id.')';
        }
        if(isset($filter['express_code']) && $filter['express_code'] != '' && strpos('all',$filter['express_code']) === false){
            $express_code = $this->arr_to_in_sql_value(explode(',',$filter['express_code']),'express_code',$sql_values);
            $sql_main .= ' and express_code in ('.$express_code.')';
        }
        if(isset($filter['out_express_code']) && $filter['out_express_code'] != ''){
            $sql_values[':out_express_code'] = $filter['out_express_code'];
            $sql_main .= ' and out_express_code = :out_express_code ';
        }
        $select = 'wec.lastchanged,wec.wms_id,wec.wms_config_id,wec.express_code,wec.out_express_code,wc.wms_config_name';
        $data = $this->get_page_from_sql($filter,$sql_main,$sql_values,$select);
        foreach($data['data'] as &$val){
            $_url = base64_encode('?app_act=sys/wms_express_config/show_log&_id='.$val['wms_id']);
            $url = '?app_act=sys/wms_express_config/show_log&_id='.$val['wms_id'];
            $u = "javascript:openPage('{$_url}', '{$url}', '日志')";
            $val['log'] = '<a style="cursor:pointer" onclick="'.$u.'">查看</a>';
            $val['express_name'] = oms_tb_val('base_express', 'express_name', array('express_code'=>$val['express_code']));
            $val['express_name'] = $val['express_name'].'('.$val['express_code'].')';

        }
        return $this->format_ret(1,$data);
    }
    public function add($data){
        $data['desc'] = htmlspecialchars_decode(trim($data['desc']));
        $values = array();
        foreach ($this->keys as $key){
            $values[$key] = isset($data[$key]) && $data[$key] != '' ? trim($data[$key]) : '';
        }
        //检验参数
        $check_ret = $this->check($values);
        if($check_ret['status'] < 1){
            return $check_ret;
        }
        $sql_values = array(
            ':wms_config_id'=>$values['wms_config_id'],
            ':out_express_code'=>$values['out_express_code']
        );
        $sql = 'select count(*) total from wms_express_config 
                where wms_config_id = :wms_config_id
                and out_express_code = :out_express_code ';
        $total = $this->db->get_value($sql,$sql_values);
        if($total < 1){
            $values['lastchanged'] = date('Y-m-d H:i:s');
            $ret = $this->insert($values);
            if($ret['status'] == 1){
                //记录日志
                $log_arr = array(
                    'wms_id' => $ret['data']['id']
                );
                $this->add_action($log_arr,'添加配置','添加配置');
                return $this->format_ret(1,'','添加成功');
            }else{
                return $this->format_ret(-1,'','添加失败');
            }
        }else{
            return $this->format_ret(-1,'','wms配送方式代码重复添加');
        }
    }
    public function edit($data){
        $wms_id = isset($data['wms_id']) ? $data['wms_id'] : '';
        if($wms_id <= 0) return $this->format_ret(-1,'','编辑失败');
        $old_data = $this->get_record_by_id($wms_id);
        $values = array();
        foreach ($this->keys as $key){
            $values[$key] = isset($data[$key]) && $data[$key] != '' ? $data[$key] : '';
        }
        $check_ret = $this->check($values);
        if($check_ret['status'] < 1){
            return $check_ret;
        }
        $log_str = '';
        foreach ($values as $key=>$val){
            if($val != $old_data['data'][$key]){
                if($key == 'wms_config_id'){
                    $wms_config_arr = array($old_data['data'][$key],$val);
                    $wms_val = array();
                    $wms_config_str = $this->arr_to_in_sql_value($wms_config_arr,'wms_config_id',$wms_val);
                    $wms_sql =  'select wms_config_id,wms_config_name from wms_config where wms_config_id in('.$wms_config_str.')';
                    $wms_ret = $this->db->get_all($wms_sql,$wms_val);
                    $wms_ret = load_model('util/ViewUtilModel')->get_map_arr($wms_ret, 'wms_config_id');
                    $log_str .= $this->column_name[$key].'：由'.$wms_ret[$old_data['data'][$key]]['wms_config_name'].'改为'.$wms_ret[$val]['wms_config_name'].'<br/>';
                }else{
                    $log_str .= $this->column_name[$key].'：由'.$old_data['data'][$key].'改为'.$val.'<br/>';
                }
            }
        }
        $sql_values = array(
            ':wms_config_id'=>$values['wms_config_id'],
            ':out_express_code'=>$values['out_express_code'],
            ':wms_id'=>$wms_id
        );
        $sql = 'select count(*) total from wms_express_config 
                where wms_config_id =:wms_config_id 
                and out_express_code = :out_express_code
                and wms_id != :wms_id ';
        $total = $this->db->get_value($sql,$sql_values);
        if($total < 1){
            $values['lastchanged'] = date('Y-m-d H:i:s');
            $ret = $this->update($values,array('wms_id'=>$wms_id));
            if($ret['status'] == 1){
                //记录日志
                $log_arr = array(
                    'wms_id' => $wms_id
                );
                if($log_str != '') $this->add_action($log_arr,'编辑配置',$log_str);
                return $this->format_ret(1,'','编辑成功');
            }else{
                return $this->format_ret(-1,'','编辑失败');
            }
        }else{
            return $this->format_ret(-1,'','wms配送方式代码已经重复');
        }
    }
    public function check($data){
        $request = array('wms_config_id'=>'wms配置名称','express_code'=>'系统配送方式代码','out_express_code'=>'wms配送方式代码');
        foreach ($request as $key=>$val){
            if($data[$key] === ''){
                return $this->format_ret(-1,'',$val.'不能为空');
            }
        }
        //wms配置是否存在系统
        $config_sql = 'select count(*) total from wms_config where wms_config_id = :wms_config_id ';
        $config_total = $this->db->get_value($config_sql,array(':wms_config_id'=>$data['wms_config_id']));
        if($config_total < 1){
            return $this->format_ret(-1,'',$request['wms_config_id'].'不存在');
        }
        //系统配送方式和wms配送方式不能相同
        if($data['express_code'] == $data['out_express_code']){
            return $this->format_ret(-1,'',$this->column_name['express_code'].'和'.$this->column_name['out_express_code'].'相同，不能添加');
        }
        return $this->format_ret(1);
    }
    public function add_action($data, $action_name, $action_note = '',$type){
        $log['user_code'] = load_model('sys/UserTaskModel')->get_user_code();
        $log['user_name'] =  load_model('sys/UserTaskModel')->get_user_name();
        $log['user_code'] = !empty($log['user_code'])?$log['user_code']:'系统管理员';
        $log['user_name'] = !empty($log['user_name'])?$log['user_name']:'系统管理员';
        $log['wms_id'] = $data['wms_id'];
        $log['action_name'] = $action_name;
        $log['action_note'] = $action_note;
        $log['lastchanged'] = date('Y-m-d H:i:s');
        if($type == 'update'){
            $sql = 'select count(*) total from '.$this->table.' where wms_id = :wms_id ';
            $total = $this->db->get_value($sql,array(':wms_id'=>$data['wms_id']));
            if($total < 1){
                return $this->format_ret(-1,'','保存日志失败');
            }
        }
        $ret = $this->db->insert('wms_express_action',$log);
        return $ret ? $this->format_ret(1,'','保存日志成功') : $this->format_ret(-1,'','保存日志失败');
    }
    public function get_record_by_id($wms_id,$column = '*'){
        $sql = 'select '.$column.' from wms_express_config where wms_id=:wms_id';
        $data = $this->db->get_row($sql,array(':wms_id'=>$wms_id));
        return $this->format_ret(1,$data);
    }
}