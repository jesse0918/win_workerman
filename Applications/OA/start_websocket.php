<?php

use \Workerman\Worker;
use \Workerman\Error;

require_once __DIR__ . '/../../vendor/autoload.php';

//错误捕捉机制
//$erroe_hander = new  \Workerman\Error\errorhandler();
//$erroe_hander -> register();

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// SSH连接用
//{
//$context = array(
//    'ssl' => array(
//        'local_cert'  => 'D:\\newstruct\\ssl\\server.crt',
//        'local_pk'    => 'D:\\newstruct\\ssl\\server.key',
//        'verify_peer' => false,
//    )
//);
//
//$ws_worker = new Worker("text://0.0.0.0:7272", $context);
//
//$ws_worker->transport = 'ssl';
//}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

$ws_worker = new Worker("text://0.0.0.0:7272");

$ws_worker->count = 4;

$ws_worker->name  = "OA";

$ws_worker->onMessage = function($connection, $message)
{
		$message_data = json_decode($message, true);
  	
		if(!$message_data) return;
  	
		if(!isset($message_data ['api_time']) || !isset($message_data ['api_salt']) || !isset($message_data ['api_signature'])) 	return;

    //用户认证
    $auth_chk = new \Service\Common\Auth\auth_check();
    $auth_chk ->set_timeStamp($message_data ['api_time']);
    $auth_chk ->set_salt($message_data ['api_salt']);
    $auth_chk ->set_signature($message_data ['api_signature']);
    
    //验证失败
    if(!($auth_chk->auth_chk_info())) return;
    
    if(!isset($message_data ['mod'])) return;

    switch($message_data ['mod'])
    {
    	case "finance":
    		//财务金额再计算
    		$tmp_class = "\\Service\\Finance\\".$message_data ['class'];
    		
    		if(class_exists($tmp_class))
    		{
    				$call_class  = new $tmp_class();
    				if(isset($message_data ['method']))
    				{
    						switch ($message_data ['method'])
    						{
    								case 'approve_notice':
    										call_user_func_array(array($call_class,$message_data ['method']),array($message_data ['type'],$message_data ['def_no']));
    										break;
    								case 'notice':
    										call_user_func(array($call_class,$message_data ['method']));
    										break;
    								case 'finance_approve_notice':
    										call_user_func_array(array($call_class,$message_data ['method']),array($message_data ['def_no']));
    										break;
    						}
    				}
    				else
    				{
    						call_user_func(array($call_class,"cost_count"),$message_data ['def_no']);
    				}
    		}
    		
    		break;
    	case "purchasing":
    		//定时邮件发送
    		$tmp_class = "\\Service\\Purchasing\\".$message_data ['class'];
    		if(class_exists($tmp_class))
    		{
    				$call_class  = new $tmp_class();
    				call_user_func(array($call_class ,"send_main"),$message_data ['id']);
    		}
				break;
			case "train_notice_mail":
				$tmp_class = "\\Service\\Train\\".$message_data ['class'];
    		if(class_exists($tmp_class))
    		{
    			$call_class  = new $tmp_class();
    			call_user_func(array($call_class ,"send_mail"),array("",$message_data ['def_no'],""));
    		}
				break;
			case "alerm":
				$tmp_class = "\\Service\\Alerm\\".$message_data ['class'];
    		if(class_exists($tmp_class))
    		{
    			$call_class  = new $tmp_class();
    			call_user_func(array($call_class ,"info_check"),array($message_data ['alerm_id']));
    		}
				break;
			case "Message":
				$tmp_class = "\\Service\\Message\\".$message_data ['class'];
				if(class_exists($tmp_class))
    		{
    			$call_class  = new $tmp_class();
    			if(isset($message_data ['method']))
    			{
    					switch ($message_data ['method'])
    					{
    							case 'user_add':
    									call_user_func_array(array($call_class,$message_data ['method']),array($message_data ['msg_id']));
    									break;
    							case  'send_message':
    									call_user_func_array(array($call_class,$message_data ['method']),array($message_data ['msg_id']));
    									break;
    					}
    			}
    		}
				break;
    	case "exit":
    		break;
    }
};

$ws_worker->onClose = function($client_id)
{
};


// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}
