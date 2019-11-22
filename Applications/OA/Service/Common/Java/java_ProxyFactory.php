<?php
namespace Service\Common\Java;

class java_ProxyFactory extends java_SimpleFactory {
		function create($result,$signature) {
				return new java_JavaProxy($result,$signature);
		}

		function createInternal($proxy) {
				return new java_InternalJava($proxy);
		}

		function getProxy($result,$signature,$exception,$wrap) {
				$proxy=$this->create($result,$signature);
				if($wrap) $proxy=$this->createInternal($proxy);
				return $proxy;
		}
}
?>