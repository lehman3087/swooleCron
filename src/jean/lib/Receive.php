<?php
/**
 * filename: Receive.php
 * Created by pjianwei.
 * Date: 2016/3/18 23:33
 * description:
 */

namespace jean\lib;


class Receive
{
    protected $server = null;

    public function __construct(\swoole_server $server)
    {
        $this->server = $server;
    }

    public function run($data)
    {
        if ($data = json_decode($data)) {
            $data = get_object_vars($data);
            switch ($data['target']) {
                case 'task':
                    $task = unserialize($data['data']);
                    if ($task instanceof Task) {
                        return $this->runTask($task);
                    }
                    break;
                case 'server':
                    return $this->runServer($data['action']);
                    break;
            }
        }
        return false;
    }

    protected function runTask(Task $task)
    {
        $this->server->task($task);
        return json_encode(['code' => 0, 'message' => '任务已顺利投放']);
    }

    protected function runServer($action)
    {
        switch($action){
            case 'stop':
                return $this->server->shutdown();
                break;
            case 'reload':
                return $this->server->reload();
                break;
            case 'status':
                return json_encode($this->server->stats());
                break;
            default:
                break;
        }
    }
}