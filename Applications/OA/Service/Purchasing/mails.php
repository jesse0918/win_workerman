<?php
namespace Service\Purchasing;

use \Workerman\Lib\Timer;
use \Workerman\MySQL;

/**
 *	@desc：定时器，延时发送邮件
 */
class mails
{
		private $mail_send_obj = null;
		
		/**
		 *	@desc:构造函数
		 */
		public function __construct()
		{
		}
		
		/**
		 *	@desc:增加一个时钟
		 */
		public function start_mail()
		{		
				$time_interval = 10;
				echo "================================================================\r\n";
				$timer_id = Timer::add($time_interval, array($this, 'send_mail'), array('to', 'content', &$timer_id));
		}
		
		/**
		 *	@desc:邮件发送
		 *  parm1：邮件发送对象
		 *  Parm2: 邮件正文内容
		 *  Parm3: 间隔时间
		 */
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
    
    /**
     *	@desc:转态判定
     */
    public function obj_status()
    {
    	$db = new \Workerman\MySQL\Connection('localhost', '3306', 'root', 'root', 'oa');
    	
    	//DB 取状态
    	
    	return false;
    }
}