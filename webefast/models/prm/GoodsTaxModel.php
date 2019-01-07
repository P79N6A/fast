<?php
require_lib('util/oms_util', true);
require_model('tb/TbModel');
require_lang('prm');
/**
 * 商品税务编码
 */
class GoodsTaxModel extends TbModel {

    function get_table() {
        return 'goods_tax';
    }
    public $barcode = array();
    public $tax_code = array();
    public $goods_code = array();
    /*
     * 根据条件查询数据
     */
    public function get_by_page($filter) {
        $sql_values = array();
        $sql_main = "FROM {$this->table}  WHERE 1";
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword']);
        }
        if (!empty($filter['barcode'])) {
            $sql_main .= " AND barcode LIKE :barcode ";
            $sql_values[':barcode'] = '%' . $filter['barcode'] . '%';
        }
        if (!empty($filter['goods_code'])) {
            $sql_main .= " AND goods_code LIKE :goods_code ";
            $sql_values[':goods_code'] = '%' . $filter['goods_code'] . '%';
        }
        if (!empty($filter['tax_code'])) {
            $sql_main .= " AND tax_code LIKE :tax_code ";
            $sql_values[':tax_code'] = '%' . $filter['tax_code'] . '%';
        }
        $sql_main .= " ORDER BY lastchanged DESC ";
        $select = '*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);

        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    
    
    //商品税务编码按条码导入
    function import_tax_by_barcode($file,$type='') {
        require_lib('csv_util');
        $csv_cls = new execl_csv();
        $key_arr = array('barcode', 'tax_code','unit','goods_code_short');
        $data = $csv_cls->read_csv($file, 2, $key_arr);
        $this->barcode = array_unique(array_column($data, 'barcode'));
        if(empty($this->barcode)){
            return $this->format_ret('-1', '','条码不能为空');
        }
        $this->tax_code = array_unique(array_column($data, 'tax_code','barcode'));

        $barcode_arr = $this->get_barcode();//导入的条码
        $tax_arr = $this->get_tax_code();//已使用税务编码
        $use_bar = $this->get_use_barcode();//已使用条码
        $success_num = 0;
        $fail_num = 0;
        $faild = array();
        $tax_data = array();
        foreach ($data as $key =>$val) {
            $rus = $this->is_valid_excel_data($val, $key,$type);
            if($rus['status'] < 1){
                $faild[] = $rus;
                $fail_num ++;
                continue;
            }
            $r = $this->set_check_barcode($val,$barcode_arr,$tax_arr,$use_bar,$key);
            if ($r['status'] == '1') {
                $success_num++;
                $tax_data[] = $r['data'];             
            } else {
                $faild[] = $r['message'];
                $fail_num ++;
            }
        }
        
         $message = '导入成功：' . $success_num . '条';          
        $status = 1;
        if (!empty($faild)) {
            $status= '-1';
            $message .=',' . '失败数量:' . $fail_num;
            $file_name = $this->create_import_fail_files($faild, 'customer_import');
            $url = set_download_csv_url($file_name,array('export_name'=>'error'));
            $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        }
        if (!empty($tax_data)) {
            $up_str = "tax_code=VALUES(tax_code),unit=VALUES(unit),goods_code_short=VALUES(goods_code_short)";
            $res = $this->insert_multi_duplicate('goods_tax', $tax_data, $up_str);
        }
        $ret['status'] = $status;
        $ret['message'] = $message;
        return $ret;
    }
    
    
    //按商品编码导入
    function import_tax_by_goods_code($file,$type='') {
        $success_num = 0;
        $fail_num = 0;
        $all_num = 0;
        $faild = array();
        $data = array();
        require_lib('csv_util');
        $csv_cls = new execl_csv();
        $key_arr = array('goods_code', 'tax_code','unit','goods_code_short');
        $list = $csv_cls->read_csv($file, 2, $key_arr);
        $this->goods_code = array_unique(array_column($list, 'goods_code'));
        if(empty($this->goods_code )){
            return $this->format_ret('-1', '','商品编码不能为空');
        }
        $this->tax_code = array_unique(array_column($list, 'tax_code'));
        $goods_code_list = $this->get_goods_code_list();//导入的商品编码
        $tax_arr = $this->get_tax_code();//已使用税务编码
        $tax_data = array();
        $bar_arr = array();
        $i = 3;
        foreach($list as $k => $v){
            $rus = $this->is_valid_excel_data($v,$k,$type);
            if($rus['status'] < 1){
                $faild[] = $rus;
                $fail_num ++;
                $i++;
                continue;
            }
            if(!in_array($v['goods_code'], $goods_code_list)){
                //检验商品编码是否存在商品表中
                $base_ret = $this->check_base_goods_code($v['goods_code']);
                $fail_str = '';
                if(!empty($base_ret)){
                    $fail_str = '第'.$i.'行商品编码：'.$v['goods_code'].'有部分条码为空';
                }else{
                    $fail_str = '第'.$i.'行系统中不存在此商品编码：'.$v['goods_code'];
                }
                    $faild[] = $fail_str;
                    $fail_num ++;
                    $i++;
                    continue;
            }
            
            $barcode = array_keys($goods_code_list,$v['goods_code'],false);
                        
            foreach ($barcode as $key) {
                $bar = explode('_bc_',$key);
                $bar_arr[] = $bar[0];
                if(empty($bar[0])){
                    $bar_arr = [];
                    $faild[] = '第'.$i.'行商品编码：'.$v['goods_code'].'有部分条码为空';
                    break;//如果商品编码下有条码为空，就忽略次编码
                }
            }
            if(empty($bar_arr)){
                $fail_num ++;
                $i++;
                continue;
            }
            foreach($bar_arr as $value){
                   $data[]=array('barcode'=>$value,
                   'goods_code'=>$v['goods_code'],
                   'tax_code'=>$v['tax_code'],
                   'unit'=>$v['unit'],
                   'goods_code_short'=>$v['goods_code_short'],
                    );
                 }
                 unset($bar_arr);
                 $i++;
        }
            $this->barcode = array_unique(array_column($data,'barcode'));
            if(empty($this->barcode)){
                if(!empty($faild)){
                   $file_name = $this->create_import_fail_files($faild, 'goods_tax_import');
                   $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                   $message = "数据导入失败，失败信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
                }else{
                   $message =  '没有找到这些商品编码对应的条形码';
                }
                return $this->format_ret('-1', '',$message);
            }
            $use_bar = $this->get_use_barcode();//已使用条码
            $barcode_arr = $this->get_barcode();
            foreach ($data as $val) {
                $r = $this->check_goods_code_data($val,$tax_arr,$use_bar,$barcode_arr);
                if ($r['status'] == '1') {
                    $tax_data[] = $r['data'];             
                } else {
                    $faild[] = $r['message'];
                }
            }
            $all_num = count($list);
            $success_num = count(array_unique(array_column($tax_data, 'goods_code')));
            $fail_num = $all_num - $success_num;
            $message = '导入成功：' . $success_num . '条';          
            $status = 1;
            if (!empty($faild)) {
                $status= '-1';
                $message .=',' . '失败数量:' . $fail_num;
                $file_name = $this->create_import_fail_files($faild, 'goods_tax_import');
                $url = set_download_csv_url($file_name,array('export_name'=>'error'));
                $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
            }
            if (!empty($tax_data)) {
                $up_str = "tax_code=VALUES(tax_code),unit=VALUES(unit),goods_code_short=VALUES(goods_code_short)";
                $res = $this->insert_multi_duplicate('goods_tax', $tax_data, $up_str);
            }
            $ret['status'] = $status;
            $ret['message'] = $message;
            return $ret;
    }
    
