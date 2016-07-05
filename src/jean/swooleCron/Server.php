<?php
/**
 * filename: Server.php
 * Created by pjianwei.
 * Date: 2016/3/14 9:29
 * description:
 */

namespace jean\swoolecron;

!defined('DS') and define('DS', DIRECTORY_SEPARATOR);
define('SRC', dirname(dirname(__DIR__)) . DS);

use jean\lib\BaseObject;
use jean\lib\Environment;
use jean\lib\Exception;
use jean\lib\Receive;
use jean\lib\Redis;
use jean\lib\Task;
use jean\lib\TaskProcess;
use jean\lib\TaskServer;
use jean\lib\WorkProcess;
use jean\swoolecron\Client;

class Server
{
    static public $app = null;
    static $configFile = SRC . 'jean/config/config.php';

    static function __init(array $config)
    {
        
        if (is_null(self::$app)) {
            self::$app = new BaseObject($config);
            $arr = include SRC . 'jean/config/config.php';
            BaseObject::__init(self::$app, $arr);
        }
        !empty($config) and BaseObject::__init(self::$app, $config);
    }

    static function run(array $config = [])
    {
        self::__init($config);
        self::checkEnvironment();
        self::start();
        return 1;
    }

    static private function start()
    {
        $_server = new \swoole_server(self::$app['server_ip'], self::$app['server_port'], SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $_config = get_object_vars(Server::$app);
        $_server->set($_config);
        $_server->on('start', function ($server) use ($_config) {

        });
        $_server->on('Receive', function ($server, $fd, $from_id, $data) {
            $receiveServer = new Receive($server);
            $rs = $receiveServer->run($data);
            unset($receiveServer);
            $server->send($fd, $rs);
            $server->close($fd);
        });
        $_server->on('WorkerStart', function ($server, $worker_id) use ($_config) {
            if (!$server->taskworker && $worker_id < $_config['worker_num']) {
                if ($worker_id == '0') {
                    $taskServer = new TaskServer($server);
                    $taskServer->loadTask();
                    set_error_handler(function () use ($server, $taskServer) {
                        $server->tick(WorkProcess::getInterVal($server), function ($id) use ($taskServer, $server) {
                            $taskServer->loadTask();
                        });
                    });
                    $server->tick(WorkProcess::getInterVal($server), function ($id) use ($taskServer, $server) {
                        $taskServer->loadTask();
                    });
                }
                (new WorkProcess($server))->run();
            }
        });
        $_server->on('task', function ($server, $task_id, $from_id, $data) {
            if ($data instanceof Task) {
                try {
                    $taskProcess = new TaskProcess($server);
                    $taskProcess->run($data);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                $WorkerProcess = new WorkProcess($server);
                $data->runStatus = 2;
                $WorkerProcess->saveTaskRunStatus($data);
                $WorkerProcess->checkTaskRunStatus($data);
            }
        });
        $_server->on('finish', function ($server, $task_id, $from_id, $data) {
        });
        $cache = new Redis();
        $cache->del($_config['table_key']);//清空共享缓存,防止被上次服务的缓存数据影响
        $cache->close();
        $_server->start();
    }

    static function stop(array $config = [])
    {
        self::__init($config);
        return (new Client())->stopServer(
            self::$app['server_ip'],
            self::$app['server_port']
        );
    }

    static function reload(array $config = [])
    {
        !empty($config) and self::__init($config);
        (new Client())->reloadServer(
            self::$app['server_ip'],
            self::$app['server_port']
        );
    }

    static function serverStatus(array $config = [])
    {
        !empty($config) and self::__init($config);
        return (new Client())->serverStatus(
            self::$app['server_ip'],
            self::$app['server_port']
        );
    }

    /**
     * cli环境检查
     * @return bool
     * @throws Exception
     */
    static function checkEnvironment()
    {
        $mod = Environment::getName();
        if ($mod == 'cli') {
            return true;
        }
        throw  new Exception("Server 只能运行在CLI环境下~!", '1');
    }
}
