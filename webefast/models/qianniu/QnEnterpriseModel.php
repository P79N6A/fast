<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of QnEnterpriseModel
 *
 * @author wq
 */
require_model('tb/TbModel');
class QnEnterpriseModel extends TbModel {

    function get_kh_id_by_qn_id($qn_id, $app_key ='') {
        $sql = "select kh_id from osp_qn_enterprise where qn_id=:qn_id "; // AND app_key=:app_key
        $sql_values = array(
            ':qn_id' => $qn_id,
         //   ':app_key' => $app_key,
        );
        $kh_id = $this->db->get_value($sql, $sql_values);

        return $kh_id;
    }

}
