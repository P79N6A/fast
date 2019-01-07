<?php

/**
 * @author FBB
 */
require_model('tb/TbModel');
require_lib('tool/Cache.class', false);

class MobileVerifyCodeModel extends TbModel {

    private $active_time = 300; //验证码有效时间
    private $cache_time = 3600; //缓存有效期，一小时
    private $pcache = '';

    /**
     * @todo 获取验证码，使用文件缓存保存验证码
     */
    function get_verify_code($mobile_num) {
        $is_match = preg_match('/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/', $mobile_num);

        if ($is_match == 0) {
            return $this->format_ret(-1, '', '这手机号码不太对哦~');
        }
        $code = $this->generate_code();

        $verify_data = '';
        //文件缓存
        if (empty($this->pcache)) {
            $this->pcache = new FileCache();
        }
        //获取文件缓存中的内容
        $data = $this->pcache->get($mobile_num);

        $values = array('mobile_num' => $mobile_num, 'verify_code' => $code, 'create_time' => time());

        if ($data === null) {
            $this->pcache->set($mobile_num, $values, $this->cache_time);
            $verify_data = $values;
        } else if ($data['create_time'] + $this->active_time < time()) {
            //当前时间超过上一次获取验证码一定时间后，擦除文件缓存中的内容，重新写入
            $this->pcache->delete($mobile_num);
            $this->pcache->set($mobile_num, $values, $this->cache_time);
            $verify_data = $values;
        } else {
            $verify_data = $data;
        }
        $params['sms_param'] = json_encode(array('msg' => $verify_data['verify_code']));
        $params['rec_num'] = $verify_data['mobile_num'];
        $result = load_model('sys/EfastApiModel')->request_api('taobao_api/sms_send', $params);
        if ($result['resp_data']['code'] == 0) {
            return $this->format_ret(1);
        } else {
            return $this->format_ret(-1, '', '获取验证码失败！');
        }
    }

    /**
     * @todo 校验验证码
     */
    function check_verify_code($params) {
        $is_match = preg_match('/(13\d|14[57]|15[^4,\D]|17[678]|18\d)\d{8}|170[059]\d{7}/', $params['mobile_num']);
        if ($is_match == 0) {
            return $this->format_ret(-1, '', '这手机号码不太对哦~');
        }
        if (empty($this->pcache)) {
            $this->pcache = new FileCache();
        }
        $data = $this->pcache->get($params['mobile_num']);
        if ($data === null) {
            //不存在改手机号码的缓存文件的情况
            return $this->format_ret(-1, '', '验证码错误!');
        }
        if ($data['create_time'] + $this->active_time < time() && $data['mobile_num'] == $params['mobile_num']) {
            //验证码超时的情况
            return $this->format_ret(-1, '', '验证码已过期!');
        }

        if ($data['verify_code'] == $params['verify_code'] && $data['mobile_num'] == $params['mobile_num']) {
            return $this->format_ret(1, '', '验证码正确!');
        } else {
            return $this->format_ret(-1, '', '验证码错误!');
        }
    }

    /**
     * @todo 获取随机验证码
     * @param int $length 验证码长度
     */
    function generate_code($length = 6) {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

}
