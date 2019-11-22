<?php
namespace Service\Common\Java;

class java_Exception implements java_JavaType {
		public $__serialID,$__java,$__client;
		public $__delegate;
		public $__signature;
		public $__hasDeclaredExceptions;
		
		function __construct() {
				$this->__client=__javaproxy_Client_getClient();
				$args=func_get_args();
				$name=array_shift($args);
				if(is_array($name)) { $args=$name; $name=array_shift($args); }
				//if (count($args)==0)
				//		Exception::__construct($name);
				//else
				//		Exception::__construct($args[0]);
				$delegate=$this->__delegate=$this->__client->createObject($name,$args);
				$this->__java=$delegate->__java;
				$this->__signature=$delegate->__signature;
				$this->__hasDeclaredExceptions='T';
		}
		
		function __cast($type) {
				return $this->__delegate->__cast($type);
		}

		function __sleep() {
				$this->__delegate->__sleep();
				return array("__delegate");
		}

		function __wakeup() {
				$this->__delegate->__wakeup();
				$this->__java=$this->__delegate->__java;
				$this->__client=$this->__delegate->__client;
		}

		function __get($key) {
				return $this->__delegate->__get($key);
		}

		function __set($key,$val) {
				$this->__delegate->__set($key,$val);
		}

		function __call($method,$args) {
				return $this->__delegate->__call($method,$args);
		}

		function __toString() {
				return $this->__delegate->__toExceptionString($this->getTraceAsString());
		}
}
?>