<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExplainJosnModel
 *
 * @author wq
 */
class ExplainModel {

    private $conf_all = array();
    private $param_data = array();
    private $return_data = array();

    //put your code here
    function get_explain_data(&$data) {
        $this->conf_all = $this->set_config($data['type'], $data['method']);

        if (!empty($this->conf_all)) {
            $this->set_param_data($data['params']);
            $this->set_return_data($data['return_data']);

            $this->get_error($data, $this->conf_all['error']);

            foreach ($this->conf_all['method'][$data['method']] as $key => $conf) {
                $func = "get_" . $key;
                $this->$func($data, $conf);
            }
        }
    }

    protected function set_config($api_type, $method) {
        //taobao_explain.conf
        $conf_name = $api_type . "_explain";
        $conf = require_conf('apilog/' . $conf_name);
        return isset($conf['method'][$method]) ? $conf : array();
    }

    protected function get_post_data_key(&$data, $conf) {
        //data_fmt
        $word_data['post_data_key'] = '';
        $word_data['post_data'] = '';
        if (empty($this->param_data)) {
            return false;
        }
        if (!isset($conf['key_list'])) {
            $word_data = $this->get_word_data($this->param_data, $conf);
        } else {
            $key_data = $this->get_key_list($this->param_data, $conf['key_list']);
            if($key_data===false&&isset($conf['post_data_key'])){//通色通码找不到
                return $this->get_post_data_key($data, $conf['post_data_key']);
                
            }
            if (isset($conf['data_fmt'])) {
                $key_data = $this->get_fmt_data($key_data, $conf['data_fmt']);
            }

            $word_data = $this->get_word_data($key_data, $conf);
        }
        $data = array_merge($data, $word_data);
    }

    protected function get_return_data(&$data, $conf) {
        if ($data['is_err'] < 2) {//未处理或正常
            $check = $this->get_key_list($this->return_data, $conf['key_list']);
            if ($check === false) {
                $data['is_err'] = 3;   //异常
            }
            $data['is_err'] = 1;   //正确
        }
    }

    protected function get_index_key(&$data, $conf) {
        $data['index_key1'] = '';
        if (isset($conf['data_key'] ) && $conf['data_key'] == 'params') {
            $index_data = $this->param_data;
            $data['index_key1'] = $this->get_key_list($index_data, $conf['key_list']);
        }
    }

    protected function get_error(&$data, $conf) {
        $data['api_data'] = '';
        $error_msg = $this->get_key_list($this->return_data, $conf['key_list']);
        if ($error_msg !== false) {
            $data['api_data'] = $error_msg;
            $data['is_err'] = 2;
        }
    }

    private function get_key_list($data, $conf) {
        $key_data = $data;
        foreach ($conf as $key) {
            $key_data = isset($key_data[$key]) ? $key_data[$key] : false;
        }
        return $key_data;
    }

    private function get_fmt_data($params_str, $fmt_type) {
        $new_data = array();
        if (is_array($fmt_type)) {

            if ($fmt_type['data_fmt'] == 'explode') {
                $data = explode($fmt_type['split'], $params_str);

                if (isset($fmt_type['row_type'])) {
                    $data = array_filter($data);
                    foreach ($data as $row) {
                        $new_data[] = $this->get_fmt_data($row, $fmt_type['row_type']['data_fmt']);
                    }
                } else {
                    $new_data = $data;
                }
            }
        } else if ($fmt_type == 'json') {
            $new_data = json_decode($params_str, true);
        }
        return $new_data;
    }

    private function get_word_data($data, $conf) {
        $word_data['post_data_key'] = array();
        $word_data['post_data'] = array();
        if ($conf['type'] == 1) {
            
            $key_word = isset($data[$conf['key_word']])?$data[$conf['key_word']]:'';
            
            //淘宝通色通码处理
            if(empty($key_word)&&isset($conf['_key_word'])){
                $key_word = $data[$conf['_key_word']];
            }
    
            $word_data['post_data_key'] [] = $key_word;  //key_word ,val_word
            $word_data['post_data'][$key_word] = $data[$conf['val_word']];  //key_word ,val_word      
        } else if ($conf['type'] == 2) {
            foreach ($data as $val) {
                $key_word = $val[$conf['key_word']];
                $word_data['post_data_key'][] = $key_word;
                $word_data['post_data'][$key_word] = $val[$conf['val_word']];
            }
        } else if ($conf['type'] == 3) {
            $key_word_key_arr = explode("|", $conf['key_word']);
            $key_word_arr = array();
            foreach ($key_word_key_arr as $w_key) {
                $key_word_arr[] = $data[$w_key];
            }
            $key_word = implode('|', $key_word_arr);
            $word_data['post_data_key'][] = $key_word;
            $word_data['post_data'][$key_word] = $data[$conf['val_word']];  //key_word ,val_word         
        }


        $word_data['post_data_key'] = implode(",", $word_data['post_data_key']);
        $word_data['post_data'] = json_encode($word_data['post_data']);
        return $word_data;
    }

    private function set_param_data($params) {

        $this->param_data = $this->get_fmt_data($params, $this->conf_all['data_fmt']);
    }

    private function set_return_data($return_data) {
        $this->return_data = $this->get_fmt_data($return_data, $this->conf_all['data_fmt']);
    }

}
