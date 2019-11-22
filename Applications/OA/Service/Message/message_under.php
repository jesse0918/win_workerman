<?php
namespace Service\Message;

use \Workerman\MySQL;

class message_under extends message_common
{
		private $all_under = null;

		public function __construct()
		{
				parent::__construct();
		}

		/**
		 *
		 */
		public function send_main($id,$send_id)
		{
				$insert_info = array();
				
				for($i=0 ;$i<10;$i++)
				{
						$insert_info[$i] = "insert into ".$this->tbl_prefix."user_message_list_".$i." (uid,send_uid,msg_id,status)";
				}
				
				$this -> get_all_under($send_id);
				
				if(empty($this -> all_under)) return;
				
				$info = array();
				
				foreach($this -> all_under as $value)
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
		 *
		 */
		public function get_all_under($userid)
		{
				$sql = "select a.id 
									from ".$this->tbl_prefix."employee a
									left join ".$this->tbl_prefix."employee b on a.report_to = b.email
									where a.isleave = 0 
										and b.id = ".$userid;
				$q = $this->db_conn -> query($sql);
				
				while($r = $this->db_conn -> query($q))
				{
						$this -> all_under[] = $r['id'];
						$this -> get_all_under($r['id']);
				}
		}
}
?>