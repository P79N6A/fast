<?php
class OspCryptModel extends TbModel {
    /**
     * 获取有效期内的密钥key
     *
     * @param   date   $keydate   加密日期
     * @access  public
     * @return  string   返回key字符串
     */
    function get_keylock_string($keydate){
        if(!$keydate){
            return '';
        }
        $keydate = date('Y-m-d',  strtotime($keydate));
        //获取key
        $strSQL="select key_string from osp_keylock where key_startdate<=:startdate and key_enddate>=:enddate";
        $key=CTX()->db->getOne($strSQL,array(':startdate'=>  $keydate,':enddate'=>$keydate));
        if(!$key){
            //生成有效期内的key
            $key=uniqid();
            //保存keylock表，默认日期段一个月
            $y = date('Y', strtotime($keydate));
            $m = date('m', strtotime($keydate));
            $mindate = date('Y-m-d', mktime(0, 0, 0, $m, 1, $y));
            $maxdate = date('Y-m-d', mktime(0, 0, 0, $m+1, 1, $y) - 1);
            $keylist=array(
                'key_startdate'=>$mindate,
                'key_enddate'=>$maxdate,
                'key_string'=>$key
            );
            CTX()->db->insert('osp_keylock',$keylist);
            return $this->getmd5_tobase64($key);
        }else{
            return $this->getmd5_tobase64($key);
        }
    }

    /**
    * 生成mysql加密密码
    */
    function create_aes_encrypt($str,$key){
        $strSQL="select HEX(AES_ENCRYPT(:pwd,:key))";
        $enkey=CTX()->db->getOne($strSQL,array(':pwd'=>  $str,':key'=>$key));
        return $enkey;
    }

    /**
    * 解密mysql加密密码
    */
    function create_aes_decrypt($str,$key){
        $strSQL="select AES_DECRYPT(UNHEX(:pwd),:key)";
        $dekey=CTX()->db->getOne($strSQL,array(':pwd'=>  $str,':key'=>$key));
        return $dekey;
    }


    /**
    * 获取md5加密,base64,二进制数据包组合
    */
    function getmd5_tobase64($str){
        return base64_encode(pack("H32",md5($str)));
    }

}