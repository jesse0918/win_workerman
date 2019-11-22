<?php
namespace Service\Common\Auth;


/**
 *	@desc:�����û���֤��
 */
class auth_check
{
		private $timeStamp;
		
		private $salt;
		
		private $signature;
		
		public function set_timeStamp($timeStamp)
		{
				$this->timeStamp = $timeStamp;
		}
		
		public function set_salt($salt)
		{
				$this->salt = $salt;
		}
		
		public function set_signature($signature)
		{
				$this->signature = $signature;
		}
		
		/**
		 *	@desc:��������
		 */
		public function __construct()
		{
		
		}
		
		/**
		 *	@desc:�û���֤
		 */
		public function auth_chk_info()
		{
				//ʱ���check
				if(empty($this->timeStamp))
				{
						return false;
				}
			
				//��ֹ�������
				list($t1, $t2) = explode(' ', microtime());
				$nowtimeStamp  = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
			
				$time_diff = $nowtimeStamp - $this->timeStamp;
			
				if($time_diff > 210000)
				{
						return false;
				}
			
				//��ȡƾ֤
				$server_signature = $this->get_auth_sign();
			
				//checkƾ֤;
				if($server_signature === $this->signature)
				{
						return true;
				}
			
				return  false;
		}
		
		/**
		 *	@desc:ǩ��������
		 */
		private function get_auth_sign()
		{
				$rtn_signature = null;
				//�����
				$salt      = rand(0,1000);
				//����ǩ��
				$arr =array(\Service\Common\Config\config::api_token,
										$this->timeStamp,
										$this->salt,
										\Service\Common\Config\config::secrct_key);

				sort($arr,SORT_STRING);

				$str = implode($arr);
				
				$rtn_signature = sha1($str);
				$rtn_signature = md5($rtn_signature);
				
				return $rtn_signature ;
		}
}
?>