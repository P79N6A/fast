<?php

require_lib('apiclient/ApiClient');
require_lib('util/crm_util');

class YDApiModel extends ApiClient implements ApiClientInterface {

    protected $api_url = '';
    protected $conf = array();

    function __construct() {
        parent::__construct();
    }

    function set_api_url() {
        if ($this->api_url == '') {
            $sql = "select api_content from base_express_company where company_code='YUNDA'";
            $api_content = CTX()->db->get_value($sql);
            if(!empty($api_content)){
                $this->conf = json_decode($api_content,TRUE);
                $this->api_url = isset($this->conf['url'])?$this->conf['url']:'';
            }
        }
    }

    function request_api($params) {
        $this->set_api_url();
        if ($this->api_url == '') {
            return -1;
        }
        $response = $this->exec('', $params);

        $data = $this->xml2array($response);

        return $data['responses']['response'];
    }

    public function newHandle($apiName, $parameters) {

        $arr['partnerid'] = $this->conf['partnerid'];
        $arr['version'] = '1.0';
        $arr['request'] = 'data';
     
        $xmldata = $this->get_data_xml($parameters);
        $arr['xmldata'] = base64_encode($xmldata);

        $arr['validation'] = $this->validation($arr);

        $handle = array();
        $handle['type'] = "post";
        $handle['url'] = $this->api_url;


        $handle['body'] = $arr;
        return $handle;
    }

    protected function xml2array($data) {
        require_lib('util/xml_util');
        $return = array();
        xml2array($data, $return);
        return $return;
    }

    private function validation($arr) {

        $str = $arr['xmldata']. $arr['partnerid'] . $this->conf['secret'];
        return md5($str);
    }

    public function get_data_xml($array) {

        //   $xml =  $this->array2xml($array, 'xmldata');
        //$xml = "<![CDATA[".$xml."]]>";
        require_lib('new_xml_util');
        $xml = array_create_xml($array, '');
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        return trim($xml);
    }


}

?>
