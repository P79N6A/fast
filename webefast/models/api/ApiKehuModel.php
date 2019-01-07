<?php

require_model('tb/TbModel');
require_lib('tool/Cache.class',false);
class ApiKehuModel extends TbModel {

    /**
     * 根据客户ID获取RDS链接信息
     * @author jhua.zuo<jhua.zuo@baisonmail.com>
     * @date 2015-03-11
     * @todo 保存和获取的信息需要加密和解密
     * @param int $kehu_id 运营平台客户ID
     * @return array 返回json_decode以后的RDS连接信息
     */
    public function get_rds_info_by_kehu2($kehu_id) {
        $result = $this->get_row(array('kh_id' => $kehu_id));
        if (1 != $result['status'] || empty($result['data'])) {
            throw new Exception('|客户ID：' . $kehu_id . ' 没有获取到有效的客户数据库链接信息');
        }
        $rds = json_decode($result['data']['rds'], 1);
        return $rds;
    }

    function get_rds_info_by_kehu($kh_id) {
         $key_str = "open_api_kh_link_".$kh_id;
         $key = md5($key_str);
         
         $cache = new FileCache();
         $rds_data_str = $cache->get($key);
        if(empty($rds_data_str)){
            $rds_data = $this->db->get_row(" select * from osp_aliyun_rds where rds_id in(select rem_db_pid from osp_rdsextmanage_db where rem_db_khid=:kh_id )",array(':kh_id'=>$kh_id));
            if (!empty($rds_data)) {
                $keylock_date = date('Y-m-d', strtotime($rds_data['rds_createdate']));
                $keylock = get_keylock_string($keylock_date);

                $rds_data['rds_pass'] = create_aes_decrypt($rds_data['rds_pass'], $keylock);
                $rds_data['db_name'] = $this->get_kh_db_name($kh_id);
                $rds_data_str = json_encode($rds_data);
                $cache->set($key,$rds_data_str,7200);
            }
        }else{
            $rds_data = json_encode($rds_data_str);
        }


        return $rds_data;
    }

    function get_kh_id_by_shop($shop_nick) {
        $key_str = "open_api_kh_link_" . $shop_nick;
        $key = md5($key_str);

        $cache = new FileCache();
        $shop_data_str = $cache->get($key);
        if (empty($shop_data_str)) {
            $shop_data = $this->db->get_row("SELECT sd_kh_id AS kh_id FROM osp_shangdian WHERE sd_nick=:shop_nick )", array(':shop_nick' => $shop_nick));
            if (!empty($shop_data)) {
                $shop_data_str = json_encode($shop_data);
                $cache->set($key, $shop_data_str, 7200);
            }
        } else {
            $shop_data = json_encode($shop_data_str);
        }


        return $shop_data;
    }

    function get_kh_db_name($kh_id) {
        $sql = "select rem_db_name from osp_rdsextmanage_db WHERE rem_db_khid=:kh_id  ";
        $data = $this->db->get_row($sql,array(':kh_id'=>$kh_id)); 
        return $data['rem_db_name'];
    }

    function change_db_conn($kh_id) {
        
        if(!is_numeric($kh_id)){

             echo "请求非法！";die;

        }
        
        $init_info = array();
        $init_info['client_id'] = $kh_id;
        $ret_status = CTX()->saas->init_saas_client($init_info); 
        if($ret_status['status']<1){
            // 切换到运营中心据链接
            $rds = $this->get_rds_info_by_kehu($kh_id);
            if (empty($rds)) {
                return FALSE;
            }


            $init_info['rds_id'] = $rds['rds_id'];
            $init_info['db_conf'] = array(
                'name' => $rds['db_name'],
                'host' => $rds['rds_link'],
                'user' => $rds['rds_user'],
                'pwd' => $rds['rds_pass'],
                'type' => 'mysql',
                'port' => '3306'
            );
            CTX()->saas->init_saas_client($init_info);
        }
        CTX()->saas->set_saas_mode_api();
        return TRUE;
    }
    function getIP() {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}
