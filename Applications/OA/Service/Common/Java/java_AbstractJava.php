<?php
namespace Service\Common\Java;

abstract class java_AbstractJava implements java_JavaType {
		public $__client;
		public $__delegate;
		public $__serialID;
		public $__factory;
		public $__java,$__signature;
		public $__cancelProxyCreationTag;

		function __createDelegate() {
				$proxy=$this->__delegate=
				$this->__factory->create($this->__java,$this->__signature);
				$this->__java=$proxy->__java;
				$this->__signature=$proxy->__signature;
		}

		function __cast($type) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				return $this->__delegate->__cast($type);
		}

		function __sleep() {
				if(!isset($this->__delegate)) $this->__createDelegate();
				$this->__delegate->__sleep();
				return array("__delegate");
		}

		function __wakeup() {
				if(!isset($this->__delegate)) $this->__createDelegate();
				$this->__delegate->__wakeup();
				$this->__java=$this->__delegate->__java;
				$this->__client=$this->__delegate->__client;
		}

		function __get($key) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				return $this->__delegate->__get($key);
		}

		function __set($key,$val) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				$this->__delegate->__set($key,$val);
		}

		function __call($method,$args) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				return $this->__delegate->__call($method,$args);
		}

		function __toString() {
				if(!isset($this->__delegate)) $this->__createDelegate();
				return $this->__delegate->__toString();
		}

		function getIterator() {
				if(!isset($this->__delegate)) $this->__createDelegate();
				if(func_num_args()==0) return $this->__delegate->getIterator();
				$args=func_get_args();
				return $this->__call("getIterator",$args);
		}
		
		function offsetExists($idx) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				if(func_num_args()==1) return $this->__delegate->offsetExists($idx);
				$args=func_get_args(); return $this->__call("offsetExists",$args);
		}

		function offsetGet($idx) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				if(func_num_args()==1) return $this->__delegate->offsetGet($idx);
				$args=func_get_args(); return $this->__call("offsetGet",$args);
		}

		function offsetSet($idx,$val) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				if(func_num_args()==2) return $this->__delegate->offsetSet($idx,$val);
				$args=func_get_args(); return $this->__call("offsetSet",$args);
		}

		function offsetUnset($idx) {
				if(!isset($this->__delegate)) $this->__createDelegate();
				if(func_num_args()==1) return $this->__delegate->offsetUnset($idx);
				$args=func_get_args(); return $this->__call("offsetUnset",$args);
		}
}
?>