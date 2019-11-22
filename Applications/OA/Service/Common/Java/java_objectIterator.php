<?php
namespace Service\Common\Java;

class java_objectIterator implements Iterator {
		private $var;
		function __construct($javaProxy) {
				$this->var=java_cast ($javaProxy,"A");
		}

		function rewind() {
				reset($this->var);
		}

		function valid() {
				return $this->current() !==false;
		}

		function next() {
				return next($this->var);
		}

		function key() {
				return key($this->var);
		}

		function current() {
				return current($this->var);
		}
}
?>