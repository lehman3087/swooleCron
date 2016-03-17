<?php
/**
 * filename: Process.php
 * Created by pjianwei.
 * Date: 2016/3/17 10:20
 * description:
 */

namespace jean\lib;


class Process extends \swoole_process
{
    protected $workers = [];
    protected $worker_num = 1;
    protected $fun = null;
    protected $data = [];

    public function __construct(array $params)
    {
        isset($params['worker_num']) and $this->worker_num = intval($params['worker_num']);
        !$this->worker_num and $this->worker_num = 1;
        if (!isset($params['fun']) && is_callable($params['fun'])) {
            throw  new  \Exception("请确认回调函数", '10001');
        }
        $this->fun = $params['fun'];
        isset($params['data']) and $this->data = $params['data'];
    }

    protected function _init()
    {

    }

    public function run()
    {
        for ($i = 0; $i < $this->worker_num; $i++) {
            $process = new \swoole_process(function () use ($this) {
                \swoole_process::daemon(true);
                call_user_func($this->fun, $this->data);
                exit(0);
            });
            $pid = $process->start();
            $workers[$pid] = $process;
        }
    }


}