    //校验按商品编码导入
    function check_goods_code_data($data,$tax_arr,$use_bar,$barcode_arr) {
        if(array_key_exists($data['barcode'],$use_bar)){
            $msg = '此商品编码:'.$data['goods_code'].'对应的'.$data['barcode'].'条码原税务编码正在使用';
            return array('status' => -1, 'message' => $msg);
        }
        
        if (in_array($data['tax_code'], $tax_arr)) {
            $msg = '此税务编码正在使用：' .$data['tax_code'];
            return array('status' => -1, 'message' => $msg);
        }
        $sku = explode('_line_', $barcode_arr[$data['barcode']]);
        $d = array(
            'barcode' => $data['barcode'],
            'sku' => $sku[1],
            'goods_code' => isset($data['goods_code']) ? $data['goods_code'] : $sku[0],
            'tax_code' => $data['tax_code'],
            'use_num' => 0,
            'unit' => $data['unit'],
            'goods_code_short' => $data['goods_code_short'],
        );
        return $this->format_ret(1, $d);
        
    }
   
    //导入的条码
    function get_barcode(){
        $sql_values = [];
        $barcode_str = $this->arr_to_in_sql_value($this->barcode, 'barcode', $sql_values);
        $sql = "select CONCAT(goods_code,'_line_',sku) as goods_sku,barcode from goods_sku where barcode in ($barcode_str)";
        $bar_arr = $this->db->get_all($sql,$sql_values);
        $barcode_arr = array_column($bar_arr,'goods_sku', 'barcode');
        return $barcode_arr;
    }
    //已使用的税务
    function get_tax_code() {
        $sql_values = [];
        $tax_code_str = $this->arr_to_in_sql_value($this->tax_code, 'tax_code', $sql_values);
        $sql = "SELECT barcode,tax_code FROM goods_tax WHERE use_num = 1 AND tax_code IN ($tax_code_str)";
        $ret_tax = $this->db->get_all($sql,$sql_values);
        $ret_tax = array_column($ret_tax,'tax_code', 'barcode');
        return $ret_tax;
    }
    //已使用的条码
    function get_use_barcode() {
        $sql_values = [];
        $bar_str = $this->arr_to_in_sql_value($this->barcode, 'barcode', $sql_values);
        $sql = "SELECT barcode,tax_code FROM goods_tax WHERE use_num = 1 AND barcode IN ($bar_str)";
        $use_barcode = $this->db->get_all($sql,$sql_values);
        $use_barcode = array_column($use_barcode,'tax_code', 'barcode');
         return $use_barcode;
    }
    
