<?php
/**
 * 拣货单相关业务
 *
 * @author xjy
 *
 */
require_model('tb/TbModel');
require_lang('sys');

class PickingModel extends TbModel
{

    /*
     * 初始化表
     */
    function get_table()
    {
        return 'base_picking_tpl';
    }
    /*
     * 根据条件查询数据
     */
    function get_by_page($filter)
    {
        $sql_join = "";
        $sql_main = "FROM {$this->table} rl $sql_join WHERE 1";
        
        if (isset($filter['keyword']) && $filter['keyword'] != '') {
            $sql_main .= " AND (rl.picking_name LIKE :keyword OR rl.picking_desc LIKE :keyword) ";
            $sql_values[':keyword'] = '%'.$filter['keyword'].'%';
        }
        
        if (isset($filter['__sort']) && $filter['__sort'] != '') {
            $filter['__sort_order'] = $filter['__sort_order'] == '' ? 'asc' : $filter['__sort_order'];
            $sql_main .= ' order by ' . trim($filter['__sort']) . ' ' . $filter['__sort_order'];
        }
        $select = 'rl.*';
        
        $data = $this->get_page_from_sql($filter, $sql_main, $select);
        
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    
    /*
     * 添加新纪录
     */
    function insert($picking)
    {
        $status = $this->valid($picking);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        
        $ret = $this->is_exists($picking['picking_name']);
        if ($ret['status'] > 0 && ! empty($ret['data']))
            return $this->format_ret(SHOP_ERROR_UNIQUE_CODE);
        
        $ret = $this->is_exists($picking['picking_content'], 'picking_content');
        if ($ret['status'] > 0 && ! empty($ret['data']))
            return $this->format_ret(SHOP_ERROR_UNIQUE_NAME);
        
        return parent::insert($picking);
    }

    /*
     * 更新记录状态
     */
    function update_active($active, $id)
    {
        if (! in_array($active, array(
            0,
            1
        ))) {
            return $this->format_ret('error_params');
        }
        
        $ret = parent::update(array(
            'ispublic' => $active
        ), array(
            'id' => $id
        ));
        return $ret;
    }
    
    /*
     * 删除记录
     */
    function do_delete($id)
    {
        $ret = parent::delete(array(
            'id' => $id
        ));
        return $ret;
    }
    
    /*
     * 修改纪录
     */
    function update($picking, $id)
    {
        $status = $this->valid($picking, true);
        if ($status < 1) {
            return $this->format_ret($status);
        }
        
        $ret = $this->get_row(array(
            'id' => $id
        ));
        if ($picking['picking_name'] != $ret['data']['picking_name']) {
            $ret = $this->is_exists($picking['picking_content'], 'picking_content');
            if ($ret['status'] > 0 && ! empty($ret['data']))
                return $this->format_ret(SHOP_ERROR_UNIQUE_NAME);
        }
        $ret = parent::update($picking, array(
            'id' => $id
        ));
        return $ret;
    }
}