<?php
/**
 * filename: Client.php
 * Created by pjianwei.
 * Date: 2016/3/14 9:29
 * description:
 */

namespace jean\swoolecron;


use jean\lib\SingleTask;
use jean\lib\Task;

class Client
{
    static private $client = null;

    public function __construct($ip = null, $port = 0)
    {

        if (is_null($ip) || !$port) {
            Server::__init([]);
            $ip = Server::$app['server_ip'];
            $port = Server::$app['server_port'];
        }
        self::getClient($ip, $port);
    }

    private static function getClient($host, $port)
    {
        is_null(self::$client) and self::$client = new \swoole_client(SWOOLE_SOCK_TCP);
        if (!self::$client->isConnected()) {
            if (!self::$client->connect($host, $port, 2)) {
                throw new  \Exception("SWOOLE SERVER 连接异常.ip:$host,port:$port", self::$client->errCode);
            }
        }
        return self::$client;
    }

    /**
     * 这里的任务暂时全部走匿名ID任务,投递过去马上执行,不支持相关熟悉控制和状态查询
     * @param SingleTask $task
     * @return mixed
     */
    function job(SingleTask $task)
    {
        $client = self::$client;
        $_params = array(
            'target' => 'task',
            'data' => serialize($task),
        );
        $client->send(json_encode($_params));
        $data = $client->recv();
        if ($rs = json_decode($data)) {
            return $rs;
        }
        return $data;
    }

    function stopTask(Task $task)
    {

    }

    function reloadTask(Task $task)
    {

    }

    function statusTask(Task $task)
    {

    }

    function countTask()
    {

    }

    function listTask($start = 0, $limit = 10)
    {

    }

    function reloadServer()
    {
        $client = self::$client;
        $_params = array(
            'target' => 'server',
            'action' => 'reload',
        );
        $client->send(json_encode($_params));
        $data = $client->recv();
        if ($rs = json_decode($data)) {
            return $rs;
        }
        return $data;
    }

    static function stopServer()
    {
        $client = self::$client;
        $_params = array(
            'target' => 'server',
            'action' => 'stop',
        );
        $client->send(json_encode($_params));
        $data = $client->recv();
        if ($rs = json_decode($data)) {
            return $rs;
        }
        return $data;
    }

    function serverStatus($ip, $port)
    {
        $client = self::$client;
        $_params = array(
            'target' => 'server',
            'action' => 'status',
        );
        $client->send(json_encode($_params));
        $data = $client->recv();
        if ($rs = json_decode($data)) {
            return $rs;
        }
        return $data;
    }
}