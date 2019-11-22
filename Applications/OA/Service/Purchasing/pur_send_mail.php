<?php
namespace Service\Purchasing;

use \Workerman\Lib\Timer;
use \Workerman\MySQL;

/**
 *  邮件发送规则
 *  说明:整个邮件的规则
 */
class pur_send_mail
{
		private $db_conn     = null;
		
		private $tbl_prefix  = null;
		
		private $object_mail = null;
		
		private $mail_set    = null;
		
		private $mail_temp   = null;
		
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
				
				$this->mail_set   = \Service\Common\Config\config::$mail_grp_pur;
				
				$this->object_mail = new \Service\Common\Email\sendmail();
				
				$this->object_mail -> set_mail_set($this->mail_set);
				
				$this->mail_temp_obj  = new \Service\Purchasing\mail_module();
		}
		
		/**
		 *  邮件主处理
		 */
		public function send_main($id)
		{
				$send_id_arr = array();
				
				$send_id_arr = explode(",",$id);
				
				foreach($send_id_arr as $value)
				{
						if(empty($value))
						{
								continue;
						}
						$pur_info  = $this -> get_pur_info($value);
						
						//审核人
						$approver  = array();

						if($pur_info['group_flg'] == "1")
						{
								$approver = $this -> get_group_user($pur_info['current_approve_user']);
						}
						else
						{
								$approver[] = $pur_info['current_approve_user'];
						}
						
						$mail_temp = $this -> mail_temp_obj -> get_email_moudle();
						
						$mail_temp = str_replace('{$department}'  ,str_replace(",","<br>",$pur_info['dept_name'])   ,$mail_temp);
						$mail_temp = str_replace('{$cost_code}'   ,str_replace(",","<br>",$pur_info['cost_code'])   ,$mail_temp);
						$mail_temp = str_replace('{$created_user}',$pur_info['created_user'],$mail_temp);
						$mail_temp = str_replace('{$created_date}',$pur_info['created_date'],$mail_temp);
						$mail_temp = str_replace('{$demand_desc}' ,$pur_info['demand_desc'] ,$mail_temp);
						$mail_temp = str_replace('{$reason}'      ,$pur_info['apply_reason'],$mail_temp);
						$mail_temp = str_replace('{$show_audit}'  ,""                       ,$mail_temp);
						$mail_temp = str_replace('{$approve_user}',implode("<br>",$approver),$mail_temp);
						
						if(empty($pur_info['remark']))
						{
								$show_content = "display:none";
						}
						else
						{
								$show_content = "display:block";
						}
						$mail_temp = str_replace('{$show_content}',$show_content      ,$mail_temp);
						$mail_temp = str_replace('{$remark}'      ,$pur_info['remark'],$mail_temp);
						
						$operate_task = "";
						switch($pur_info['audit_page'])
						{
								case '1':
										$operate_task = "approve_manager";
										break;
								case '2':
										$operate_task = "approve_it";
										break;
								case '3':
										$operate_task = "approve_admin";
										break;
								case '4':
										$operate_task = "approve_other";
										break;
								case '5':
										$operate_task = "approve_contract";
										break;
						}
						
						$url = '请<a href="' . \Service\Common\Config\config::html_url . '/?mod=new_purchasing&task='.$operate_task.'&id=' . $value . '">点击审核</a>';

						$mail_title = null;
						switch($pur_info['approve_result'])
						{
								case '0':
										$mail_title = '【采购申请-' . $value . '】新的申请等待您审核';
										break;
								case '1':
										$mail_title = '【采购申请-' . $value . '】需要复核';
										break;
								case '2':
										$mail_title = '【采购申请-' . $value . '】被拒绝';
										$url = '请<a href="' . \Service\Common\Config\config::html_url . '/?mod=new_purchasing&task=view&id=' . $value . '">点击查看</a>';
										break;
								case '3':
										$mail_title = '【采购申请-' . $value . '】采购被取消';
										$url = '请<a href="' . \Service\Common\Config\config::html_url . '/?mod=new_purchasing&task=view&id=' . $value . '">点击查看</a>';
										break;
								case '4':
										$mail_title = '【采购申请-' . $value . '】采购申请审核完成';
										$url = str_replace ( '点击审核', '点击完成采购', $url );
										break;
								case '5':
										$mail_title = '【采购申请-' . $value . '】采购完成，请确认收货';
										$url = str_replace ( '点击审核', '点击确认收货', $url );
										break;
								case '6':
										$mail_title = '【采购申请-' . $value . '】需求部门确认收货完成';
										$url = '请<a href="' . \Service\Common\Config\config::html_url . '/?mod=new_purchasing&task=view&id=' . $id . '">点击查看</a>';
										break;
								case '6':
										$mail_title = '【采购申请-' . $value . '】法务部原件归档完成';
										$url = '请<a href="' . \Service\Common\Config\config::html_url . '/?mod=new_purchasing&task=view&id=' . $id . '">点击查看</a>';
										break;
								default:
										$mail_title = '【采购申请-' . $value . '】新的申请等待您审核';
										break;
						}
						$mail_temp = str_replace('{$link}'         ,$url       ,$mail_temp);
						$mail_temp = str_replace('{$subject}'      ,$mail_title,$mail_temp);
						
						$tmp = array_diff($approver,array($pur_info['created_user']));
						$mail_temp.="<br>SC:".implode(";",$approver);
						$mail_temp.="<br>CC:".implode(";",$tmp);
						//$this->object_mail->set_SendTo($approver);
						//if(!empty($tmp))
						//{
						//$this->object_mail->set_SendCC(array($pur_info['created_user']);
						//}
						$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
						
						$this->object_mail->set_SendSubject($mail_title);
						$this->object_mail->set_SendContent($mail_temp);

						$this->object_mail->send();
				}
		}
		
		/**
		 *  获取当前采购数据的信息
		 */
		private function get_pur_info($id)
		{
				$sql = "select group_concat(b.dept_name) dept_name,
												group_concat(b.cost_code) cost_code,
												a.created_user,
												a.created_date,
												a.goods_desc,
												a.demand_desc,
												a.apply_reason,
												a.current_approve_user,
												a.audit_page,
												a.level,
												a.status,
												a.approve_result,
												d.next_approve_user,
												d.remark,
												e.group_flg
										from ".$this->tbl_prefix."purchasing a
										left join ".$this->tbl_prefix."purchasing_dept b on a.id = b.purchasing_id
										left join (select max(id) id from ".$this->tbl_prefix."purchasing_approve where parentid = ".$id.") c on 1 = 1
										left join ".$this->tbl_prefix."purchasing_approve d on c.id = d. id
										left join ".$this->tbl_prefix."purchasing_rule e on a.purchase_process = e.number and a.item_type = e.type and e.level = a.level and e.active = 1
										where a.id = ".$id;
				$q = $this->db_conn -> query($sql);
				
				return $this->db_conn -> fetch_array($q);
		}
		
		/**
		 *  获取群组用户
		 */
		private function get_group_user($positon_name)
		{
				$sql = "select a.group_email
									from ".$this->tbl_prefix."purchasing_group a
									left join ".$this->tbl_prefix."employee b on a.group_empcode = b.empcode
									where a.positon_name = '".$positon_name."'
										and b.isleave = '0'";
				$rtn_info = array();

				$q = $this->db_conn -> query($sql);

				while($r = $this->db_conn -> fetch_array($q))
				{
						$rtn_info[] = $r['group_email'];
				}
				
				return $rtn_info;
		}
}
?>