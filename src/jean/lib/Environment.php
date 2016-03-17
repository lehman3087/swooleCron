<?php
/**
 * filename: Environment.php
 * Created by pjianwei.
 * Date: 2016/3/14 9:56
 * description:
 */

namespace jean\lib;


class Environment
{

    static function getName(){
        return php_sapi_name();
    }
}