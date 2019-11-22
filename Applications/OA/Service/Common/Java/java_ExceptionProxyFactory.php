<?php
namespace Service\Common\Java;

class java_ExceptionProxyFactory extends java_SimpleFactory {
		function create($result,$signature) {
				return new java_ExceptionProxy($result,$signature);
		}

		function getProxy($result,$signature,$exception,$wrap) {
				$proxy=$this->create($result,$signature);
				if($wrap) $proxy=new java_InternalException($proxy,$exception);
				return $proxy;
		}
}
?>