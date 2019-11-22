<?php
namespace Service\Common\Java;

class java_CacheEntry {
		public $fmt,$signature,$factory,$java;
		public $resultVoid;
		function __construct($fmt,$signature,$factory,$resultVoid) {
				$this->fmt=$fmt;
				$this->signature=$signature;
				$this->factory=$factory;
				$this->resultVoid=$resultVoid;
		}
}
?>