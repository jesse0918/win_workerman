<?php
namespace Service\Common\Java;

abstract class java_SocketChannel extends java_EmptyChannel {

		public $peer,$host;
		
		function __construct($peer,$host) {
				$this->peer=$peer;
				$this->host=$host;
		}

		function fwrite($data) {
				return fwrite($this->peer,$data);
		}

		function fread($size) {
				return fread($this->peer,$size);
		}

		function shutdownBrokenConnection () {
				fclose($this->peer);
		}
}
?>