    //导入的商品编码
    function get_goods_code_list() {
        $sql_values = [];
        $goods_code_str = $this->arr_to_in_sql_value($this->goods_code, 'goods_code', $sql_values);
        $sql = "select CONCAT(IFNULL(barcode,''),'_bc_',sku_id) as barcode,goods_code from goods_sku where goods_code in ($goods_code_str)";
        $goods_code_arr = $this->db->get_all($sql,$sql_values);
        $goods_code_arr = array_column($goods_code_arr,'goods_code', 'barcode');
        return $goods_code_arr;
    }
    
    
    //按条码导入校验
    function set_check_barcode($data,$barcode_arr,$tax_arr,$use_bar,$key) {
        $key += 3;
        $sku = explode('_line_', $barcode_arr[$data['barcode']]);
        if (!array_key_exists($data['barcode'], $barcode_arr)) {
            $msg = '第' . $key . '行系统不存在条形码:' . $data['barcode'];
            return array('status' => -1, 'message' => $msg);
        }
        
        if(array_key_exists($data['barcode'],$use_bar)){
            $msg = '第' . $key . '行此条码:'.$data['barcode'].'的原税务编码正在使用';
            return array('status' => -1, 'message' => $msg);
        }
        
        if (in_array($data['tax_code'], $tax_arr)) {
            $msg = '第' . $key . '行此税务编码正在使用：' .$data['tax_code'];
            return array('status' => -1, 'message' => $msg);
        }
        $d = array(
            'barcode' => $data['barcode'],
            'sku' => $sku[1],
            'goods_code' => isset($data['goods_code']) ? $data['goods_code'] : $sku[0],
            'tax_code' => $data['tax_code'],
            'use_num' => 0,
            'unit' => $data['unit'],
            'goods_code_short' => $data['goods_code_short'],
        );
        return $this->format_ret(1, $d);
        
    }
    
     /**
     * 判定导入数据是否有效
     * @param type $row_data 行数据
     * @return true 有效 false 无效
     */
    function is_valid_excel_data($row_data, $key,$type) {
        $key += 3;
        if($type == 'do_barcode'){
            if ($row_data['barcode'] == '') {
                $err = '第' . $key . '行条形码不能为空;';
                return $err;
            }
        }
        if($type == 'do_goods_code'){
            if ($row_data['goods_code'] == '') {
                $err = '第' . $key . '行商品编码不能为空;';
                return $err;
            }
        }
        if ($row_data['tax_code'] == '') {
            $err = '第' . $key . '行税收分类编码不能为空;';
            return $err;
        }
        if ($row_data['goods_code_short'] == '') {
            $err = '第' . $key . '行商品编码简称不能为空;';
            return $err;
        }
        return $this->format_ret(1);
    }

    
    function create_import_fail_files($msg_arr, $name) {
        $fail_top = array('错误信息');
        $file_str = implode(",", $fail_top) . "\n";
        foreach ($msg_arr as $key => $val) {
            $file_str .= $val . "\r\n";
        }
        $filename = md5($name . time());
        $file_path = ROOT_PATH . CTX()->app_name . "/temp/export/" . $filename . ".csv";
        file_put_contents($file_path, iconv('utf-8', 'gbk', $file_str), FILE_APPEND);
        return $filename;
    }
    /**
     * 检验导入的商品编码在商品表中是否存在
     * @param type $goods_code
     * @return type
     */
    function check_base_goods_code($goods_code) {
        $sql = "SELECT 1 FROM `base_goods` where goods_code = :goods_code";
        $ret = $this->db->getOne($sql, array('goods_code' => $goods_code));
        return $ret;
    }
    
    
    //编辑
    function do_edit($tax_id,$up_arr){
        $sql = "SELECT 1 from goods_tax WHERE use_num = 1 AND tax_code = :tax_code";
        $tax_data = $this->db->getRow($sql, array(':tax_code' => $up_arr['tax_code']));
        if(!empty($tax_data)){
            return $this->format_ret(-1, '','此税务编码在业务中已使用');
        }
        $param = [
            'tax_code' => $up_arr['tax_code'],
            'unit' => $up_arr['unit'],
            'goods_code_short' => $up_arr['goods_code_short'],
        ];
        $this->begin_trans();
        $res = $this->update($param, array('tax_id' => $tax_id));
        if($res['status']<0){
           $this->rollback();
           return $res;
        }
        $this->commit();
        return $res;
    }
    
    function get_by_id($id) {
        return $this->get_row(array('tax_id' => $id));
    }
    
    //删除税务编码
    function do_delete($tax_id) {
        $sql = "select use_num from goods_tax where tax_id = :tax_id";
        $use_num = $this->db->getRow($sql, array('tax_id' => $tax_id));
        if($use_num['use_num'] == '1'){
            return $this->format_ret(-1, '','此税务编码在业务中已使用');
        }
        $ret = parent::delete(array('tax_id'=>$tax_id));
	return $ret;
    }
}