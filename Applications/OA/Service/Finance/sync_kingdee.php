<?php
namespace Service\Finance;

use \Workerman\MySQL;
/**
 *	@desc:������ݵ�ʵʱͬ��
 */
class sync_kingdee
{
		/**
		 *	@desc:���캯��
		 */
		public function __construct()
		{
				
		}
		
		/**
		 *	@desc ���ͬ��
		 *	@parm1:������
		 */
		public function sync($def_no)
		{
				/* �����Զ�ȷ����� */
				define("JAVA_HOSTS", "127.0.0.1:8080");
	
				require_once(__Dir__."..\..\Common\Java\Java_main.php");
	
				java_set_file_encoding("utf-8"); 
				//
				$system = java_process("transcosmos.oa.finance.finance_sync_kingdee");
				
				//
				$sql_parm = array();
				$sql_parm['user']     = \Service\Common\Config\config::db_user;
				$sql_parm['pass']     = \Service\Common\Config\config::db_pass;
				$sql_parm['host']     = \Service\Common\Config\config::db_host;
				$sql_parm['database'] = \Service\Common\Config\config::db_name;

				$sync_date = "";
	
				$system->sync($sql_parm,$sync_date,$def_no);
		}
}
?>