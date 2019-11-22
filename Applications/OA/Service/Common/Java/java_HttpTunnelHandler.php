<?php
namespace Service\Common\Java;

class java_HttpTunnelHandler extends java_SimpleHttpTunnelHandler {
		function fread($size) {
				if ($this->hasContentLength)
						return fread($this->socket,$this->headers["content_length"]);
				else
						return parent::fread($size);
		}

		function fwrite($data) {
				if ($this->hasContentLength)
						return fwrite($this->socket,$data);
				else
						return parent::fwrite($data);
		}

		function close() {
				if ($this->hasContentLength) {
						fwrite($this->socket,"0\r\n\r\n");
						fclose($this->socket);
				} else {
						parent::fclose($this->socket);
				}
		}
}
?>