<?php
namespace Service\Common\Java;

class java_InternalException extends java_Exception {
		function __construct($proxy,$exception) {
				$this->__delegate              = $proxy;
				$this->__java                  = $proxy->__java;
				$this->__signature             = $proxy->__signature;
				$this->__client                = $proxy->__client;
				$this->__hasDeclaredExceptions = $exception;
		}
}
?>