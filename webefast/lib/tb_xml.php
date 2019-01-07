<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbxml_util
 *
 * @author wq
 */
class tb_xml {

    /**
     * xml字符串解析为数组，xml字符串如<orders><order orderid="97913707" merchantid="6619"
     * paysum="32.90" isdeleted="0" ispaid="0" status="1" merchantorderid="" /></orders>
     * <br/>如果不是xml字符串，不解析。<br/>原如果解析失败，抛出Execption异常
     * @param string $xmlString xml字符串

     * @param array $arr 解析后的结果数组
     */
    function xml2array(&$xmlString, array &$arr) {
        $xmlString = trim($xmlString);
        if (strpos($xmlString, '<') !== 0)
            return false; //simple test is xml
        try {
            $root = new SimpleXMLElement($xmlString, LIBXML_NOCDATA);
            $name = $root->getName();
            $resultarr = array();
            $this->_webmethod_xml2array_fillArray($root, $resultarr);
            $arr[$name] = $resultarr; //set return val form
            return true;
        } catch (Exception $e) {
            throw new Exception('返回xml解析错误:' . $e->getMessage());
        }
    }

//----webmethod_xml2array private function ------//
    private function _webmethod_xml2array_fillArray($root, array &$resultarr) {
        if (!$root)
            return;
        $rootarr = get_object_vars($root);
        if (empty($rootarr)) {
            $root = $root->children();
            $rootarr = get_object_vars($root);
        }

        foreach ($rootarr as $key => $val) {
            if (is_object($val)) {
                $newarr = array();
                $this->_webmethod_xml2array_fillArray($val, $newarr);
                if (count($newarr) > 0)
                    $resultarr[$key] = $newarr;
                else
                    $resultarr[$key] = NULL;
            }
            else if (is_array($val) && count($val) > 0) {//is array
                $valarr = array();
                // add by  wzd at 2010-10-15
                if ($key == '@attributes') {
                    foreach ($val as $attr => $name) {
                        $valarr[$attr] = $name;
                    }
                } else {
                    $val = array_values($val); //对数组重新索引
                    for ($i = 0; $i < count($val); $i++) {
                        $v = $val[$i];
                        if (is_object($v)) {
                            $newarr = array();
                            $this->_webmethod_xml2array_fillArray($v, $newarr);
                            $valarr[] = $newarr;
                        } else
                            $valarr[] = $v;
                    }
                }
                $resultarr[$key] = $valarr;
            } else
                $resultarr[$key] = $val;
        }
    }

    function get_tb_xml($array) {
        $key_arr = array_keys($array);

        $xml = $this->array2xml($array[$key_arr[0]], $key_arr[0]);
        $head = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml = str_replace($head, '', $xml);
        return $xml;
    }

    function array2xml($array, $root_tag = NULL, $xmlns = '') {
        if ($root_tag == NULL) {
            $head = '<?xml version="1.0" encoding="UTF-8" ?>';
        } else {
            $head = '<?xml version="1.0" encoding="UTF-8" ?>' . "<{$root_tag} {$xmlns}></$root_tag>";
        }
        try {
            // creating object of SimpleXMLElement
            $xml_info = new SimpleXMLElement($head);
            // function call to convert array to xml
            $this->__array2xml($array, $xml_info);
            //return generated xml file
            $xml = $xml_info->asXML();
        } catch (Exception $ex) {
            var_dump($ex);
            die;
        }
        $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
        $xml = str_replace("\n", '', $xml);
        return $xml;
    }

    private function __array2xml($data, &$xml_info) {

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    if ($key == '@attributes') {
                        foreach ($value as $k => $v) {
                            $xml_info->addAttribute($k, $v);
                        }
                    } else {

                        if (isset($value[1])) {
                            foreach ($value as $c_v) {

                                if (is_array($c_v)) {
                                    $subnode = $xml_info->addChild("$key");
                                    $this->__array2xml($c_v, $subnode);
                                } else {
                                    $xml_info->addChild("$key", "$c_v");
                                }
                            }
                        } else {
                            $subnode = $xml_info->addChild("$key");
                            $this->__array2xml($value, $subnode);
                        }
                    }
                } else {
                    $this->__array2xml($value, $xml_info);
                }
            } else {
                $xml_info->addChild("$key", "$value");
            }
        }
    }

}
