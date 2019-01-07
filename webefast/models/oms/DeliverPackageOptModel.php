<?php

require_model('tb/TbModel');

/**
 * 多包裹验货操作（后置打单）
 *
 * @author WMH
 */
class DeliverPackageOptModel extends TbModel {

    protected $table = 'oms_deliver_record_package';
    protected $detail_table = 'oms_deliver_package_detail';

    /**
     * 批量获取菜鸟电子面单号
     * @param string $sell_code 订单号
     * @param int $package_num 包裹数量
     * @return array
     */
    public function get_waybill_multi($sell_code, $package_num) {
        $ret_delivery = load_model('oms/DeliverPackageModel')->check_sell_record($sell_code);
        if ($ret_delivery['status'] < 1) {
            return $ret_delivery;
        }
        $deliver_record_id = $ret_delivery['data']['deliver_record_id'];
        $waves_record_id = $ret_delivery['data']['waves_record_id'];
        unset($ret_delivery);

        $where = array('sell_record_code' => $sell_code, 'is_cancel' => 0);
        $obj_deliver = load_model('oms/DeliverRecordModel');

        for ($i = 1; $i <= $package_num; $i++) {
            $this->update_exp('oms_deliver_record', array('package_no' => $i), $where);
            $ret = $obj_deliver->cn_wlb_waybill_get($waves_record_id, $deliver_record_id);
            //获取失败
            if ($ret['status'] != 1) {
                return $ret;
            }
        }

        $this->update(array('is_multi_examine' => 1), array('sell_record_code' => $sell_code, 'waves_record_id' => $waves_record_id));
        $this->update_exp('oms_deliver_record', array('package_no' => 1), array('sell_record_code' => $sell_code, 'is_cancel' => 0));

        $package_data = load_model('oms/DeliverPackageModel')->get_package_data($sell_code);
        return $this->format_ret(1, $package_data);
    }

    /**
     * 单个获取菜鸟电子面单号
     * @param string $sell_code 订单号
     * @param int $package_no 包裹号
     * @return array
     */
    public function get_waybill_single($sell_code, $package_no) {
        $ret_delivery = load_model('oms/DeliverPackageModel')->check_sell_record($sell_code);
        if ($ret_delivery['status'] < 1) {
            return $ret_delivery;
        }
        $deliver_record_id = $ret_delivery['data']['deliver_record_id'];
        $waves_record_id = $ret_delivery['data']['waves_record_id'];

        $this->update_exp('oms_deliver_record', array('package_no' => $package_no), array('sell_record_code' => $sell_code, 'is_cancel' => 0));

        $ret = load_model('oms/DeliverRecordModel')->cn_wlb_waybill_get($waves_record_id, $deliver_record_id);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '获取电子面单号失败');
        }

