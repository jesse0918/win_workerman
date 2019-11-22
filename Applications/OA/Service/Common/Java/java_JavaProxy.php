<?php
namespace Service\Common\Java;

class java_JavaProxy implements java_JavaType {
		public $__serialID,$__java;
		public $__signature;
		public $__client;
		public $__tempGlobalRef;

		function __construct($java,$signature){
				$this->__java=$java;
				$this->__signature=$signature;
				$this->__client=__javaproxy_Client_getClient();
		}

		function __cast($type) {
				return $this->__client->cast($this,$type);
		}

		function __sleep() {
				$args=array($this,java_get_lifetime());
				$this->__serialID=$this->__client->invokeMethod(0,"serialize",$args);
				$this->__tempGlobalRef=$this->__client->globalRef;
				return array("__serialID","__tempGlobalRef");
		}

		function __wakeup() {
				$args=array($this->__serialID,java_get_lifetime());
				$this->__client=__javaproxy_Client_getClient();
				if($this->__tempGlobalRef)
						$this->__client->globalRef=$this->__tempGlobalRef;
				$this->__tempGlobalRef=null;
				$this->__java=$this->__client->invokeMethod(0,"deserialize",$args);
		}

		function __destruct() {
				if(isset($this->__client))
						$this->__client->unref($this->__java);
		}

		function __get($key) {
				return $this->__client->getProperty($this->__java,$key);
		}

		function __set($key,$val) {
				$this->__client->setProperty($this->__java,$key,$val);
		}

		function __call($method,$args) {
				return $this->__client->invokeMethod($this->__java,$method,$args);
		}

		function __toString() {
				try {
						return $this->__client->invokeMethod(0,"ObjectToString",array($this));
				} catch (JavaException $ex) {
						trigger_error("Exception in Java::__toString(): ". java_truncate((string)$ex),E_USER_WARNING);
						return "";
				}
		}
}
?>