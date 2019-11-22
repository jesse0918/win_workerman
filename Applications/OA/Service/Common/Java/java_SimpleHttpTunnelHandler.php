<?php
namespace Service\Common\Java;

class java_SimpleHttpTunnelHandler extends java_SimpleHttpHandler {
		public $socket;
		protected $hasContentLength=false;

		function createSimpleChannel () {
				$this->channel=new java_EmptyChannel($this);
		}

		function createChannel() {
				$this->createSimpleChannel();
		}

		function shutdownBrokenConnection ($msg) {
				fclose($this->socket);
				$this->dieWithBrokenConnection($msg);
		}

		function checkSocket($socket,&$errno,&$errstr) {
				if (!$socket) {
						$msg="Could not connect to the JEE server {$this->ssl}{$this->host}:{$this->port}. Please start it.";
						$msg.=java_checkCliSapi()
						?" Or define('JAVA_HOSTS',9267); define('JAVA_SERVLET',false); before including 'Java.inc' and try again. Error message: $errstr ($errno)\n"
						:" Error message: $errstr ($errno)\n";
						throw new java_ConnectException($msg);
				}
		}

		function open() {
				$errno=null; $errstr=null;
				$socket=fsockopen("{$this->ssl}{$this->host}",$this->port,$errno,$errstr,20);
				$this->checkSocket($socket,$errno,$errstr);
				stream_set_timeout($socket,-1);
				$this->socket=$socket;
		}

		function fread($size) {
				$length=hexdec(fgets($this->socket,JAVA_RECV_SIZE));
				$data="";
				while ($length > 0) {
						$str=fread($this->socket,$length);
						if (feof ($this->socket)) return null;
						$length -=strlen($str);
						$data .=$str;
				}
				fgets($this->socket,3);
				return $data;
		}

		function fwrite($data) {
				$len=dechex(strlen($data));
				return fwrite($this->socket,"${len}\r\n${data}\r\n");
		}

		function close() {
				fwrite($this->socket,"0\r\n\r\n");
				fgets($this->socket,JAVA_RECV_SIZE);
				fgets($this->socket,3);
				fclose($this->socket);
		}

		function __construct($protocol,$ssl,$host,$port) {
				parent::__construct($protocol,$ssl,$host,$port);
				$this->open();
		}

		function read($size) {
				if(is_null($this->headers)) $this->parseHeaders();
				if (isset($this->headers["http_error"])) {
						if (isset($this->headers["transfer_chunked"])) {
								$str=$this->fread(JAVA_RECV_SIZE);
						} elseif (isset($this->headers['content_length'])) {
								$len=$this->headers['content_length'];
								for($str=fread($this->socket,$len); strlen($str)<$len; $str.=fread($this->socket,$len-strlen($str)))
								if (feof ($this->socket)) break;
						} else {
								$str=fread($this->socket,JAVA_RECV_SIZE);
						}
						$this->shutdownBrokenConnection($str);
				}
				return $this->fread(JAVA_RECV_SIZE);
		}

		function getBodyFor ($compat,$data) {
				$len=dechex(2+strlen($data));
				return "Cache-Control: no-cache\r\nPragma: no-cache\r\nTransfer-Encoding: chunked\r\n\r\n${len}\r\n\177${compat}${data}\r\n";
		}

		function write($data) {
				$compat=java_getCompatibilityOption($this->protocol->client);
				$this->headers=null;
				$socket=$this->socket;
				$webapp=$this->getWebApp();
				$cookies=$this->getCookies();
				$context=$this->getContext();
				$res="PUT ";
				$res .=$webapp;
				$res .=" HTTP/1.1\r\n";
				$res .="Host: {$this->host}:{$this->port}\r\n";
				$res .=$context;
				$res .=$cookies;
				$res .=$this->getBodyFor($compat,$data);
				$count=fwrite($socket,$res) or $this->shutdownBrokenConnection("Broken connection handle");
				fflush($socket) or $this->shutdownBrokenConnection("Broken connection handle");
				return $count;
		}

		function parseHeaders() {
				$this->headers=array();
				$line=trim(fgets($this->socket,JAVA_RECV_SIZE));
				$ar=explode (" ",$line);
				$code=((int)$ar[1]);
				if ($code !=200) $this->headers["http_error"]=$code;
				while (($str=trim(fgets($this->socket,JAVA_RECV_SIZE)))) {
						if($str[0]=='X') {
								if(!strncasecmp("X_JAVABRIDGE_REDIRECT",$str,21)) {
										$this->headers["redirect"]=trim(substr($str,22));
								} else if(!strncasecmp("X_JAVABRIDGE_CONTEXT",$str,20)) {
										$this->headers["context"]=trim(substr($str,21));
								}
						} else if($str[0]=='S') {
								if(!strncasecmp("SET-COOKIE",$str,10)) {
										$str=substr($str,12);
										$this->cookies[]=$str;
										$ar=explode(";",$str);
										$cookie=explode("=",$ar[0]);
										$path="";
										if(isset($ar[1])) $p=explode("=",$ar[1]);
										if(isset($p)) $path=$p[1];
										$this->doSetCookie($cookie[0],$cookie[1],$path);
								}
						} else if($str[0]=='C') {
								if(!strncasecmp("CONTENT-LENGTH",$str,14)) {
										$this->headers["content_length"]=trim(substr($str,15));
										$this->hasContentLength=true;
								} else if(!strncasecmp("CONNECTION",$str,10) && !strncasecmp("close",trim(substr($str,11)),5)) {
										$this->headers["connection_close"]=true;
								}
						} else if($str[0]=='T') {
								if(!strncasecmp("TRANSFER-ENCODING",$str,17) && !strncasecmp("chunked",trim(substr($str,18)),7)) {
									$this->headers["transfer_chunked"]=true;
								}
						}
				}
		}

		function getSimpleChannel() {
				return new java_ChunkedSocketChannel($this->socket,$this->protocol,$this->host);
		}

		function redirect() {
				$this->isRedirect=isset($this->headers["redirect"]);
				if ($this->isRedirect)
						$channelName=$this->headers["redirect"];
						$context=$this->headers["context"];
						$len=strlen($context);
						$len0=chr(0xFF);
						$len1=chr($len&0xFF); $len>>=8;
						$len2=chr($len&0xFF);
						if ($this->isRedirect) {
								$this->protocol->socketHandler=new java_SocketHandler($this->protocol,$this->getChannel($channelName));
								$this->protocol->write("\177${len0}${len1}${len2}${context}");
								$this->context=sprintf("X_JAVABRIDGE_CONTEXT: %s\r\n",$context);
								$this->close ();
								$this->protocol->handler=$this->protocol->socketHandler;
								$this->protocol->handler->write($this->protocol->client->sendBuffer)
								or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
								$this->protocol->client->sendBuffer=null;
								$this->protocol->handler->read(1)
								or $this->protocol->handler->shutdownBrokenConnection("Broken local connection handle");
						} else {
								$this->protocol->handler=$this->protocol->socketHandler=new java_SocketHandler($this->protocol,$this->getSimpleChannel());
						}
		}
}
?>