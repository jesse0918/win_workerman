<?php
namespace Service\Message;

use \Workerman\MySQL;

/**
 *	@desc:按成本中心获取人员信息
 */
class message_cost_code extends message_common
{
		/**
		 *	@desc:构造函数
		 */
		public function __construct()
		{
				parent::__construct();
		}

		/**
		 *	@desc:邮件发送处理
		 */
		public function send_main($id,$send_id)
		{
				$insert_info = array();
				
				for($i=0 ;$i<10;$i++)
				{
						$insert_info[$i] = "insert into ".$this->tbl_prefix."user_message_list_".$i." (uid,send_uid,msg_id,status)";
				}
				
				$user_info = $this -> get_all_dept_user($send_id);
				
				if(empty($user_info)) return;
				
				$info = array();
				
				foreach($user_info as $value)
				{
						$tmp = $value % 10;
						
						$info[$tmp][] = "(".$value.",".$send_id.",".$id.",0)";
				}
				
				if(!empty($info))
				{
						foreach($info as $key => $value)
						{
								if(empty($value)) continue;
								
								$inset_sql = $insert_info[$key] ." values ". implode(",",$value);
								$this->db_conn -> query($inset_sql);
						}
				}
		}
		
		/**
		 *	@desc:获取部门信息
		 */
		public function get_all_dept_user($userid)
		{
				//
				$manager_id = $this -> get_manager($userid);
				
				$sql = "select a.id
									from ".$this->tbl_prefix."employee a
									left join ".$this->tbl_prefix."employee_org b on a.id = b.userid and b.active = 1
									left join ".$this->tbl_prefix."org_orginfo c on c.finance_code = right(b.cost_code,6)
									right join (select c.finance_code 
																from ".$this->tbl_prefix."employee a
																left join ".$this->tbl_prefix."employee_org b on a.id = b.userid and b.active = 1
																left join ".$this->tbl_prefix."org_orginfo c on c.finance_code = right(b.cost_code,6)
																where a.isleave = 0 and a.id = ".$userid.") d on d.finance_code = c.finance_code
									where a.isleave = 0
										and a.job_type in ('SV','Staff','TL','AG')";
				$q = $this->db_conn -> query($sql);
				$info = array();
				
				if(!empty($manager_id)) $info[] = $manager_id;
				
				while($r = $this -> db_conn -> fetch_array($q))
				{
						$info[] = $r['id'];
				}

				return $info;
		}
		
		/**
		 *	@desc:获取经理信息
		 */
		private function get_manager($userid)
		{
				$sql = "select b.id,b.job_type
									from ".$this->tbl_prefix."employee a
									left join ".$this->tbl_prefix."employee b on a.report_to = b.email
									left join ".$this->tbl_prefix."employee_org d on a.id = d.userid and d.active = 1
									left join ".$this->tbl_prefix."org_orginfo c on c.finance_code = right(b.cost_code,6)
									right join (select c.finance_code 
																from ".$this->tbl_prefix."employee a
																left join ".$this->tbl_prefix."employee_org b on a.id = b.userid and b.active = 1
																left join ".$this->tbl_prefix."org_orginfo c on c.finance_code = right(b.cost_code,6)
																where a.isleave = 0 and a.id = ".$userid.") f on f.finance_code = c.finance_code
									where a.id = ". $userid;
				$q = $this->db_conn -> query($sql);
				
				$r = $this->db_conn -> fetch_array($q);
				
				if($r)
				{
						if(in_array($r['job_type'],array("Manager","Director")))
						{
								return $r['id'];
						}
						else
						{
								return $this -> get_manager($r['id']);
						}
				}
				else
				{
						return null;
				}
		}
}
?>