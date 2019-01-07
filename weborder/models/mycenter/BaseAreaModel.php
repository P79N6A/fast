<?php

require_model('tb/TbModel');

class BaseAreaModel extends TbModel {

    function get_table() {
        return 'base_area';
    }

    function get_area($parent_id) {
        $rs = array();

        if (strlen($parent_id) == 6) {
            $p = $parent_id . '000000';
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' or parent_id = '$p'  ";
            $rs = $this->db->get_all($sql);
        } else {
            $sql = "select * FROM {$this->table} WHERE parent_id = '$parent_id' ";
            $rs = $this->db->get_all($sql);
        }
        return $rs;
    }

}
