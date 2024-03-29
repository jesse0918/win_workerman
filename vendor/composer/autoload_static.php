<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0ae1b1369f6f46a3e7325262ae8d8b15
{
    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
        		'Workerman\\PhpMail\\' => 18,
        		'Workerman\\MySQL\\' => 16,
				'Workerman\\' => 10,
        ),
        'G' => 
        array (
            'GatewayWorker\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
    		'Workerman\\PhpMail\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman-for-win/phpmailer',
        ),
    		'Workerman\\MySQL\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman-for-win/mysql/src',
        ),
        'Workerman\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/workerman-for-win',
        ),
        'GatewayWorker\\' => 
        array (
            0 => __DIR__ . '/..' . '/workerman/gateway-worker-for-win/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0ae1b1369f6f46a3e7325262ae8d8b15::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0ae1b1369f6f46a3e7325262ae8d8b15::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
