<?php
/**
 * 客户相关业务
 *
 * @author WangShouChong
 *
 */
require_model('tb/TbModel');

class ApiClientModel extends TbModel {
    
    function get_table() {
        return 'osp_kehu';
    }
    
    /*
     * 插入客户信息
     */
    function addclient($data) {
        //解析request
        return parent::insert($data);
    }
    
    /*
     * 修改客户信息
     */
    function editclient($data,$fiter){
        //解析request
        return parent::update($data,$fiter);
    }
    
    function is_exists($value, $field_name = 'kh_id') {
        $ret = parent::get_row(array($field_name => $value));
        return $ret;
    }

	/**
	 * RDS安装, 通过聚石塔API接口创建数据库
	 * @param int $id 商户ID
	 */
	function install_by_rds($id) {

		$auth_conf = require_conf('auth');

		require_lib('util/taobao_util', true);

//		require_lib('sq/Loading');
//		//   require_lib('sq/api/taobao/top/TopClient');
//		require_lib('sq/taobao');
//		require_model('rds');

		CTX()->register_tool('db_kh', 'lib/db/PDODB.class.php');
		//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//		$ld = new Loading('renter_install_' . $id);

		//总安装步骤
		$total = 8;
		//step1 查找客户信息 +++++++++++++++++++++++++++++++++++++++++++++++++++
		$kehu_info = $this->is_exists($id);

		if (empty($kehu_info['data'])) {

//			$ld->push(1, $total, false, '商户信息查找失败')->done();
			return false;
		}

		//step2 更新安装状态 以及安装版本号 ++++++++++++++++++++++++++++++++++++++
		if (1 == $kehu_info['data']['sys_clear_rate']) {
//			$ld->push(2, $total, false, '系统正在部署中，请勿重复部署')->done();
			CTX()->log_error('install_db:系统正在部署中，请勿重复部署');
			return false;
		}

		if (2 == $kehu_info['data']['sys_clear_rate']) {
//			$ld->push(2, $total, false, '系统已部署完成，请勿重复部署')->done();
			CTX()->log_debug('install_db:系统已部署完成，请勿重复部署');
			return false;
		}

//		$ld->push(1, $total, true, '商户信息查找成功');

	//	CTX()->log_debug('install_db:' . '商户信息查找成功');

		//获取对应的数据库配置-2014-07-12
		$server_conf = $auth_conf['servers'];
		$db_conf = $server_conf['jushita_servers'][$server_conf['web'][$kehu_info['data']['kh_web_host']]['jushita_key']];

		//测试用
//		$db_conf['jushita_key'] = '21814154';
//		$db_conf['jushita_secret'] = 'a1d813f6289cbe91c00b42dd0dea6484';
//		$db_conf['rds_instance'] = 'jrdsa5xw9yyv';
//
//		$db_conf['rds_account'] = 'jusr3keeskxf';
//		$db_conf['rds_password'] = 'CS69973677bs';
//		$db_conf['rds_host'] = 'jconnervyg378.mysql.rds.aliyuncs.com';

		//step3 新建数据库 +++++++++++++++++++++++++++++++++++++++++++++++++++++

		$instanceName = $db_conf['rds_instance'];

		//获取对应key的session 2014-09-23

		require_model('sys/RdsModel_ex');
		$mdl_api_rds = new RdsModel_ex();

		$rds_info = $mdl_api_rds->is_exists($db_conf['jushita_key'],'app_key');

		if (empty($rds_info['data'])) {
			return false;
		}
		$session = $rds_info['data']['access_token'];

		$_params = array('app' => $db_conf['jushita_key'], 'secret' => $db_conf['jushita_secret'], 'session' => $session, 'db_name' => $kehu_info['data']['kh_code'], 'instance_name' => $instanceName);

//	    if(defined('TBSANDBOX') && TBSANDBOX){
//
//		    //sandbox 试用正式key来创建数据库
//		    $_top_app_keys = CTX()->get_app_conf('top_app_keys');
//		    $params = array('app'=>$_top_app_keys['_by_time']['top_app_key'], 'secret' => $_top_app_keys['_by_time']['top_app_secret'], 'session'=>$session, 'db_name'=>$renter['renter_code'], 'instance_name'=>$instanceName);
//	    }

		$taobao = new taobao_util($_params['app'],$_params['secret'], $_params['session']);

		$taobao->topUrl = 'http://gw.api.taobao.com/router/rest?';

		$params = array();

		$params['db_name'] = $_params['db_name'];
		//测试用
	//	$params['db_name'] = 'efast_adiu';

		$params['instance_name'] = $_params['instance_name'];

		$rds_create_status = $taobao->post('taobao.rds.db.create', $params);
		if (1 != $rds_create_status['status']) {
//			$ld->push(3, $total, false, '创建数据库失败!' . json_encode($rds_create_status))->done();
			$this->editclient(array('sys_clear_rate' => 0), array('kh_id'=>$id));
			CTX()->log_error('install_db:创建数据库失败!' . json_encode($rds_create_status));
			return false;
		}

//		$ld->push(3, $total, true, '成功创建数据库 ' . $renter['renter_code']);
		CTX()->log_debug('install_db:成功创建数据库 ');
		sleep(10);
		//step4 执行安装sql +++++++++++++++++++++++++++++++++++++++++++++++++++
		CTX()->db_kh->set_conf(array(
			'name' => $kehu_info['data']['kh_code'],
			'host' => $db_conf['rds_host'],
			'user' => $db_conf['rds_account'],
			'pwd' => $db_conf['rds_password'],
			'type' => 'mysql',
		));
		$db = CTX()->db_kh;

		//@TODO 如果SAAS中心管理发版信息，此处需要从版本表中获取最新版本的sql脚本路径 以及版本号
		$sql_path = ROOT_PATH . 'install' . DIRECTORY_SEPARATOR . 'efast_oms' . DIRECTORY_SEPARATOR . 'all_sql.sql';
		if (!file_exists($sql_path)) {
			CTX()->log_error('install_db:创建数据库失败!数据库文件不存在！' );
			return false;
		}

		$sql_str = file_get_contents($sql_path);

		$db->query('set names utf8;');
		$db->query('DROP PROCEDURE IF EXISTS `install_func`;');
//		$ld->push(3.5, $total, true, '开始执行数据库脚本,时间较长请耐心等待');
		$proc = 'CREATE DEFINER = CURRENT_USER PROCEDURE `install_func`()
                    BEGIN ' . $sql_str . '
                    END;';
		try {
			$db->query($proc);
			$db->query('call install_func();');
			$db->query('DROP PROCEDURE IF EXISTS `install_func`;');
		} catch (Exception $exc) {
//			$ld->push(4, $total, false, '执行数据库脚本失败!' . $exc->getMessage())->done();
			CTX()->log_error('执行数据库脚本失败!');
			return false;
			//清除掉废数据库
			//$db->query('DROP DATABASE IF EXISTS `' . $renter['renter_code'] . "`");
		}
//		$ld->push(4, $total, true, '成功执行数据库脚本');
		CTX()->log_debug('install_db:成功执行数据库脚本');
		//step5 创建客户目录 ++++++++++++++++++++++++++++++++++++++++++++++++++
		//@TODO 此处为app程序根据客户代码自动创建和使用
		//如文件采用nfs分布式存储,则在此处创建
//		$ld->push(5, $total, true, '创建商户私有目录');
		//step6 初始化体验授权 ++++++++++++++++++++++++++++++++++++++++++++++++
		//默认7天体验时间
//		$this->edit(array(
//			'renter_start_time' => date('Y-m-d h:i:s'),
//			//     'renter_end_time' => date('Y-m-d h:i:s', time() + 86400 * 7),
//		), $id);
//		$ld->push(6, $total, true, '初始化体验授权');
		CTX()->log_debug('install_db:初始化体验授权成功');


		//step7 初始化超级管理员 ++++++++++++++++++++++++++++++++++++++++++++++

		require_model('api/ApiClientLoginModel');
		$mdl_login = new ApiClientloginModel();
		$mdl_login->addclogin(array(
			'kh_id' => $kehu_info['data']['kh_id'],
			'kh_code' => $kehu_info['data']['kh_code'],
			'user_code' => 'admin',
			'user_password' => md5('baison@2014'),
			'status' => '1',
			'remark' => '系统默认管理员',
			'sys_create_time' => date('Y-m-d h:i:s'),
		));

		//step8 完成安装 +++++++++++++++++++++++++++++++++++++++++++++++++++++
		$this->editclient(array('sys_clear_rate' => 2), array('kh_id'=>$id));
//		$ld->push(8, $total, true, '安装完成！')->done();

		return true;
	}

	###############################################################
	/**
	 * 客户软件登录判断
	 * @author jhua.zuo <jhua.zuo@baisommail.com>
	 * @since 2014-11-22
	 * @todo 暂无公共登录页面，待开发
	 * @param string $kh_name 客户登录名
	 * @param string $user_code 用户代码
	 * @param string $password 用户密码
	 * @return array
	 */

	function check_login($kh_name, $user_code, $password) {
		return false;
	}

	/**
	 * 客户软件登录判断，不需要密码（针对淘宝已经登录过的用户），返回加密字符串用于软件二次登录
	 * @author jhua.zuo <jhua.zuo@baisommail.com>
	 * @since 2014-11-22
	 * @param string $kh_code 客户代码
	 * @param string $user_code 用户代码
	 */
	function check_login_without_password($kh_code, $user_code){
		$data = array('kh_code' => $kh_code,'user_code' => $user_code);
		//登录TOKEN默认有效期为5分钟
		$token = fast_encode($data);
		return $token;
	}

}