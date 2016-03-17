<?php
/**
 * filename: test.php
 * Created by pjianwei.
 * Date: 2016/3/11 13:58
 * description:
 */
use jean\swooleCron\Main;

!defined('DS') and define('DS', DIRECTORY_SEPARATOR);
define('SRC', dirname(__DIR__) . DS . 'src' . DS);

spl_autoload_register(function ($class) {
    if (file_exists(SRC . $class . '.php')) {
        require SRC . $class . '.php';
    }
});

Main::run();