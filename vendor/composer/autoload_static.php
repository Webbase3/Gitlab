<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit411c5b482fae7f2729719a247c6f4270
{
    public static $prefixesPsr0 = array (
        'G' => 
        array (
            'Gitlab\\' => 
            array (
                0 => __DIR__ . '/../..' . '/Zoho_git/Gitlab',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit411c5b482fae7f2729719a247c6f4270::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
