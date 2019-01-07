<?php
function array2xml($array, $root_tag=NULL, $xmlns='') {
    if ($root_tag == NULL) {
        $head = '<?xml version="1.0" encoding="UTF-8" ?>';
    } else {
        $head = '<?xml version="1.0" encoding="UTF-8" ?>'."<{$root_tag} {$xmlns}></$root_tag>";
    }
    // creating object of SimpleXMLElement
    $xml_info = new SimpleXMLElement($head);
    // function call to convert array to xml
    __array2xml($array, $xml_info);
    //return generated xml file
    return $xml_info->asXML();
}
function __array2xml($data, &$xml_info) {
    foreach($data as $key => $value) {
        if(is_array($value)) {
            if(!is_numeric($key)){
                $subnode = $xml_info->addChild("$key");
                __array2xml($value, $subnode);
            } else {
                __array2xml($value, $xml_info);
            }
        } else {
            $xml_info->addChild("$key","$value");
        }
    }
}

/**
 * xml字符串解析为数组，xml字符串如<orders><order orderid="97913707" merchantid="6619"
 * paysum="32.90" isdeleted="0" ispaid="0" status="1" merchantorderid="" /></orders>
 * <br/>如果不是xml字符串，不解析。<br/>原如果解析失败，抛出Execption异常
 * @param string $xmlString xml字符串

 * @param array $arr 解析后的结果数组
 */
function xml2array(&$xmlString,array &$arr){
    $xmlString=trim($xmlString);
    if(strpos($xmlString,'<')!==0) return false; //simple test is xml
    try{
        $root =new SimpleXMLElement($xmlString, LIBXML_NOCDATA);
        $name=$root->getName();
        $resultarr=array();
        _webmethod_xml2array_fillArray($root,$resultarr);
        $arr[$name]=$resultarr; //set return val form
        return true;
    }catch (Exception $e){
        throw new Exception('返回xml解析错误:' . $e->getMessage());
    }
}
//----webmethod_xml2array private function ------//
function _webmethod_xml2array_fillArray($root,array &$resultarr){
    if(! $root) return;
    $rootarr=get_object_vars($root);
    foreach ($rootarr as $key=>$val) {
        if(is_object($val)){
            $newarr=array();
            _webmethod_xml2array_fillArray($val,$newarr);
            if(count($newarr) >0 )  $resultarr[$key]=$newarr;
            else  $resultarr[$key]=NULL;
        }
        else if(is_array($val) && count($val)>0){//is array
            $valarr=array();
            // add by  wzd at 2010-10-15
            $val = array_values($val); //对数组重新索引


            for($i=0;$i<count($val);$i++){
                $v=$val[$i];
                if(is_object($v)){
                    $newarr=array();
                    _webmethod_xml2array_fillArray($v,$newarr);
                    $valarr[]=$newarr;
                }
                else
                    $valarr[]=$v;
            }
            $resultarr[$key]=$valarr;
        }
        else
            $resultarr[$key]=$val;
    }
}
