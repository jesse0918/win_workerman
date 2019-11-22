<?php
namespace Service\Common\Java;

class java_SocketHandler {
		public $protocol,$channel;
		
		function __construct($protocol,$channel) {
				$this->protocol=$protocol;
				$this->channel=$channel;
		}

		function write($data) {
				return $this->channel->fwrite($data);
		}

		function fwrite($data) {return $this->write($data);}

		function read($size) {
				return $this->channel->fread($size);
		}

		function fread($size) {return $this->read($size);}

		function redirect() {}

		function getKeepAlive() {
				return $this->channel->getKeepAlive();
		}

		function keepAlive() {
				$this->channel->keepAlive();
		}

		function dieWithBrokenConnection($msg) {
				unset($this->protocol->client->protocol);
				trigger_error ($msg?$msg:"unknown error: please see back end log for details",E_USER_ERROR);
		}

		function shutdownBrokenConnection ($msg) {
				$this->channel->shutdownBrokenConnection();
				$this->dieWithBrokenConnection($msg);
		}
}
?>