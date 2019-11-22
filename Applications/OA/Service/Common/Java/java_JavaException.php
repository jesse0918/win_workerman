<?php
namespace Service\Common\Java;

class java_JavaException extends Exception {
		function __toString() {
				return $this->getMessage();
		}
}
?>