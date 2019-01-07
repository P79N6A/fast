<?php

function array_create_xml($data, $root_tag = '') {

// create doctype
    $dom = new DOMDocument("1.0", "UTF-8");

    //header("Content-Type: text/plain");
    if ($root_tag != '') {
        $root = $dom->createElement($root_tag);
        $dom->appendChild($root);
    }else{
        $root = $dom;
    }
    __create_node_xml($data, $dom, $root);
    return $dom->saveXML();
}

function __create_node_xml(&$data, &$dom, &$root) {
    try {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $key_dom = $dom->createElement($key);
                    $root->appendChild($key_dom);
                    __create_node_xml($value, $dom, $key_dom);
                } else {
                    __create_node_xml($value, $dom, $root);
                }
            } else {
                $key_dom = $dom->createElement($key);
                $root->appendChild($key_dom);
                if (check_is_change_cdata($value)) {
                    $text = $dom->createCDATASection($value);
                } else {
                    $text = $dom->createTextNode($value);
                }
                $key_dom->appendChild($text);
            }
        }
    } catch (Exception $e) {
        var_dump($e->getMessage());
        die;
    }
}

function check_is_change_cdata($value) {
    $check_arr = array('&'); //'<','>',
    foreach ($check_arr as $v) {
        if (strpos($value, $v) !== FALSE) {
            return TRUE;
        }
    }
    return FALSE;
}