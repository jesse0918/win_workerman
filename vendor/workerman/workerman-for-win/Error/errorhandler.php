<?php
namespace Workerman\Error;

use Workerman\PhpMail;

/**
 * 新处理架构，一旦出现错误，立马通知相关人员
 */
class errorhandler
{
		private $log_file  = null;
		
		private $mail_obj  = null;
		
		private $fatal_err = array(E_ERROR,
															 E_PARSE,
															 E_CORE_ERROR,
															 E_CORE_WARNING,
															 E_COMPILE_ERROR,
															 E_COMPILE_WARNING,
															);
		
		public function __construct()
		{
			$mail = new \Workerman\PhpMail\PhpMailer();
		}
		
		public function register()
		{
				//
				set_exception_handler(array($this, 'handleException'));
				//
				set_error_handler(array($this, 'handleError'));
				//
				register_shutdown_function(array($this, 'handleFatalError'));
		}

		/**
		 *  Exception 处理
		 *  Error级别,Mail通知,其他记载文件即可。
		 */
		public function handleException()
		{
				//记载文件
				$log_file = "./../../RunLog/workman_log_".date("Ymd").".log";
				
				//暂定(还没有追加错误处理机制)
		}
		
		/**
		 *  Error 处理
		 *  Error级别,Mail通知,其他记载文件即可。
		 */
		public function handleError($errno, $errstr, $errfile, $errline)
		{		
				switch ($errno) {
		    		case E_USER_ERROR:
		    				//发送通知邮件
		    				//发送通知邮件
								$error = error_get_last();
				
								$mail = new PhpMailer();
				
								$mail -> IsSMTP();   
								$mail -> Host     = 'mail.transcosmos-cn.com"';
								$mail -> SMTPAuth = true;
								$mail -> Username = 'Crm1'; 
								$mail -> Password = 'Password01!';
								$mail -> From     = "crm1@transcosmos-cn.com";
								$mail -> FromName = "大宇宙OA系统";
				
								$mail -> AddAddress ("george.xu@transcosmos-cn.com");
								$mail -> WordWrap = 75;
		
								$mail -> IsHTML(true);		   
								$mail -> CharSet="UTF-8";   

								$mail -> Encoding = "base64";    
								$mail -> Subject  = "WorkMan 用户致命错误!";    
				
								//
								$content = '<div style="font-size:12px"><b>OA系统(WorkerMan)发生用户致命错误，请立即排查!</b><br/><br/></div>
															<table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >
																<tr><td width="60" bgcolor="#EEEEEE">错误Code：</td><td bgcolor="#FFFFFF">'.$errno.'</td></tr>
																<tr><td bgcolor="#EEEEEE">错误信息：</td><td bgcolor="#FFFFFF">'.$errstr.'</td></tr>
																<tr><td bgcolor="#EEEEEE">错误文件：</td><td bgcolor="#FFFFFF">'.$errfile.'</td></tr>
  															<tr><td bgcolor="#EEEEEE">错误行号：</td><td bgcolor="#FFFFFF">'.$errline.'</td></tr>
    													</table>';
								
								$mail -> Body    = $content;    
								$mail -> AltBody = $content;
				
       					$mail -> Send();
       					
       					unset($mail);
		    				break;
		    		default:
		    				break;
		    }
		    
		    //记载内容
		    $log_file = "./../../../RunLog/workman_log_".date("Ymd").".log";
		    $template = '';
		    switch ($errno) {
		    case E_USER_ERROR:
		        $template .= "用户ERROR级错误，必须修复 错误编号[$errno] $errstr ";
		        $template .= "错误位置 文件$errfile,第 $errline 行\n";
		        $log_file = sprintf($log_file,'error');
		        
		        exit(1);

		        break;
		    case E_USER_WARNING:
		        $template .= "用户WARNING级错误，建议修复 错误编号[$errno] $errstr ";
		        $template .= "错误位置 文件$errfile,第 $errline 行\n";
		        $log_file = sprintf($log_file,'warning');
		        break;
		    case E_USER_NOTICE:
		        $template .= "用户NOTICE级错误，不影响系统，可不修复 错误编号[$errno] $errstr ";
		        $template .= "错误位置 文件$errfile,第 $errline 行\n";
						$log_file = sprintf($log_file,'notice');
		        break;
		    default:
		        $template .= "未知错误类型: 错误编号[$errno] $errstr  ";
		        $template .= "错误位置 文件$errfile,第 $errline 行\n";
		        $log_file = sprintf($log_file,'unknown');
		        break;
		    }

		    file_put_contents($log_file,$template,FILE_APPEND);
		}
		
		/**
		 *  Fatal 处理
		 *  Mail通知,OA Owner。
		 */
		public function handleFatalError()
		{
				//发送通知邮件
				$error = error_get_last();
				
				$mail = new \Workerman\PhpMail\PhpMailer();
				
				$mail -> IsSMTP();   
				$mail -> Host     = 'mail.transcosmos-cn.com"';
				$mail -> SMTPAuth = true;
				$mail -> Username = 'Crm1'; 
				$mail -> Password = 'Password01!';
				$mail -> From     = "crm1@transcosmos-cn.com";
				$mail -> FromName = "大宇宙OA系统";
				
				$mail -> AddAddress ("george.xu@transcosmos-cn.com");
				$mail -> WordWrap = 75;
		
				$mail -> IsHTML(true);		   
				$mail -> CharSet  ="UTF-8";   

				$mail -> Encoding = "base64";   
				$mail -> Subject  = "WorkMan 致命错误!";
				
				//
				$content = '<div style="font-size:12px"><b>OA系统(WorkerMan)发生致命错误，请立即排查!</b><br/><br/></div>
											<table width="90%" border=0 cellpadding=6 cellspacing=1 style="font-size:12px" >
												<tr><td width="60" bgcolor="#EEEEEE">错误Code：</td><td bgcolor="#FFFFFF">'.$error['type'].'</td></tr>
												<tr><td bgcolor="#EEEEEE">错误信息：</td><td bgcolor="#FFFFFF">'.$error['message'].'</td></tr>
												<tr><td bgcolor="#EEEEEE">错误文件：</td><td bgcolor="#FFFFFF">'.$error['file'].'</td></tr>
  											<tr><td bgcolor="#EEEEEE">错误行号：</td><td bgcolor="#FFFFFF">'.$error['line'].'</td></tr>
    									</table>';

				$mail -> Body    = $content;    
				$mail -> AltBody = $content;
				
       	$mail -> Send();
       					
        unset($mail);
        
        $this->handleError($error['type'],$error['message'],$error['file'],$error['line']);
        
        exit(1);
		}
}
?>