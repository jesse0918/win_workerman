<?php
namespace Service\Common\Java;

class java_ThrowExceptionProxyFactory extends java_ExceptionProxyFactory {
		function getProxy($result,$signature,$exception,$wrap) {
				$proxy=$this->create($result,$signature);
				$proxy=new java_InternalException($proxy,$exception);
				return $proxy;
		}
		function checkResult($result) {
				if (JAVA_PREFER_VALUES || ($result->__hasDeclaredExceptions=='T'))
						throw $result;
				else {
					trigger_error("Unchecked exception detected: ".java_truncate($result->__toString()),E_USER_WARNING);
				}
		}
}
?>