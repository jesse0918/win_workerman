<?php
namespace Service\Common\Java;

class java_ChunkedSocketChannel extends java_SocketChannel {
		function fwrite($data) {
				$len=dechex(strlen($data));
				return fwrite($this->peer,"${len}\r\n${data}\r\n");
		}

		function fread($size) {
				$length=hexdec(fgets($this->peer,JAVA_RECV_SIZE));
				$data="";
				while ($length > 0) {
						$str=fread($this->peer,$length);
						if (feof ($this->peer)) return null;
						$length -=strlen($str);
						$data .=$str;
				}
				fgets($this->peer,3);
				return $data;
		}

		function keepAlive() { 
				$this->keepAliveSC();
				$this->checkE();
				fclose ($this->peer);
		}
}
?>