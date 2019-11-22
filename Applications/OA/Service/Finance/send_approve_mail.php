<?php
namespace Service\Finance;

use \Workerman\MySQL;

/**
 *  @desc ：财务审核
 */

class send_approve_mail
{
		private $db_conn      = null;
		
		private $tbl_prefix   = null;
		
		private $object_mail  = null;
		
		private $mail_set     = null;
		
		private $sample_flag  = null;
		
		private $finance_type = null;
		
		private $msg_info     = null;
		
		private $sc_info      = null;
		
		private $cc_info      = null;
		
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
						
						$this->tbl_prefix  = \Service\Common\Config\config::g_tbl_prefix;
						
						$this->mail_set    = \Service\Common\Config\config::$mail_grp_fin;
				
						$this->object_mail = new \Service\Common\Email\sendmail();
				
						$this->object_mail -> set_mail_set($this->mail_set);
				}
		}
		
		public function finance_approve_notice($finance_def_no)
		{
				//
				$tmp_def_no = explode(",",$finance_def_no);
				
				if(!empty($tmp_def_no))
				{
						foreach($tmp_def_no as $value)
						{
								$this -> sc_info = null;
				
								$this -> cc_info = null;
				
								if(empty($value)) continue;
								
								$this -> get_finance_type($value);
				
								$this -> get_finance_info($value);
				
								$this -> pay_send_mail();
						}
				}
		}
		
		private function get_finance_type($def_no)
		{
				if(empty($def_no)) return null;
				
				$sql =	"select a.finance_def_no,
												b.finance_def_no tel_flag,
												c.finance_def_no oth_flag,
												d.finance_def_no ent_flag,
												e.finance_def_no bus_flag,
												f.finance_def_no tb_flag,
												g.finance_def_no tra_flag,
												a.pro_sam
										from 
												".$this->tbl_prefix."finance_list a
												left join ".$this->tbl_prefix."finance_telephone b on a.finance_def_no = b.finance_def_no and b.wiped_type = '00'
												left join ".$this->tbl_prefix."finance_telephone c on a.finance_def_no = c.finance_def_no and b.wiped_type = '01'
												left join ".$this->tbl_prefix."finance_entertain d on a.finance_def_no = d.finance_def_no
												left join ".$this->tbl_prefix."finance_business e on a.finance_def_no = e.finance_def_no
												left join ".$this->tbl_prefix."finance_team_building f on a.finance_def_no = f.finance_def_no
												left join ".$this->tbl_prefix."finance_traffice g on a.finance_def_no = g.finance_def_no
												where a.finance_def_no = '".$def_no."'";
				
				$q = $this->db_conn -> query($sql);
				
				$r = $this->db_conn -> fetch_array($q);
				
				$this -> finance_type = "";
				
				$this -> sample_flag  = 0;
				
				if(!empty($r['tel_flag']))
				{
						$this -> finance_type = "1";
				}
				else if (!empty($r['oth_flag']))
				{
						$this -> finance_type = "5";
				}
				else if (!empty($r['ent_flag']))
				{
						$this -> finance_type = "3";
				}
				else if (!empty($r['bus_flag']))
				{
						$this -> finance_type = "2";
				}
				else if (!empty($r['tra_flag']))
				{
						$this -> finance_type = "4";
				}
				else if (!empty($r['tb_flag']))
				{
						$this -> finance_type = "6";
				}
				
				$this -> sample_flag = $r['pro_sam'];
		}
		
		private function get_finance_info($finance_def_no)
		{
				$sql = "select a.finance_def_no,
												a.position,
												a.approve_status,
												a.approve_result,
												a.remark,
												a.create_user,
												f.e_name create_e_name,
												d.email,
												b.company_name,
												e.type_cd,
												b.pro_sam,
												b.pro_flag,
												b.pro_sts,
												b.pro_obj,
												b.total_cost,
												b.site,
												h.group_flg
										from ".$this->tbl_prefix."finance_approve a
										left join ".$this->tbl_prefix."finance_list b on a.finance_def_no = b.finance_def_no
										left join ".$this->tbl_prefix."employee d on b.emp_code = d.empcode
										left join ".$this->tbl_prefix."common_cd e on e.type_key = 'K006' and e.type_name = b.company_name
										left join ".$this->tbl_prefix."employee f on a.create_user = f.empcode
										left join ".$this->tbl_prefix."finance_rule_meisei h on h.pid = b.pro_typ and h.level = b.pro_num
										where a.finance_def_no = '".$finance_def_no."'
										order by a.id desc
										limit 1";

				$q = $this->db_conn -> query($sql);

				$this -> msg_info = $this->db_conn -> fetch_array($q);
				
		}
		
		private function pay_send_mail()
		{
				if(empty($this -> msg_info)) return;

				$mail_temp = $this ->pay_mail_model();
				
				//群组处理
				//if()
				//{
				//}
				$mail_title = "";
				$f_type     = "";
				$html       = "";
				
				if($this -> msg_info['pro_sts'] == "99")
				{
						//当前审核人取得
						$this -> sc_info = array($this -> msg_info['email']);
						$this -> cc_info = null;
						
						$before = "";
						if($this -> sample_flag == "1") $before = "报销申请";
						if($this -> sample_flag == "2") $before = "报销申请";
						
						switch($this -> finance_type)
						{
								case '1':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核完成";
										$f_type        = "手机费";
										$html          = \Service\Common\Config\config::html_url."?mod=finance&task=tel_view&def_no=".$this -> msg_info['finance_def_no'];
										break;
								case '2':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核完成";
										$f_type        = "差旅费";
										$html          = \Service\Common\Config\config::html_url."?mod=finance&task=bus_view&def_no=".$this -> msg_info['finance_def_no'];
										break;
								case '3':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核完成";
										$f_type        = "业务招待费";
										$html          = \Service\Common\Config\config::html_url."?mod=finance&task=ent_view&def_no=".$this -> msg_info['finance_def_no'];
										break;
								case '6':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核完成";
										$f_type        = "团建费";
										$html          = \Service\Common\Config\config::html_url."?mod=finance&task=tb_view&def_no=".$this -> msg_info['finance_def_no'];
										break;
						}
				}
				else if($this -> msg_info['pro_sts'] == "05")
				{
						//当前审核人取得
						if($this -> msg_info['group_flg'] != "1")
						{
								$this -> sc_info = $this -> get_user_email($this -> msg_info['pro_obj']);
						}
						else
						{
								$this -> sc_info = $this -> get_group_email($this -> msg_info['pro_obj'],$this -> msg_info['site']);
						}
						
						$this -> cc_info = array($this -> msg_info['email']);
						
						if($this -> sample_flag == "1") $before = "报销申请";
						if($this -> sample_flag == "2") $before = "报销申请";

						switch($this -> finance_type)
						{
								case '1':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "手机费";
										break;
								case '2':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "差旅费";
										break;
								case '3':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "业务招待费";
										break;
								case '4':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "交通费";
										break;
								case '5':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "其他费用";
										break;
								case '6':
										$mail_title    = $before."【".$this -> msg_info['finance_def_no']."】审核通知";
										$f_type        = "团建费";
										break;
						}
						$html          = \Service\Common\Config\config::html_url."?mod=finance&task=main_view&def_no=".$this -> msg_info['finance_def_no'];
				}else
				{
				}
				
				$mail_content  = $mail_temp;
				
				$mail_content  = str_replace("{subject}"          ,$mail_title                          ,$mail_content);
				$mail_content  = str_replace("{finance_def_no}"   ,$this -> msg_info['finance_def_no']  ,$mail_content);
				$mail_content  = str_replace("{finance_type}"     ,$f_type                              ,$mail_content);
				$mail_content  = str_replace("{total_cost}"       ,$this -> msg_info['total_cost']      ,$mail_content);
				$mail_content  = str_replace("{operater}"         ,$this -> msg_info['create_e_name']   ,$mail_content);
				$mail_content  = str_replace("{remark}"           ,$this -> msg_info['remark']          ,$mail_content);
			
				if($this -> msg_info['pro_sts'] == "05")
				{
						$url = '请<a href="' . $html . '">点击审核</a>';
				}
				else if($this -> msg_info['pro_sts'] == "99")
				{
						$url = '请<a href="' . $html . '">点击查看</a>';
				}
				$mail_content  = str_replace("{link}"             ,$url                                 ,$mail_content);
				
				$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
				//$this->object_mail->set_SendTo($this -> sc_info);
				//$this->object_mail->set_SendCC($this -> cc_info);

				$this->object_mail->set_SendSubject($mail_title);
				$this->object_mail->set_SendContent($mail_content);

				$this->object_mail->send();
		}
		
		private function pay_mail_model()
		{
				$model = '';
				
				$model = '<div style="font-size:12px"><b>{subject}</b><br/><br/></div><table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >';
				$model .= '<tr><td width="100px" bgcolor="#EEEEEE" >报销单编号：</td><td bgcolor="#FFFFFF">{finance_def_no}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">费用类型：</td><td bgcolor="#FFFFFF">{finance_type}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">金    额：</td><td bgcolor="#FFFFFF">{total_cost}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">操 作 人：</td><td bgcolor="#FFFFFF">{operater}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">备    注：</td><td bgcolor="#FFFFFF">{remark}</td></tr>';
				$model .= '<tr><td bgcolor="#EEEEEE">详细信息：</td><td bgcolor="#FFFFFF">{link}</td></tr></table>';
				
				return $model;
		}
		
		private function get_user_email($empcode)
		{
				if(empty($empcode)) return ;
				
				$sql = "select email from ".$this->tbl_prefix."employee where empcode = '".$empcode."'";
				
				return $this->db_conn -> select($sql);
		}
		
		private function get_group_email($positon_id,$site)
		{
				if(empty($positon_id)) return ;
				
				$sql = "select c.email,d.type_name from ".$this->tbl_prefix."finance_position_set a
									left join ".$this->tbl_prefix."finance_authority b on b.pri_type = a.position_name
									left join ".$this->tbl_prefix."employee c on b.pri_empcode = c.empcode
									left join ".$this->tbl_prefix."common_cd d on d.type_key = 'k013' and b.company_cd = d.type_cd
									where a.id = ".$positon_id;
				$grp_chk = false;
				
				$q = $this->db_conn -> query($sql);
				
				$rtn_info = array();
				$tmp_info = array();
				
				while($r = $this->db_conn -> fetch_array($q))
				{
						if(!empty($r['type_name'])) $grp_chk = true;
						$tmp_info[] = $r;
				}
				
				foreach($tmp_info as $key => $value)
				{
						if($tmp_info)
						{
								if($value['type_name'] == $site) $rtn_info[] = $value['email'];
						}
						else
						{
								$rtn_info[] = $value['email'];
						}
				}
				
				return $rtn_info;
		}
}
?>