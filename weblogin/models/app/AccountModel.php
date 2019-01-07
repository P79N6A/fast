<?php

/**
 * @author FBB
 */
require_lib('keylock_util');
require_model('tb/TbModel');

class AccountModel extends TbModel {

    private $user_table = 'sys_user';
    private $role_table = 'sys_user_role';
    private $new_db;
    private $kh_id = '2257';
            
    function __construct($table = '', $pk = '', $db = '') {
        parent::__construct($table, $pk, $db);
        $this->new_db = $this->create_rds_db($this->kh_id);
    }


    /**
     * @todo 根据action不同对账户进行不同的设置
     */
    function set_account($params) {
        //对应申请体验账号
        if ($params['action'] == 'create') {
            return $this->create_experience_account($params['mobile_num']);
        }
        //对应重置密码
        if ($params['action'] == 'change') {
            return $this->change_pwd($params['mobile_num']);
        }
    }

    /**
     * @todo 创建体验账号
     * @param varchar $mobile 申请的手机号码
     */
    function create_experience_account($mobile_num) {
        if (empty($mobile_num)) {
            return $this->format_ret(-1, '', '手机号码不可为空！');
        }
        $re = $this->is_exists($mobile_num);
        if($re['status'] == -1){
          return $re;
        }
        //创建账户时因为需要重置密码，所以密码写固定
        $password = 'baota666';
        $encode_pwd = $this->encode_pwd($password);
        $create_time = date('Y-m-d H:i:s', time());
        $this->begin_trans();
        //创建用户
        $data = array('user_code' => $mobile_num, 'user_name' => '演示账号', 'password' => $encode_pwd, 'phone' => $mobile_num, 'is_strong' => '2', 'create_time' => $create_time);
        $ret = $this->new_db->insert($this->user_table, $data);

        if ($ret !== TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建体验账号失败，请重新验证！');
        }
        //绑定角色
        $user_data = $this->get_user_info($mobile_num);
        $role_data = array('role_id' => 4, 'user_id' => $user_data['user_id']);
        $res = $this->new_db->insert($this->role_table, $role_data);
        if ($res !== TRUE) {
            $this->rollback();
            return $this->format_ret(-1, '', '创建体验账号失败，请重新验证！');
        }
        $this->commit();
        //创建用户且绑定角色后发送短信,主要有公司名称、用户名称、密码
        $params['sms_template_code'] = 'SMS_11480416';
        $params['sms_param'] = json_encode(array('username' => $mobile_num, 'password' => $password));
        $params['rec_num'] = $mobile_num;
        $result = load_model('sys/EfastApiModel')->request_api('taobao_api/sms_send', $params);
        if ($result['resp_data']['code'] == 0) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1);
        }
    }

    /**
     * @todo 重置体验账号的密码
     * @param varchar $mobile_num 申请的手机号码
     */
    function change_pwd($mobile_num) {
        $data = $this->get_user_info($mobile_num);
        if (!empty($data)) {
            $pwd = $this->generatePassword();
            $encode_pwd = $this->encode_pwd($pwd);
            $sql = "UPDATE sys_user SET password='{$encode_pwd}' WHERE user_id='{$data['user_id']}'";
            $ret = $this->new_db->query($sql);
            if ($ret !== TRUE) {
                return $this->format_ret(-1, '', '重置密码失败，请重新验证！');
            }
            //重置密码成功后发送短信。主要有公司名称、用户名称、新密码密码
            $params['sms_template_code'] = 'SMS_11520526';
            $params['sms_param'] = json_encode(array('username' => $mobile_num, 'password' => $pwd));
            $params['rec_num'] = $mobile_num;
            $result = load_model('sys/EfastApiModel')->request_api('taobao_api/sms_send', $params);
            if ($result['resp_data']['code'] == 0) {
                return $this->format_ret(1);
            } else {
                return $this->format_ret(-1);
            }
        } else {
            return $this->format_ret(-1, '', '无此账号！');
        }
    }

    /**
     * @todo 获取体验账号的基本信息
     * @param varchar $mobile_num 申请的手机号码
     */
    function get_user_info($mobile_num) {
        $sql = "SELECT user_id,user_code FROM $this->user_table WHERE user_code=:user_code";
        $sql_value = array(':user_code' => $mobile_num);
        $data = $this->new_db->get_row($sql, $sql_value);
        return $data;
    }

    /**
     * @todo 创建体验系统数据库对象
     */
    function create_rds_db($kh_id) {
        $rem_sql = "SELECT rem_db_name,rem_db_pid FROM osp_rdsextmanage_db WHERE rem_db_khid=:rem_db_khid";
        $rem_sql_value = array(":rem_db_khid" => $kh_id);
        $res = $this->db->get_row($rem_sql, $rem_sql_value);
        $sql = "SELECT * FROM osp_aliyun_rds WHERE rds_id=:rds_id";
        $sql_value = array("rds_id" => $res['rem_db_pid']);
        $ret = $this->db->get_row($sql, $sql_value);
        $keylock = get_keylock_string($ret['rds_createdate']);
        $ret['rds_pass'] = create_aes_decrypt($ret['rds_pass'], $keylock);
        $config = array(
            'name' => $res['rem_db_name'],
            'user' => $ret['rds_user'],
            'pwd' => $ret['rds_pass'],
            'host' => $ret['rds_link'],
            'type' => 'mysql'
        );
        return create_db($config);
    }

    /**
     * @todo 验证是否是百胜内部手机号码
     */
    function is_auth_mobile($mobile_num) {
        $sql = 'SELECT user_name FROM osp_auth_user WHERE phone=:phone';
        $sql_value = array(':phone' => $mobile_num);
        $ret = $this->db->get_row($sql, $sql_value);
        if (empty($ret)) {
            return $this->format_ret(-1, '', '此手机号码没有操作的权限');
        } else {
            return $this->format_ret(1);
        }
    }

    /**
     * @todo 获取百胜员工手机号码
     */
    function get_baison_info() {
        $soapClient = new SoapClient("http://218.242.249.183/DMSWF.SupportService/SDMService.asmx?WSDL");
        //获取数据
        $xml = $soapClient->GetUserInfo()->GetUserInfoResult;
        $obj = simplexml_load_string($xml);
        $json = json_encode($obj);
        $user_data = json_decode($json, true);
        $data = array();
        foreach ($user_data['DataRow']['UserInfo'] as $key => $value) {
            $data[$key]['user_name'] = $value['Name'];
            $data[$key]['phone'] = !is_array($value['Mobile']) ? $value['Mobile'] : '';
            $data[$key]['email'] = $value['Email'];
            $data[$key]['department'] = $value['Department'];
            $data[$key]['create_time'] = date('Y-m-d H:i:s', time());
        }
        $update_str = " phone = VALUES(phone), email = VALUES(email) ";
        $ret = $this->insert_multi_duplicate('osp_auth_user', $data, $update_str);
        return $this->format_ret($ret);
    }

    /**
     * @todo 判断手机号码是否存在
     */
    function is_exists($mobile_num) {
        $user_exits = $this->get_user_info($mobile_num);
        if (!empty($user_exits)) {
            return $this->format_ret(-1, '', '此手机号码已存在演示账号！');
        }
    }

    /**
     * @todo 生成强密码
     */
    function generatePassword($length = 8) {
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'), array('!', '@', '$', '%', '^', '&', '*'));
        shuffle($chars);
        $password = '';
        for ($i = 0; $i < 8; $i++) {
            $password .= $chars[$i];
        }
        if (preg_match("/(?=^.{8,}$)(?=.*\d)(?=.*\W+)(?=.*[A-Z])(?=.*[a-z])(?!.*\n).*$/", $password) == true) {
            return $password;
        }
        return $this->generatePassword($length);
    }

    /**
     * @todo 密码加密
     */
    function encode_pwd($pwd) {
        return md5(md5($pwd) . $pwd);
    }

}
