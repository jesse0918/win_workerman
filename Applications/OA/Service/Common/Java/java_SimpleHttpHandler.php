<?php
namespace Service\Common\Java;

class java_SimpleHttpHandler extends java_SocketHandler {
		public $headers,$cookies;
		public $context,$ssl,$port;
		public $host;

		function createChannel() {
				$channelName=java_getHeader("X_JAVABRIDGE_REDIRECT",$_SERVER);
				$context=java_getHeader("X_JAVABRIDGE_CONTEXT",$_SERVER);
				$len=strlen($context);
				$len0=java_getCompatibilityOption($this->protocol->client);
				$len1=chr($len&0xFF); $len>>=8;
				$len2=chr($len&0xFF);
				$this->channel=new java_EmptyChannel($this);
				$this->channel=$this->getChannel($channelName);
				$this->protocol->socketHandler=new java_SocketHandler($this->protocol,$this->channel);
				$this->protocol->write("\177${len0}${len1}${len2}${context}");
				$this->context=sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n",$context);
				$this->protocol->handler=$this->protocol->socketHandler;
				$this->protocol->handler->write($this->protocol->client->sendBuffer)
				or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
				$this->protocol->client->sendBuffer=null;
				$this->protocol->handler->read(1)
				or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
		}
	
		function __construct($protocol,$ssl,$host,$port) {
				$this->cookies=array();
				$this->protocol=$protocol;
				$this->ssl=$ssl;
				$this->host=$host;
				$this->port=$port;
				$this->createChannel();
		}
	
		function getCookies() {
				$str="";
				$first=true;
				foreach($_COOKIE as $k=> $v) {
						$str .=($first ? "Cookie: $k=$v":"; $k=$v");
						$first=false;
				}
				if(!$first) $str .="\r\n";
				return $str;
		}

		function getContextFromCgiEnvironment() {
				$ctx=java_getHeader('X_JAVABRIDGE_CONTEXT',$_SERVER);
				return $ctx;
		}

		function getContext() {
				static $context=null;
				if($context) return $context;
				$ctx=$this->getContextFromCgiEnvironment();
				$context="";
				if($ctx) {
						$context=sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n",$ctx);
				}
				return $context;
		}

		function getWebAppInternal() {
				$context=$this->protocol->webContext;
				if(isset($context)) return $context;
				return (JAVA_SERVLET=="User" &&
				array_key_exists('PHP_SELF',$_SERVER) &&
				array_key_exists('HTTP_HOST',$_SERVER))
				? $_SERVER['PHP_SELF']."javabridge"
				: null;
		}

		function getWebApp() {
				$context=$this->getWebAppInternal();
				if(is_null($context)) $context=JAVA_SERVLET;
				if(is_null($context) || $context[0]!="/")
						$context="/JavaBridge/JavaBridge.phpjavabridge";
				return $context;
		}

		function write($data) {
				return $this->protocol->socketHandler->write($data);
		}

		function doSetCookie($key,$val,$path) {
				$path=trim($path);
				$webapp=$this->getWebAppInternal(); if(!$webapp) $path="/";
				setcookie($key,$val,0,$path);
		}

		function read($size) {
				return $this->protocol->socketHandler->read($size);
		}

		function getChannel($channelName) {
				$errstr=null; $errno=null;
				$peer=pfsockopen($this->host,$channelName,$errno,$errstr,20);
				if (!$peer) throw new java_IllegalStateException("No ContextServer for {$this->host}:{$channelName}. Error: $errstr ($errno)\n");
						stream_set_timeout($peer,-1);
				return new java_SocketChannelP($peer,$this->host);
		}

		function keepAlive() {
				parent::keepAlive();
		}

		function redirect() {}
}
?>