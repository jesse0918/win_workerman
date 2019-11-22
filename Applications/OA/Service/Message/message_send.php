<?php
namespace Service\Message;

use \Workerman\MySQL;

class message_send extends message_common
{
		public function __construct()
		{
				parent::__construct();
		}

		/**
		 *	@desc : 增加邮件数据
		 *	@Parm1: 邮件id
		 *
		 */
		public function user_add($id)
		{
				if(empty($id)) return;
				
				$m_info = $this -> get_message_info($id);
				
				if(empty($m_info)) return;
				
				if($m_info['msg_type'] == "1")
				{
						switch($m_info['send_range'])
						{
								case '0':
										$obj = new \Service\Message\message_tcc();
										$obj -> send_main($id,$m_info['uid']);
										break;
								case '1':
								case '2':
								case '3':
										$obj = new \Service\Message\message_select();
										$obj -> send_main($id,$m_info['uid']);
										break;
								case '4':
										$obj = new \Service\Message\message_emp();
										$obj -> send_main($id,$m_info['uid'],$m_info['send_emp']);
										break;
						}
				}
				else
				{
						switch($m_info['send_range'])
						{
								case '0':
										$obj = new \Service\Message\message_cost_code();
										$obj -> send_main($id,$m_info['uid']);
										break;
								case '1':
										$obj = new \Service\Message\message_emp();
										$obj -> send_main($id,$m_info['uid'],$m_info['send_emp']);
										break;
								case '2':
										$obj = new \Service\Message\message_fun_dept();
										$obj -> send_main($id,$m_info['uid'],$m_info['send_emp']);
										break;
								case '3':
										$obj = new \Service\Message\message_under();
										$obj -> send_main($id,$m_info['uid']);
										break;
						}
				}
		}

		/**
		 *	@desc:获取发件信息
		 */
		private function get_message_info($id)
		{
				$sql = "select msg_type,
												send_range,
												send_emp,
												uid
									from ".$this->tbl_prefix."message_list
									where id = ".$id;

				$q   = $this->db_conn -> query($sql);
				
				return $this->db_conn -> fetch_array($q);
		}
}
?>