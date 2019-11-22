<?php
namespace Service\Common\Java;

class java_SocketChannelP extends java_SocketChannel {
		function getKeepAlive() {
				return $this->getKeepAliveA();
		}

		function keepAlive() {
				$this->keepAliveS();
				$this->checkA($this->peer);
		}
}
?>