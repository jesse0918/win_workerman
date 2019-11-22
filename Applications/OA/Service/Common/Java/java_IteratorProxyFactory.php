<?php
namespace Service\Common\Java;

class java_IteratorProxyFactory extends java_ProxyFactory {
		function create($result,$signature) {
				return new java_IteratorProxy($result,$signature);
		}
}
?>