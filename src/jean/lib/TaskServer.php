<?php
/**
 * filename: TaskServer.php
 * Created by pjianwei.
 * Date: 2016/3/21 13:46
 * description:
 */

namespace jean\lib;


class TaskServer
{
    protected $redis = null;
    public $server = null;

    public function __construct(\swoole_server $server)
    {
        $this->redis = new Redis();
        $this->server = $server;
    }

    public function lock()
    {
        $this->redis->lock();
        return $this;
    }

    public function unlock()
    {
        $this->redis->unlock();
        return $this;
    }

    public function getTask($taskId)
    {
    }

    public function listTask()
    {
        $obj = $this->redis->getObject();
        if (isset($obj['tasks'])) {
            return array_map(function ($v) {
                return unserialize($v);
            }, $obj['tasks']);
        }
        return [];
    }

    public function initTask()
    {
        $tasks = [];
        switch ($this->server->setting['task_source']) {
            case 'rest':
                try {
                    $rs = CurlHelper::curlGet($this->server->setting['task_url']);
                    $result = isset($rs->data) ? $rs->data : (isset($rs->result) ? $rs->result : []);
                    if (is_array($result)) {
                        $tasks = $result;
                    } else if (is_object($result)) {
                        $tasks = get_object_vars($result);
                    }
                    $tasks = array_map(function ($v) {
                        if (is_object($v)) return get_object_vars($v);
                        return $v;
                    }, $tasks);
                } catch (\Exception $e) {
                    $tasks = [];
                }
                break;
            default:
                try {
                    $tasks = include SRC . 'jean/config/task.php';
                } catch (\Exception $e) {
                    $tasks = [];
                }
                break;
        }
        $rs = [];
        foreach ($tasks as $task) {
            $rs[$task['taskId']] = $task;
        }
        return $rs;
    }

    public function loadTask($task = [])
    {
        $overLoad = false;
        !empty($task) and $overLoad = true;
        $task = empty($task) ? $this->initTask() : $task;
        if (empty($task)) return false;
        if ($this->lock()) {
            $res = $this->redis->getObject();
            if (empty($res)) {
                foreach ($task as $item) {
                    if (!isset($item['taskId'])) continue;
                    $res['tasks'][$item['taskId']] = serialize(new Task($item));
                }
                $this->redis->setObject($res);
            } elseif (isset($res['tasks'])) {
                $runTasks = ($overLoad == true) ? $task : array_map(function ($v) {
                    return unserialize($v);
                }, get_object_vars($res['tasks']));
                foreach ($runTasks as $k => $item) {
                    if (isset($task[$k]) && $overLoad == false) {
                        $item->update($task[$item->taskId]);
                    }
                    $runTasks[$k] = serialize($item);
                }
                $res['tasks'] = $runTasks;
                $this->redis->setObject($res);
            }
            $this->unlock();
        }
        unset($task);
        unset($runTasks);
    }
}