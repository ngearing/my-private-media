<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0242745c0095d0cb7f6f06cd1570fd36
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MPM\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MPM\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib',
        ),
    );

    public static $classMap = array (
        'MPM\\Activator' => __DIR__ . '/../..' . '/lib/activator.php',
        'MPM\\Common\\Loader' => __DIR__ . '/../..' . '/lib/common/loader.php',
        'MPM\\Common\\htaccess' => __DIR__ . '/../..' . '/lib/common/htaccess.php',
        'MPM\\Controllers\\Admin' => __DIR__ . '/../..' . '/lib/controllers/admin.php',
        'MPM\\Deactivator' => __DIR__ . '/../..' . '/lib/deactivator.php',
        'MPM\\Plugin' => __DIR__ . '/../..' . '/lib/plugin.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0242745c0095d0cb7f6f06cd1570fd36::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0242745c0095d0cb7f6f06cd1570fd36::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit0242745c0095d0cb7f6f06cd1570fd36::$classMap;

        }, null, ClassLoader::class);
    }
}