//        $package_data = load_model('oms/DeliverPackageModel')->get_package_data($sell_code);
        return $this->format_ret(1);
    }

    /**
     * 创建包裹单，非预置包裹使用
     * @param array $deliver_data 发货数据
     * @param array $package_no 当前扫描包裹号
     * @param int $is_update_package 是否更新订单当前包裹
     * @return array
     */
    private function create_package_record($deliver_data, &$package_no, $is_update_package) {
        $package_data = get_array_vars($deliver_data, array('sell_record_code', 'express_code', 'waves_record_id'));
        if ($package_no == 0) {
            $package_no = 1;
        } else {
            $no_packet = load_model('oms/DeliverPackageModel')->get_no_packet_first_package($package_data);
            if ($no_packet == FALSE) {
                $package_no += 1;
            } else {
                return $this->format_ret(1);
            }
        }

        $package_data['package_no'] = $package_no;
        $package_data['is_multi_examine'] = 1;
        $ret = $this->insert_dup($package_data);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '创建包裹失败');
        }

        if ($is_update_package == 1) {
            $ret = $this->update_exp('oms_deliver_record', array('package_no' => $package_no), array('sell_record_code' => $package_data['sell_record_code'], 'is_cancel' => 0));
            if ($ret['status'] < 1) {
                return $this->format_ret(-1, '', '创建包裹失败');
            }
        }

        return $this->format_ret(1);
    }

    /**
     * 扫描条码
     * @param array $params 参数
     * @return array
     */
    public function scan_barcode($params) {
        $deliver_record_id = $params['deliver_record_id'];
        $waves_record_id = $params['waves_record_id'];
        $sell_code = $params['sell_record_code'];
        $barcode = $params['barcode'];
        $package_no = &$params['package_no'];

        $this->begin_trans();
        $ret_deliver = load_model('oms/DeliverPackageModel')->check_sell_record($sell_code);
        if ($ret_deliver['status'] < 1) {
            $this->rollback();
            return $ret_deliver;
        }
        if ($params['pre_set_num'] == 0) {
            $ret = $this->create_package_record($ret_deliver['data'], $package_no, 1);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
        }

        $ret_package = $this->get_row(array('sell_record_code' => $sell_code, 'package_no' => $package_no, 'waves_record_id' => $waves_record_id));
        $ret_package = $ret_package['data'];
        if (empty($ret_package)) {
            return $this->format_ret(-1, '', "包裹{$package_no} 不存在");
        }
        if ($ret_package['packet_status']) {
            return $this->format_ret(-1, '', "包裹已封包");
        }
        $params['package_record_id'] = $ret_package['package_record_id'];
        unset($ret_package, $ret_deliver);

        //扫描条码识别
        $ret_sku = $this->get_barcode_ident($sell_code, $barcode);
        if ($ret_sku['status'] < 1) {
            return $ret_sku;
        }

        //添加唯一码跟踪中间表记录
        if ($ret_sku['data']['unique_flag'] == 1) {
            $unique_data = array('sell_record_code' => $sell_code, 'unique_code' => $barcode, 'barcode_type' => 'unique_code');
            $ret = load_model('oms/UniqueCodeScanTemporaryLogModel')->insert($unique_data);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $this->format_ret(-1, '', '添加唯一码跟踪记录失败');
            }
        }

        $sku = $ret_sku['data']['sku'];
        $sql = "SELECT deliver_record_detail_id,sku,num,scan_num FROM oms_deliver_record_detail 
                WHERE deliver_record_id=:id AND sku=:sku ORDER BY is_gift ASC";
        $deliver_detail = $this->db->get_all($sql, array(':id' => $deliver_record_id, ':sku' => $sku));
        if (empty($deliver_detail)) {
            $this->rollback();
            return $this->format_ret(-1, '', '发货订单不存在此商品');
        }

        //扫描数量校验，更新发货单扫描数量
        $deliver_detail_id = 0;
        $up_scan_num = 0;
        foreach ($deliver_detail as $row) {
            if ($row['num'] == $row['scan_num']) {
                continue;
            }
            $deliver_detail_id = $row['deliver_record_detail_id'];
            $up_scan_num = $row['scan_num'] + 1;
            break;
        }
        if ($deliver_detail_id == 0) {
            $this->rollback();
            return $this->format_ret(-1, '', '该商品已扫描完毕');
        }
        $ret = $this->update_exp('oms_deliver_record_detail', array('scan_num' => $up_scan_num), array('deliver_record_detail_id' => $deliver_detail_id));
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新发货数据失败');
        }

        //插入及更新包裹明细
        $params['sku'] = $sku;
        $ret = $this->update_package_detail($params);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }
        //回写包裹表数量
        $ret = $this->writeback_package_data($params['package_record_id']);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $ret;
        }

        load_model('oms/SellRecordActionModel')->add_action($sell_code, '扫描出库', '扫描条码：' . $barcode);

        $this->commit();

        $package_data = load_model('oms/DeliverPackageModel')->get_package_data($sell_code);
        return $this->format_ret(1, $package_data, '扫描成功');
    }

    /**
     * 条码识别
     * @param string $sell_record_code 订单号
     * @param string $barocde 商品条码
     * @return array
     */
    public function get_barcode_ident($sell_record_code, $barocde) {
        $unique_flag = 0;
        $sku = '';
        $sku_data = load_model('prm/SkuModel')->convert_scan_barcode($barocde, 1, 1);
        if (empty($sku_data)) {
            //开启唯一码，识别唯一码
            $unique_arr = load_model('sys/SysParamsModel')->get_val_by_code(array('unique_status'));
            if ($unique_arr['unique_status'] != 1) {
                return $this->format_ret(-1, '', '条码不存在');
            }
            $sql = "SELECT `sku`,`status` FROM goods_unique_code WHERE unique_code=:unique_code";
            $ret_unique = $this->db->get_row($sql, array('unique_code' => $barocde));
            if (empty($ret_unique)) {
                return $this->format_ret(-1, '', '条码不存在');
            } else if ($ret_unique['status'] == 1) {
                return $this->format_ret(-1, '', '唯一码不可用');
            }
            //判断唯一码是否已使用
            $sql = "SELECT * FROM unique_code_scan_temporary_log WHERE barcode_type='unique_code' AND unique_code=:unique_code AND sell_record_code=:code";
            $unique = $this->db->get_all($sql, array(':code' => $sell_record_code, ':unique_code' => $barocde));
            if (!empty($unique)) {
                return $this->format_ret(-1, '', '该唯一码已被使用');
            }
            $sku = $ret_unique['sku'];

            $unique_flag = 1;
        } else {
            $sku = $sku_data['sku'];
        }

        return $this->format_ret(1, array('sku' => $sku, 'unique_flag' => $unique_flag));
    }

    /**
     * 回写包裹表数据
     * @param int $package_record_id 包裹ID
     * @return array
     */
    private function writeback_package_data($package_record_id) {
        $sql = "UPDATE {$this->table} AS rp,(
                    SELECT package_record_id,SUM(goods_num) AS goods_num,SUM(scan_num) AS scan_num 
                    FROM {$this->detail_table} WHERE package_record_id=:id ) AS pd 
                SET rp.goods_num=pd.goods_num,rp.scan_num=pd.scan_num
                WHERE rp.package_record_id=pd.package_record_id AND rp.package_record_id=:id";
        $ret = $this->query($sql, array(':id' => $package_record_id));
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            return $this->format_ret(-1, '', '更新包裹数据失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 插入或更新包裹明细
     * @param int $package_record_id
     * @param string $sell_code
     * @param string $package_no
     * @param string $sku
     * @return array
     */
    private function update_package_detail($data) {
        $package_data = array(
            'package_record_id' => $data['package_record_id'],
            'sell_record_code' => $data['sell_record_code'],
            'package_no' => $data['package_no'],
            'sku' => $data['sku'],
            'goods_num' => 1,
            'scan_num' => 1,
        );
        $update_str = 'goods_num=VALUES(goods_num)+goods_num,scan_num=VALUES(scan_num)+scan_num';
        $ret = $this->insert_multi_duplicate($this->detail_table, array($package_data), $update_str);
        if ($ret['status'] < 1) {
            return $this->format_ret(-1, '', '更新包裹明细数据失败');
        }
        return $this->format_ret(1);
    }

    /**
     * 包裹封包
     * @param array $params
     * @return array
     */
    public function packet_package($params) {
        $obj_package = load_model('oms/DeliverPackageModel');
        $wh_data = array(
            'sell_record_code' => $params['sell_record_code'],
            'waves_record_id' => $params['waves_record_id'],
            'package_no' => $params['package_no'],
        );
        $ret_deliver = $obj_package->check_sell_record($wh_data['sell_record_code']);
        if ($ret_deliver['status'] < 1) {
            return $ret_deliver;
        }
        $ret_package = $this->get_row($wh_data);
        $ret_package = $ret_package['data'];
        $msg = '封包失败，包裹' . $params['package_no'];
        if (empty($ret_package)) {
            return $this->format_ret(-1, '', $msg . '不存在');
        }
        if ($ret_package['packet_status'] == 1) {
            return $this->format_ret(-1, '', $msg . '已封包');
        }
        $detail_count = $obj_package->check_empty_package($params['sell_record_code'], $params['package_no']);
        if ($detail_count < 1) {
            return $this->format_ret(-1, '', $msg . '商品为空');
        }

        $this->begin_trans();

        if ($params['pre_set_num'] == 0) {
            $ret = $this->get_waybill_single($params['sell_record_code'], $params['package_no']);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
        }

        $up_data = array('packet_status' => 1, 'packet_time' => time());
        $ret = $this->update($up_data, $wh_data);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '封包失败');
        }

        //校验订单是否扫描完毕
        $status = 1;
        $deliver_data = $ret_deliver['data'];
        $sql = 'SELECT COUNT(1) FROM oms_deliver_record_detail WHERE scan_num<num AND deliver_record_id=:id';
        $ret = $this->db->get_value($sql, array(':id' => $deliver_data['deliver_record_id']));
        if ($ret < 1) {
            $status = 2;
        }

        //非预置包裹时创建下一个扫描包裹
        if ($params['pre_set_num'] == 0 && $status == 1) {
            $ret = $this->create_package_record($deliver_data, $params['package_no'], 0);
            if ($ret['status'] < 1) {
                $this->rollback();
                return $ret;
            }
        }

        $package_data = $obj_package->get_package_data($params['sell_record_code']);
        $msg = "包裹{$wh_data['package_no']}封包完成";

        //更新订单快递数据
        $ret_package = $obj_package->get_package_record($wh_data['sell_record_code'], $wh_data['package_no']);
        $up_data = get_array_vars($ret_package, array('express_no', 'express_data'));
        $ret = $this->update_exp('oms_sell_record', $up_data, array('sell_record_code' => $params['sell_record_code']));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新订单快递数据失败');
        }
        $up_data['package_no'] = $ret_package['package_no'];
        $ret = $this->update_exp('oms_deliver_record', $up_data, array('sell_record_code' => $params['sell_record_code'], 'is_cancel' => 0));
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '更新订单快递数据失败');
        }
        
        //更新下一个扫描包裹
