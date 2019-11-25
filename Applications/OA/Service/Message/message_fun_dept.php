<?php
namespace Service\Message;

use \Workerman\MySQL;

/**
 *	@desc:按处理群组获取人员信息
 */
class message_fun_dept extends message_common
{
		public function __construct()
		{
				parent::__construct();
		}

		/**
		 *	@desc:邮件发送处理
		 */
		public function send_main($id,$send_id,$fun_id)
		{
				$insert_info = array();
				
				for($i=0 ;$i<10;$i++)
				{
						$insert_info[$i] = "insert into ".$this->tbl_prefix."user_message_list_".$i." (uid,send_uid,msg_id,status)";
				}
				
				$user_info = $this -> get_fun_dept_user($fun_id);
				
				if(empty($user_info)) return;
				
				$q = $this->db_conn -> query($sql);
				
				$info = array();
				
				foreach($user_info as $value)
				{
						$tmp = $r['id'] % 10;
						
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
		 *	@desc:获取发送群组信息
		 */
		public function get_fun_dept_user($fun_id)
		{
				$sql = "select b.id 
									from ".$this->tbl_prefix."message_authority a
									left join ".$this->tbl_prefix."employee b on a.pri_empcode = b.empcode
									where a.pri_type = 'rece'
										and b.isleave = 0
										and b.pri_msg_group = ".$fun_id;
				$q = $this->db_conn -> query($sql);
				$info = array();
				while($r = $this -> db_conn -> fetch_array($q))
				{
						$info[] = $r['id'];
				}
				
				return $info;
		}
}
?>