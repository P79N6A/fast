<?php
class code_manage {
    public function index()
    {
    }
    
    public function gen_code(array & $request)
    {
        exit_json_response(1, array(
            'code' => $this->ProductEnryptV3($request['hardware_num'])
        ));
    }
    
    private function ProductEnryptV3($sDevNo, $sCheckNo = '')
    {
        $sDes = "";
        $sKey = "BSEFAST365";
        //添加秘钥
        $sCheckNo = $sDevNo . $sKey;
        //二次MD5加密
        $sDesMd5 = md5(strtoupper($sCheckNo));
        $sIndex = "";
        $index = 0;
        //从结果中，取对应设备ID位数的字符
        $len = strlen($sDevNo);

        for ($i = 0; $i < $len; $i++) {
            $sIndex = substr($sDevNo,$i, 1);

            $index = $i + 3;
            if (preg_match("/\d/", $sIndex)) {

                $index = (int) $sIndex;

                if ($i + $index < 30) {
                    $index = $i + $index;
                }
            }

            $sDes = $sDes .substr($sDesMd5, $index, 1);
        }
        return strtoupper($sDes);
    }
}