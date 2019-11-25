<?php
namespace Service\Common\Auth;

/**
 *	@desc:请求用户验证。
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
		 *	@desc:构造函数
		 */
		public function __construct()
		{
		
		}
		
		/**
		 *	@desc:用户验证
		 */
		public function auth_chk_info()
		{
				//时间的check
				if(empty($this->timeStamp)) return false;
			
				//防止恶意访问
				list($t1, $t2) = explode(' ', microtime());
				$nowtimeStamp  = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
			
				$time_diff = $nowtimeStamp - $this->timeStamp;
			
				if($time_diff > 210000) return false;
			
				//获取凭证
				$server_signature = $this->get_auth_sign();
			
				//check凭证;
				if($server_signature === $this->signature) return true;
			
				return  false;
		}
		
		/**
		 *	@desc:签名的生成
		 */
		private function get_auth_sign()
		{
				$rtn_signature = null;
				//随机数
				$salt      = rand(0,1000);
				//生成签名
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