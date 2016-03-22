<?php
/**
 * filename: WorkProcess.php
 * Created by pjianwei.
 * Date: 2016/3/21 13:03
 * description:
 */

namespace jean\lib;

class WorkProcess
{
    protected $worker_id = null;
    protected $process_id = null;
    protected $server = null;
    static $timers = [];

    public function __construct(\swoole_server $server)
    {
        $this->server = $server;
        $this->worker_id = $server->worker_id;
        $this->process_id = $server->worker_pid;
    }

    /**
     * 获取任务源更新时间频率,单位秒
     * @param \swoole_server $server
     * @return int
     */
    static function getInterVal(\swoole_server $server)
    {
        $interval = isset($server->setting['task_update_interval']) ? intval($server->setting['task_update_interval']) : 30 * 60;
        $interval <= 0 and $interval = 30 * 60;
        return $interval * 1000;//定时器的频率单位是毫秒,这里转换成秒
    }

    public function run()
    {
        $this->runTask();
        $this->server->tick(self::getInterVal($this->server), function () {
            $this->runTask();
        });
    }

    public function runTask()
    {
        $tasks = $this->loadTask();
        if ($tasks) {
            foreach ($tasks as $taskId => $task) {
                if (!$task instanceof Task) continue;
                if ($this->checkTask($task)) {
                    $task->worker_pid = $this->process_id;
                    $task->worker_id = $this->worker_id;
                    $this->task($task);
                }
            }
            unset($tasks);
        }
    }

    public function task(Task $task)
    {
        switch ($task->action) {
            case 'start':
                if ($task->runStatus == '0') {
                    $this->startTask($task);
                }
                break;
            case 'stop':
                if ($task->runStatus > 0) {
                    $this->stopTask($task);
                }
                break;
            case 'reload':
                if ($task->runStatus > 0) {
                    $this->stopTask($task);
                } else {
                    $this->startTask($task);
                }
                break;
            default:
                break;
        }
    }

    public function startTask(Task $task)
    {
        $delay = intval($task->on) - time();
        if ($delay >= 1) {
            $this->server->after($delay * 1000, function () use ($task) {
                if ($task->taskType == 'single') {
                    if ($this->checkTaskRunStatus($task)) {
                        $this->runTaskUpdate($task);
                        $this->server->task($task);
                    }
                } else {
                    $timer_id = $this->server->tick($task->interval * 1000, function ($id) use ($task) {
                        $task->timer_id = $id;
                        if ($this->checkTaskRunStatus($task)) {
                            $this->runTaskUpdate($task);
                            $this->server->task($task);
                        } else {
                            try {
                                $this->server->clearTimer($id);
                                unset(self::$timers[$id]);
                            } catch (\Exception $e) {
                            }
                        }

                    });
                    self::$timers[$timer_id] = $timer_id;
                }
            });
        } else {
            if ($task->taskType == 'single') {
                if ($this->checkTaskRunStatus($task)) {
                    $this->runTaskUpdate($task);
                    $this->server->task($task);
                }
            } else {
                $timer_id = $this->server->tick($task->interval * 1000, function ($id) use ($task) {
                    $task->timer_id = $id;
                    $checked = $this->checkTaskRunStatus($task);
                    if ($checked) {
                        $this->runTaskUpdate($task);
                        $this->server->task($task);
                    } else {
                        try {
                            $this->server->clearTimer($id);
                            unset(self::$timers[$id]);
                        } catch (\Exception $e) {
                        }
                    }
                });
                self::$timers[$timer_id] = $timer_id;
            }
        }
    }


    /**
     * 循环任务每次循环的时候都需要检查任务的状态
     * @param Task $task
     * @return bool
     */
    public function checkTaskRunStatus(Task $task)
    {
        $tasks = $this->loadTask();
        if (!isset($tasks[$task->taskId])) return false;
        $task = $tasks[$task->taskId];
        //print_r($task);
        if ($task->loop_count > 0 && $task->hasRunCount >= $task->loop_count) {
            $this->endTask($task);
            return false;
        }
        if ($task->loop_end > 0 && $task->loop_end < time()) {
            $this->endTask($task);
            return false;
        }
        if ($task->action == 'stop') {
            $this->stopTask($task);
            return false;
        }
        if ($task->action == 'reload') {
            $this->reloadTask($task, true);
            return false;
        }
        if ($task->runStatus == '-1') {
            $this->endTask($task);
            return false;
        }
        return true;
    }

    public function runTaskUpdate(Task $task)
    {
        $task->runStatus = 1;
        $task->latestRunTime = time();
        $task->nextRunTime = $task->latestRunTime + $task->interval;
        $task->hasRunCount += 1;
        $this->saveTaskRunStatus($task);
    }

    public function saveTaskRunStatus(Task $task)
    {
        if (intval($task->taskId) <= 0) return;
        $redis = new Redis();
        $redis->lock();
        $obj = $redis->getObject();
        $redis->unlock();
        if (isset($obj['tasks'])) {
            $runTasks = array_map(function ($v) {
                return unserialize($v);
            }, get_object_vars($obj['tasks']));
            if (isset($runTasks[$task->taskId])) {
                $runTasks[$task->taskId] = $task;
                $taskServer = (new TaskServer($this->server));
                $taskServer->loadTask($runTasks);
                unset($taskServer);
            }
        }
        unset($redis);
        unset($obj);
    }

    protected function stopTask(Task $task)
    {
        $task->runStatus = 0;
        $this->saveTaskRunStatus($task);
        $timer_id = $task->timer_id;
        try {
            $this->server->clearTimer($timer_id);
        } catch (\Exception $e) {
        }
        unset (self::$timers[$timer_id]);
    }

    protected function reloadTask(Task $task, $start = false)
    {
        $currentStatus = $task->runStatus;
        $task->action = 'start';
        $task->runStatus = 0;
        $this->saveTaskRunStatus($task);
        $timer_id = $task->timer_id;
        if (isset(self::$timers[$timer_id])) {
            try {
                $this->server->clearTimer($timer_id);
            } catch (\Exception $e) {
            }
            unset (self::$timers[$timer_id]);
        }
        if ($currentStatus == '1') {
            if ($start) $this->startTask($task);
        } else {
            $this->startTask($task);
        }
    }

    protected function endTask(Task $task)
    {
        $task->runStatus = -1 and $this->saveTaskRunStatus($task);
        $timer_id = $task->timer_id;
        try {
            $this->server->clearTimer($timer_id);
        } catch (\Exception $e) {
        }
        unset (self::$timers[$timer_id]);
    }

    protected function checkTask(Task $task)
    {
        $taskId = intval($task['taskId']);
        return ($taskId % ($this->server->setting['worker_num'])) == $this->worker_id;
    }

    protected function loadTask()
    {
        $redis = new Redis();
        $redis->lock();
        $obj = $redis->getObject();
        $redis->unlock();
        if (isset($obj['tasks'])) {
            return $runTasks = array_map(function ($v) {
                return unserialize($v);
            }, get_object_vars($obj['tasks']));
        }
        return false;
    }
}