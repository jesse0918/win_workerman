<?php
namespace Service\Purchasing;

use \Workerman\Lib\Timer;
use \Workerman\MySQL;

/**
 *
 */
class mail_module
{
		private $email = null;
		
		public function __construct()
		{
		}
		
		/**
		 *		获取邮件模板
		 */
		public function get_email_moudle($type = null)
		{
				$rtn_info = null;

				switch($type)
				{
						default:
								$template  = '<div style="font-size:12px"><b>{$subject}</b><br/><br/></div><table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >';
								$template .= '<tr><td width="60" bgcolor="#EEEEEE" >需求部门：</td><td bgcolor="#FFFFFF">{$department}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">成本中心：</td><td bgcolor="#FFFFFF">{$cost_code}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">申 请 人：</td><td bgcolor="#FFFFFF">{$created_user}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">申请时间：</td><td bgcolor="#FFFFFF">{$created_date}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">需求描述：</td><td bgcolor="#FFFFFF">{$demand_desc}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">申请理由：</td><td bgcolor="#FFFFFF">{$reason}</td></tr>';
								$template .= '<tr style={$show_audit}><td bgcolor="#EEEEEE">审 核 人：</td><td bgcolor="#FFFFFF">{$approve_user}</td></tr>';
								$template .= '<tr style={$show_content}><td bgcolor="#EEEEEE">备&nbsp;&nbsp;&nbsp;&nbsp;注：</td><td bgcolor="#FFFFFF" style="color:blue;font-weight:bold;">{$remark}</td></tr>';
								$template .= '<tr><td bgcolor="#EEEEEE">详情链接：</td><td bgcolor="#FFFFFF">{$link}</td></tr></table>';
								$rtn_info  = $template;
								break;
				}
				
				return $rtn_info;
		}
}