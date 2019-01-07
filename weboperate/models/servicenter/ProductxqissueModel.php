<?php

/**
 * 服务中心-提单管理-产品需求提单
 *
 * @author wangshouchong
 *
 */
require_model('tb/TbModel');
require_lib("comm_util");

class ProductxqissueModel extends TbModel {
    
    function get_table() {
        return 'osp_product_xqissue';
    }

    public $xqsue_service_type = array(
        '001' => '平台接口',
        '002' => 'WMS接口',
        '003' => 'ERP接口',
        '004' => '网络订单处理',
        '005' => '网络退单处理',
    );

    public $xqsue_difficulty = array(
        '001' => '低',
        '002' => '中',
        '003' => '高',
    );

    /*
     * 获取需求提单列表方法
     */
    function get_by_page($filter) {
        //$sql_join = " left join osp_user u on p.xqsue_user=u.user_id  ";
        $sql_main = "FROM {$this->table}  p WHERE 1";
        $sql_value = array();
        //名称搜索条件
        if (isset($filter['xqsue_number']) && $filter['xqsue_number'] != '') {
            $sql_main .= " AND p.xqsue_number LIKE '%" . $filter['xqsue_number'] . "%'";
        }
       //需求详情
        if (isset($filter['xqsue_detail']) && $filter['xqsue_detail'] != '') {
            $sql_main .= " AND p.xqsue_detail LIKE '%" . $filter['xqsue_detail'] . "%'";
        }
        //紧急程度
        if (isset($filter['xqsue_urgency']) && $filter['xqsue_urgency'] != '') {
            $xqsue_urgency_arr=explode(',',$filter['xqsue_urgency']);
            $xqsue_urgency_str=$this->arr_to_in_sql_value($xqsue_urgency_arr,'xqsue_urgency',$sql_value);
            $sql_main .= " AND p.xqsue_urgency IN ({$xqsue_urgency_str})";
        }
        //难易度
        if (isset($filter['xqsue_difficulty']) && $filter['xqsue_difficulty'] != '') {
            $xqsuetype_arr = explode(',', $filter['xqsue_difficulty']);
            $xqsuetype_str = $this->arr_to_in_sql_value($xqsuetype_arr, 'xqsue_difficulty', $sql_value);
            $sql_main .= " AND p.xqsue_difficulty IN ({$xqsuetype_str})";
        }
        //产品搜索条件sue_title
        if (isset($filter['xqsue_cp_id']) && $filter['xqsue_cp_id'] != '') {
            $sql_main .= " AND p.xqsue_cp_id = '" . $filter['xqsue_cp_id'] . "'";
        }
        //提单标题模糊搜索。
        if (isset($filter['xqsue_title']) && $filter['xqsue_title'] != '') {
            $sql_main .= " AND p.xqsue_title LIKE '%" . $filter['xqsue_title'] . "%'";
        }
        //提单状态
        if (isset($filter['xqsue_status']) && $filter['xqsue_status'] != '') {
            $sql_main .= " AND p.xqsue_status in (" . $filter['xqsue_status'] . ")";
        }
        //是否计划周次
        if (isset($filter['xqsue_plan_week_status']) && $filter['xqsue_plan_week_status'] != '') {
            if ($filter['xqsue_plan_week_status'] == 1) {
                $sql_main .= " AND (p.xqsue_plan_week IS NULL OR p.xqsue_plan_week='') ";
            } else if ($filter['xqsue_plan_week_status'] == 2) {
                $sql_main .= " AND (p.xqsue_plan_week IS NOT NULL OR p.xqsue_plan_week<>'') ";
            }
        }
        //计划周次
        if (isset($filter['xqsue_plan_week']) && $filter['xqsue_plan_week'] != '') {
            $sql_main .= " AND p.xqsue_plan_week={$filter['xqsue_plan_week']} ";
        }
        //需求类型
        if (isset($filter['xqsuetype']) && $filter['xqsuetype'] != '') {
            $xqsuetype_arr = explode(',', $filter['xqsuetype']);
            $xqsuetype_str = $this->arr_to_in_sql_value($xqsuetype_arr, 'xqsuetype', $sql_value);
            $sql_main .= " AND p.xqsue_xqtype in ({$xqsuetype_str})";
        }
        //业务类型
        if (isset($filter['xqsue_service_type']) && $filter['xqsue_service_type'] != '') {
            $sql_main .= " AND p.xqsue_service_type={$filter['xqsue_service_type']}";
        }
        //提单人
        if (isset($filter['xqsue_user']) && $filter['xqsue_user'] != '') {
            $sql_main .= " AND p.xqsue_user LIKE '%" . $filter['xqsue_user'] . "%'";
        }
                //反馈时间
        if (isset($filter['xqsue_return_time_start']) && $filter['xqsue_return_time_start'] != '') {
            $sql_main .= " AND  p.xqsue_return_time  >='{$filter['xqsue_return_time_start']}'";
        }
        if (isset($filter['xqsue_return_time_end']) && $filter['xqsue_return_time_end'] != '') {
            $sql_main .= " AND p.xqsue_return_time  <='{$filter['xqsue_return_time_end']}'";
        }
        //客户
        if (isset($filter['kh_name']) && $filter['kh_name']!='' ) {
            $sql_kh = "select kh_id from osp_kehu where kh_name LIKE '%". $filter['kh_name'] ."%'";
           //echo $sql_kh;die;
            $kh_id_arr = $this->db->get_all_col($sql_kh);
            if(empty($kh_id_arr)){
                $sql_main .= " AND 1=2 ";
            } else {
               $kh_id_str = join(",", $kh_id_arr);
               $sql_main .= " AND p.xqsue_kh_id in ($kh_id_str) ";
            }
        }
        //审批意见
        if (isset($filter['xqsue_idea']) && $filter['xqsue_idea'] != '') {
            $sql_main .= " AND p.xqsue_idea LIKE '%" . $filter['xqsue_idea'] . "%'";
        }
        //排序条件
        $sql_main .= " order by p.xqsue_submit_time desc";

        $select = 'p.*';
        $data = $this->get_page_from_sql($filter, $sql_main, $sql_value, $select);

        $kh_id_arr = array_unique(array_column($data['data'], 'xqsue_kh_id'));
        if (!empty($kh_id_arr)) {
            $sql_values = array();
            $kh_id_str = $this->arr_to_in_sql_value($kh_id_arr, 'kh_id', $sql_values);
            $sql = "SELECT kh_id,kh_fwuser,kh_xsuser FROM osp_kehu WHERE kh_id IN ({$kh_id_str})";
            $kh_ret = $this->db->get_all($sql, $sql_values);
            $kh_info = array();
            foreach ($kh_ret as $kh_val) {
                $kh_info[$kh_val['kh_id']] = $kh_val;
            }
        }
        foreach ($data['data'] as &$value) {
            $value['xqsue_remark'] = strip_tags($value['xqsue_remark']);
            $value['xqsue_remark']=html_entity_decode($value['xqsue_remark']);
            $value['xqsue_idea'] = strip_tags($value['xqsue_idea']);
            $value['xqsue_idea'] = html_entity_decode($value['xqsue_idea']);
            $value['xqsue_detail'] = strip_tags($value['xqsue_detail']);
            $value['xqsue_detail'] = html_entity_decode($value['xqsue_detail']);
            $value['xqsue_service_type_name'] = $this->xqsue_service_type[$value['xqsue_service_type']];
            $value['xqsue_difficulty_name'] = $this->xqsue_difficulty[$value['xqsue_difficulty']];
            //导出
            $value['kh_fwuser'] = $kh_info[$value['xqsue_kh_id']]['kh_fwuser'];
            $value['kh_xsuser'] = $kh_info[$value['xqsue_kh_id']]['kh_xsuser'];
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        //处理关联客户名称、产品等信息
        filter_fk_name($ret_data['data'], array('xqsue_kh_id|osp_kh', 'xqsue_cp_id|osp_chanpin','kh_fwuser|osp_user_id_p', 'kh_xsuser|osp_user_id_p','xqsue_product_fun|osp_product_module'));
        return $this->format_ret($ret_status, $ret_data);
    }
    
    
    function get_by_id($id) {
        $params = array('xqsue_number' => $id);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        if ($ret_status == 1) {
            $data['xqsue_service_type_name'] = $this->xqsue_service_type[$data['xqsue_service_type']];
            $data['xqsue_plan_week_name'] = empty($data['xqsue_plan_week']) ? "" : "第{$data['xqsue_plan_week']}周";
        }
        //处理关联代码表
        filter_fk_name($data, array('xqsue_kh_id|osp_kh', 'xqsue_pv_id|osp_chanpin_version', 'xqsue_product_fun|osp_product_module'));

        //获取附件明细
        $sql_fjmx = "SELECT * FROM osp_proxqissue_annex WHERE xqnex_number=:num ";
        $sql_valuesmx[':num'] = $id;

        $retfj = $this->db->get_all($sql_fjmx, $sql_valuesmx);
        $data['fjmx'] = $retfj;
        return $this->format_ret($ret_status, $data);
    }
    
    //需求提单选择客户带出客户联系人和联系方式
    function get_clients($id) {
        //获取客户联系人和联系方式
        $sql_khmx = "SELECT kh_itphone,kh_itname,kh_email,kh_name,kh_fwuser_email FROM osp_kehu WHERE kh_id=:kh_id ";
        $sql_mx[':kh_id'] = $id;

        $data = $this->db->get_row($sql_khmx, $sql_mx);
        $ret_status = $data ? 1 : 'op_no_data';
        return $this->format_ret($ret_status, $data);
    }
    
    //获取客户联系人和联系方式
    function get_clients_other($id) {
        //获取客户联系人和联系方式
        $sql_khmx = "SELECT kh_itphone,kh_itname FROM osp_kehu WHERE kh_id=:kh_id ";
        $sql_mx[':kh_id'] = $id;

        $data = $this->db->get_row($sql_khmx, $sql_mx);
        return $data;
    }
    
    /*
     * 新增需求提单
     */

    function insert($issue, $filelist) {
        if (isset($issue)) {
            $issue['xqsue_number'] = create_fast_bill_sn('XQTD');
            $ret = parent::insert($issue);
            //保存提单邮箱
           $insert_data = array(
                'user_code' => $issue['xqsue_user_code'],
                'user_name' => $issue['xqsue_user_name'],
                'kh_id' => $issue['xqsue_kh_id'],
                'email' => $issue['xqsue_email'],
            );
            $update_str = "email = VALUES(email)";
            $ret = $this->insert_multi_duplicate('osp_kh_sys_user_info', array($insert_data), $update_str);
            //保存附件明细
            $arrayfile = json_decode($filelist);
            if (!empty($arrayfile)) {
                $proxqissue_nex = array();
                foreach ($arrayfile as $fileindexlist) {
                    $proxqissue_nex[] = array(
                        'xqnex_path' => $fileindexlist[0],
                        'xqnex_name' => $fileindexlist[1],
                        'xqnex_number' => $issue['xqsue_number']);
                }
            }
            $data = $this->db->create_mapper('osp_proxqissue_annex')->insert($proxqissue_nex);
            if ($ret) {
                $num = $issue['xqsue_number'];
                $opera = '新建提单';
                $status = $issue['xqsue_status'];
                $note = '操作成功';
                $data = $this->save_log($num, $opera, $status, $note);
                if (isset($data)) {
                    //获取011需求受理组成员为默认接收人
                    $sql = "SELECT u.user_code from sys_user_role i INNER  JOIN osp_user u on i.user_id = u.user_id WHERE i.role_id=11";   //需求受理角色组接收处理通知消息
                    $retsub = $this->db->get_col($sql);
                    if(!empty($retsub)){
                        $retsub = implode(';', $retsub);
                        $user = CTX()->get_session('user_code');
                        $username=CTX()->get_session('user_name');
                        $title = '提交需求';
                        $con = "编号" . "$num" . "的需求提单等待处理,提交人：".$username.",详情请登录系统查看";
                        $this->send_submit_rtx($retsub, $title, $con);
                    }
                }
                if ($ret['status'] == "1")
                    return $this->format_ret("1", $issue['xqsue_number'], 'insert_success');
                else
                    return $ret;
            } else {
                return false;
            }
        }
    }
    
    /*
     * 修改提单信息。
     */

    function update($issue, $id) {
        if (isset($issue)) {
            $ret = parent::update($issue, array('xqsue_number' => $id));
            return $ret;
        }
    }
    
    
    //操作日志记录
    function save_log($num, $operate, $status, $note = '操作成功') {
        $loginfo = array();
        if (!empty($num)) {
            $loginfo['xqlog_number'] = $num;
            $loginfo['xqlog_operater'] = CTX()->get_session("user_id");
            $loginfo['xqlog_operate_detail'] = $operate;
            $loginfo['xqlog_operate_date'] = date('Y-m-d H:i:s');
            $loginfo['xqlog_operate_state'] = $status;
            $loginfo['xqlog_notes'] = $note;
            $logdata = $this->db->create_mapper('osp_xqissue_log')->insert($loginfo);
            return $logdata;
        } else {
            return false;
        }
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
    
    
    //更新提单状态，需求受理
    function update_accept_status($number, $subdata) {
        if (!empty($number)) {
            $ret = parent::update($subdata, array('xqsue_number' => $number));
            if (ret) {
                $num = $number;
                $opera = '提单已受理，正在审批';
                $status = $subdata['xqsue_status'];
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
    //需求审批
    function do_xqissueidea($request){
        $xq_issue_ret = $this->get_row(array('xqsue_number' => $request['xqsue_number']));
        if ($xq_issue_ret['status'] == '-1'){
            return false;
        }
        $ideadata = get_array_vars($request, array(
            'xqsue_processtype',
            'xqsue_return_time',
            'xqsue_idea',
            'xqsue_xqtype',
            'xqsue_service_type',
        ));
        $ideadata['xqsue_idea_user'] = CTX()->get_session("user_id");
        $ideadata['xqsue_idea_time'] = date('Y-m-d H:i:s');
        $ideadata['xqsue_status'] = '4';   //需求—已审批
        $ret = $this->update_idea_status($request['xqsue_number'],$ideadata);
        if ($ret['status'] == '-1') {
            return false;
            
        }
        //发送邮件
        $kh_id = $xq_issue_ret['data']['xqsue_kh_id'];
        $kh_info = $this->get_clients($kh_id);
//        $kh_email = !empty($kh_info['data']['kh_fwuser_email']) ? $kh_info['data']['kh_email'] . ';' . $kh_info['data']['kh_fwuser_email'].';'.$xq_issue_ret['data']['xqsue_email'] : $kh_info['data']['kh_email'].';'.$xq_issue_ret['data']['xqsue_email'];
        $kh_name = $kh_info['data']['kh_name'];
        $kh_email=$this->check_email($kh_info['data'],$xq_issue_ret['data']);
        if(empty($kh_email)){
            //return $ret;
        }
        $subject = "提单审批通过啦";
        $cont = "您好！您的建议‘".$request['xqsue_number'].':'.$xq_issue_ret['data']['xqsue_title']."’已被采纳，我们将在7个工作日内安排处理！感谢你对宝塔的支持，祝您工作愉快！ ";
        $cont_arr = array(
            'title'=>'产品建议处理进度通知',
            'cont'=>array(
                array('key'=>'当前进度','val'=>'您的产品建议我们已接收并通过审核，产品组会在开发完成后第一时间通知您，具体上线日期请关注系统公告！感谢您对宝塔的支持，祝您工作愉快！'),
                array('key'=>'客户名称','val'=>$kh_name),
                array('key'=>'需求编号','val'=>$xq_issue_ret['data']['xqsue_number']),
                array('key'=>'需求名称','val'=>$xq_issue_ret['data']['xqsue_title']),
                array('key'=>'需求描述','val'=>$xq_issue_ret['data']['xqsue_detail']),
            ),
            );
        $cont = $this->email_content($cont_arr);
        $mail_send[] = array(
            'kh_id'=>$kh_id,
            'subject'=>$subject,
            'send_to'=>$kh_email,
            'cont_json'=>addslashes(json_encode($cont)),
            'cont_body'=>addslashes(htmlentities($cont)),
            'create_time'=>date('Y-m-d H:i:s'),
        );
        load_model('mailer/QueueModel')->add_mailer_queue($mail_send);
        return $ret;
    }

    function batch_update_online_status($numbers,$xqsue_idea){
        $error_msg = "";
        foreach ($numbers as $number) {
            $ret = $this->update_online_status($number,$xqsue_idea);
            if ($ret['status'] == '-1'){
                $error_msg .= $ret['message'];
            }
        }
        if (!empty($error_msg)){
            return $this->format_ret(-1,'',$error_msg);
        }
        return $this->format_ret(1,'');
        
    }
    //更新提单状态，需求上线
    function update_online_status($number,$xqsue_idea) {
       
        if(empty($number)){
            return false;
        }
        $xq_issue_ret = $this->get_row(array('xqsue_number' => $number));
        if ($xq_issue_ret['status'] == '-1'){
            return false;
        }
        if ($xq_issue_ret['data']['xqsue_status'] != 5){
            return $this->format_ret(-1,'','提单'.$number.'还未解决，无法上线<br/>');
        }
        $subdata['xqsue_status'] = '7';   //已上线
        if (!empty($xqsue_idea)){
            $subdata['xqsue_idea'] = $xq_issue_ret['data']['xqsue_idea'].' 上线备注：'.$xqsue_idea; 
        }
        $ret = parent::update($subdata, array('xqsue_number' => $number));
        if ($ret['status'] == '-1') {
            return false;
            
        }
        
        $opera = '提单已上线';
        $status = $subdata['xqsue_status'];
        $note = '操作成功';
        $this->save_log($number, $opera, $status, $note);
       //发送邮件
         
        $kh_id = $xq_issue_ret['data']['xqsue_kh_id'];
        $kh_info = $this->get_clients($kh_id);
      //  $kh_email = !empty($kh_info['data']['kh_fwuser_email']) ? $kh_info['data']['kh_email'] . ';' . $kh_info['data']['kh_fwuser_email'].';'.$xq_issue_ret['data']['xqsue_email'] : $kh_info['data']['kh_email'].';'.$xq_issue_ret['data']['xqsue_email'];
      $kh_email = $this->check_email($kh_info['data'], $xq_issue_ret['data']);
        $kh_name = $kh_info['data']['kh_name'];
        if(empty($kh_email)){
            //return $ret;
        }
        $subject = "新功能上线啦";
        //$cont = "您好！您的建议’".$number.':'.$xq_issue_ret['data']['xqsue_title']."‘已于本周四晚上上线，详情请关注本次更新日志！感谢你对宝塔的支持，祝您工作愉快！";
        $cont_arr = array(
            'title'=>'产品建议处理进度通知',
            'cont'=>array(
                array('key'=>'当前进度','val'=>'您的产品建议已成功上线。详情请关注本次更新日志！感谢您对宝塔的支持，祝您工作愉快！'),
                array('key'=>'客户名称','val'=>$kh_name),
                array('key'=>'需求编号','val'=>$xq_issue_ret['data']['xqsue_number']),
                array('key'=>'需求名称','val'=>$xq_issue_ret['data']['xqsue_title']),
                array('key'=>'需求描述','val'=>$xq_issue_ret['data']['xqsue_detail']),
            ),
            );
         $cont = $this->email_content($cont_arr);
        $mail_send[] = array(
            'kh_id'=>$kh_id,
            'subject'=>$subject,
            'send_to'=>$kh_email,
            'cont_json'=>addslashes(json_encode($cont)),
            'cont_body'=>addslashes(htmlentities($cont)),
            'create_time'=>date('Y-m-d H:i:s'),
        );
        load_model('mailer/QueueModel')->add_mailer_queue($mail_send);
        return $ret;
    }
    
    function email_content($arr){
        $con = "
	    <table width=\"70%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family:Microsoft YaHei UI;border: 1px #98c9ee dotted;\">
          <tr>
            <td height=\"47\" align=\"center\" valign=\"middle\" bgcolor=\"#1794CA\">
            <font style=\"font-size: 24px;	font-weight: 100;	color: #FFF;\">".$arr['title']."</font>
            </td>
          </tr>
          <tr>
            <td height=\"90\">
            <table width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size: 12px; border: 1px; border-color: #98c9ee; border-style: dotted;\" >";
        foreach ($arr['cont'] as $cont_row) {
            $con .= " <tr>
                <td width=\"15%\" height=\"30\" align=\"right\" style=\"border-bottom:#98c9ee dotted 1px;border-right:#98c9ee dotted 1px;\">{$cont_row['key']}：</td>
                <td width=\"88%\" height=\"30\" style=\"border-bottom:#98c9ee dotted 1px;\">".$cont_row['val']."</td>
              </tr>";
            
        }
        $con .= " </table>
            </td>
          </tr>
        </table>";
        return $con;
	    
    }


    //匹配产品模块
    function getmod_bycp($cpid,$mname){
        $modid=$this->db->get_value("select pm_id from osp_chanpin_module where pm_name=:pm_name and pm_cp_id=:pm_cp_id", array(":pm_name" => $mname,":pm_cp_id"=>$cpid));
        return $modid;
    }
    
    //匹配产品默认版本
    function getversion_bycp($cpid){
        $verid=$this->db->get_value("select pv_id from  osp_chanpin_version where pv_cp_id=:pv_cp_id and pv_type='0'", array(":pv_cp_id"=>$cpid));
        return $verid;
    }
    
    //验证客户信息
    function ver_kehu_auth($khid){
        $sql = "select * from osp_kehu where MD5(kh_id)=:khid";
        $sql_value[':khid'] = $khid;
        $khinfo = $this->db->get_row($sql, $sql_value);
        if(!empty($khinfo)){
            //验证客户是否产品授权,产品默认efast5
            $sql_auth="select * from osp_productorder_auth where pra_cp_id='21' and pra_kh_id=:pra_kh_id and pra_state='1' and pra_enddate>=:pra_enddate";
            $sql_auth_value[':pra_kh_id']=$khinfo['kh_id'];
            $sql_auth_value[':pra_enddate']=date('Y-m-d H:i:s');
            $khauth = $this->db->get_row($sql_auth, $sql_auth_value);
            if(!empty($khauth)){
                return $khinfo;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    //获取提单人
    function get_submiter($num) {
        if (isset($num)) {
            $sql = "SELECT u.user_code from osp_product_xqissue i INNER  JOIN osp_user u on i.xqsue_user = u.user_id WHERE i.xqsue_number=:num ";
            $sql_value[':num'] = $num;
            $retsub = $this->db->get_row($sql, $sql_value);
            return $retsub;
        }
    }
    
    /*
     * 提单无法解决,已解决，拒绝
     */

    function update_unable_status($number, $unabledata, $type) {
        if (!empty($number)) {
            $ret = parent::update($unabledata, array('xqsue_number' => $number));
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
                        $con = "您的提单号码:" . "$num" . "需求无法解决，详情请登录系统查看";
                        $this->send_submit_rtx($user['user_code'], $title, $con);
                    }
                    return $ret;
                } elseif ($type == 2) {
                    $num = $number;
                    $opera = '提单已经解决';
                    $status = '5';
                    $note = '操作成功';
                    $data = $this->save_log($num, $opera, $status, $note);
                    if (isset($data)) {
                        $user = $this->get_submiter($num);
                        $title = '提单解决';
                        $con = "您的提单号码:" . "$num" . "需求已经解决，详情请登录系统查看";
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
                        $con = "您的提单号码:" . "$num" . "需求已经拒绝，详情请登录系统查看";
                        $this->send_submit_rtx($user['user_code'], $title, $con);
                        //发送邮件    
                        $xq_issue_ret = $this->get_row(array('xqsue_number' => $number));
                        $kh_id = $xq_issue_ret['data']['xqsue_kh_id'];
                        $kh_info = $this->get_clients($kh_id);
//                        $kh_email = !empty($kh_info['data']['kh_fwuser_email']) ? $kh_info['data']['kh_email'] . ';' . $kh_info['data']['kh_fwuser_email'] . ';' . $xq_issue_ret['data']['xqsue_email'] : $kh_info['data']['kh_email'] . ';' . $xq_issue_ret['data']['xqsue_email'];
                        $kh_email = $this->check_email($kh_info['data'],$xq_issue_ret['data']);
                        $kh_name = $kh_info['data']['kh_name'];
                        $subject = "提单审批不通过";
                        $cont_arr = array(
                            'title' => '产品建议处理进度通知',
                            'cont' => array(
                                array('key' => '拒绝通知', 'val' => '很抱歉通知您，您提交的产品建议不在今年产品规划内，近期不会处理。感谢您对宝塔的支持，祝您工作愉快！'),
                                array('key' => '客户名称', 'val' => $kh_name),
                                array('key' => '需求编号', 'val' => $xq_issue_ret['data']['xqsue_number']),
                                array('key' => '需求名称', 'val' => $xq_issue_ret['data']['xqsue_title']),
                                array('key' => '需求描述', 'val' => $xq_issue_ret['data']['xqsue_detail']),
                                array('key' => '拒绝原因', 'val' => $xq_issue_ret['data']['xqsue_idea']),
                            ),
                        );
                        $cont = $this->email_content($cont_arr);
                        $mail_send[] = array(
                            'kh_id' => $kh_id,
                            'subject' => $subject,
                            'send_to' => $kh_email,
                            'cont_json' => addslashes(json_encode($cont)),
                            'cont_body' => addslashes(htmlentities($cont)),
                            'create_time' => date('Y-m-d H:i:s'),
                        );
                        load_model('mailer/QueueModel')->add_mailer_queue($mail_send);
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
    
    /*
     * 提单需求审批
     */
    function update_idea_status($number, $ideadata) {
        if (!empty($number)) {
            $ret = parent::update($ideadata, array('xqsue_number' => $number));
            if ($ret) {
                $num = $number;
                $opera = '提单需求审批';
                $status = '4';
                $note = '操作成功';
                $data = $this->save_log($num, $opera, $status, $note);
                if (isset($data)) {
                    $user = $this->get_submiter($num);
                    $title = '提单需求审批';
                    $con = "您的提单号码:" . "$num" . "需求已经审批，详情请登录系统查看";
                    $this->send_submit_rtx($user['user_code'], $title, $con);
                }
                return $ret;
            }else {
                return false;
            }
        }else{
            return false;
        }
    }
    

    /**
     *获取提单邮箱 
     */
    public function get_email_by_field($kh_id, $user_code) {
        $sql = "SELECT email FROM osp_kh_sys_user_info WHERE kh_id=:kh_id AND user_code=:user_code ";
        $result = $this->db->get_row($sql,array(':kh_id'=>$kh_id,':user_code'=>$user_code));
        $email = $result['email'];
        return $email;
    }
     
    /**
     * 组装邮箱
     */
    public function check_email($kh_info,$xq_issue_ret) {
        $email_arr = array();
        if (!empty($kh_info['kh_fwuser_email'])) {
            $email_arr[] = $kh_info['kh_fwuser_email'];
        }
        if (!empty($kh_info['kh_email'])) {
            $email_arr[] = $kh_info['kh_email'];
        }
        if (!empty($xq_issue_ret['xqsue_email'])) {
            $email_arr[] = $xq_issue_ret['xqsue_email'];
        }
        $email = empty($email_arr) ? '' : implode(';', $email_arr);
        return $email;
    }
      
    /**
     * API-获取产品建议列表
     * @author wmh
     * @date 2017-06-01
     * @param array $params 接口参数
     * @return array 操作结果
     */
    public function api_suggest_list_get($params) {
        $select = 'p.*';
        $sql_main = "FROM {$this->table} p WHERE 1";
        $sql_values = array();

        if (!empty($params['kh_fwuser']) && $params['kh_fwuser'] != 'admin') {
            $sql_user = 'SELECT user_id FROM osp_user WHERE user_code=:user_code';
            $user_id = $this->db->get_value($sql_user, array(':user_code' => $params['kh_fwuser']));
            if ($user_id === FALSE) {
                return array();
            }
            $sql_kh = 'SELECT kh_id FROM osp_kehu WHERE kh_fwuser=:kh_fwuser';
            $kh_id_arr = $this->db->get_all_col($sql_kh, array(':kh_fwuser' => $user_id));
            if(empty($kh_id_arr)){
                return array();
            }
            $kh_id_str = implode(',', $kh_id_arr);
            $sql_main .= " AND p.xqsue_kh_id IN({$kh_id_str})";
        }
        //提单编号
        if (isset($params['xqsue_number']) && $params['xqsue_number'] != '') {
            $sql_main .= ' AND p.xqsue_number LIKE :xqsue_number';
            $sql_values[':xqsue_number'] = "%{$params['xqsue_number']}%";
        }
        //产品编码
        if (isset($params['xqsue_cp_code']) && $params['xqsue_cp_code'] != '') {
            $sql = 'SELECT cp_id FROM osp_chanpin WHERE cp_code=:code';
            $cp_id = $this->db->get_value($sql, array(':code' => $params['xqsue_cp_code']));
            if ($cp_id === FALSE) {
                return array();
            }
            $sql_main .= ' AND p.xqsue_cp_id = :cp_id';
            $sql_values[':cp_id'] = $cp_id;
        }
        //提单标题
        if (isset($params['xqsue_title']) && $params['xqsue_title'] != '') {
            $sql_main .= ' AND p.xqsue_title LIKE :xqsue_title';
            $sql_values[':xqsue_title'] = "%{$params['xqsue_title']}%";
        }
        //提单状态
        if (isset($params['xqsue_status']) && $params['xqsue_status'] != '') {
            $xqsue_status_arr = explode(',', $params['xqsue_status']);
            $xqsue_status_str = $this->arr_to_in_sql_value($xqsue_status_arr, 'xqsue_status', $sql_values);
            $sql_main .= " AND p.xqsue_status IN({$xqsue_status_str})";
        }
        //需求类型
        if (isset($params['xqsue_xqtype']) && $params['xqsue_xqtype'] != '') {
            $xqsue_xqtype_arr = explode(',', $params['xqsue_xqtype']);
            $xqsue_xqtype_str = $this->arr_to_in_sql_value($xqsue_xqtype_arr, 'xqsue_xqtype', $sql_values);
            $sql_main .= " AND p.xqsue_xqtype IN({$xqsue_xqtype_str})";
        }
        //提单人
        if (isset($params['xqsue_user']) && $params['xqsue_user'] != '') {
            $sql_main .= ' AND p.xqsue_user LIKE :xqsue_user';
            $sql_values[':xqsue_user'] = "%{$params['xqsue_user']}%";
        }
        //反馈时间
        if (isset($params['xqsue_return_time_start']) && $params['xqsue_return_time_start'] != '') {
            $sql_main .= ' AND  p.xqsue_return_time >=:start_time';
            $sql_values[':start_time'] = $params['xqsue_return_time_start'];
        }
        if (isset($params['xqsue_return_time_end']) && $params['xqsue_return_time_end'] != '') {
            $sql_main .= ' AND p.xqsue_return_time <=:end_time';
            $sql_values[':end_time'] = $params['xqsue_return_time_end'];
        }
        //客户
        if (isset($params['kh_name']) && $params['kh_name'] != '') {
            $sql_kh = "SELECT kh_id FROM osp_kehu WHERE kh_name LIKE :kh_name";
            $kh_id_arr = $this->db->get_all_col($sql_kh, array(':kh_name' => "%{$params['kh_name']}%"));
            if (empty($kh_id_arr)) {
                $sql_main .= " AND 1=2 ";
            } else {
                $kh_id_str = join(",", $kh_id_arr);
                $sql_main .= " AND p.xqsue_kh_id in ($kh_id_str) ";
            }
        }
        //审批意见
        if (isset($params['xqsue_idea']) && $params['xqsue_idea'] != '') {
            $sql_main .= ' AND p.xqsue_idea LIKE :xqsue_idea';
            $sql_values[':xqsue_idea'] = "%{$params['xqsue_idea']}%";
        }

        //排序条件
        $sql_main .= " ORDER BY p.xqsue_submit_time DESC";

        $data = $this->get_page_from_sql($params, $sql_main, $sql_values, $select);
        
        foreach ($data['data'] as &$val) {
            if ($val['xqsue_return_time'] == '0000-00-00 00:00:00' || empty($val['xqsue_return_time'])) {
                $val['xqsue_return_time'] = '';
            } else {
                $xqsue_return_date = strtotime($val['xqsue_return_time']);
                $val['xqsue_return_time'] = date('Y-m-d', $xqsue_return_date);
            }
        }
        $ret_status = OP_SUCCESS;
        $ret_data = $data;
        
        //处理关联客户名称、产品等信息
        filter_fk_name($ret_data['data'], array('xqsue_kh_id|osp_kh','xqsue_user|osp_user_id_p'));
        return $this->format_ret($ret_status, $ret_data);
    }

    /**
     * API-获取产品建议详情
     * @author wmh
     * @date 2017-06-02
     * @param array $params 接口参数
     * @return array 操作结果
     */
    public function api_suggest_detail_get($params) {
        if (empty($params['xqsue_number'])) {
            return $this->format_ret(1, array());
        }
        $params = array('xqsue_number' => $params['xqsue_number']);
        $data = $this->create_mapper($this->table)->where($params)->find_by();
        $ret_status = $data ? 1 : 'op_no_data';
        //处理关联代码表
        filter_fk_name($data, array('xqsue_kh_id|osp_kh', 'xqsue_product_fun|osp_product_module','xqsue_user|osp_user_id_p','xqsue_accept_user|osp_user_id_p','xqsue_idea_user|osp_user_id_p'));

        //获取附件明细
        $sql_fjmx = "SELECT * FROM osp_proxqissue_annex WHERE xqnex_number=:xqnex_number ";
        $sql_valuesmx[':xqnex_number'] = $params['xqsue_number'];

        $retfj = $this->db->get_all($sql_fjmx, $sql_valuesmx);
        $data['fjmx'] = $retfj;
        return $this->format_ret($ret_status, $data);
    }


    function get_xqsue_info($xqsue_number_arr) {
        $sql_value = array();
        $xqsue_number_str = $this->arr_to_in_sql_value($xqsue_number_arr, 'xqsue_number', $sql_value);
        $sql = "SELECT * FROM osp_product_xqissue WHERE xqsue_number IN({$xqsue_number_str})";
        $ret = $this->db->get_all($sql, $sql_value);
        return $ret;
    }

}
