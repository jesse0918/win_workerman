<?php
namespace Service\Message;

use \Workerman\MySQL;

/**
 *	@发送邮件
 */
class message_mail extends message_common
{
		private $object_mail = null;
		
		private $mail_set    = null;
		
		private $msg_info    = null;
		
		private $emp_list    = null;
		
		/**
		 *	@desc:构造函数
		 */
		public function __construct()
		{
				parent::__construct();
				
				$this->mail_set   = \Service\Common\Config\config::$mail_grp_fin;
				
				$this->object_mail = new \Service\Common\Email\sendmail();
				
				$this->object_mail -> set_mail_set($this->mail_set);
		}

		/**
		 *	@desc : 增加邮件数据
		 *	@Parm1: 邮件id
		 *
		 */
		public function send_message($id)
		{
				$this -> get_message_info($id);
				
				$this -> get_emp_info($id);
				
				//1：单独发送；群组发送
				switch($this -> msg_info['msg_type'])
				{
						case '1':
								$this -> send_one_by_one();
								break;
						case '0':
								$this -> send_one_by_all();
								break;
				}
		}

		/**
		 *	@desc:获取发件信息
		 *	@Parm1: 邮件id
		 */
		private function get_message_info($id)
		{
				$sql = "select msg_type,
												send_range,
												send_emp,
												uid,
												subject,
												content
									from ".$this->tbl_prefix."message_list
									where id = ".$id;

				$q   = $this->db_conn -> query($sql);
				
				$this -> msg_info =  $this->db_conn -> fetch_array($q);
		}
		
		/**
		 *	@desc：员工信息获取
		 */
		public function get_emp_info($id)
		{
				for($i=0;$i<10;$i++)
				{
						$sql = "select b.email
											from ".$this->tbl_prefix."user_message_list_".$i." a
											left join ".$this->tbl_prefix."employee b on a.uid = b.id
											where a.msg_id = ".$id;
						$q   = $this->db_conn -> query($sql);
				
						while($r=  $this->db_conn -> fetch_array($q))
						{
								if(empty($r['email'])) continue;
								
								$this -> emp_list[] = $r['email'];
						}
				}
		}
		
		/**
		 *	@desc:单独邮件发送
		 */
		public function send_one_by_one()
		{
				if(empty($this -> emp_list)) return;
				
				foreach($this -> emp_list as $value)
				{
						//$this->object_mail->set_SendTo(array($value));
						$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
						$this->object_mail->set_SendSubject($this -> msg_info['subject']);
						$this->object_mail->set_SendContent($this -> msg_info['content']);
						$this->object_mail->send();
				}
		}
		
		/**
		 *	@desc:群发邮件，一个发送对象
		 */
		public function send_one_by_all()
		{
				if(empty($this -> emp_list)) return;
				
				//$this->object_mail->set_SendTo($this -> emp_list);
				$this->object_mail->set_SendTo(array("george.xu@transcosmos-cn.com"));
				$this->object_mail->set_SendSubject($this -> msg_info['subject']);
				$this->object_mail->set_SendContent($this -> msg_info['content']);
				$this->object_mail->send();
		}
}
?>