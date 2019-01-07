<?php

require_model('tb/TbModel');

/**
 * 已开票
 */
class WeipinhuijitGoodsModel extends TbModel {

    /**
     * @todo 根据条件查询数据
     */
    function get_by_page($filter) {
        if (isset($filter['keyword_type']) && $filter['keyword_type'] !== '') {
            $filter[$filter['keyword_type']] = trim($filter['keyword_goods_value']);
        }
        $sql_main = "FROM api_goods_sku r1 LEFT JOIN api_goods r2 on r1.goods_from_id = r2.goods_from_id LEFT JOIN api_weipinhuijit_goods r3 ON r1.goods_barcode=r3.barcode WHERE r1.source='weipinhui'";
        $sql_values = array();
        //过滤未开启店铺商品
        $shop_code_arr = load_model('base/ShopModel')->get_wepinhuijit_shop();
        //没有任何店铺权限时 $shop_code_arr='' 查出了所有数据
        if (!empty($shop_code_arr)) {
            $shop_code_new = array_map('array_shift', $shop_code_arr);
            $shop_code_str = deal_array_with_quote($shop_code_new);       
            $sql_main .= " AND (r1.shop_code in ({$shop_code_str}) )";
        }else{
            $sql_main .= " AND 1=2 ";
        }     
        // 商品编码
        if (isset($filter['goods_barcode']) && $filter['goods_barcode'] != '') {
            $sql_main .= " AND (r1.goods_barcode=:goods_barcode )";
            $sql_values[':goods_barcode'] = $filter['goods_barcode'];
        }
        //店铺
        if (isset($filter['shop_code']) && $filter['shop_code'] != '' && !is_array($filter['shop_code'])) {
            $shop_code_str = deal_strs_with_quote($filter['shop_code']);
            $sql_main .= " AND (r1.shop_code in ({$shop_code_str}) )";
        }
        //是否允许同步
        if (isset($filter['is_sync_inv']) && $filter['is_sync_inv'] != '') {
            $sql_main .= " AND (r1.is_allow_sync_inv=:is_allow_sync_inv )";
            $sql_values[':is_allow_sync_inv'] = $filter['is_sync_inv'];
        }
        //平台状态
        if (isset($filter['status']) && $filter['status'] != '') {
            $sql_main .= " AND (r1.status=:status )";
            $sql_values[':status'] = $filter['status'];
        }
        //货号
        if(isset($filter['goods_from_id']) && $filter['goods_from_id'] != '') {
            $sql_main .= " AND (r1.goods_from_id LIKE :goods_from_id )";
            $sql_values[':goods_from_id'] = '%' . $filter['goods_from_id'] . '%';
        }

        //最后同步时间
        if (!empty($filter['latest_update_time_start'])) {
            $sql_main .= " AND r1.inv_update_time >= :latest_update_time_start ";
            $sql_values[':latest_update_time_start'] = $filter['latest_update_time_start'];
        }
        if (!empty($filter['latest_update_time_end'])) {
            $sql_main .= " AND r1.inv_update_time <= :latest_update_time_end ";
            $sql_values[':latest_update_time_end'] = $filter['latest_update_time_end'];
        }
        $select = 'r1.*,r2.goods_name,r2.api_goods_id,r3.num AS weipinhui_num,r3.last_sync_inv_num AS weipinhui_last_sync_inv_num,r3.warehouse,r3.inv_up_time AS weipinhui_inv_up_time,r3.current_hold';
        $sql_main .= " GROUP BY r1.shop_code,r1.goods_barcode,r3.warehouse";
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select, true);
        $shop_arr = array_unique(array_column($data['data'], 'shop_code'));
        $shop_api = array();
        if (!empty($shop_arr)) {
            $shop_params = load_model('base/ShopApiModel')->get_shop_api_info($shop_arr);
            $shop_api = $shop_params['data'];
        }
        foreach ($data['data'] as &$value) {
            $arr = load_model('base/ShopModel')->get_by_field('shop_code',$value['shop_code'],'shop_name');
            $value['shop_name']=$arr['data']['shop_name'];
            $value['status_name'] = ($value['status'] == 0) ? '未售' : '在售';
            $sql_join = '';
            if (isset($shop_api[$value['shop_code']]['api_arr']['co_mode']) && $shop_api[$value['shop_code']]['api_arr']['co_mode'] == '普通JIT') {
                $sql_join=" AND warehouse='{$value['warehouse']}' ";
            }
            $sql="select sum(amount) from api_weipinhuijit_order_detail where 1 {$sql_join} and  barcode='{$value['goods_barcode']}' and status=1 group by barcode";
            $sum=$this->db->getOne($sql);
            $value['warehouse_name'] = oms_tb_val('api_weipinhuijit_warehouse', 'warehouse_name', array('warehouse_code' => $value['warehouse']));
            $value['order_item_sum'] = empty($sum) ? 0 : $sum;
            $value['last_sync_inv_num'] = $value['last_sync_inv_num'] == -1 ? '' : $value['last_sync_inv_num'];
            $value['is_allow_sync_inv_name'] = $value['is_allow_sync_inv'] == 0 ? '不允许' : '允许';
            $value['weipinhui_last_sync_inv_num'] = $value['weipinhui_last_sync_inv_num'] == -1 ? '' : $value['weipinhui_last_sync_inv_num'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;

        return $this->format_ret($ret_status, $ret_data);
    }

    function import_stock_data($shop_code, $file){
        if (empty($shop_code)) {
            return $this->format_ret(-1, '', '请先选择同步店铺');
        }
        $detail = array();
        $this->read_csv($file, $detail);
        $err_msg = array();
        $adjust_data = array();
        foreach ($detail as $val) {
            $adjust_data[$val['barcode']] = array(
                'barcode' => trim($val['barcode']),
                'quantity' => trim($val['num']),
                'warehouse'=>trim($val['warehouse'])
            );
        }
        $status = 1;
        if (!empty($adjust_data)) {
            //同步调整平台库存
            $request['shop_code']=$shop_code;
            $request['adjust_data']=$adjust_data;
            $request['act']='weipinhuijit_import_stock';
            $sync_result = $this->quantity_sync($request);

            if (!empty($sync_result['err_msg'])) {
                $adjust_data = array_column($detail, 'num', 'barcode');
                foreach ($sync_result['err_msg'] as $val) {
                    $err_msg[] = array(
                        'barcode' => $val['barcode'],
                        'num' => $adjust_data[$val['barcode']],
                        'warehouse'=>$val['warehouse'],
                        'error' => $val['message']
                    );
                }
            }
        } else {
            $status = -1;
        }

        $err_num = $sync_result['err_num'];
        $success_num = $sync_result['success_num'];
        $top=array('商品条形码','商品库存','仓库编码','同步失败原因');
        $message = '导入成功：' . $success_num;
        if ($err_num > 0) {
            $message .= '；' . '失败数量：' . $err_num;
            $message .=$this->create_fail_file($err_msg,$top,'failed_barcode');
        }

        return $this->format_ret($status, '', $message);
    }


    function quantity_sync($request){
            $fun = 'weipinhuijit_api/import_item_quantity_sync';
            $success_num=0;$error_num=0;
            $error_msg='';
            foreach ($request['adjust_data'] as $key=>$value) {
                $sql = "select shop_code,goods_barcode,sku_id,goods_from_id from api_goods_sku  where goods_barcode='{$value['barcode']}' and shop_code='{$request['shop_code']}' and status=1 and is_allow_sync_inv=1 ";
                $sku = $this->db->get_row($sql);
                if (empty($sku)) {
                    $error_msg [$key]['barcode']= $value['barcode'];
                    $error_msg [$key]['message']= '条码不存在或不允许库存同步';
                    $error_msg [$key]['warehouse']= $value['warehouse'];
                    $error_num++;
                    continue;
                }
                $sku['quantity'] = $value['quantity'];
                $sku['warehouse'] = $value['warehouse'];
                $result = load_model('sys/EfastApiModel')->request_api($fun, $sku);
                $resp_data=$result['resp_data'];
                if($resp_data['code']==1){
                    $sql="update api_goods_sku set is_allow_sync_inv=0 where goods_barcode='{$value['barcode']}'";
                    $this->db->query($sql);
                    $success_num++;
                }else{
                    $error_msg [$key]['barcode']= $value['barcode'];
                    $error_msg [$key]['message']= $resp_data['msg'];
                    $error_msg [$key]['warehouse']= $value['warehouse'];
                    $error_num++;
                }
            }
            $ret['success_num']=$success_num;
            $ret['err_num']=$error_num;
            $ret['err_msg']=$error_msg;
        return $ret;
    }
    /**
     * 读取文件，保存到数组中
     */
    function read_csv($file, &$detail) {
        $file = fopen($file, "r");
        $i = 0;
        while (!feof($file)) {
            $row = fgetcsv($file);
            if ($i >= 1) {
                $this->tran_csv($row);
                if (!empty($row[0])) {
                    $d = array();
                    $d['barcode'] = $row[0];
                    $d['num'] = $row[1];
                    $d['warehouse'] = $row[2];
                    $detail[$i] = $d;
                }
            }
            $i++;
        }
        fclose($file);
        return $i;
    }

    /**
     * 创建导出信息csv文件
     * @param array $msg 信息数组
     * @return string 文件地址
     */
    private function create_fail_file($msg, $fail_top, $name) {
        require_lib('csv_util');
        $csv_obj = new execl_csv();
        $file_name = $csv_obj->create_fail_csv_files($fail_top, $msg, $name);
//        $message = "，错误信息<a target=\"_blank\" href=\"?app_act=sys/export_csv/download_csv&file_key={$file_name}&export_name={$name}\" >下载</a>";
        $url = set_download_csv_url($file_name,array('export_name'=>'error'));
        $message .= "，错误信息<a target=\"_blank\" href=\"{$url}\" >下载</a>";
        return $message;
    }



}
