<?php
namespace Service\Common\Java;

class java_IllegalArgumentException extends java_RuntimeException {
		function __construct($ob) {
				parent::__construct("illegal argument: ".gettype($ob));
		}
}
?>