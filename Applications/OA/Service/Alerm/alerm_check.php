<?php
namespace Service\Alerm;

use \Workerman\MySQL;
/**
 *   @desc:系统导出数据的监测
 */
class alerm_check
{
		//数据库连接
		Private $db_conn     = null;
		
		Private $tbl_prefix  = null;
		
		private $object_mail = null;
		
		private $mail_set    = null;
		
		/**
		 *	@desc:构造函数
		 *  初始化信息
		 */
		public function __construct()
		{
				if(is_null($this->db_conn))
				{
						$this->db_conn = new \Service\Common\Db\db_mysql();
						
						$this->db_conn ->connect(\Service\Common\Config\config::db_host,
																		 \Service\Common\Config\config::db_user,
																		 \Service\Common\Config\config::db_pass,
																		 \Service\Common\Config\config::db_name,
																		 0,
																		 'utf8');
				}
				
				$this->tbl_prefix = \Service\Common\Config\config::g_tbl_prefix;
				
				$this->mail_set   = \Service\Common\Config\config::$mail_grp_tra;
				
				$this->object_mail = new \Service\Common\Email\sendmail();
				
				$this->object_mail -> set_mail_set($this->mail_set);
		}
		
		/**
		 *	@desc:查看信息的导出量
		 */
		public function get_alert_info($alerm_id)
		{
				$sql = "select export_num,
												b.report_to
									from ".$this->tbl_prefix."alerm_info a
									left join ".$this->tbl_prefix."employee b on a.operat_user = b.empcode
									where a.id = ".$alerm_id;
				
				$mod = $this->db_conn -> query($sql);
		
				$r   = $this->db_conn -> fetch_array($mod);
				
				if($r['export_num'] > 1000)
				{
						$send_to = array($['report_to']);
						$this->object_mail->set_SendTo($send_to);
						
						if(!in_array("sunny.lin@transcosmos-cn.com",$send_to))
						{
								$send_cc = array("sunny.lin@transcosmos-cn.com");
								$this->object_mail->set_SendCC(array('sunny.lin@transcosmos-cn.com','sunny.lin@transcosmos-cn.com'));
						}
						$mail_title = "OA系统导出上限通知";
						$mail_content = $this->get_mail_content($alerm_id);

						$this->object_mail->set_SendSubject($mail_title);
						$this->object_mail->set_SendContent($mail_content);

						$this->object_mail->send();
				}
				
				return;
		}
		
		/**
		 *	@desc:邮件内容获取
		 */
		public function get_mail_content($alert_id)
		{
				$sql = "select task_name,
												b.e_name,
												a.export_num,
												a.operat_date
									from ".$this->tbl_prefix."alerm_info a
									left join ".$this->tbl_prefix."employee b on a.operat_user = b.empcode
									where a.id = ".$alert_id;
				$mod = $this->db_conn -> query($sql);
		
				$r   = $this->db_conn -> fetch_array($mod);
				
				$rtn_mail_info = null;
				
				
				$rtn_mail_info = '<div style="font-size:12px"><b>OA系统导出上限通知</b><br/><br/></div>
													<table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >
														<tr>
															<td width="60" bgcolor="#EEEEEE" >导 出 人：</td>
															<td bgcolor="#FFFFFF">'.$r['e_name'].'</td>
														</tr>
														<tr>
															<td width="60" bgcolor="#EEEEEE" >导出文件：</td>
															<td bgcolor="#FFFFFF">'.$r['task_name'].'</td>
														</tr>
														<tr>
															<td bgcolor="#EEEEEE">导出日期：</td>
															<td bgcolor="#FFFFFF">'.$r['operat_date'].'</td>
														</tr>
														<tr>
															<td bgcolor="#EEEEEE">导出累计件数：</td>
															<td bgcolor="#FFFFFF">'.$r['export_num'].'</td>
														</tr>
														<tr>
															<td bgcolor="#EEEEEE">备注：</td>
															<td bgcolor="#FFFFFF">导出件数已经超过了每天天的上限(10000件)，请确认下。</td>
														</tr>
													</table>';

				return $rtn_mail_info;
		}
}