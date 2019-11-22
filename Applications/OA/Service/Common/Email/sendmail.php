<?php
namespace Service\Common\Email;

use \Workerman\PhpMail;

/*
 * @function MyMailer extend phpmailer  
 * @auther George
 * @Param $SendTo String Not Null
 * @Param $SendCC String Null
 * @Param $SendBCC String Null
 * @Param $SendSubject String Not Null
 * @Param $SendAttachment String Null
 * @Param $SendContent String Not Null
 * @Param $Account String default Null 
 *
 */
Class sendmail
{
	private $mail_set       = null;
	
	private $SendTo         = null;
	
	private $SendCC         = null;
	
	private $SendBCC        = null;
	
	private $SendSubject    = null;
	
	private $SendAttachment = null;
	
	private $SendContent    = null;
	
	private $Account        = null;
	
	public function set_mail_set($mail_set)
	{
		$this->mail_set = $mail_set;
	}
	
	public function set_SendTo($SendTo)
	{
		$this->SendTo = $SendTo;
	}
	
	public function set_SendCC($SendCC)
	{
		$this->SendCC = $SendCC;
	}
	
	public function set_SendBCC($SendBCC)
	{
		$this->SendBCC = $SendBCC;
	}
	
	public function set_SendSubject($SendSubject)
	{
		$this->SendSubject = $SendSubject;
	}
	
	public function set_SendAttachment($SendAttachment)
	{
		$this->SendAttachment = $SendAttachment;
	}
	
	public function set_SendContent($SendContent)
	{
		$this->SendContent = $SendContent;
	}
	
	public function set_Account($Account)
	{
		$this->Account = $Account;
	}

	public function Send()
	{
		$done = 0;
		$error_msg = '';
		
		if(!$this->SendTo || !$this->SendSubject)
		{
			$done += 1;
			$error_msg .= $done.'.收件人、或者邮件主题、或者邮件内容为空</br>';
		}
	
		//过滤特殊字符
		$this->SendSubject = str_replace("\r\n", "\n", $this->SendSubject);
		$this->SendSubject = str_replace("\n", "\r\n", $this->SendSubject);
		$this->SendSubject = str_replace("\r\n.\r\n", "\r\n..\r\n", $this->SendSubject);
	
		if($done > 0){
			return $error_msg;
		}
		else{
			$mail = new \Workerman\PhpMail\PhpMailer(); 		   
			$mail -> IsSMTP();   
			$mail -> Host     = \Service\Common\Config\Config::mail_host;
			$mail -> SMTPAuth = true;
			$mail -> Username = $this->mail_set['user']; 
			$mail -> Password = $this->mail_set['pass']; 
			$mail -> From     = $this->mail_set['from'];
			$mail -> FromName = $this->mail_set['from_name'];
		
			//添加收件人部分
			if($this->SendTo != '')
			{
				if(is_array($this->SendTo)){
					foreach($this->SendTo as $k=>$v)
					{
						if (trim($v)) $mail->AddAddress(trim($v));
					}
				}
			}
		
			//添加抄送部分
			if($this->SendCC != '')
			{
				if(is_array($this->SendCC)){
					foreach($this->SendCC as $k=>$v)
					{
						if (trim($v)) $mail->AddCC(trim($v));
					}
				}
			}
		
			//添加密送部分
			if($this->SendBCC != '')
			{
				if(is_array($this->SendBCC)){
					foreach($this->SendBCC as $k=>$v)
					{
						if (trim($v)) $mail->AddBCC(trim($v));
					}
			
				}
			}
		
			$mail->AddReplyTo($this->mail_set['from_name']);
		
			$mail->WordWrap = 75; //
		
			$mail->IsHTML(true); // HTML格式  		   
			$mail->CharSet="UTF-8"; //编码    

			$mail->Encoding = "base64";    
			$mail->Subject  = $this->SendSubject;    
			$mail->Body     = $this->SendContent;    
			$mail->AltBody  = $this->SendContent;    

			if(!$mail->Send())
			{
				return $mail->ErrorInfo;
			}else{
				return 'success';
			}
		}
	}
}
?>