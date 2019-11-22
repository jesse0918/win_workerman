<?php
namespace Service\Common\Java;

class java_ArrayProxyFactory extends java_ProxyFactory {
		function create($result,$signature) {
				return new java_ArrayProxy($result,$signature);
		}
}
?>