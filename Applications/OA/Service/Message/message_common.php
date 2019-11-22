<?php
namespace Service\Message;

use \Workerman\MySQL;

class message_common
{
		protected $db_conn      = null;

		protected $tbl_prefix   = null;

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
		}
}
?>