<?php
namespace Service\Common\Java;

class java_ExceptionProxy extends java_JavaProxy {
		function __toExceptionString($trace) {
				$args=array($this,$trace);
				return $this->__client->invokeMethod(0,"ObjectToString",$args);
		}
}
?>