//        $ret = $this->update_exp('oms_deliver_record', array('package_no' => $package_data['package_no']), array('sell_record_code' => $params['sell_record_code'], 'is_cancel' => 0));
//        if ($ret['status'] < 1) {
//            $this->rollback();
//            return $this->format_ret(-1, '', '获取下一个扫描包裹失败');
//        }
        $this->commit();

        return $this->format_ret($status, $package_data, $msg);
    }

    /**
     * 删除空包裹
     * @param array $params
     * @return array
     */
    public function delete_package($params) {
        $obj_package = load_model('oms/DeliverPackageModel');
        $ret_record = $obj_package->check_sell_record($params['sell_record_code']);
        if ($ret_record['status'] < 1) {
            return $ret_record;
        }
        $ret_exists = $obj_package->check_package_exists($params['sell_record_code'], $params['package_no']);
        if ($ret_exists < 1) {
            return $this->format_ret(-1, '', "包裹{$params['package_no']}不存在");
        }
        $detail_count = $obj_package->check_empty_package($params['sell_record_code'], $params['package_no']);
        if ($detail_count > 0) {
            return $this->format_ret(-1, '', "包裹{$params['package_no']}不为空，不能删除");
        }

        $this->update_exp('oms_deliver_record', array('express_data' => $ret_exists['express_data'], 'express_no' => $ret_exists['express_no']), array('sell_record_code' => $ret_exists['sell_record_code']));

        //取消菜鸟电子面单号
        $obj_deliver = load_model('oms/DeliverRecordModel');
        $ret = $obj_deliver->cancle_tb_wlb_waybil($params);
        if ($ret['status'] < 1) {
            return $ret;
        }

        $where = array('sell_record_code' => $params['sell_record_code'], 'package_no' => $params['package_no'], 'waves_record_id' => $params['waves_record_id']);
        $ret = $this->delete($where);
        if ($ret['status'] < 1 || $this->affected_rows() != 1) {
            return $this->format_ret(-1, '', "删除包裹{$params['package_no']}失败");
        }

        $package_data = $obj_package->get_package_data($params['sell_record_code']);
        $this->update_exp('oms_deliver_record', array('package_no' => $package_data['package_no']), array('sell_record_code' => $params['sell_record_code'], 'is_cancel' => 0));

        return $this->format_ret(1, $package_data, "删除包裹{$params['package_no']}成功");
    }

    /**
     * 重置当前包裹扫描数据
     * @param array $params
     * @return array
     */
    public function clear_curr_package($params) {
        $obj_package = load_model('oms/DeliverPackageModel');
        $ret_record = $obj_package->check_sell_record($params['sell_record_code']);
        if ($ret_record['status'] < 1) {
            return $ret_record;
        }
        $ret_package = $obj_package->get_package_record($params['sell_record_code'], $params['package_no']);
        if (empty($ret_package)) {
            return $this->format_ret(-1, '', "包裹{$params['package_no']}不存在");
        }
        if ($ret_package['packet_status'] == 1) {
            return $this->format_ret(-1, '', "包裹{$params['package_no']}已封包");
        }
        $ret_detail = $obj_package->get_package_detail($params['sell_record_code'], $params['package_no']);
        if (empty($ret_detail)) {
            return $this->format_ret(-1, '', "包裹{$params['package_no']}明细为空，无需重置");
        }
        $this->begin_trans();
        //清空包裹明细
        $where = array('sell_record_code' => $params['sell_record_code'], 'package_no' => $params['package_no']);
        $ret = $this->delete_exp($this->detail_table, $where);
        if ($ret === FALSE) {
            $this->rollback();
            return $this->format_ret(-1, '', "清空包裹{$params['package_no']}明细失败");
        }
        //更新包裹主表数量
        $where['waves_record_id'] = $params['waves_record_id'];
        $ret = $this->update(array('goods_num' => 0, 'scan_num' => 0), $where);
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', "包裹{$params['package_no']}数量维护失败");
        }
        //更新发货明细扫描数量
        $sql = 'SELECT deliver_record_detail_id,deliver_record_id,sell_record_code,deal_code,waves_record_id,sku,scan_num,is_gift
                FROM oms_deliver_record_detail WHERE deliver_record_id=:id AND scan_num<>0 ORDER BY is_gift DESC';
        $deliver_detail = $this->db->get_all($sql, array(':id' => $params['deliver_record_id']));
        $sku_scan = array_column($ret_detail, 'scan_num', 'sku');
        $up_detail = array();
        foreach ($deliver_detail as $row) {
            $package_scan_num = &$sku_scan[$row['sku']];
            if ($package_scan_num == 0) {
                continue;
            }
            $diff_num = $package_scan_num - $row['scan_num'];
            if ($diff_num <= 0) {
                $row['scan_num'] = abs($diff_num);
                $package_scan_num = 0;
            } else {
                $package_scan_num = $diff_num;
            }
            $up_detail[] = $row;
        }
        $ret = $this->insert_multi_duplicate('oms_deliver_record_detail', $up_detail, 'scan_num=VALUES(scan_num)');
        if ($ret['status'] < 1) {
            $this->rollback();
            return $this->format_ret(-1, '', '重置发货扫描数据失败');
        }
        load_model('oms/SellRecordActionModel')->add_action($params['sell_record_code'], '重置扫描', '多包裹验货重置发货扫描数据');

        $this->commit();

        $package_data = $obj_package->get_package_data($params['sell_record_code']);
        return $this->format_ret(1, $package_data, "包裹{$params['package_no']}扫描数据已重置");
    }

    /**
     * 发货
     * @param string $params
     * @return array
     */
    function delivery($params) {
        //订单的所有包裹必须已封包
        $sql = "SELECT 1 FROM oms_deliver_record_package WHERE sell_record_code=:sell_record_code AND packet_status=0";
        $sql_value[':sell_record_code'] = $params['sell_record_code'];
        $package_ret = $this->db->get_row($sql, $sql_value);
        if (!empty($package_ret)) {
            return $this->format_ret('-1', '', '存在未封包的包裹！');
        }
        $sell_obj = load_model('oms/SellRecordOptModel');
        $record = $sell_obj->get_record_by_code($params['sell_record_code']);
        $detail = $sell_obj->get_detail_list_by_code($params['sell_record_code']);
        $sys_user = $sell_obj->sys_user();
        $ret = $sell_obj->sell_record_send($record, $detail, $sys_user, 'scan', 1);
        if ($ret['status'] < 1 && !empty($ret['data'])) {
            $ret['message'] .= $ret['data'];
        } else if ($ret['status'] == 1) {
            $ret['message'] = "发货成功";
        }

        $this->clear_scan($params);

        return $ret;
    }

    /**
     * 发货后清除扫描记录
     * @param array $params
     * @return type
     */
    function clear_scan($params) {
        //清除唯一码跟踪中间表数据
        $sql = "DELETE FROM unique_code_scan_temporary_log WHERE barcode_type='unique_code' AND sell_record_code=:code";
        $this->query($sql, array(':code' => $params['sell_record_code']));

        //重置发货单明细扫描数量
        $sql = "UPDATE oms_deliver_record_detail SET scan_num=0 WHERE deliver_record_id=:id";
        $this->query($sql, array(':id' => $params['deliver_record_id']));

        load_model('oms/SellRecordActionModel')->add_action($params['sell_record_code'], '清除扫描记录', '发货后清除多包裹扫描记录');
    }

}
