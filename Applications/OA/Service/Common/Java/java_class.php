<?php
namespace Service\Common\Java;

class java_class extends java_R {
		function __construct() {
				$this->__client=__javaproxy_Client_getClient();
				$args=func_get_args();
				$name=array_shift($args);
				if(is_array($name)) { $args=$name; $name=array_shift($args); }
				$delegate=$this->__delegate=$this->__client->referenceObject($name,$args);
				$this->__java=$delegate->__java;
				$this->__signature=$delegate->__signature;
		}
}
?>