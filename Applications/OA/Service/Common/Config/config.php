<?php
namespace Service\Common\Config;


/**
 *	@desc:共通信息的配置
 */
class config
{
		const db_host      = "localhost";
		
		const db_port      = "3306";
		
		const db_user      = "root";
		
		const db_pass      = "root";
		
		const db_name      = "trainingsystem";
		
		const g_tbl_prefix = 'tcitrn_';
		
		const api_token    = "transcosmos-7617-1298--11%2-md5!^";
		
		const secrct_key   = "finance-mode-test";
		
		const mail_host    = "172.16.216.31";
		
		const html_url     = "http://10.2.12.55/";

		//共通邮件配置
		static $mail_grp_com = array("user"      => "Crm1",
															   "pass"      => "Password01!",
															   "from"      => "crm1@transcosmos-cn.com",
															   "from_name" => "大宇宙OA系统",
															  );
		//财务邮件配置
		static $mail_grp_fin = array("user"      => "Crm9",
															   "pass"      => "Password09!",
															   "from"      => "crm9@transcosmos-cn.com",
															   "from_name" => "TCC OA Finance",
															  );

		//培训系统配置
		static $mail_grp_tra = array('user'      => 'Crm15',
															   'pass'      => 'Password15!',
															   'from'      => 'crm15@transcosmos-cn.com',
															   'from_name' => 'TCC OA Training',
															  );
		//采购系统配置
		static $mail_grp_pur = array('user'      => 'Crm2',
															   'pass'      => 'Password02!',
															   'from'      => 'crm2@transcosmos-cn.com',
															   'from_name' => 'TCC OA Purchasing',
															  );
}