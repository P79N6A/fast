<?php

require_model('tb/TbModel');

class OspReceiptApplyModel extends TbModel {

    function get_table() {
        return 'osp_receipt_apply';
    }

    /**
     *    @todo 添加申请发票记录
     */
    function add($request) {
        $request['kh_id'] = CTX()->get_session("kh_id");
        //只保存上传文件的文件名
        $request['kh_licence_img'] = basename($request['kh_licence_img']);
        $request['kh_tax_img'] = basename($request['kh_tax_img']);
        $request['kh_qualification_img'] = basename($request['kh_qualification_img']);
        $request['country'] = 1;
        $request['create_time'] = date('Y-m-d H:i:s', time());
        //添加记录至发票申请表
        $ret = $this->db->insert($this->table, $request);
        //添加记录至发表主表
        if ($ret) {
            //获取最后一次插入的数据的自增长id
            $apply_id = $this->db->insert_id();
            $apply_info = $this->get_info_by_id($apply_id);
            if (isset($request['pro_num']) && empty($request['pro_num'])) {
                $pro_data = load_model('product/SoonbuyModel')->get_order_byid($request['pro_num']);
                $cp_id = $pro_data['pro_cp_id'];
            }
            $receipt_money = $request['receipt_money'];
            $data = array('receipt_apply_id' => $apply_info['apply_id'], 'kh_id' => $request['kh_id'], 'cp_id' => $cp_id, 'receipt_money' => $receipt_money, 'applied_time' => $apply_info['create_time']);
            $res = $this->insert_exp('osp_receipt', $data);
            return $res;
        } else {
            return $ret;
        }
    }

    /**
     * @todo 修改申请发票资料,重新上传图片时删除原图片
     */
    function update($request) {
        $data = $this->get_info_by_receipt_id($request['receipt_id']);
        //只获取文件名
        $request['kh_licence_img'] = basename($request['kh_licence_img']);
        $request['kh_tax_img'] = basename($request['kh_tax_img']);
        $request['kh_qualification_img'] = basename($request['kh_qualification_img']);
        if ($data['kh_licence_img'] != $request['kh_licence_img']) {
            $this->unlink_img('licenceimg/' . $data['kh_licence_img']);
        }
        if($data['kh_tax_img'] != $request['kh_tax_img']){
             $this->unlink('taximg/' . $data['kh_tax_img']);
        }
        if($data['kh_qualification_img'] != $request['kh_qualification_img']){
             $this->unlink('qualificationimg/' . $data['kh_qualification_img']);
        }
        $where = array('apply_id' => $data['receipt_apply_id']);
        $ret = $this->db->update($this->table, $request, $where);
        if ($ret) {
            $res = $this->db->update('osp_receipt', $request, array('receipt_id' => $request['receipt_id']));
        }
        return $res;
    }

    /**
     * @todo 通过apply_id获取申请发票资料详情
     */
    function get_info_by_id($apply_id) {
        $sql = "SELECT * FROM {$this->table} WHERE apply_id = :apply_id";
        $sql_value = array(":apply_id" => $apply_id);
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }

    /**
     * @todo 通过receipt_id获取发票资料详情
     */
    function get_info_by_receipt_id($receipt_id) {
        $sql = "SELECT * FROM {$this->table} ra
                        LEFT JOIN osp_receipt r ON ra.apply_id=r.receipt_apply_id 
                        WHERE receipt_id=:receipt_id";
        $sql_value = array(":receipt_id" => $receipt_id);
        $ret = $this->db->get_row($sql, $sql_value);
        return $ret;
    }

    /**
     * @todo 通过receipt_id删除申请发票的资料,同时删除上传的图片
     */
    function delete_info_by_receipt_id($receipt_id) {
        $data = $this->get_info_by_receipt_id($receipt_id);
        $this->unlink_img('licenceimg/' . $data['kh_licence_img']);
        $this->unlink('taximg/' . $data['kh_tax_img']);
        $this->unlink('qualificationimg/' . $data['kh_qualification_img']);
        $ret = parent::delete_exp('osp_receipt_apply', array('apply_id' => $data['apply_id']));
        return $ret;
    }

    /**
     * @todo 图片上传
     */
    function upload_images($request, $upload_files) {
        $app['fmt'] = 'json';
        $files = array();
        $fileInput = 'fileData';
        $dir = ROOT_PATH . 'weborder/web/' . $request['path'] . '/';
        if (!file_exists($dir)) {
            mkdir($dir);
        }
        $type = $_POST['type'];

        $isExceedSize = false;
        $files_name_arr = array($fileInput);
        $is_max = 0;
        $is_file_type = 0;
        $file_type = array('jpg', 'png', 'gif');
        $upload_max_filesize = 2097152;
        foreach ($files_name_arr as $k => $v) {
            $pic = $upload_files[$v];
            if (!isset($pic['tmp_name']) || empty($pic['tmp_name'])) {
                $is_max = 1;
                continue;
            }
            $file_ext = $this->get_file_extension($pic['name']);
            if (!in_array($file_ext, $file_type)) {
                $is_file_type = 1;
                continue;
            }
            $isExceedSize = $pic['size'] > $upload_max_filesize;
            if (!$isExceedSize) {
                if (file_exists($dir . $pic['name'])) {
                    @unlink($dir . $pic['name']);
                }
                $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
                $result = move_uploaded_file($pic['tmp_name'], $dir . $new_file_name);
            }
        }
        if ($is_max) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', substr(ini_get('upload_max_filesize'), 0, -1) * 1024, lang('upload_msg_maxSize'))
            );
        } else if ($is_file_type) {
            return array(
                'status' => 0,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'msg' => str_replace('{0}', implode(',', $file_type), lang('upload_msg_ext'))
            );
        } else if (!$isExceedSize && $result) {
            return array(
                'status' => 1,
                'type' => $type,
                'name' => $upload_files[$fileInput]['name'],
                'url' => $dir . $new_file_name
            );
        } else if ($isExceedSize) {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => str_replace('{0}', $upload_max_filesize / 1024, lang('upload_msg_maxSize'))
            );
        } else {
            return array(
                'status' => 0,
                'type' => $type,
                'msg' => "未知错误！" . $result
            );
        }
    }

    /**
     * 方法名       get_file_extension
     * 功能描述     获取文件扩展名
     * @author      BaiSon PHP R&D
     * @param       string $file
     * @return      string $file_ext_name [扩展名]
     */
    function get_file_extension($file) {
        $temp_arr = explode('.', $file);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        return $file_ext;
    }

    /**
     * @todo 图片存在时删除图片
     */
    function unlink_img($path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }

}
