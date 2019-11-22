<?php
namespace Service\Finance;

use \Workerman\MySQL;

/**
 *  @desc ：付款申请单通知
 */
class pay_operate
{
		private $db_conn     = null;
		
		private $tbl_prefix  = null;
		
		private $object_mail = null;
		
		private $mail_set    = null;
		
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
						
						$this->tbl_prefix = \Service\Common\Config\config::g_tbl_prefix;
						
						$this->mail_set   = \Service\Common\Config\config::$mail_grp_fin;
				
						$this->object_mail = new \Service\Common\Email\sendmail();
				
						$this->object_mail -> set_mail_set($this->mail_set);
				}
		}
		
		public function approve_notice($type,$finance_def_no)
		{
				//
				$rtn = null;
				
				$list_arr = explode(",",$finance_def_no);
				foreach($list_arr as $value)
				{
						switch($type)
						{
								case '5':
										$rtn = $this -> get_pay_info($value);
										$this -> pay_send_mail($rtn);
										break;
								case '6':
										$rtn = $this -> get_adv_info($value);
										$this -> adv_send_mail($rtn);
										break;
						}
				}
		}
		
		private function get_pay_info($finance_def_no)
		{
				$sql = "select a.finance_def_no,
												a.approve_status,
												a.approve_result,
												a.remark,
												a.create_user,
												c.request_company,
												d.email,
												b.pro_sts,
												b.company_name,
												e.type_cd
										from ".$this->tbl_prefix."finance_approve a
										left join ".$this->tbl_prefix."finance_pay_list b on a.finance_def_no = b.finance_def_no
										left join ".$this->tbl_prefix."finance_pay_request c on a.finance_def_no = c.finance_def_no
										left join ".$this->tbl_prefix."employee d on b.emp_code = d.empcode
										left join ".$this->tbl_prefix."common_cd e on e.type_key = 'K006' and e.type_name = b.company_name
										where a.finance_def_no = '".$finance_def_no."'
										order by a.id desc
										limit 1";

				$q = $this->db_conn -> query($sql);

				$msg_info = $this->db_conn -> fetch_array($q);
				
				$pri_type = "";
				switch($msg_info['approve_status'])
				{
						case '12':
								$pri_type = "Pay_Rec";
								break;
						case '13':
								$pri_type = "Pay_Aprove";
								break;
						case '14':
								$pri_type = "Pay_Pay";
								break;
						case '15':
								$pri_type = "Pay_Cret";
								break;
						case '16':
								break;
						case '17':
								break;
						case '18':
								//$pri_type = "Pay_Canc";
								break;
						case '19':
								break;
				}
				
				if(!empty($pri_type))
				{
						$sql = "select b.email
											from ".$this->tbl_prefix."finance_authority a
											left join ".$this->tbl_prefix."employee b on a.pri_empcode = b.empcode
											where pri_type = '".$pri_type."' and  company_cd = '".$msg_info['type_cd']."'
												and b.isleave = 0";
						$msg_info['next_operator'] = $this->db_conn -> select($sql);
				}
				
				return $msg_info;
		}
		
		private function pay_send_mail($msg_info)
		{
				if(empty($msg_info))
				{
						return;
				}

				$mail_temp = $this ->pay_mail_model();
				
				$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】处理通知";
				
				$operate   = null;
				switch($msg_info['approve_status'])
				{
						case '12':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】已提交";
								$operate       = "提交";
								break;
						case '13':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】已收单";
								$operate       = "收单";
								break;
						case '14':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】审核完成";
								$operate       = "审核";
								break;
						case '15':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】支付完成";
								$operate       = "支付";
								break;
						case '16':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】已驳回";
								$operate       = "驳回";
								break;
						case '17':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】已驳回";
								$operate       = "驳回";
								break;
						case '18':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】已制证";
								$operate       = "制证";
								break;
						case '19':
								$mail_title    = "付款申请单【".$msg_info['finance_def_no']."】发票核销完成";
								$operate       = "发票核销";
								break;
				}
				
				$mail_content  = $mail_temp;
				
				$mail_content  = str_replace("{subject}"          ,$mail_title                          ,$mail_content);
				$mail_content  = str_replace("{finance_def_no}"   ,$msg_info['finance_def_no']          ,$mail_content);
				$mail_content  = str_replace("{request_company}"  ,$msg_info['request_company']         ,$mail_content);
				$mail_content  = str_replace("{operate}"          ,$operate                             ,$mail_content);
				$mail_content  = str_replace("{operater}"         ,$msg_info['create_user']             ,$mail_content);
				$mail_content  = str_replace("{remark}"           ,$msg_info['remark']                  ,$mail_content);
				$html = \Service\Common\Config\config::html_url."?mod=finance&task=pay_summary&def_no=".$msg_info['finance_def_no'];
				$url = '请<a href="' . $html . '">点击查看</a>';
				$mail_content  = str_replace("{link}"             ,$url                                 ,$mail_content);
				
				$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
				//$this->object_mail->set_SendTo(array($msg_info['email']));
				//$this->object_mail->set_SendCC($msg_info['next_operator']);
				
				$this->object_mail->set_SendSubject($mail_title);
				$this->object_mail->set_SendContent($mail_content);

				$this->object_mail->send();
		}
		
		private function pay_mail_model()
		{
				$model = '';
				
				$model = '<div style="font-size:12px"><b>{subject}</b><br/><br/></div><table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >';
				$model .= '<tr><td width="100px" bgcolor="#EEEEEE" >付款申请单编号：</td><td bgcolor="#FFFFFF">{finance_def_no}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">付款公司：</td><td bgcolor="#FFFFFF">{request_company}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">操    作：</td><td bgcolor="#FFFFFF">{operate}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">操 作 人：</td><td bgcolor="#FFFFFF">{operater}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">备    注：</td><td bgcolor="#FFFFFF">{remark}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">详细信息：</td><td bgcolor="#FFFFFF">{link}</td></tr></table>';
				
				return $model;
		}
		
		private function get_adv_info($finance_def_no)
		{
				$sql = "select a.finance_def_no,
												a.approve_status,
												a.approve_result,
												a.remark,
												a.create_user,
												c.remark adv_remark,
												d.email,
												b.pro_sts,
												b.company_name,
												e.type_cd
										from ".$this->tbl_prefix."finance_approve a
										left join ".$this->tbl_prefix."finance_adv_list b on a.finance_def_no = b.finance_def_no
										left join ".$this->tbl_prefix."finance_adv_request c on a.finance_def_no = c.finance_def_no
										left join ".$this->tbl_prefix."employee d on b.emp_code = d.empcode
										left join ".$this->tbl_prefix."common_cd e on e.type_key = 'K006' and e.type_name = b.company_name
										where a.finance_def_no = '".$finance_def_no."'
										order by a.id desc
										limit 1";

				$q = $this->db_conn -> query($sql);

				$msg_info = $this->db_conn -> fetch_array($q);
				
				$pri_type = "";
				switch($msg_info['approve_status'])
				{
						case '12':
								$pri_type = "Pay_Rec";
								break;
						case '13':
								$pri_type = "Pay_Aprove";
								break;
						case '14':
								$pri_type = "Pay_Pay";
								break;
						case '15':
								$pri_type = "Pay_Cret";
								break;
						case '16':
								break;
						case '17':
								break;
						case '18':
								//$pri_type = "Pay_Canc";
								break;
						case '19':
								break;
				}
				
				if(!empty($pri_type))
				{
						$sql = "select b.email
											from ".$this->tbl_prefix."finance_authority a
											left join ".$this->tbl_prefix."employee b on a.pri_empcode = b.empcode
											where pri_type = '".$pri_type."' and  company_cd = '".$msg_info['type_cd']."'
												and b.isleave = 0";
						$msg_info['next_operator'] = $this->db_conn -> select($sql);
				}
				
				return $msg_info;
		}
		
		private function adv_send_mail($msg_info)
		{
				if(empty($msg_info))
				{
						return;
				}

				$mail_temp = $this ->adv_mail_model();
				
				$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】处理通知";
				
				$operate   = null;
				switch($msg_info['approve_status'])
				{
						case '12':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】已提交";
								$operate       = "提交";
								break;
						case '13':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】已收单";
								$operate       = "收单";
								break;
						case '14':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】审核完成";
								$operate       = "审核";
								break;
						case '15':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】支付完成";
								$operate       = "支付";
								break;
						case '16':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】已驳回";
								$operate       = "驳回";
								break;
						case '17':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】已驳回";
								$operate       = "驳回";
								break;
						case '18':
								$mail_title    = "暂支申请单【".$msg_info['finance_def_no']."】已制证";
								$operate       = "制证";
								break;
				}
				
				$mail_content  = $mail_temp;
				
				$mail_content  = str_replace("{subject}"          ,$mail_title                          ,$mail_content);
				$mail_content  = str_replace("{finance_def_no}"   ,$msg_info['finance_def_no']          ,$mail_content);
				$mail_content  = str_replace("{adv_remark}"       ,$msg_info['adv_remark']              ,$mail_content);
				$mail_content  = str_replace("{operate}"          ,$operate                             ,$mail_content);
				$mail_content  = str_replace("{operater}"         ,$msg_info['create_user']             ,$mail_content);
				$mail_content  = str_replace("{remark}"           ,$msg_info['remark']                  ,$mail_content);
				$html = \Service\Common\Config\config::html_url."?mod=finance&task=adv_summary&def_no=".$msg_info['finance_def_no'];
				$url = '请<a href="' . $html . '">点击查看</a>';
				$mail_content  = str_replace("{link}"             ,$url                                 ,$mail_content);
				
				$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
				//$this->object_mail->set_SendTo(array($msg_info['email']));
				//$this->object_mail->set_SendCC($msg_info['next_operator']);
				
				$this->object_mail->set_SendSubject($mail_title);
				$this->object_mail->set_SendContent($mail_content);

				$this->object_mail->send();
		}
		
		private function adv_mail_model()
		{
				$model = '';
				
				$model = '<div style="font-size:12px"><b>{subject}</b><br/><br/></div><table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >';
				$model .= '<tr><td width="100px" bgcolor="#EEEEEE" >暂支申请单编号：</td><td bgcolor="#FFFFFF">{finance_def_no}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">暂支理由：</td><td bgcolor="#FFFFFF">{adv_remark}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">操    作：</td><td bgcolor="#FFFFFF">{operate}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">操 作 人：</td><td bgcolor="#FFFFFF">{operater}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">备    注：</td><td bgcolor="#FFFFFF">{remark}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">详细信息：</td><td bgcolor="#FFFFFF">{link}</td></tr></table>';
				
				return $model;
		}
}
?>