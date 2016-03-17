<?php
/**
 * filename: Process.php
 * Created by pjianwei.
 * Date: 2016/3/17 10:20
 * description:
 */

namespace jean\lib;


class Process
{
    protected $workers = [];
    protected $worker_num = 1;
    protected $fun = null;
    protected $data = [];
    protected $status = [];

    public function __construct(array $params)
    {
        isset($params['worker_num']) and $this->setWorkNum($params['worker_num']);
        !$this->worker_num and $this->worker_num = 1;
        $this->fun = $params['fun'];
        isset($params['data']) and $this->data = $params['data'];
    }

    protected function setWorkNum($num)
    {
        $num = intval($num);
        if ($num > 0 & $num <= 10) {
            $this->worker_num = $num;
        }
    }

    /**
     * @return $this
     */
    public function syncRun()
    {
        $data = $this->data;
        $fun = $this->fun;
        for ($i = 0; $i < $this->worker_num; $i++) {
            $process = new \swoole_process(function (\swoole_process $worker) use ($fun, $data, $i) {
                //\swoole_process::daemon(true);
                $params = isset($data[$i]) ? $data[$i] : [];
                try {
                    if (is_callable($fun)) {
                        $rs = call_user_func_array($fun, [$params, $i]);
                    } elseif (isset($fun[$i]) && is_callable($fun[$i])) {
                        $rs = call_user_func_array($fun[$i], [$params, $i]);
                    }
                    if ($rs) {
                        $worker->write(json_encode($rs));
                    }
                } catch (\Exception $e) {
                    $worker->exit(1);
                }
                $worker->exit(1);
            });
            $pid = $process->start();
            $this->workers[$pid] = $process;
        }
        return $this;
    }

    public function result()
    {
        $rs = [];
        $i = 0;
        foreach ($this->workers as $process) {
            $str = $process->read();
            $rs[$i] = $str;
            if ($str = json_decode($str)) {
                $rs[$i] = $str;
            }
            $i++;
        }
        return $rs;
    }

    public function quit()
    {
        for ($i = 0; $i < $this->worker_num; $i++) {
            $this->status[] = \swoole_process::wait();
        }
    }

    public function asyncRun()
    {
        //TODO
        return $this;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }
        return $this;
    }

    public function __destruct()
    {
        $this->quit();
    }


}