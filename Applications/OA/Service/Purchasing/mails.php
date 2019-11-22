<?php
namespace Service\Purchasing;

use \Workerman\Lib\Timer;
use \Workerman\MySQL;

class Mails
{
		private $mail_send_obj = null;
		
		public function __construct()
		{
			
		}
		
		public function start_mail()
		{		
				$time_interval = 10;
				echo "================================================================\r\n";
				$timer_id = Timer::add($time_interval, array($this, 'send_mail'), array('to', 'content', &$timer_id));
		}
		
    public function send_mail($to, $content, $timer_id)
    {
        Timer::del($timer_id);
        
        $mail_send = new \Service\Common\Email\sendmail();
        $mail_send -> Send();
        unset($mail_send);
        
        if($this->obj_status())
        {
        }
        else
        {
        		$time_interva = 20;
        	  $timer_id = Timer::add($time_interva, array($this, 'send_mail'), array('to', 'content', &$timer_id));
        }
    }
    
    public function obj_status()
    {
    	$db = new \Workerman\MySQL\Connection('localhost', '3306', 'root', 'root', 'oa');
    	
    	return false;
    }
}