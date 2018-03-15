<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit57098402a87753353b9b42e595525580
{
    public static $files = array (
        'e40631d46120a9c38ea139981f8dab26' => __DIR__ . '/..' . '/ircmaxell/password-compat/lib/password.php',
        '5255c38a0faeba867671b61dfda6d864' => __DIR__ . '/..' . '/paragonie/random_compat/lib/random.php',
        '848a06382c1c883893a9c9833ae2b551' => __DIR__ . '/..' . '/symfony/polyfill/src/Apcu/bootstrap.php',
        'd075ca29fdd460a76e9d730a9724dc20' => __DIR__ . '/..' . '/symfony/polyfill/src/Php54/bootstrap.php',
        '7d1c739f734e1193d0c090179eeb95aa' => __DIR__ . '/..' . '/symfony/polyfill/src/Php55/bootstrap.php',
        '974c792dde7e26133ce76c5ff3d097b1' => __DIR__ . '/..' . '/symfony/polyfill/src/Php56/bootstrap.php',
        '8ac57d99d5d58e71376ea5f919e28d23' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/bootstrap.php',
        '0782f09865a7e9f4ebd12e5f68b3135f' => __DIR__ . '/..' . '/symfony/polyfill/src/Iconv/bootstrap.php',
        'f38c346c3a1bb49bd02ba8e9177d7e56' => __DIR__ . '/..' . '/symfony/polyfill/src/Intl/Grapheme/bootstrap.php',
        '946db64f9a5c0688514fdcdafcabe7d2' => __DIR__ . '/..' . '/symfony/polyfill/src/Intl/Icu/bootstrap.php',
        '299b3c040b39cb03c6eceb9bb272ad1d' => __DIR__ . '/..' . '/symfony/polyfill/src/Intl/Normalizer/bootstrap.php',
        'e59f725579f9974327c76777296d6dcc' => __DIR__ . '/..' . '/symfony/polyfill/src/Mbstring/bootstrap.php',
        '17dde14e168d8aa5de531eefe5689d6b' => __DIR__ . '/..' . '/symfony/polyfill/src/Xml/bootstrap.php',
        'f54c9b5e988cab550b89236716b03511' => __DIR__ . '/..' . '/graylog2/gelf-php/src/check_technical_requirements.php',
        'b46ad4fe52f4d1899a2951c7e6ea56b0' => __DIR__ . '/..' . '/voku/portable-utf8/bootstrap.php',
        '01872de466184325f7c54c2eed2fbb45' => __DIR__ . '/..' . '/tmtbe/swooledistributed/src/Server/helpers/Common.php',
        '85a3d10c16585b22f2dd265f7c845f3f' => __DIR__ . '/../..' . '/src/app/function.php',
    );

    public static $prefixLengthsPsr4 = array (
        'v' => 
        array (
            'voku\\helper\\' => 12,
            'voku\\' => 5,
        ),
        't' => 
        array (
            'test\\' => 5,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\' => 17,
            'Symfony\\Component\\Intl\\' => 23,
            'Server\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PhpAmqpLib\\' => 11,
        ),
        'N' => 
        array (
            'Noodlehaus\\' => 11,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'L' => 
        array (
            'League\\Plates\\' => 14,
        ),
        'G' => 
        array (
            'Gelf\\' => 5,
        ),
        'D' => 
        array (
            'Ds\\' => 3,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'voku\\helper\\' => 
        array (
            0 => __DIR__ . '/..' . '/voku/anti-xss/src/voku/helper',
        ),
        'voku\\' => 
        array (
            0 => __DIR__ . '/..' . '/voku/portable-utf8/src/voku',
        ),
        'test\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/test',
            1 => __DIR__ . '/..' . '/tmtbe/swooledistributed/src/test',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/app',
            1 => __DIR__ . '/..' . '/tmtbe/swooledistributed/src/app',
        ),
        'Symfony\\Polyfill\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill/src',
        ),
        'Symfony\\Component\\Intl\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/intl',
        ),
        'Server\\' => 
        array (
            0 => __DIR__ . '/..' . '/tmtbe/swooledistributed/src/Server',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'PhpAmqpLib\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-amqplib/php-amqplib/PhpAmqpLib',
        ),
        'Noodlehaus\\' => 
        array (
            0 => __DIR__ . '/..' . '/hassankhan/config/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'League\\Plates\\' => 
        array (
            0 => __DIR__ . '/..' . '/league/plates/src',
        ),
        'Gelf\\' => 
        array (
            0 => __DIR__ . '/..' . '/graylog2/gelf-php/src/Gelf',
        ),
        'Ds\\' => 
        array (
            0 => __DIR__ . '/..' . '/php-ds/php-ds/src',
        ),
    );

    public static $classMap = array (
        'ArithmeticError' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/ArithmeticError.php',
        'AssertionError' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/AssertionError.php',
        'CallbackFilterIterator' => __DIR__ . '/..' . '/symfony/polyfill/src/Php54/Resources/stubs/CallbackFilterIterator.php',
        'Collator' => __DIR__ . '/..' . '/symfony/intl/Resources/stubs/Collator.php',
        'DivisionByZeroError' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/DivisionByZeroError.php',
        'Error' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/Error.php',
        'IntlDateFormatter' => __DIR__ . '/..' . '/symfony/intl/Resources/stubs/IntlDateFormatter.php',
        'Locale' => __DIR__ . '/..' . '/symfony/intl/Resources/stubs/Locale.php',
        'Normalizer' => __DIR__ . '/..' . '/symfony/polyfill/src/Intl/Normalizer/Resources/stubs/Normalizer.php',
        'NumberFormatter' => __DIR__ . '/..' . '/symfony/intl/Resources/stubs/NumberFormatter.php',
        'ParseError' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/ParseError.php',
        'RecursiveCallbackFilterIterator' => __DIR__ . '/..' . '/symfony/polyfill/src/Php54/Resources/stubs/RecursiveCallbackFilterIterator.php',
        'SessionHandlerInterface' => __DIR__ . '/..' . '/symfony/polyfill/src/Php54/Resources/stubs/SessionHandlerInterface.php',
        'TypeError' => __DIR__ . '/..' . '/symfony/polyfill/src/Php70/Resources/stubs/TypeError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit57098402a87753353b9b42e595525580::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit57098402a87753353b9b42e595525580::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit57098402a87753353b9b42e595525580::$classMap;

        }, null, ClassLoader::class);
    }
}
