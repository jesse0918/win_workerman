<?php
namespace Service\Common\Java;

class java_InternalJava extends Java {
		function __construct($proxy) {
				$this->__delegate=$proxy;
				$this->__java=$proxy->__java;
				$this->__signature=$proxy->__signature;
				$this->__client=$proxy->__client;
		}
}
?>