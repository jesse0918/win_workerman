<?php
namespace Service\Finance;

use \Workerman\MySQL;

/**
 *  @desc ：发票核销通知
 */
class pay_notince
{
		private $db_conn     = null;
		
		private $tbl_prefix  = null;
		
		private $object_mail = null;
		
		private $mail_set    = null;
		
		private $list_info   = null;
		
		private $mail_model  = null;

		/**
		 *	@desc:构造函数
		 *	初期化设置
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
						
				$this->mail_set   = \Service\Common\Config\config::$mail_grp_fin;
				
				$this->object_mail = new \Service\Common\Email\sendmail();
				
				$this->object_mail -> set_mail_set($this->mail_set);
						
				$this->list_info   = array();
						
				$this->mail_model  = $this->mail_model();
		}
		
		/**
		 *	@desc:通知处理
		 */
		public function notice()
		{
				//對象取得
				$this -> get_notice_list();
				
				//邮件发送
				$this -> send_mail();
		}
		
		/**
		 *	@desc 對象取得
		 */
		private function get_notice_list()
		{
				$sql = "select a.finance_def_no
									from ".$this->tbl_prefix."finance_pay_list a
									left join ".$this->tbl_prefix."finance_pay_request b on a.finance_def_no = b.finance_def_no
									where a.pro_sts in ('15','18')
										and ticket_flag = '1'
										and pay_ticket_chk = '0'
										and (to_days(pay_ticket_time) - to_days(now())) <= 3";
				$q = $this->db_conn -> query($sql);

				while($r = $this->db_conn -> fetch_array($q))
				{
						$this->list_info[] = $r['finance_def_no'];
				}
		}
		
		/**
		 *	@desc:邮件发送
		 */
		private function send_mail()
		{
				if(empty($this->list_info))
				{
						return;
				}
				
				$sql = "select b.email,
												b.e_name,
												b.report_to,
												date_format(date(a.create_time),'%Y年%m月%d日') cre_date,
												a.total_cost,
												date_format(date(now()),'%Y年%m月%d日') now_date,
												date_format(date(c.pay_ticket_time),'%Y年%m月%d日') chk_date,
												c.request_company,
												a.finance_def_no
									from ".$this->tbl_prefix."finance_pay_list a
									left join ".$this->tbl_prefix."employee b on a.emp_code = b.empcode
									left join ".$this->tbl_prefix."finance_pay_request c on a.finance_def_no = c.finance_def_no
									where a.finance_def_no in ('".implode("','",$this->list_info)."')
									order by a.finance_def_no asc";
				$q = $this->db_conn -> query($sql);

				while($r = $this->db_conn -> fetch_array($q))
				{
						//$this->object_mail->set_SendTo($r['email']);
						//$this->object_mail->set_SendCC($r['report_to']);
						$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
						
						$meisel_html = $this -> get_mei_sei($r['finance_def_no']);
						
						$mail_title    =  "催发票函";
						
						$mail_content  =  $this->mail_model;
						$mail_content  = str_replace("{emp_email}"   ,$r['e_name']          ,$mail_content);
						$mail_content  = str_replace("{req_date}"    ,$r['cre_date']        ,$mail_content);
						$mail_content  = str_replace("{req_company}" ,$r['request_company'] ,$mail_content);
						$mail_content  = str_replace("{mei_info}"    ,$meisel_html          ,$mail_content);
						$mail_content  = str_replace("{req_cost}"    ,$r['total_cost']      ,$mail_content);
						$mail_content  = str_replace("{chk_now}"     ,$r['now_date']        ,$mail_content);
						$mail_content  = str_replace("{req_pay_time}",$r['chk_date']        ,$mail_content);
						
						$this->object_mail->set_SendSubject($mail_title);
						$this->object_mail->set_SendContent($mail_content);

						$this->object_mail->send();
				}
				
		}
		
		/**
		 *	@desc:邮件模板取得处理
		 */
		private function mail_model()
		{
				$model = '';
				
				$model = '<table style="text-align:center;width:100%;" cellpadding="0" cellspacing="0" border="0" bordercolor="#000000">';
				$model .= '<tbody><tr><td><span style="font-size:24px;font-family:Microsoft YaHei;">催发票函</span>';
				$model .= '</td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">Dear {emp_email}</span></td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您{req_date}申请支付给《{req_company}》公司，</td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">费用详细（金额合计为{req_cost}元）：</span></td>';
				$model .= '</tr>';
				$model .= '{mei_info}';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">于{chk_now}为止财务部还未收到发票，故特致此函提醒，</span></td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">请即时向对方催讨。如果未能在{req_pay_time}提供，财务部</span></td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">将依照公司规定把税额进入贵部门成本中心,请悉知！</span></td>';
				$model .= '</tr>';
				$model .= '<tr><td><span style="font-size:16px;font-family:Microsoft YaHei;">&nbsp;</span></td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">财务部</span></p>';
				$model .= '</td>';
				$model .= '</tr>';
				$model .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">{chk_now}</span></p>';
				$model .= '</td>';
				$model .= '</tr>';
				$model .= '</tbody>';
				$model .= '</table>';
				$model .= '</div>';
				
				return $model;
		}
		
		private function get_mei_sei($def_no)
		{
				$rtn_info = null;

				$sql = "select pay_request_reason
									from ".$this->tbl_prefix."finance_pay_meisei
									where finance_def_no = '".$def_no."'
									order by finance_def_no asc";
				$q = $this->db_conn -> query($sql);
				
				$i = 1;
				while($r = $this->db_conn -> fetch_array($q))
				{
						$rtn_info .= '<tr><td style="text-align:left;"><span style="font-size:16px;font-family:Microsoft YaHei;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$i."：".$r['pay_request_reason'].'</span></td>';
						$rtn_info .= '</tr>';
						
						$i++;
				}
				
				return $rtn_info;
		}
}
?>