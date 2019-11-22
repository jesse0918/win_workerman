<?php
namespace Service\Message;

use \Workerman\MySQL;

class message_tcc extends message_common
{
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
				
				$sql = "select id from ".$this->tbl_prefix."employee where isleave = 0 order by id asc";
				
				$q = $this->db_conn -> query($sql);
				
				$info = array();
				
				while($r = $this -> db_conn -> fetch_array($q))
				{
						$tmp = $r['id'] % 10;
						
						$info[$tmp][] = "(".$r['id'].",".$send_id.",".$id.",0)";
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
}
?>