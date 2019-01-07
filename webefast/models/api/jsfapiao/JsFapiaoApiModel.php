<?php

/**
 * Kisdee API
 */
require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class JsFapiaoApiModel extends ApiClient implements ApiClientInterface {

    protected $api_url = 'https://www.jsfapiao.cn:8443/jsAeroInfoTransferServicePro_zzs/services/DispatcherController?wsdl';
    protected $api_param;

    function __construct($api_param) {
        $this->api_param = $api_param;
    }

    function request_api($interfaceCode, $data_xml) {
        $this->api_url = $this->api_param ['electron_url'];
        $logPath = $this->get_log_path();
        error_log(date("Y-m-d H:i:s") . "req: \n" . $data_xml."\n", 3, $logPath);
        $xml = $this->set_data_format($interfaceCode, $data_xml);
        $result = $this->exec('', $xml);

        return $this->get_response($result);
    }

    function request_api_old($interfaceCode, $data_xml) {
        $time1 = date("Y-m-d H:i:s");
        $time_num = time();
        $soap_client = new SoapClient($this->api_url);
        $xml = $this->set_data_format($interfaceCode, $data_xml);

        $logPath = $this->get_log_path();
        error_log(date("Y-m-d H:i:s") . "req: \n" . $xml, 3, $logPath);
        $api_param['in0'] = $xml;
        $result = $soap_client->eiInterface($api_param);

        $time2 = date("Y-m-d H:i:s");
        $cha_time = time() - $time_num;
        error_log(date("Y-m-d H:i:s") . "ret:({$time1}-{$time2} 耗时：{$cha_time}) \n" . $result . "\n", 3, $logPath);

        return $this->get_response($result);
    }

    function get_response($result) {
        $data = $this->xml2array($result);

        $returnMessage = $data['interface']['returnStateInfo']['returnMessage'];
        $return_data['returnMessage'] = base64_decode($returnMessage);
        $return_data['returnCode'] = $data['interface']['returnStateInfo']['returnCode'];
        $return_data['returnMessage'] = base64_decode($returnMessage);
        if(isset($data['interface']['Data']['content'])){
            $content = base64_decode($data['interface']['Data']['content']);
            if(isset($data['interface']['Data']['dataDescription']['zipCode'])&&$data['interface']['Data']['dataDescription']['zipCode']==1){
                     $content = gzdecode ($content) ;
            }
            $return_data['content'] = $content;
        }

        return $return_data;
    }

    protected function xml2array($data) {
        require_lib('util/xml_util');
        $return = array();
        xml2array($data, $return);
        return $return;
    }

    function set_data_format($interfaceCode, $data_xml) {
        $requestTime = date('Y-m-d H:i:s');
        $data_xml_base64 = base64_encode($data_xml);
        $sort_no = rand(0, 999999999);
        $sort_no = $this->get_no($sort_no,9);
        //requestCode+8 位日期(YYYYMMDD)+9 位序列号
        $dataExchangeId = $this->api_param['username'] . date('Ymd') . $sort_no;
        $xml = <<< EOD
<?xml version = "1.0" encoding = "utf-8" ?>
<interface version="DZFP1.0" >
    <globalInfo>
        <terminalCode>0</terminalCode>
        <appId>ZZS_PT_DZFP</appId>
        <version>2.0</version>
        <interfaceCode>{$interfaceCode}</interfaceCode>
        <userName>{$this->api_param['username']}</userName>
        <passWord>{$this->api_param['password']}</passWord>
        <taxpayerId>{$this->api_param['nsrsbh']}</taxpayerId>
        <authorizationCode>{$this->api_param['authorizationcode']}</authorizationCode>
        <requestCode>{$this->api_param['username']}</requestCode>
        <requestTime>{$requestTime}</requestTime>
        <responseCode>144</responseCode>
        <dataExchangeId>{$dataExchangeId}</dataExchangeId>
    </globalInfo>
    <returnStateInfo>
	<returnCode/>
	<returnMessage/>
	</returnStateInfo>
    <Data>
        <dataDescription>
            <zipCode>0</zipCode>
            <encryptCode>0</encryptCode>
            <codeType>0</codeType>
        </dataDescription>
        <content>{$data_xml_base64}</content>
    </Data>
</interface>
EOD;
        //返回格式 
//         <returnStateInfo>--------------返回信息
//        <returnCode>返回代码</returnCode>
//        <returnMessage>base64 返回描述</returnMessage>
//    </returnStateInfo>
        return $xml;
    }

    function array2xml($data, $tag, $class = '') {
        require_lib('util/xml_util');
        $xml = array2xml($data, $tag, '');

        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        $xml = '<REQUEST_FPKJXX class="REQUEST_FPKJXX "></>';
        return $xml;
    }

    function get_log_path() {
        static $logPath = NULL;
        if ($logPath === NULL) {
            $date = date("Y-m-d");
            $logPath = ROOT_PATH . "logs" . DIRECTORY_SEPARATOR;

            if (defined('RUN_SAAS') && RUN_SAAS) {
                $logPath .= "http_client" . DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)) {
                    mkdir($logPath);
                }
                $logPath .= $date . DIRECTORY_SEPARATOR;
                if (!file_exists($logPath)) {
                    mkdir($logPath);
                }
                $logPath .= "http_client_";
                $saas_key = CTX()->saas->get_saas_key();
                if (!empty($saas_key)) {
                    $logPath .= $saas_key . "_";
                }

                $logPath .=$date . ".log";
            } else {
                $logPath .= "http_client" . $date . ".log";
            }
        }
        return $logPath;
    }

    public function newHandle($apiName, $parameters) {


        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url;
        //$header = array("Content-Type:application/xml;charset=UTF-8");
        $handle['header'] = array();
  
        $handle['body'] = $parameters;
        
        return $handle;
    }
   function get_no($no,$max=20){
        $z='';
        $len = strlen($no);
        if($max>$len){
        for($i=0;$i<$max-$len;$i++){
            $z="0".$z;
        }
        }
        
        return $z.$no;
    }
}
