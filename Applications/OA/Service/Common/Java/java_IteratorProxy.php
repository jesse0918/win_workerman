<?php
namespace Service\Common\Java;

class java_IteratorProxy extends java_JavaProxy implements IteratorAggregate {
		function getIterator() {
				return new java_ObjectIterator($this);
		}
}
?>