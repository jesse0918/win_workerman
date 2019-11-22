<?php
namespace Service\Common\Java;

class java_EmptyChannel {
		protected $handler;
		private $res;
		
		function __construct($handler) {
			$this->handler=$handler;
		}

		function shutdownBrokenConnection () {}

		function fwrite($data) {
				return $this->handler->fwrite($data);
		}

		function fread($size) {
				return $this->handler->fread($size);
		}

		function getKeepAliveA() {
				return "<F p=\"A\" />";
		}

		function getKeepAliveE() {
				return "<F p=\"E\" />";
		}

		function getKeepAlive() {
				return $this->getKeepAliveE();
		}

		function error() {
				trigger_error("An unchecked exception occured during script execution. Please check the server log files for details.",E_USER_ERROR);
		}

		function checkA($peer) {
				$val=$this->res[6];
				if ($val !='A') fclose($peer);
				if ($val !='A' && $val !='E') {
						$this->error();
				}
		}

		function checkE() {
				$val=$this->res[6];
				if ($val !='E') {
						$this->error();
				}
		}

		function keepAliveS() {
				$this->res=$this->fread(10);
		}

		function keepAliveSC() {
				$this->res=$this->fread(10);
				$this->fwrite("");
				$this->fread(JAVA_RECV_SIZE);
		}

		function keepAliveH() {
				$this->res=$this->handler->read(10);
		}

		function keepAlive() {
				$this->keepAliveH();
				$this->checkE();
		}
}
?>