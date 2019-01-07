<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class ProductInformBackClient extends ApiClient implements ApiClientInterface {

    public $message_version = '1.0.0.1';
    public $version = '2.0.0';

    public $url = 'http://licensetrack.baison.com.cn:8488/BS/BSBackTrackV1.0/';//接口地址

    /**
     * @param string $api_config
     */
    public function __construct() {

    }


    /**
     * 客户订单信息回溯
     */
    function Order_information_traceback($outer_params) {
        $handles = array();
        $i = 0;
        foreach ($outer_params as $kh_id => $kh_params) {
            $params = array();
            $params['Version'] = $this->version;
            $MessageInfo = array();
            $MessageInfo['Version'] = $this->message_version;
            $MessageInfo['Randkey'] = date('YmdHis') . rand(00000, 99999) . $i;

            $Information = array();
            $Information['Action'] = '9004';
            //公共参数
            $ActionInfo = $this->set_information($kh_params['action']);

            //机器信息
            $ComputerPara = array();
            $ComputerPara['IpAddr'] = '';
            $ComputerPara['MacAddr'] = '';
            $ComputerPara['HostName'] = '';
            $ActionInfo['ComputerPara'] = $ComputerPara;

            //订单信息
            $OrderInfoPara = array();
            $info_id_arr = array();
            foreach ($kh_params['order'] as $order) {
                $info = array_values($order['Info']);
                $OrderInfoPara[] = array(
                    'Date' => str_replace('-', '', $order['Date']),
                    'Platform' => $order['Platform'],
                    'App_nick' => '',
                    'Info' => $info,
                );
                //获取中间的id
                $info_id_arr[] = array_keys($order['Info']);
            }
            $ActionInfo['OrderInfoPara'] = $OrderInfoPara;

            $Information['ActionInfo'] = $ActionInfo;
            $MessageInfo['Information'] = $Information;
            $MessageInfo['Token'] = '';
            $MessageInfo['Ak'] = '';
            $MessageInfo['Sk'] = '';
            $MessageInfo_json=$this->json_encode_ex($MessageInfo);
            $params['MessageInfo'] = $this->encode_string($MessageInfo_json);
            $key_arr = array();
            foreach ($info_id_arr as $id_arr) {
                foreach ($id_arr as $id) {
                    $key_arr[] = $id;
                }
            }
            $key = implode(',', $key_arr);
            $handles[$key] = $this->newHandle('BSBackTrack_Interface', $params);
            $i++;
        }
        //批量调用接口,分页
        $page = array_chunk($handles, 10, true);
        $error_id = array();
        foreach ($page as $page_no) {
            $result = $this->multiExec($page_no);
            foreach ($result as $id_str => $value) {
                if ($value != 0) {
                    $error_id[] = $id_str;
                }
            }
        }
        $ret = array('status' => '1', 'data' => '', 'message' => '回溯成功！');
        if (!empty($error_id)) {
            $error_id_str = implode(',', $error_id);
            $ret = array('status' => '-1', 'data' => $error_id_str, 'message' => '回溯异常！');
        }
        return $ret;
    }


    /**
     *登录信息回溯
     * @param $outer_params
     */
    function login_information_traceback($outer_params) {
        $handles = array();
        $i = 0;
        foreach ($outer_params as $kh_id => $kh_params) {
            $params = array();
            $params['Version'] = $this->version;
            $MessageInfo = array();
            $MessageInfo['Version'] = $this->message_version;


            $Information = array();
            $Information['Action'] = '9001';
            //公共参数
            $ActionInfo = $this->set_information($kh_params['action']);
            //客户登录信息
            $login_params = $kh_params['login'];
            foreach ($login_params as $login) {
                $MessageInfo['Randkey'] = date('YmdHis') . rand(00000, 99999) . $i;

                //机器信息
                $ComputerPara = array();
                $ComputerPara['IpAddr'] = isset($login['IpAddr']) ? $login['IpAddr'] : '';
                $ComputerPara['MacAddr'] = isset($login['MacAddr']) ? $login['MacAddr'] : '';
                $ComputerPara['HostName'] = isset($login['HostName']) ? $login['HostName'] : '';
                $ActionInfo['ComputerPara'] = $ComputerPara;

                //登录信息
                $LoginInfoPara = array();
                $LoginInfoPara['LoginQD'] = $login['LoginQD'];
                $LoginInfoPara['LoginDM'] = $login['LoginDM'];
                $LoginInfoPara['LoginName'] = $login['LoginName'];
                $LoginInfoPara['LoginCompanyName'] = $login['LoginCompanyName'];
                $ActionInfo['LoginInfoPara'] = $LoginInfoPara;

                $Information['ActionInfo'] = $ActionInfo;
                $MessageInfo['Information'] = $Information;
                $MessageInfo['Token'] = '';
                $MessageInfo['Ak'] = '';
                $MessageInfo['Sk'] = '';
                $MessageInfo_json = $this->json_encode_ex($MessageInfo);
                $params['MessageInfo'] = $this->encode_string($MessageInfo_json);
                $handles[$login['id']] = $this->newHandle('BSBackTrack_Interface', $params);
                $i++;
            }
        }
        //批量调用接口,分页
        $page = array_chunk($handles, 10, true);
        $error_id = array();
        foreach ($page as $page_no) {
            $result = $this->multiExec($page_no);
            //出现接口异常导致失败情况
            foreach ($result as $id => $value) {
                if ($value != 0) {
                    $error_id[] = $id;
                }
            }
        }
        $ret = array('status' => '1', 'data' => '', 'message' => '回溯成功！');
        if (!empty($error_id)) {
            $ret = array('status' => '-1', 'data' => $error_id, 'message' => '回溯异常！');
        }
        return $ret;
    }

    /**
     * 组装公共参数
     */
    function set_information($action_params) {
        $ActionInfo = array();
        //产品信息
        $ProductPara = array();
        $ProductPara['ProductID'] = '7003';
        $ProductPara['ModuleID'] = '0';
        $ProductPara['FeatureID'] = '0';
        $ProductPara['ControlID'] = '0';
        $ProductPara['DatebaseName'] = isset($action_params['DatebaseName']) ? $action_params['DatebaseName'] : "";
        $ProductPara['ProductVer'] = isset($action_params['ProductVer']) ? $action_params['ProductVer'] : '';
        $ActionInfo['ProductPara'] = $ProductPara;
        //授权信息
        $LicensePara = array();
        $LicensePara['LicType'] = $action_params['LicType'];
        $LicensePara['LicMark'] = $action_params['LicMark'];
        $LicensePara['TypeInfo'] = $action_params['TypeInfo'];
        $LicensePara['LicSerial'] = $action_params['LicSerial'];
        $LicensePara['LicCompanyName'] = $action_params['LicCompanyName'];
        $LicensePara['LicCountPara'] = $action_params['LicCountPara'];
        $LicensePara['LicModulePara'] = $action_params['LicModulePara'];
        $ActionInfo['LicensePara'] = $LicensePara;
        //机器信息
        //$ComputerPara = array();
        //$ComputerPara['IpAddr'] = isset($kh_params['IpAddr']) ? $kh_params['IpAddr'] : '';
        //$ComputerPara['MacAddr'] = isset($kh_params['MacAddr']) ? $kh_params['MacAddr'] : '';
        //$ComputerPara['HostName'] = isset($kh_params['HostName']) ? $kh_params['HostName'] : '';
        //$ActionInfo['ComputerPara'] = $ComputerPara;

        return $ActionInfo;
    }

    /**
     * 组装接口参数
     * @param $apiName
     * @param $parameters
     * @return array
     */
    public function newHandle($apiName, $parameters) {
        $arr = $parameters;
        $handle = array();
        $handle['headers'] = array('Content-Type: application/json; charset=utf-8');
        $handle['type'] = "post";
        $handle['url'] = $this->url . $apiName;
        $handle['body'] = json_encode($arr);
        return $handle;
    }

    /**
     * aes加密
     * @param $Req
     * @return string
     */
    public function encode_string($Req) {
        $privateKey = "BAISON8888888888";
        $iv = "BAISON20160330JE";
        $_cipher = MCRYPT_RIJNDAEL_128;
        $_mode = MCRYPT_MODE_CBC;
        $json_string = trim($Req);
        $blockSize = mcrypt_get_block_size($_cipher, $_mode);
        $pad = $blockSize - (strlen($json_string) % $blockSize);
        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $privateKey, $json_string . str_repeat(chr($pad), $pad), MCRYPT_MODE_CBC, $iv);
        $encode_body = base64_encode($encrypted);
        $encode_tail = substr(md5($encode_body), 0, 8);
        $encode_string = $encode_body . $encode_tail;
        return $encode_string;
    }

    /**
     * 解决json_encode 后，中文会转换为\u***，导致加密不一致
     * @param $value
     * @return mixed|string
     */
    function json_encode_ex($value) {
        if (version_compare(PHP_VERSION,'5.4.0','<')) {
            $str = json_encode($value);
            $str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function($matchs) {
                    return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
                }, $str);
            return $str;
        } else {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    }
}
