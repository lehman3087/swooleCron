<?php
/**
 * filename: TaskServer.php
 * Created by pjianwei.
 * Date: 2016/3/19 15:01
 * description:
 */

namespace jean\lib;


class TaskProcess
{
    protected $timer_id = null;
    protected $worker_pid = null;
    protected $server = null;
    protected $task_pid = null;

    public function __construct(\swoole_server $server)
    {
        $this->worker_pid = $server->worker_pid;
        $this->server = $server;
    }

    public function run(Task $task)
    {
        $rs = null;
        if ($task->callbackType == 'rest') {
            $method = 'curl' . ucwords(strtolower($task->requestType));
            if (method_exists(new CurlHelper(), $method)) {
                $rs = CurlHelper::$method($task->script, $task->data);
            }
        } else{
            exec($task->script . ' ' .json_encode($task->data), $rs);
        }
        return $rs;
    }
}