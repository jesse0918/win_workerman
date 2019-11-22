<?php
namespace Service\Common\Java;

class java_SimpleFactory {
		public $client;
		function __construct($client) {
				$this->client=$client;
		}
		function getProxy($result,$signature,$exception,$wrap) {
			return $result;
		}
		function checkResult($result) {
		}
}
?>