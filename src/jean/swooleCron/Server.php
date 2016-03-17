<?php
/**
 * filename: Server.php
 * Created by pjianwei.
 * Date: 2016/3/14 9:29
 * description:
 */

namespace jean\swooleCron;


use jean\lib\BaseObject;
use jean\lib\Environment;
use jean\lib\Exception;

class Server
{
    static $app = null;
    static $defaultParams = [

    ];

    static function __init(array $config)
    {
        is_null(self::$app) and self::$app = new BaseObject($config);
        BaseObject::__init(self::$app, $config);
    }

    static function run(array $config = [])
    {
        !empty($config) and self::__init($config);
        self::checkEnvironment();
    }

    /**
     * cli环境检查
     * @return bool
     * @throws Exception
     */
    static private function checkEnvironment()
    {
        $mod = Environment::getName();
        if ($mod == 'cli') {
            return true;
        }
        throw  new Exception("Server 只能运行在CLI环境下~!", '1');
    }

    static private function checkConfig()
    {
        $rs = true;
        if (!is_null(self::$app)) {
            extract(get_object_vars(self::$app));
            !isset($ip) and $rs = false;
            !isset($port) and $rs = false;
        }
        if (!$rs) {
            throw new Exception('Server 参数配置错误', '2');
        }
        return $rs;
    }
}