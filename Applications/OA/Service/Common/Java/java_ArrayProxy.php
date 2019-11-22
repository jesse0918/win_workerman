<?php
namespace Service\Common\Java;

class java_ArrayProxy extends java_IteratorProxy implements ArrayAccess {
		function offsetExists($idx) {
				$ar=array($this,$idx);
				return $this->__client->invokeMethod(0,"offsetExists",$ar);
		}

		function offsetGet($idx) {
				$ar=array($this,$idx);
				return $this->__client->invokeMethod(0,"offsetGet",$ar);
		}

		function offsetSet($idx,$val) {
				$ar=array($this,$idx,$val);
				return $this->__client->invokeMethod(0,"offsetSet",$ar);
		}

		function offsetUnset($idx) {
				$ar=array($this,$idx);
				return $this->__client->invokeMethod(0,"offsetUnset",$ar);
		}
}
?>