<?php

require_model('pubdata/BasePubModel');

class UserPubModel extends BasePubModel {
    public $product_version = array(
        1 => '标准版',
        2 => '企业版',
        3 => '旗舰版'
    );
    function add_app_user_info($kh_id, $user_data) {

        $db = $this->create_kh_db($kh_id);
        if ($db === false) {
            return $this->format_ret(-1, '', '请核查客户库是否绑定好');
        }
        //验证用户名是否已存在
        $ret = $this->get_user_info($db, $user_data['user_code']);
        if ($ret) {
            return $this->format_ret(-1, '', '用户名已存在！');
        }
        $user_data['password'] = $this->encode_pwd($user_data['password']);
        $user_data['is_manage'] = 1; //默认管理员
        //插入
        $status = $db->insert('sys_user', $user_data);
        if ($status === false) {
            return $this->format_ret(-1, '', '客户信息创建失败，请检查表是否创建！');
        }
        
        return $this->format_ret(1);
    }

    private function encode_pwd($pwd) {
        return md5(md5($pwd) . $pwd);
    }

    /**处理邮件内容
     * @param $arr
     * @return string
     */
    function email_content($arr) {
        $con = "
	    <table width=\"70%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family:Microsoft YaHei UI;border: 1px #98c9ee dotted;\">
          <tr>
            <td height=\"47\" align=\"center\" valign=\"middle\" bgcolor=\"#1794CA\">
            <font style=\"font-size: 24px;	font-weight: 100;	color: #FFF;\">" . $arr['title'] . "</font>
            </td>
          </tr>
          <tr>
            <td height=\"90\">
            <table width=\"100%\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-size: 12px; border: 1px; border-color: #98c9ee; border-style: dotted;\" >";
        foreach ($arr['cont'] as $cont_row) {
            if (!isset($cont_row['sub_title'])) {
                $con .= " <tr>
                <td width=\"15%\" height=\"30\" align=\"right\" style=\"border-bottom:#98c9ee dotted 1px;border-right:#98c9ee dotted 1px;\">{$cont_row['key']}：</td>
                <td width=\"88%\" height=\"30\" style=\"border-bottom:#98c9ee dotted 1px;\">" . $cont_row['val'] . "</td>
              </tr>";
            } else {
                $con .= " <tr>
                <td width=\"15%\" height=\"30\" align=\"\" style=\"border-bottom:#98c9ee dotted 1px;border-right:#98c9ee dotted 1px;\">
                <font style=\\\"font - size: 24px;	font - weight: 100;	color: #FFF;\\\">{$cont_row['key']}</font>
                </td>
                <td width=\"88%\" height=\"30\" style=\"border-bottom:#98c9ee dotted 1px;\">" . $cont_row['val'] . "</td>
              </tr>";
            }
        }
        $con .= " </table>
            </td>
          </tr>
          <tr>
            <td height=\"47\" align=\"center\" valign=\"middle\" bgcolor=\"#1794CA\">
            <font style=\"font-size: 15px;	font-weight: 100;	color: #FFF;\">" . $arr['end'] . "</font>
            </td>
          </tr>
        </table>";
        return $con;
    }

    /**发送邮件
     * @param $pro_info
     * @param $user_data
     */
    function send_email($pro_info,$user_data){
        $kh_info = load_model('servicenter/ProductxqissueModel')->get_clients($pro_info['pro_kh_id']);
        $kh_email = load_model('servicenter/ProductxqissueModel')->check_email($kh_info['data'],'');
        $kh_name = $kh_info['data']['kh_name'];
        //获取授权KEY
        $auth_info=load_model('products/ProductorderauthModel')->get_row(array('pra_kh_id'=>$pro_info['pro_kh_id'],'pra_cp_id'=>$pro_info['pro_cp_id']));
        $authkey = ($auth_info['status'] != 1) ? '' : $auth_info['data']['pra_authkey'];
        $subject = "产品授权";
        $cont_arr = array(
            'title' => 'eFAST365授权信息',
            'end' => '如有疑问，联系在线客户QQ：400-680-9510，方便您解决问题。或发送邮件到bt@baisonmail.com,感谢您的支持!',
            'cont' => array(
                array('key' => '授权信息', 'val' => '','sub_title'=>1),
                array('key' => '客户名称', 'val' => $kh_name),
                array('key' => '产品名称', 'val' => 'efast365'),
                array('key' => '产品版本', 'val' => $this->product_version[$pro_info['pro_product_version']]),
                array('key' => '授权KEY', 'val' => $authkey),
                array('key' => '授权点数', 'val' => $pro_info['pro_dot_num']),
                array('key' => '初始账号', 'val' => '','sub_title'=>1),
                array('key' => '登陆地址', 'val' => 'http://login.baotayun.com'),
                array('key' => '公司名称', 'val' => $kh_name),
                array('key' => '用户名', 'val' => $user_data['user_code']),
                array('key' => '密码', 'val' => $user_data['password']),
            ),
        );
        $cont = $this->email_content($cont_arr);
        $mail_send[] = array(
            'kh_id' => $pro_info['pro_kh_id'],
            'subject' => $subject,
            'send_to' => $kh_email,
            'cont_json' => addslashes(json_encode($cont)),
            'cont_body' => addslashes(htmlentities($cont)),
            'create_time' => date('Y-m-d H:i:s'),
        );
        load_model('mailer/QueueModel')->add_mailer_queue($mail_send);
    }

    /**
     *  检测是否存在用户名
     */
    function get_user_info($obj, $user_code) {
        $sql = "SELECT user_id FROM sys_user WHERE user_code=:user_code";
        $sql_value = array(":user_code" => $user_code);
        $res = $obj->get_row($sql, $sql_value);
        return $res;
    }


}
