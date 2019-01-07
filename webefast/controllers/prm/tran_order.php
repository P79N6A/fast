<?php
/**
 * 商品控制器相关业务
 * @author dfr
 *
 */

require_model('tb/TbModel');

class tran_order extends TbModel {
    function connect_efast3($name, $host, $user, $pwd) {
        CTX()->db->set_conf(array(
            'name' => $name,
            'host' => $host,
            'user' => $user,
            'pwd' => $pwd,
            'type' => 'mysql',
            'port' => '3306',
        ));

    }

    function do_list(array & $request, array & $response, array & $app){
    }

    function test_connect(array & $request, array & $response, array & $app){
        $this->connect_efast3($request['db_name'], $request['db_host'], $request['db_user'], $request['db_pass']);
        $sql = "select order_sn from order_info limit 1";
        $res = CTX()->db->get_all($sql);
        var_dump($res);
        if ($res) {
            CTX()->set_session('dbhost3',$request['db_host']);
            CTX()->set_session('dbuser3',$request['db_user']);
            CTX()->set_session('dbpwd3',$request['db_pass']);
            CTX()->set_session('dbname3',$request['db_name']);
        }
        if ($res) {
            $response['status'] = 1;
        } else {
            $response['status'] = 0;
        }
    }

    function search_order(array & $request, array & $response, array & $app)
    {
        $result = $this->search_param($request);
        if ($result) {
            $response['status'] = 1;
            $response['order_count'] = count($result);
        } else {
            $response['status'] = -1;
        }
        
    }

    function search_param($filter){
        $host = CTX()->get_session('dbhost3');
        $user = CTX()->get_session('dbuser3');
        $pwd = CTX()->get_session('dbpwd3');
        $name = CTX()->get_session('dbname3');
        $this->connect_efast3($name, $host, $user, $pwd);
        $sql = "select order_sn, deal_code from order_info";
        $sql_where = " WHERE order_status != 3";
        if (isset($filter['created_start']) && $filter['created_start'] <> '') {
            $created_start = strtotime($filter['created_start']);
            $sql_where .= " AND add_time>".$created_start; 
        }
        if (isset($filter['created_end']) && $filter['created_end'] <> '') {
            $created_end = strtotime($filter['created_end']);
            $sql_where .= " AND add_time<".$created_end; 
        }
        if (isset($filter['short_store_status']) && $filter['short_store_status'] <> '' && $filter['short_store_status'] != 'all') {
            $sql_where .= " AND is_separate =" .$filter['short_store_status'];
        }
        if (isset($filter['send_store_status']) && $filter['send_store_status'] <> '' && $filter['send_store_status'] != 'all') {
            $sql_where .= " AND shipping_status =" .$filter['send_store_status'];
        }
        $res =  CTX()->db->getAll($sql.$sql_where);
        return $res;
    }

    function export(array &$request, array &$response, array &$app){
        $res = $this->search_param($request);
            
        $file_str = '';
        foreach($res as $v){
            $v['deal_code'] = str_replace(',', '，', $v['deal_code']);
            $file_str .= "\t".$v['order_sn'].','."\t".$v['deal_code']."\n";
        }
        $str1 = "订单号,交易号"."\n";
        $header_str .= iconv("utf-8", 'gbk', $str1);
        $filename = "eFAST3.0订单.csv";
        $filename = iconv('UTF-8', 'GBK', $filename);
        // header('Cache-Control:   must-revalidate,   post-check=0,   pre-check=0');
        // header("Pragma: public");
        // header("Content-type:application/vnd.ms-excel");
        // header("Content-Disposition:attachment;filename=" . $filename);
        // header('Content-Type:APPLICATION/OCTET-STREAM');

    // $file_name=auto_charset("合并订单列表","gb2312","utf8");
    header('Cache-Control: no-cache, must-revalidate');
    Header("Content-type:application/vnd.ms-excel;charset=utf-8");
    Header("Content-Disposition:filename=".$filename.".csv");

        $file_str = iconv('UTF-8', 'GBK', $file_str);
        ob_end_clean();
        
        echo $header_str;
        echo $file_str;
        die;
    }
}