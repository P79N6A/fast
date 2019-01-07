<?php

/**
 * 用于调用云中心接口
 * 2014/12/6
 * @author jia.ceng
 */
class CloudModel {

        /**
         * 发送同步请求
         */
        static public function send($api_act, $data = '') {
                if (defined('CLOUD') && CLOUD) {
                        $url = CTX()->get_app_conf('cloud_api');
                        if($url==''){
                                return array("status"=>-1,"data"=>'',"message"=>'配置文件出错，没有cloud_api参数');
                        }
                        $sign = self::sign($data);
                        $url = $url . $api_act . '&' . $sign['query'] . '&sign=' . $sign['sign'];

                        //echo $url;exit;
                        //    $result_str = file_get_contents($url);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

                        $result_str = curl_exec($ch);
                        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);

                        if ($result_str) {
                                $result = json_decode($result_str, true);
                                return $result;
                        }
                }

                return array("status"=>true,"message"=>'',"data"=>'');
        }

        /**
         * 生成签名和时间戳
         * @param type $data
         */
        static public function sign($data) {
                $timestamp = time();
                $data['timestamp'] = $timestamp;
                $data['kh_code'] = isset($_SESSION['kh_code']) ? $_SESSION['kh_code'] : '';
                ksort($data);
                $sign = md5(http_build_query($data) . APP_SALT);
                return array(
                        'sign' => $sign,
                        'timestamp' => $timestamp,
                        'query' => http_build_query($data)
                );
        }

}
