<?php
/**
 * filename: Task.php
 * Created by pjianwei.
 * Date: 2016/3/11 20:51
 * description:
 */

namespace jean\lib;


class Task extends BaseObject
{
    public $taskId = null;
    /**
     * 任务首次执行时间戳（秒）
     * 当前时间大于或等于该时间戳时即刻执行,
     * 否则等待到该时间戳间执行
     * @var integer $on
     */
    public $on = 0;
    /**
     * 任务间隔执行时间秒
     * @var int $interval
     */
    public $interval = 24 * 3600;

    public $taskType = 'single';

    public $callbackType = 'rest';

    public $hasRunCount = 0;//已经运行次数

    public $loop_count = 0;//循环任务执行的次数限制,0表示不限制

    public $action = 'start';//任务指令

    public $latestRunTime = 0;//最新一次执行时间

    public $nextRunTime = 0;

    public $timer_id = null; //循环任务的循环句柄标识ID

    public $task_pid = null; //循环任务运行的进程ID

    public $worker_pid = null;//任务进程的父进程ID

    public $worker_id = null; //任务进程的父进程索引编号

    public $loop_end = 0;//循环任务截止时间戳 0表示不限制

    public $data = [];//执行任务的参数

    public $script = null; //回调的脚本命令或URL

    public $requestType = 'get';//默认请求类型[get,post,delete,put]

    public $actionUpdateTime = null;


    /**
     * 任务运行状态（0:未开始;1：运行中,2：等待下一次执行中,-1:已执行完成）
     * @var int
     */
    public $runStatus = 0;

    public function __construct(array $params = [])
    {
        parent::__construct($params);
    }

    public function update(array $config)
    {
        if (!isset($config['actionUpdateTime'])) {
            return $this;
        }
        if ($config['actionUpdateTime'] <= $this->actionUpdateTime) {
            return $this;
        } else {
            if (isset($config['action']) && $config['action'] == 'reload') {
                unset($config['taskId']);
                self::__init($this, $config);
                $this->hasRunCount = 0;
                $this->nextRunTime = 0;
                $this->runStatus = 0;
                $this->latestRunTime = 0;
                return $this;
            }
        }
        $fields = ['loop_end', 'loop_count', 'callbackType', 'script', 'data', 'action', 'actionUpdateTime'];
        foreach ($fields as $k) {
            isset($config[$k]) and $this->$k = $config[$k];
        }
        return $this;
    }

    public function __toString()
    {
        return json_encode($this);
    }
}