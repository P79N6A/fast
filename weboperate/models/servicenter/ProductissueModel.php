<?php

/**
 * 服务中心-提单管理-产品问题提单
 *
 * @author zyp
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class ProductissueModel extends TbModel {

    function get_table() {
        return 'osp_product_issue';
    }

    /*
     * 获取提单问题列表方法
     */

    function get_product_issue($filter) {
        $sql_join = " left join osp_user u on p.sue_user=u.user_id  "
                . "left join osp_user up on p.sue_idea_user=up.user_id";
        $sql_main = "FROM {$this->table}  p $sql_join WHERE 1";
        //名称搜索条件
        if (isset($filter['sue_number']) && $filter['sue_number'] != '') {
            $sql_main .= " AND p.sue_number LIKE '%" . $filter['sue_number'] . "%'";
        }
        //产品搜索条件sue_title
        if (isset($filter['product']) && $filter['product'] != '') {
            $sql_main .= " AND p.sue_cp_id = '" . $filter['product'] . "'";
        }
        //提单标题模糊搜索。
        if (isset($filter['sue_title']) && $filter['sue_title'] != '') {
            $sql_main .= " AND p.sue_title LIKE '%" . $filter['sue_title'] . "%'";
        }
        //提单状态
        if (isset($filter['issue_status']) && $filter['issue_status'] != '') {
            $sql_main .= " AND p.sue_status in (" . $filter['issue_status'] . ")";
        }
        //提单人
        if (isset($filter['sue_user']) && $filter['sue_user'] != '') {
            $sql_main .= " AND u.user_name LIKE '%" . $filter['sue_user'] . "%'";
        }
        //受理人
        if (isset($filter['sue_idea_user']) && $filter['sue_idea_user'] != '') {
            $sql_main .= " AND up.user_name LIKE '%" . $filter['sue_idea_user'] . "%'";
        }
        
        //排序条件
        $sql_main .= " order by p.sue_submit_time desc";

        $select = 'p.*,u.user_name';
        $data = $this->get_page_from_sql($filter, $sql_main, array(),$select);
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联客户名称、产品等信息
        filter_fk_name($ret_data['data'], array('sue_kh_id|osp_kh', 'sue_cp_id|osp_chanpin'));
        return $this->format_ret($ret_status, $ret_data);
    }

    function get_by_id($id) {
        $params = array('sue_number' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('sue_kh_id|osp_kh', 'sue_pv_id|osp_chanpin_version', 'sue_product_fun|osp_product_module'));

        //获取附件明细
        $sql_fjmx = "SELECT * FROM osp_proissue_annex WHERE nex_sue_number=:num ";
        $sql_valuesmx[':num'] = $id;

        $retfj = $this->db->get_all($sql_fjmx, $sql_valuesmx);
        $data['fjmx'] = $retfj;
        return $this->format_ret($ret_status, $data);
    }

    /*
     * 添加问题提单
     */

    function insert($issue, $filelist) {
        if (isset($issue)) {
            $issue['sue_number'] = create_fast_bill_sn('WTTD');
            $ret = parent::insert($issue);
            //保存附件明细
            $arrayfile = json_decode($filelist);
            if (!empty($arrayfile)) {
                $proissue_nex = array();
                foreach ($arrayfile as $fileindexlist) {
                    $proissue_nex[] = array(
                        'nex_path' => $fileindexlist[0],
                        'nex_name' => $fileindexlist[1],
                        'nex_sue_number' => $issue['sue_number']);
                }
            }
            $data = $this->db->create_mapper('osp_proissue_annex')->insert($proissue_nex);
            if ($ret) {
                $num = $issue['sue_number'];
                $opera = '新建提单';
                $status = $issue['sue_status'];
                $note = '操作成功';
                $data = $this->save_log($num, $opera, $status, $note);
                if (isset($data)) {
                    //获取010问题受理组成员为默认接收人
                    $sql = "SELECT u.user_code from sys_user_role i INNER  JOIN osp_user u on i.user_id = u.user_id WHERE i.role_id=10";
                    $retsub = $this->db->get_col($sql);
                    if(!empty($retsub)){
                        $retsub = implode(';', $retsub);
                        $user = CTX()->get_session('user_code');
                        $username=CTX()->get_session('user_name');
                        $title = '提交问题';
                        $con = "编号" . "$num" . "的提单等待处理,提交人：".$username.",详情请登录系统查看";
                        $this->send_submit_rtx($retsub, $title, $con);
                    }
                }
            } else {
                return false;
            }
            //判断产品是否为自动受理
            $is_acc=$this->db->get_value("select cp_autoacc from osp_chanpin where cp_id=:cp_id", array(":cp_id" => $issue['sue_cp_id']));
            if($is_acc=="1"){
                //自动分配,获取产品下属成员（服务工程师）id=4
                $retcpAcc=$this->db->get_all("select * from osp_chanpin_member where pcm_cp_id=:pcm_cp_id and pcm_user_post=4",array(':pcm_cp_id'=>$issue['sue_cp_id']));
                if(!empty($retcpAcc)){//存在服务工程师
                    //获取当天提单已经存在的数据受理情况
                    $retAcclist=$this->db->get_row("select a.pcm_user,IFNULL(b.accnum,0) as accnum from 
                                                    (select pcm_user from osp_chanpin_member where pcm_cp_id=18 and pcm_user_post=4) a left join 
                                                    (select sue_idea_user,count(sue_idea_user) as accnum,sue_cp_id from osp_product_issue
                                                     where DATE_FORMAT(sue_submit_time,'%Y-%m-%d')=curdate() 
                                                     group by sue_idea_user,sue_cp_id) b on a.pcm_user=b.sue_idea_user
                                                    order by IFNULL(b.accnum,0) asc");
                    if(!empty($retAcclist)){
                        $issue_acc=array('sue_status'=>3,'sue_idea_user'=>$retAcclist['pcm_user'],'sue_accept_time'=>date('Y-m-d H:i:s'));
                        parent::update($issue_acc, array('sue_number' => $issue['sue_number']));
                    }else{
                        //获取成员列表中默认第一个自动受理
                        $issue_acc=array('sue_status'=>3,'sue_idea_user'=>$retcpAcc[0]['pcm_user'],'sue_accept_time'=>date('Y-m-d H:i:s'));
                        parent::update($issue_acc, array('sue_number' => $issue['sue_number']));
                        
                    }
                }
            }
            if ($ret['status'] == "1")
                return $this->format_ret("1", $issue['sue_number'], 'insert_success');
            else
                return $ret;
        }
    }

    /*
     * 修改提单信息。
     */

    function update($issue, $id) {
        if (isset($issue)) {
            $ret = parent::update($issue, array('sue_number' => $id));
            return $ret;
        }
    }

    //更新提单状态，问题受理
    function update_accept_status($number, $subdata) {
        if (!empty($number)) {
            $ret = parent::update($subdata, array('sue_number' => $number));
            if (ret) {
                $num = $number;
                $opera = '提单已受理，正在解决';
                $status = $subdata['sue_status'];
                $note = '操作成功';
                $data = $this->save_log($num, $opera, $status, $note);
                if (isset($data)) {
                    $user = $this->get_submiter($num);
                    $title = '提单受理';
                    $con = "提单号码:" . "$num" . "已经受理，正在解决";
                    $this->send_submit_rtx($user['user_code'], $title, $con);
                }
                return $ret;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //研发介入处理
    function update_research_status($number, $denydata) {
        if (!empty($number)) {
            $ret = parent::update($denydata, array('sue_number' => $number));
            if (ret) {
                $num = $number;
                $opera = '研发已经介入，问题正在解决';
                $status = $denydata['sue_status'];
                $note = '操作成功';
                $data = $this->save_log($num, $opera, $status, $note);
                return $ret;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * 提单无法解决
     */

    function update_unable_status($number, $unabledata, $type) {
        if (!empty($number)) {
            $ret = parent::update($unabledata, array('sue_number' => $number));
            if ($ret) {
                if ($type == 1) {
                    $num = $number;
                    $opera = '提单无法解决';
                    $status = '6';
                    $note = '操作成功';
                    $data = $this->save_log($num, $opera, $status, $note);
                    if (isset($data)) {
                        $user = $this->get_submiter($num);
                        $title = '提单终止';
                        $con = "您的提单号码:" . "$num" . "问题无法解决，详情请登录系统查看";
                        $this->send_submit_rtx($user['user_code'], $title, $con);
                    }
                    return $ret;
                } elseif ($type == 2) {
                    $num = $number;
                    $opera = '提单已经解决';
                    $status = '4';
                    $note = '操作成功';
                    $data = $this->save_log($num, $opera, $status, $note);
                    if (isset($data)) {
                        $user = $this->get_submiter($num);
                        $title = '提单解决';
                        $con = "您的提单号码:" . "$num" . "问题已经解决，详情请登录系统查看";
                        $this->send_submit_rtx($user['user_code'], $title, $con);
                    }
                    return $ret;
                } else {
                    $num = $number;
                    $opera = '提单已经拒绝';
                    $status = '2';
                    $note = '操作成功';
                    $data = $this->save_log($num, $opera, $status, $note);
                    if (isset($data)) {
                        $user = $this->get_submiter($num);
                        $title = '提单拒绝';
                        $con = "您的提单号码:" . "$num" . "已经拒绝，详情请登录系统查看";
                        $this->send_submit_rtx($user['user_code'], $title, $con);
                    }
                    return $ret;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //操作日志记录
    function save_log($num, $operate, $status, $note = '操作成功') {
        $loginfo = array();
        if (!empty($num)) {
            $loginfo['log_sue_number'] = $num;
            $loginfo['log_operater'] = CTX()->get_session("user_id");
            $loginfo['log_operate_detail'] = $operate;
            $loginfo['log_operate_date'] = date('Y-m-d H:i:s');
            $loginfo['log_sue_status'] = $status;
            $loginfo['log_notes'] = $note;
            $logdata = $this->db->create_mapper('osp_issue_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
    }

    //问题提单选择客户带出客户联系人和联系方式
    function get_clients($id) {
        //获取客户联系人和联系方式
        $sql_khmx = "SELECT kh_itphone,kh_itname FROM osp_kehu WHERE kh_id=:kh_id ";
        $sql_mx[':kh_id'] = $id;

        $data = $this->db->get_row($sql_khmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }

    //提单受理后发送rtx消息
    function send_submit_rtx($receiver, $title, $content) {
        /*$rtxserver = CTX()->get_app_conf('rtx_send_url');
        if (isset($rtxserver) && isset($receiver) && isset($title) && isset($content)) {
            $url = "$rtxserver?receiver=$receiver&title=$title&content=$content";
            $fp = file_get_contents("$url");
            if ($fp) {
                return ture;
            }
        } else {
            return false;
        }*/
        //暂时不发送消息
    }

    //获取提单人
    function get_submiter($num) {
        if (isset($num)) {
            $sql = "SELECT u.user_code from osp_product_issue i INNER  JOIN osp_user u on i.sue_user = u.user_id WHERE i.sue_number=:num ";
            $sql_value[':num'] = $num;
            $retsub = $this->db->get_row($sql, $sql_value);
            return $retsub;
        }
    }

}
