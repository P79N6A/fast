<?php

/**
 *

  订单快递适配策略相关业务
 *
 * @author huanghy
 *
 */
require_model('tb/TbModel');
require_lang('crm');
require_lib('util/oms_util', true);

class PolicyExpressAreaModel extends TbModel {

    function get_table() {
        return 'op_policy_express_area';
    }

    /*
     * 根据条件查询数据
     */

    function get_by_page($filter) {

        $sql_join = "";
        $sql_main = "FROM {$this->table} ol  left join base_express be  on  ol.express_code = be.express_code  WHERE 1";
        $sql_values = array();

        if (isset($filter['pid']) && $filter['pid'] != '') {
            $sql_main .= " AND pid = :pid ";
            $sql_values[':pid'] = $filter['pid'];
        }

        $select = 'be.*,ol.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_values, $select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * @param $id
     * @return array
     */
    function get_by_id($id) {

        return $this->get_row(array('policy_express_id' => $id));
    }

    /**
     * @param $code
     * @return array
     */
    function get_by_code($code) {
        return $this->get_row(array('customer_code' => $code));
    }

    /*
     * 添加新纪录
     */

    function insert($express_area) {
//		$status = $this->valid($express_strategy);
//		if ($status < 1) {
//			return $this->format_ret($status);
//		}
//		
//		$ret = $this->is_exists($customer['policy_express_code']);
//
//		if (!empty($ret['data'])) {
//			return $this->format_ret(CUSTOMER_ERROR_UNIQUE_NAME);
//		}
        $data = parent::insert($express_area);
//		$info['policy_express_id'] = $ret['data'];
//		return   $this->format_ret(1,$info);
        $ret_status = OP_SUCCESS;
        return $this->format_ret($ret_status);
    }

//	/*
//	 * 服务器端验证
//	 */
//	private function valid($data, $is_edit = false) {
//		if (!isset($data['customer_name']) || !valid_input($data['customer_name'], 'required')) return CUSTOMER_ERROR_NAME;
//		return 1;
//	}
//	
//	function is_exists($value, $field_name='customer_name') {
//		$ret = parent::get_row(array($field_name=>$value));
//		return $ret;
//	}

    /**
     * 更新
     */
    function save($policy_express_id, $idstr) {
        $this->delete(array('pid' => $policy_express_id));

        $r1 = array();
        $ids = explode(',', $idstr);

        if (empty($ids)) {
            return array('status' => '1', 'message' => '更新成功');
        }

        foreach ($ids as $id) {
            // 保存非叶子节点, 仅用于tree控件显示选中状态
            //$r[$id] = array('pid'=>$policy_express_id, 'area_id'=>$id);
            $r = array();
            //$rt[$id] = array('pid'=>$policy_express_id, 'area_id'=>$id);
            //$ifFull = false;

            /* $sql = "select * from base_area where id = :id";
              $area = $this->db->get_row($sql, array('id'=>$id));
              if(empty($area)) continue; */

            //$this->get_sub_area($policy_express_id, $area['id'], $area['type'], $rt, $ifFull, $r1);

            $this->get_sub($policy_express_id, $id, $r, $r1);
        }
        return $this->insert_multi($r1);
    }

    function save_area($param) {
        $ret = load_model('crm/ExpressStrategyModel')->is_exists($param['policy_express_id'], 'policy_express_id');
        if (empty($ret['data'])) {
            return $this->format_ret(-1, '', '该策略不存在，请检查重试');
        }

        $data = $this->get_area_data_by_type($param);
        $sql_values = [':pid' => $param['policy_express_id']];
        if ($param['checked'] == 1) {
            $this->insert_multi($data);
        } else {
            $area_id_arr = array();
            foreach ($data as $val) {
                $area_id_arr[] = $val['area_id'];
            }
            $area_id_str = $this->arr_to_in_sql_value($area_id_arr, 'area_id', $sql_values);
            $sql = "DELETE FROM {$this->table} WHERE pid=:pid AND area_id IN ({$area_id_str})";
            $this->db->query($sql, $sql_values);
        }
        $action_name = '编辑';
        $data = array(
            'pid' => $param['policy_express_id'],
            'action_name' => $action_name,
            'desc' => '修改区域范围'
        );
        load_model('crm/ExpressStrategyLogModel')->insert($data);
        return $this->format_ret(1);
    }

    function get_area_data_by_type($param) {
        $area_id = $param['id'];
        $type = $param['type'];
        if ($param['type'] == 4) {
            $data = array(array('area_id' => $area_id, 'pid' => $param['policy_express_id']));
            return $data;
        }

        $no_select = array('820000', '810000', '710000');
//        $other_area = array('441900000000','442000000000');//东莞市 ，中山市 广东440000
        $no_select_where = implode(',', $no_select);

        $sql = " 1  ";

        switch ($type) {
            case 1:
                $sql .= " AND  parent_id in(select id from base_area where parent_id in(select id from base_area where parent_id='{$area_id}') AND id not in({$no_select_where}) ) ";
                break;
            case 2:
                $sql .= " AND   parent_id in(select id from base_area where parent_id='{$area_id}') ";
                break;
            case 3:
//                $sql.= in_array($area_id,$other_area)?" AND id='{$area_id}' ":" AND parent_id='{$area_id}' ";
                $sql .= " AND parent_id='{$area_id}' ";
                break;
            default :
        }


        if ($type == 1 || $area_id == '440000') {
//            $sql = "( ({$sql}) OR ( id in('".implode("','", $other_area)."') ) )";
            $sql = "( {$sql})";
        }

        if ($param['checked'] == 1) {
            $sql .= "AND id not in(select area_id from  op_policy_express_area )";
        } else {
            $sql .= "AND id  in(select area_id from  op_policy_express_area where pid='{$param['policy_express_id']}' )";
        }

        $sql = " select id as area_id, '{$param['policy_express_id']}' as  pid from base_area where " . $sql;

        $data = $this->db->get_all($sql);

        return $data;
    }

    function get_sub_area($policy_express_id, $id, $level, &$rt, &$ifFull, &$result) {
        if ($level > 4) {
            return; // 暂时只到区级
        }

        // 叶子节点
        if ($level == 4) {
            $rt[$id] = array('pid' => $policy_express_id, 'area_id' => $id);
            //$result[$id] = array('pid'=>$policy_express_id, 'area_id'=>$id);
            $result = array_merge($result, $rt);
            return;
        } else {
            if ($ifFull) {
                $rt[$id] = array('pid' => $policy_express_id, 'area_id' => $id);
            } else {
                $rt = array();
            }
        }


        $sql = "select * from base_area where parent_id = :parent_id";
        $areaList1 = $this->db->get_all($sql, array('parent_id' => $id));

        $sql = "select * from base_area where parent_id = :parent_id and id not in (
        select area_id from op_policy_express_area where pid <> :pid)";
        $areaList = $this->db->get_all($sql, array('parent_id' => $id, 'pid' => $policy_express_id));
        if (empty($areaList))
            return;

        if (count($areaList) == count($areaList1)) {
            $isFull = true;
        } else {
            $isFull = false;
        }

        foreach ($areaList as $key => $area) {
            $this->get_sub_area($policy_express_id, $area['id'], $area['type'], $rt, $ifFull, $result);
        }
    }

    function get_sub($policy_express_id, $areaId, &$r, &$r1) {
        $sql = "select * from base_area where id = :id";
        $area = $this->db->get_row($sql, array('id' => $areaId));
        if (empty($area)) {
            return;
        }

        if ($area['type'] > 4) {
            return;
        }

        $r[$areaId] = array('pid' => $policy_express_id, 'area_id' => $areaId);

        if ($area['type'] == '4') {
            if (!isset($r1[$areaId])) {
                $r1[$areaId] = $r[$areaId];
            }
            return;
        }

        $sql = "select * from base_area where parent_id = :parent_id";
        $subAreaListFull = $this->db->get_all($sql, array('parent_id' => $areaId));

        $sql = "select * from base_area where parent_id = :parent_id and id not in (
        select area_id from op_policy_express_area where pid <> :pid)";
        $subAreaList = $this->db->get_all($sql, array('parent_id' => $areaId, 'pid' => $policy_express_id));
        if (empty($subAreaList)) {
            return;
        }
        if (count($subAreaListFull) != count($subAreaList)) {
            $r = array();
        }

        foreach ($subAreaList as $key => $area) {
            $this->get_sub($policy_express_id, $area['id'], $r, $r1);
        }
    }

}
